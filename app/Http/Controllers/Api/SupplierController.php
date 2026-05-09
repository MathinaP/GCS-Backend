<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SupplierController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $suppliers = Supplier::query()
            ->when($request->q, fn($q) => $q->where('name', 'ilike', "%{$request->q}%")
                ->orWhere('mobile', 'ilike', "%{$request->q}%")
                ->orWhere('gstin', 'ilike', "%{$request->q}%"))
            ->orderBy('name')
            ->paginate(50);

        return SupplierResource::collection($suppliers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'address'        => 'required|string',
            'mobile'         => 'required|string|max:20',
            'email'          => 'nullable|email|max:255',
            'pan_number'     => 'nullable|string|max:20',
            'gstin'          => 'nullable|string|max:20',
            'state_name'     => 'nullable|string|max:100',
            'state_code'     => 'nullable|string|max:10',
        ]);

        return response()->json(new SupplierResource(Supplier::create($validated)), 201);
    }

    public function show(Supplier $supplier): SupplierResource
    {
        return new SupplierResource($supplier);
    }

    public function update(Request $request, Supplier $supplier): SupplierResource
    {
        $validated = $request->validate([
            'name'           => 'sometimes|required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'address'        => 'sometimes|required|string',
            'mobile'         => 'sometimes|required|string|max:20',
            'email'          => 'nullable|email|max:255',
            'pan_number'     => 'nullable|string|max:20',
            'gstin'          => 'nullable|string|max:20',
            'state_name'     => 'nullable|string|max:100',
            'state_code'     => 'nullable|string|max:10',
        ]);

        $supplier->update($validated);
        return new SupplierResource($supplier);
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        $supplier->delete();
        return response()->json(null, 204);
    }
}
