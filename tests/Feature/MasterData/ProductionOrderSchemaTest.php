<?php

use App\Enums\ProductionOrderStatus;
use App\Models\ProductionOrder;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

test('production_orders table has all 24 sap columns and required system attributes', function () {
    expect(Schema::hasTable('production_orders'))->toBeTrue();

    $expectedColumns = [
        'id',
        // 24 Kolom Header SAP
        'po_number', // NO PO
        'no_obc', // No OBC
        'perso_type', // Perso / non Perso
        'seri', // SERI
        'order_date', // TGL OBC
        'delivery_deadline', // TGL JTempo
        'tgl_pakai_bb', // TGL_PAKAI_BB
        'tgl_cetak', // TGL_CETAK
        'tgl_verifikasi', // TGL_VERIFIKASI
        'packaging_date', // Tanggal Kemas
        'target_quantity', // QTY PESAN
        'rencet_quantity', // RENCET
        'jml_bb', // JML_BB (LK)
        'jml_cd', // JLM_CD (LK)
        'total_bb', // TOTAL_BB (LK)
        'jml_cetak', // JML_CETAK
        'baik_verifikasi', // BAIK_VERIFIKASI
        'rusak_verifikasi', // RUSAK_VERIFIKASI
        'hcts_baik', // HASIL BAIK DIJADIKAN HCTS
        'total_hcts', // TOTAL_HCTS
        'kemas_quantity', // KEMAS
        'kirim_quantity', // KIRIM
        'machine_number', // WORK CENTER
        'product_name', // Material
        'product_code', // Material Code

        // Atribut Tambahan Sistem
        'customer_name',
        'specification',
        'paper_type',
        'unit',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];

    foreach ($expectedColumns as $column) {
        expect(Schema::hasColumn('production_orders', $column))
            ->toBeTrue("Column {$column} is missing from production_orders table.");
    }
});

test('production order model accurately stores and parses real sap output record', function () {
    $supervisor = User::factory()->supervisor()->create();

    // Data riil ekspor SAP:
    // 3000189509 | ACE211474 | P | 1 | 12/18/2023 | 1/10/2024 | 1/6/2024 | 1/9/2024 | 1/10/2024 | 1/11/2024 | 2000 | 2.068 | - | 2.068 | 2.068 | 2.068 | 2.037 | 31 | 37 | 68 | 2.000 | - | HDR-1001 | 931110001-24
    $order = ProductionOrder::create([
        'po_number' => '3000189509',
        'no_obc' => 'ACE211474',
        'perso_type' => 'P',
        'seri' => '1',
        'order_date' => Carbon::createFromFormat('m/d/Y', '12/18/2023')->format('Y-m-d'),
        'delivery_deadline' => Carbon::createFromFormat('n/j/Y', '1/10/2024')->format('Y-m-d'),
        'tgl_pakai_bb' => Carbon::createFromFormat('n/j/Y', '1/6/2024')->format('Y-m-d'),
        'tgl_cetak' => Carbon::createFromFormat('n/j/Y', '1/9/2024')->format('Y-m-d'),
        'tgl_verifikasi' => Carbon::createFromFormat('n/j/Y', '1/10/2024')->format('Y-m-d'),
        'packaging_date' => Carbon::createFromFormat('n/j/Y', '1/11/2024')->format('Y-m-d'),
        'target_quantity' => 2000,
        'rencet_quantity' => 2068,
        'jml_bb' => 0, // Nilai '-' pada SAP dikonversi ke 0
        'jml_cd' => 2068,
        'total_bb' => 2068,
        'jml_cetak' => 2068,
        'baik_verifikasi' => 2037,
        'rusak_verifikasi' => 31,
        'hcts_baik' => 37,
        'total_hcts' => 68,
        'kemas_quantity' => 2000,
        'kirim_quantity' => 0,
        'machine_number' => 'HDR-1001',
        'product_name' => '931110001-24',
        'product_code' => '931110001-24',
        'status' => ProductionOrderStatus::READY,
        'created_by' => $supervisor->id,
    ]);

    expect($order->po_number)->toBe('3000189509');
    expect($order->no_obc)->toBe('ACE211474');
    expect($order->perso_type)->toBe('P');
    expect($order->seri)->toBe('1');
    expect($order->order_date->format('Y-m-d'))->toBe('2023-12-18');
    expect($order->delivery_deadline->format('Y-m-d'))->toBe('2024-01-10');
    expect($order->tgl_pakai_bb->format('Y-m-d'))->toBe('2024-01-06');
    expect($order->tgl_cetak->format('Y-m-d'))->toBe('2024-01-09');
    expect($order->tgl_verifikasi->format('Y-m-d'))->toBe('2024-01-10');
    expect($order->packaging_date->format('Y-m-d'))->toBe('2024-01-11');
    expect($order->target_quantity)->toBe(2000);
    expect($order->rencet_quantity)->toBe(2068);
    expect($order->jml_bb)->toBe(0);
    expect($order->jml_cd)->toBe(2068);
    expect($order->total_bb)->toBe(2068);
    expect($order->jml_cetak)->toBe(2068);
    expect($order->baik_verifikasi)->toBe(2037);
    expect($order->rusak_verifikasi)->toBe(31);
    expect($order->hcts_baik)->toBe(37);
    expect($order->total_hcts)->toBe(68);
    expect($order->kemas_quantity)->toBe(2000);
    expect($order->kirim_quantity)->toBe(0);
    expect($order->machine_number)->toBe('HDR-1001');
    expect($order->product_name)->toBe('931110001-24');
    expect($order->product_code)->toBe('931110001-24');

    // Barcode scanner lookup on real PO number
    $lookup = ProductionOrder::where('po_number', '3000189509')->first();
    expect($lookup)->not->toBeNull();
    expect($lookup->id)->toBe($order->id);
    expect(number_format($lookup->target_quantity, 0, ',', '.'))->toBe('2.000');
    expect(number_format($lookup->kemas_quantity, 0, ',', '.'))->toBe('2.000');
});

