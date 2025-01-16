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
                    $query->select('id', 'user_id', 'first_name', 'last_name', 'email', 'emp_id')
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
            $employee = Employee::where('emp_id', $validatedData['emp_id'])->first();

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












}
