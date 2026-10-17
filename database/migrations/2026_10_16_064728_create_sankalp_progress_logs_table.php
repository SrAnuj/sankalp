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
        Schema::create('sankalp_progress_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sankalp_id')
                ->constrained('sankalps')
                ->cascadeOnDelete();

            $table->date('progress_date');
            $table->unsignedInteger('count')->default(0);

            $table->timestamps();

            $table->unique(['sankalp_id', 'progress_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sankalp_progress_logs');
    }
};
