<?php

namespace Database\Seeders;

use App\Models\NutritionLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NutritionLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        NutritionLog::factory()->count(10)->create();
    }
}
