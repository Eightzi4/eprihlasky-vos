<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_presets', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('icon')->default('description');
            $table->string('color_class')->default('text-gray-500');
            $table->string('checkpoint')->nullable();
            $table->string('state')->nullable();
            $table->foreignId('study_program_id')->nullable()->constrained('study_programs')->nullOnDelete();
            $table->foreignId('round_id')->nullable()->constrained('application_rounds')->nullOnDelete();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_presets');
    }
};
