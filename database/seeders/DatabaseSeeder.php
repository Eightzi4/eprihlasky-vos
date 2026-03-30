<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\ApplicationRound;
use App\Models\StudyProgram;
use App\Models\WebsiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Admin::create([
            'name' => 'Hlavní Admin',
            'email' => 'admin@oauh.cz',
            'password' => Hash::make('admin123'),
            'is_main_admin' => true,
        ]);

        WebsiteSetting::create(WebsiteSetting::defaults());

        $program = StudyProgram::create([
            'name' => 'Ekonomicko-právní činnost',
            'code' => '63-41-N/04',
            'degree' => 'DiS. (Diplomovaný specialista)',
            'form' => 'Prezenční',
            'length' => '3 roky',
            'tuition_fee' => '2 500 Kč / rok',
            'description' => 'Komplexní vzdělávací program zaměřený na propojení ekonomických znalostí s právním povědomím. Absolventi získají kvalifikaci pro práci ve státní správě, v právních kancelářích nebo v managementu firem.',
            'image_path' => 'https://www.oauh.cz/content/subjects/6.jpg',
            'info_url' => StudyProgram::DEFAULT_INFO_URL,
            'is_active' => true,
        ]);

        ApplicationRound::create([
            'study_program_id' => $program->id,
            'academic_year' => '2025/2026',
            'label' => '1. kolo 2025',
            'opens_at' => '2025-01-01 00:00:00',
            'closes_at' => '2025-03-31 23:59:59',
            'completion_deadline_at' => '2025-05-04 23:59:59',
            'max_applicants' => null,
            'is_active' => true,
        ]);

        ApplicationRound::create([
            'study_program_id' => $program->id,
            'academic_year' => '2026/2027',
            'label' => '1. kolo 2026',
            'opens_at' => '2026-03-01 00:00:00',
            'closes_at' => '2026-04-30 23:59:59',
            'completion_deadline_at' => '2026-05-04 23:59:59',
            'max_applicants' => null,
            'is_active' => true,
        ]);

        ApplicationRound::create([
            'study_program_id' => $program->id,
            'academic_year' => '2026/2027',
            'label' => '2. kolo 2026',
            'opens_at' => '2026-05-01 00:00:00',
            'closes_at' => '2026-06-30 23:59:59',
            'completion_deadline_at' => '2026-06-30 23:59:59',
            'max_applicants' => null,
            'is_active' => true,
        ]);

        $this->call(ApplicationsSeeder::class);
    }
}
