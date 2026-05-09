<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $customers = Customer::query()
            ->when($request->q, fn($q) => $q->where('name', 'ilike', "%{$request->q}%")
                ->orWhere('mobile', 'ilike', "%{$request->q}%")
                ->orWhere('gstin', 'ilike', "%{$request->q}%"))
            ->orderBy('name')
            ->paginate(50);

        return CustomerResource::collection($customers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'address'    => 'required|string',
            'mobile'     => 'required|string|max:20',
            'email'      => 'nullable|email|max:255',
            'pan_number' => 'nullable|string|max:20',
            'gstin'      => 'nullable|string|max:20',
            'state_name' => 'nullable|string|max:100',
            'state_code' => 'nullable|string|max:10',
        ]);

        return response()->json(new CustomerResource(Customer::create($validated)), 201);
    }

    public function show(Customer $customer): CustomerResource
    {
        return new CustomerResource($customer);
    }

    public function update(Request $request, Customer $customer): CustomerResource
    {
        $validated = $request->validate([
            'name'       => 'sometimes|required|string|max:255',
            'address'    => 'sometimes|required|string',
            'mobile'     => 'sometimes|required|string|max:20',
            'email'      => 'nullable|email|max:255',
            'pan_number' => 'nullable|string|max:20',
            'gstin'      => 'nullable|string|max:20',
            'state_name' => 'nullable|string|max:100',
            'state_code' => 'nullable|string|max:10',
        ]);

        $customer->update($validated);
        return new CustomerResource($customer);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();
        return response()->json(null, 204);
    }
}
