<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckIpAddress;
use Illuminate\Console\Scheduling\Schedule;
use App\Http\Middleware\TrackUserActivity;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // You can also register specific route middleware
        // Register the 'check.ip' middleware using the proper method
        $middleware->alias([
            'check.ip'=>CheckIpAddress::class,
            'track.activity' => TrackUserActivity::class,
        ]);
        // Add the middleware globally

//        $middleware->group('api', [
//            TrackUserActivity::class,
//        ]);
    })

    // Manually define middleware groups




    ->withSchedule(function (Schedule $schedule) {
        // Add your scheduled commands here
        $schedule->command('attendance:create-daily')
            ->timezone('Asia/Dhaka')
            ->dailyAt('00:00'); // Schedule to run at midnight

        // New: Fetch attendance logs from biometric device every minute
        $schedule->command('app:fetch-attendance-logs')
            ->timezone('Asia/Dhaka')
            ->everyMinute(); // Runs every minute
        // New: Check late count and notify employees if late > 2
        $schedule->command('app:check-employee-late-count')
            ->timezone('Asia/Dhaka')
            ->dailyAt('18:00'); // You can change the time as needed
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();


