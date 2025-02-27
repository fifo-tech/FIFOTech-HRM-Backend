<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
//Use App\Libraries\ZKLibrary\ZKLibrary;



class AttendanceController extends Controller
{

//    public function getAttendanceFromDevice()
//    {
//        // Instantiate the ZKTeco object with device IP and port
//        $zk = new ZKLibrary('192.168.105.80', 4370); // Replace with your device IP and port
//
//        // Connect to the device
//        if ($zk->connect()) {
//            // Assuming the library has a method like `getAttendance()` to get logs
//            $attendance = $zk->getAttendance();  // Replace with correct method if necessary
//
//            // echo "<pre>";print_r($attendance); exit;
//             // dd($attendance);
//
//
//            // Return response with attendance data
//            return response()->json($attendance);
//        } else {
//            // Return an error if unable to connect
//            return response()->json(['error' => 'Unable to connect to device'], 500);
//        }
//    }



public function getAttendanceList()
    {
        try {
            // Get today's date
            $date = now()->format('Y-m-d');

            // Fetch attendance records for today's date
            $attendances = Attendance::with([
                'employee' => function ($query) {
                    $query->select('id', 'user_id', 'first_name', 'last_name', 'email','phone_num', 'emp_id')
                        ->with(['user' => function ($userQuery) {
                            $userQuery->select('id', 'profile_photo_path');
                        }]);
                }
            ])
                ->where('date', $date)
                ->get();

            // Customize the response structure
            $customizedResponse = $attendances->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'employee_id' => $attendance->employee_id,
                    'date' => $attendance->date,
                    'status' => $attendance->status,
                    'clock_in' => $attendance->clock_in,
                    'clock_in_reason' => $attendance->clock_in_reason,
                    'clock_out' => $attendance->clock_out,
                    'clock_out_reason' => $attendance->clock_out_reason,
                    'early_leaving' => $attendance->early_leaving,
                    'total_work_hour' => $attendance->total_work_hour,
                    'ip_address' => $attendance->ip_address,
                    'device' => $attendance->device,
                    'late' => $attendance->late,
                    'location' => $attendance->location,
                    'daily_work_updates' => $attendance->daily_work_updates,
                    // Embed employee data directly
                    'first_name' => $attendance->employee->first_name ?? null,
                    'last_name' => $attendance->employee->last_name ?? null,
                    'email' => $attendance->employee->email ?? null,
                    'phone_num' => $attendance->employee->phone_num ?? null,
                    'emp_id' => $attendance->employee->emp_id ?? null,

                    // Include profile photo path
                    'image' => $attendance->employee->user->profile_photo_path
                        ? url('storage/' . $attendance->employee->user->profile_photo_path) : null,
                ];
            });

            // Return success response with the customized data
            return $this->response(
                true,
                'Attendance records fetched successfully',
                $customizedResponse,
                200
            );
        } catch (\Exception $e) {
            // Handle any exceptions
            return $this->response(
                false,
                'Something went wrong while fetching attendance',
                $e->getMessage(),
                500
            );
        }
    }

