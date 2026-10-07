<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Vier testactiviteiten die elk een andere combinatie van velden laten zien.
     */
    public function run(): void
    {
        // Eén avond, met eten en partner, gepubliceerd
        $start = now()->addWeeks(3)->setTime(18, 0);
        Activity::create([
            'title' => 'Kerstdiner',
            'description' => 'Gezellig kerstdiner met het hele team in het restaurant om de hoek. Neem gerust je partner mee!',
            'starts_at' => $start,
            'ends_at' => $start->copy()->setTime(22, 30),
            'signup_deadline' => $start->copy()->subWeek()->setTime(12, 0),
            'cost' => 45.00,
            'personal_contribution' => 15.00,
            'includes_food' => true,
            'food_description' => 'Driegangenmenu, vegetarische optie beschikbaar.',
            'min_participants' => 10,
            'max_participants' => 60,
            'allow_partner' => true,
            'is_external' => false,
            'status' => Activity::STATUS_PUBLISHED,
        ]);

        // Meerdaags, extern, met eten, gepubliceerd
        $start = now()->addWeeks(6)->next('Friday')->setTime(16, 0);
        Activity::create([
            'title' => 'Teamweekend Ardennen',
            'description' => 'Een weekend weg met wandelen, mountainbiken en een kampvuur. Overnachting in een groepsaccommodatie.',
            'starts_at' => $start,
            'ends_at' => $start->copy()->addDays(2)->setTime(14, 0),
            'signup_deadline' => $start->copy()->subWeeks(3)->setTime(17, 0),
            'cost' => 250.00,
            'personal_contribution' => 75.00,
            'includes_food' => true,
            'food_description' => 'Ontbijt, lunch en diner inbegrepen.',
            'min_participants' => 8,
            'max_participants' => 24,
            'allow_partner' => false,
            'is_external' => true,
            'status' => Activity::STATUS_PUBLISHED,
        ]);

        // Kort, gratis, zonder eten, concept
        $start = now()->addWeeks(2)->next('Wednesday')->setTime(14, 0);
        Activity::create([
            'title' => 'Workshop Laravel voor beginners',
            'description' => 'In twee en een half uur bouw je je eerste Laravel-applicatie. Laptop meenemen.',
            'starts_at' => $start,
            'ends_at' => $start->copy()->setTime(16, 30),
            'signup_deadline' => $start->copy()->subDays(2)->setTime(23, 59),
            'cost' => 0,
            'personal_contribution' => 0,
            'includes_food' => false,
            'food_description' => null,
            'min_participants' => 3,
            'max_participants' => 12,
            'allow_partner' => false,
            'is_external' => false,
            'status' => Activity::STATUS_DRAFT,
        ]);

        // Minimaal ingevuld: geen beschrijving, deadline of deelnemersgrenzen, concept
        $start = now()->addMonths(2)->next('Thursday')->setTime(19, 30);
        Activity::create([
            'title' => 'Bowlingavond',
            'description' => null,
            'starts_at' => $start,
            'ends_at' => $start->copy()->setTime(22, 0),
            'signup_deadline' => null,
            'cost' => 12.50,
            'personal_contribution' => 5.00,
            'includes_food' => false,
            'food_description' => null,
            'min_participants' => null,
            'max_participants' => null,
            'allow_partner' => true,
            'is_external' => true,
            'status' => Activity::STATUS_DRAFT,
        ]);
    }
}
