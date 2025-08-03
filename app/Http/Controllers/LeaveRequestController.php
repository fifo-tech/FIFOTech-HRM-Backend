<?php

namespace App\Http\Controllers;

use App\Mail\TestMail;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Notifications\LeaveRequestNotification;
use App\Notifications\LeaveStatusUpdateNotification;


class LeaveRequestController extends Controller
{
    /**
     * Get a list of all leave requests.
     */
    public function getAllLeaveRequestsList()
    {
        try {
            $leaves = LeaveRequest::with(['employee' => function ($query) {
                $query->select('id', 'first_name', 'last_name', 'email', 'phone_num', 'dept_id', 'emp_id', 'user_id')
                    ->with(['user:id,profile_photo_path']);
            }])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($leave) {
                    return [
                        'id' => $leave->id,
                        'leave_type' => $leave->leave_type,
                        'employee' => [
                            'id' => $leave->employee->id,
                            'first_name' => $leave->employee->first_name,
                            'last_name' => $leave->employee->last_name,
                            'email' => $leave->employee->email,
                            'phone_num' => $leave->employee->phone_num,
                            'dept_id' => $leave->employee->dept_id,
                            'emp_id' => $leave->employee->emp_id,
                            // Fetching profile_photo_path from the related user
                            'image' => $leave->employee->user->profile_photo_path
                                ? url('storage/' . $leave->employee->user->profile_photo_path)
                                : null,

                        ],
                        'start_date' => $leave->start_date,
                        'end_date' => $leave->end_date,
                        'leave_duration' => $leave->start_date . ' to ' . $leave->end_date,
                        'leave_days' => \Carbon\Carbon::parse($leave->start_date)
                                ->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1, // Inclusive of both days
                        'remarks' => $leave->remarks,
                        'leave_reason' => $leave->leave_reason,
                        'leave_attachment' => $leave->leave_attachment
                            ? url('storage/' .  $leave->leave_attachment)
                            : null,
                        'status' => $leave->status,
                        'approved_by' => $leave->approved_by,
                        'created_at' => $leave->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $leave->updated_at->format('Y-m-d H:i:s'),
                    ];
                });

            return $this->response(
                true,
                'Leave requests retrieved successfully',
                $leaves,
                200
            );
        } catch (\Exception $e) {
            return $this->response(
                false,
                'Something went wrong',
                $e->getMessage(),
                500
            );
        }
    }



    public function getSelfLeaveRequestsList()
    {
        try {
            // Get the logged-in user
            $loggedInUser = auth()->user();

            $leaves = LeaveRequest::with(['employee' => function ($query) {
                $query->select('id', 'first_name', 'last_name', 'email', 'phone_num', 'dept_id', 'emp_id', 'user_id')
                    ->with(['user:id,profile_photo_path']);
            }])
                // Filter leave requests for the logged-in user's employee
                ->whereHas('employee', function ($query) use ($loggedInUser) {
                    $query->where('user_id', $loggedInUser->id);
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($leave) {
                    return [
                        'id' => $leave->id,
                        'leave_type' => $leave->leave_type,
                        'employee' => [
                            'id' => $leave->employee->id,
                            'first_name' => $leave->employee->first_name,
                            'last_name' => $leave->employee->last_name,
                            'email' => $leave->employee->email,
                            'phone_num' => $leave->employee->phone_num,
                            'dept_id' => $leave->employee->dept_id,
                            'emp_id' => $leave->employee->emp_id,
                            'image' => $leave->employee->user->profile_photo_path
                                ? url('storage/' . $leave->employee->user->profile_photo_path)
                                : null,
                        ],
                        'start_date' => $leave->start_date,
                        'end_date' => $leave->end_date,
                        'leave_duration' => $leave->start_date . ' to ' . $leave->end_date,
                        'leave_days' => \Carbon\Carbon::parse($leave->start_date)
                                ->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1, // Inclusive of both days
                        'remarks' => $leave->remarks,
                        'leave_reason' => $leave->leave_reason,
                        'approved_by' => $leave->approved_by,
                        'leave_attachment' => $leave->leave_attachment
                            ? url('storage/' .  $leave->leave_attachment)
                            : null,
                        'status' => $leave->status,
                        'created_at' => $leave->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $leave->updated_at->format('Y-m-d H:i:s'),
                    ];
                });

            return $this->response(
                true,
                'Leave requests retrieved successfully',
                $leaves,
                200
            );
        } catch (\Exception $e) {
            return $this->response(
                false,
                'Something went wrong',
                $e->getMessage(),
                500
            );
        }
    }



    /**
     * Create a new leave request.
     */
    public function createLeaveRequest(Request $request)
    {
        try {
            // Validate request
            $request->validate([
//                'employee_name'=> 'required|string',
                'leave_type' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
//                'half_day' => 'boolean',
                'remarks' => 'nullable|string',
                'leave_reason' => 'required|string',
                'leave_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf', // File validation
            ]);

            // Initialize leave_attachment path
            $leaveAttachmentPath = null;

            // Check if the file is uploaded
            if ($request->hasFile('leave_attachment')) {
                // Store the file and get its path
                $leaveAttachmentPath = $request->file('leave_attachment')->store('leave_attachments', 'public');
            }

            // Create leave request
            $leaveRequest = LeaveRequest::create([
                'employee_id' => auth()->user()->id, // Automatically assign the logged-in user's ID
//                'employee_name' => auth()->user()->name, // Use the logged-in user's name instead of passing it in the request
//                'employee_name' =>$request->employee_name,
                'leave_type' => $request->leave_type,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
//                'half_day' => $request->half_day,
                'remarks' => $request->remarks,
                'leave_reason' => $request->leave_reason,
                'leave_attachment' => $leaveAttachmentPath, // Save the file path if uploaded
            ]);

            // Notify HR (handle notification errors separately)
            try {
                $admins = User::where('role_id', '2')->get();

                foreach ($admins as $admin) {
                    $admin->notify(new LeaveRequestNotification([
                        'id' => $leaveRequest->id,
                        'employee_name' => auth()->user()->first_name . ' ' . auth()->user()->last_name,
                        'from_date' => $leaveRequest->start_date,
                        'to_date' => $leaveRequest->end_date,
                        'leave_reason' => $leaveRequest->leave_reason,
                    ]));
                }
            } catch (\Exception $notifyError) {
                \Log::error('Leave notification failed: ' . $notifyError->getMessage());
                // Optional: you can include this info in response if you want
            }

            // Practice
//            $code = rand(100000, 999999);
//            $userName = "Mostafiz";

//            Mail::to('mostafizurrahmanripon03@gmail.com')->send(new TestMail($code, $userName));

            return response()->json([
                'success' => true,
                'message' => 'Leave request created successfully',
                'data' => $leaveRequest
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }




    /**
     * Update a leave request.
     */
//    public function editLeaveRequest(Request $request, $id)
//    {
//        try {
//            $leaveRequest = LeaveRequest::findOrFail($id);
//
//            // Validate request
//            $validated = $request->validate([
//                'leave_type' => 'sometimes|required|string',
//                'start_date' => 'sometimes|required|date',
//                'end_date' => 'sometimes|required|date|after_or_equal:start_date',
////                'half_day' => 'sometimes|boolean',
//                'remarks' => 'nullable|string',
//                'leave_reason' => 'sometimes|required|string',
//                'leave_attachment' => 'nullable|string',
////                'status' => 'in:pending,approved,rejected',
////                'approved_by' => 'nullable|exists:employees,id'
//            ]);
//
//            // Update leave request
//            $leaveRequest->update($validated);
//
//            return $this->response(
//                true,
//                'Leave request updated successfully',
//                $leaveRequest,
//                200
//            );
//        } catch (ValidationException $e) {
//            return $this->response(
//                false,
//                'Validation Error',
//                $e->errors(),
//                422
//            );
//        } catch (\Exception $e) {
//            return $this->response(
//                false,
//                'Something went wrong',
//                $e->getMessage(),
//                500
//            );
//        }
//    }


//    public function editLeaveRequest(Request $request, $id)
//    {
//        try {
//            $leaveRequest = LeaveRequest::findOrFail($id);
//
//            // Validate request
//            $validated = $request->validate([
//                'leave_type' => 'sometimes|required|string',
//                'start_date' => 'sometimes|required|date',
//                'end_date' => 'sometimes|required|date|after_or_equal:start_date',
//                'remarks' => 'nullable|string',
//                'leave_reason' => 'sometimes|required|string',
//                'leave_attachment' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:2048',
//            ]);
//
//            // Handle file upload
//            if ($request->hasFile('leave_attachment')) {
//                $file = $request->file('leave_attachment');
//                $filePath = $file->store('leave_attachments', 'public');
//                $validated['leave_attachment'] = $filePath;
//            }
//
//            // Update leave request
//            $leaveRequest->update($validated);
//
//            return response()->json([
//                'success' => true,
//                'message' => 'Leave request updated successfully',
//                'data' => [
//                    'id' => $leaveRequest->id,
//                    'leave_type' => $leaveRequest->leave_type,
//                    'start_date' => $leaveRequest->start_date,
//                    'end_date' => $leaveRequest->end_date,
//                    'remarks' => $leaveRequest->remarks,
//                    'leave_reason' => $leaveRequest->leave_reason,
//                    'leave_attachment' => $leaveRequest->leave_attachment
//                        ? url('storage/' . $leaveRequest->leave_attachment)
//                        : null,
//                ]
//            ], 200);
//        } catch (ValidationException $e) {
//            return response()->json([
//                'success' => false,
//                'message' => 'Validation Error',
//                'errors' => $e->errors()
//            ], 422);
//        } catch (\Exception $e) {
//            return response()->json([
//                'success' => false,
//                'message' => 'Something went wrong',
//                'error' => $e->getMessage()
//            ], 500);
//        }
//    }

    public function editLeaveRequest(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::find($id);

        if (!$leaveRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Leave request not found',
            ], 404);
        }

        if (strcasecmp($leaveRequest->status, 'pending') !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'Only leave requests with status "Pending" can be edited.',
            ], 403);
        }


        try {
            // Validate request
            $validated = $request->validate([
                'leave_type' => 'sometimes|required|string',
                'start_date' => 'sometimes|required|date',
                'end_date' => 'sometimes|required|date|after_or_equal:start_date',
                'remarks' => 'nullable|string',
                'leave_reason' => 'sometimes|required|string',
                'leave_attachment' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:2048',
            ]);

            // Handle file upload
            if ($request->hasFile('leave_attachment')) {
                if ($leaveRequest->leave_attachment) {
                    Storage::disk('public')->delete($leaveRequest->leave_attachment);
                }

                $filePath = $request->file('leave_attachment')->store('leave_attachments', 'public');
                $validated['leave_attachment'] = $filePath;
            }

            // Update leave request
            $leaveRequest->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Leave request updated successfully',
                'data' => [
                    'id' => $leaveRequest->id,
                    'leave_type' => $leaveRequest->leave_type,
                    'start_date' => $leaveRequest->start_date,
                    'end_date' => $leaveRequest->end_date,
                    'remarks' => $leaveRequest->remarks,
                    'leave_reason' => $leaveRequest->leave_reason,
                    'leave_attachment' => $leaveRequest->leave_attachment
                        ? url('storage/' . $leaveRequest->leave_attachment)
                        : null,
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }






//    public function leaveRequestDetails($id)
//    {
//        try {
//            // Fetch the leave request details along with related employee and leave type
//            $leaveRequest = LeaveRequest::find($id);
//
//            // Check if leave request exists
//            if (!$leaveRequest) {
//                return response()->json([
//                    'success' => false,
//                    'message' => 'Leave request not found'
//                ], 404);
//            }
//
//            return response()->json([
//                'success' => true,
//                'data' => $leaveRequest
//            ]);
//        } catch (\Exception $e) {
//            return response()->json([
//                'success' => false,
//                'message' => 'Something went wrong!',
//                'error' => $e->getMessage()
//            ], 500);
//        }
//    }

    public function leaveRequestDetails($id)
    {
        try {
            $leaveRequest = LeaveRequest::find($id);

            if (!$leaveRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Leave request not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $leaveRequest->id,
                    'leave_type' => $leaveRequest->leave_type,
                    'start_date' => $leaveRequest->start_date,
                    'end_date' => $leaveRequest->end_date,
                    'remarks' => $leaveRequest->remarks,
                    'leave_reason' => $leaveRequest->leave_reason,
                    'leave_attachment' => $leaveRequest->leave_attachment
                        ? url('storage/' . $leaveRequest->leave_attachment)
                        : null,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    /**
     * Delete a leave request.
     */
    public function deleteLeaveRequest($id)
    {
        try {
            $leaveRequest = LeaveRequest::find($id);

            if (!$leaveRequest) {
                return $this->response(
                    false,
                    'Leave request not found',
                    null,
                    404
                );
            }

            // Case-insensitive check for 'Pending'
            if (strcasecmp($leaveRequest->status, 'Pending') !== 0) {
                return $this->response(
                    false,
                    'Only leave requests with status "Pending" can be deleted.',
                    null,
                    403
                );
            }

            $leaveRequest->delete();

            return $this->response(
                true,
                'Leave request deleted successfully',
                null,
                200
            );
        } catch (\Exception $e) {
            return $this->response(
                false,
                'Something went wrong',
                $e->getMessage(),
                500
            );
        }
    }


    public function deleteLeaveAttachment($id)
    {
        $leaveRequest = LeaveRequest::find($id);

        if (!$leaveRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Leave request not found.',
            ], 404);
        }

        // Check if the leave request status is 'Pending'
        if (strcasecmp($leaveRequest->status, 'pending') !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'Only leave requests with status "Pending" can have their attachment deleted.',
            ], 403); // 403 Forbidden status code
        }

        // Proceed with the attachment deletion
        if ($leaveRequest->leave_attachment) {
            Storage::disk('public')->delete($leaveRequest->leave_attachment);
        }

        $leaveRequest->leave_attachment = null;
        $leaveRequest->save();

        return response()->json([
            'success' => true,
            'message' => 'Leave attachment deleted successfully.',
        ]);
    }







    /**
     * Custom Response Helper.
     */
    public function response($success, $message, $data = null, $statusCode = 200)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }



    public function getDepartmentWiseLeaveRequests()
    {
        try {
            // Fetch the logged-in user
            $user = Auth::user();

            // Check if the user is valid and has an employee record
            if (!$user || !$user->employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found or not associated with an employee record',
                ], 404);
            }

            // Get the department ID of the logged-in user
            $departmentId = $user->employee->dept_id;
            $userId = $user->id; // Logged-in user ID

            // Check if the logged-in user is an HR Manager or Supervisor (role_id = 5)
            if ($user->role_id == 5 || $user->role_id == 2) {
                // Fetch leave requests for the department excluding the logged-in user's own leave request
                $leaves = LeaveRequest::with(['employee' => function ($query) {
                    $query->select('id', 'first_name', 'last_name', 'email', 'phone_num', 'dept_id', 'emp_id', 'user_id')
                        ->with(['user:id,profile_photo_path']);
                }])
                    ->whereHas('employee', function ($q) use ($departmentId) {
                        // Fetch employees belonging to the logged-in user's department
                        $q->where('dept_id', $departmentId);
                    })
                    ->where('employee_id', '!=', $userId) // Exclude the logged-in user's leave request
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($leave) {
                        return [
                            'id' => $leave->id,
                            'leave_type' => $leave->leave_type,
                            'employee' => [
                                'id' => $leave->employee->id,
                                'first_name' => $leave->employee->first_name,
                                'last_name' => $leave->employee->last_name,
                                'email' => $leave->employee->email,
                                'phone_num' => $leave->employee->phone_num,
                                'dept_id' => $leave->employee->dept_id,
                                'emp_id' => $leave->employee->emp_id,
                                'image' => $leave->employee->user->profile_photo_path
                                    ? url('storage/' . $leave->employee->user->profile_photo_path)
                                    : null,
                            ],
                            'start_date' => $leave->start_date,
                            'end_date' => $leave->end_date,
                            'leave_duration' => $leave->start_date . ' to ' . $leave->end_date,
                            'leave_days' => \Carbon\Carbon::parse($leave->start_date)
                                    ->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1,
                            'remarks' => $leave->remarks,
                            'leave_reason' => $leave->leave_reason,
                            'leave_attachment' => $leave->leave_attachment
                                ? url('storage/' .  $leave->leave_attachment)
                                : null,
                            'status' => $leave->status,
                            'approved_by' => $leave->approved_by,
                            'created_at' => $leave->created_at->format('Y-m-d H:i:s'),
                            'updated_at' => $leave->updated_at->format('Y-m-d H:i:s'),
                        ];
                    });

                return response()->json([
                    'success' => true,
                    'message' => 'Department leave requests retrieved successfully (excluding own)',
                    'data' => $leaves,
                ], 200);
            }

            // If the user is not an HR Manager, return an error
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view leave requests for your department.',
            ], 403);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function departmentWiseLeaveRequestApprove(Request $request, $id)
    {
        try {
            // Validate the request
            $request->validate([
                'status' => 'required|in:Pending,Approved,Rejected', // Ensure valid status
            ]);

            // Fetch the logged-in user
            $user = Auth::user();

            // Ensure the user is an HR Manager (role_id = 5)
            if (!$user || $user->role_id != 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized! Only Supervisor can approve/reject leave requests.',
                ], 403);
            }

            // Find the leave request by ID
            $leaveRequest = LeaveRequest::with('employee')->findOrFail($id);

            // Ensure the leave request belongs to the same department as the Supervisor
            if ($leaveRequest->employee->dept_id != $user->employee->dept_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only approve/reject leave requests from your department.',
                ], 403);
            }

            // Update leave request status and approver
            $leaveRequest->status = $request->status;