// to avoid test admin and test employee(with emp_id=null) to the list
//    public function getAttendanceList()
//    {
//        try {
//            // Get today's date
//            $date = now()->format('Y-m-d');
//
//            // Fetch attendance records for today's date, filtering out employees with NULL emp_id
//            $attendances = Attendance::with([
//                'employee' => function ($query) {
//                    $query->whereNotNull('emp_id') // Ensure emp_id is not NULL
//                    ->where('emp_id', '!=', '') // Ensure emp_id is not empty
//                    ->select('id', 'user_id', 'first_name', 'last_name', 'email', 'phone_num', 'emp_id')
//                        ->with(['user' => function ($userQuery) {
//                            $userQuery->select('id', 'profile_photo_path');
//                        }]);
//                }
//            ])
//                ->where('date', $date)
//                ->whereHas('employee', function ($query) {
//                    $query->whereNotNull('emp_id')->where('emp_id', '!=', '');
//                })
//                ->get();
//
//            // Customize the response structure
//            $customizedResponse = $attendances->map(function ($attendance) {
//                return [
//                    'id' => $attendance->id,
//                    'employee_id' => $attendance->employee_id,
//                    'date' => $attendance->date,
//                    'status' => $attendance->status,
//                    'clock_in' => $attendance->clock_in,
//                    'clock_in_reason' => $attendance->clock_in_reason,
//                    'clock_out' => $attendance->clock_out,
//                    'clock_out_reason' => $attendance->clock_out_reason,
//                    'early_leaving' => $attendance->early_leaving,
//                    'total_work_hour' => $attendance->total_work_hour,
//                    'ip_address' => $attendance->ip_address,
//                    'device' => $attendance->device,
//                    'late' => $attendance->late,
//                    'location' => $attendance->location,
//                    // Embed employee data directly
//                    'first_name' => $attendance->employee->first_name ?? null,
//                    'last_name' => $attendance->employee->last_name ?? null,
//                    'email' => $attendance->employee->email ?? null,
//                    'phone_num' => $attendance->employee->phone_num ?? null,
//                    'emp_id' => $attendance->employee->emp_id ?? null,
//                    // Include profile photo path
//                    'image' => $attendance->employee->user->profile_photo_path
//                        ? url('storage/' . $attendance->employee->user->profile_photo_path) : null,
//                ];
//            });
//
//            // Return success response with the customized data
//            return $this->response(
//                true,
//                'Attendance records fetched successfully',
//                $customizedResponse,
//                200
//            );
//        } catch (\Exception $e) {
//            // Handle any exceptions
//            return $this->response(
//                false,
//                'Something went wrong while fetching attendance',
//                $e->getMessage(),
//                500
//            );
//        }
//    }

    function getPresentAttendanceList()
    {
        try {
            // Get today's date
            $date = now()->format('Y-m-d');

            // Fetch attendance records for today's date where status is 'present'
            $attendances = Attendance::with([
                'employee' => function ($query) {
                    $query->select('id', 'user_id', 'first_name', 'last_name', 'email', 'phone_num', 'emp_id')
                        ->with(['user' => function ($userQuery) {
                            $userQuery->select('id', 'profile_photo_path');
                        }]);
                }
            ])
                ->where('date', $date)
                ->where('status', 'present') // Filter only present employees
                ->get();

            // Customize the response structure
            $customizedResponse = $attendances->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'employee_id' => $attendance->employee_id,
                    'date' => $attendance->date,
                    'status' => $attendance->status,
                    'clock_in' => $attendance->clock_in,
                    'clock_in_reason' => $attendance->clock_in_reason,
                    'clock_out' => $attendance->clock_out,
                    'clock_out_reason' => $attendance->clock_out_reason,
                    'early_leaving' => $attendance->early_leaving,
                    'total_work_hour' => $attendance->total_work_hour,
                    'ip_address' => $attendance->ip_address,
                    'device' => $attendance->device,
                    'late' => $attendance->late,
                    'location' => $attendance->location,
                    'daily_work_updates' => $attendance->daily_work_updates,
                    // Embed employee data directly
                    'first_name' => $attendance->employee->first_name ?? null,
                    'last_name' => $attendance->employee->last_name ?? null,
                    'email' => $attendance->employee->email ?? null,
                    'phone_num' => $attendance->employee->phone_num ?? null,
                    'emp_id' => $attendance->employee->emp_id ?? null,

                    // Include profile photo path
                    'image' => $attendance->employee->user->profile_photo_path
                        ? url('storage/' . $attendance->employee->user->profile_photo_path) : null,
                ];
            });

            // Return success response with the customized data
            return $this->response(
                true,
                'Present employees attendance records fetched successfully',
                $customizedResponse,
                200
            );
        } catch (\Exception $e) {
            // Handle any exceptions
            return $this->response(
                false,
                'Something went wrong while fetching present employee attendance',
                $e->getMessage(),
                500
            );
        }
    }


    function getAbsentAttendanceList()
    {
        try {
            // Get today's date
            $date = now()->format('Y-m-d');

            // Fetch attendance records for today's date where status is 'absent'
            $attendances = Attendance::with([
                'employee' => function ($query) {
                    $query->select('id', 'user_id', 'first_name', 'last_name', 'email', 'phone_num', 'emp_id')
                        ->with(['user' => function ($userQuery) {
                            $userQuery->select('id', 'profile_photo_path');
                        }]);
                }
            ])
                ->where('date', $date)
                ->where('status', 'absent') // Filter only absent employees
                ->get();

            // Customize the response structure
            $customizedResponse = $attendances->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'employee_id' => $attendance->employee_id,
                    'date' => $attendance->date,
                    'status' => $attendance->status,
                    'clock_in' => $attendance->clock_in,
                    'clock_in_reason' => $attendance->clock_in_reason,
                    'clock_out' => $attendance->clock_out,
                    'clock_out_reason' => $attendance->clock_out_reason,
                    'early_leaving' => $attendance->early_leaving,
                    'total_work_hour' => $attendance->total_work_hour,
                    'ip_address' => $attendance->ip_address,
                    'device' => $attendance->device,
                    'late' => $attendance->late,
                    'location' => $attendance->location,
                    'daily_work_updates' => $attendance->daily_work_updates,
                    // Embed employee data directly
                    'first_name' => $attendance->employee->first_name ?? null,
                    'last_name' => $attendance->employee->last_name ?? null,
                    'email' => $attendance->employee->email ?? null,
                    'phone_num' => $attendance->employee->phone_num ?? null,
                    'emp_id' => $attendance->employee->emp_id ?? null,

                    // Include profile photo path
                    'image' => $attendance->employee->user->profile_photo_path
                        ? url('storage/' . $attendance->employee->user->profile_photo_path) : null,
                ];
            });

            // Return success response with the customized data
            return $this->response(
                true,
                'Absent employees attendance records fetched successfully',
                $customizedResponse,
                200
            );
        } catch (\Exception $e) {
            // Handle any exceptions
            return $this->response(
                false,
                'Something went wrong while fetching absent employee attendance',
                $e->getMessage(),
                500
            );
        }
    }




    // Update Attendance by Admin
    public function updateAttendanceByAdmin(Request $request)
    {
        try {
            // Validate the input data
            $validatedData = $request->validate([
                'emp_id' => 'required|string|exists:employees,emp_id',
                'date' => 'required|date',
                'clock_in' => 'nullable|date_format:h:i A', // 12-hour format with AM/PM
                'clock_out' => 'nullable|date_format:h:i A', // 12-hour format with AM/PM
            ]);

            // Find the employee by emp_id
            $employee = Employee::where('emp_id', $validatedData['emp_id'])
                ->orderBy('updated_at', 'desc') // Order by updated_at in descending order
                ->first();


            if (!$employee) {
                return $this->response(false, 'Employee not found', null, 404);
            }

            // Check if an attendance record exists for the given date and employee
            $attendance = Attendance::where('employee_id', $employee->id)
                ->where('date', $validatedData['date'])
                ->first();

            if (!$attendance) {
                return $this->response(false, 'Attendance record not found for the given employee and date', null, 404);
            }

            // Check if clock_in or clock_out is already set
            if ($attendance->clock_in && $attendance->clock_out) {
                return $this->response(false, 'Attendance already recorded', null, 400);
            }

            // Reference times for calculations in Asia/Dhaka timezone
            $timezone = 'Asia/Dhaka';
            $officeStartTime = \Carbon\Carbon::createFromTime(9, 0, 0, $timezone); // 09:00 AM
            $officeEndTime = \Carbon\Carbon::createFromTime(18, 0, 0, $timezone); // 06:00 PM

            // Parse clock_in and clock_out times in 12-hour format with AM/PM
            $clockIn = $validatedData['clock_in']
                ? \Carbon\Carbon::createFromFormat('h:i A', $validatedData['clock_in'], $timezone)
                : null;
            $clockOut = $validatedData['clock_out']
                ? \Carbon\Carbon::createFromFormat('h:i A', $validatedData['clock_out'], $timezone)
                : null;

            // Calculate late time (if applicable)
            $late = $clockIn && $clockIn->gt($officeStartTime)
                ? $officeStartTime->diff($clockIn)->format('%H:%I') // Calculate the difference between office start and clock-in time
                : '00:00'; // Default to '00:00' if the employee is not late

            // Calculate early leaving time (if applicable)
            $earlyLeaving = $clockOut && $clockOut->lt($officeEndTime)
                ? $officeEndTime->diff($clockOut)->format('%H:%I') // Calculate the difference between clock-out and office end time
                : '00:00'; // Default to '00:00' if the employee didn't leave early

            // Calculate total work hours (if both clock-in and clock-out are provided)
            $totalWorkHour = $clockIn && $clockOut
                ? $clockIn->diff($clockOut)->format('%H:%I') // Calculate the total work hours
                : '00:00'; // Default to '00:00' if the employee didn't clock-in or clock-out

            // Update the attendance record with the calculated values
            $attendance->update([
                'clock_in' => $clockIn ? $clockIn->format('H:i') : $attendance->clock_in, // Store in 24-hour format
                'clock_out' => $clockOut ? $clockOut->format('H:i') : $attendance->clock_out, // Store in 24-hour format
                'status' => $clockIn && $clockOut ? 'Present' : 'Absent',
                'late' => $late, // Store the late value as a time
                'early_leaving' => $earlyLeaving, // Store the early leaving value as a time
                'total_work_hour' => $totalWorkHour, // Store the total work hours value as a time
            ]);

            // Return success response
            return $this->response(true, 'Attendance updated successfully', $attendance, 200);
        } catch (\Exception $e) {
            // Handle exceptions
            return $this->response(false, 'Something went wrong while updating attendance', $e->getMessage(), 500);
        }
    }

    // Total Present and Absent
    public function getTotalPresentAndAbsent()
    {
        try {
            // Get today's date
            $today = Carbon::today();
           // echo print_r($today);exit;

            // Get the total number of present employees today
//            $totalPresent = Attendance::where('status', 'Present',)
//                ->whereDate('date', $today)
//                ->count();

            // Get the total present employees who have a non-null emp_id
            $totalPresent = Attendance::join('employees', 'attendances.employee_id', '=', 'employees.id')
                ->where('attendances.status', 'Present')
                ->whereDate('attendances.date', $today) // Ensures we only count today's attendance
                ->whereNotNull('employees.emp_id') // Ensure emp_id is not null
                ->count();


            // Get the total number of absent employees today
//            $totalAbsent = Attendance::where('status', 'Absent',)
//                ->whereDate('date', $today)
//                ->count();


            $totalAbsent = Attendance::join('employees', 'attendances.employee_id', '=', 'employees.id')
                ->where('attendances.status', 'Absent')
                ->whereDate('attendances.date', $today) // Ensures we only count today's attendance
                ->whereNotNull('employees.emp_id') // Ensure emp_id is not null
                ->count();


            // Return the response
            return $this->response(true, 'Attendance data fetched successfully', [
                'total_present' => $totalPresent,
                'total_absent' => $totalAbsent
            ],201);
        } catch (\Exception $e) {
            // Handle errors and return the response with an error message
            return $this->response(false, 'Something went wrong while fetching attendance data', $e->getMessage(), 500);
        }
    }



    // CLock in API
    public function clockIn(Request $request)
    {
        try {
            // Validate the request
            $validatedData = $request->validate([
                'late_reason' => 'nullable|string|max:255',
            ]);

            $user = auth()->user(); // Get the logged-in user
            $employee = $user->employee; // Get the employee record

            if (!$employee) {
                return $this->response(false, 'Employee record not found', null, 404);
            }

            $today = now()->format('Y-m-d'); // Get today's date
            $timezone = 'Asia/Dhaka';

            // Check if an attendance record already exists for today
            $attendance = Attendance::where('employee_id', $employee->id)
                ->where('date', $today)
                ->first();

            if ($attendance && $attendance->clock_in) {
                return $this->response(false, 'Clock In already recorded for today', null, 400);
            }

            // Get the current time in 12-hour AM/PM format with hours, minutes, and seconds
            $clockInTime = now()->setTimezone($timezone)->format('h:i:s A'); // Example: "09:15:30 AM"

            // Get current time in 24-hour format for database storage
            $clockInTime24 = now()->setTimezone($timezone)->format('H:i:s'); // Example: "09:15:30"

            $officeStartTime = \Carbon\Carbon::createFromTime(9, 0, 0, $timezone); // 09:00 AM

            // Calculate late time
            $clockInCarbon = \Carbon\Carbon::createFromFormat('h:i:s A', $clockInTime, $timezone);
            $late = $clockInCarbon->gt($officeStartTime)
                ? $officeStartTime->diff($clockInCarbon)->format('%H:%I:%S') // Include seconds
                : '00:00:00'; // Include seconds if no late time

            // Create or update attendance record
            $attendance = Attendance::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'date' => $today,
                ],
                [
                    'clock_in' => $clockInTime24, // Save in 24-hour format
                    'clock_in_12hr' => $clockInTime, // Save in 12-hour format with AM/PM
                    'late' => $late,
                    'clock_in_reason' => $validatedData['late_reason'] ?? null,
                    'status' => 'Present',
                ]
            );

            return $this->response(true, 'Clock In recorded successfully', $attendance, 200);
        } catch (\Exception $e) {
            return $this->response(false, 'Something went wrong while clocking in', $e->getMessage(), 500);
        }
    }


    // Clock Out
    public function clockOut(Request $request)
    {
        $today = Carbon::today()->format('Y-m-d');
        try {
            // Validate the request
            $validatedData = $request->validate([
                'early_leave_reason' => 'nullable|string|max:255',
            ]);

            $user = auth()->user(); // Get the logged-in user
            $employee = $user->employee; // Get the employee record

            if (!$employee) {
                return $this->response(false, 'Employee record not found', null, 404);
            }

            $today = now()->format('Y-m-d'); // Get today's date
            $timezone = 'Asia/Dhaka';

            // Check if an attendance record exists for today
            $attendance = Attendance::where('employee_id', $employee->id)
                ->where('date', $today)
                ->first();

            if (!$attendance || !$attendance->clock_in) {
                return $this->response(false, 'Clock In must be recorded first', null, 400);
            }

            if ($attendance->clock_out) {
                return $this->response(false, 'Clock Out already recorded for today', null, 400);
            }

            // Get current time in 12-hour AM/PM format with hour, minute, and second
            $clockOutTime = now()->setTimezone($timezone)->format('h:i:s A'); // Example: "12:17:00 PM"

            // Get current time in 24-hour format with hour, minute, and second (for database)
            $clockOutTime24 = now()->setTimezone($timezone)->format('H:i:s'); // Example: "12:17:00"

            // Office end time (6:00 PM) for early leaving calculation
            $officeEndTime = \Carbon\Carbon::createFromTime(18, 0, 0, $timezone); // 06:00 PM

            // Parse clock-in and clock-out times
            $clockInCarbon = \Carbon\Carbon::parse($attendance->clock_in)->setTimezone($timezone);
            $clockOutCarbon = \Carbon\Carbon::createFromFormat('H:i:s', $clockOutTime24, $timezone);

            // Calculate total work hours including seconds
            $totalWorkHour = $clockInCarbon->diff($clockOutCarbon)->format('%H:%I:%S'); // Including seconds

            // Calculate early leaving time
            $earlyLeaving = $clockOutCarbon->lt($officeEndTime)
                ? $officeEndTime->diff($clockOutCarbon)->format('%H:%I:%S') // Include seconds
                : '00:00:00'; // Include seconds if early leaving

            // Update the attendance record with the correct time formats
            $attendance->update([
                'clock_out' => $clockOutTime24, // Store in 24-hour format with seconds in the DB
                'early_leaving' => $earlyLeaving,
                'total_work_hour' => $totalWorkHour,
                'clock_out_reason' => $validatedData['early_leave_reason'] ?? null,
            ]);

            return $this->response(true, 'Clock Out recorded successfully', $attendance, 200);
        } catch (\Exception $e) {
            return $this->response(false, 'Something went wrong while clocking out', $e->getMessage(), 500);
        }
    }



    /// Specific Employee Attendance
    public function getSpecificEmployeeAttendance()
    {
        try {
            // logged in user
            $user = auth()->user();

            // employee record of user
            $employee = $user->employee;

            if (!$employee) {
                return $this->response(false, 'Employee record not found', null, 404);
            }

            // find out first and last date of current month
            $startOfMonth = now()->startOfMonth()->format('Y-m-d');
            $endOfMonth = now()->endOfMonth()->format('Y-m-d');

            // find out current month attendance record (Descending Order)
            $attendances = Attendance::with('employee')
                ->where('employee_id', $employee->id)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->orderBy('date', 'desc') //  DESCENDING ORDER (Latest First)
                ->get();

            // to make customised response
            $customizedResponse = $attendances->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'employee_id' => $attendance->employee_id,
                    'date' => $attendance->date,
                    'status' => $attendance->status,
                    'clock_in' => $attendance->clock_in,
                    'clock_in_reason' => $attendance->clock_in_reason,
                    'clock_out' => $attendance->clock_out,
                    'clock_out_reason' => $attendance->clock_out_reason,
                    'early_leaving' => $attendance->early_leaving,
                    'total_work_hour' => $attendance->total_work_hour,
                    'daily_work_updates' => $attendance->daily_work_updates,
                    'ip_address' => $attendance->ip_address,
                    'device' => $attendance->device,
                    'late' => $attendance->late,
                    'location' => $attendance->location,
                    'first_name' => $attendance->employee->first_name ?? null,
                    'last_name' => $attendance->employee->last_name ?? null,
                    'email' => $attendance->employee->email ?? null,
                    'phone_num' => $attendance->employee->phone_num ?? null,
                    'emp_id' => $attendance->employee->emp_id ?? null,
                    'image' => $attendance->employee->user->profile_photo_path
                        ? url('storage/' . $attendance->employee->user->profile_photo_path) : null,
                ];
            });

            return $this->response(
                true,
                'Employee attendance records fetched successfully',
                $customizedResponse,
                200
            );

        } catch (\Exception $e) {
            return $this->response(
                false,
                'Something went wrong while fetching employee attendance',
                $e->getMessage(),
                500
            );
        }
    }

    // In Out Time
    public function inOutTime()
    {
        try {

            $user = auth()->user();


            $employee = $user->employee;

            if (!$employee) {
                return $this->response(false, 'Employee record not found', null, 404);
            }


            $today = now()->format('Y-m-d');

            //find out today attendance record
            $attendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('date', $today)
                ->first();

            if (!$attendance) {
                return $this->response(false, 'No attendance record found for today', null, 404);
            }


            $customizedResponse = [
                'date' => $attendance->date,
                'clock_in' => $attendance->clock_in,
                'clock_out' => $attendance->clock_out,
            ];

            return $this->response(
                true,
                'Employee attendance fetched successfully',
                $customizedResponse,
                200
            );

        } catch (\Exception $e) {
            return $this->response(
                false,
                'Something went wrong while fetching today\'s attendance',
                $e->getMessage(),
                500
            );
        }
    }



