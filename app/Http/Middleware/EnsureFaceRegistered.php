<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureFaceRegistered
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Pengecekan hanya untuk user dengan role/akses siswa
        if ($user && $user->student) { // Sesuaikan jika menggunakan Spatie Permission, e.g., $user->hasRole('student')
            
            // Hitung jumlah sampel wajah siswa di database
            $faceCount = $user->student->faceDescriptors()->count();

            // Jika belum punya data wajah dan TIDAK sedang mengakses halaman registrasi wajah atau logout
            if ($faceCount === 0 && !$request->routeIs('student.face.register', 'student.face.store', 'logout')) {
                return redirect()->route('student.face.register')
                    ->with('warning', 'Anda wajib mendaftarkan data wajah terlebih dahulu sebelum melanjutkan!');
            }
        }

        return $next($request);
    }
}