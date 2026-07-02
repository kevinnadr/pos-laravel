<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AkunController extends Controller
{
    public function index()
    {
        $akuns = User::orderBy('created_at', 'desc')->get();
        return view('pos.akun', compact('akuns'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,kasir',
            'status' => 'required|in:aktif,nonaktif'
        ]);

        User::create([
            'name' => $request->nama,
            'username' => $request->username,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => $request->role,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Akun berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $akun = User::findOrFail($id);

        $rules = [
            'nama' => 'required',
            'username' => 'required|unique:users,username,'.$id,
            'email' => 'required|email|unique:users,email,'.$id,
            'role' => 'required|in:admin,kasir',
            'status' => 'required|in:aktif,nonaktif'
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'min:6';
        }

        $request->validate($rules);

        $akun->name = $request->nama;
        $akun->username = $request->username;
        $akun->email = $request->email;
        $akun->role = $request->role;
        $akun->status = $request->status;

        if ($request->filled('password')) {
            $akun->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $akun->save();

        return redirect()->back()->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $akun = User::findOrFail($id);
        
        // Prevent deleting yourself
        if (auth()->id() == $akun->id) {
            return redirect()->back()->withErrors(['error' => 'Tidak bisa menghapus akun yang sedang login.']);
        }

        $akun->delete();
        return redirect()->back()->with('success', 'Akun berhasil dihapus.');
    }
}
