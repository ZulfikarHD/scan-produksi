<?php

use App\Enums\ProductionOrderStatus;
use App\Models\ProductionOrder;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;

test('production order can be instantiated via factory with proper casts', function () {
    $order = ProductionOrder::factory()->create([
        'po_number' => 'PO-2026-9999',
        'product_code' => 'PRD-999',
        'product_name' => 'Sertifikat Khusus',
        'customer_name' => 'Lembaga Negara',
        'target_quantity' => 12500,
        'order_date' => '2026-08-20',
        'delivery_deadline' => '2026-08-30',
        'status' => ProductionOrderStatus::READY,
    ]);

    expect($order->po_number)->toBe('PO-2026-9999');
    expect($order->target_quantity)->toBeInt()->toBe(12500);
    expect($order->order_date)->toBeInstanceOf(CarbonInterface::class);
    expect($order->order_date->format('Y-m-d'))->toBe('2026-08-20');
    expect($order->delivery_deadline)->toBeInstanceOf(CarbonInterface::class);
    expect($order->delivery_deadline->format('Y-m-d'))->toBe('2026-08-30');
    expect($order->status)->toBe(ProductionOrderStatus::READY);
});

test('production order belongs to a creator user and user has many production orders', function () {
    $user = User::factory()->supervisor()->create();
    $order = ProductionOrder::factory()->create([
        'created_by' => $user->id,
    ]);

    expect($order->creator)->toBeInstanceOf(User::class);
    expect($order->creator->id)->toBe($user->id);
    expect($order->user->id)->toBe($user->id);

    expect($user->productionOrders)->toHaveCount(1);
    expect($user->productionOrders->first()->id)->toBe($order->id);
});

test('search scope filters by po number, product code, product name, and customer name', function () {
    ProductionOrder::factory()->create([
        'po_number' => 'PO-2026-1111',
        'product_code' => 'PRD-ABC',
        'product_name' => 'Cetak Ijazah Nasional',
        'customer_name' => 'Kementerian Dikdasmen',
    ]);

    ProductionOrder::factory()->create([
        'po_number' => 'PO-2026-2222',
        'product_code' => 'PRD-XYZ',
        'product_name' => 'Pita Cukai Minuman',
        'customer_name' => 'Bea Cukai RI',
    ]);

    // Search by PO number
    expect(ProductionOrder::search('PO-2026-1111')->count())->toBe(1);
    expect(ProductionOrder::search('1111')->count())->toBe(1);

    // Search by Product Code
    expect(ProductionOrder::search('PRD-XYZ')->count())->toBe(1);

    // Search by Product Name
    expect(ProductionOrder::search('Ijazah')->count())->toBe(1);

    // Search by Customer Name
    expect(ProductionOrder::search('Bea Cukai')->count())->toBe(1);

    // Empty search returns all
    expect(ProductionOrder::search('')->count())->toBe(2);
    expect(ProductionOrder::search(null)->count())->toBe(2);
});

test('status scopes filter orders correctly', function () {
    ProductionOrder::factory()->ready()->create();
    ProductionOrder::factory()->inProgress()->create();
    ProductionOrder::factory()->completed()->create();
    ProductionOrder::factory()->cancelled()->create();

    expect(ProductionOrder::ready()->count())->toBe(1);
    expect(ProductionOrder::inProgress()->count())->toBe(1);
    expect(ProductionOrder::completed()->count())->toBe(1);
    expect(ProductionOrder::cancelled()->count())->toBe(1);

    // byStatus with enum
    expect(ProductionOrder::byStatus(ProductionOrderStatus::READY)->count())->toBe(1);
    // byStatus with string
    expect(ProductionOrder::byStatus('in_progress')->count())->toBe(1);
    // byStatus with null returns all
    expect(ProductionOrder::byStatus(null)->count())->toBe(4);
});

test('machine and date range scopes filter orders accurately', function () {
    ProductionOrder::factory()->create([
        'machine_number' => 'MESIN-01',
        'order_date' => '2026-08-01',
    ]);

    ProductionOrder::factory()->create([
        'machine_number' => 'MESIN-02',
        'order_date' => '2026-08-15',
    ]);

    ProductionOrder::factory()->create([
        'machine_number' => 'MESIN-01',
        'order_date' => '2026-08-25',
    ]);

    // byMachine
    expect(ProductionOrder::byMachine('MESIN-01')->count())->toBe(2);
    expect(ProductionOrder::byMachine('MESIN-02')->count())->toBe(1);
    expect(ProductionOrder::byMachine(null)->count())->toBe(3);

    // byDateRange
    expect(ProductionOrder::byDateRange('2026-08-10', '2026-08-20')->count())->toBe(1);
    expect(ProductionOrder::byDateRange('2026-08-10', null)->count())->toBe(2);
    expect(ProductionOrder::byDateRange(null, '2026-08-10')->count())->toBe(1);
    expect(ProductionOrder::byDateRange(null, null)->count())->toBe(3);
});

