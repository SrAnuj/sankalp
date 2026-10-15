<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NaamLibrary extends Model
{
    use SoftDeletes;

    protected $table = 'naam_library';

    protected $fillable = [
        'name',
        'name_hindi',
        'name_punjabi',
        'slug',
        'description',
        'icon',
        'parent_id',
        'status',
        'sort_order',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('sort_order');
    }
}
