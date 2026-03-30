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
            $table->unsignedBigInteger('round_id')->nullable();
            $table->foreignId('application_status_id')->constrained('application_statuses');
            $table->timestamp('status_changed_at')->nullable();
            $table->timestamp('status_notified_at')->nullable();

            $table->boolean('identity_verified')->default(false);
            $table->boolean('prev_study_info')->default(false);
            $table->boolean('paid')->default(false);
            $table->boolean('gdpr_accepted')->default(false);
            $table->boolean('submitted')->default(false);

            $table->boolean('prev_study_info_accepted')->default(false);
            $table->timestamp('education_accepted_at')->nullable();
            $table->timestamp('education_notified_at')->nullable();
            $table->boolean('payment_accepted')->default(false);
            $table->timestamp('payment_accepted_at')->nullable();
            $table->timestamp('payment_notified_at')->nullable();

            $table->string('application_number')->nullable();
            $table->string('evidence_number')->nullable()->unique();
            $table->timestamp('submitted_at')->nullable();

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
            $table->decimal('half_year_grade_average', 4, 2)->nullable();
            $table->decimal('maturita_grade_average', 4, 2)->nullable();
            $table->boolean('bring_maturita_in_person')->default(false);

            $table->text('specific_needs')->nullable();
            $table->text('note')->nullable();

            $table->json('verified_fields')->nullable();

            $table->timestamps();

            $table->index(['application_status_id', 'updated_at']);
            $table->index(['round_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
