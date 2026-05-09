<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentCounterSeeder extends Seeder
{
    public function run(): void
    {
        $year = (int) now()->format('Y');
        $month = (int) now()->format('n');
        $startYear = $month >= 4 ? $year : $year - 1;
        $financialYear = $startYear . '-' . substr((string) ($startYear + 1), -2);

        $counters = [
            ['type' => 'invoice',          'prefix' => 'INV', 'last_number' => 0, 'financial_year' => $financialYear],
            ['type' => 'proforma_invoice', 'prefix' => 'PRO', 'last_number' => 0, 'financial_year' => $financialYear],
            ['type' => 'purchase_order',   'prefix' => 'PO',  'last_number' => 0, 'financial_year' => $financialYear],
            ['type' => 'quotation',        'prefix' => 'QUO', 'last_number' => 0, 'financial_year' => $financialYear],
        ];

        foreach ($counters as $counter) {
            DB::table('document_counters')->updateOrInsert(
                ['type' => $counter['type']],
                array_merge($counter, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
