<?php

use App\Enums\ProductionOrderStatus;
use App\Livewire\MasterData\UpdateDatabase;
use App\Models\ProductionOrder;
use App\Models\User;
use Livewire\Livewire;

test('supervisor can view paginated master data production orders list (US-2.5)', function () {
    $supervisor = User::factory()->supervisor()->create();

    ProductionOrder::factory()->count(20)->create([
        'created_by' => $supervisor->id,
    ]);

    Livewire::actingAs($supervisor)
        ->test(UpdateDatabase::class)
        ->set('activeTab', 'orders')
        ->assertSee('Penjelajah Master Data Order')
        ->assertViewHas('orders', fn ($orders) => $orders->total() === 20 && $orders->count() === 15);
});

test('master data orders can be searched in real-time by po number, obc, product name, or customer', function () {
    $supervisor = User::factory()->supervisor()->create();

    $order1 = ProductionOrder::factory()->create([
        'po_number' => 'PO-ALPHA-1001',
        'no_obc' => 'OBC-9988',
        'product_name' => 'Sertifikat Tanah Hologram',
        'customer_name' => 'Kementerian ATR BPN',
        'created_by' => $supervisor->id,
    ]);

    $order2 = ProductionOrder::factory()->create([
        'po_number' => 'PO-BETA-2002',
        'no_obc' => 'OBC-1122',
        'product_name' => 'Pita Cukai Hasil Tembakau',
        'customer_name' => 'Direktorat Jenderal Bea Cukai',
        'created_by' => $supervisor->id,
    ]);

    $component = Livewire::actingAs($supervisor)
        ->test(UpdateDatabase::class)
        ->set('activeTab', 'orders');

    // Search by PO
    $component->set('search', 'ALPHA')
        ->assertSee('PO-ALPHA-1001')
        ->assertDontSee('PO-BETA-2002');

    // Search by OBC
    $component->set('search', '1122')
        ->assertSee('PO-BETA-2002')
        ->assertDontSee('PO-ALPHA-1001');

    // Search by Product Name
    $component->set('search', 'Tanah')
        ->assertSee('PO-ALPHA-1001')
        ->assertDontSee('PO-BETA-2002');

    // Search by Customer
    $component->set('search', 'Bea Cukai')
        ->assertSee('PO-BETA-2002')
        ->assertDontSee('PO-ALPHA-1001');
});

test('master data orders can be filtered by status, machine number, customer, and date range', function () {
    $supervisor = User::factory()->supervisor()->create();

    $orderReady = ProductionOrder::factory()->create([
        'po_number' => 'PO-FILTER-READY',
        'machine_number' => 'MESIN-01',
        'customer_name' => 'Kemenkes',
        'status' => ProductionOrderStatus::READY,
        'order_date' => '2026-08-01',
        'created_by' => $supervisor->id,
    ]);

    $orderProgress = ProductionOrder::factory()->create([
        'po_number' => 'PO-FILTER-PROG',
        'machine_number' => 'MESIN-02',
        'customer_name' => 'Kemendagri',
        'status' => ProductionOrderStatus::IN_PROGRESS,
        'order_date' => '2026-08-15',
        'created_by' => $supervisor->id,
    ]);

    $component = Livewire::actingAs($supervisor)
        ->test(UpdateDatabase::class)
        ->set('activeTab', 'orders');

    // Filter by status
    $component->set('statusFilter', 'in_progress')
        ->assertSee('PO-FILTER-PROG')
        ->assertDontSee('PO-FILTER-READY');

    // Filter by machine
    $component->set('statusFilter', '')
        ->set('machineFilter', 'MESIN-01')
        ->assertSee('PO-FILTER-READY')
        ->assertDontSee('PO-FILTER-PROG');

    // Filter by customer
    $component->set('machineFilter', '')
        ->set('customerFilter', 'Kemendagri')
        ->assertSee('PO-FILTER-PROG')
        ->assertDontSee('PO-FILTER-READY');

    // Filter by date range
    $component->set('customerFilter', '')
        ->set('startDateFilter', '2026-08-10')
        ->set('endDateFilter', '2026-08-20')
        ->assertSee('PO-FILTER-PROG')
        ->assertDontSee('PO-FILTER-READY');

    // Reset filters
    $component->call('resetOrderFilters')
        ->assertSet('search', '')
        ->assertSet('statusFilter', '')
        ->assertSet('machineFilter', '')
        ->assertSet('customerFilter', '')
        ->assertSet('startDateFilter', '')
        ->assertSet('endDateFilter', '')
        ->assertSee('PO-FILTER-READY')
        ->assertSee('PO-FILTER-PROG');
});

