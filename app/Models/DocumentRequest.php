<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class DocumentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reference_number',
        'document_type',
        'for_whom',
        'application_data',
        'purpose',
        'contact_first_name',
        'contact_middle_name',
        'contact_last_name',
        'contact_phone',
        'contact_email',
        'claim_date',
        'claim_time',
        'status',
    ];

    protected $casts = [
        'application_data' => 'array',
        'claim_date' => 'date',
        'claim_time' => 'datetime:H:i',
    ];

    /**
     * Get the user that owns the document request
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
