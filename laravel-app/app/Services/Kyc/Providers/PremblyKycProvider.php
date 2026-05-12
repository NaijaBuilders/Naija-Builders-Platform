<?php

namespace App\Services\Kyc\Providers;

use App\Models\SupplierApplication;
use App\Services\Kyc\Contracts\KycProvider;
use App\Services\Kyc\Contracts\ProgressiveKycProvider;
use App\Services\Kyc\Data\ProviderResponseNormalizer;
use App\Services\Kyc\Data\VerificationResult;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PremblyKycProvider implements KycProvider, ProgressiveKycProvider
{
    public function __construct(private readonly ProviderResponseNormalizer $normalizer) {}

    public function name(): string
    {
        return 'prembly';
    }

    public function verifyBusiness(SupplierApplication $application, array $context = []): VerificationResult
    {
        if (is_array($context['provider_response'] ?? null)) {
            return $this->normalizer->fromPrembly('cac_lookup', $context['provider_response']);
        }

        [$rcNumber, $companyType] = $this->cacParts((string) $application->cac_number);

        return $this->postAndNormalize('cac_lookup', $this->endpoint('cac'), [
            'rc_number' => $rcNumber,
            'company_name' => $application->business_name,
            'company_type' => $companyType,
        ]);
    }

    public function verifyIdentity(SupplierApplication $application, array $context = []): VerificationResult
    {
        if (is_array($context['provider_response'] ?? null)) {
            return $this->normalizer->fromPrembly('identity', $context['provider_response']);
        }

        $results = [];
        $documentResult = $this->verifySupplierDocumentWithFace($application);
        if ($documentResult !== null) {
            $results[] = $documentResult;
        }

        $bvn = $this->digits($context['bvn'] ?? '');
        if ($bvn !== '') {
            $results[] = $this->postAndNormalize('identity', $this->endpoint('bvn_basic'), [
                'number' => $bvn,
            ]);
        }

        $nin = $this->digits($context['nin'] ?? '');
        if ($nin !== '') {
            $results[] = $this->postAndNormalize('identity', $this->endpoint('nin'), [
                'number_nin' => $nin,
            ]);
        }

        if ($results === []) {
            return VerificationResult::failed('identity', $this->name(), ['IDENTITY_NUMBER_MISSING_OR_INVALID']);
        }

        return $this->combineResults('identity', $results);
    }

    public function verifyBank(SupplierApplication $application, array $context = []): VerificationResult
    {
        if (is_array($context['provider_response'] ?? null)) {
            return $this->normalizer->fromPrembly('bank', $context['provider_response']);
        }

        $accountNumber = $this->digits($context['account_number'] ?? '');
        if ($accountNumber === '') {
            return VerificationResult::failed('bank', $this->name(), ['BANK_ACCOUNT_UNVERIFIABLE']);
        }

        $payload = [
            'number' => $accountNumber,
            'bank_code' => (string) $application->bank_code,
        ];

        $accountName = trim((string) $application->account_name);
        if ($accountName !== '') {
            $payload[$this->bankCustomerNameField()] = $accountName;

            return $this->postAndNormalize('bank', $this->endpoint('bank_comparison'), $payload);
        }

        return $this->postAndNormalize('bank', $this->endpoint('bank_basic'), $payload);
    }

    public function screen(SupplierApplication $application, array $context = []): VerificationResult
    {
        if (is_array($context['provider_response'] ?? null)) {
            return $this->normalizer->fromPrembly('aml_pep', $context['provider_response']);
        }

        $endpoint = $this->endpoint('aml_pep');
        if ($endpoint === null) {
            return VerificationResult::manualReview('aml_pep', $this->name(), ['PROVIDER_CHECK_NOT_CONFIGURED']);
        }

        // TODO: Confirm Prembly's AML payload shape for business and contact screening before live use.
        return $this->postAndNormalize('aml_pep', $endpoint, [
            'name' => $application->business_name ?: $application->contact_name,
        ]);
    }

    public function passiveFraudCheck(array $context = []): VerificationResult
    {
        if (is_array($context['provider_response'] ?? null)) {
            return $this->normalizer->fromPrembly('buyer_tier_1_passive_fraud', $context['provider_response']);
        }

        // TODO: Wire Prembly fraud/payment telemetry when buyer onboarding starts.
        return VerificationResult::manualReview('buyer_tier_1_passive_fraud', $this->name(), ['PROVIDER_CHECK_NOT_CONFIGURED']);
    }

    public function verifyEmail(array $context = []): VerificationResult
    {
        if (is_array($context['provider_response'] ?? null)) {
            return $this->normalizer->fromPrembly('email', $context['provider_response']);
        }

        $email = $this->email($context['email'] ?? null);
        if ($email === null) {
            return VerificationResult::failed('email', $this->name(), ['EMAIL_INVALID']);
        }

        return $this->postAndNormalize('email', $this->endpoint('email_company_search'), [
            'email' => $email,
        ]);
    }

    public function verifyDocumentWithFace(array $context = []): VerificationResult
    {
        if (is_array($context['provider_response'] ?? null)) {
            return $this->normalizer->fromPrembly('buyer_tier_2_identity', $context['provider_response']);
        }

        $documentImage = $this->string($context['doc_image'] ?? null);
        $selfieImage = $this->string($context['selfie_image'] ?? null);

        if ($documentImage === null || $selfieImage === null) {
            return VerificationResult::manualReview('buyer_tier_2_identity', $this->name(), ['PROVIDER_CHECK_NOT_CONFIGURED']);
        }

        return $this->postAndNormalize('buyer_tier_2_identity', $this->endpoint('document_with_face'), [
            'doc_type' => $this->premblyDocumentType((string) ($context['id_document_type'] ?? 'national_id')),
            'doc_country' => (string) ($context['doc_country'] ?? 'NGA'),
            'doc_image' => $documentImage,
            'selfie_image' => $selfieImage,
        ]);
    }

    public function verifyBillingName(array $context = []): VerificationResult
    {
        if (is_array($context['provider_response'] ?? null)) {
            return $this->normalizer->fromPrembly('buyer_tier_3_billing_name', $context['provider_response']);
        }

        $accountNumber = $this->digits($context['account_number'] ?? '');
        $bankCode = $this->string($context['bank_code'] ?? null);
        $billingName = $this->string($context['billing_name'] ?? $context['account_name'] ?? null);

        if ($accountNumber === '' || $bankCode === null || $billingName === null) {
            return VerificationResult::manualReview('buyer_tier_3_billing_name', $this->name(), ['PROVIDER_CHECK_NOT_CONFIGURED']);
        }

        return $this->postAndNormalize('buyer_tier_3_billing_name', $this->endpoint('bank_comparison'), [
            'number' => $accountNumber,
            'bank_code' => $bankCode,
            $this->bankCustomerNameField() => $billingName,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function postAndNormalize(string $checkType, ?string $endpoint, array $payload): VerificationResult
    {
        if ($endpoint === null || $endpoint === '') {
            return VerificationResult::manualReview($checkType, $this->name(), ['PROVIDER_CHECK_NOT_CONFIGURED']);
        }

        $response = $this->post($endpoint, $payload);
        if ($response === null) {
            return VerificationResult::manualReview($checkType, $this->name(), ['PROVIDER_TEMPORARY_FAILURE']);
        }

        return $this->normalizer->fromPrembly($checkType, $response);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    private function post(string $endpoint, array $payload): ?array
    {
        $correlationId = (string) Str::uuid();

        try {
            $response = $this->client($correlationId)->post($endpoint, $payload);
        } catch (ConnectionException) {
            return null;
        }

        $data = $response->json();
        if (! is_array($data)) {
            return null;
        }

        $data['correlation_id'] = $correlationId;
        if (! $response->successful() && ! isset($data['response_code'])) {
            $data['response_code'] = (string) $response->status();
        }

        return $data;
    }

    private function client(string $correlationId): PendingRequest
    {
        $headers = [
            'x-api-key' => (string) config('services.kyc.prembly.api_key'),
            'X-Correlation-ID' => $correlationId,
        ];

        $appId = trim((string) config('services.kyc.prembly.app_id', ''));
        if ($appId !== '') {
            $headers['app-id'] = $appId;
        }

        return Http::baseUrl(rtrim((string) config('services.kyc.prembly.base_url'), '/'))
            ->acceptJson()
            ->asJson()
            ->withHeaders($headers)
            ->timeout(max(1, (int) config('services.kyc.prembly.timeout', 30)))
            ->retry(2, 200, fn ($exception): bool => $exception instanceof ConnectionException, throw: false);
    }

    private function verifySupplierDocumentWithFace(SupplierApplication $application): ?VerificationResult
    {
        if (! $application->id_document_path || ! $application->selfie_path) {
            return null;
        }

        if ($this->storedFileIsPdf((string) $application->id_document_path)) {
            return VerificationResult::manualReview('identity', $this->name(), ['ID_DOCUMENT_UNSUPPORTED_FOR_PROVIDER']);
        }

        $document = $this->base64PrivateFile((string) $application->id_document_path);
        $selfie = $this->base64PrivateFile((string) $application->selfie_path);

        if ($document === null || $selfie === null) {
            return VerificationResult::manualReview('identity', $this->name(), ['ID_DOCUMENT_UNAVAILABLE']);
        }

        return $this->postAndNormalize('identity', $this->endpoint('document_with_face'), [
            'doc_type' => $this->premblyDocumentType((string) $application->id_document_type),
            'doc_country' => 'NGA',
            'doc_image' => $document,
            'selfie_image' => $selfie,
        ]);
    }

    private function base64PrivateFile(string $path): ?string
    {
        if (! Storage::disk('local')->exists($path)) {
            return null;
        }

        $contents = Storage::disk('local')->get($path);

        return is_string($contents) ? base64_encode($contents) : null;
    }

    private function storedFileIsPdf(string $path): bool
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf';
    }

    /**
     * @param  array<int, VerificationResult>  $results
     */
    private function combineResults(string $checkType, array $results): VerificationResult
    {
        $reasonCodes = [];
        $data = [];
        $references = [];
        $hasFailed = false;
        $hasManualReview = false;

        foreach ($results as $result) {
            $hasFailed = $hasFailed || $result->isFailed();
            $hasManualReview = $hasManualReview || $result->isManualReview();
            $reasonCodes = array_merge($reasonCodes, $result->reasonCodes);
            $data = array_merge($data, $result->data);

            $reference = $this->string($result->data['reference'] ?? null);
            if ($reference !== null) {
                $references[] = $reference;
            }
        }

        $reasonCodes = array_values(array_unique($reasonCodes));
        $references = array_values(array_unique($references));
        if ($references !== []) {
            $data['reference'] = $references[0];
            $data['provider_references'] = $references;
        }

        if ($hasFailed) {
            return VerificationResult::failed($checkType, $this->name(), $reasonCodes ?: ['PROVIDER_CHECK_FAILED'], $data);
        }

        if ($hasManualReview) {
            return VerificationResult::manualReview($checkType, $this->name(), $reasonCodes ?: ['PROVIDER_MANUAL_REVIEW'], $data);
        }

        return VerificationResult::passed($checkType, $this->name(), $data);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function cacParts(string $value): array
    {
        $value = strtoupper(trim($value));
        if (preg_match('/^(RC|BN|IT)[\s\-\/]*(.+)$/', $value, $matches) === 1) {
            return [preg_replace('/[^A-Z0-9]/', '', $matches[2]) ?: $matches[2], $matches[1]];
        }

        return [preg_replace('/[^A-Z0-9]/', '', $value) ?: $value, 'RC'];
    }

    private function premblyDocumentType(string $documentType): string
    {
        return match ($documentType) {
            'passport', 'international_passport' => 'PP',
            'drivers_license', 'driver_licence', 'driver_license' => 'DL',
            default => 'ID',
        };
    }

    private function endpoint(string $key): ?string
    {
        $endpoint = config('services.kyc.prembly.endpoints.'.$key);

        return is_string($endpoint) && trim($endpoint) !== '' ? trim($endpoint) : null;
    }

    private function bankCustomerNameField(): string
    {
        $field = trim((string) config('services.kyc.prembly.bank_customer_name_field', 'customer_name'));

        return $field === '' ? 'customer_name' : $field;
    }

    private function digits(mixed $value): string
    {
        return preg_replace('/\D+/', '', (string) $value) ?: '';
    }

    private function string(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function email(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = strtolower(trim((string) $value));

        return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : null;
    }
}
