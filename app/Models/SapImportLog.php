<?php

namespace App\Models;

use Database\Factories\SapImportLogFactory;
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
class SapImportLog extends Model
{
    /** @use HasFactory<SapImportLogFactory> */
    use HasFactory;

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
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
