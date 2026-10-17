<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SankalpProgressLog extends Model
{
    protected $fillable = [
        'sankalp_id',
        'progress_date',
        'count',
    ];

    protected function casts(): array
    {
        return [
            'progress_date' => 'date',
            'count' => 'integer',
        ];
    }

    public function sankalp(): BelongsTo
    {
        return $this->belongsTo(Sankalp::class);
    }
}
