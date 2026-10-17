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
        Schema::create('sankalps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('naam_library_id')
                ->constrained('naam_library')
                ->restrictOnDelete();

            $table->unsignedInteger('target_count');
            $table->unsignedInteger('completed_count')->default(0);

            $table->string('status', 20)->default('active');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sankalps');
    }
};
