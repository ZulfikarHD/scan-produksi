<?php

namespace App\Models;

use Database\Factories\DailyReportItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Model data baris item PO yang dipindai dalam laporan harian.
 *
 * @property int $id
 * @property int $daily_report_id
 * @property int|null $production_order_id
 * @property string $po_number
 * @property string $product_name
 * @property string|null $specification
 * @property string|null $machine_number
 * @property int $target_quantity
 * @property int $good_quantity
 * @property string $unit
 * @property string|null $notes
 * @property Carbon|null $scanned_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read DailyReport $dailyReport
 * @property-read ProductionOrder|null $productionOrder
 */
class DailyReportItem extends Model
{
    /** @use HasFactory<DailyReportItemFactory> */
    use HasFactory;

    protected $fillable = [
        'daily_report_id',
        'production_order_id',
        'po_number',
        'product_name',
        'specification',
        'machine_number',
        'target_quantity',
        'good_quantity',
        'unit',
        'notes',
        'scanned_at',
    ];

    protected function casts(): array
    {
        return [
            'target_quantity' => 'integer',
            'good_quantity' => 'integer',
            'scanned_at' => 'datetime',
        ];
    }

    /**
     * Induk laporan harian.
     *
     * @return BelongsTo<DailyReport, $this>
     */
    public function dailyReport(): BelongsTo
    {
        return $this->belongsTo(DailyReport::class, 'daily_report_id');
    }

    /**
     * Relasi ke data master SAP Production Order jika tersedia.
     *
     * @return BelongsTo<ProductionOrder, $this>
     */
    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }
}
