<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\TimeCard;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class TimeCardSeeder extends Seeder
{
    public function run(): void
    {
        if (! Employee::query()->exists()) {
            $this->call(EmployeeSeeder::class);
        }

        $today = today();

        foreach (Employee::query()->lazyById(100) as $employee) {
            TimeCard::factory()
                ->for($employee)
                ->count(60)
                ->sequence(fn (Sequence $sequence) => [
                    'date' => $today->copy()->subWeekdays($sequence->index + 1)->toDateString(),
                ])
                ->create();
        }
    }
}
