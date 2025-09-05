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

        // Insert base lookup values for moods.
        DB::table('moods')->insert([
            ['id' => 1, 'label' => 'AWFUL'],
            ['id' => 2, 'label' => 'BAD'],
            ['id' => 3, 'label' => 'GOOD'],
            ['id' => 4, 'label' => 'GREAT'],
            ['id' => 5, 'label' => 'NORMAL'],
            ['id' => 6, 'label' => 'N/A'],
        ]);

        // Insert base lookup values for conditions.
        DB::table('conditions')->insert([
            ['id' => 1, 'label' => 'RAINY'],
            ['id' => 2, 'label' => 'SUNNY'],
            ['id' => 3, 'label' => 'WINDY'],
            ['id' => 4, 'label' => 'COLD'],
            ['id' => 5, 'label' => 'N/A'],
            ['id' => 6, 'label' => 'HOT TOPIC'],
            ['id' => 7, 'label' => 'CHARMING'],
        ]);

        // Insert base lookup values for strategies.
        DB::table('strategies')->insert([
            ['id' => 1, 'label' => 'FRONT'],
            ['id' => 2, 'label' => 'PACE'],
            ['id' => 3, 'label' => 'LATE'],
            ['id' => 4, 'label' => 'END'],
            ['id' => 5, 'label' => 'N/A'],
        ]);
    }
}
