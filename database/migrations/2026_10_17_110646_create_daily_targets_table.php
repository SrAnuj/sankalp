<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_targets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sankalp_id')
                ->constrained('sankalps')
                ->cascadeOnDelete();

            $table->unsignedInteger('target_count');

            $table->date('effective_from');

            $table->date('effective_until')->nullable();

            $table->timestamps();

            $table->index([
                'sankalp_id',
                'effective_from',
                'effective_until',
            ], 'daily_targets_sankalp_dates_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_targets');
    }
};
