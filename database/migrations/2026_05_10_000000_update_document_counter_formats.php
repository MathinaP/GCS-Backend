<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $counters = [
            ['type' => 'invoice',          'prefix' => 'INV-GCS', 'last_number' => 21, 'financial_year' => '26-27'],
            ['type' => 'proforma_invoice', 'prefix' => 'PI-GCS',  'last_number' => 0,  'financial_year' => '26-27'],
            ['type' => 'purchase_order',   'prefix' => 'PO-GCS',  'last_number' => 0,  'financial_year' => '26-27'],
            ['type' => 'quotation',        'prefix' => 'QTN-GCS', 'last_number' => 0,  'financial_year' => '26-27'],
        ];

        foreach ($counters as $counter) {
            DB::table('document_counters')->updateOrInsert(
                ['type' => $counter['type']],
                array_merge($counter, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }

    public function down(): void
    {
        $now = now();
        $counters = [
            ['type' => 'invoice',          'prefix' => 'INV', 'last_number' => 0, 'financial_year' => '2026-27'],
            ['type' => 'proforma_invoice', 'prefix' => 'PRO', 'last_number' => 0, 'financial_year' => '2026-27'],
            ['type' => 'purchase_order',   'prefix' => 'PO',  'last_number' => 0, 'financial_year' => '2026-27'],
            ['type' => 'quotation',        'prefix' => 'QUO', 'last_number' => 0, 'financial_year' => '2026-27'],
        ];

        foreach ($counters as $counter) {
            DB::table('document_counters')->updateOrInsert(
                ['type' => $counter['type']],
                array_merge($counter, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }
};
