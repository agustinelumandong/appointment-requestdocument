<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Barangay extends Model
{
    protected $fillable = ['name', 'code', 'city_id'];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
