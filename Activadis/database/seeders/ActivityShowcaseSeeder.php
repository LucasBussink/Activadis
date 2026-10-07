<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Seeder;

class ActivityShowcaseSeeder extends Seeder
{
  /**
   * Extra activiteiten om het overzicht te testen met verschillende situaties.
   * Draaien met: php artisan db:seed --class=ActivityShowcaseSeeder
   */
  public function run(): void
  {
    //* Hulpfunctie: een nieuw tijdstip, X dagen vanaf nu
    $at = fn(int $days, int $hour, int $minute = 0) => now()->addDays($days)->setTime($hour, $minute);

    $rows = [
      // Een avond, met partner, gepubliceerd
      [
        'title' => 'Padelavond',
        'description' => "Gezellig een potje padel met collega's. Rackets zijn aanwezig.",
        'starts_at' => $at(12, 19),
        'ends_at' => $at(12, 21),
        'signup_deadline' => $at(9, 12),
        'cost' => 15.00,
        'min_participants' => 4,
        'max_participants' => 8,
        'allow_partner' => true,
        'status' => Activity::STATUS_PUBLISHED,
      ],
      // Meerdaags, gratis, met eten
      [
        'title' => 'LAN-party weekend',
        'description' => 'Een heel weekend gamen, pizza en energy drinks. Neem je eigen laptop of console mee. Slaapzakken zijn handig want we blijven slapen op kantoor.',
        'starts_at' => now()->next('Friday')->setTime(18, 0),
        'ends_at' => now()->next('Friday')->addDays(2)->setTime(12, 0),
        'signup_deadline' => $at(8, 17),
        'cost' => null,
        'includes_food' => true,
        'food_description' => 'Pizza op vrijdag en zaterdag, ontbijt op zondag.',
        'min_participants' => 6,
        'max_participants' => 20,
        'status' => Activity::STATUS_PUBLISHED,
      ],
      // Extern, zeer lange titel, geen beschrijving, geen deadline, geen maximum
      [
        'title' => 'Open dag Covadis met een bijzonder lange titel die over twee regels gaat',
        'description' => null,
        'starts_at' => $at(25, 10),
        'ends_at' => $at(25, 16),
        'signup_deadline' => null,
        'cost' => 0,
        'is_external' => true,
        'status' => Activity::STATUS_PUBLISHED,
      ],
      // Inschrijving al gesloten
      [
        'title' => 'Pubquiz',
        'description' => 'Vier rondes, drie bier, een winnaar.',
        'starts_at' => $at(3, 20),
        'ends_at' => $at(3, 23),
        'signup_deadline' => $at(-1, 12),
        'cost' => 7.50,
        'includes_food' => true,
        'food_description' => 'Bitterballen en nacho\'s.',
        'min_participants' => 10,
        'max_participants' => 40,
        'allow_partner' => true,
        'status' => Activity::STATUS_PUBLISHED,
      ],
      // Zonder eindtijd en zonder kosten
      [
        'title' => 'Yoga in de lunchpauze',
        'description' => 'Een half uurtje ontspannen tussen de middag. Matjes liggen klaar.',
        'starts_at' => $at(6, 12, 30),
        'ends_at' => null,
        'signup_deadline' => $at(5, 17),
        'cost' => 0,
        'max_participants' => 15,
        'status' => Activity::STATUS_PUBLISHED,
      ],
      // Concept zonder titel en datum (alleen zichtbaar voor beheerders)
      [
        'title' => null,
        'description' => null,
        'starts_at' => null,
        'ends_at' => null,
        'status' => Activity::STATUS_DRAFT,
      ],
    ];

    foreach ($rows as $row) {
      //* forceCreate omdat ends_at nog niet in de fillable-lijst van het model staat
      Activity::forceCreate($row);
    }
  }
}
