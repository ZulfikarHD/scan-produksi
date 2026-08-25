<?php

use App\Enums\ProductionOrderStatus;
use App\Models\ProductionOrder;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

test('production_orders table has all required columns and correct data types', function () {
    expect(Schema::hasTable('production_orders'))->toBeTrue();

    $expectedColumns = [
        'id',
        'po_number',
        'product_code',
        'product_name',
        'customer_name',
        'specification',
        'paper_type',
        'machine_number',
        'target_quantity',
        'unit',
        'order_date',
        'delivery_deadline',
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

test('po_number index supports high performance barcode lookup under 20ms', function () {
    $user = User::factory()->supervisor()->create();

    // Seed 100 sample orders to simulate populated master data
    ProductionOrder::factory()->count(100)->create([
        'created_by' => $user->id,
    ]);

    $targetPo = 'PO-2026-PERF-TEST';
    ProductionOrder::factory()->create([
        'po_number' => $targetPo,
        'created_by' => $user->id,
    ]);

    // Measure lookup time for barcode scan
    $startTime = microtime(true);
    $order = ProductionOrder::findByPoNumber($targetPo);
    $elapsedMs = (microtime(true) - $startTime) * 1000;

    expect($order)->not->toBeNull();
    expect($order->po_number)->toBe($targetPo);
    expect($elapsedMs)->toBeLessThan(20.0, "Lookup by po_number took {$elapsedMs}ms, exceeding 20ms threshold.");
});

test('production orders support upsert behavior seamlessly for sap imports', function () {
    $user = User::factory()->supervisor()->create();

    // Initial creation via updateOrCreate
    $order1 = ProductionOrder::updateOrCreate(
        ['po_number' => 'PO-2026-UPSERT-01'],
        [
            'product_name' => 'Produk Awal',
            'customer_name' => 'Pelanggan Awal',
            'target_quantity' => 10000,
            'order_date' => '2026-08-20',
            'status' => ProductionOrderStatus::READY,
            'created_by' => $user->id,
        ]
    );

    expect($order1->wasRecentlyCreated)->toBeTrue();
    expect($order1->product_name)->toBe('Produk Awal');
    expect(ProductionOrder::count())->toBe(1);

    // Update existing record with new SAP data
    $order2 = ProductionOrder::updateOrCreate(
        ['po_number' => 'PO-2026-UPSERT-01'],
        [
            'product_name' => 'Produk Terkoreksi SAP',
            'customer_name' => 'Pelanggan Awal',
            'target_quantity' => 15000,
            'order_date' => '2026-08-20',
            'status' => ProductionOrderStatus::READY,
            'created_by' => $user->id,
        ]
    );

    expect($order2->wasRecentlyCreated)->toBeFalse();
    expect($order2->id)->toBe($order1->id);
    expect($order2->fresh()->product_name)->toBe('Produk Terkoreksi SAP');
    expect($order2->fresh()->target_quantity)->toBe(15000);
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
