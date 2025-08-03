<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Notifications\AnnouncementNotification;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AnnouncementController extends Controller
{
    // List announcements based on user role and department
//    public function announcementsList(Request $request)
//    {
//        try {
//            $user = $request->user();
//
//            $announcements = Announcement::query()
//                ->where(function ($query) use ($user) {
//                    $query->where('audience', 'all')
//                        ->orWhereNull('audience')
//                        ->orWhere(function ($subQuery) use ($user) {
//                            if ($user->role_id == 1) {
//                                $subQuery->where('audience', 'only_admins');
//                            } elseif ($user->role_id == 2) {
//                                $subQuery->where('audience', 'only_hrs');
//                            } elseif ($user->role_id == 3) {
//                                $subQuery->where('audience', 'only_employees');
//                            }
//                        });
//                })
//                ->where(function ($query) use ($user) {
//                    $query->whereNull('department_id')
//                        ->orWhere('department_id', $user->department_id);
//                })
//                ->where('is_active', true)
//                ->orderByDesc('start_date')
//                ->get();
//
//            return $this->response(true, 'Announcements retrieved successfully', $announcements, 200);
//        } catch (\Exception $e) {
//            return $this->response(false, 'Something went wrong', $e->getMessage(), 500);
//        }
//    }

//    public function announcementsList(Request $request)
//    {
//        try {
//            $user = $request->user();
//
//            $announcements = Announcement::query()
//                ->where(function ($query) use ($user) {
//                    $query->where('audience', 'all')
//                        ->orWhereNull('audience')
//                        ->orWhere(function ($subQuery) use ($user) {
//                            if ($user->role_id == 1) {
//                                $subQuery->where('audience', 'only_admins');
//                            } elseif ($user->role_id == 2) {
//                                $subQuery->where('audience', 'only_hrs');
//                            } elseif ($user->role_id == 3) {
//                                $subQuery->where('audience', 'only_employees');
//                            }
//                        });
//                })
//                ->where(function ($query) use ($user) {
//                    $query->whereNull('department_id')
//                        ->orWhere('department_id', $user->department_id);
//                })
//                ->where('is_active', true)
//                ->orderByDesc('start_date')
//                ->get()
//                ->map(function ($announcement) {
//                    return [
//                        'id' => $announcement->id,
//                        'title' => $announcement->title,
//                        'description' => $announcement->description,
//                        'department_id' => $announcement->department_id,
//                        'start_date' => $announcement->start_date,
//                        'end_date' => $announcement->end_date,
//                        'announcement_type' => $announcement->announcement_type,
//                        'is_active' => $announcement->is_active,
//                        'audience' => $announcement->audience,
//                        'attachment' => $announcement->attachment,
//                        'created_by' => $announcement->created_by,
//                        'created_at' => $announcement->created_at,
//                        'updated_at' => $announcement->updated_at,
//                    ];
//                });
//
//            return $this->response(true, 'Announcements retrieved successfully', $announcements, 200);
//        } catch (\Exception $e) {
//            return $this->response(false, 'Something went wrong', $e->getMessage(), 500);
//        }
//    }
    public function announcementsList(Request $request)
    {
        try {
            $user = $request->user();
            $departmentId = $user->employee->dept_id ?? null;

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                    'data' => null
                ], 401);
            }

            $announcements = Announcement::with('creator')
                ->where(function ($query) use ($user) {
                    $query->where('audience', 'all')
                        ->orWhereNull('audience')
                        ->orWhere(function ($subQuery) use ($user) {
                            if ($user->role_id == 1) {
                                $subQuery->where('audience', 'only_admins');
                            } elseif ($user->role_id == 2) {
                                $subQuery->where('audience', 'only_hrs');
                            } elseif ($user->role_id == 3) {
                                $subQuery->where('audience', 'only_employees');
                            }elseif ($user->role_id == 5) {
                                $subQuery->where('audience', 'only_supervisors');
                            } elseif ($user->role_id == 6) {
                                $subQuery->where('audience', 'only_accountants');
                            } elseif ($user->role_id == 7) {
                                $subQuery->where('audience', 'only_gm');
                            } elseif ($user->role_id == 8) {
                                $subQuery->where('audience', 'only_ceo');
                            }
                        });
                })
                ->where(function ($query) use ($departmentId) {
                    $query->whereNull('department_id')
                        ->orWhere('department_id', $departmentId);
                })
                ->where('is_active', true)
                ->orderByDesc('start_date')
                ->get()
                ->map(function ($announcement) {
                    return [
                        'id' => $announcement->id,
                        'title' => $announcement->title,
                        'description' => $announcement->description,
                        'department_id' => $announcement->department_id,
                        'department_name' => $announcement->department->name ?? null,
                        'start_date' => $announcement->start_date,
                        'end_date' => $announcement->end_date,
                        'announcement_type' => $announcement->announcement_type,
                        'is_active' => $announcement->is_active,
                        'audience' => $announcement->audience,
                        'attachment' => $announcement->attachment
                            ? asset('storage/' . $announcement->attachment)
                            : null,
//                        'attachment' => $announcement->attachment,
//                        'created_by' => $announcement->created_by,
                        'created_by' => $announcement->creator?->first_name . $announcement->creator?-> first_name?? 'Unknown',
                        'created_at' => $announcement->created_at,
                        'updated_at' => $announcement->updated_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Announcements retrieved successfully',
                'data' => $announcements
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    // Create new announcement
//    public function createAnnouncement(Request $request)
//    {
//        try {
//            $validated = $request->validate([
//                'title' => 'required|string|max:255',
//                'description' => 'nullable|string',
//                'department_id' => 'nullable|exists:departments,id',
//                'start_date' => 'required|date',
//                'end_date' => 'nullable|date|after_or_equal:start_date',
//                'announcement_type' => 'nullable|in:holiday,general,emergency',
//                'is_active' => 'boolean',
//                'audience' => 'nullable|string',
//                'attachment' => 'nullable|json',
//            ]);
//
//            $validated['created_by'] = auth()->id();
//
//            $announcement = Announcement::create($validated);
//
//            return $this->response(true, 'Announcement created successfully', $announcement, 201);
//        } catch (ValidationException $e) {
//            return $this->response(false, 'Validation Error', $e->errors(), 422);
//        } catch (\Exception $e) {
//            return $this->response(false, 'Something went wrong', $e->getMessage(), 500);
//        }
//    }

    public function createAnnouncement(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'department_id' => 'nullable|exists:departments,id',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'announcement_type' => 'nullable|in:holiday,general,emergency,event',
                'is_active' => 'boolean',
                'audience' => 'nullable|string',
                'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
            ]);

            // Create announcement instance
            $announcement = new Announcement();
            $announcement->title = $validated['title'];
            $announcement->description = $validated['description'] ?? null;
            $announcement->department_id = $validated['department_id'] ?? null;
            $announcement->start_date = $validated['start_date'];
            $announcement->end_date = $validated['end_date'] ?? null;
            $announcement->announcement_type = $validated['announcement_type'] ?? null;
            $announcement->is_active = $validated['is_active'] ?? true;
            $announcement->audience = $validated['audience'] ?? null;
            $announcement->created_by = auth()->id();

            // Handle file upload
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $path = $file->store('announcements', 'public'); // storage/app/public/announcements
                $announcement->attachment = $path;
            }

            $announcement->save();


            try {
                $audience = $announcement->audience;
                $departmentId = $announcement->department_id;

                // Step 1: Fetch users who match both audience and department logic
                $users = User::whereHas('employee', function ($query) use ($departmentId) {
                    if ($departmentId) {
                        $query->where('dept_id', $departmentId);
                    }
                })
                    ->where(function ($query) use ($audience) {
                        $query->when($audience === 'all' || $audience === null, function ($q) {
                            // Do nothing, allow all
                        })
                            ->when($audience === 'only_employees', function ($q) {
                                $q->where('role', 3);
                            })
                            ->when($audience === 'only_admins', function ($q) {
                                $q->where('role', 1);
                            })
                            ->when($audience === 'only_hrs', function ($q) {
                                $q->where('role', 2);
                            })
                            ->when($audience === 'only_supervisors', function ($q) {
                                $q->where('role', 5);
                            })
                            ->when($audience === 'only_accountants', function ($q) {
                                $q->where('role', 6);
                            })
                            ->when($audience === 'only_gm', function ($q) {
                                $q->where('role', 7);
                            })
                            ->when($audience === 'only_ceo', function ($q) {
                                $q->where('role', 8);
                            });
                    })
                    ->get();

                // Step 2: Notify each matched user
                foreach ($users as $user) {
                    $user->notify(new AnnouncementNotification([
                        'title' => $announcement->title,
                        'description' => $announcement->description,
                        'announcement_type' => $announcement->announcement_type,
                        'id' => $announcement->id,
                    ]));
                }

            } catch (\Exception $e) {
                \Log::error('Failed to send announcement notifications: ' . $e->getMessage());
            }


            return $this->response(true, 'Announcement created successfully', $announcement, 201);
        } catch (ValidationException $e) {
            return $this->response(false, 'Validation Error', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->response(false, 'Something went wrong', $e->getMessage(), 500);
        }
    }



    // Show single announcement
    public function announcementDetails($id)
    {
        try {
            $announcement = Announcement::find($id);

            if (!$announcement) {
                return $this->response(false, 'Announcement not found', null, 404);
            }

            return $this->response(true, 'Announcement retrieved successfully', $announcement, 200);
        } catch (\Exception $e) {
            return $this->response(false, 'Something went wrong', $e->getMessage(), 500);
        }
    }

    // Update announcement
    public function editAnnouncement(Request $request, $id)
    {
        try {
            $announcement = Announcement::findOrFail($id);

            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'department_id' => 'nullable|exists:departments,id',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'announcement_type' => 'nullable|in:holiday,general,emergency',
                'is_active' => 'boolean',
                'audience' => 'nullable|string',
                'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
            ]);

            // Handle file upload if present
            if ($request->hasFile('attachment')) {
                // Optional: delete old file if exists
                if ($announcement->attachment && \Storage::disk('public')->exists($announcement->attachment)) {
                    \Storage::disk('public')->delete($announcement->attachment);
                }

                // Store new file
                $validated['attachment'] = $request->file('attachment')->store('announcements', 'public');
            }

            // Update announcement with validated data
            $announcement->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Announcement updated successfully',
                'data' => $announcement
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


    // Delete announcement
    public function deleteAnnouncement($id)
    {
        try {
            $announcement = Announcement::find($id);

            if (!$announcement) {
                return $this->response(false, 'Announcement not found', null, 404);
            }

            $announcement->delete();

            return $this->response(true, 'Announcement deleted successfully', null, 200);
        } catch (\Exception $e) {
            return $this->response(false, 'Something went wrong', $e->getMessage(), 500);
        }
    }

    // Common response method (if not already in base controller)
    public function response($success, $message, $data, $code)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ], $code);
    }
}



