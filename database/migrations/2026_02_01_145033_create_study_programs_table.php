<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('study_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('degree')->default('DiS.');
            $table->string('form')->default('Prezenční');
            $table->string('length')->default('3 roky');
            $table->string('language')->default('Čeština');
            $table->string('location')->default('Uherské Hradiště');
            $table->string('tuition_fee')->nullable();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('info_url', 2048)->default('https://www.oauh.cz/ekonomicko-pravni-cinnost-68-41-n-03.htm');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_programs');
    }
};
