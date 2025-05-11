<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Translation;
class TranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $chunkSize = 10000;
        $total = 100000;

        for ($i = 0; $i < $total / $chunkSize; $i++) {
            Translation::factory()->count($chunkSize)->create();
        }
    }
}
