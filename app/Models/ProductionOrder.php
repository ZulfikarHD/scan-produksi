<?php

namespace App\Models;

use App\Enums\ProductionOrderStatus;
use Database\Factories\ProductionOrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $po_number
 * @property string|null $no_obc
 * @property string|null $perso_type
 * @property string|null $seri
 * @property string|null $product_code
 * @property string $product_name
 * @property string|null $customer_name
 * @property string|null $specification
 * @property string|null $paper_type
 * @property string|null $machine_number
 * @property Carbon $order_date
 * @property Carbon|null $delivery_deadline
 * @property Carbon|null $tgl_pakai_bb
 * @property Carbon|null $tgl_cetak
 * @property Carbon|null $tgl_verifikasi
 * @property Carbon|null $packaging_date
 * @property int $target_quantity
 * @property int $rencet_quantity
 * @property int $jml_bb
 * @property int $jml_cd
 * @property int $total_bb
 * @property int $jml_cetak
 * @property int $baik_verifikasi
 * @property int $rusak_verifikasi
 * @property int $hcts_baik
 * @property int $total_hcts
 * @property int $kemas_quantity
 * @property int $kirim_quantity
 * @property string $unit
 * @property ProductionOrderStatus $status
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $creator
 * @property-read User|null $user
 */
#[Fillable([
    'po_number',
    'no_obc',
    'perso_type',
    'seri',
    'product_code',
    'product_name',
    'customer_name',
    'specification',
    'paper_type',
    'machine_number',
    'order_date',
    'delivery_deadline',
    'tgl_pakai_bb',
    'tgl_cetak',
    'tgl_verifikasi',
    'packaging_date',
    'target_quantity',
    'rencet_quantity',
    'jml_bb',
    'jml_cd',
    'total_bb',
    'jml_cetak',
    'baik_verifikasi',
    'rusak_verifikasi',
    'hcts_baik',
    'total_hcts',
    'kemas_quantity',
    'kirim_quantity',
    'unit',
    'status',
    'created_by',
])]
class ProductionOrder extends Model
{
    /** @use HasFactory<ProductionOrderFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'po_number',
        'no_obc',
        'perso_type',
        'seri',
        'product_code',
        'product_name',
        'customer_name',
        'specification',
        'paper_type',
        'machine_number',
        'order_date',
        'delivery_deadline',
        'tgl_pakai_bb',
        'tgl_cetak',
        'tgl_verifikasi',
        'packaging_date',
        'target_quantity',
        'rencet_quantity',
        'jml_bb',
        'jml_cd',
        'total_bb',
        'jml_cetak',
        'baik_verifikasi',
        'rusak_verifikasi',
        'hcts_baik',
        'total_hcts',
        'kemas_quantity',
        'kirim_quantity',
        'unit',
        'status',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'delivery_deadline' => 'date',
            'tgl_pakai_bb' => 'date',
            'tgl_cetak' => 'date',
            'tgl_verifikasi' => 'date',
            'packaging_date' => 'date',
            'target_quantity' => 'integer',
            'rencet_quantity' => 'integer',
            'jml_bb' => 'integer',
            'jml_cd' => 'integer',
            'total_bb' => 'integer',
            'jml_cetak' => 'integer',
            'baik_verifikasi' => 'integer',
            'rusak_verifikasi' => 'integer',
            'hcts_baik' => 'integer',
            'total_hcts' => 'integer',
            'kemas_quantity' => 'integer',
            'kirim_quantity' => 'integer',
            'status' => ProductionOrderStatus::class,
            'created_by' => 'integer',
        ];
    }

    /**
     * The user (supervisor/operator) who created or imported this order.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Alias for creator relationship.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->creator();
    }

    /**
     * Scope query to search by keyword across PO number, OBC number, product code, product name, or customer name.
     *
     * @param  Builder<ProductionOrder>  $query
     * @return Builder<ProductionOrder>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $q) use ($term) {
            $q->where('po_number', 'like', "%{$term}%")
                ->orWhere('no_obc', 'like', "%{$term}%")
                ->orWhere('product_code', 'like', "%{$term}%")
                ->orWhere('product_name', 'like', "%{$term}%")
                ->orWhere('customer_name', 'like', "%{$term}%");
        });
    }

    /**
     * Scope query by status (enum or string).
     *
     * @param  Builder<ProductionOrder>  $query
     * @return Builder<ProductionOrder>
     */
    public function scopeByStatus(Builder $query, ProductionOrderStatus|string|null $status): Builder
    {
        if (blank($status)) {
            return $query;
        }

        $statusValue = $status instanceof ProductionOrderStatus ? $status->value : $status;

        return $query->where('status', $statusValue);
    }

