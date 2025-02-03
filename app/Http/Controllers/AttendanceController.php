<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;

class AttendanceController extends Controller
{
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
            $totalPresent = Attendance::where('status', 'Present')
                ->whereDate('date', $today)
                ->count();

            // Get the total number of absent employees today
            $totalAbsent = Attendance::where('status', 'Absent')
                ->whereDate('date', $today)
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
            //print_r($user);exit;
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

            $clockInTime = now()->setTimezone($timezone)->format('H:i'); // Current time in 24-hour format
            $officeStartTime = \Carbon\Carbon::createFromTime(9, 0, 0, $timezone); // 09:00 AM

            // Calculate late time
            $clockInCarbon = \Carbon\Carbon::createFromFormat('H:i', $clockInTime, $timezone);
            $late = $clockInCarbon->gt($officeStartTime)
                ? $officeStartTime->diff($clockInCarbon)->format('%H:%I')
                : '00:00';

            // Create or update attendance record
            $attendance = Attendance::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'date' => $today,
                ],
                [
                    'clock_in' => $clockInTime,
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
        //print_r($today);exit;
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

            $clockOutTime = now()->setTimezone($timezone)->format('H:i'); // Current time in 24-hour format
            $officeEndTime = \Carbon\Carbon::createFromTime(18, 0, 0, $timezone); // 06:00 PM

            // Parse clock-in and clock-out times
            $clockInCarbon = \Carbon\Carbon::parse($attendance->clock_in)->setTimezone($timezone);

            $clockOutCarbon = \Carbon\Carbon::parse($clockOutTime)->setTimezone($timezone);


            // Calculate total work hours
            $totalWorkHour = $clockInCarbon->diff($clockOutCarbon)->format('%H:%I');

            // Calculate early leaving time
            $earlyLeaving = $clockOutCarbon->lt($officeEndTime)
                ? $officeEndTime->diff($clockOutCarbon)->format('%H:%I')
                : '00:00';

            // Update the attendance record
            $attendance->update([
                'clock_out' => $clockOutTime,
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
                ->orderBy('date', 'desc') // 🔥 DESCENDING ORDER (Latest First)
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
















}
