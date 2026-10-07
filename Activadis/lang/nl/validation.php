<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validatieberichten
    |--------------------------------------------------------------------------
    |
    | Nederlandse vertalingen van de standaard validatieberichten. Regels die
    | hier ontbreken vallen terug op de Engelse versie (fallback_locale).
    |
    */

    'accepted' => ':Attribute moet geaccepteerd worden.',
    'after' => ':Attribute moet na :date liggen.',
    'after_or_equal' => ':Attribute moet op of na :date liggen.',
    'array' => ':Attribute moet een lijst zijn.',
    'before' => ':Attribute moet vóór :date liggen.',
    'before_or_equal' => ':Attribute moet op of vóór :date liggen.',
    'between' => [
        'array' => ':Attribute moet tussen :min en :max items bevatten.',
        'file' => ':Attribute moet tussen :min en :max kilobytes zijn.',
        'numeric' => ':Attribute moet tussen :min en :max liggen.',
        'string' => ':Attribute moet tussen :min en :max tekens bevatten.',
    ],
    'boolean' => ':Attribute moet ja of nee zijn.',
    'confirmed' => 'De bevestiging van :attribute komt niet overeen.',
    'date' => ':Attribute moet een geldige datum zijn.',
    'date_format' => ':Attribute moet het formaat :format hebben.',
    'decimal' => ':Attribute moet :decimal decimalen hebben.',
    'different' => ':Attribute en :other moeten verschillen.',
    'email' => ':Attribute moet een geldig e-mailadres zijn.',
    'exists' => 'De gekozen :attribute is ongeldig.',
    'gt' => [
        'array' => ':Attribute moet meer dan :value items bevatten.',
        'file' => ':Attribute moet groter zijn dan :value kilobytes.',
        'numeric' => ':Attribute moet groter zijn dan :value.',
        'string' => ':Attribute moet meer dan :value tekens bevatten.',
    ],
    'gte' => [
        'array' => ':Attribute moet :value of meer items bevatten.',
        'file' => ':Attribute moet :value kilobytes of groter zijn.',
        'numeric' => ':Attribute moet groter dan of gelijk aan :value zijn.',
        'string' => ':Attribute moet :value of meer tekens bevatten.',
    ],
    'in' => 'De gekozen :attribute is ongeldig.',
    'integer' => ':Attribute moet een geheel getal zijn.',
    'lt' => [
        'array' => ':Attribute moet minder dan :value items bevatten.',
        'file' => ':Attribute moet kleiner zijn dan :value kilobytes.',
        'numeric' => ':Attribute moet kleiner zijn dan :value.',
        'string' => ':Attribute moet minder dan :value tekens bevatten.',
    ],
    'lte' => [
        'array' => ':Attribute mag niet meer dan :value items bevatten.',
        'file' => ':Attribute moet :value kilobytes of kleiner zijn.',
        'numeric' => ':Attribute moet kleiner dan of gelijk aan :value zijn.',
        'string' => ':Attribute mag niet meer dan :value tekens bevatten.',
    ],
    'max' => [
        'array' => ':Attribute mag niet meer dan :max items bevatten.',
        'file' => ':Attribute mag niet groter zijn dan :max kilobytes.',
        'numeric' => ':Attribute mag niet groter zijn dan :max.',
        'string' => ':Attribute mag niet meer dan :max tekens bevatten.',
    ],
    'min' => [
        'array' => ':Attribute moet minimaal :min items bevatten.',
        'file' => ':Attribute moet minimaal :min kilobytes zijn.',
        'numeric' => ':Attribute moet minimaal :min zijn.',
        'string' => ':Attribute moet minimaal :min tekens bevatten.',
    ],
    'numeric' => ':Attribute moet een getal zijn.',
    'required' => ':Attribute is verplicht.',
    'required_if' => ':Attribute is verplicht als :other :value is.',
    'required_with' => ':Attribute is verplicht als :values ingevuld is.',
    'same' => ':Attribute en :other moeten overeenkomen.',
    'string' => ':Attribute moet tekst zijn.',
    'unique' => ':Attribute is al in gebruik.',
    'url' => ':Attribute moet een geldige URL zijn.',

    /*
    |--------------------------------------------------------------------------
    | Veldnamen
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'title' => 'titel',
        'description' => 'omschrijving',
        'starts_at' => 'begintijd',
        'ends_at' => 'eindtijd',
        'signup_deadline' => 'inschrijfdeadline',
        'personal_contribution' => 'eigen bijdrage',
        'includes_food' => 'inclusief eten',
        'food_description' => 'omschrijving eten',
        'min_participants' => 'minimaal aantal deelnemers',
        'max_participants' => 'maximaal aantal deelnemers',
        'allow_partner' => '+1 meenemen',
        'is_external' => 'externe activiteit',
        'publish' => 'publiceren',
    ],

];
