<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $activities = Activity::query()
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
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['required', 'date'],
            'signup_deadline' => ['nullable', 'date', 'before_or_equal:starts_at'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'personal_contribution' => ['nullable', 'numeric', 'min:0'],
            'includes_food' => ['boolean'],
            'food_description' => ['nullable', 'string'],
            'min_participants' => ['nullable', 'integer', 'min:0'],
            'max_participants' => ['nullable', 'integer', 'gte:min_participants'],
            'allow_partner' => ['boolean'],
            'is_external' => ['boolean'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $validated['includes_food'] = $request->boolean('includes_food');
        $validated['allow_partner'] = $request->boolean('allow_partner');
        $validated['is_external'] = $request->boolean('is_external');

        Activity::create($validated);

        return redirect()->route('activities.index')->with('status', 'Activiteit aangemaakt.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Activity $activity): View
    {
        return view('activities.show', [
            'activity' => $activity,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
