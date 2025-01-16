<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use App\Models\Employee;

class AttendanceCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:create-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
//        $date = now()->format('Y-m-d'); // Get today's date
//
//        // Get all employees
//        $employees = Employee::all();
//
//        foreach ($employees as $employee) {
//            // Create a new attendance record if not exists for today
//            Attendance::firstOrCreate([
//                'employee_id' => $employee->id,
//                'date' => $date, // Set the date as today's date
//            ]);
//        }
//
//        $this->info('Daily attendance records created.');
    }
}