    /**
     * Scope query by machine number (WORK CENTER).
     *
     * @param  Builder<ProductionOrder>  $query
     * @return Builder<ProductionOrder>
     */
    public function scopeByMachine(Builder $query, ?string $machineNumber): Builder
    {
        if (blank($machineNumber)) {
            return $query;
        }

        return $query->where('machine_number', trim($machineNumber));
    }

    /**
     * Scope query by OBC number.
     *
     * @param  Builder<ProductionOrder>  $query
     * @return Builder<ProductionOrder>
     */
    public function scopeByObc(Builder $query, ?string $noObc): Builder
    {
        if (blank($noObc)) {
            return $query;
        }

        return $query->where('no_obc', trim($noObc));
    }

    /**
     * Scope query by date range (order_date).
     *
     * @param  Builder<ProductionOrder>  $query
     * @return Builder<ProductionOrder>
     */
    public function scopeByDateRange(Builder $query, ?string $startDate, ?string $endDate): Builder
    {
        if (filled($startDate) && filled($endDate)) {
            return $query->whereBetween('order_date', [$startDate, $endDate]);
        }

        if (filled($startDate)) {
            return $query->where('order_date', '>=', $startDate);
        }

        if (filled($endDate)) {
            return $query->where('order_date', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Scope query for ready status.
     *
     * @param  Builder<ProductionOrder>  $query
     * @return Builder<ProductionOrder>
     */
    public function scopeReady(Builder $query): Builder
    {
        return $query->where('status', ProductionOrderStatus::READY);
    }

    /**
     * Scope query for in_progress status.
     *
     * @param  Builder<ProductionOrder>  $query
     * @return Builder<ProductionOrder>
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', ProductionOrderStatus::IN_PROGRESS);
    }

    /**
     * Scope query for completed status.
     *
     * @param  Builder<ProductionOrder>  $query
     * @return Builder<ProductionOrder>
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', ProductionOrderStatus::COMPLETED);
    }

    /**
     * Scope query for cancelled status.
     *
     * @param  Builder<ProductionOrder>  $query
     * @return Builder<ProductionOrder>
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', ProductionOrderStatus::CANCELLED);
    }

    /**
     * Fast indexed lookup by PO number (< 20ms for barcode scanner).
     */
    public static function findByPoNumber(string $poNumber): ?self
    {
        $poNumber = trim($poNumber);
        if ($poNumber === '') {
            return null;
        }

        return static::where('po_number', $poNumber)->first();
    }

    /**
     * Fast indexed lookup by barcode identifier (sanitizes input).
     */
    public static function findByBarcode(string $barcode): ?self
    {
        return static::findByPoNumber($barcode);
    }

    /**
     * Format target quantity with thousand separator (e.g., "10.000").
     */
    public function formattedTargetQuantity(): string
    {
        return number_format($this->target_quantity, 0, ',', '.');
    }

    /**
     * Format kemas quantity with thousand separator.
     */
    public function formattedKemasQuantity(): string
    {
        return number_format($this->kemas_quantity, 0, ',', '.');
    }

    /**
     * Get the human-readable Indonesian label for order status.
     */
    public function statusLabel(): string
    {
        return $this->status->label();
    }

    /**
     * Get the badge color corresponding to current status.
     */
    public function statusBadgeColor(): string
    {
        return $this->status->badgeColor();
    }

    /**
     * Check if the order is ready for packaging.
     */
    public function isReady(): bool
    {
        return $this->status === ProductionOrderStatus::READY;
    }

    /**
     * Check if the order is currently in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === ProductionOrderStatus::IN_PROGRESS;
    }

    /**
     * Check if the order is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === ProductionOrderStatus::COMPLETED;
    }

    /**
     * Check if the order is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === ProductionOrderStatus::CANCELLED;
    }
}
