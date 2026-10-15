
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('naam_library', function (Blueprint $table) {
            $table->id();

            $table->string('name', 150);
            $table->string('name_hindi', 150)->nullable();
            $table->string('name_punjabi', 150)->nullable();

            $table->string('slug', 180)->unique();
            $table->text('description')->nullable();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('naam_library')
                ->nullOnDelete();

            $table->string('icon', 255)->nullable();

            $table->string('status', 20)->default('active');
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['parent_id', 'status', 'sort_order']);
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('naam_library');
    }
};
