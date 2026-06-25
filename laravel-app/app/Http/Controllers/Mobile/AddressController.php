<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    private const RULES = [
        'label' => ['nullable', 'string', 'max:80'],
        'contact_name' => ['nullable', 'string', 'max:120'],
        'contact_phone' => ['nullable', 'string', 'max:30'],
        'state' => ['nullable', 'string', 'max:80'],
        'lga' => ['nullable', 'string', 'max:80'],
        'address' => ['required', 'string', 'max:255'],
        'instructions' => ['nullable', 'string', 'max:1000'],
        'default_window' => ['nullable', 'string', 'max:60'],
        'is_default' => ['nullable', 'boolean'],
    ];

    public function index(Request $request)
    {
        $userId = (int) $request->user()->id;

        $addresses = Address::query()
            ->where('owner_type', 'user')
            ->where('owner_id', $userId)
            ->orderByDesc('is_default')
            ->orderBy('label')
            ->get();

        return response()->json(['addresses' => $addresses]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(self::RULES);
        $userId = (int) $request->user()->id;

        $validated['owner_type'] = 'user';
        $validated['owner_id'] = $userId;

        if (! empty($validated['is_default'])) {
            $this->clearDefault($userId);
        }

        $address = Address::query()->create($validated);

        return response()->json(['address' => $address], 201);
    }

    public function update(Request $request, int $addressId)
    {
        $userId = (int) $request->user()->id;

        $address = Address::query()
            ->where('id', $addressId)
            ->where('owner_type', 'user')
            ->where('owner_id', $userId)
            ->first();

        if (! $address) {
            return response()->json(['message' => 'Address not found.'], 404);
        }

        $validated = $request->validate(self::RULES);

        if (! empty($validated['is_default'])) {
            $this->clearDefault($userId, $addressId);
        }

        $address->update($validated);

        return response()->json(['address' => $address]);
    }

    public function destroy(Request $request, int $addressId)
    {
        $userId = (int) $request->user()->id;

        $deleted = Address::query()
            ->where('id', $addressId)
            ->where('owner_type', 'user')
            ->where('owner_id', $userId)
            ->delete();

        return response()->json(['deleted' => (bool) $deleted]);
    }

    private function clearDefault(int $userId, ?int $exceptId = null): void
    {
        Address::query()
            ->where('owner_type', 'user')
            ->where('owner_id', $userId)
            ->when($exceptId !== null, fn ($query) => $query->where('id', '<>', $exceptId))
            ->update(['is_default' => false]);
    }
}
