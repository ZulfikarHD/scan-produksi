<?php

namespace Database\Factories;

use App\Enums\ProductionOrderStatus;
use App\Models\ProductionOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductionOrder>
 */
class ProductionOrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<ProductionOrder>
     */
    protected $model = ProductionOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $products = [
            ['code' => 'PRD-101', 'name' => 'Sertifikat Sekuriti Hologram A4'],
            ['code' => 'PRD-102', 'name' => 'Ijazah Kelulusan Berpengaman'],
            ['code' => 'PRD-103', 'name' => 'Materai Tempel Seri 2026'],
            ['code' => 'PRD-104', 'name' => 'Pita Cukai Tembakau Golongan A'],
            ['code' => 'PRD-105', 'name' => 'Paspor Elektronik RI 48 Halaman'],
            ['code' => 'PRD-106', 'name' => 'KTP-el Blank Card Pengaman'],
            ['code' => 'PRD-107', 'name' => 'Buku Pemilik Kendaraan Bermotor (BPKB)'],
            ['code' => 'PRD-108', 'name' => 'Surat Tanda Nomor Kendaraan (STNK)'],
        ];

        $customers = [
            'Kementerian Keuangan RI',
            'Bank Indonesia',
            'Kementerian Dalam Negeri',
            'Kementerian Pendidikan Dasar dan Menengah',
            'Otoritas Jasa Keuangan (OJK)',
            'Kepolisian Negara Republik Indonesia (Korlantas)',
            'Direktorat Jenderal Bea dan Cukai',
        ];

        $paperTypes = [
            'Security Paper 120gsm Watermarked',
            'Synthetic Holographic Paper 150gsm',
            'Cotton Security Paper 90gsm',
            'Thermal Security Board 200gsm',
        ];

        $machines = ['MESIN-01', 'MESIN-02', 'MESIN-03', 'MESIN-04', 'MESIN-05'];
        $selectedProduct = fake()->randomElement($products);

        $targetQty = fake()->randomElement([10000, 25000, 50000, 100000]);
        $inschietRate = 0.03; // 3% inschiet toleransi
        $cadangan = (int) ($targetQty * $inschietRate);
        $totalBb = $targetQty + $cadangan;
        $rencet = $totalBb;
        $jmlCetak = $totalBb;
        $rusakVerif = (int) ($targetQty * 0.005);
        $baikVerif = $jmlCetak - $rusakVerif;
        $hctsBaik = 0;
        $kemasQty = $targetQty;
        $kirimQty = $targetQty;

        $orderDate = fake()->dateTimeBetween('-1 month', 'now');
        $deliveryDeadline = (clone $orderDate)->modify('+14 days');

        return [
            // 24 Kolom Header SAP
            'po_number' => 'PO-2026-'.fake()->unique()->numberBetween(1000, 9999), // NO PO
            'no_obc' => 'OBC-2026-'.fake()->numberBetween(10000, 99999), // No OBC
            'perso_type' => fake()->randomElement(['Perso', 'Non Perso']), // Perso / non Perso
            'seri' => fake()->randomElement(['A', 'B', 'C', 'SERI-2026']), // SERI
            'order_date' => $orderDate->format('Y-m-d'), // TGL OBC
            'delivery_deadline' => $deliveryDeadline->format('Y-m-d'), // TGL JTempo
            'tgl_pakai_bb' => (clone $orderDate)->modify('+1 day')->format('Y-m-d'), // TGL_PAKAI_BB
            'tgl_cetak' => (clone $orderDate)->modify('+3 days')->format('Y-m-d'), // TGL_CETAK
            'tgl_verifikasi' => (clone $orderDate)->modify('+5 days')->format('Y-m-d'), // TGL_VERIFIKASI
            'packaging_date' => (clone $orderDate)->modify('+6 days')->format('Y-m-d'), // Tanggal Kemas
            'target_quantity' => $targetQty, // QTY PESAN
            'rencet_quantity' => $rencet, // RENCET
            'jml_bb' => $targetQty, // JML_BB (LK)
            'jml_cd' => $cadangan, // JLM_CD (LK)
            'total_bb' => $totalBb, // TOTAL_BB (LK)
            'jml_cetak' => $jmlCetak, // JML_CETAK
            'baik_verifikasi' => $baikVerif, // BAIK_VERIFIKASI
            'rusak_verifikasi' => $rusakVerif, // RUSAK_VERIFIKASI
            'hcts_baik' => $hctsBaik, // HASIL BAIK DIJADIKAN HCTS
            'total_hcts' => $rusakVerif, // TOTAL_HCTS
            'kemas_quantity' => $kemasQty, // KEMAS
            'kirim_quantity' => $kirimQty, // KIRIM
            'machine_number' => fake()->randomElement($machines), // WORK CENTER
            'product_name' => $selectedProduct['name'], // Material
            'product_code' => $selectedProduct['code'], // Material Code

            // Properti Tambahan & Relasi
            'customer_name' => fake()->randomElement($customers),
            'specification' => 'Kertas sekuriti berfitur pengaman serat tampak & tak tampak (UV fluorescent), hologram foil strip, dan cetak mikro.',
            'paper_type' => fake()->randomElement($paperTypes),
            'unit' => 'lembar',
            'status' => ProductionOrderStatus::READY,
            'created_by' => User::factory()->supervisor(),
        ];
    }

    /**
     * Indicate that the order is ready for packaging.
     */
    public function ready(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductionOrderStatus::READY,
        ]);
    }

    /**
     * Indicate that the order is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductionOrderStatus::IN_PROGRESS,
        ]);
    }

    /**
     * Indicate that the order is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductionOrderStatus::COMPLETED,
        ]);
    }

    /**
     * Indicate that the order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductionOrderStatus::CANCELLED,
        ]);
    }

    /**
     * Set specific machine number (WORK CENTER).
     */
    public function withMachine(string $machine): static
    {
        return $this->state(fn (array $attributes) => [
            'machine_number' => $machine,
        ]);
    }

    /**
     * Set specific customer name.
     */
    public function forCustomer(string $customer): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_name' => $customer,
        ]);
    }
}
