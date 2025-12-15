<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('worldable.tables.cities', 'world_cities'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->string('name');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();

            $table->index('country_id');
            $table->index('state_id');
            $table->index(['country_id', 'state_id']);
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('worldable.tables.cities', 'world_cities'));
    }
};
