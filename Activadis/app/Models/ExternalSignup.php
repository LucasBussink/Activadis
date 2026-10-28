<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ExternalSignup extends Model
{
    use Notifiable;

    protected $fillable = [
        'activity_id',
        'name',
        'email',
        'brings_partner',
        'dietary_notes',
        'token',
    ];

    protected function casts(): array
    {
        return [
            'brings_partner' => 'boolean',
        ];
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * De bevestigingsmail gaat naar het opgegeven e-mailadres.
     */
    public function routeNotificationForMail(): string
    {
        return $this->email;
    }
}
