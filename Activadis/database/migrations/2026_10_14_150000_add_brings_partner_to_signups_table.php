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
        Schema::table('signups', function (Blueprint $table) {
            // Een +1 telt niet mee voor het maximum aantal deelnemers, we onthouden alleen dat iemand er een meeneemt.
            $table->boolean('brings_partner')->default(false)->after('activity_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signups', function (Blueprint $table) {
            $table->dropColumn('brings_partner');
        });
    }
};