test('po_number index supports high performance barcode lookup under 20ms', function () {
    $user = User::factory()->supervisor()->create();

    // Seed 100 sample orders to simulate populated master data
    ProductionOrder::factory()->count(100)->create([
        'created_by' => $user->id,
    ]);

    $targetPo = '3000189509';
    ProductionOrder::factory()->create([
        'po_number' => $targetPo,
        'created_by' => $user->id,
    ]);

    // Measure lookup time for barcode scan
    $startTime = microtime(true);
    $order = ProductionOrder::where('po_number', $targetPo)->first();
    $elapsedMs = (microtime(true) - $startTime) * 1000;

    expect($order)->not->toBeNull();
    expect($order->po_number)->toBe($targetPo);
    expect($elapsedMs)->toBeLessThan(20.0, "Lookup by po_number took {$elapsedMs}ms, exceeding 20ms threshold.");
});

test('production orders support upsert behavior seamlessly for sap imports with all sap metrics', function () {
    $user = User::factory()->supervisor()->create();

    // Initial creation via updateOrCreate
    $order1 = ProductionOrder::updateOrCreate(
        ['po_number' => '3000189509'],
        [
            'no_obc' => 'ACE211474',
            'perso_type' => 'P',
            'seri' => '1',
            'product_code' => '931110001-24',
            'product_name' => '931110001-24',
            'customer_name' => 'Kementerian Keuangan RI',
            'target_quantity' => 2000,
            'rencet_quantity' => 2068,
            'jml_bb' => 0,
            'jml_cd' => 2068,
            'total_bb' => 2068,
            'order_date' => '2023-12-18',
            'status' => ProductionOrderStatus::READY,
            'created_by' => $user->id,
        ]
    );

    expect($order1->wasRecentlyCreated)->toBeTrue();
    expect($order1->product_name)->toBe('931110001-24');
    expect($order1->rencet_quantity)->toBe(2068);
    expect(ProductionOrder::count())->toBe(1);

    // Update existing record with new SAP data
    $order2 = ProductionOrder::updateOrCreate(
        ['po_number' => '3000189509'],
        [
            'no_obc' => 'ACE211474',
            'perso_type' => 'P',
            'seri' => '1',
            'product_code' => '931110001-24',
            'product_name' => '931110001-24 - Terkoreksi SAP',
            'customer_name' => 'Kementerian Keuangan RI',
            'target_quantity' => 2000,
            'rencet_quantity' => 2068,
            'jml_bb' => 0,
            'jml_cd' => 2068,
            'total_bb' => 2068,
            'order_date' => '2023-12-18',
            'status' => ProductionOrderStatus::READY,
            'created_by' => $user->id,
        ]
    );

    expect($order2->wasRecentlyCreated)->toBeFalse();
    expect($order2->id)->toBe($order1->id);
    expect($order2->fresh()->product_name)->toBe('931110001-24 - Terkoreksi SAP');
    expect($order2->fresh()->target_quantity)->toBe(2000);
    expect($order2->fresh()->rencet_quantity)->toBe(2068);
    expect(ProductionOrder::count())->toBe(1);
});

test('deleting creator user sets created_by to null without cascading deletion of order', function () {
    $user = User::factory()->supervisor()->create();
    $order = ProductionOrder::factory()->create(['created_by' => $user->id]);

    expect($order->created_by)->toBe($user->id);

    $user->delete();

    expect($order->fresh()->created_by)->toBeNull();
    expect($order->fresh())->not->toBeNull();
});
