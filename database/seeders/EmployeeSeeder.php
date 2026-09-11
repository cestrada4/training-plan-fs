<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        Employee::factory()->count(90)->create();
        Employee::factory()->inactive()->count(10)->create();
    }
}
