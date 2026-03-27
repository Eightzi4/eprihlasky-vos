<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('study_program_id')->constrained('study_programs');

            $table->string('status')->default('draft');

            $table->boolean('identity_verified')->default(false);
            $table->boolean('prev_study_info')->default(false);
            $table->boolean('paid')->default(false);
            $table->boolean('gdpr_accepted')->default(false);
            $table->boolean('submitted')->default(false);

            $table->boolean('prev_study_info_accepted')->default(false);
            $table->boolean('payment_accepted')->default(false);

            $table->string('application_number')->nullable();
            $table->string('evidence_number')->nullable()->unique();
            $table->timestamp('submitted_at')->nullable();

            $table->timestamp('education_locked_at')->nullable();
            $table->timestamp('payment_locked_at')->nullable();
            $table->timestamp('deadline_at')->nullable();

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('gender')->nullable();
            $table->string('birth_number')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('birth_city')->nullable();
            $table->string('citizenship')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('zip')->nullable();
            $table->string('country')->nullable();

            $table->string('previous_school')->nullable();
            $table->string('izo')->nullable();
            $table->string('school_type')->nullable();
            $table->string('previous_study_field')->nullable();
            $table->string('previous_study_field_code')->nullable();
            $table->string('graduation_year')->nullable();
            $table->decimal('grade_average', 4, 2)->nullable();

            $table->text('specific_needs')->nullable();
            $table->text('note')->nullable();

            $table->json('verified_fields')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