test('supervisor can create a manual emergency production order with validation (US-2.5)', function () {
    $supervisor = User::factory()->supervisor()->create();

    Livewire::actingAs($supervisor)
        ->test(UpdateDatabase::class)
        ->set('activeTab', 'orders')
        ->call('openCreateOrderModal')
        ->assertSet('isEditingOrder', false)
        ->assertSet('order_status', 'ready')
        ->set('order_po_number', '')
        ->set('order_product_name', '')
        ->set('order_target_quantity', 0)
        ->call('saveOrder')
        ->assertHasErrors(['order_po_number', 'order_product_name', 'order_target_quantity'])
        ->set('order_po_number', 'PO-MANUAL-8888')
        ->set('order_no_obc', 'OBC-8888')
        ->set('order_product_code', 'MAT-8888')
        ->set('order_product_name', 'Ijazah Percetakan Negara')
        ->set('order_customer_name', 'Kemendikbudristek')
        ->set('order_machine_number', 'MESIN-05')
        ->set('order_target_quantity', 3500)
        ->set('order_rencet_quantity', 3600)
        ->set('order_unit', 'lembar')
        ->set('order_paper_type', 'Kertas Sekuriti 120 GSM')
        ->set('order_specification', 'Guilloche border, microtext, invisible ink')
        ->set('order_date', '2026-08-26')
        ->set('order_delivery_deadline', '2026-09-10')
        ->set('order_status', 'ready')
        ->call('saveOrder')
        ->assertHasNoErrors();

    expect(ProductionOrder::count())->toBe(1);
    $created = ProductionOrder::findByPoNumber('PO-MANUAL-8888');
    expect($created)->not->toBeNull();
    expect($created->product_name)->toBe('Ijazah Percetakan Negara');
    expect($created->customer_name)->toBe('Kemendikbudristek');
    expect($created->target_quantity)->toBe(3500);
    expect($created->rencet_quantity)->toBe(3600);
    expect($created->machine_number)->toBe('MESIN-05');
    expect($created->created_by)->toBe($supervisor->id);
    expect($created->status)->toBe(ProductionOrderStatus::READY);
});

test('supervisor can edit an existing production order details (US-2.5)', function () {
    $supervisor = User::factory()->supervisor()->create();

    $order = ProductionOrder::factory()->create([
        'po_number' => 'PO-EDIT-9999',
        'product_name' => 'Nama Sebelum Edit',
        'target_quantity' => 1000,
        'machine_number' => 'MESIN-01',
        'status' => ProductionOrderStatus::READY,
        'created_by' => $supervisor->id,
    ]);

    Livewire::actingAs($supervisor)
        ->test(UpdateDatabase::class)
        ->set('activeTab', 'orders')
        ->call('openEditOrderModal', $order->id)
        ->assertSet('isEditingOrder', true)
        ->assertSet('editingOrderId', $order->id)
        ->assertSet('order_po_number', 'PO-EDIT-9999')
        ->assertSet('order_product_name', 'Nama Sebelum Edit')
        ->set('order_product_name', 'Nama Sesudah Edit')
        ->set('order_target_quantity', 2500)
        ->set('order_machine_number', 'MESIN-09')
        ->set('order_status', 'in_progress')
        ->call('saveOrder')
        ->assertHasNoErrors();

    $order->refresh();
    expect($order->product_name)->toBe('Nama Sesudah Edit');
    expect($order->target_quantity)->toBe(2500);
    expect($order->machine_number)->toBe('MESIN-09');
    expect($order->status)->toBe(ProductionOrderStatus::IN_PROGRESS);
});

