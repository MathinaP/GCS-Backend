<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentCounter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DocumentCounterController extends Controller
{
    public function index(): JsonResponse
    {
        $counters = DocumentCounter::query()
            ->orderBy('type')
            ->get();

        return response()->json(['data' => $counters]);
    }

    public function update(Request $request, DocumentCounter $documentCounter): JsonResponse
    {
        $validated = $request->validate([
            'prefix' => 'required|string|max:30',
            'last_number' => 'required|integer|min:0',
            'financial_year' => 'required|string|max:20',
        ]);

        $documentCounter->update($validated);

        return response()->json($documentCounter->fresh());
    }
}
