<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function createRole(Request $request) {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $role = new Role();
            $role->name = $request->name;
            $role->save();
            return $this->response(true,'Role Created Successful',$role,201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return $this->response(false,$e->getMessage(),[],500);

        } catch (\Exception $e) {
            // Handle all other exceptions
            return $this->response(false,$e->getMessage(),[],500);
        }
    }
    public function getRoles()
    {
        try {
            $roles = Role::all();
//            print_r($roles);exit;
            return $this->response(true, 'Roles Fetched Successfully', $roles, 200);
        } catch (\Exception $e) {
            return $this->response(false, $e->getMessage(), [], 500);
        }
    }

    public function getRoleById($id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                return $this->response(false, 'Role Not Found', [], 404);
            }

            return $this->response(true, 'Role Fetched Successfully', $role, 200);
        } catch (\Exception $e) {
            return $this->response(false, $e->getMessage(), [], 500);
        }
    }

    public function updateRole(Request $request, $id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                return $this->response(false, 'Role Not Found', [], 404);
            }

            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $role->name = $request->name;
            $role->save();

            return $this->response(true, 'Role Updated Successfully', $role, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->response(false, $e->getMessage(), [], 422);
        } catch (\Exception $e) {
            return $this->response(false, $e->getMessage(), [], 500);
        }
    }

    public function deleteRole($id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                return $this->response(false, 'Role Not Found', [], 404);
            }

            $role->delete();
            return $this->response(true, 'Role Deleted Successfully', null, 200);
        } catch (\Exception $e) {
            return $this->response(false, $e->getMessage(), [], 500);
        }
    }


    /**
     * Helper function for consistent API responses.
     */
    public function response($success, $message, $data, $status)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

}



