<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MaterialResource;
use App\Models\Material;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MaterialController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $materials = Material::query()
            ->when($request->q, fn($q) => $q->where('material_name', 'ilike', "%{$request->q}%")
                ->orWhere('hsn_code', 'ilike', "%{$request->q}%"))
            ->when($request->has('active'), function ($q) use ($request) {
                $val = filter_var($request->active, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
                $q->whereRaw("\"is_active\" = $val");
            })
            ->orderBy('material_name')
            ->paginate(500);

        return MaterialResource::collection($materials);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'material_name'       => 'required|string|max:255|unique:materials,material_name',
            'unit_of_measurement' => 'required|string|max:50',
            'hsn_code'            => 'nullable|string|max:20',
            'default_rate'        => 'nullable|numeric|min:0',
            'gst_rate'            => 'nullable|numeric|in:0,5,12,18,28',
            'is_active'           => 'boolean',
        ]);

        return response()->json(new MaterialResource(Material::create($validated)), 201);
    }

    public function show(Material $material): MaterialResource
    {
        return new MaterialResource($material);
    }

    public function update(Request $request, Material $material): MaterialResource
    {
        $validated = $request->validate([
            'material_name'       => 'sometimes|required|string|max:255|unique:materials,material_name,' . $material->id,
            'unit_of_measurement' => 'sometimes|required|string|max:50',
            'hsn_code'            => 'nullable|string|max:20',
            'default_rate'        => 'nullable|numeric|min:0',
            'gst_rate'            => 'nullable|numeric|in:0,5,12,18,28',
            'is_active'           => 'boolean',
        ]);

        $material->update($validated);
        return new MaterialResource($material);
    }

    public function destroy(Material $material): JsonResponse
    {
        $material->delete();
        return response()->json(null, 204);
    }
}
