<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\AttendanceController;

class FetchAttendanceLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-attendance-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch attendance logs from biometric API and update database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Call the AttendanceController method to fetch and update the logs
        $controller = new AttendanceController();
        $controller->fetchAttendanceLogs();
    }
}
