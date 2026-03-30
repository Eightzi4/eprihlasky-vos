<?php

use App\Models\ApplicationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('label', 100);
        });

        DB::table('application_statuses')->insert([
            ['code' => ApplicationStatus::DRAFT, 'label' => 'Rozpracováno'],
            ['code' => ApplicationStatus::SUBMITTED, 'label' => 'Odesláno'],
            ['code' => ApplicationStatus::COMPLETED_IN_TIME, 'label' => 'Dokončeno včas'],
            ['code' => ApplicationStatus::INCOMPLETE_AFTER_DEADLINE, 'label' => 'Nedokončeno včas'],
            ['code' => ApplicationStatus::MOVED_TO_FURTHER_ROUND, 'label' => 'Přesunuto do dalšího kola'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('application_statuses');
    }
};
