<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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


//    /**
//     * Fetch logs from biometric API and update database.
//     */
//    public function fetchAttendanceLogs()
//    {
//        // API URL
//        $apiUrl = 'http://192.168.200.170:8000/api/logs';
//
//        // Fetch data from API
//        $response = Http::get($apiUrl);
//
//        if ($response->successful()) {
//            $data = $response->json(); // Parse JSON data
//
//            if (!isset($data['logs'])) {
//                return response()->json(['message' => 'Invalid API response format'], 400);
//            }
//
//            // Iterate over the logs and group them by date
//            $attendanceRecords = [];
//            foreach ($data['logs'] as $log) {
//                // Extract user_id (e.g., 267) and compare with emp_id's last 3 digits
//                $user_id = $log['user_id'];
//
//                // Get the employee record by comparing user_id with last 3 digits of emp_id
//                $employee = Employee::whereRaw('RIGHT(emp_id, 3) = ?', [$user_id])->first();
//
//                if ($employee) {
//                    // Store log timestamp for further processing, grouped by date
//                    $logDate = substr($log['timestamp'], 0, 10); // Extract the date part from timestamp (YYYY-MM-DD)
//
//                    if (!isset($attendanceRecords[$employee->id][$logDate])) {
//                        $attendanceRecords[$employee->id][$logDate] = [];
//                    }
//                    $attendanceRecords[$employee->id][$logDate][] = $log['timestamp'];
//                }
//            }
//// Now loop through each employee and each date, and update clock_in and clock_out times
//            foreach ($attendanceRecords as $employee_id => $dates) {
//                foreach ($dates as $date => $timestamps) {
//                    // Sort timestamps in ascending order
//                    sort($timestamps);
//
//                    // Assign clock_in and clock_out based on timestamp count
//                    if (count($timestamps) === 1) {
//                        // If only one timestamp, assign it to both clock_in and clock_out
//                        $clock_in = $timestamps[0];
//                        $clock_out = null;
//                    } else {
//                        // If multiple timestamps, assign first as clock_in and last as clock_out
//                        $clock_in = $timestamps[0];
//                        $clock_out = $timestamps[count($timestamps) - 1];
//                    }
//                    // Calculate late time and early leaving time
//                    $late = $this->calculateLateTime($clock_in, $date);
//                    $early_leaving = $this->calculateEarlyLeavingTime($clock_out, $date);
//
//                    // Get the attendance record for the employee and date
//                    $attendance = Attendance::where('employee_id', $employee_id)
//                        ->where('date', $date)
//                        ->first();
//
//                    // If attendance does not exist, create a new one
//                    if (!$attendance) {
//                        $attendance = new Attendance();
//                        $attendance->employee_id = $employee_id;
//                        $attendance->date = $date; // Use the specific date from the logs
//                    }
//
//                    // Update the attendance details
//                    $attendance->clock_in = $clock_in;
//                    $attendance->clock_out = $clock_out;
//                    $attendance->status = 'Present'; // Assuming present by default, you can customize
//                    $attendance->late = $late; // Save the late time
//                    $attendance->early_leaving = $early_leaving; // Save the early leaving time
//                    $attendance->save();
//                }
//            }
//
//            return response()->json(['message' => 'Attendance logs updated successfully!'], 200);
//        } else {
//            return response()->json(['message' => 'Failed to fetch attendance logs'], 500);
//        }
//    }
//
//    /**
//     * Calculate the late time based on clock_in and office start time (9:00 AM).
//     */
//    private function calculateLateTime($clock_in, $date)
//    {
//        // Office start time is 9:00 AM on the same date
//        $office_start_time = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' 09:00:00');
//
//        // Convert the clock_in time to Carbon instance
//        $clock_in_time = Carbon::parse($clock_in);
//
//        // Check if the employee is late
//        if ($clock_in_time->gt($office_start_time)) {
//            // Calculate late time (difference between clock_in and office_start_time)
//            $late = $clock_in_time->diff($office_start_time);
//            return $late->format('%H:%I:%S'); // Return the late time in HH:MM:SS format
//        }
//
//        // If no late arrival, return null
//        return null;
//    }
//
//    /**
//     * Calculate the early leaving time based on clock_out and office end time (6:00 PM).
//     */
//    private function calculateEarlyLeavingTime($clock_out, $date)
//    {
//        // If clock_out is null, no need to calculate early leaving time
//        if (!$clock_out) {
//            return null;
//        }
//
//        // Office end time is 6:00 PM on the given date
//        $office_end_time = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' 18:00:00');
//
//        // Convert clock_out to a Carbon instance
//        $clock_out_time = Carbon::parse($clock_out);
//
//        // Check if the employee left early
//        if ($clock_out_time->lt($office_end_time)) {
//            // Calculate the early leaving time (difference between clock_out and office_end_time)
//            $early_leaving = $clock_out_time->diff($office_end_time);
//            return $early_leaving->format('%H:%I:%S'); // Return time in HH:MM:SS format
//        }
//
//        return null; // If the employee didn't leave early, return null
//    }




    public function fetchAttendanceLogs()
    {
        // API URL
        $apiUrl = 'http://192.168.200.170:8000/api/logs';

        // Fetch data from API
        $response = Http::get($apiUrl);

        if ($response->successful()) {
            $data = $response->json(); // Parse JSON data

            if (!isset($data['logs'])) {
                return response()->json(['message' => 'Invalid API response format'], 400);
            }

            // Iterate over the logs and group them by date
            $attendanceRecords = [];
            foreach ($data['logs'] as $log) {
                $user_id = ltrim($log['user_id'], '0'); // Remove leading zeros from user_id

                // Get employee whose emp_id last numeric part matches user_id
                $employees = Employee::all();
                $matchedEmployee = null;

                foreach ($employees as $employee) {
                    if (preg_match('/(\d+)$/', $employee->emp_id, $matches)) {
                        $emp_last_part = ltrim($matches[1], '0'); // Remove leading zeros

                        if ($emp_last_part === $user_id) {
                            $matchedEmployee = $employee;
                            break;
                        }
                    }
                }

                if ($matchedEmployee) {
                    $logDate = substr($log['timestamp'], 0, 10); // Extract the date part from timestamp (YYYY-MM-DD)

                    if (!isset($attendanceRecords[$matchedEmployee->id][$logDate])) {
                        $attendanceRecords[$matchedEmployee->id][$logDate] = [];
                    }

                    // Convert timestamp to MySQL DATETIME format
                    $formattedTimestamp = Carbon::parse($log['timestamp'])->format('Y-m-d H:i:s');

                    $attendanceRecords[$matchedEmployee->id][$logDate][] = $formattedTimestamp;
                }
            }

            // Process attendance records
            foreach ($attendanceRecords as $employee_id => $dates) {
                foreach ($dates as $date => $timestamps) {
                    sort($timestamps);
                    $clock_in = $timestamps[0];
                    $clock_out = count($timestamps) > 1 ? $timestamps[count($timestamps) - 1] : null;

                    $late = $this->calculateLateTime($clock_in, $date);
                    $early_leaving = $this->calculateEarlyLeavingTime($clock_out, $date);

                    $attendance = Attendance::updateOrCreate(
                        ['employee_id' => $employee_id, 'date' => $date],
                        [
                            'clock_in' => $clock_in,
                            'clock_out' => $clock_out,
                            'status' => 'Present',
                            'late' => $late,
                            'early_leaving' => $early_leaving
                        ]
                    );
                }
            }

            return response()->json(['message' => 'Attendance logs updated successfully!'], 200);
        } else {
            return response()->json(['message' => 'Failed to fetch attendance logs'], 500);
        }
    }
    private function calculateLateTime($clock_in, $date)
    {
        // Office start time is 9:00 AM
        $office_start_time = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' 09:00:00');

        // Convert the clock_in time to Carbon instance
        $clock_in_time = Carbon::parse($clock_in);

        // Check if the employee is late
        if ($clock_in_time->gt($office_start_time)) {
            // Calculate late time
            $late = $clock_in_time->diff($office_start_time);
            return $late->format('%H:%I:%S');
        }

        return null;
    }
    private function calculateEarlyLeavingTime($clock_out, $date)
    {
        // If clock_out is null, return null
        if (!$clock_out) {
            return null;
        }

        // Office end time is 6:00 PM
        $office_end_time = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' 18:00:00');

        // Convert clock_out to Carbon instance
        $clock_out_time = Carbon::parse($clock_out);

        // Check if the employee left early
        if ($clock_out_time->lt($office_end_time)) {
            // Calculate the early leaving time
            $early_leaving = $clock_out_time->diff($office_end_time);
            return $early_leaving->format('%H:%I:%S');
        }

        return null;
    }


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
            $validatedData = $request->validate([
                'emp_id' => 'required|string|exists:employees,emp_id',
                'date' => 'required|date',
                'clock_in' => 'nullable|string',
                'clock_out' => 'nullable|string',
            ]);

            // Find employee
            $employee = Employee::where('emp_id', $validatedData['emp_id'])->first();
            if (!$employee) {
                return $this->response(false, 'Employee not found', null, 404);
            }

            // Find attendance record
            $attendance = Attendance::where('employee_id', $employee->id)
                ->where('date', $validatedData['date'])
                ->first();

            if (!$attendance) {
                return $this->response(false, 'Attendance record not found', null, 404);
            }

            $timezone = 'Asia/Dhaka';
            $officeStartTime = Carbon::createFromTime(9, 0, 0, $timezone);
            $officeEndTime = Carbon::createFromTime(18, 0, 0, $timezone);

            // Parse times
            $clockIn = !empty($validatedData['clock_in'])
                ? $this->parseTimeString($validatedData['clock_in'], $timezone, 'clock_in')
                : (!empty($attendance->clock_in)
                    ? $this->parseTimeString($attendance->clock_in, $timezone, 'clock_in')
                    : null);

            $clockOut = !empty($validatedData['clock_out'])
                ? $this->parseTimeString($validatedData['clock_out'], $timezone, 'clock_out')
                : null;

            // Validate time logic
            if ($clockIn && $clockOut && $clockOut->lte($clockIn)) {
                return $this->response(false, 'Clock-out must be after clock-in', null, 400);
            }

            if (!$clockIn && $clockOut) {
                return $this->response(false, 'Clock-in is required before clock-out', null, 400);
            }

            // Time calculations
            $totalWorkHour = ($clockIn && $clockOut) ? $clockIn->diff($clockOut)->format('%H:%I') : null;
            $late = ($clockIn && $clockIn->gt($officeStartTime)) ? $officeStartTime->diff($clockIn)->format('%H:%I') : null;
            $earlyLeaving = ($clockOut && $clockOut->lt($officeEndTime)) ? $officeEndTime->diff($clockOut)->format('%H:%I') : null;

            // Prepare update
            $updateData = [];

            if (!empty($validatedData['clock_in'])) {
                $updateData['clock_in'] = $clockIn->format('H:i');
            }

            if (!empty($validatedData['clock_out'])) {
                $updateData['clock_out'] = $clockOut->format('H:i');
            }

            $updateData['status'] = $attendance->status;
            if (empty($attendance->clock_in) && $clockIn) {
                $updateData['status'] = 'Present';
            }

            $updateData['late'] = $late;
            $updateData['early_leaving'] = $earlyLeaving;
            $updateData['total_work_hour'] = $totalWorkHour;

            // Update attendance
            $attendance->update($updateData);

            return $this->response(true, 'Attendance updated', $attendance, 200);

        } catch (\Illuminate\Validation\ValidationException $ve) {
            return $ve->getResponse(); // Laravel handles this well
        } catch (\Exception $e) {
            return $this->response(false, 'Error updating attendance', $e->getMessage(), 500);
        }
    }



    /**
     * Try parsing a time string in both 12h and 24h formats
     */
    private function parseTimeString($timeStr, $timezone, $fieldName = 'time')
    {
        try {
            return Carbon::createFromFormat('h:i A', $timeStr, $timezone);
        } catch (\Exception $e1) {
            try {
                return Carbon::createFromFormat('H:i', $timeStr, $timezone);
            } catch (\Exception $e2) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    $fieldName => ["Invalid {$fieldName} format: '{$timeStr}'. Use HH:MM (24hr) or HH:MM AM/PM (12hr)"]
                ]);
            }
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
//    public function clockIn(Request $request)
//    {
//        try {
//            // Validate the request
//            $validatedData = $request->validate([
//                'late_reason' => 'nullable|string|max:255',
//            ]);
//
//            $user = auth()->user(); // Get the logged-in user
//            $employee = $user->employee; // Get the employee record
//
//            if (!$employee) {
//                return $this->response(false, 'Employee record not found', null, 404);
//            }
//
//            $today = now()->format('Y-m-d'); // Get today's date
//            $timezone = 'Asia/Dhaka';
//
//            // Check if an attendance record already exists for today
//            $attendance = Attendance::where('employee_id', $employee->id)
//                ->where('date', $today)
//                ->first();
//
//            if ($attendance && $attendance->clock_in) {
//                return $this->response(false, 'Clock In already recorded for today', null, 400);
//            }
//
//            // Get the current time in 12-hour AM/PM format with hours, minutes, and seconds
//            $clockInTime = now()->setTimezone($timezone)->format('h:i:s A'); // Example: "09:15:30 AM"
//
//            // Get current time in 24-hour format for database storage
//            $clockInTime24 = now()->setTimezone($timezone)->format('H:i:s'); // Example: "09:15:30"
//
//            $officeStartTime = \Carbon\Carbon::createFromTime(9, 0, 0, $timezone); // 09:00 AM
//
//            // Calculate late time
//            $clockInCarbon = \Carbon\Carbon::createFromFormat('h:i:s A', $clockInTime, $timezone);
//            $late = $clockInCarbon->gt($officeStartTime)
//                ? $officeStartTime->diff($clockInCarbon)->format('%H:%I:%S') // Include seconds
//                : null; // Include seconds if no late time
//
//            // Create or update attendance record
//            $attendance = Attendance::updateOrCreate(
//                [
//                    'employee_id' => $employee->id,
//                    'date' => $today,
//                ],
//                [
//                    'clock_in' => $clockInTime24, // Save in 24-hour format
//                    'clock_in_12hr' => $clockInTime, // Save in 12-hour format with AM/PM
//                    'late' => $late,
//                    'clock_in_reason' => $validatedData['late_reason'] ?? null,
//                    'status' => 'Present',
//                ]
//            );
//
//            return $this->response(true, 'Clock In recorded successfully', $attendance, 200);
//        } catch (\Exception $e) {
//            return $this->response(false, 'Something went wrong while clocking in', $e->getMessage(), 500);
//        }
//    }
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

            if ($attendance) {
                if ($attendance->clock_in) {
                    // If Clock In already recorded, update only late reason
                    $attendance->update([
                        'clock_in_reason' => $validatedData['late_reason'] ?? $attendance->clock_in_reason
                    ]);
                    return $this->response(true, 'Late reason updated successfully', $attendance, 200);
                } else {
                    // If Clock In hasn't been recorded yet, perform the full clock-in logic
                    $clockInTime = now()->setTimezone($timezone)->format('h:i:s A');
                    $clockInTime24 = now()->setTimezone($timezone)->format('H:i:s');

                    $officeStartTime = \Carbon\Carbon::createFromTime(9, 0, 0, $timezone);

                    // Calculate late time
                    $clockInCarbon = \Carbon\Carbon::createFromFormat('h:i:s A', $clockInTime, $timezone);
                    $late = $clockInCarbon->gt($officeStartTime)
                        ? $officeStartTime->diff($clockInCarbon)->format('%H:%I:%S')
                        : null;

                    // Create or update attendance record
                    $attendance->update([
                        'clock_in' => $clockInTime24,
                        'clock_in_12hr' => $clockInTime,
                        'late' => $late,
                        'clock_in_reason' => $validatedData['late_reason'] ?? null,
                        'status' => 'Present',
                    ]);

                    return $this->response(true, 'Clock In recorded successfully', $attendance, 200);
                }
            } else {
                return $this->response(false, 'No attendance record found for today', null, 404);
            }
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

