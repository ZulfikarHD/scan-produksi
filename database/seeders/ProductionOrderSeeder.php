<?php

namespace Database\Seeders;

use App\Enums\ProductionOrderStatus;
use App\Models\ProductionOrder;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductionOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $supervisor = User::where('username', 'supervisor')->first()
            ?? User::factory()->supervisor()->create(['username' => 'supervisor']);

        $sampleOrders = [
            [
                'po_number' => 'PO-2026-8891',
                'product_code' => 'PRD-101',
                'product_name' => 'Sertifikat Sekuriti Hologram A4',
                'customer_name' => 'Kementerian Keuangan RI',
                'specification' => 'Kertas sekuriti 120 gsm, watermark Garuda, strip hologram 2D/3D, cetak mikroteks dan tinta pendar UV.',
                'paper_type' => 'Security Paper 120gsm Watermarked',
                'machine_number' => 'MESIN-01',
                'target_quantity' => 25000,
                'unit' => 'lembar',
                'order_date' => now()->subDays(3)->toDateString(),
                'delivery_deadline' => now()->addDays(10)->toDateString(),
                'status' => ProductionOrderStatus::READY,
                'created_by' => $supervisor->id,
            ],
            [
                'po_number' => 'PO-2026-8892',
                'product_code' => 'PRD-102',
                'product_name' => 'Ijazah Kelulusan Berpengaman',
                'customer_name' => 'Kementerian Pendidikan Dasar dan Menengah',
                'specification' => 'Kertas sekuriti 150 gsm berpendar serat dwiwarna, anti-copy guilloche pattern, emboss stempel.',
                'paper_type' => 'Security Paper 150gsm',
                'machine_number' => 'MESIN-02',
                'target_quantity' => 50000,
                'unit' => 'lembar',
                'order_date' => now()->subDays(2)->toDateString(),
                'delivery_deadline' => now()->addDays(14)->toDateString(),
                'status' => ProductionOrderStatus::IN_PROGRESS,
                'created_by' => $supervisor->id,
            ],
            [
                'po_number' => 'PO-2026-8893',
                'product_code' => 'PRD-103',
                'product_name' => 'Materai Tempel Seri 2026',
                'customer_name' => 'Direktorat Jenderal Pajak',
                'specification' => 'Kertas cetak materai sekuriti berpori khusus, perforasi bintang sekuriti, tinta thermo-chromic.',
                'paper_type' => 'Adhesive Security Paper 90gsm',
                'machine_number' => 'MESIN-03',
                'target_quantity' => 100000,
                'unit' => 'lembar',
                'order_date' => now()->subDays(5)->toDateString(),
                'delivery_deadline' => now()->addDays(7)->toDateString(),
                'status' => ProductionOrderStatus::IN_PROGRESS,
                'created_by' => $supervisor->id,
            ],
            [
                'po_number' => 'PO-2026-8894',
                'product_code' => 'PRD-104',
                'product_name' => 'Pita Cukai Tembakau Golongan A',
                'customer_name' => 'Direktorat Jenderal Bea dan Cukai',
                'specification' => 'Kertas berperekat sekuriti, benang pengaman sintetis teranyam, mikroteks BC-RI beresolusi tinggi.',
                'paper_type' => 'Cukai Security Paper 80gsm',
                'machine_number' => 'MESIN-01',
                'target_quantity' => 200000,
                'unit' => 'rim',
                'order_date' => now()->subDays(10)->toDateString(),
                'delivery_deadline' => now()->subDays(1)->toDateString(),
                'status' => ProductionOrderStatus::COMPLETED,
                'created_by' => $supervisor->id,
            ],
            [
                'po_number' => 'PO-2026-8895',
                'product_code' => 'PRD-105',
                'product_name' => 'Paspor Elektronik RI 48 Halaman',
                'customer_name' => 'Direktorat Jenderal Imigrasi',
                'specification' => 'Buku paspor e-cover chip RFID terintegrasi, benang jahit sekuriti UV fluorescent, intaglio print.',
                'paper_type' => 'Security Passport Booklet Paper',
                'machine_number' => 'MESIN-04',
                'target_quantity' => 15000,
                'unit' => 'buku',
                'order_date' => now()->subDay()->toDateString(),
                'delivery_deadline' => now()->addDays(20)->toDateString(),
                'status' => ProductionOrderStatus::READY,
                'created_by' => $supervisor->id,
            ],
        ];

        foreach ($sampleOrders as $order) {
            ProductionOrder::updateOrCreate(
                ['po_number' => $order['po_number']],
                $order
            );
        }
    }
}
