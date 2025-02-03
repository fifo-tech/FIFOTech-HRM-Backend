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
Use App\Http\Controllers\UserActivityController;
use App\Http\Controllers\DocumentController;





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

    // Role  API
    Route::post('/create-role',[RoleController::class,'createRole']);
    Route::get('/roles-list', [RoleController::class, 'getRoles']); // Fetch all roles
    Route::get('/role-details/{id}', [RoleController::class, 'getRoleById']); // Fetch a single role by ID
    Route::put('/update-role/{id}', [RoleController::class, 'updateRole']); // Update a role
    Route::delete('/delete-role/{id}', [RoleController::class, 'deleteRole']); // Delete a role


    // Department API
    Route::post('/create-department',[DepartmentController::class,'createDepartment']);
    Route::post('/update-department/{id}',[DepartmentController::class,'updateDepartment']);
    Route::get('/department-list',[DepartmentController::class,'getDepartments']);
    Route::get('/department-details/{id}',[DepartmentController::class,'getDepartmentByID']);
    Route::delete('/delete-department/{id}',[DepartmentController::class,'deleteDepartment']);

    // Designation API
    Route::post('/create-designation',[DesignationController::class,'createDesignation']);
    Route::get('/designation-list',[DesignationController::class,'designationList']);
    Route::get('/designation-details/{id}',[DesignationController::class,'getDesignationByID']);
    Route::post('/update-designation/{id}',[DesignationController::class,'updateDesignation']);
    Route::delete('/delete-designation/{id}',[DesignationController::class,'deleteDesignation']);

    // Employee API
    Route::post('/create-employee', [EmployeeController::class, 'createEmployee']);
    Route::get('/get-employee-details/{id}', [EmployeeController::class, 'getEmployeeDetails']);
    Route::post('/edit-employee/{id}', [EmployeeController::class, 'editEmployee']);
    Route::get('/employee-list', [EmployeeController::class, 'employeeList']);
    Route::post('/update-employee/{id}', [EmployeeController::class, 'updateEmployee']);
    Route::get('/total-employee', [EmployeeController::class, 'countEmployeesWithEmpId']);
    Route::delete('/delete-employee/{id}', [EmployeeController::class, 'deleteEmployee']);


    // Attendance API
    //Route::post('/create-attendance', [AttendanceController::class, 'createAttendance']);
    Route::get('/attendance-list', [AttendanceController::class, 'getAttendanceList']);
    Route::get('/employee-attendance-list', [AttendanceController::class, 'getSpecificEmployeeAttendance']);
    Route::post('/update-attendance', [AttendanceController::class, 'updateAttendanceByAdmin']);
    //Route::delete('/delete-attendance/{id}', [AttendanceController::class, 'deleteAttendance']);
    Route::get('/total-present', [AttendanceController::class, 'getTotalPresentAndAbsent']);
    Route::get('/in-out-time', [AttendanceController::class, 'inOutTime']);

    // User_activity_log API
    Route::post('/log-user-activity', [UserActivityController::class, 'logActivity']);
    Route::get('/user-activity-logs', [UserActivityController::class, 'getActivityLogs']);


    // Employee Profile ALL API
    Route::get('/employee-profile/{id}', [EmployeeController::class, 'specificEmployeeDetails']);



    // Documents API
    Route::post('/add-documents', [DocumentController::class, 'addDocument']);
    Route::delete('/delete-document/{id}', [DocumentController::class, 'deleteDocument']);
    Route::get('/get-documents/{id}', [DocumentController::class, 'getDocuments']);
    Route::get('download-document/{id}', [DocumentController::class, 'downloadDocument']);



    // Clock in and Clock Out API Routes
    Route::post('/clock-in',[AttendanceController::class,'clockIn']);
    Route::post('/clock-out',[AttendanceController::class,'clockOut']);



    // Logout route
    Route::delete('/logout', [UserController::class, 'logout']);
    Route::post('/change-password', [UserController::class, 'changePassword']);





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


