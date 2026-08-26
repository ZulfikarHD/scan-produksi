<?php

namespace App\Models;

use App\Enums\ProductSeries;
use App\Enums\ReportStatus;
use App\Enums\WorkShift;
use Database\Factories\DailyReportFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Model data laporan produksi harian per sesi pita cukai & shift.
 *
 * @property int $id
 * @property string $report_code
 * @property ProductSeries $product_series
 * @property Carbon $report_date
 * @property WorkShift $shift
 * @property int $user_id
 * @property int|null $supervisor_id
 * @property Carbon|null $verified_at
 * @property int $total_items_count
 * @property int $total_good_quantity
 * @property int $total_reject_quantity
 * @property string|null $notes
 * @property ReportStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read User|null $supervisor
 * @property-read Collection<int, DailyReportItem> $items
 */
class DailyReport extends Model
{
    /** @use HasFactory<DailyReportFactory> */
    use HasFactory;

    protected $fillable = [
        'report_code',
        'product_series',
        'report_date',
        'shift',
        'user_id',
        'supervisor_id',
        'verified_at',
        'total_items_count',
        'total_good_quantity',
        'total_reject_quantity',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'verified_at' => 'datetime',
            'product_series' => ProductSeries::class,
            'shift' => WorkShift::class,
            'status' => ReportStatus::class,
            'total_items_count' => 'integer',
            'total_good_quantity' => 'integer',
            'total_reject_quantity' => 'integer',
        ];
    }

    /**
     * Petugas pembuat laporan.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Supervisor yang memverifikasi laporan.
     *
     * @return BelongsTo<User, $this>
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Daftar item PO yang dipindai dalam sesi laporan ini.
     *
     * @return HasMany<DailyReportItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(DailyReportItem::class, 'daily_report_id');
    }
}
