<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_program_id')->constrained('study_programs')->cascadeOnDelete();
            $table->string('academic_year');
            $table->string('label')->nullable();
            $table->dateTime('opens_at');
            $table->dateTime('closes_at');
            $table->unsignedInteger('max_applicants')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['study_program_id', 'academic_year']);
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('round_id')
                ->nullable()
                ->after('study_program_id')
                ->constrained('application_rounds')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['round_id']);
            $table->dropColumn('round_id');
        });

        Schema::dropIfExists('application_rounds');
    }
};
