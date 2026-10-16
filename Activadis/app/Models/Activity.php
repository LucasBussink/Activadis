<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\ActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'ends_at',
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
            'ends_at' => 'datetime',
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

    public function images(): HasMany
    {
        return $this->hasMany(ActivityImage::class)->orderBy('position');
    }

    public function signups(): HasMany
    {
        return $this->hasMany(Signups::class);
    }

    /**
     * Schrijf een gebruiker in. Geeft null terug als dat niet (meer) kan.
     */
    public function addUser(User $user, bool $withPartner = false): ?Signups
    {
        if (! $this->isSignupOpen() || $this->isFull() || $this->isUserSignedUp($user)) {
            return null;
        }

        return $this->signups()->create([
            'user_id' => $user->id,
            // Een +1 kan alleen bij activiteiten die dat toestaan
            'brings_partner' => $withPartner && $this->allow_partner,
        ]);
    }

    /**
     * Zet een +1 aan of uit voor een bestaande inschrijving. Geeft false terug als dat niet (meer) kan.
     */
    public function setPartner(User $user, bool $withPartner): bool
    {
        if (! $this->isSignupOpen() || ($withPartner && ! $this->allow_partner)) {
            return false;
        }

        return $this->signups()->where('user_id', $user->id)->update(['brings_partner' => $withPartner]) > 0;
    }

    /**
     * Schrijf een gebruiker uit. Geeft false terug als dat niet (meer) kan.
     */
    public function removeUser(User $user): bool
    {
        if (! $this->isSignupOpen()) {
            return false;
        }

        return $this->signups()->where('user_id', $user->id)->delete() > 0;
    }

    /**
     * In- en uitschrijven kan alleen bij een gepubliceerde activiteit waarvan de deadline nog niet voorbij is en die nog niet is afgelopen.
     */
    public function isSignupOpen(): bool
    {
        return $this->status === self::STATUS_PUBLISHED
          && ($this->signup_deadline === null || $this->signup_deadline->isFuture())
          && ! $this->hasEnded();
    }

    /**
     * Een activiteit is afgelopen als het einde (of anders het begin) in het verleden ligt.
     * Zonder datum is hij nog niet ingepland, dus ook niet afgelopen.
     */
    public function hasEnded(): bool
    {
        $end = $this->ends_at ?? $this->starts_at;

        return $end !== null && \Illuminate\Support\Carbon::parse($end)->isPast();
    }

    public function isFull(): bool
    {
        return $this->max_participants !== null
          && $this->getSignedUpUserCount() >= $this->max_participants;
    }

    public function isUserSignedUp(User $user): bool
    {
        return $this->signups()->where('user_id', $user->id)->exists();
    }

    public function getSignedUpUsers(): array
    {
        return $this->signups()->with('user')->get()->toArray();
    }

    public function getSignedUpUserCount(): int
    {
        return $this->signups()->count();
    }

    /**
     * Formatteer een datum in woorden, bijv. "10 jan 2026 14:00".
     */
    public static function formatDate(?CarbonInterface $date, bool $withTime = true): ?string
    {
        if ($date === null) {
            return null;
        }

        return str_replace('.', '', $date->translatedFormat($withTime ? 'j M Y H:i' : 'j M Y'));
    }

    /**
     * De periode van de activiteit, bijv. "10 jan 2026 14:00 – 17:00"
     * of "10 jan 2026 14:00 – 12 jan 2026 17:00" bij meerdaagse activiteiten.
     */
    public function period(): string
    {
        $start = self::formatDate($this->starts_at) ?? '—';

        if ($this->starts_at === null || $this->ends_at === null) {
            return $start;
        }

        $end = $this->starts_at->isSameDay($this->ends_at)
          ? $this->ends_at->format('H:i')
          : self::formatDate($this->ends_at);

        return $start.' – '.$end;
    }
}