//Department wise Attendance only by Clock In time.
    public function departmentWiseAttendance()
    {
        try {
            // Get today's date
            $date = now()->format('Y-m-d');

            // Fetch attendance records for today's date, filtering only employees who have clocked in but not clocked out
            $attendances = Attendance::with([
                'employee' => function ($query) {
                    $query->select('id', 'user_id', 'first_name', 'last_name', 'email', 'phone_num', 'emp_id', 'dept_id')
                        ->with(['user' => function ($userQuery) {
                            $userQuery->select('id', 'profile_photo_path');
                        }])
                        ->with('department:id,name'); // Load department details
                }
            ])
                ->where('date', $date)
                ->whereNotNull('clock_in') // Only include employees who clocked in
                ->whereNull('clock_out')   // Exclude employees who have clocked out
                ->get();

            // Group attendance records by department
            $departmentWiseAttendance = $attendances->groupBy(function ($attendance) {
                return $attendance->employee->department->name ?? 'Unknown Department';
            });

            // Customize the response structure department-wise
            $customizedResponse = $departmentWiseAttendance->map(function ($attendancesInDepartment, $departmentName) {
                return [
                    'department' => $departmentName,
                    'employees' => $attendancesInDepartment->map(function ($attendance) {
                        return [
                            'id' => $attendance->id,
                            'employee_id' => $attendance->employee_id,
                            'date' => $attendance->date,
                            'status' => $attendance->status,
                            'clock_in' => $attendance->clock_in,
                            'clock_in_reason' => $attendance->clock_in_reason,
                            'late' => $attendance->late,
                            // Embed employee data directly
                            'first_name' => $attendance->employee->first_name ?? null,
                            'last_name' => $attendance->employee->last_name ?? null,
                            'email' => $attendance->employee->email ?? null,
                            'phone_num' => $attendance->employee->phone_num ?? null,
                            'emp_id' => $attendance->employee->emp_id ?? null,
                            // Include profile photo path
                            'image' => $attendance->employee->user->profile_photo_path
                                ? url('storage/' . $attendance->employee->user->profile_photo_path) : null,
                        ];
                    })
                ];
            });

            // Return success response with the department-wise attendance data
            return $this->response(
                true,
                'Department-wise attendance records fetched successfully',
                $customizedResponse,
                200
            );
        } catch (\Exception $e) {
            // Handle any exceptions
            return $this->response(
                false,
                'Something went wrong while fetching attendance',
                $e->getMessage(),
                500
            );
        }
    }


    public function getCurrentMonthAttendance($employee_id)
    {
        $year = date('Y');
        $month = date('m');

        $summary = Attendance::where('employee_id', $employee_id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->selectRaw("
            COUNT(CASE WHEN status = 'Present' THEN 1 END) as total_present,
            COUNT(CASE WHEN status = 'Absent' THEN 1 END) as total_absent,
            COUNT(CASE WHEN late IS NOT NULL THEN 1 END) as total_late
        ")
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Current month attendance summary fetched successfully',
            'data' => [
                'total_present' => $summary->total_present ?? 0,
                'total_absent' => $summary->total_absent ?? 0,
                'total_late' => $summary->total_late ?? 0,
            ],
        ], 200);
    }
    public function getYearlySelfAttendance($employee_id)
    {
        $year = date('Y');

        $summary = Attendance::where('employee_id', $employee_id)
            ->whereYear('date', $year)   // Filtering only the data for the current year
            ->selectRaw("
            COUNT(CASE WHEN status = 'Present' THEN 1 END) as total_present,
            COUNT(CASE WHEN status = 'Absent' THEN 1 END) as total_absent,
            COUNT(CASE WHEN late IS NOT NULL THEN 1 END) as total_late
        ")
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Yearly attendance summary fetched successfully',
            'data' => [
                'total_present' => $summary->total_present ?? 0,
                'total_absent' => $summary->total_absent ?? 0,
                'total_late' => $summary->total_late ?? 0,
            ],
        ], 200);
    }



    // Get all work updates up to today
