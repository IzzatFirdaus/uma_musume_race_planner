<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActivityLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('activity_log')->truncate();

        DB::table('activity_log')->insert([
            ['description' => 'New sample plan created: [pf. Winning Equation…] Biwa Hayahide Plan', 'icon_class' => 'bi-person-plus'],
            ['description' => 'New sample plan created: [Wild Top Gear] Vodka Plan', 'icon_class' => 'bi-person-plus'],
            ['description' => 'New sample plan created: [Wild Top Gear] Vodka Plan', 'icon_class' => 'bi-person-plus'],
            ['description' => 'New sample plan created: [Peak Blue] Daiwa Scarlet Plan', 'icon_class' => 'bi-person-plus'],
            ['description' => 'New sample plan created: [Beyond the Horizon] Tokai Teio Plan', 'icon_class' => 'bi-person-plus'],
            ['description' => 'New sample plan created: [Bestest Prize 𝆕] Haru Urara Plan', 'icon_class' => 'bi-person-plus'],
            ['description' => 'New sample plan created: [Bestest Prize 𝆕] Haru Urara Plan', 'icon_class' => 'bi-person-plus'],
            ['description' => 'New sample plan created: [El☆Número 1] El Condor Pasa Plan', 'icon_class' => 'bi-person-plus'],
        ]);
    }
}
