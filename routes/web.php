<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Mail;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});


Route::get('/send-test-mail', function () {
    Mail::raw('This is a test email from your HRM system.', function ($message) {
        $message->to('mostafizurrahmanripon03@gmail.com')
            ->subject('Test Mail');
    });

    return 'Test mail sent!';
});
