<?php

use App\Enums\ProductionOrderStatus;
use App\Models\ProductionOrder;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;

test('production order can be instantiated via factory with all 24 sap columns and proper casts', function () {
    $order = ProductionOrder::factory()->create([
        'po_number' => 'PO-2026-9999',
        'no_obc' => 'OBC-2026-9999',
        'perso_type' => 'Non Perso',
        'seri' => 'A',
        'product_code' => 'PRD-999',
        'product_name' => 'Sertifikat Khusus',
        'customer_name' => 'Lembaga Negara',
        'order_date' => '2026-08-20',
        'delivery_deadline' => '2026-08-30',
        'tgl_pakai_bb' => '2026-08-21',
        'tgl_cetak' => '2026-08-23',
        'tgl_verifikasi' => '2026-08-25',
        'packaging_date' => '2026-08-26',
        'target_quantity' => 12500,
        'rencet_quantity' => 12875,
        'jml_bb' => 12500,
        'jml_cd' => 375,
        'total_bb' => 12875,
        'jml_cetak' => 12875,
        'baik_verifikasi' => 12850,
        'rusak_verifikasi' => 25,
        'hcts_baik' => 0,
        'total_hcts' => 25,
        'kemas_quantity' => 12500,
        'kirim_quantity' => 12500,
        'machine_number' => 'MESIN-01',
        'status' => ProductionOrderStatus::READY,
    ]);

    expect($order->po_number)->toBe('PO-2026-9999');
    expect($order->no_obc)->toBe('OBC-2026-9999');
    expect($order->perso_type)->toBe('Non Perso');
    expect($order->seri)->toBe('A');
    expect($order->target_quantity)->toBeInt()->toBe(12500);
    expect($order->rencet_quantity)->toBeInt()->toBe(12875);
    expect($order->jml_bb)->toBeInt()->toBe(12500);
    expect($order->jml_cd)->toBeInt()->toBe(375);
    expect($order->total_bb)->toBeInt()->toBe(12875);
    expect($order->jml_cetak)->toBeInt()->toBe(12875);
    expect($order->baik_verifikasi)->toBeInt()->toBe(12850);
    expect($order->rusak_verifikasi)->toBeInt()->toBe(25);
    expect($order->hcts_baik)->toBeInt()->toBe(0);
    expect($order->total_hcts)->toBeInt()->toBe(25);
    expect($order->kemas_quantity)->toBeInt()->toBe(12500);
    expect($order->kirim_quantity)->toBeInt()->toBe(12500);

    expect($order->order_date)->toBeInstanceOf(CarbonInterface::class);
    expect($order->order_date->format('Y-m-d'))->toBe('2026-08-20');
    expect($order->delivery_deadline)->toBeInstanceOf(CarbonInterface::class);
    expect($order->delivery_deadline->format('Y-m-d'))->toBe('2026-08-30');
    expect($order->tgl_pakai_bb)->toBeInstanceOf(CarbonInterface::class);
    expect($order->tgl_pakai_bb->format('Y-m-d'))->toBe('2026-08-21');
    expect($order->tgl_cetak)->toBeInstanceOf(CarbonInterface::class);
    expect($order->tgl_cetak->format('Y-m-d'))->toBe('2026-08-23');
    expect($order->tgl_verifikasi)->toBeInstanceOf(CarbonInterface::class);
    expect($order->tgl_verifikasi->format('Y-m-d'))->toBe('2026-08-25');
    expect($order->packaging_date)->toBeInstanceOf(CarbonInterface::class);
    expect($order->packaging_date->format('Y-m-d'))->toBe('2026-08-26');

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

test('direct eloquent query filters by po number, obc number, product code, product name, and customer name', function () {
    ProductionOrder::factory()->create([
        'po_number' => 'PO-2026-1111',
        'no_obc' => 'OBC-2026-1111',
        'product_code' => 'PRD-ABC',
        'product_name' => 'Cetak Ijazah Nasional',
        'customer_name' => 'Kementerian Dikdasmen',
    ]);

    ProductionOrder::factory()->create([
        'po_number' => 'PO-2026-2222',
        'no_obc' => 'OBC-2026-2222',
        'product_code' => 'PRD-XYZ',
        'product_name' => 'Pita Cukai Minuman',
        'customer_name' => 'Bea Cukai RI',
    ]);

    // Search by PO number
    expect(ProductionOrder::where('po_number', 'like', '%PO-2026-1111%')->count())->toBe(1);
    expect(ProductionOrder::where('po_number', 'like', '%1111%')->count())->toBe(1);

    // Search by OBC number
    expect(ProductionOrder::where('no_obc', 'OBC-2026-2222')->count())->toBe(1);

    // Search by Product Code
    expect(ProductionOrder::where('product_code', 'PRD-XYZ')->count())->toBe(1);

    // Search by Product Name
    expect(ProductionOrder::where('product_name', 'like', '%Ijazah%')->count())->toBe(1);

    // Search by Customer Name
    expect(ProductionOrder::where('customer_name', 'like', '%Bea Cukai%')->count())->toBe(1);
});

test('status enum returns accurate labels and badge colors', function () {
    $readyStatus = ProductionOrderStatus::READY;
    expect($readyStatus->label())->toBe('Siap Kemas');
    expect($readyStatus->badgeColor())->toBe('blue');

    $inProgressStatus = ProductionOrderStatus::IN_PROGRESS;
    expect($inProgressStatus->label())->toBe('Sedang Diproses');
    expect($inProgressStatus->badgeColor())->toBe('amber');

    $completedStatus = ProductionOrderStatus::COMPLETED;
    expect($completedStatus->label())->toBe('Selesai');
    expect($completedStatus->badgeColor())->toBe('green');

    $cancelledStatus = ProductionOrderStatus::CANCELLED;
    expect($cancelledStatus->label())->toBe('Dibatalkan');
    expect($cancelledStatus->badgeColor())->toBe('red');
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
        'target_quantity' => 500,
        'order_date' => '2026-08-26',
        'status' => ProductionOrderStatus::READY,
        'no_obc' => null,
        'perso_type' => null,
        'seri' => null,
        'product_code' => null,
        'customer_name' => null,
        'specification' => null,
        'paper_type' => null,
        'machine_number' => null,
        'delivery_deadline' => null,
        'tgl_pakai_bb' => null,
        'tgl_cetak' => null,
        'tgl_verifikasi' => null,
        'packaging_date' => null,
        'created_by' => null,
    ]);

    expect($order->no_obc)->toBeNull();
    expect($order->perso_type)->toBeNull();
    expect($order->seri)->toBeNull();
    expect($order->product_code)->toBeNull();
    expect($order->customer_name)->toBeNull();
    expect($order->specification)->toBeNull();
    expect($order->paper_type)->toBeNull();
    expect($order->machine_number)->toBeNull();
    expect($order->delivery_deadline)->toBeNull();
    expect($order->tgl_pakai_bb)->toBeNull();
    expect($order->tgl_cetak)->toBeNull();
    expect($order->tgl_verifikasi)->toBeNull();
    expect($order->packaging_date)->toBeNull();
    expect($order->created_by)->toBeNull();
    expect($order->creator)->toBeNull();
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
