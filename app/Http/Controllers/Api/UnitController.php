<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UnitResource;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UnitController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $units = Unit::query()
            ->when($request->q, fn($q) => $q->where('name', 'ilike', "%{$request->q}%"))
            ->when($request->has('active'), fn($q) => $q->where('is_active', filter_var($request->active, FILTER_VALIDATE_BOOLEAN)))
            ->orderBy('name')
            ->paginate(100);

        return UnitResource::collection($units);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:50|unique:units',
            'is_active' => 'boolean',
        ]);

        return response()->json(new UnitResource(Unit::create($validated)), 201);
    }

    public function show(Unit $unit): UnitResource
    {
        return new UnitResource($unit);
    }

    public function update(Request $request, Unit $unit): UnitResource
    {
        $validated = $request->validate([
            'name'      => 'sometimes|required|string|max:50|unique:units,name,' . $unit->id,
            'is_active' => 'boolean',
        ]);

        $unit->update($validated);
        return new UnitResource($unit);
    }

    public function destroy(Unit $unit): JsonResponse
    {
        $unit->delete();
        return response()->json(null, 204);
    }
}
