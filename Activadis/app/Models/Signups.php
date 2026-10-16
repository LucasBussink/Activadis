<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signups extends Model
{
    protected $fillable = [
        'user_id',
        'activity_id',
        'brings_partner',
    ];

    protected function casts(): array
    {
        return [
            'brings_partner' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}
