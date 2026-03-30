<?php

use App\Models\AuditActionType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_action_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('label', 100);
        });

        DB::table('audit_action_types')->insert([
            ['code' => AuditActionType::VIEW, 'label' => 'Zobrazení'],
            ['code' => AuditActionType::EDIT, 'label' => 'Úprava'],
            ['code' => AuditActionType::EXPORT, 'label' => 'Export'],
            ['code' => AuditActionType::DELETE, 'label' => 'Smazání'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_action_types');
    }
};
