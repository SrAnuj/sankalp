<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sankalp extends Model
{
    protected $fillable = [
        'user_id',
        'naam_library_id',
        'target_count',
        'completed_count',
        'status',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'target_count' => 'integer',
            'completed_count' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function naam(): BelongsTo
    {
        return $this->belongsTo(NaamLibrary::class, 'naam_library_id');
    }

    public function progressLogs(): HasMany
    {
        return $this->hasMany(SankalpProgressLog::class);
    }
}
