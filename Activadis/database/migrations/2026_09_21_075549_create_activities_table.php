<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('signup_deadline')->nullable();
            $table->decimal('cost', 8, 2)->nullable();
            $table->decimal('personal_contribution', 8, 2)->nullable();
            $table->boolean('includes_food')->default(false);
            $table->text('food_description')->nullable();
            $table->unsignedInteger('min_participants')->nullable();
            $table->unsignedInteger('max_participants')->nullable();
            $table->boolean('allow_partner')->default(false);
            $table->boolean('is_external')->default(false);
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
