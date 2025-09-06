<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LookupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // To safely reseed lookup tables, use delete() instead of truncate().
        // Truncate is not allowed if the table is referenced by a foreign key constraint.
        // Delete preserves the table structure and works even with FK constraints.

        DB::table('moods')->delete();
        DB::table('conditions')->delete();
        DB::table('strategies')->delete();

        // Insert updated moods
        DB::table('moods')->insert([
            ['id' => 1, 'label' => 'AWFUL'],
            ['id' => 2, 'label' => 'BAD'],
            ['id' => 3, 'label' => 'NORMAL'],
            ['id' => 4, 'label' => 'GOOD'],
            ['id' => 5, 'label' => 'GREAT'],
        ]);

        // Insert updated conditions
        DB::table('conditions')->insert([
            ['id' => 1, 'label' => 'MIGRAINE'],
            ['id' => 2, 'label' => 'DRY SKIN'],
            ['id' => 3, 'label' => 'INSOMNIA'],
            ['id' => 4, 'label' => 'SLOW METABOLISM'],
            ['id' => 5, 'label' => 'SLACKER'],
            ['id' => 6, 'label' => 'UNDER THE WEATHER'],
            ['id' => 7, 'label' => 'SPRING BUD'],
            ['id' => 8, 'label' => 'SUSPICIOUS CLOUDS'],
        ]);

        // Insert updated strategies
        DB::table('strategies')->insert([
            ['id' => 1, 'label' => 'FRONT'],
            ['id' => 2, 'label' => 'PACE'],
            ['id' => 3, 'label' => 'LATE'],
            ['id' => 4, 'label' => 'END'],
        ]);
    }
}
