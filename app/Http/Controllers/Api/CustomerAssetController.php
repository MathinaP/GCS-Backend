<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerAssetResource;
use App\Models\CustomerAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerAssetController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $assets = CustomerAsset::with('customer')
            ->where('is_active', true)
            ->when($request->q, fn ($q) => $q->where('fabrication_number', 'ilike', "%{$request->q}%")
                ->orWhere('compressor_model', 'ilike', "%{$request->q}%")
                ->orWhere('compressor_make', 'ilike', "%{$request->q}%"))
            ->orderBy('fabrication_number')
            ->paginate(50);

        return CustomerAssetResource::collection($assets);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id'             => 'required|exists:customers,id',
            'fabrication_number'      => 'required|string|max:255|unique:customer_assets,fabrication_number',
            'compressor_model'        => 'required|string|max:255',
            'service_dealer'          => 'nullable|string|max:255',
            'product'                 => 'required|string|in:ENCAP,EG SERIES,RECPIT,HORIZON SERIES,GLOBAL SERIES',
            'compressor_make'         => 'required|string|in:ELGI,ATLASCOPCO,CP COMPRESSOR,KEAISER,IR COMPRESSOR,CHINA COMPRESSOR,AIRWA',
            'service_engineer'        => 'nullable|string|max:255',
            'contact_person_name'     => 'required|string|max:255',
            'contact_person_mail'     => 'required|email|max:255',
            'contact_person_number'   => 'required|string|max:20',
            'alternate_person_name'   => 'nullable|string|max:255',
            'alternate_person_mail'   => 'nullable|email|max:255',
            'alternate_person_number' => 'nullable|string|max:20',
            'hours_meter_reading'     => 'required|numeric|min:0',
            'hmr_date'                => 'required|date',
            'amc'                     => 'required|boolean',
            'amc_start_date'          => 'nullable|date|required_if:amc,true',
            'amc_end_date'            => 'nullable|date|required_if:amc,true|after_or_equal:amc_start_date',
        ]);

        if (empty($validated['service_dealer'])) {
            $validated['service_dealer'] = 'Go Care Solutions';
        }

        $asset = CustomerAsset::create($validated);
        $asset->load('customer');

        return response()->json(new CustomerAssetResource($asset), 201);
    }

    public function show(CustomerAsset $customerAsset): CustomerAssetResource
    {
        $customerAsset->load('customer');
        return new CustomerAssetResource($customerAsset);
    }

    public function update(Request $request, CustomerAsset $customerAsset): CustomerAssetResource
    {
        $validated = $request->validate([
            'customer_id'             => 'sometimes|required|exists:customers,id',
            'fabrication_number'      => 'sometimes|required|string|max:255|unique:customer_assets,fabrication_number,' . $customerAsset->id,
            'compressor_model'        => 'sometimes|required|string|max:255',
            'service_dealer'          => 'nullable|string|max:255',
            'product'                 => 'sometimes|required|string|in:ENCAP,EG SERIES,RECPIT,HORIZON SERIES,GLOBAL SERIES',
            'compressor_make'         => 'sometimes|required|string|in:ELGI,ATLASCOPCO,CP COMPRESSOR,KEAISER,IR COMPRESSOR,CHINA COMPRESSOR,AIRWA',
            'service_engineer'        => 'nullable|string|max:255',
            'contact_person_name'     => 'sometimes|required|string|max:255',
            'contact_person_mail'     => 'sometimes|required|email|max:255',
            'contact_person_number'   => 'sometimes|required|string|max:20',
            'alternate_person_name'   => 'nullable|string|max:255',
            'alternate_person_mail'   => 'nullable|email|max:255',
            'alternate_person_number' => 'nullable|string|max:20',
            'hours_meter_reading'     => 'sometimes|required|numeric|min:0',
            'hmr_date'                => 'sometimes|required|date',
            'amc'                     => 'sometimes|required|boolean',
            'amc_start_date'          => 'nullable|date',
            'amc_end_date'            => 'nullable|date|after_or_equal:amc_start_date',
        ]);

        $customerAsset->update($validated);
        $customerAsset->load('customer');
        return new CustomerAssetResource($customerAsset);
    }

    public function destroy(CustomerAsset $customerAsset): JsonResponse
    {
        $customerAsset->update(['is_active' => false]);
        return response()->json(null, 204);
    }
}
