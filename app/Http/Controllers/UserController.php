<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Rules\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function showMe(Request $request)
    {
        return new UserResource($request->user());
    }

    public function index(Request $request)
    {
        if ($request->user()->cannot('viewAny', User::class)) {
            abort(403);
        }
        return User::all();
    }

    public function view(Request $request, $id)
    {
        $user = User::findOrFail($id);
        if ($request->user()->cannot('view', $user)) {
            abort(403);
        }
        return $user;
    }

    public function store(Request $request)
    {
        if ($request->user()->cannot('create', User::class)) {
            abort(403);
        }
        $validation_rules = [
            'name' => 'required|string|max:191',
            'email' => 'required|unique:users|email|max:191',
            'password' => 'required|string|max:191',
            'role_id' => [
                'bail',
                'required',
                'numeric',
                'min:1',
                'exists:roles,id',
                new UserRole($request->user()->role)
            ]
        ];
        $data = $request->validate($validation_rules);
        $new_user = User::make($data);
        $new_user->role_id = $data['role_id'];
        $new_user->password = Hash::make($data['password']);
        $new_user->save();
        return new UserResource($new_user);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        if ($request->user()->cannot('update', $user)) {
            abort(403);
        }
        $validation_rules = [
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email,' . $user->id,
            'password' => 'required|string|max:191',
            'role_id' => [
                'bail',
                'required',
                'numeric',
                'min:1',
                'exists:roles,id',
                new UserRole($request->user()->role)
            ]
        ];
        $att_to_update = $request->request->keys();

        // Start of the differential validating
        $differential_validation = [];
        foreach ($att_to_update as $att) {
            // Prevent validating null values and adding non existent validation rules
            if (!empty($request->$att) && array_key_exists($att, $validation_rules)) {
                $differential_validation[$att] = $validation_rules[$att];
            }
        }
        $data = $request->validate($differential_validation);
        
        // Start of the differential updating
        $att_to_update = array_keys($differential_validation);
        foreach ($att_to_update as $att) {
            $user->$att = ($att == 'password') ? Hash::make($data[$att]) : $data[$att];
        }
        $user->save();
    }

    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);
        if ($request->user()->cannot('delete', $user)) {
            abort(403);
        }
        // Check if the user is the only admin left. In the database must be at least one ADMIN user.
        $admin_role_id = Role::where('hierarchy', '=', 0)->firstOrFail()->id;
        if ($user->role->hierarchy === 0 && User::where('role_id', '=', $admin_role_id)->count() < 2) {
            abort(406, trans('validation.custom.user_destroy.sole_admin'));
        }
        $user->delete();
    }
}
