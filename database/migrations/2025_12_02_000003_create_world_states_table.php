<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('worldable.tables.states', 'world_states'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->string('name');
            $table->string('code')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('country_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('worldable.tables.states', 'world_states'));
    }
};
