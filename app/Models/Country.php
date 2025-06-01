<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Country extends Model
{
    protected $fillable = ['name', 'code'];

    public function regions(): HasMany
    {
        return $this->hasMany(Regions::class);
    }
}
