<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('worldable.tables.continents', 'world_continents'), function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 2)->unique(); // AF, EU, AS, NA, SA, OC, AN
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('worldable.tables.continents', 'world_continents'));
    }
};
