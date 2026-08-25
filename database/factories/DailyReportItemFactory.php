<?php

namespace Database\Factories;

use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\ProductionOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyReportItem>
 */
class DailyReportItemFactory extends Factory
{
    protected $model = DailyReportItem::class;

    public function definition(): array
    {
        $targetQty = fake()->numberBetween(5000, 50000);

        return [
            'daily_report_id' => DailyReport::factory(),
            'production_order_id' => null,
            'po_number' => 'PO-'.fake()->unique()->numerify('2026-####'),
            'product_name' => fake()->randomElement([
                'Pita Cukai Hasil Tembakau Seri I',
                'Pita Cukai MMEA Golongan B',
                'Pita Cukai HPTL Cerutu',
                'Pita Cukai Rokok Elektrik Cair',
            ]),
            'specification' => 'Kertas Sekuriti Berpendar UV, Watermark Kemenkeu',
            'machine_number' => 'M-'.fake()->numberBetween(1, 10),
            'target_quantity' => $targetQty,
            'good_quantity' => $targetQty,
            'unit' => 'lembar',
            'notes' => null,
            'scanned_at' => now(),
        ];
    }

    public function withProductionOrder(): static
    {
        return $this->state(function (array $attributes) {
            $order = ProductionOrder::factory()->create();

            return [
                'production_order_id' => $order->id,
                'po_number' => $order->po_number,
                'product_name' => $order->product_name,
                'specification' => $order->specification,
                'machine_number' => $order->machine_number,
                'target_quantity' => $order->target_quantity,
                'good_quantity' => $order->target_quantity,
                'unit' => $order->unit,
            ];
        });
    }
}
