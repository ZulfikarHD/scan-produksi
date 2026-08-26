<?php

namespace Database\Factories;

use App\Enums\ProductSeries;
use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Enums\WorkShift;
use App\Models\DailyReport;
use App\Models\User;
use App\Services\ReportCodeGenerator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyReport>
 */
class DailyReportFactory extends Factory
{
    protected $model = DailyReport::class;

    public function definition(): array
    {
        $series = fake()->randomElement(ProductSeries::cases());
        $date = fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d');
        $shift = fake()->randomElement(WorkShift::cases());
        $code = ReportCodeGenerator::generateWithShift($series, $date, $shift);

        return [
            'report_code' => $code.'-'.fake()->unique()->randomNumber(4),
            'product_series' => $series,
            'report_date' => $date,
            'shift' => $shift,
            'user_id' => User::factory(),
            'supervisor_id' => null,
            'total_items_count' => 0,
            'total_good_quantity' => 0,
            'total_reject_quantity' => 0,
            'notes' => fake()->optional()->sentence(),
            'status' => ReportStatus::DRAFT,
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReportStatus::SUBMITTED,
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReportStatus::VERIFIED,
            'supervisor_id' => User::factory()->state(['role' => UserRole::SUPERVISOR]),
            'verified_at' => now(),
        ]);
    }
}
