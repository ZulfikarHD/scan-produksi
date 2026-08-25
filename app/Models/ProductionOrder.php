<?php

namespace App\Models;

use App\Enums\ProductionOrderStatus;
use Database\Factories\ProductionOrderFactory;
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
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->creator();
    }
}
