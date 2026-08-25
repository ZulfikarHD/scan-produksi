<?php

namespace App\Models;

use Database\Factories\SapImportLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $file_name
 * @property string|null $file_path
 * @property int $total_rows
 * @property int $imported_count
 * @property int $updated_count
 * @property int $failed_count
 * @property string $status
 * @property array<string>|null $error_log
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 */
#[Fillable([
    'user_id',
    'file_name',
    'file_path',
    'total_rows',
    'imported_count',
    'updated_count',
    'failed_count',
    'status',
    'error_log',
])]
class SapImportLog extends Model
{
    /** @use HasFactory<SapImportLogFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'file_name',
        'file_path',
        'total_rows',
        'imported_count',
        'updated_count',
        'failed_count',
        'status',
        'error_log',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_rows' => 'integer',
            'imported_count' => 'integer',
            'updated_count' => 'integer',
            'failed_count' => 'integer',
            'error_log' => 'array',
        ];
    }

    /**
     * User (Supervisor) who uploaded the SAP file.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Indonesian human-readable label for import status.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'success' => 'Berhasil Penuh',
            'partial' => 'Sebagian Berhasil',
            'failed' => 'Gagal Diproses',
            default => ucfirst($this->status),
        };
    }

    /**
     * Flux UI badge color for import status.
     */
    public function statusBadgeColor(): string
    {
        return match ($this->status) {
            'success' => 'green',
            'partial' => 'amber',
            'failed' => 'red',
            default => 'zinc',
        };
    }

    /**
     * Check if this import log has any logged error items.
     */
    public function hasErrors(): bool
    {
        return $this->failed_count > 0 && ! empty($this->error_log);
    }
}
