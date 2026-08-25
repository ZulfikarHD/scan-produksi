<?php

namespace App\Models;

use App\Enums\ProductionOrderStatus;
use Database\Factories\ProductionOrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Model data master order produksi (sinkronisasi dari berkas SAP).
 *
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
class ProductionOrder extends Model
{
    /** @use HasFactory<ProductionOrderFactory> */
    use HasFactory;

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
     * Akun Supervisor atau Petugas yang mengimpor/membuat order ini.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Alias relasi creator untuk kemudahan akses $order->user.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->creator();
    }

    /**
     * Pencarian cepat berdasarkan nomor PO, nomor OBC, kode produk, nama produk, atau pelanggan.
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
     * Filter query berdasarkan status order.
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
     * Filter query berdasarkan nomor mesin (Work Center cetak).
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
     * Filter query berdasarkan nomor OBC.
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
     * Filter query berdasarkan nama pelanggan.
     *
     * @param  Builder<ProductionOrder>  $query
     * @return Builder<ProductionOrder>
     */
    public function scopeByCustomer(Builder $query, ?string $customerName): Builder
    {
        if (blank($customerName)) {
            return $query;
        }

        return $query->where('customer_name', 'like', '%'.trim($customerName).'%');
    }

    /**
     * Filter query berdasarkan rentang tanggal order.
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
     * Shortcut filter order yang siap dikemas.
     *
     * @param  Builder<ProductionOrder>  $query
     * @return Builder<ProductionOrder>
     */
    public function scopeReady(Builder $query): Builder
    {
        return $query->where('status', ProductionOrderStatus::READY);
    }

    /**
     * Shortcut filter order yang sedang berjalan.
     *
     * @param  Builder<ProductionOrder>  $query
     * @return Builder<ProductionOrder>
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', ProductionOrderStatus::IN_PROGRESS);
    }

    /**
     * Shortcut filter order yang telah selesai.
     *
     * @param  Builder<ProductionOrder>  $query
     * @return Builder<ProductionOrder>
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', ProductionOrderStatus::COMPLETED);
    }

    /**
     * Shortcut filter order yang dibatalkan.
     *
     * @param  Builder<ProductionOrder>  $query
     * @return Builder<ProductionOrder>
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', ProductionOrderStatus::CANCELLED);
    }

    /**
     * Pencarian indexed cepat berdasarkan nomor PO (dioptimalkan untuk barcode scanner di bawah 20ms).
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
     * Pencarian berdasarkan barcode (nomor PO sebagai identifier barcode).
     */
    public static function findByBarcode(string $barcode): ?self
    {
        return static::findByPoNumber($barcode);
    }

    /**
     * Format angka target kuantitas dengan pemisah ribuan titik (contoh: 10.000).
     */
    public function formattedTargetQuantity(): string
    {
        return number_format($this->target_quantity, 0, ',', '.');
    }

    /**
     * Format angka rencet kuantitas dengan pemisah ribuan titik.
     */
    public function formattedRencetQuantity(): string
    {
        return number_format($this->rencet_quantity, 0, ',', '.');
    }

    /**
     * Format angka kuantitas yang sudah dikemas dengan pemisah ribuan titik.
     */
    public function formattedKemasQuantity(): string
    {
        return number_format($this->kemas_quantity, 0, ',', '.');
    }

    /**
     * Hitung persentase ketercapaian kemas dibanding target (0 - 100%).
     */
    public function fulfillmentPercentage(): float
    {
        if ($this->target_quantity <= 0) {
            return 0.0;
        }

        return min(100.0, round(($this->kemas_quantity / $this->target_quantity) * 100, 1));
    }

    /**
     * Label status berbahasa Indonesia dari Enum ProductionOrderStatus.
     */
    public function statusLabel(): string
    {
        return $this->status->label();
    }

    /**
     * Warna badge status dari Enum ProductionOrderStatus.
     */
    public function statusBadgeColor(): string
    {
        return $this->status->badgeColor();
    }

    public function isReady(): bool
    {
        return $this->status === ProductionOrderStatus::READY;
    }

    public function isInProgress(): bool
    {
        return $this->status === ProductionOrderStatus::IN_PROGRESS;
    }

    public function isCompleted(): bool
    {
        return $this->status === ProductionOrderStatus::COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === ProductionOrderStatus::CANCELLED;
    }
}
