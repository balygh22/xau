<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'FullName'  => 'required|string|max:100',
            'UserName'  => 'required|string|max:60|unique:users,UserName',
            'Password'  => 'required|string|min:6',
            'GroupID'   => 'nullable|integer|exists:usergroups,GroupID',
            'IsActive'  => 'nullable|boolean',
        ]);

        DB::table('users')->insert([
            'FullName'     => $data['FullName'],
            'UserName'     => $data['UserName'],
            'PasswordHash' => Hash::make($data['Password']),
            'GroupID'      => $data['GroupID'] ?? null,
            'IsActive'     => (int)($data['IsActive'] ?? 1),
        ]);

        return redirect()->route('settings.users')->with('status', 'تم إضافة المستخدم بنجاح');
    }
}