<?php

namespace App\Services\SupplierOnboarding;

use App\Models\ManualReviewNote;
use App\Models\RiskSignal;
use App\Models\SupplierApplication;
use App\Models\SupplierAuditLog;
use App\Models\User;
use App\Models\VerificationCheck;
use App\Models\VerificationDecision;
use App\Services\Kyc\Contracts\KycProvider;
use App\Services\Kyc\Data\DecisionResult;
use App\Services\Kyc\Data\VerificationResult;
use App\Support\NameFormatter;
use App\Support\Security\SensitiveData;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SupplierOnboardingService
{
    public function __construct(
        private readonly KycProvider $provider,
        private readonly SupplierDecisionEngine $decisionEngine,
    ) {}

    public function applicationForUser(User $user): SupplierApplication
    {
        return SupplierApplication::query()->firstOrCreate(
            ['user_id' => (int) $user->id],
            [
                'status' => SupplierApplication::STATUS_DRAFT,
                'current_stage' => 'ACCOUNT',
                'provider' => $this->provider->name(),
            ]
        );
    }

    public function captureRegistrationSignals(User $user, Request $request, ?string $phone = null): SupplierApplication
    {
        $application = $this->applicationForUser($user);

        if ($phone) {
            $this->storeRiskSignal($application, 'phone', $phone, SensitiveData::maskDigits($phone), ['source' => 'registration']);
        }

        $this->captureRequestSignals($application, $request);

        return $application;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function submitBusinessDetails(User $user, array $data, Request $request): SupplierApplication
    {
        $application = $this->applicationForUser($user);

        $application->fill([
            'status' => $this->editableStatus($application->status),
            'current_stage' => 'BUSINESS_DETAILS',
            'provider' => $this->provider->name(),
            'cac_number' => strtoupper((string) $data['cac_number']),
            'cac_number_hash' => SensitiveData::fingerprint((string) $data['cac_number']),
            'business_name' => $data['business_name'],
            'business_type' => $data['business_type'],
            'business_address' => $data['business_address'],
            'state' => $data['state'],
            'contact_name' => NameFormatter::title((string) ($data['contact_name'] ?? $user->full_name ?? '')),
            'contact_email' => $data['contact_email'] ?? $user->email ?? null,
            'contact_phone' => $data['contact_phone'] ?? $user->phone ?? null,
        ])->save();

        $this->storeRiskSignal($application, 'phone', (string) ($application->contact_phone ?? ''), SensitiveData::maskDigits((string) $application->contact_phone), ['source' => 'business_details']);
        $this->captureRequestSignals($application, $request);
        $this->recordCheck($application, $this->provider->verifyBusiness($application));
        $this->syncLegacyUserProfile($application);

        return $application->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function submitIdentity(User $user, array $data, Request $request): SupplierApplication
    {
        $application = $this->applicationForUser($user);
        $bvn = (string) ($data['bvn'] ?? '');
        $nin = (string) ($data['nin'] ?? '');

        $application->fill([
            'status' => $this->editableStatus($application->status),
            'current_stage' => 'IDENTITY_VERIFICATION',
            'provider' => $this->provider->name(),
            'bvn_hash' => $bvn !== '' ? SensitiveData::fingerprint($bvn) : $application->bvn_hash,
            'bvn_mask' => $bvn !== '' ? SensitiveData::maskDigits($bvn) : $application->bvn_mask,
            'nin_hash' => $nin !== '' ? SensitiveData::fingerprint($nin) : $application->nin_hash,
            'nin_mask' => $nin !== '' ? SensitiveData::maskDigits($nin) : $application->nin_mask,
            'id_document_type' => $data['id_document_type'] ?? $application->id_document_type,
        ]);

        if ($request->file('selfie') instanceof UploadedFile) {
            $application->selfie_path = $this->storeUploadedFile($request->file('selfie'), $application, 'selfie');
        }

        if ($request->file('id_document') instanceof UploadedFile) {
            $application->id_document_path = $this->storeUploadedFile($request->file('id_document'), $application, 'id-document');
        }

        $application->save();

        $this->captureRequestSignals($application, $request);
        $this->recordCheck($application, $this->provider->verifyIdentity($application, [
            'bvn' => $bvn,
            'nin' => $nin,
            'id_document_reference' => $data['id_document_reference'] ?? '',
            'face_match_reference' => $data['face_match_reference'] ?? '',
            'liveness_reference' => $data['liveness_reference'] ?? '',
        ]));

        return $application->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function submitBankDetails(User $user, array $data, Request $request): SupplierApplication
    {
        $application = $this->applicationForUser($user);
        $accountNumber = (string) $data['account_number'];

        $application->fill([
            'status' => $this->editableStatus($application->status),
            'current_stage' => 'BANK_VERIFICATION',
            'provider' => $this->provider->name(),
            'bank_name' => $data['bank_name'],
            'bank_code' => $data['bank_code'],
            'account_number_hash' => SensitiveData::fingerprint($accountNumber),
            'account_number_mask' => SensitiveData::maskDigits($accountNumber),
            'account_name' => $data['account_name'] ?? $application->business_name,
        ])->save();

        $this->storeRiskSignal($application, 'bank_account', $accountNumber, SensitiveData::maskDigits($accountNumber), ['source' => 'bank_details']);
        $this->captureRequestSignals($application, $request);
        $this->recordCheck($application, $this->provider->verifyBank($application, [
            'account_number' => $accountNumber,
        ]));
        $this->syncLegacyUserProfile($application);

        return $application->refresh();
    }

    public function submitForDecision(User $user, Request $request): SupplierApplication
    {
        $application = $this->applicationForUser($user)->refresh();
        $previousStatus = (string) $application->status;

        DB::transaction(function () use ($application, $previousStatus): void {
            $application->fill([
                'status' => SupplierApplication::STATUS_VERIFYING,
                'current_stage' => 'AUTOMATED_CHECKS',
                'submitted_at' => $application->submitted_at ?: now(),
                'provider' => $this->provider->name(),
            ])->save();

            $checks = $this->latestChecks($application);

            if (! $checks->has('cac_lookup')) {
                $checks->put('cac_lookup', $this->recordCheck($application, $this->missingOrProviderBusinessCheck($application)));
            }

            if (! $checks->has('identity')) {
                $checks->put('identity', $this->recordCheck($application, $this->provider->verifyIdentity($application)));
            }

            if (! $checks->has('bank')) {
                $checks->put('bank', $this->recordCheck($application, $this->missingOrProviderBankCheck($application)));
            }

            $checks->put('aml_pep', $this->recordCheck($application, $this->provider->screen($application)));

            $duplicateCheck = $this->duplicateDetectionCheck($application);
            if ($duplicateCheck) {
                $checks->put('duplicate_detection', $this->recordCheck($application, $duplicateCheck));
            }

            $decision = $this->decisionEngine->decide($checks->values());
            $this->persistDecision($application, $decision, $previousStatus, null, null);
        });

        $this->captureRequestSignals($application, $request);

        return $application->refresh();
    }

    public function approveManually(SupplierApplication $application, User $reviewer, ?string $notes = null): SupplierApplication
    {
        return $this->manualDecision($application, $reviewer, DecisionResult::AUTO_APPROVED, SupplierApplication::STATUS_APPROVED, 'ADMIN_APPROVED', $notes);
    }

    public function rejectManually(SupplierApplication $application, User $reviewer, ?string $notes = null): SupplierApplication
    {
        return $this->manualDecision($application, $reviewer, DecisionResult::AUTO_REJECTED, SupplierApplication::STATUS_REJECTED, 'ADMIN_REJECTED', $notes);
    }

    public function requestMoreInfo(SupplierApplication $application, User $reviewer, ?string $message, ?string $notes = null): SupplierApplication
    {
        return DB::transaction(function () use ($application, $reviewer, $message, $notes): SupplierApplication {
            $previousStatus = (string) $application->status;
            $application->fill([
                'status' => SupplierApplication::STATUS_MORE_INFO_REQUIRED,
                'more_info_message' => $message ?: 'Please update the requested supplier application information.',
                'supplier_message' => null,
            ])->save();

            ManualReviewNote::query()->create([
                'supplier_application_id' => $application->id,
                'reviewer_id' => $reviewer->id,
                'action' => 'MORE_INFO_REQUIRED',
                'note' => $notes,
            ]);

            $this->writeAudit($application, 'MORE_INFO_REQUIRED', DecisionResult::MANUAL_REVIEW, $previousStatus, SupplierApplication::STATUS_MORE_INFO_REQUIRED, ['manual_review'], ['MORE_INFO_REQUIRED'], $reviewer, $notes);
            $this->syncUserVerificationStatus($application);

            return $application->refresh();
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function supplierPayload(SupplierApplication $application): array
    {
        return [
            'id' => (int) $application->id,
            'status' => (string) $application->status,
            'current_stage' => (string) $application->current_stage,
            'supplier_message' => $application->status === SupplierApplication::STATUS_REJECTED
                ? SupplierApplication::GENERIC_REJECTION_MESSAGE
                : ($application->supplier_message ?: null),
            'more_info_message' => $application->status === SupplierApplication::STATUS_MORE_INFO_REQUIRED
                ? $application->more_info_message
                : null,
            'business' => [
                'cac_number' => $application->cac_number,
                'business_name' => $application->business_name,
                'business_type' => $application->business_type,
                'state' => $application->state,
            ],
            'identity' => [
                'bvn' => $application->bvn_mask,
                'nin' => $application->nin_mask,
                'id_document_type' => $application->id_document_type,
            ],
            'bank' => [
                'bank_name' => $application->bank_name,
                'bank_code' => $application->bank_code,
                'account_number' => $application->account_number_mask,
                'account_name' => $application->account_name,
            ],
            'submitted_at' => optional($application->submitted_at)->toISOString(),
            'decided_at' => optional($application->decided_at)->toISOString(),
            'updated_at' => optional($application->updated_at)->toISOString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function adminPayload(SupplierApplication $application): array
    {
        $application->loadMissing(['user', 'checks', 'decisions', 'auditLogs', 'manualReviewNotes']);

        return [
            'id' => (int) $application->id,
            'supplier' => [
                'id' => (int) $application->user_id,
                'name' => NameFormatter::title((string) ($application->user->full_name ?? '')),
                'email' => (string) ($application->user->email ?? ''),
                'phone' => SensitiveData::maskDigits((string) ($application->user->phone ?? '')),
            ],
            'status' => (string) $application->status,
            'current_stage' => (string) $application->current_stage,
            'provider' => (string) $application->provider,
            'business' => [
                'cac_number' => $application->cac_number,
                'business_name' => $application->business_name,
                'business_type' => $application->business_type,
                'business_address' => $application->business_address,
                'state' => $application->state,
            ],
            'identity' => [
                'bvn' => $application->bvn_mask,
                'nin' => $application->nin_mask,
                'id_document_type' => $application->id_document_type,
                'face_match_score' => $application->face_match_score,
                'liveness_passed' => $application->liveness_passed,
            ],
            'bank' => [
                'bank_name' => $application->bank_name,
                'bank_code' => $application->bank_code,
                'account_number' => $application->account_number_mask,
                'account_name' => $application->account_name,
                'name_match_score' => $application->name_match_score,
            ],
            'checks' => $application->checks->map(fn (VerificationCheck $check): array => [
                'check_type' => $check->check_type,
                'status' => $check->status,
                'provider' => $check->provider,
                'provider_reference' => $check->provider_reference,
                'reason_codes' => $check->reason_codes ?? [],
                'normalized_result' => $check->normalized_result ?? [],
                'checked_at' => optional($check->checked_at)->toISOString(),
            ])->values(),
            'decisions' => $application->decisions->map(fn (VerificationDecision $decision): array => [
                'decision' => $decision->decision,
                'previous_status' => $decision->previous_status,
                'new_status' => $decision->new_status,
                'provider' => $decision->provider,
                'provider_references' => $decision->provider_references ?? [],
                'triggered_checks' => $decision->triggered_checks ?? [],
                'internal_reason_codes' => $decision->internal_reason_codes ?? [],
                'reviewer_id' => $decision->reviewer_id,
                'notes' => $decision->notes,
                'created_at' => optional($decision->created_at)->toISOString(),
            ])->values(),
            'audit_logs' => $application->auditLogs->map(fn (SupplierAuditLog $log): array => [
                'action' => $log->action,
                'decision' => $log->decision,
                'previous_status' => $log->previous_status,
                'new_status' => $log->new_status,
                'provider' => $log->provider,
                'provider_references' => $log->provider_references ?? [],
                'triggered_checks' => $log->triggered_checks ?? [],
                'internal_reason_codes' => $log->internal_reason_codes ?? [],
                'reviewer_id' => $log->reviewer_id,
                'notes' => $log->notes,
                'created_at' => optional($log->created_at)->toISOString(),
            ])->values(),
        ];
    }

    private function editableStatus(?string $status): string
    {
        return in_array($status, [SupplierApplication::STATUS_APPROVED, SupplierApplication::STATUS_REJECTED, SupplierApplication::STATUS_SUSPENDED], true)
            ? SupplierApplication::STATUS_DRAFT
            : ($status ?: SupplierApplication::STATUS_DRAFT);
    }

    private function storeUploadedFile(UploadedFile $file, SupplierApplication $application, string $prefix): string
    {
        return $file->store('supplier-verification/'.$application->id.'/'.$prefix, 'local') ?: '';
    }

    private function recordCheck(SupplierApplication $application, VerificationResult $result): VerificationResult
    {
        VerificationCheck::query()->create([
            'supplier_application_id' => $application->id,
            'provider' => $result->provider,
            'provider_reference' => $this->providerReferenceFromResult($result),
            'check_type' => $result->checkType,
            'status' => $result->status,
            'reason_codes' => $result->reasonCodes,
            'normalized_result' => $result->toArray(),
            'checked_at' => now(),
        ]);

        $this->applyCheckSideEffects($application, $result);

        return $result;
    }

    public function applyProviderWebhookResult(VerificationCheck $check, VerificationResult $result): SupplierApplication
    {
        return DB::transaction(function () use ($check, $result): SupplierApplication {
            $check->loadMissing('application');
            $application = $check->application;
            $previousStatus = (string) $application->status;

            $check->forceFill([
                'provider' => $result->provider,
                'provider_reference' => $this->providerReferenceFromResult($result) ?: $check->provider_reference,
                'status' => $result->status,
                'reason_codes' => $result->reasonCodes,
                'normalized_result' => $result->toArray(),
                'checked_at' => now(),
            ])->save();

            $this->applyCheckSideEffects($application, $result);
            $this->writeAudit($application, 'PROVIDER_WEBHOOK_RECEIVED', null, $previousStatus, (string) $application->status, [$result->checkType], $result->reasonCodes, null, null);

            if (in_array($application->status, [SupplierApplication::STATUS_VERIFYING, SupplierApplication::STATUS_SUBMITTED], true)) {
                $checks = $this->latestChecks($application);
                if ($checks->has('cac_lookup') && $checks->has('identity') && $checks->has('bank') && $checks->has('aml_pep')) {
                    $decision = $this->decisionEngine->decide($checks->values());
                    $this->persistDecision($application, $decision, $previousStatus, null, null);
                }
            }

            return $application->refresh();
        });
    }

    private function applyCheckSideEffects(SupplierApplication $application, VerificationResult $result): void
    {
        $updates = [];
        if (array_key_exists('face_match_score', $result->data)) {
            $updates['face_match_score'] = (int) $result->data['face_match_score'];
        }
        if (array_key_exists('liveness_passed', $result->data)) {
            $updates['liveness_passed'] = (bool) $result->data['liveness_passed'];
        }
        if (array_key_exists('name_match_score', $result->data)) {
            $updates['name_match_score'] = (int) $result->data['name_match_score'];
        }
        if (array_key_exists('account_name', $result->data)) {
            $updates['account_name'] = (string) $result->data['account_name'];
        }

        if ($updates !== []) {
            $application->forceFill($updates)->save();
        }
    }

    /**
     * @return Collection<string, VerificationResult>
     */
    private function latestChecks(SupplierApplication $application): Collection
    {
        return VerificationCheck::query()
            ->where('supplier_application_id', $application->id)
            ->latest('id')
            ->get()
            ->unique('check_type')
            ->mapWithKeys(fn (VerificationCheck $check): array => [
                $check->check_type => new VerificationResult(
                    (string) $check->check_type,
                    (string) $check->status,
                    (string) $check->provider,
                    $check->reason_codes ?? [],
                    array_filter(array_merge(
                        $check->normalized_result['data'] ?? [],
                        ['reference' => $check->provider_reference]
                    ), fn ($value): bool => $value !== null && $value !== '')
                ),
            ]);
    }

    private function missingOrProviderBusinessCheck(SupplierApplication $application): VerificationResult
    {
        if (! $application->cac_number || ! $application->business_name) {
            return VerificationResult::failed('cac_lookup', $this->provider->name(), ['CAC_INVALID_OR_UNREGISTERED']);
        }

        return $this->provider->verifyBusiness($application);
    }

    private function missingOrProviderBankCheck(SupplierApplication $application): VerificationResult
    {
        if (! $application->account_number_hash || ! $application->bank_code) {
            return VerificationResult::failed('bank', $this->provider->name(), ['BANK_ACCOUNT_UNVERIFIABLE']);
        }

        return $this->provider->verifyBank($application);
    }

    private function duplicateDetectionCheck(SupplierApplication $application): ?VerificationResult
    {
        $codes = [];

        if ($application->bvn_hash) {
            $hasRejectedBvn = SupplierApplication::query()
                ->where('id', '<>', $application->id)
                ->where('bvn_hash', $application->bvn_hash)
                ->where('status', SupplierApplication::STATUS_REJECTED)
                ->exists();

            if ($hasRejectedBvn) {
                $codes[] = 'DUPLICATE_REJECTED_BVN';
            }

            $hasAnyDuplicateBvn = SupplierApplication::query()
                ->where('id', '<>', $application->id)
                ->where('bvn_hash', $application->bvn_hash)
                ->exists();

            if ($hasAnyDuplicateBvn && ! in_array('DUPLICATE_REJECTED_BVN', $codes, true)) {
                $codes[] = 'DUPLICATE_BVN';
            }
        }

        if ($application->nin_hash && SupplierApplication::query()->where('id', '<>', $application->id)->where('nin_hash', $application->nin_hash)->exists()) {
            $codes[] = 'DUPLICATE_NIN';
        }

        if ($application->account_number_hash && SupplierApplication::query()->where('id', '<>', $application->id)->where('account_number_hash', $application->account_number_hash)->exists()) {
            $codes[] = 'DUPLICATE_BANK_ACCOUNT';
        }

        foreach ($application->riskSignals()->whereIn('signal_type', ['phone', 'device', 'ip'])->get() as $signal) {
            $distinctUsers = RiskSignal::query()
                ->where('signal_type', $signal->signal_type)
                ->where('signal_hash', $signal->signal_hash)
                ->when($signal->signal_type === 'ip', fn ($query) => $query->where('created_at', '>=', now()->subDay()))
                ->distinct('user_id')
                ->count('user_id');

            if ($signal->signal_type === 'phone' && $distinctUsers >= 2) {
                $codes[] = 'DUPLICATE_PHONE';
            }
            if ($signal->signal_type === 'device' && $distinctUsers >= 2) {
                $codes[] = 'DUPLICATE_DEVICE';
            }
            if ($signal->signal_type === 'ip' && $distinctUsers >= 3) {
                $codes[] = 'DUPLICATE_IP_PATTERN';
            }
        }

        $codes = array_values(array_unique($codes));
        if ($codes === []) {
            return null;
        }

        if (in_array('DUPLICATE_REJECTED_BVN', $codes, true)) {
            return VerificationResult::failed('duplicate_detection', $this->provider->name(), $codes);
        }

        return VerificationResult::manualReview('duplicate_detection', $this->provider->name(), $codes);
    }

    private function persistDecision(SupplierApplication $application, DecisionResult $decision, string $previousStatus, ?User $reviewer, ?string $notes): void
    {
        $providerReferences = $this->providerReferences($application);

        $application->fill([
            'status' => $decision->newStatus,
            'supplier_message' => $decision->newStatus === SupplierApplication::STATUS_REJECTED
                ? SupplierApplication::GENERIC_REJECTION_MESSAGE
                : null,
            'decided_at' => now(),
        ])->save();

        VerificationDecision::query()->create([
            'supplier_application_id' => $application->id,
            'decision' => $decision->decision,
            'previous_status' => $previousStatus,
            'new_status' => $decision->newStatus,
            'provider' => (string) ($application->provider ?: $this->provider->name()),
            'provider_references' => $providerReferences,
            'triggered_checks' => $decision->triggeredChecks,
            'internal_reason_codes' => $decision->reasonCodes,
            'reviewer_id' => $reviewer?->id,
            'notes' => $notes,
        ]);

        $this->writeAudit($application, 'DECISION_RECORDED', $decision->decision, $previousStatus, $decision->newStatus, $decision->triggeredChecks, $decision->reasonCodes, $reviewer, $notes);
        $this->syncUserVerificationStatus($application);
    }

    private function manualDecision(SupplierApplication $application, User $reviewer, string $decision, string $newStatus, string $action, ?string $notes): SupplierApplication
    {
        return DB::transaction(function () use ($application, $reviewer, $decision, $newStatus, $action, $notes): SupplierApplication {
            $previousStatus = (string) $application->status;
            $decisionResult = new DecisionResult($decision, $newStatus, ['manual_review'], [$action]);

            ManualReviewNote::query()->create([
                'supplier_application_id' => $application->id,
                'reviewer_id' => $reviewer->id,
                'action' => $action,
                'note' => $notes,
            ]);

            $this->persistDecision($application, $decisionResult, $previousStatus, $reviewer, $notes);

            return $application->refresh();
        });
    }

    private function writeAudit(SupplierApplication $application, string $action, ?string $decision, ?string $previousStatus, string $newStatus, array $triggeredChecks, array $reasonCodes, ?User $reviewer, ?string $notes): void
    {
        SupplierAuditLog::query()->create([
            'supplier_application_id' => $application->id,
            'user_id' => $application->user_id,
            'action' => $action,
            'decision' => $decision,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'provider' => (string) ($application->provider ?: $this->provider->name()),
            'provider_references' => $this->providerReferences($application),
            'triggered_checks' => $triggeredChecks,
            'internal_reason_codes' => $reasonCodes,
            'reviewer_id' => $reviewer?->id,
            'notes' => $notes,
        ]);
    }

    private function providerReferenceFromResult(VerificationResult $result): ?string
    {
        $reference = $result->data['reference'] ?? null;

        if (! is_scalar($reference)) {
            return null;
        }

        $reference = trim((string) $reference);

        return $reference === '' ? null : substr($reference, 0, 191);
    }

    /**
     * @return array<int, string>
     */
    private function providerReferences(SupplierApplication $application): array
    {
        return VerificationCheck::query()
            ->where('supplier_application_id', $application->id)
            ->whereNotNull('provider_reference')
            ->pluck('provider_reference')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function syncUserVerificationStatus(SupplierApplication $application): void
    {
        $status = match ($application->status) {
            SupplierApplication::STATUS_APPROVED => 'approved',
            SupplierApplication::STATUS_REJECTED => 'rejected',
            SupplierApplication::STATUS_MANUAL_REVIEW => 'manual_review',
            SupplierApplication::STATUS_MORE_INFO_REQUIRED => 'more_info_required',
            SupplierApplication::STATUS_VERIFYING => 'verifying',
            SupplierApplication::STATUS_SUBMITTED => 'submitted',
            default => 'pending',
        };

        $payload = ['updated_at' => now()];

        if (Schema::hasColumn('users', 'kyc_status')) {
            $payload['kyc_status'] = $status;
        }

        if ($application->status === SupplierApplication::STATUS_APPROVED && Schema::hasColumn('users', 'kyc_verified_at')) {
            $payload['kyc_verified_at'] = now();
        }

        if (Schema::hasColumn('users', 'is_verified_badge')) {
            $payload['is_verified_badge'] = $application->status === SupplierApplication::STATUS_APPROVED ? 1 : 0;
        }

        DB::table('users')->where('id', $application->user_id)->update($payload);
    }

    private function syncLegacyUserProfile(SupplierApplication $application): void
    {
        $payload = [
            'updated_at' => now(),
        ];

        if ($application->business_name) {
            $payload['company'] = $application->business_name;
        }
        if ($application->state) {
            $payload['location'] = $application->state;
        }
        if ($application->business_type && Schema::hasColumn('users', 'business_category')) {
            $payload['business_category'] = $application->business_type;
        }
        if ($application->business_address && Schema::hasColumn('users', 'business_address')) {
            $payload['business_address'] = $application->business_address;
        }
        if ($application->bank_name && Schema::hasColumn('users', 'bank_name')) {
            $payload['bank_name'] = $application->bank_name;
        }
        if ($application->account_number_mask && Schema::hasColumn('users', 'account_number')) {
            $payload['account_number'] = $application->account_number_mask;
        }

        DB::table('users')->where('id', $application->user_id)->update($payload);
    }

    private function captureRequestSignals(SupplierApplication $application, Request $request): void
    {
        $this->storeRiskSignal($application, 'ip', (string) $request->ip(), SensitiveData::maskIp((string) $request->ip()), ['source' => 'request']);

        $deviceFingerprint = (string) ($request->header('X-Device-Fingerprint') ?: $request->input('device_fingerprint', ''));
        if ($deviceFingerprint !== '') {
            $this->storeRiskSignal($application, 'device', $deviceFingerprint, SensitiveData::maskToken($deviceFingerprint), ['source' => 'request']);
        }
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function storeRiskSignal(SupplierApplication $application, string $type, string $value, ?string $display, array $metadata): void
    {
        $fingerprint = SensitiveData::fingerprint($value);
        if (! $fingerprint) {
            return;
        }

        RiskSignal::query()->updateOrCreate(
            [
                'user_id' => $application->user_id,
                'signal_type' => $type,
                'signal_hash' => $fingerprint,
            ],
            [
                'supplier_application_id' => $application->id,
                'signal_display' => $display,
                'metadata' => $metadata,
                'captured_at' => now(),
            ]
        );
    }
}
