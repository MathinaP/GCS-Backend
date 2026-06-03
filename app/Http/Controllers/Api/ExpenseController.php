<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    const CATEGORIES = [
        'fuel_travel', 'salary', 'rent_utilities',
        'tools_equipment', 'material', 'food', 'other',
    ];

    public function index(Request $request): JsonResponse
    {
        $expenses = Expense::query()
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->when($request->from,     fn($q) => $q->where('date', '>=', $request->from))
            ->when($request->to,       fn($q) => $q->where('date', '<=', $request->to))
            ->when($request->search,   fn($q) => $q->where('description', 'ilike', "%{$request->search}%"))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json($expenses);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date'        => 'required|date',
            'category'    => 'required|in:' . implode(',', self::CATEGORIES),
            'description' => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0.01',
            'notes'       => 'nullable|string',
        ]);

        return response()->json(Expense::create($validated), 201);
    }

    public function update(Request $request, Expense $expense): JsonResponse
    {
        $validated = $request->validate([
            'date'        => 'required|date',
            'category'    => 'required|in:' . implode(',', self::CATEGORIES),
            'description' => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0.01',
            'notes'       => 'nullable|string',
        ]);

        $expense->update($validated);
        return response()->json($expense);
    }

    public function destroy(Expense $expense): JsonResponse
    {
        $expense->delete();
        return response()->json(null, 204);
    }

    public function summary(Request $request): JsonResponse
    {
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->endOfMonth()->toDateString();

        // Total revenue from all invoices in period
        $revenue = DB::table('documents')
            ->whereNull('deleted_at')
            ->where('type', 'invoice')
            ->whereBetween('date', [$from, $to])
            ->sum('grand_total');

        // Total expenses in period
        $totalExpenses = Expense::whereBetween('date', [$from, $to])->sum('amount');

        // Expenses grouped by category
        $byCategory = Expense::whereBetween('date', [$from, $to])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByRaw('SUM(amount) DESC')
            ->get();

        // Monthly revenue
        $monthlyRevenue = DB::table('documents')
            ->whereNull('deleted_at')
            ->where('type', 'invoice')
            ->whereBetween('date', [$from, $to])
            ->selectRaw("TO_CHAR(DATE_TRUNC('month', date), 'YYYY-MM') as month, SUM(grand_total) as revenue")
            ->groupByRaw("DATE_TRUNC('month', date)")
            ->get()
            ->keyBy('month');

        // Monthly expenses
        $monthlyExpenses = Expense::whereBetween('date', [$from, $to])
            ->selectRaw("TO_CHAR(DATE_TRUNC('month', date), 'YYYY-MM') as month, SUM(amount) as expenses")
            ->groupByRaw("DATE_TRUNC('month', date)")
            ->get()
            ->keyBy('month');

        // Merge into unified monthly list
        $allMonths = collect(array_unique(array_merge(
            $monthlyRevenue->keys()->toArray(),
            $monthlyExpenses->keys()->toArray()
        )))->sort()->values();

        $monthly = $allMonths->map(fn($m) => [
            'month'    => $m,
            'revenue'  => (float) ($monthlyRevenue->get($m)?->revenue  ?? 0),
            'expenses' => (float) ($monthlyExpenses->get($m)?->expenses ?? 0),
            'profit'   => (float) ($monthlyRevenue->get($m)?->revenue  ?? 0)
                        - (float) ($monthlyExpenses->get($m)?->expenses ?? 0),
        ]);

        return response()->json([
            'from'        => $from,
            'to'          => $to,
            'revenue'     => (float) $revenue,
            'expenses'    => (float) $totalExpenses,
            'profit'      => (float) $revenue - (float) $totalExpenses,
            'by_category' => $byCategory,
            'monthly'     => $monthly,
        ]);
    }
}
