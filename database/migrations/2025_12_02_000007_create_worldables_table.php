<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('worldable.tables.worldables', 'worldables'), function (Blueprint $table) {
            $table->id();
            $table->morphs('worldable');
            $table->unsignedBigInteger('world_entity_id');
            $table->string('world_entity_type');
            $table->string('group')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['worldable_type', 'worldable_id', 'world_entity_type']);
            $table->index(['world_entity_id', 'world_entity_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('worldable.tables.worldables', 'worldables'));
    }
};
