<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Aanmeldingen van externen: mensen zonder account die zich met naam en e-mail aanmelden.
     */
    public function up(): void
    {
        Schema::create('external_signups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->boolean('brings_partner')->default(false);
            $table->text('dietary_notes')->nullable();
            // Geheime code voor de uitschrijflink in de bevestigingsmail
            $table->string('token', 64)->unique();
            $table->timestamps();

            $table->unique(['activity_id', 'email']); // een e-mailadres kan maar een keer per activiteit
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_signups');
    }
};
