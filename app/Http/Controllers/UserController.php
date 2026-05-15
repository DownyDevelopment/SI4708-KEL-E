<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('admin.roles', compact('users'));
    }

    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:admin,pengawas,supervisor,relawan',
        ]);

        $user = User::findOrFail($id);
        $user->role = $request->role;
        $user->save();

        return redirect()->back()->with('success', 'Role berhasil diperbarui!');
    }
}
