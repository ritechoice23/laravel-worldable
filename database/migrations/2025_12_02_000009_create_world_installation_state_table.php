<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('world_installation_state')) {
            Schema::create('world_installation_state', function (Blueprint $table) {
                $table->id();
                $table->string('component')->unique();
                $table->boolean('installed')->default(true);
                $table->timestamp('installed_at')->useCurrent();
                $table->timestamp('last_seeded_at')->nullable();
                $table->integer('record_count')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('world_installation_state');
    }
};
