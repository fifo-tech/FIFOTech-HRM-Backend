<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Middleware\CheckIpAddress;





// Admin SignUp and Login
Route::post('/signup',[UserController::class,'signUp']);
Route::post('/login', [UserController::class, 'login']);


Route::middleware([
    'auth:sanctum'
])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Add more protected routes here

    // Role Add API
    Route::post('/create-role',[RoleController::class,'createRole']);

    // Department API
    Route::post('/create-department',[DepartmentController::class,'createDepartment']);
    Route::post('/update-department/{id}',[DepartmentController::class,'updateDepartment']);
    Route::get('/department-list',[DepartmentController::class,'getDepartments']);
    Route::delete('/delete-department/{id}',[DepartmentController::class,'deleteDepartment']);

    // Designation API
    Route::post('/create-designation',[DesignationController::class,'createDesignation']);
    Route::get('/designation-list',[DesignationController::class,'designationList']);
    Route::delete('/delete-designation/{id}',[DesignationController::class,'deleteDesignation']);

    // Employee API
    Route::post('/create-employee', [EmployeeController::class, 'createEmployee']);
    Route::get('/employee-list', [EmployeeController::class, 'employeeList']);
    Route::post('/update-employee/{id}', [EmployeeController::class, 'updateEmployee']);
    Route::get('/total-employee', [EmployeeController::class, 'countEmployeesWithEmpId']);
    Route::delete('/delete-employee/{id}', [EmployeeController::class, 'deleteEmployee']);


    // Attendance API
    //Route::post('/create-attendance', [AttendanceController::class, 'createAttendance']);
    Route::get('/attendance-list', [AttendanceController::class, 'getAttendanceList']);
    Route::post('/update-attendance', [AttendanceController::class, 'updateAttendanceByAdmin']);
    //Route::delete('/delete-attendance/{id}', [AttendanceController::class, 'deleteAttendance']);
    Route::get('/total-present', [AttendanceController::class, 'getTotalPresentAndAbsent']);







    // Logout route
    Route::delete('/logout', [UserController::class, 'logout']);





//
//    // Attendance Routes (Apply IP Address check here)
//    Route::middleware(['check.ip'])->group(function () {
//        Route::post('/create-attendance', [AttendanceController::class, 'createAttendance']);
//        Route::get('/attendance-list', [AttendanceController::class, 'getAttendanceList']);
//        Route::post('/update-attendance', [AttendanceController::class, 'updateAttendance']);
//        Route::delete('/delete-attendance/{id}', [AttendanceController::class, 'deleteAttendance']);
//        Route::get('/total-present', [AttendanceController::class, 'getTotalPresentAndAbsent']);
//    });
//
//




});


