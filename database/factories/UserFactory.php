<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = \App\Models\User::class;

    private static array $femaleFirstNames = [
        'Jana',
        'Lucie',
        'Petra',
        'Tereza',
        'Michaela',
        'Veronika',
        'Eliška',
        'Klára',
        'Martina',
        'Monika',
        'Alena',
        'Lenka',
        'Eva',
        'Hana',
        'Simona',
        'Barbora',
        'Renata',
        'Markéta',
        'Andrea',
        'Kateřina',
    ];

    private static array $maleFirstNames = [
        'Tomáš',
        'Martin',
        'Ondřej',
        'Jakub',
        'Radek',
        'Michal',
        'Jan',
        'Petr',
        'Lukáš',
        'Pavel',
        'Jiří',
        'Marek',
        'Vojtěch',
        'David',
        'Miroslav',
        'Zdeněk',
        'Stanislav',
        'Roman',
        'Karel',
        'Filip',
    ];

    private static array $femaleLastNames = [
        'Nováková',
        'Svobodová',
        'Horáková',
        'Marková',
        'Blažková',
        'Čermáková',
        'Pospíšilová',
        'Veselá',
        'Procházková',
        'Dvořáková',
        'Novotná',
        'Kratochvílová',
        'Mazánková',
        'Šimánková',
        'Blahnová',
        'Kopečná',
        'Navrátilová',
        'Pokorná',
    ];

    private static array $maleLastNames = [
        'Novák',
        'Procházka',
        'Dvořák',
        'Krejčí',
        'Beneš',
        'Fiala',
        'Šimánek',
        'Navrátil',
        'Pokorný',
        'Blažek',
        'Kratochvíl',
        'Kopec',
        'Mazánek',
        'Veselý',
        'Čermák',
        'Pospíšil',
        'Horák',
        'Marek',
        'Svoboda',
    ];

    private static array $cities = [
        'Praha',
        'Brno',
        'Ostrava',
        'Plzeň',
        'Olomouc',
        'Liberec',
        'Hradec Králové',
        'České Budějovice',
        'Zlín',
        'Pardubice',
        'Jihlava',
        'Kladno',
        'Most',
        'Teplice',
        'Uherské Hradiště',
        'Kroměříž',
        'Znojmo',
        'Hodonín',
        'Přerov',
    ];

    public function definition(): array
    {
        $gender    = $this->faker->randomElement(['male', 'female']);
        $firstName = $gender === 'female'
            ? $this->faker->randomElement(self::$femaleFirstNames)
            : $this->faker->randomElement(self::$maleFirstNames);
        $lastName  = $gender === 'female'
            ? $this->faker->randomElement(self::$femaleLastNames)
            : $this->faker->randomElement(self::$maleLastNames);

        $slug  = \Illuminate\Support\Str::ascii(strtolower("{$firstName}.{$lastName}"));
        $email = "{$slug}@" . $this->faker->randomElement(['email.cz', 'gmail.com', 'seznam.cz', 'centrum.cz']);

        return [
            'name'     => "{$firstName} {$lastName}",
            'email'    => $email,
            'password' => Hash::make('heslo123'),
        ];
    }

    public static function czechProfile(string $gender): array
    {
        $faker = \Faker\Factory::create('cs_CZ');

        if ($gender === 'female') {
            $first = $faker->randomElement(self::$femaleFirstNames);
            $last  = $faker->randomElement(self::$femaleLastNames);
        } else {
            $first = $faker->randomElement(self::$maleFirstNames);
            $last  = $faker->randomElement(self::$maleLastNames);
        }

        $city  = $faker->randomElement(self::$cities);
        $slug  = \Illuminate\Support\Str::ascii(strtolower("{$first}.{$last}"));
        $email = "{$slug}" . $faker->numberBetween(1, 99) . "@"
            . $faker->randomElement(['email.cz', 'gmail.com', 'seznam.cz']);

        return compact('first', 'last', 'email', 'city', 'gender');
    }
}
