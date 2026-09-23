<?php

namespace App\Models;

use Database\Factories\ActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    /** @use HasFactory<ActivityFactory> */
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'title',
        'description',
        'starts_at',
        'signup_deadline',
        'cost',
        'personal_contribution',
        'includes_food',
        'food_description',
        'min_participants',
        'max_participants',
        'allow_partner',
        'is_external',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'signup_deadline' => 'datetime',
            'cost' => 'decimal:2',
            'personal_contribution' => 'decimal:2',
            'includes_food' => 'boolean',
            'min_participants' => 'integer',
            'max_participants' => 'integer',
            'allow_partner' => 'boolean',
            'is_external' => 'boolean',
        ];
    }
}
