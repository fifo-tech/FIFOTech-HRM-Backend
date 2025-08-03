<?php

namespace App\Http\Controllers;

use App\Models\Policy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PolicyController extends Controller
{
    // Get all active or recent policies
    public function policiesList(Request $request)
    {
        try {
            $user = $request->user();
            $departmentId = $user->employee->dept_id ?? null;

            $policies = Policy::query()
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
                ->with(['creator', 'department'])
                ->latest()
                ->get()
                ->map(function ($policy) {
                    return [
                        'id' => $policy->id,
                        'title' => $policy->title,
                        'description' => $policy->description,
                        'category' => $policy->category,
                        'department_id' => $policy->department_id,
                        'department_name' => $policy->department->name ?? null,
                        'audience' => $policy->audience,
                        'is_active' => $policy->is_active,
                        'attachment' => $policy->attachment
                            ? asset('storage/' . $policy->attachment)
                            : null,
                        'created_by' => $policy->creator?->first_name . " " . $policy->creator?->last_name ?? 'Unknown',
                        'created_at' => $policy->created_at,
                        'updated_at' => $policy->updated_at,
                    ];
                });

//            return $this->response(true, 'Policies fetched successfully', $policies, 200);
            return response()->json([
                'success' => true,
                'message' => 'Policy retrieved successfully',
                'data' => $policies
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Store new policy
    public function createPolicy(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category' => 'nullable|string|max:100',
                'department_id' => 'nullable|exists:departments,id',
                'audience' => 'nullable|string',
                'is_active' => 'boolean',
                'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
            ]);

            if ($request->hasFile('attachment')) {
                $validated['attachment'] = $request->file('attachment')->store('hr-policies', 'public');
            }

            $validated['created_by'] = auth()->id();

            $policy = Policy::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Policy created successfully',
                'data' => $policy
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
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


    // Get a single policy
    public function policyDetails($id)
    {
        try {
            $policy = Policy::with(['creator', 'department'])->find($id);

            if (!$policy) {
                return $this->response(false, 'Policy not found', null, 404);
            }

            $data = [
                'id' => $policy->id,
                'title' => $policy->title,
                'description' => $policy->description,
                'category' => $policy->category,
                'department_name' => $policy->department->name ?? null,
                'audience' => $policy->audience,
                'is_active' => $policy->is_active,
                'attachment' => $policy->attachment
                    ? asset('storage/' . $policy->attachment)
                    : null,
                'created_by' => $policy->creator?->first_name ?? 'Unknown',
                'created_at' => $policy->created_at,
            ];

            return $this->response(true, 'Policy retrieved successfully', $data, 200);
        } catch (\Exception $e) {
            return $this->response(false, 'Something went wrong', $e->getMessage(), 500);
        }
    }

    // Update policy
    public function editPolicy(Request $request, $id)
    {
        try {
            $policy = Policy::findOrFail($id);

            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'category' => 'nullable|string',
                'department_id' => 'nullable|exists:departments,id',
                'audience' => 'nullable|string',
                'is_active' => 'boolean',
                'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
            ]);

            if ($request->hasFile('attachment')) {
                if ($policy->attachment) {
                    Storage::disk('public')->delete($policy->attachment);
                }

                $validated['attachment'] = $request->file('attachment')->store('hr-policies', 'public');
            }

            $policy->update($validated);

            return $this->response(true, 'Policy updated successfully', $policy, 200);
        } catch (ValidationException $e) {
            return $this->response(false, 'Validation Error', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->response(false, 'Something went wrong', $e->getMessage(), 500);
        }
    }

    // Delete policy
    public function deletePolicy($id)
    {
        try {
            $policy = Policy::find($id);

            if (!$policy) {
                return $this->response(false, 'Policy not found', null, 404);
            }

            if ($policy->attachment) {
                Storage::disk('public')->delete($policy->attachment);
            }

            $policy->delete();

            return $this->response(true, 'Policy deleted successfully', null, 200);
        } catch (\Exception $e) {
            return $this->response(false, 'Something went wrong', $e->getMessage(), 500);
        }
    }

    // Unified response
    public function response($success, $message, $data, $code)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ], $code);
    }
}