//    public function getAllDailyWorkUpdates ()
//    {
//        $today = Carbon::now()->format('Y-m-d');
//        $updates = Attendance::whereDate('date', '<=', $today)->orderBy('date', 'asc')->get();
//        return response()->json($updates);
//    }

    // Update daily work updates for a specific day
    public function updateDailyWorksByDate(Request $request, $id)
    {
        $date = $request->input('date');
        $today = Carbon::now()->format('Y-m-d');

        if ($date > $today) {
            return response()->json(['message' => 'Cannot update future dates'], 403);
        }

        // Find attendance for the given employee and date
        $attendance = Attendance::where('date', $date)
            ->where('employee_id', $id) // Use employee ID from route
            ->first();

        if (!$attendance) {
            return response()->json(['message' => 'No records found for this employee on this date'], 404);
        }

        // Check if the status is "Present" before allowing updates
        if ($attendance->status !== 'Present') {
            return response()->json(['message' => 'Work updates can only be made for Present employees only'], 403);
        }

        // Check if an update has already been made
        if (!empty($attendance->daily_work_updates)) {
            return response()->json(['message' => 'This update has already been made and cannot be modified'], 403);
        }

        // Save the new work update
        $attendance->daily_work_updates = $request->input('daily_work_updates');
        $attendance->save();

        return response()->json(['message' => 'Work update saved successfully']);
    }

    public function getWorkUpdateByDate(Request $request, $id)
    {
        $date = $request->input('date');

        $attendance = Attendance::where('date', $date)
            ->where('employee_id', $id)
            ->first();

        if (!$attendance) {
            return response()->json(['message' => 'No records found for this date'], 404);
        }

        return response()->json($attendance);
    }











}
