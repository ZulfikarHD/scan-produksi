<?php

namespace Database\Factories;

use App\Models\SapImportLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SapImportLog>
 */
class SapImportLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<SapImportLog>
     */
    protected $model = SapImportLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'file_name' => 'sap_export_'.fake()->numerify('#####').'.xlsx',
            'file_path' => 'sap-imports/'.fake()->uuid().'.xlsx',
            'total_rows' => 50,
            'imported_count' => 45,
            'updated_count' => 5,
            'failed_count' => 0,
            'status' => 'success',
            'error_log' => null,
        ];
    }

    /**
     * Indicate that the import was partial.
     */
    public function partial(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'partial',
            'failed_count' => 2,
            'error_log' => [
                'Baris 14: Nomor PO tidak boleh kosong.',
                'Baris 20: Target Qty harus berupa angka bulat.',
            ],
        ]);
    }

    /**
     * Indicate that the import failed completely.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'imported_count' => 0,
            'updated_count' => 0,
            'failed_count' => 50,
            'error_log' => [
                'Format berkas spreadsheet rusak atau tidak dapat dibaca.',
            ],
        ]);
    }
}
