<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceOperatingHour extends Model
{
    public $timestamps = false; // As per migration, no timestamps for this table

    protected $fillable = [
        'resource_id',
        'day_of_week',
        'open_time',
        'close_time',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }
}