//            if ($attendance->clock_out) {
//                return $this->response(false, 'Clock Out already recorded for today', null, 400);
//            }

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
                : null;

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
//                ->whereNull('clock_out')   // Exclude employees who have clocked out
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


//    public function getCurrentMonthAttendance($employee_id)
//    {
//        $year = date('Y');
//        $month = date('m');
//
//        $summary = Attendance::where('employee_id', $employee_id)
//            ->whereYear('date', $year)
//            ->whereMonth('date', $month)
//            ->selectRaw("
//            COUNT(CASE WHEN status = 'Present' THEN 1 END) as total_present,
//            COUNT(CASE WHEN status = 'Absent' THEN 1 END) as total_absent,
//            COUNT(CASE WHEN late IS NOT NULL AND late != '00:00:00' THEN 1 END) as total_late
//
//
//
//
//        ")
//            ->first();
//
//        return response()->json([
//            'success' => true,
//            'message' => 'Current month attendance summary fetched successfully',
//            'data' => [
//                'total_present' => $summary->total_present ?? 0,
//                'total_absent' => $summary->total_absent ?? 0,
//                'total_late' => $summary->total_late ?? 0,
//            ],
//        ], 200);
//    }






//    public function getCurrentMonthAttendance($employee_id)
//    {
//        $year = date('Y');
//        $month = date('m');
//
//        $startDate = Carbon::now()->startOfMonth();
//        $endDate = Carbon::now()->endOfMonth();
//
//        // Calculate Total Days in Month
//        $totalDays = $startDate->diffInDays($endDate) + 1;
//
//        // Calculate Total Fridays (Weekends)
//        $totalFridays = 0;
//        for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
//            if ($date->format('w') == 5) { // '5' means Friday in PHP (Sunday = 0)
//                $totalFridays++;
//            }
//        }
//
//        // Fetch Total Holidays
//        $totalHolidays = DB::table('holidays')
//            ->whereBetween('date', [$startDate, $endDate])
//            ->count();
//
//        // Fetch Employee Attendance Summary
//        $summary = Attendance::where('employee_id', $employee_id)
//            ->whereYear('date', $year)
//            ->whereMonth('date', $month)
//            ->selectRaw("
//            COUNT(CASE WHEN status = 'Present' THEN 1 END) as total_present,
//            COUNT(CASE WHEN status = 'Absent' THEN 1 END) as total_absent,
//            COUNT(CASE WHEN late IS NOT NULL AND late != '00:00:00' THEN 1 END) as total_late
//        ")
//            ->first();
//
//        // Total Working Days (Excluding Fridays and Holidays)
//        $totalWorkingDays = (int)($totalDays - $totalFridays - $totalHolidays);
//
//        // Calculate Total Work on Holidays
//        $totalWorkOnHolidays = Attendance::where('employee_id', $employee_id)
//            ->whereIn('date', function ($query) use ($startDate, $endDate) {
//                $query->select('date')
//                    ->from('holidays')
//                    ->whereBetween('date', [$startDate, $endDate]);
//            })
//            ->where('status', 'Present')
//            ->count();
//
//        return response()->json([
//            'success' => true,
//            'message' => 'Current month attendance summary fetched successfully',
//            'data' => [
//                'total_working_days' => $totalWorkingDays,
//                'total_present' => $summary->total_present ?? 0,
//                'total_absent' => $summary->total_absent ?? 0,
//                'total_late' => $summary->total_late ?? 0,
//                'total_holidays' => $totalHolidays,
//                'total_work_on_holidays' => $totalWorkOnHolidays,
//            ],
//        ], 200);
//    }






    public function getTotalCurrentMonthAttendance($employee_id)
    {
        $year = date('Y');
        $month = date('m');

        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        // Calculate Total Days in the Month
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // Calculate Total Fridays (Weekends)
        $totalFridays = 0;
        for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
            if ($date->format('w') == 5) { // '5' means Friday in PHP (Sunday = 0)
                $totalFridays++;
            }
        }

        // Fetch Total Holidays
        $totalHolidays = DB::table('holidays')
            ->whereBetween('date', [$startDate, $endDate])
            ->count();

        // Fetch Employee Attendance Summary (Updated total_absent Calculation)
        $summary = Attendance::where('employee_id', $employee_id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->selectRaw("
            COUNT(CASE WHEN status = 'Present' THEN 1 END) as total_present,
            COUNT(CASE
                WHEN status = 'Absent'
                AND DATE_FORMAT(date, '%w') NOT IN (5)  -- Exclude Fridays (Friday = 5)
                AND date NOT IN (SELECT date FROM holidays WHERE date BETWEEN ? AND ?)
            THEN 1 END) as total_absent,
            COUNT(CASE WHEN late IS NOT NULL AND late != '00:00:00' THEN 1 END) as total_late
        ", [$startDate, $endDate])
            ->first();

        // Total Working Days (Excluding Fridays and Holidays)
        $totalWorkingDays = (int)($totalDays - $totalFridays - $totalHolidays);

        // Calculate Total Work on Holidays
        $totalWorkOnHolidays = Attendance::where('employee_id', $employee_id)
            ->whereIn('date', function ($query) use ($startDate, $endDate) {
                $query->select('date')
                    ->from('holidays')
                    ->whereBetween('date', [$startDate, $endDate]);
            })
            ->where('status', 'Present')
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Current month attendance summary fetched successfully',
            'data' => [
                'total_working_days' => $totalWorkingDays,
                'total_present' => $summary->total_present ?? 0,
                'total_absent' => $summary->total_absent ?? 0,  // Excluding Fridays & Holidays
                'total_late' => $summary->total_late ?? 0,
                'total_holidays' => $totalHolidays,
                'total_work_on_holidays' => $totalWorkOnHolidays,
            ],
        ], 200);
    }



