<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('worldable.tables.timezones', 'world_timezones'), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('zone_name')->unique();
            $table->integer('gmt_offset');
            $table->string('gmt_offset_name');
            $table->string('abbreviation');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('worldable.tables.timezones', 'world_timezones'));
    }
};
