<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Student; // Pastikan model Student diimport

class AuthController extends Controller
{
    /**
     * Handle incoming login request.
     */
    public function login(Request $request)
    {
        // 1. Validasi Input
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Cek Kredensial
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // 3. Cek Data Tambahan (Jika Siswa, ambil student_id untuk portal)
            $studentId = null;
            
            // Asumsi kolom role ada di tabel users (atau jenis_user)
            // Sesuaikan dengan nama kolom di database Anda (role / jenis_user)
            $role = $user->role ?? $user->jenis_user ?? 'siswa'; 

            if ($role === 'siswa') {
                // Cari data siswa yang terhubung dengan user ini
                $student = Student::where('user_id', $user->id)->first();
                $studentId = $student ? $student->id : null;
            }

            // 4. Return Response JSON untuk React
            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $role,
                    'student_id' => $studentId // Penting untuk Portal Siswa di React
                ]
            ], 200);
        }

        // Jika Gagal
        return response()->json([
            'success' => false,
            'message' => 'Email atau password salah.'
        ], 401);
    }

    /**
     * Handle logout request.
     */
    public function logout()
    {
        Auth::logout();
        return response()->json(['message' => 'Berhasil logout']);
    }
}