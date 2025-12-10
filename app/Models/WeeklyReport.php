<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyReport extends Model
{
    protected $fillable = [
        'item_id',
        'year',
        'month',
        'week',
        'start_date',
        'end_date',
        'stok_awal',
        'total_penambahan',
        'total_pengurangan',
        'stok_akhir',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
