<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\ApplicationRound;
use App\Models\ApplicationStatus;
use App\Models\StudyProgram;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ApplicationsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('cs_CZ');
        $program = StudyProgram::first();
        $round = ApplicationRound::where('study_program_id', $program->id)
            ->where('label', '1. kolo 2026')
            ->first();

        $schools = [
            ['name' => 'Gymnázium Uherské Hradiště', 'izo' => '60371731', 'type' => 'GYM', 'field' => 'Gymnázium', 'code' => '79-41-K/41'],
            ['name' => 'Obchodní akademie Uherské Hradiště', 'izo' => '60371740', 'type' => 'SOŠ', 'field' => 'Ekonomické lyceum', 'code' => '78-42-M/02'],
            ['name' => 'SOŠ Kroměříž', 'izo' => '60371758', 'type' => 'SOŠ', 'field' => 'Obchodní akademie', 'code' => '63-41-M/02'],
            ['name' => 'Gymnázium Zlín', 'izo' => '60371766', 'type' => 'GYM', 'field' => 'Gymnázium', 'code' => '79-41-K/81'],
            ['name' => 'Střední průmyslová škola Otrokovice', 'izo' => '60371774', 'type' => 'SOŠ', 'field' => 'Strojírenství', 'code' => '23-41-M/01'],
        ];

        for ($i = 0; $i < 25; $i++) {
            $firstName = $faker->firstName;
            $lastName = $faker->lastName;
            $email = $faker->unique()->safeEmail;

            $user = User::create([
                'name' => "$firstName $lastName",
                'email' => $email,
                'password' => Hash::make('heslo123'),
            ]);

            $school = $faker->randomElement($schools);
            $createdAt = Carbon::instance($faker->dateTimeBetween('-60 days', '-1 day'));

            $base = [
                'user_id' => $user->id,
                'study_program_id' => $program->id,
                'round_id' => $round->id,
                'application_status_id' => ApplicationStatus::idFor(ApplicationStatus::DRAFT),
                'status_changed_at' => $createdAt,
                'email' => $email,
                'evidence_number' => 'EV2026' . str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'gender' => $faker->randomElement(['Muž', 'Žena']),
                'birth_number' => $faker->numerify('######/####'),
                'birth_date' => $faker->dateTimeBetween('-26 years', '-18 years')->format('Y-m-d'),
                'birth_city' => $faker->city,
                'citizenship' => 'Česká republika',
                'phone' => $faker->phoneNumber,
                'street' => $faker->streetAddress,
                'city' => $faker->city,
                'zip' => preg_replace('/\s+/', '', $faker->postcode),
                'country' => 'Česká republika',
                'identity_verified' => true,
                'verified_fields' => ['first_name', 'last_name', 'birth_date', 'street', 'city', 'zip'],
                'gdpr_accepted' => true,
                'previous_school' => $school['name'],
                'izo' => $school['izo'],
                'school_type' => $school['type'],
                'previous_study_field' => $school['field'],
                'previous_study_field_code' => $school['code'],
                'graduation_year' => $faker->numberBetween(2020, 2025),
                'grade_average' => $faker->randomFloat(2, 1.00, 4.00),
                'half_year_grade_average' => $faker->randomFloat(2, 1.00, 4.00),
                'maturita_grade_average' => $faker->randomFloat(2, 1.00, 4.00),
                'bring_maturita_in_person' => false,
                'prev_study_info' => true,
                'specific_needs' => $faker->boolean(20) ? $faker->sentence : null,
                'note' => $faker->boolean(15) ? $faker->sentence : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];

            if ($i < 5) {
                Application::create($base);
                continue;
            }

            $submittedAt = $createdAt->copy()->addDays($faker->numberBetween(1, 10));
            $base = array_merge($base, [
                'submitted' => true,
                'submitted_at' => $submittedAt,
                'application_status_id' => ApplicationStatus::idFor(ApplicationStatus::SUBMITTED),
                'status_changed_at' => $submittedAt,
                'application_number' => '2026' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'updated_at' => $submittedAt,
            ]);

            if ($i < 11) {
                Application::create($base);
                continue;
            }

            $base['prev_study_info_accepted'] = true;

            if ($i < 16) {
                Application::create($base);
                continue;
            }

            $base['paid'] = true;

            if ($i < 21) {
                Application::create($base);
                continue;
            }

            $base['payment_accepted'] = true;
            Application::create($base);
        }
    }
}
