<?php

namespace App\Http\Controllers;

use App\Mail\ActivityDeleted;
use App\Models\Activity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $activities = Activity::query()
            ->with('images')
            ->withCount(['signups', 'externalSignups'])
            ->withExists(['signups as is_signed_up' => fn ($query) => $query->where('user_id', $request->user()->id)])
            ->when(! $request->user()->isAdmin(), fn ($query) => $query->where('status', Activity::STATUS_PUBLISHED)->where('is_deleted', false))
            // Verlopen activiteiten (einde of anders begin ligt in het verleden) zijn alleen voor admins zichtbaar.
            // Activiteiten zonder datum blijven staan, die zijn nog niet ingepland.
            ->when(! $request->user()->isAdmin(), fn ($query) => $query->where(
                fn ($query) => $query->whereNull('starts_at')->orWhereRaw('COALESCE(ends_at, starts_at) >= ?', [now()])
            ))
            ->orderBy('signup_deadline')
            ->get();

        return view('activities.index', [
            'activities' => $activities,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('activities.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedActivityData($request);

        $activity = Activity::create($validated);

        foreach ($request->file('images', []) as $position => $file) {
            $activity->images()->create([
                'path' => $file->store('activities', 'public'),
                'position' => $position,
            ]);
        }

        return redirect()->route('activities.index')->with('status', 'Activiteit aangemaakt.');
    }

    /**
     * Valideer en normaliseer de ingevoerde activiteitgegevens.
     *
     * Wordt door store() en update() gebruikt, zodat beide dezelfde regels hanteren
     * (bij publiceren zijn meer velden verplicht dan bij een concept).
     *
     * @return array<string, mixed>
     */
    private function validatedActivityData(Request $request, ?Activity $activity = null): array
    {
        // Een "-" bij eigen bijdrage betekent: geen bijdrage (0). Een komma mag als decimaalteken.
        $contribution = trim((string) $request->input('personal_contribution'));
        if (in_array($contribution, ['-', '--', '—'], true)) {
            $request->merge(['personal_contribution' => 0]);
        } elseif ($contribution !== '') {
            $request->merge(['personal_contribution' => str_replace(',', '.', $contribution)]);
        }

        // Een concept mag onvolledig zijn (lege velden worden als "—" getoond),
        // maar bij publiceren moeten alle velden behalve omschrijving, eigen bijdrage en minimum ingevuld zijn.
        $publish = $request->boolean('publish');
        $required = $publish ? 'required' : 'nullable';
        $foodRequired = $publish && $request->boolean('includes_food') ? 'required' : 'nullable';

        // Alleen vergelijken met de begintijd / het minimum als die zelf geldig zijn,
        // anders krijg je bij één fout ook een verwarrende fout op het andere veld.
        $hasValidStart = $request->filled('starts_at') && strtotime((string) $request->input('starts_at')) !== false;
        $hasValidMin = filter_var($request->input('min_participants'), FILTER_VALIDATE_INT) !== false;

        // Bij bewerken telt een nieuwe foto alleen mee als die niet al verwijderd wordt.
        $imagesRule = $activity
            ? function (string $attribute, mixed $value, \Closure $fail) use ($activity, $request): void {
                $remaining = $activity->images()->whereNotIn('id', $request->input('remove_images', []))->count();
                if ($remaining + count($value) > 5) {
                    $fail("Je kunt maximaal 5 foto's hebben.");
                }
            }
        : 'max:5';

        $validated = $request->validate([
            'title' => [$required, 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'starts_at' => [$required, 'date'],
            'ends_at' => [$required, 'date', ...($hasValidStart ? ['after:starts_at'] : [])],
            'signup_deadline' => [$required, 'date', ...($hasValidStart ? ['before_or_equal:starts_at'] : [])],
            'personal_contribution' => ['nullable', 'numeric', 'min:0'],
            'includes_food' => ['boolean'],
            'food_description' => [$foodRequired, 'string'],
            'min_participants' => ['nullable', 'integer', 'min:0'],
            'max_participants' => [$required, 'integer', 'min:0', ...($hasValidMin ? ['gte:min_participants'] : [])],
            'allow_partner' => ['boolean'],
            'is_external' => ['boolean'],
            'publish' => ['boolean'],
            'images' => ['nullable', 'array', $imagesRule],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer'],
        ], [
            'required' => 'Vul een geldige waarde in.',
            'images.max' => "Je kunt maximaal 5 foto's uploaden.",
            'images.*.image' => 'Een van de bestanden is geen afbeelding.',
            'images.*.mimes' => 'Foto\'s moeten JPG, PNG of WebP zijn.',
            'images.*.max' => 'Een foto is groter dan 5 MB.',
            'images.*.uploaded' => 'Uploaden van een foto is mislukt. Is hij kleiner dan 5 MB?',
        ]);

        unset($validated['publish'], $validated['images'], $validated['remove_images']);

        $validated['status'] = $publish ? Activity::STATUS_PUBLISHED : Activity::STATUS_DRAFT;
        $validated['includes_food'] = $request->boolean('includes_food');
        $validated['allow_partner'] = $request->boolean('allow_partner');
        $validated['is_external'] = $request->boolean('is_external');

        return $validated;
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Activity $activity): View
    {
        abort_if(($activity->status === Activity::STATUS_DRAFT || $activity->is_deleted) && ! $request->user()->isAdmin(), 404);

        $activity->load(['images', 'signups.user', 'externalSignups'])->loadCount(['signups', 'externalSignups']);

        return view('activities.show', [
            'activity' => $activity,
            'isSignedUp' => $activity->signups->contains('user_id', $request->user()->id),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Activity $activity): View
    {
        $activity->load('images');

        return view('activities.edit', [
            'activity' => $activity,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Activity $activity): RedirectResponse
    {
        $validated = $this->validatedActivityData($request, $activity);

        $activity->update($validated);

        $removeIds = $request->input('remove_images', []);
        if (! empty($removeIds)) {
            $imagesToRemove = $activity->images()->whereIn('id', $removeIds)->get();

            foreach ($imagesToRemove as $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            }
        }

        $position = $activity->images()->count();
        foreach ($request->file('images', []) as $file) {
            $activity->images()->create([
                'path' => $file->store('activities', 'public'),
                'position' => $position++,
            ]);
        }

        return redirect()->route('activities.show', $activity)->with('status', 'Activiteit bijgewerkt.');
    }

    /**
     * Markeer de activiteit als verwijderd (blijft zichtbaar voor beheerders) en mail de ingeschreven deelnemers.
     */
    public function destroy(Activity $activity): RedirectResponse
    {
        if ($activity->is_deleted) {
            return back()->with('error', 'Deze activiteit is al verwijderd.');
        }

        $activity->update(['is_deleted' => true]);

        foreach ($activity->signups()->with('user')->get() as $signup) {
            Mail::to($signup->user)->send(new ActivityDeleted($activity));
        }

        foreach ($activity->externalSignups as $externalSignup) {
            Mail::to($externalSignup->email)->send(new ActivityDeleted($activity, isExternal: true));
        }

        return redirect()->route('activities.show', $activity)->with('status', 'Activiteit verwijderd. Ingeschreven deelnemers zijn per mail op de hoogte gebracht.');
    }
}
