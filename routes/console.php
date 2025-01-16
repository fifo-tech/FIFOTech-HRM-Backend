<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\AttendanceCreate;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();



Artisan::command('attendance:create-daily', function () {
    $date = now()->format('Y-m-d'); // Get today's date

    // Get all employees
    $employees = Employee::all();

    foreach ($employees as $employee) {
        // Create a new attendance record if not exists for today
        Attendance::firstOrCreate([
            'employee_id' => $employee->id,
            'date' => $date, // Set the date as today's date
        ]);
    }

    $this->info('Daily attendance records created.');
})->daily(); // Schedule it to run daily


//
//// Schedule Attendance Create Command
//Schedule::command(AttendanceCreate::class)->daily();
