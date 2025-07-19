<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use App\Models\User;
use App\Notifications\LateCountExceededNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CheckEmployeeLateCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-employee-late-count';

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
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $lateCounts = Attendance::whereNotNull('late')
            ->where('late', '!=', '00:00:00')
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->select('employee_id', DB::raw("COUNT(*) as total_late"))
            ->groupBy('employee_id')
            ->having('total_late', '>=', 3)
            ->get();

        foreach ($lateCounts as $late) {
            $lateCount = $late->total_late;

            $employee = User::find($late->employee_id);

            $alreadyNotified = $employee->notifications()
                ->where('type', LateCountExceededNotification::class)
                ->whereMonth('created_at', Carbon::now()->month)
                ->exists();

            if (!$alreadyNotified) {
                $employee->notify(new LateCountExceededNotification($lateCount));
                $this->info("Notified employee ID {$employee->id} for being late {$lateCount} times this month.");
            } else {
                $this->info("Employee ID {$employee->id} already notified this month.");
            }
        }


        $this->info("Late check completed.");
    }
}
