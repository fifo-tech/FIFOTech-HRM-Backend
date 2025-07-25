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
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\LateTypeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\SalaryStructureController;
use App\Http\Controllers\PayrollAdjustmentController;
use App\Http\Controllers\NotificationController;






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
    Route::get('/roles-list', [RoleController::class, 'getRoles']);
    Route::get('/role-details/{id}', [RoleController::class, 'getRoleById']);
    Route::post('/update-role/{id}', [RoleController::class, 'updateRole']);
    Route::delete('/delete-role/{id}', [RoleController::class, 'deleteRole']);


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
    Route::get('/department-wise-employee-ids', [EmployeeController::class, 'getDepartmentWiseEmployeeIds']);
    Route::get('/employee-upcoming-birthdays', [EmployeeController::class, 'getUpcomingBirthdays']);



    // Attendance API
    //Route::post('/create-attendance', [AttendanceController::class, 'createAttendance']);
    Route::get('/digital-attendance-list', [AttendanceController::class, 'getAttendanceFromDevice']);
    Route::get('/fetch-attendance-logs', [AttendanceController::class, 'fetchAttendanceLogs']);
    Route::get('/attendance-list', [AttendanceController::class, 'getAttendanceList']);
    Route::get('/present-daily-list', [AttendanceController::class, 'getPresentAttendanceList']);
    Route::get('/absent-daily-list', [AttendanceController::class, 'getAbsentAttendanceList']);
    Route::get('/employee-attendance-list', [AttendanceController::class, 'getSpecificEmployeeAttendance']);
    Route::post('/update-attendance', [AttendanceController::class, 'updateAttendanceByAdmin']);
    //Route::delete('/delete-attendance/{id}', [AttendanceController::class, 'deleteAttendance']);
    Route::get('/total-present', [AttendanceController::class, 'getTotalPresentAndAbsent']);
    Route::get('/in-out-time', [AttendanceController::class, 'inOutTime']);
    Route::get('/department-wise-attendance-list', [AttendanceController::class, 'departmentWiseAttendance']);
    Route::get('/self-monthly-attendance/{id}', [AttendanceController::class, 'getTotalCurrentMonthAttendance']);
    Route::get('/self-yearly-attendance/{id}', [AttendanceController::class, 'getTotalYearlySelfAttendance']);
    Route::get('/filter-attendance-list', [AttendanceController::class, 'filterAttendanceListForAllTime']);
    Route::get('/employee-attendance-leave-report', [AttendanceController::class, 'employeeAttendanceLeaveReport']);

    //Late Types API
    Route::get('/late-types-list', [LateTypeController::class, 'lateTypesList']);
    Route::post('/create-late-type', [LateTypeController::class, 'createLateType']);
    Route::post('/edit-late-type/{id}', [LateTypeController::class, 'updateLateType']);
    Route::get('/late-type-details/{id}', [LateTypeController::class, 'getLateTypeDetails']);
    Route::delete('/delete-leave-type/{id}', [LateTypeController::class, 'deleteLateType']);

    //Leave Request API
    Route::get('/leave-types-list', [LeaveTypeController::class, 'leaveTypesList']);
    Route::post('/create-leave-type', [LeaveTypeController::class, 'createLeaveType']);
    Route::post('/edit-leave-type/{id}', [LeaveTypeController::class, 'updateLeaveType']);
    Route::get('/leave-type-details/{id}', [LeaveTypeController::class, 'getLeaveTypeDetails']);
    Route::delete('/delete-leave-type/{id}', [LeaveTypeController::class, 'deleteLeaveType']);

    Route::get('/leave-requests-list', [LeaveRequestController::class, 'getAllLeaveRequestsList']);
    Route::get('/self-leave-requests-list', [LeaveRequestController::class, 'getSelfLeaveRequestsList']);
    Route::post('/create-leave-request', [LeaveRequestController::class, 'createLeaveRequest']);
    Route::get('/leave-request-details/{id}', [LeaveRequestController::class, 'leaveRequestDetails']);
    Route::post('/edit-leave-request/{id}', [LeaveRequestController::class, 'editLeaveRequest']);
    Route::delete('/delete-leave-request/{id}', [LeaveRequestController::class, 'deleteLeaveRequest']);
    Route::delete('/delete-leave-attachment/{id}', [LeaveRequestController::class, 'deleteLeaveAttachment']);
    Route::get('/department-wise-leave-requests', [LeaveRequestController::class, 'getDepartmentWiseLeaveRequests']);
    Route::put('/leave-request-approve/{id}', [LeaveRequestController::class, 'departmentWiseLeaveRequestApprove']);
    Route::put('/leave-request-approve-hr/{id}', [LeaveRequestController::class, 'LeaveRequestApproveByHr']);
    Route::get('/hr-admin-leave-requests', [LeaveRequestController::class, 'getHrAdminLeaveRequests']);
    Route::put('/leave-request-approve-gm/{id}', [LeaveRequestController::class, 'LeaveRequestApproveByGm']);
    Route::get('/gm-leave-requests', [LeaveRequestController::class, 'getGmLeaveRequest']);
    Route::put('/leave-request-approve-ceo/{id}', [LeaveRequestController::class, 'LeaveRequestApproveByCeo']);




    //Holidays API
    Route::get('/holidays-list', [HolidayController::class, 'holidaysList']);
    Route::post('/create-holiday', [HolidayController::class, 'createHoliday']);
    Route::post('/edit-holiday/{id}', [HolidayController::class, 'updateHoliday']);
    Route::get('/holiday-details/{id}', [HolidayController::class, 'getHolidayDetails']);
    Route::delete('/delete-holiday/{id}', [HolidayController::class, 'deleteHoliday']);
    Route::get('/upcoming-holidays', [HolidayController::class, 'upcomingHolidays']);



    //Work Updates API
    Route::get('/get-daily-work-update/{id}', [AttendanceController::class, 'getWorkUpdateByDate']);
    Route::post('/update-daily-works/{id}', [AttendanceController::class, 'updateDailyWorksByDate']);

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

    // Payrolls
    Route::get('/payrolls', [PayrollController::class, 'payrolls']);
    Route::post('/create-payroll', [PayrollController::class, 'createPayroll']);
    Route::get('/payroll-details/{id}', [PayrollController::class, 'getPayrollDetails']);
    Route::post('/update-payroll/{id}', [PayrollController::class, 'updatePayroll']);
    Route::delete('/delete-payroll/{id}', [PayrollController::class, 'deletePayroll']);

    // Salary Structure
    Route::get('/salary-structure-list', [SalaryStructureController::class, 'salaryStructureList']);
    Route::post('/create-salary-structure', [SalaryStructureController::class, 'createSalaryStructure']);
    Route::get('/salary-structure-details/{id}', [SalaryStructureController::class, 'getSalaryStructureDetails']);
    Route::put('/update-salary-structure/{id}', [SalaryStructureController::class, 'updateSalaryStructure']);
    Route::delete('/delete-salary-structure/{id}', [SalaryStructureController::class, 'deleteSalaryStructure']);


    // Payroll Adjustments
    Route::get('/payroll-adjustments-list', [PayrollAdjustmentController::class, 'payrollAdjustmentsList']);
    Route::post('/create-payroll-adjustment', [PayrollAdjustmentController::class, 'createPayrollAdjustment']);
    Route::get('/payroll-adjustment-details/{id}', [PayrollAdjustmentController::class, 'getPayrollAdjustmentDetails']);
    Route::put('/update-payroll-adjustment/{id}', [PayrollAdjustmentController::class, 'updatePayrollAdjustment']);
    Route::delete('/delete-payroll-adjustment/{id}', [PayrollAdjustmentController::class, 'deletePayrollAdjustment']);


    //Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);



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


