<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\TimeCard;
use Illuminate\Foundation\Testing\Attributes\SetUp;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PayrollExportControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private function generateFakeEmployees($periodStart, $periodEnd, $numberOfEmployees): array
    {
        $expectedTotals = [];

        foreach (range(1, $numberOfEmployees) as $employeeNumber) {
            $employee = Employee::create([
                'name' => $this->faker->unique()->name(),
                'status' => 'active',
            ]);

            $hours = [];

            foreach (range(1, 3) as $cardNumber) {
                $totalHours = $this->faker->randomFloat(2, 1, 8);
                $hours[] = $totalHours;

                TimeCard::create([
                    'employee_id' => $employee->id,
                    'date' => $this->faker->dateTimeBetween($periodStart, $periodEnd)->format('Y-m-d'),
                    'total_hours' => $totalHours,
                ]);
            }

            $expectedTotals[$employee->name] = round(array_sum($hours), 2);
        }

        return $expectedTotals;
    }

    private function runReturnsEmployeeHoursReportAssertions(TestResponse $response, int $numberOfEmployees, array $expectedTotals, array $queries, int $expectedQueries): void
    {
        $response
            ->assertOk()
            ->assertJsonCount($numberOfEmployees)
            ->assertJsonStructure([
                '*' => [
                    'employee',
                    'total_hours',
                ],
            ]);

        foreach ($response->json() as $row) {
            $this->assertArrayHasKey($row['employee'], $expectedTotals);
            $this->assertEquals($expectedTotals[$row['employee']], (float) $row['total_hours']);
        }

        $this->assertCount(count($queries), $queries);
    }

    public function test_it_returns_employee_hours_report(): void
    {

        $periodStart = '2026-09-01';
        $periodEnd = '2026-09-30';
        $numberOfEmployees = 10;
        $expectedTotals = $this->generateFakeEmployees($periodStart, $periodEnd, $numberOfEmployees);
        $expectedQueries = 1;

        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });


        $response = $this->getJson('/time-cards/export-period-totals?' . http_build_query([
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
        ]));

        $this->runReturnsEmployeeHoursReportAssertions($response, $numberOfEmployees, $expectedTotals, $queries, $expectedQueries);
    }

        public function test_it_returns_employee_hours_report_old(): void
    {

        $periodStart = '2026-09-01';
        $periodEnd = '2026-09-30';
        $numberOfEmployees = 10;
        $expectedTotals = $this->generateFakeEmployees($periodStart, $periodEnd, $numberOfEmployees);
        $expectedQueries = 11;

        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });


        $response = $this->getJson('/time-cards/export-period-totals-old?' . http_build_query([
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
        ]));

        $this->runReturnsEmployeeHoursReportAssertions($response, $numberOfEmployees, $expectedTotals, $queries, $expectedQueries);
    }
}
