<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $start = now()->startOfMonth()->toDateString();
        $end   = now()->endOfMonth()->toDateString();

        $rows = Document::selectRaw("type, COUNT(*) as cnt, COALESCE(SUM(grand_total), 0) as total")
            ->whereBetween('date', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->whereNull('deleted_at')
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        $result = [];
        foreach (['invoice', 'proforma_invoice', 'purchase_order', 'quotation'] as $type) {
            $row = $rows->get($type);
            $result[$type] = [
                'count' => $row ? (int) $row->cnt   : 0,
                'total' => $row ? (float) $row->total : 0.0,
            ];
        }

        return response()->json($result);
    }
}
