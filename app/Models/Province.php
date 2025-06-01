<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Province extends Model
{
  protected $fillable = ['name', 'code', 'region_id'];

  public function region(): BelongsTo
  {
    return $this->belongsTo(Regions::class);
  }

  public function cities(): HasMany
  {
    return $this->hasMany(City::class);
  }
}
