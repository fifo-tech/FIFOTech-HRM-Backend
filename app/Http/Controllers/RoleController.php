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
}