test('supervisor can view complete technical specification detail modal (US-2.5)', function () {
    $supervisor = User::factory()->supervisor()->create();

    $order = ProductionOrder::factory()->create([
        'po_number' => 'PO-DETAIL-7777',
        'no_obc' => 'OBC-7777',
        'product_name' => 'Sertifikat Uji Kompetensi',
        'customer_name' => 'BNSP',
        'specification' => 'Watermark logo Garuda, barcode 128, anti-copy background',
        'paper_type' => 'Secuprint 90 GSM',
        'machine_number' => 'MESIN-03',
        'target_quantity' => 5000,
        'kemas_quantity' => 2500,
        'status' => ProductionOrderStatus::IN_PROGRESS,
        'created_by' => $supervisor->id,
    ]);

    Livewire::actingAs($supervisor)
        ->test(UpdateDatabase::class)
        ->set('activeTab', 'orders')
        ->call('showOrderDetail', $order->id)
        ->assertSet('selectedOrderId', $order->id)
        ->assertSee('PO-DETAIL-7777')
        ->assertSee('Sertifikat Uji Kompetensi')
        ->assertSee('BNSP')
        ->assertSee('Watermark logo Garuda')
        ->assertSee('Secuprint 90 GSM')
        ->assertSee('MESIN-03')
        ->assertSee('5.000')
        ->assertSee('2.500');
});

test('supervisor can delete a ready production order with confirmation modal (US-2.5)', function () {
    $supervisor = User::factory()->supervisor()->create();

    $order = ProductionOrder::factory()->create([
        'po_number' => 'PO-DELETE-1111',
        'status' => ProductionOrderStatus::READY,
        'created_by' => $supervisor->id,
    ]);

    Livewire::actingAs($supervisor)
        ->test(UpdateDatabase::class)
        ->set('activeTab', 'orders')
        ->call('confirmDeleteOrder', $order->id)
        ->assertSet('confirmingDeleteOrderId', $order->id)
        ->assertSet('confirmingDeleteOrderPo', 'PO-DELETE-1111')
        ->call('deleteOrder')
        ->assertSet('confirmingDeleteOrderId', null);

    expect(ProductionOrder::find($order->id))->toBeNull();
});

test('supervisor cannot delete an in_progress production order to protect packaging flow (US-2.5)', function () {
    $supervisor = User::factory()->supervisor()->create();

    $order = ProductionOrder::factory()->create([
        'po_number' => 'PO-PROTECTED-2222',
        'status' => ProductionOrderStatus::IN_PROGRESS,
        'created_by' => $supervisor->id,
    ]);

    Livewire::actingAs($supervisor)
        ->test(UpdateDatabase::class)
        ->set('activeTab', 'orders')
        ->call('confirmDeleteOrder', $order->id)
        ->call('deleteOrder');

    expect(ProductionOrder::find($order->id))->not->toBeNull();
});

test('supervisor can export master data orders to excel (.xlsx) file (US-2.5)', function () {
    $supervisor = User::factory()->supervisor()->create();

    ProductionOrder::factory()->create([
        'po_number' => 'PO-EXPORT-01',
        'product_name' => 'Produk Ekspor A',
        'created_by' => $supervisor->id,
    ]);

    ProductionOrder::factory()->create([
        'po_number' => 'PO-EXPORT-02',
        'product_name' => 'Produk Ekspor B',
        'created_by' => $supervisor->id,
    ]);

    $test = Livewire::actingAs($supervisor)
        ->test(UpdateDatabase::class)
        ->set('activeTab', 'orders')
        ->call('exportOrders')
        ->assertFileDownloaded();

    $downloadName = data_get($test->effects, 'download.name');
    expect($downloadName)->toStartWith('master_data_order_produksi_')->toEndWith('.xlsx');
});
