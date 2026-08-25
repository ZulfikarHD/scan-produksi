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
            'Sertifikat Sekuriti Hologram A4',
            'Ijazah Kelulusan Berpengaman',
            'Materai Tempel Seri 2026',
            'Pita Cukai Tembakau',
            'KTP-el Blank Card',
            'Paspor Elektronik RI 48 Halaman',
            'Buku Pemilik Kendaraan Bermotor (BPKB)',
            'Surat Tanda Nomor Kendaraan (STNK)',
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

        return [
            'po_number' => 'PO-2026-'.fake()->unique()->numberBetween(1000, 9999),
            'product_code' => 'PRD-'.fake()->numberBetween(100, 999),
            'product_name' => fake()->randomElement($products),
            'customer_name' => fake()->randomElement($customers),
            'specification' => 'Kertas sekuriti berfitur pengaman serat tampak & tak tampak (UV fluorescent), hologram foil strip, dan cetak mikro.',
            'paper_type' => fake()->randomElement($paperTypes),
            'machine_number' => fake()->randomElement($machines),
            'target_quantity' => fake()->randomElement([5000, 10000, 20000, 50000, 100000]),
            'unit' => 'lembar',
            'order_date' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'delivery_deadline' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
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
     * Set specific machine number.
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
