<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ExternalSignup;
use App\Models\Signups;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Demodata voor de presentatie: gebruikers, activiteiten in allerlei situaties, inschrijvingen,
 * externe aanmeldingen en foto's (zelf getekende SVG's, dus zonder externe bestanden).
 *
 * Draaien met: php artisan migrate:fresh --seed
 * Alle datums zijn relatief aan vandaag, zodat de demo er altijd actueel uitziet.
 */
class DemoSeeder extends Seeder
{
    private const COLLEAGUE_PASSWORD = 'wachtwoord';

    /** @var array<int, User> */
    private array $colleagues = [];

    public function run(): void
    {
        mt_srand(2026);

        $this->cleanUp();
        $this->createUsers();
        $this->createActivities();
    }

    // ------------------------------------------------------------------
    // Opruimen, zodat de seeder vaker gedraaid kan worden
    // ------------------------------------------------------------------

    private function cleanUp(): void
    {
        DB::table('external_signups')->delete();
        DB::table('signups')->delete();
        DB::table('activity_images')->delete();
        DB::table('activities')->delete();
        DB::table('user_invite_tokens')->delete();

        foreach (Storage::disk('public')->files('activities') as $file) {
            if (str_starts_with(basename($file), 'demo-')) {
                Storage::disk('public')->delete($file);
            }
        }
    }

    // ------------------------------------------------------------------
    // Gebruikers
    // ------------------------------------------------------------------

    private function createUsers(): void
    {
        $this->call(UserSeeder::class); // admin@admin.nl / admin en user@user.nl / user

        $hash = Hash::make(self::COLLEAGUE_PASSWORD);

        $this->makeUser('Sanne de Boer', 'sanne.deboer@covadis-demo.nl', $hash, isAdmin: true);

        $names = [
            'Daan Visser', 'Emma Janssen', 'Lars Mulder', 'Noa Bakker', 'Thijs de Vries', 'Lotte Smit',
            'Bram Meijer', 'Fleur van Dijk', 'Jesse Bos', 'Mila Peters', 'Sem Hendriks', 'Julia van Leeuwen',
            'Ruben Dekker', 'Anouk Brouwer', 'Tim de Groot', 'Eva Kok', 'Niels Jacobs', 'Sophie Vos',
            'Mees van den Berg', 'Iris Willems', 'Koen Hoekstra', 'Lisa Schouten',
            'Rik Franken', 'Maud Hofman', 'Sven Wouters', 'Yasmin El Amrani', 'Joris Prins', 'Fenna Bouwman',
            'Luuk Scholten', 'Isa Verhoeven', 'Cas Mol', 'Nova Kramer', 'Hugo Zwart', 'Lena Vink',
        ];

        foreach ($names as $name) {
            $email = Str::slug($name, '.').'@covadis-demo.nl';
            $this->colleagues[] = $this->makeUser($name, $email, $hash);
        }

        // Uitnodigingen: drie openstaand, een verlopen (een uitnodiging is 28 dagen geldig)
        foreach ([['Yara Kramer', 1], ['Milan Postma', 5], ['Wendy Lammers', 12], ['Peter Vermeulen', 40]] as [$name, $daysAgo]) {
            $user = $this->makeUser($name, Str::slug($name, '.').'@covadis-demo.nl', Hash::make(Str::random(40)));

            DB::table('user_invite_tokens')->insert([
                'email' => $user->email,
                'token' => Hash::make(Str::random(40)),
                'created_at' => now()->subDays($daysAgo),
            ]);
        }
    }

    private function makeUser(string $name, string $email, string $passwordHash, bool $isAdmin = false): User
    {
        $user = User::firstOrNew(['email' => $email]);
        $user->name = $name;
        $user->is_admin = $isAdmin;
        $user->email_verified_at ??= now();
        $user->password = $passwordHash;
        $user->save();

        return $user;
    }

    // ------------------------------------------------------------------
    // Activiteiten
    // ------------------------------------------------------------------

