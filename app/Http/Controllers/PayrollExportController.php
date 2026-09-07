<?php

/**
 * Day 6 seed — Timecard payroll export endpoint
 *
 * Seeded into the training repo on Day 1. This is deliberately the SAME scenario Chris was asked
 * about in his mock technical interview (2026-08-19) — exporting every employee's total hours for a
 * pay period by looping through employees and querying inside the loop. He described the correct
 * fix in the interview at a conceptual level ("select all employees... instead of looping and
 * querying per employee") but self-reported low SQL/database-design fluency. Day 6 is where he
 * actually implements the fix and defends it with a query-plan / row-count argument, not just says it.
 *
 * Task (Day 6 day card): identify the N+1 query, fix it with a single batched query, and add an index
 * if the fix still requires one. Submit: PR + a short note showing query count before/after (e.g. via
 * Laravel Debugbar, `DB::listen`, or `EXPLAIN` output) and the migration for any index added.
 */

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\TimeCard;
use Illuminate\Http\Request;

class PayrollExportController extends Controller
{
    public function exportPeriodTotals(Request $request)
    {
        $periodStart = $request->input('period_start');
        $periodEnd = $request->input('period_end');

        $rows = Employee::query()
        ->join('time_cards', 'time_cards.employee_id', '=', 'employees.id')
        ->select('employees.id','employees.name AS employee')
        ->selectRaw('SUM(time_cards.total_hours) as all_hours')
        ->whereBetween('date', [$periodStart, $periodEnd])
        ->groupBy('employees.id','employees.name')
        ->get();

        $rows = $rows->map(function ($row) {
            return [
                'employee' => $row->employee,
                'total_hours' => round($row->all_hours, 2)
            ];
        });

        return response()->json($rows);
    }

    public function exportPeriodTotalsOld(Request $request)
    {
        $periodStart = $request->input('period_start');
        $periodEnd = $request->input('period_end');

        $employees = Employee::where('status', 'active')->get();

        $rows = [];

        foreach ($employees as $employee) {
            $timeCards = TimeCard::where('employee_id', $employee->id)
                ->whereBetween('date', [$periodStart, $periodEnd])
                ->get();

            $totalHours = 0;
            foreach ($timeCards as $card) {
                $totalHours += $card->total_hours;
            }

            $rows[] = [
                'employee' => $employee->name,
                'total_hours' => round($totalHours, 2),
            ];
        }

        return response()->json($rows);
    }
}

/**
 * Schema reference (for context — do not modify migrations unless your fix requires a new index):
 *
 * Schema::create('time_cards', function (Blueprint $table) {
 *     $table->id();
 *     $table->unsignedBigInteger('employee_id');
 *     $table->date('date');
 *     $table->decimal('total_hours', 5, 2);
 *     $table->timestamps();
 * });
 */