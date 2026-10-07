<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\ActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Signups;

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

    public function signups()
    {
        return $this->hasMany(Signups::class);
    }

    public function addUser(User $user): Signups
    {
        return $this->signups()->create([
            'user_id' => $user->id,
            'activity_id' => $this->id,
        ]);
    }

    public function removeUser(User $user): void
    {
        $this->signups()->where('user_id', $user->id)->delete();
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
