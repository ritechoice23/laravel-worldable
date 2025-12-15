<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('worldable.tables.languages', 'world_languages'), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('native_name')->nullable();
            $table->string('iso_code', 2)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('worldable.tables.languages', 'world_languages'));
    }
};
