<?php

namespace Database\Seeders;

use App\Models\DashboardPreset;
use Illuminate\Database\Seeder;

class DashboardPresetsSeeder extends Seeder
{
    public function run(): void
    {
        $presets = [
            [
                'label'       => 'Celkem přihlášek',
                'icon'        => 'description',
                'color_class' => 'text-gray-500',
                'checkpoint'  => null,
                'state'       => null,
                'sort_order'  => 0,
            ],
            [
                'label'       => 'Odeslané přihlášky',
                'icon'        => 'check_circle',
                'color_class' => 'text-green-500',
                'checkpoint'  => 'submitted',
                'state'       => 'complete',
                'sort_order'  => 1,
            ],
            [
                'label'       => 'Rozpracované',
                'icon'        => 'edit_note',
                'color_class' => 'text-amber-500',
                'checkpoint'  => 'submitted',
                'state'       => 'incomplete',
                'sort_order'  => 2,
            ],
            [
                'label'       => 'Čeká na schválení platby',
                'icon'        => 'payments',
                'color_class' => 'text-blue-500',
                'checkpoint'  => 'payment',
                'state'       => 'pending',
                'sort_order'  => 3,
            ],
        ];

        foreach ($presets as $p) {
            DashboardPreset::create($p);
        }
    }
}
