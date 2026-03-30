<?php

namespace Database\Seeders;

use App\Models\WebsiteSetting;
use Illuminate\Database\Seeder;

class WebsiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        WebsiteSetting::query()->firstOrCreate([], WebsiteSetting::defaults());
    }
}