test('findByPoNumber and findByBarcode locate orders quickly and sanitize whitespace', function () {
    $order = ProductionOrder::factory()->create([
        'po_number' => 'PO-2026-7788',
    ]);

    $foundByPo = ProductionOrder::findByPoNumber('  PO-2026-7788  ');
    expect($foundByPo)->not->toBeNull();
    expect($foundByPo->id)->toBe($order->id);

    $foundByBarcode = ProductionOrder::findByBarcode('PO-2026-7788');
    expect($foundByBarcode)->not->toBeNull();
    expect($foundByBarcode->id)->toBe($order->id);

    expect(ProductionOrder::findByPoNumber('PO-NON-EXISTENT'))->toBeNull();
});

test('formattedTargetQuantity formats number with Indonesian thousand separator', function () {
    $order = ProductionOrder::factory()->make([
        'target_quantity' => 1250000,
    ]);

    expect($order->formattedTargetQuantity())->toBe('1.250.000');
});

test('status boolean helper methods and label/badge methods return accurate values', function () {
    $readyOrder = ProductionOrder::factory()->ready()->make();
    expect($readyOrder->isReady())->toBeTrue();
    expect($readyOrder->isInProgress())->toBeFalse();
    expect($readyOrder->isCompleted())->toBeFalse();
    expect($readyOrder->isCancelled())->toBeFalse();
    expect($readyOrder->statusLabel())->toBe('Siap Kemas');
    expect($readyOrder->statusBadgeColor())->toBe('blue');

    $inProgressOrder = ProductionOrder::factory()->inProgress()->make();
    expect($inProgressOrder->isInProgress())->toBeTrue();
    expect($inProgressOrder->statusLabel())->toBe('Sedang Diproses');
    expect($inProgressOrder->statusBadgeColor())->toBe('amber');

    $completedOrder = ProductionOrder::factory()->completed()->make();
    expect($completedOrder->isCompleted())->toBeTrue();
    expect($completedOrder->statusLabel())->toBe('Selesai');
    expect($completedOrder->statusBadgeColor())->toBe('green');

    $cancelledOrder = ProductionOrder::factory()->cancelled()->make();
    expect($cancelledOrder->isCancelled())->toBeTrue();
    expect($cancelledOrder->statusLabel())->toBe('Dibatalkan');
    expect($cancelledOrder->statusBadgeColor())->toBe('red');
});

test('po_number enforces unique constraint at database level', function () {
    ProductionOrder::factory()->create(['po_number' => 'PO-DUPLICATE-01']);

    expect(fn () => ProductionOrder::factory()->create(['po_number' => 'PO-DUPLICATE-01']))
        ->toThrow(QueryException::class);
});

test('production order handles nullable attributes gracefully', function () {
    $order = ProductionOrder::create([
        'po_number' => 'PO-2026-NULL-TEST',
        'product_name' => 'Minimal Order',
        'customer_name' => 'Klien Minimalis',
        'target_quantity' => 500,
        'order_date' => '2026-08-26',
        'status' => ProductionOrderStatus::READY,
        'product_code' => null,
        'specification' => null,
        'paper_type' => null,
        'machine_number' => null,
        'delivery_deadline' => null,
        'created_by' => null,
    ]);

    expect($order->product_code)->toBeNull();
    expect($order->specification)->toBeNull();
    expect($order->paper_type)->toBeNull();
    expect($order->machine_number)->toBeNull();
    expect($order->delivery_deadline)->toBeNull();
    expect($order->created_by)->toBeNull();
    expect($order->creator)->toBeNull();
});

test('findByPoNumber and findByBarcode return null for empty or whitespace-only inputs', function () {
    expect(ProductionOrder::findByPoNumber(''))->toBeNull();
    expect(ProductionOrder::findByPoNumber('   '))->toBeNull();
    expect(ProductionOrder::findByBarcode(''))->toBeNull();
    expect(ProductionOrder::findByBarcode('   '))->toBeNull();
});

test('production order serializes status enum correctly to array and json', function () {
    $order = ProductionOrder::factory()->ready()->create([
        'po_number' => 'PO-JSON-001',
    ]);

    $array = $order->toArray();
    expect($array['status'])->toBe('ready');

    $json = $order->toJson();
    expect($json)->toContain('"status":"ready"');
});