//            $leaveRequest->approved_by = $user->id; // Store the ID of the Supervisor who approved/rejected it
            $leaveRequest->approved_by = $user->first_name . ' ' . $user->last_name;
            $leaveRequest->updated_at = now();
            $leaveRequest->save();

            return response()->json([
                'success' => true,
                'message' => 'Leave request updated successfully',
                'data' => [
                    'id' => $leaveRequest->id,
                    'status' => $leaveRequest->status,
                    'approved_by' => $leaveRequest->approved_by,
                    'created_at' => $leaveRequest->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $leaveRequest->updated_at->format('Y-m-d H:i:s'),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function LeaveRequestApproveByHr(Request $request, $id)
    {
        try {
            // Validate the request
            $request->validate([
                'status' => 'required|in:Pending,Approved,Rejected',
            ]);

            // Fetch the logged-in user
            $user = Auth::user();

            // Check if user is HR
            if (!$user || $user->role_id != 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized! Only HR can approve/reject leave requests through this endpoint.',
                ], 403);
            }

            // Find the leave request by ID with employee and user's role
            $leaveRequest = LeaveRequest::with(['employee.user'])->findOrFail($id);

            $employeeUser = optional($leaveRequest->employee)->user;

            // If employee's user role is HR (2) , HR can't approve
            if ($employeeUser && in_array($employeeUser->role_id, [ 2 ])) {
                return response()->json([
                    'success' => false,
                    'message' => 'HR cannot approve/reject leave requests for himself/herself.',
                ], 403);
            }

            // Update leave request
            $leaveRequest->status = $request->status;
            $leaveRequest->approved_by = $user->first_name . ' ' . $user->last_name;
            $leaveRequest->updated_at = now();
            $leaveRequest->save();

            if ($employeeUser) {
                $employeeName = $employeeUser->name ?? ($employeeUser->first_name . ' ' . $employeeUser->last_name);

                $employeeUser->notify(new LeaveStatusUpdateNotification(
                    $leaveRequest,
                    $leaveRequest->status,
                    $leaveRequest->approved_by,
                    $employeeName
                ));
            }


            return response()->json([
                'success' => true,
                'message' => 'Leave request updated successfully by HR.',
                'data' => [
                    'id' => $leaveRequest->id,
                    'status' => $leaveRequest->status,
                    'approved_by' => $leaveRequest->approved_by,
                    'created_at' => $leaveRequest->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $leaveRequest->updated_at->format('Y-m-d H:i:s'),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function getHrAdminLeaveRequests()
    {
        try {
            $leaves = LeaveRequest::with(['employee' => function ($query) {
                $query->select('id', 'first_name', 'last_name', 'email', 'phone_num', 'dept_id', 'emp_id', 'user_id')
                    ->whereHas('user', function ($q) {
                        $q->whereIn('role_id', [1, 2]); //  Filter by role_id Admin (1) or HR (2)
                    })
                    ->with(['user:id,profile_photo_path,role_id']);
            }])
                ->whereHas('employee.user', function ($query) {
                    $query->whereIn('role_id', [1, 2]); //  Ensures only leave requests by users with role_id 1/2
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($leave) {
                    return [
                        'id' => $leave->id,
                        'leave_type' => $leave->leave_type,
                        'employee' => [
                            'id' => $leave->employee->id,
                            'first_name' => $leave->employee->first_name,
                            'last_name' => $leave->employee->last_name,
                            'email' => $leave->employee->email,
                            'phone_num' => $leave->employee->phone_num,
                            'dept_id' => $leave->employee->dept_id,
                            'emp_id' => $leave->employee->emp_id,
                            'image' => $leave->employee->user->profile_photo_path
                                ? url('storage/' . $leave->employee->user->profile_photo_path)
                                : null,
                        ],
                        'start_date' => $leave->start_date,
                        'end_date' => $leave->end_date,
                        'leave_duration' => $leave->start_date . ' to ' . $leave->end_date,
                        'leave_days' => \Carbon\Carbon::parse($leave->start_date)
                                ->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1,
                        'remarks' => $leave->remarks,
                        'leave_reason' => $leave->leave_reason,
                        'leave_attachment' => $leave->leave_attachment
                            ? url('storage/' .  $leave->leave_attachment)
                            : null,
                        'status' => $leave->status,
                        'approved_by' => $leave->approved_by,
                        'created_at' => $leave->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $leave->updated_at->format('Y-m-d H:i:s'),
                    ];
                });

            return $this->response(
                true,
                'Leave requests retrieved successfully',
                $leaves,
                200
            );
        } catch (\Exception $e) {
            return $this->response(
                false,
                'Something went wrong',
                $e->getMessage(),
                500
            );
        }
    }


    public function LeaveRequestApproveByGm(Request $request, $id)
    {
        try {
            // Validate the request
            $request->validate([
                'status' => 'required|in:Pending,Approved,Rejected',
            ]);

            // Fetch the logged-in user
            $user = Auth::user();

            // Check if user is GM (role_id = 7)
            if (!$user || $user->role_id != 7) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized! Only GM can approve/reject leave requests through this endpoint.',
                ], 403);
            }

            // Find the leave request with employee and user's role
            $leaveRequest = LeaveRequest::with(['employee.user'])->findOrFail($id);

            $employeeUser = optional($leaveRequest->employee)->user;

            // If employee's user role is NOT HR (2) or Admin (1), GM cannot approve
            if (!$employeeUser || !in_array($employeeUser->role_id, [1, 2])) {
                return response()->json([
                    'success' => false,
                    'message' => 'GM can only approve/reject leave requests submitted by HR or Admin.',
                ], 403);
            }

            // Update leave request
            $leaveRequest->status = $request->status;
            $leaveRequest->approved_by = $user->first_name . ' ' . $user->last_name;
            $leaveRequest->updated_at = now();
            $leaveRequest->save();

            return response()->json([
                'success' => true,
                'message' => 'Leave request updated successfully by GM.',
                'data' => [
                    'id' => $leaveRequest->id,
                    'status' => $leaveRequest->status,
                    'approved_by' => $leaveRequest->approved_by,
                    'created_at' => $leaveRequest->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $leaveRequest->updated_at->format('Y-m-d H:i:s'),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function getGmLeaveRequest()
    {
        try {
            $user = Auth::user();

            // Check if the user is a GM
            if (!$user || $user->role_id != 8) {
                return $this->response(
                    false,
                    'Unauthorized! Only CEO can access this data.',
                    null,
                    403
                );
            }

            $leaves = LeaveRequest::with(['employee' => function ($query) {
                $query->select('id', 'first_name', 'last_name', 'email', 'phone_num', 'dept_id', 'emp_id', 'user_id')
                    ->whereHas('user', function ($q) {
                        $q->whereIn('role_id', [ 7 ]); //  Filter by role_id GM (8)
                    })
                    ->with(['user:id,profile_photo_path,role_id']);
            }])
                ->whereHas('employee.user', function ($query) {
                    $query->whereIn('role_id', [ 7 ]); //  Ensures only leave requests by users with role_id 8 (GM)
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($leave) {
                    return [
                        'id' => $leave->id,
                        'leave_type' => $leave->leave_type,
                        'employee' => [
                            'id' => $leave->employee->id,
                            'first_name' => $leave->employee->first_name,
                            'last_name' => $leave->employee->last_name,
                            'email' => $leave->employee->email,
                            'phone_num' => $leave->employee->phone_num,
                            'dept_id' => $leave->employee->dept_id,
                            'emp_id' => $leave->employee->emp_id,
                            'image' => $leave->employee->user->profile_photo_path
                                ? url('storage/' . $leave->employee->user->profile_photo_path)
                                : null,
                        ],
                        'start_date' => $leave->start_date,
                        'end_date' => $leave->end_date,
                        'leave_duration' => $leave->start_date . ' to ' . $leave->end_date,
                        'leave_days' => \Carbon\Carbon::parse($leave->start_date)
                                ->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1,
                        'remarks' => $leave->remarks,
                        'leave_reason' => $leave->leave_reason,
                        'leave_attachment' => $leave->leave_attachment
                            ? url('storage/' .  $leave->leave_attachment)
                            : null,
                        'status' => $leave->status,
                        'approved_by' => $leave->approved_by,
                        'created_at' => $leave->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $leave->updated_at->format('Y-m-d H:i:s'),
                    ];
                });

            return $this->response(
                true,
                'Leave requests retrieved successfully',
                $leaves,
                200
            );
        } catch (\Exception $e) {
            return $this->response(
                false,
                'Something went wrong',
                $e->getMessage(),
                500
            );
        }
    }



    public function LeaveRequestApproveByCeo(Request $request, $id)
    {
        try {
            // Validate the request
            $request->validate([
                'status' => 'required|in:Pending,Approved,Rejected',
            ]);

            // Fetch the logged-in user
            $user = Auth::user();

            // Check if user is CEO (role_id = 8)
            if (!$user || $user->role_id != 8) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized! Only CEO can approve/reject leave requests through this endpoint.',
                ], 403);
            }

            // Find the leave request with employee and user's role
            $leaveRequest = LeaveRequest::with(['employee.user'])->findOrFail($id);

            $employeeUser = optional($leaveRequest->employee)->user;

            // If employee's user role is NOT GM(7) CEO cannot approve
            if (!$employeeUser || !in_array($employeeUser->role_id, [7])) {
                return response()->json([
                    'success' => false,
                    'message' => 'CEO can only approve/reject leave requests submitted by GM.',
                ], 403);
            }

            // Update leave request
            $leaveRequest->status = $request->status;
            $leaveRequest->approved_by = $user->first_name . ' ' . $user->last_name;
            $leaveRequest->updated_at = now();
            $leaveRequest->save();

            return response()->json([
                'success' => true,
                'message' => 'Leave request updated successfully by CEO.',
                'data' => [
                    'id' => $leaveRequest->id,
                    'status' => $leaveRequest->status,
                    'approved_by' => $leaveRequest->approved_by,
                    'created_at' => $leaveRequest->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $leaveRequest->updated_at->format('Y-m-d H:i:s'),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }















}