//    public function getYearlySelfAttendance($employee_id)
//    {
//        $year = date('Y');
//
//        $summary = Attendance::where('employee_id', $employee_id)
//            ->whereYear('date', $year)   // Filtering only the data for the current year
//            ->selectRaw("
//            COUNT(CASE WHEN status = 'Present' THEN 1 END) as total_present,
//            COUNT(CASE WHEN status = 'Absent' THEN 1 END) as total_absent,
//
//            COUNT(CASE WHEN late IS NOT NULL AND late != '00:00:00' THEN 1 END) as total_late
//
//
//
//        ")
//            ->first();
//
//        return response()->json([
//            'success' => true,
//            'message' => 'Yearly attendance summary fetched successfully',
//            'data' => [
//                'total_present' => $summary->total_present ?? 0,
//                'total_absent' => $summary->total_absent ?? 0,
//                'total_late' => $summary->total_late ?? 0,
//            ],
//        ], 200);
//    }




//    public function getYearlySelfAttendance($employee_id)
//    {
//        $year = date('Y');
//        $startDate = Carbon::createFromDate($year, 1, 1); // January 1st
//        $endDate = Carbon::createFromDate($year, 12, 31); // December 31st
//
//        // Calculate Total Days in Year
//        $totalDays = $startDate->diffInDays($endDate) + 1;
//
//        // Calculate Total Fridays in the Year
//        $totalFridays = 0;
//        for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
//            if ($date->format('w') == 5) { // '5' means Friday in PHP (Sunday = 0)
//                $totalFridays++;
//            }
//        }
//
//        // Fetch Total Holidays in the Year
//        $totalHolidays = DB::table('holidays')
//            ->whereBetween('date', [$startDate, $endDate])
//            ->count();
//
//        // Fetch Employee Attendance Summary
//        $summary = Attendance::where('employee_id', $employee_id)
//            ->whereYear('date', $year)
//            ->selectRaw("
//            COUNT(CASE WHEN status = 'Present' THEN 1 END) as total_present,
//            COUNT(CASE WHEN status = 'Absent' THEN 1 END) as total_absent,
//            COUNT(CASE WHEN late IS NOT NULL AND late != '00:00:00' THEN 1 END) as total_late
//        ")
//            ->first();
//
//        // Total Working Days (Excluding Fridays and Holidays)
//        $totalWorkingDays = (int) ($totalDays - $totalFridays - $totalHolidays);
//
//        // Calculate Total Work on Holidays
//        $totalWorkOnHolidays = Attendance::where('employee_id', $employee_id)
//            ->whereIn('date', function ($query) use ($startDate, $endDate) {
//                $query->select('date')
//                    ->from('holidays')
//                    ->whereBetween('date', [$startDate, $endDate]);
//            })
//            ->where('status', 'Present')
//            ->count();
//
//        return response()->json([
//            'success' => true,
//            'message' => 'Yearly attendance summary fetched successfully',
//            'data' => [
//                'total_working_days' => $totalWorkingDays,
//                'total_present' => $summary->total_present ?? 0,
//                'total_absent' => $summary->total_absent ?? 0,
//                'total_late' => $summary->total_late ?? 0,
//                'total_holidays' => $totalHolidays,
//                'total_work_on_holidays' => $totalWorkOnHolidays,
//            ],
//        ], 200);
//    }








    public function getTotalYearlySelfAttendance($employee_id)
    {
        $year = date('Y');
        $startDate = Carbon::createFromDate($year, 1, 1); // January 1st
        $endDate = Carbon::createFromDate($year, 12, 31); // December 31st

        // Calculate Total Days in Year
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // Calculate Total Fridays in the Year
        $totalFridays = 0;
        for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
            if ($date->format('w') == 5) { // '5' means Friday in PHP (Sunday = 0)
                $totalFridays++;
            }
        }

        // Fetch Total Holidays in the Year
        $totalHolidays = DB::table('holidays')
            ->whereBetween('date', [$startDate, $endDate])
            ->count();

        // Fetch Employee Attendance Summary (Updated total_absent Calculation)
        $summary = Attendance::where('employee_id', $employee_id)
            ->whereYear('date', $year)
            ->selectRaw("
            COUNT(CASE WHEN status = 'Present' THEN 1 END) as total_present,
            COUNT(CASE
                WHEN status = 'Absent'
                AND DATE_FORMAT(date, '%w') NOT IN (5)  -- Exclude Fridays (Friday = 5)
                AND date NOT IN (SELECT date FROM holidays WHERE date BETWEEN ? AND ?)
            THEN 1 END) as total_absent,
            COUNT(CASE WHEN late IS NOT NULL AND late != '00:00:00' THEN 1 END) as total_late
        ", [$startDate, $endDate])
            ->first();

        // Total Working Days (Excluding Fridays and Holidays)
        $totalWorkingDays = (int)($totalDays - $totalFridays - $totalHolidays);

        // Calculate Total Work on Holidays
        $totalWorkOnHolidays = Attendance::where('employee_id', $employee_id)
            ->whereIn('date', function ($query) use ($startDate, $endDate) {
                $query->select('date')
                    ->from('holidays')
                    ->whereBetween('date', [$startDate, $endDate]);
            })
            ->where('status', 'Present')
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Yearly attendance summary fetched successfully',
            'data' => [
                'total_working_days' => $totalWorkingDays,
                'total_present' => $summary->total_present ?? 0,
                'total_absent' => $summary->total_absent ?? 0,  // Excluding Fridays & Holidays
                'total_late' => $summary->total_late ?? 0,
                'total_holidays' => $totalHolidays,
                'total_work_on_holidays' => $totalWorkOnHolidays,
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


    public function filterAttendanceListForAllTime(Request $request)
    {
        try {
            // Validate input
            $validatedData = $request->validate([
                'emp_id' => 'nullable|string|exists:employees,emp_id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            // Find the employee by emp_id if provided
            $employee = null;
            if (!empty($validatedData['emp_id'])) {
                $employee = Employee::where('emp_id', $validatedData['emp_id'])->first();

                if (!$employee) {
                    return $this->response(false, 'Employee not found', null, 404);
                }
            }

            // Fetch attendance records with necessary filters
            $attendances = Attendance::with([
                'employee' => function ($query) {
                    $query->select('id', 'user_id', 'first_name', 'last_name', 'email', 'phone_num', 'emp_id')
                        ->with(['user' => function ($userQuery) {
                            $userQuery->select('id', 'profile_photo_path');
                        }]);
                }
            ])
                ->when($employee, function ($query) use ($employee) {
                    return $query->where('employee_id', $employee->id);
                })
                ->when(isset($validatedData['start_date']) && isset($validatedData['end_date']), function ($query) use ($validatedData) {
                    return $query->whereBetween('date', [$validatedData['start_date'], $validatedData['end_date']]);
                })
                ->orderBy('date', 'desc') // Order by date in descending order
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













}