    private function createActivities(): void
    {
        $demoUser = User::where('email', 'user@user.nl')->firstOrFail();
        $day = fn (int $days, int $hour = 0, int $minute = 0) => now()->addDays($days)->setTime($hour, $minute);

        $friday = now()->addWeeks(6)->next('Friday')->setTime(16, 0);
        $lanStart = now()->addDays(25)->next('Friday')->setTime(18, 0);

        $definitions = [
            // Bijna vol: nog 1 plek, en de demo-gebruiker staat erop
            [
                'title' => 'Padelavond',
                'description' => "Een potje padel met collega's op de baan bij het sportcentrum. Rackets en ballen zijn aanwezig, ervaring is niet nodig. Na afloop drinken we nog iets in de kantine.",
                'starts_at' => $day(9, 19), 'ends_at' => $day(9, 21), 'signup_deadline' => $day(6, 12),
                'cost' => 24, 'personal_contribution' => 8, 'min_participants' => 4, 'max_participants' => 8,
                'signups' => 7, 'with' => [$demoUser],
                'images' => [['court', 'ocean']],
            ],
            // Vol
            [
                'title' => 'Bowlingavond',
                'description' => 'Strijden om de gouden bowlingbal! We spelen in teams van vier en het verliezende team trakteert op de eerste ronde.',
                'starts_at' => $day(16, 18, 30), 'ends_at' => $day(16, 22), 'signup_deadline' => $day(11, 12),
                'cost' => 30, 'personal_contribution' => 10, 'includes_food' => true, 'food_description' => 'Bittergarnituur en een drankje per persoon.',
                'min_participants' => 6, 'max_participants' => 12, 'signups' => 12,
                'images' => [['bowling', 'berry'], ['party', 'night']],
            ],
            // Ruim plek, met +1 en dieetwensen, demo-gebruiker staat erop met +1
            [
                'title' => 'Kerstdiner',
                'description' => 'Het jaarlijkse kerstdiner met het hele team in het restaurant om de hoek. Neem gerust je partner mee. We sluiten af met een kleine cadeau-ruil, een cadeautje van maximaal 10 euro.',
                'starts_at' => $day(52, 18), 'ends_at' => $day(52, 22, 30), 'signup_deadline' => $day(30, 12),
                'cost' => 45, 'personal_contribution' => 15, 'includes_food' => true, 'food_description' => 'Driegangenmenu, vegetarische en veganistische opties beschikbaar.',
                'allow_partner' => true, 'min_participants' => 10, 'max_participants' => 60, 'signups' => 31,
                'with' => [$demoUser], 'partner_for' => [$demoUser->email], 'notes_for' => [$demoUser->email => 'Vegetarisch, geen paddenstoelen'],
                'images' => [['table', 'gold'], ['night', 'night'], ['party', 'berry']],
            ],
            // Meerdaags, nog te weinig deelnemers (minimaal 8, nu 5), vijf foto's voor de carrousel
            [
                'title' => 'Teamweekend Ardennen',
                'description' => 'Een weekend weg met wandelen, mountainbiken en een kampvuur. We slapen in een groepsaccommodatie midden in de bossen, met een grote keuken en een overdekte veranda.',
                'starts_at' => $friday, 'ends_at' => $friday->copy()->addDays(2)->setTime(14, 0), 'signup_deadline' => $friday->copy()->subWeeks(3)->setTime(17, 0),
                'cost' => 250, 'personal_contribution' => 75, 'includes_food' => true, 'food_description' => 'Ontbijt, lunch en diner zijn inbegrepen.',
                'is_external' => true, 'min_participants' => 8, 'max_participants' => 24, 'signups' => 5,
                'images' => [['forest', 'forest'], ['hills', 'sunset'], ['night', 'night'], ['waves', 'mint'], ['circles', 'slate']],
            ],
            // Deadline is morgen
            [
                'title' => 'Escape room',
                'description' => 'Kunnen wij in 60 minuten ontsnappen uit de verlaten laboratoriumkamer? We gaan in groepjes van vijf. Wie als eerste eruit is, wint.',
                'starts_at' => $day(6, 20), 'ends_at' => $day(6, 21, 30), 'signup_deadline' => $day(1, 23),
                'cost' => 22, 'personal_contribution' => 0, 'min_participants' => 6, 'max_participants' => 10, 'signups' => 6,
                'images' => [['night', 'berry']],
            ],
            // Deadline voorbij, activiteit komt nog, demo-gebruiker staat erop (kan zich niet meer uitschrijven)
            [
                'title' => 'Barbecue aan de Oude IJssel',
                'description' => 'Een zomerse barbecue aan het water, ook als het weer meewerkt een beetje. Neem een stoel mee en een goed humeur. De barbecues staan klaar.',
                'starts_at' => $day(4, 17), 'ends_at' => $day(4, 21, 30), 'signup_deadline' => $day(-2, 12),
                'cost' => 18, 'personal_contribution' => 5, 'includes_food' => true, 'food_description' => 'Vlees, vis en veggie, met salades en brood.',
                'allow_partner' => true, 'min_participants' => 8, 'max_participants' => 20, 'signups' => 14, 'with' => [$demoUser],
                'images' => [['hills', 'sunset'], ['table', 'gold']],
            ],
            // Al geweest, alleen zichtbaar via "Toon verlopen" voor beheerders
            [
                'title' => 'Zomerborrel',
                'description' => 'De traditionele borrel om de zomer in te luiden. Het was gezellig, tot de volgende keer!',
                'starts_at' => $day(-10, 16, 30), 'ends_at' => $day(-10, 19), 'signup_deadline' => $day(-14, 12),
                'cost' => 12, 'personal_contribution' => 0, 'includes_food' => true, 'food_description' => 'Borrelhapjes.',
                'min_participants' => 10, 'max_participants' => 30, 'signups' => 18,
                'images' => [['party', 'gold']],
            ],
            // Gratis, zonder maximum, zonder deadline en zonder foto
            [
                'title' => 'Lunchwandeling',
                'description' => 'Even het hoofd leegmaken met een stevige wandeling van een half uur. We vertrekken bij de ingang, kom gewoon mee.',
                'starts_at' => $day(3, 12, 15), 'ends_at' => $day(3, 13, 15), 'signup_deadline' => null,
                'cost' => 0, 'personal_contribution' => 0, 'allow_partner' => true, 'min_participants' => null, 'max_participants' => null, 'signups' => 9,
                'images' => [],
            ],
            // Extern, ruim plek, met externe aanmeldingen en drie foto's
            [
                'title' => 'Open dag Covadis',
                'description' => 'Kom kijken hoe het er bij Covadis aan toe gaat! Je krijgt een rondleiding, spreekt onze mensen en ziet wat we voor onze klanten maken. Er is een lunchbuffet, en kinderen zijn welkom.',
                'starts_at' => $day(14, 10), 'ends_at' => $day(14, 16), 'signup_deadline' => $day(10, 17),
                'cost' => 0, 'personal_contribution' => 0, 'includes_food' => true, 'food_description' => 'Lunchbuffet met vegetarische opties.',
                'is_external' => true, 'allow_partner' => true, 'min_participants' => 10, 'max_participants' => 40, 'signups' => 12, 'externals' => 9,
                'images' => [['city', 'mint'], ['circles', 'gold'], ['hills', 'ocean']],
            ],
            // Extern en vol
            [
                'title' => 'Meet & greet nieuwe klanten',
                'description' => 'Een informele middag om kennis te maken met onze nieuwe klanten. Met koffie, thee en gebak, en korte presentaties van drie teams.',
                'starts_at' => $day(8, 15), 'ends_at' => $day(8, 17, 30), 'signup_deadline' => $day(5, 17),
                'cost' => 0, 'personal_contribution' => 0, 'includes_food' => true, 'food_description' => 'Koffie, thee en gebak.',
                'is_external' => true, 'min_participants' => 5, 'max_participants' => 12, 'signups' => 4, 'externals' => 8,
                'images' => [['city', 'slate']],
            ],
            // Extern, meerdaags
            [
                'title' => 'Hackathon voor studenten',
                'description' => 'Twee dagen samen bouwen aan een oplossing voor een echte opdracht van een klant. Je werkt in teams met onze developers als coach. Op de tweede dag presenteren de teams aan een jury.',
                'starts_at' => $day(30, 9), 'ends_at' => $day(31, 17), 'signup_deadline' => $day(20, 17),
                'cost' => 0, 'personal_contribution' => 0, 'includes_food' => true, 'food_description' => 'Lunch, diner en onbeperkt koffie.',
                'is_external' => true, 'min_participants' => 10, 'max_participants' => 30, 'signups' => 6, 'externals' => 7,
                'images' => [['circles', 'night'], ['waves', 'ocean']],
            ],
            // Lange titel
            [
                'title' => 'Jaarlijkse kennisdag met workshops, lezingen en een uitgebreide lunch voor alle afdelingen',
                'description' => 'Een dag vol leren van elkaar. Kies in de ochtend en middag uit zes workshops, van cybersecurity tot presenteren. Tussen de middag lunchen we samen.',
                'starts_at' => $day(21, 9, 30), 'ends_at' => $day(21, 17), 'signup_deadline' => $day(14, 12),
                'cost' => 35, 'personal_contribution' => 0, 'includes_food' => true, 'food_description' => 'Lunch en koffie de hele dag.',
                'min_participants' => 20, 'max_participants' => 80, 'signups' => 27,
                'images' => [['city', 'gold']],
            ],
            // Meerdaags weekend, zonder +1
            [
                'title' => 'LAN-party weekend',
                'description' => 'Een weekend gamen met het team, van oldschool shooters tot racegames. Neem je eigen laptop of console mee, wij zorgen voor stroom, netwerk en pizza.',
                'starts_at' => $lanStart, 'ends_at' => $lanStart->copy()->addDays(2)->setTime(12, 0), 'signup_deadline' => $lanStart->copy()->subDays(7)->setTime(12, 0),
                'cost' => 40, 'personal_contribution' => 10, 'includes_food' => true, 'food_description' => 'Pizza en snacks, drankjes zijn inbegrepen.',
                'min_participants' => 6, 'max_participants' => 16, 'signups' => 10,
                'images' => [['night', 'night'], ['party', 'berry']],
            ],
            // Concept met bijna alles ingevuld, alleen zichtbaar voor beheerders
            [
                'title' => 'Yoga in de lunchpauze',
                'description' => 'Een rustig half uur yoga om de middag fris te beginnen. We komen nog met een datum voor de eerste les.',
                'status' => Activity::STATUS_DRAFT,
                'starts_at' => $day(12, 12, 30), 'ends_at' => $day(12, 13, 15), 'signup_deadline' => $day(9, 12),
                'cost' => 0, 'personal_contribution' => 0, 'max_participants' => 10, 'signups' => 0,
                'images' => [['waves', 'mint']],
            ],
            // Naamloos concept, bijna leeg
            [
                'title' => null, 'description' => null, 'status' => Activity::STATUS_DRAFT,
                'starts_at' => null, 'ends_at' => null, 'signup_deadline' => null,
                'cost' => null, 'personal_contribution' => null, 'min_participants' => null, 'max_participants' => null,
                'signups' => 0, 'images' => [],
            ],
            // Geannuleerd, deelnemers zijn per mail ingelicht
            [
                'title' => 'Pubquiz',
                'description' => 'Een avond quizzen in teams van vier, met rondes over sport, muziek en algemene kennis.',
                'starts_at' => $day(11, 20), 'ends_at' => $day(11, 23), 'signup_deadline' => $day(8, 12),
                'cost' => 10, 'personal_contribution' => 0, 'min_participants' => 8, 'max_participants' => 24, 'signups' => 9,
                'is_deleted' => true,
                'images' => [['party', 'slate']],
            ],
        ];

        foreach ($definitions as $index => $definition) {
            $this->createActivity($definition, $index);
        }
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function createActivity(array $definition, int $index): void
    {
        $signups = $definition['signups'] ?? 0;
        $externals = $definition['externals'] ?? 0;
        $with = $definition['with'] ?? [];
        $partnerFor = $definition['partner_for'] ?? [];
        $notesFor = $definition['notes_for'] ?? [];
        $images = $definition['images'] ?? [];

        $activity = Activity::create(array_merge([
            'description' => null,
            'includes_food' => false,
            'food_description' => null,
            'allow_partner' => false,
            'is_external' => false,
            'status' => Activity::STATUS_PUBLISHED,
            'is_deleted' => false,
        ], collect($definition)->except(['signups', 'externals', 'with', 'partner_for', 'notes_for', 'images'])->all()));

        // Foto's
        foreach ($images as $position => [$scene, $palette]) {
            $path = "activities/demo-{$index}-{$position}.svg";
            Storage::disk('public')->put($path, $this->svg($scene, $palette, $index * 10 + $position));
            $activity->images()->create(['path' => $path, 'position' => $position]);
        }

        // Inschrijvingen: eerst de gevraagde gebruikers, daarna een vaste mix van collega's
        $pool = collect($with)->concat($this->rotated($index))->unique('id')->take($signups);

        foreach ($pool as $user) {
            $isDemoSignup = in_array($user->email, $partnerFor, true);

            Signups::create([
                'user_id' => $user->id,
                'activity_id' => $activity->id,
                'brings_partner' => $activity->allow_partner && ($isDemoSignup || mt_rand(1, 100) <= 25),
                'dietary_notes' => $activity->includes_food
                    ? ($notesFor[$user->email] ?? (mt_rand(1, 100) <= 25 ? $this->dietaryNote() : null))
                    : null,
            ]);
        }

        // Externe aanmeldingen
        foreach ($this->externalPeople($externals, $index) as [$name, $email]) {
            ExternalSignup::create([
                'activity_id' => $activity->id,
                'name' => $name,
                'email' => $email,
                'brings_partner' => $activity->allow_partner && mt_rand(1, 100) <= 30,
                'dietary_notes' => $activity->includes_food && mt_rand(1, 100) <= 30 ? $this->dietaryNote() : null,
                'token' => Str::random(40),
            ]);
        }
    }

    /**
     * De collega's in een andere volgorde per activiteit, zodat niet steeds dezelfde mensen vooraan staan.
     *
     * @return array<int, User>
     */
    private function rotated(int $index): array
    {
        $offset = ($index * 5) % count($this->colleagues);

        return array_merge(array_slice($this->colleagues, $offset), array_slice($this->colleagues, 0, $offset));
    }

    private function dietaryNote(): string
    {
        $notes = [
            'Vegetarisch', 'Veganistisch', 'Notenallergie', 'Glutenvrij', 'Geen varkensvlees',
            'Lactose-intolerant', 'Halal', 'Allergisch voor schaaldieren', 'Vegetarisch, ook voor mijn +1',
        ];

        return $notes[mt_rand(0, count($notes) - 1)];
    }

    /**
     * @return array<int, array{0: string, 1: string}>
     */
    private function externalPeople(int $count, int $index): array
    {
        $people = [
            ['Mark Hermans', 'mark.hermans@voorbeeld.nl'], ['Femke Bosman', 'femke.bosman@voorbeeld.nl'],
            ['Jasper Kuipers', 'jasper.kuipers@voorbeeld.nl'], ['Roos van Rijn', 'roos.vanrijn@voorbeeld.nl'],
            ['Tom Wolters', 'tom.wolters@voorbeeld.nl'], ['Nina Segers', 'nina.segers@voorbeeld.nl'],
            ['Gijs Timmer', 'gijs.timmer@voorbeeld.nl'], ['Amber Haan', 'amber.haan@voorbeeld.nl'],
            ['Stijn Maas', 'stijn.maas@voorbeeld.nl'], ['Lieke Veldman', 'lieke.veldman@voorbeeld.nl'],
        ];

        $offset = $index % count($people);
        $rotated = array_merge(array_slice($people, $offset), array_slice($people, 0, $offset));

        return array_slice($rotated, 0, $count);
    }

    // ------------------------------------------------------------------
    // Foto's: eenvoudige illustraties als SVG
    // ------------------------------------------------------------------

    private function svg(string $scene, string $palette, int $seed): string
    {
        $palettes = [
            'sunset' => ['#ff9a5a', '#7a3b8f', '#ffe08a'],
            'ocean' => ['#4facfe', '#0e3a6b', '#e8f6ff'],
            'forest' => ['#7bd88f', '#0f4d3a', '#f6f3b0'],
            'night' => ['#2b3a8c', '#0a0f2c', '#fff3b0'],
            'berry' => ['#ff6b8b', '#5a1b6b', '#ffd6e0'],
            'gold' => ['#faa21b', '#0e1e3b', '#ffe9b8'],
            'mint' => ['#5ee7df', '#2b5876', '#ffffff'],
            'slate' => ['#8e9eab', '#243b55', '#ffd27a'],
        ];
        [$top, $bottom, $accent] = $palettes[$palette];

        mt_srand($seed + 1);

        $body = match ($scene) {
            'hills' => $this->sceneHills($accent),
            'night' => $this->sceneNight($accent),
            'court' => $this->sceneCourt($accent),
            'waves' => $this->sceneWaves(),
            'city' => $this->sceneCity($accent),
            'party' => $this->sceneParty($top, $accent),
            'forest' => $this->sceneForest($accent),
            'table' => $this->sceneTable($accent),
            'bowling' => $this->sceneBowling($accent),
            default => $this->sceneCircles($accent),
        };

        mt_srand(2026 + $seed); // zodat de rest van de seeder voorspelbaar blijft

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800" preserveAspectRatio="xMidYMid slice">'
            .'<defs><linearGradient id="g" x1="0" y1="0" x2="0" y2="1">'
            ."<stop offset=\"0\" stop-color=\"{$top}\"/><stop offset=\"1\" stop-color=\"{$bottom}\"/>"
            .'</linearGradient></defs><rect width="1200" height="800" fill="url(#g)"/>'
            .$body
            .'</svg>';
    }

    private function sceneHills(string $accent): string
    {
        return "<circle cx=\"900\" cy=\"260\" r=\"110\" fill=\"{$accent}\" opacity=\"0.95\"/>"
            .'<path d="M0 560 Q300 380 620 540 T1200 480 V800 H0Z" fill="#000" opacity="0.18"/>'
            .'<path d="M0 640 Q350 480 700 620 T1200 580 V800 H0Z" fill="#000" opacity="0.28"/>'
            .'<path d="M0 720 Q400 620 800 710 T1200 690 V800 H0Z" fill="#000" opacity="0.4"/>';
    }

    private function sceneNight(string $accent): string
    {
        $stars = '';
        for ($i = 0; $i < 45; $i++) {
            $stars .= sprintf('<circle cx="%d" cy="%d" r="%.1f" fill="#fff" opacity="%.2f"/>', mt_rand(20, 1180), mt_rand(20, 470), mt_rand(8, 26) / 10, mt_rand(40, 95) / 100);
        }

        return $stars
            ."<circle cx=\"930\" cy=\"210\" r=\"85\" fill=\"{$accent}\"/>"
            .'<path d="M0 620 L180 470 L330 600 L520 430 L720 610 L900 500 L1200 640 V800 H0Z" fill="#000" opacity="0.45"/>'
            .'<path d="M0 720 L220 620 L420 710 L640 600 L900 720 L1200 650 V800 H0Z" fill="#000" opacity="0.6"/>';
    }

    private function sceneCourt(string $accent): string
    {
        return '<g fill="none" stroke="#fff" stroke-width="6" opacity="0.65">'
            .'<rect x="200" y="160" width="800" height="480" rx="6"/><line x1="600" y1="160" x2="600" y2="640"/>'
            .'<line x1="200" y1="400" x2="1000" y2="400"/><line x1="360" y1="160" x2="360" y2="640"/><line x1="840" y1="160" x2="840" y2="640"/></g>'
            ."<circle cx=\"760\" cy=\"300\" r=\"28\" fill=\"{$accent}\"/>"
            .'<rect x="560" y="520" width="80" height="14" rx="7" fill="#fff" opacity="0.9" transform="rotate(-30 600 527)"/>';
    }

    private function sceneWaves(): string
    {
        $out = '';
        foreach ([[440, 0.16], [520, 0.22], [600, 0.3], [680, 0.4]] as [$y, $opacity]) {
            $out .= "<path d=\"M0 {$y} Q150 ".($y - 70).' 300 '.$y.' T600 '.$y.' T900 '.$y.' T1200 '.$y." V800 H0Z\" fill=\"#fff\" opacity=\"{$opacity}\"/>";
        }

        return $out;
    }

    private function sceneCity(string $accent): string
    {
        $out = "<circle cx=\"280\" cy=\"220\" r=\"90\" fill=\"{$accent}\" opacity=\"0.9\"/>";
        $x = 0;
        while ($x < 1200) {
            $width = mt_rand(70, 140);
            $height = mt_rand(180, 430);
            $out .= sprintf('<rect x="%d" y="%d" width="%d" height="%d" fill="#000" opacity="0.4"/>', $x, 800 - $height, $width - 8, $height);
            for ($wy = 800 - $height + 20; $wy < 770; $wy += 36) {
                for ($wx = $x + 14; $wx < $x + $width - 30; $wx += 30) {
                    if (mt_rand(1, 100) <= 45) {
                        $out .= sprintf('<rect x="%d" y="%d" width="12" height="16" fill="%s" opacity="0.85"/>', $wx, $wy, $accent);
                    }
                }
            }
            $x += $width;
        }

        return $out;
    }

    private function sceneParty(string $top, string $accent): string
    {
        $out = "<circle cx=\"600\" cy=\"380\" r=\"300\" fill=\"{$accent}\" opacity=\"0.18\"/>";
        $colors = ['#fff', $accent, $top, '#ffd166', '#06d6a0'];
        for ($i = 0; $i < 70; $i++) {
            $color = $colors[mt_rand(0, count($colors) - 1)];
            $x = mt_rand(30, 1170);
            $y = mt_rand(30, 770);
            $out .= mt_rand(0, 1)
                ? sprintf('<circle cx="%d" cy="%d" r="%d" fill="%s" opacity="0.85"/>', $x, $y, mt_rand(6, 14), $color)
                : sprintf('<rect x="%d" y="%d" width="%d" height="%d" fill="%s" opacity="0.85" transform="rotate(%d %d %d)"/>', $x, $y, mt_rand(10, 26), mt_rand(6, 12), $color, mt_rand(0, 180), $x, $y);
        }

        return $out;
    }

    private function sceneForest(string $accent): string
    {
        $out = "<circle cx=\"960\" cy=\"200\" r=\"80\" fill=\"{$accent}\" opacity=\"0.9\"/>";
        foreach ([[560, 0.25, 90], [640, 0.4, 110], [730, 0.55, 130]] as [$base, $opacity, $size]) {
            for ($x = -40; $x < 1240; $x += $size - 10) {
                $h = $size * 2 + mt_rand(0, 60);
                $out .= sprintf('<path d="M%d %d L%d %d L%d %d Z" fill="#000" opacity="%s"/>', $x, $base + 100, $x + $size / 2, $base + 100 - $h, $x + $size, $base + 100, $opacity);
            }
        }

        return $out;
    }

    private function sceneTable(string $accent): string
    {
        return '<circle cx="600" cy="400" r="260" fill="#fff" opacity="0.9"/>'
            .'<circle cx="600" cy="400" r="190" fill="none" stroke="#000" stroke-width="4" opacity="0.12"/>'
            ."<circle cx=\"600\" cy=\"400\" r=\"110\" fill=\"{$accent}\" opacity=\"0.9\"/>"
            .'<rect x="250" y="190" width="14" height="420" rx="7" fill="#fff" opacity="0.9"/>'
            .'<rect x="940" y="190" width="14" height="420" rx="7" fill="#fff" opacity="0.9"/>'
            .'<path d="M230 190 h54 v90 a27 27 0 0 1 -54 0Z" fill="#fff" opacity="0.9"/>';
    }

    private function sceneBowling(string $accent): string
    {
        $out = '';
        $rows = [[600], [540, 660], [480, 600, 720], [420, 540, 660, 780]];
        foreach ($rows as $r => $xs) {
            foreach ($xs as $x) {
                $y = 250 + $r * 70;
                $out .= sprintf('<ellipse cx="%d" cy="%d" rx="22" ry="34" fill="#fff"/><circle cx="%d" cy="%d" r="13" fill="#fff"/><rect x="%d" y="%d" width="30" height="5" fill="%s"/>', $x, $y, $x, $y - 40, $x - 15, $y - 10, $accent);
            }
        }

        return $out
            .'<circle cx="600" cy="640" r="70" fill="#000" opacity="0.7"/>'
            .'<circle cx="582" cy="620" r="9" fill="#fff" opacity="0.5"/><circle cx="612" cy="614" r="9" fill="#fff" opacity="0.5"/><circle cx="600" cy="640" r="9" fill="#fff" opacity="0.5"/>';
    }

    private function sceneCircles(string $accent): string
    {
        return '<circle cx="300" cy="300" r="220" fill="#fff" opacity="0.14"/>'
            .'<circle cx="640" cy="440" r="280" fill="#fff" opacity="0.12"/>'
            ."<circle cx=\"930\" cy=\"260\" r=\"140\" fill=\"{$accent}\" opacity=\"0.55\"/>"
            .'<circle cx="760" cy="640" r="90" fill="#fff" opacity="0.2"/>';
    }
}
