<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('worldable.tables.countries', 'world_countries'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('continent_id')->nullable();
            $table->unsignedBigInteger('subregion_id')->nullable();
            $table->string('name');
            $table->string('iso_code', 2)->unique();
            $table->string('iso_code_3', 3)->unique();
            $table->string('calling_code')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('continent_id');
            $table->index('subregion_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('worldable.tables.countries', 'world_countries'));
    }
};
