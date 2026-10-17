<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyTarget extends Model
{
    protected $fillable = [
        'sankalp_id',
        'target_count',
        'effective_from',
        'effective_until',
    ];

    protected function casts(): array
    {
        return [
            'target_count' => 'integer',
            'effective_from' => 'date',
            'effective_until' => 'date',
        ];
    }

    public function sankalp(): BelongsTo
    {
        return $this->belongsTo(Sankalp::class);
    }
}
