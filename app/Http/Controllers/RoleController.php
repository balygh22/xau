<?php

namespace App\Http\Controllers;

use App\Models\UserGroup;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // GET /settings/roles
    public function index()
    {
        $roles = UserGroup::orderBy('GroupID')->get();
        return view('settings.roles.index', compact('roles'));
    }

    // GET /settings/roles/create
    public function create()
    {
        return view('settings.roles.create');
    }

    // POST /settings/roles
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:50','unique:usergroups,GroupName'],
        ], [], [
            'name' => 'اسم المجموعة',
        ]);

        UserGroup::create(['GroupName' => $data['name']]);
        return redirect()->route('settings.roles.index')->with('success','تم إنشاء المجموعة بنجاح');
    }

    // GET /settings/roles/{role}/edit
    public function edit(UserGroup $role)
    {
        $permissions = Permission::orderBy('PermissionName')->get();
        $assigned = $role->permissions()->pluck('permissions.PermissionID')->toArray();
        return view('settings.roles.edit', compact('role','permissions','assigned'));
    }

    // PUT /settings/roles/{role}
    public function update(Request $request, UserGroup $role)
    {
        $data = $request->validate([
            'name' => ['required','string','max:50','unique:usergroups,GroupName,' . $role->GroupID . ',GroupID'],
            'permissions' => ['nullable','array'],
            'permissions.*' => ['integer'],
        ], [], [
            'name' => 'اسم المجموعة',
            'permissions' => 'الصلاحيات',
        ]);

        // Update group name
        $role->update(['GroupName' => $data['name']]);

        // Sync permissions
        $ids = $data['permissions'] ?? [];
        $role->permissions()->sync($ids);

        return redirect()->route('settings.roles.index')->with('success','تم حفظ الصلاحيات بنجاح');
    }
}