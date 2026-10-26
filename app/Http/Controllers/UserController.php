<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Classroom;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function logout() {
        Auth::guard('web')->logout();
        return redirect('/');
    }

    public function Profile() {
        $id = Auth::user()->id;
        $profileData = User::find($id);

        return view('users.profile', compact('profileData'));
    }

    public function profileStore(Request $request) {
        $id = Auth::user()->id;
        $data = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,'.$id,
            'email' => 'required|email|unique:users,email,'.$id,
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $data->name = $request->name;
        $data->username = $request->username;
        $data->email = $request->email;

        if ($request->file('photo')) {
            $file = $request->file('photo');
            if ($data->photo && file_exists(public_path('upload/admin_images/'.$data->photo))) {
                @unlink(public_path('upload/admin_images/'.$data->photo));
            }
            $filename = date('YmdHi').'_'.$file->getClientOriginalName();
            $file->move(public_path('upload/admin_images'), $filename);
            $data->photo = $filename;
        }

        $data->save();

        return redirect()->back()->with([
            'swal_icon' => 'success',
            'swal_title' => 'Berhasil',
            'swal_text' => 'Profil berhasil diperbarui!'
        ]);
    }

    public function password() {
        $id = Auth::user()->id;
        $profileData = User::find($id);
        return view('users.password', compact('profileData'));
    }

    public function updatePassword(Request $request) {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|confirmed|min:6',
        ]);

        if (!Hash::check($request->old_password, Auth::user()->password)) {
            return back()->with([
                'swal_icon' => 'error',
                'swal_title' => 'Gagal',
                'swal_text' => 'Password lama tidak cocok!'
            ]);
        }

        User::whereId(Auth::user()->id)->update([
            'password' => Hash::make($request->new_password)
        ]);

        return redirect('/login')->with([
            'swal_icon' => 'success',
            'swal_title' => 'Berhasil',
            'swal_text' => 'Password berhasil diubah. Silakan login kembali.'
        ]);
    }

    /**
     * Halaman Utama Data Siswa & User dengan Filter accessibleBy
     */
    // public function allUser(Request $request) 
    // {
    //     $user = Auth::user();

    //     // 1. Ambil daftar kelas yang diizinkan untuk dropdown filter
    //     $classrooms = Classroom::accessibleBy($user)->orderBy('name')->get();

    //     // 2. Query data siswa dengan scope accessibleBy
    //     $query = Student::accessibleBy($user)->with(['classroom', 'user']);

    //     // Filter Kelas
    //     if ($request->filled('classroom_id')) {
    //         $query->where('classroom_id', $request->classroom_id);
    //     }

    //     // Filter Pencarian Nama / NIS
    //     if ($request->filled('search')) {
    //         $query->where(function($q) use ($request) {
    //             $q->where('name', 'like', '%' . $request->search . '%')
    //               ->orWhere('nis', 'like', '%' . $request->search . '%');
    //         });
    //     }

    //     $perPage = $request->input('per_page', 10);
    //     $students = $query->orderBy('name')->paginate($perPage)->withQueryString();

    //     return view('users.all', compact('students', 'classrooms'));
    // }

//     public function allUser(Request $request) 
// {
//     $query = User::with(['student.classroom', 'teacher']);

//     // Filter Berdasarkan Role / Hak Akses
//     if ($request->filled('role')) {
//         $query->where(function($q) use ($request) {
//             $q->where('jenis_user', $request->role)
//               ->orWhereHas('roles', function($r) use ($request) {
//                   $r->where('name', $request->role);
//               });
//         });
//     }

//     // Filter Pencarian Fleksibel (Case-Insensitive ILIKE untuk PostgreSQL)
//     if ($request->filled('search')) {
//         $search = trim($request->search);
//         $query->where(function($q) use ($search) {
//             $q->where('name', 'ILIKE', "%{$search}%")
//               ->orWhere('username', 'ILIKE', "%{$search}%")
//               ->orWhere('email', 'ILIKE', "%{$search}%")
//               ->orWhereHas('student', function($s) use ($search) {
//                   $s->where('nis', 'ILIKE', "%{$search}%");
//               })
//               ->orWhereHas('teacher', function($t) use ($search) {
//                   $t->where('nip', 'ILIKE', "%{$search}%");
//               });
//         });
//     }

//     $perPage = $request->input('per_page', 10);
//     $users = $query->orderBy('name', 'asc')->paginate($perPage)->withQueryString();

//     // Jika request dipanggil via AJAX, kembalikan hanya parsial view tabel
//     if ($request->ajax()) {
//         return view('users.partials.user_table', compact('users'))->render();
//     }

//     return view('users.all', compact('users'));
// }

// public function allUser(Request $request) 
// {
//     $query = User::with(['student.classroom', 'teacher']);

//     // Filter Berdasarkan Role / Hak Akses
//     if ($request->filled('role')) {
//         $query->where(function($q) use ($request) {
//             $q->where('jenis_user', $request->role)
//               ->orWhereHas('roles', function($r) use ($request) {
//                   $r->where('name', $request->role);
//               });
//         });
//     }

//     // Filter Berdasarkan Status Online (Opsional jika ingin difilter)
//     if ($request->filled('online_status')) {
//         if ($request->online_status == 'online') {
//             $query->where('last_seen', '>=', now()->subMinutes(5));
//         } elseif ($request->online_status == 'offline') {
//             $query->where(function($q) {
//                 $q->whereNull('last_seen')
//                   ->orWhere('last_seen', '<', now()->subMinutes(5));
//             });
//         }
//     }

//     // Filter Pencarian Fleksibel
//     if ($request->filled('search')) {
//         $search = trim($request->search);
//         $query->where(function($q) use ($search) {
//             $q->where('name', 'ILIKE', "%{$search}%")
//               ->orWhere('username', 'ILIKE', "%{$search}%")
//               ->orWhere('email', 'ILIKE', "%{$search}%")
//               ->orWhereHas('student', function($s) use ($search) {
//                   $s->where('nis', 'ILIKE', "%{$search}%");
//               })
//               ->orWhereHas('teacher', function($t) use ($search) {
//                   $t->where('nip', 'ILIKE', "%{$search}%");
//               });
//         });
//     }

//     $perPage = $request->input('per_page', 10);
//     $users = $query->orderBy('name', 'asc')->paginate($perPage)->withQueryString();

//     if ($request->ajax()) {
//         return view('users.partials.user_table', compact('users'))->render();
//     }

//     return view('users.all', compact('users'));
// }

public function allUser(Request $request) 
{
    $query = User::with(['student.classroom', 'teacher']);

    // Filter Berdasarkan Role / Hak Akses
    if ($request->filled('role')) {
        $query->where(function($q) use ($request) {
            $q->where('jenis_user', $request->role)
              ->orWhereHas('roles', function($r) use ($request) {
                  $r->where('name', $request->role);
              });
        });
    }

    // Filter Pencarian Fleksibel (Case-Insensitive ILIKE)
    if ($request->filled('search')) {
        $search = trim($request->search);
        $query->where(function($q) use ($search) {
            $q->where('name', 'ILIKE', "%{$search}%")
              ->orWhere('username', 'ILIKE', "%{$search}%")
              ->orWhere('email', 'ILIKE', "%{$search}%")
              ->orWhereHas('student', function($s) use ($search) {
                  $s->where('nis', 'ILIKE', "%{$search}%");
              })
              ->orWhereHas('teacher', function($t) use ($search) {
                  $t->where('nip', 'ILIKE', "%{$search}%");
              });
        });
    }

    $perPage = $request->input('per_page', 10);

    // =========================================================
    // URUTKAN: User Online Paling Atas, Lalu Berdasarkan Nama
    // =========================================================
    $users = $query->orderByRaw("
        CASE 
            WHEN last_seen >= NOW() - INTERVAL '5 minutes' THEN 1 
            ELSE 2 
        END ASC
    ")
    ->orderBy('name', 'asc')
    ->paginate($perPage)
    ->withQueryString();

    if ($request->ajax()) {
        return view('users.partials.user_table', compact('users'))->render();
    }

    return view('users.all', compact('users'));
}

    /**
     * Update Status Aktif/Nonaktif User via AJAX SweetAlert2
     */
    public function UpdateStatusUser(Request $request) {
        $currentUserId = Auth::id();
        $targetUserId  = $request->input('user');

        // Proteksi: Tidak boleh menonaktifkan diri sendiri
        if ($currentUserId == $targetUserId) {
            return response()->json([
                'status' => 'error',
                'title'  => 'Aksi Ditolak',
                'message' => 'Anda tidak dapat mengubah status akun Anda sendiri!'
            ], 403);
        }

        try {
            $user = User::findOrFail($targetUserId);
            $isChecked = $request->input('is_checked', 0);
            $user->status = $isChecked;
            $user->save();

            $statusText = $isChecked ? 'diaktifkan' : 'dinonaktifkan';
            $icon = $isChecked ? 'success' : 'warning';

            return response()->json([
                'status'  => 'success',
                'icon'    => $icon,
                'title'   => 'Status Diperbarui',
                'message' => "Pengguna <b>{$user->name}</b> berhasil {$statusText}."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'title'  => 'Gagal',
                'message' => 'Terjadi kesalahan sistem saat mengupdate status.'
            ], 500);
        }
    }

    public function addUser() {
        return view('users.add_manual');
    }

    public function storeUser(Request $request) {
        $request->validate([
            'name'        => 'required|string|max:255',
            'role'        => 'required|in:admin,guru,piket,siswa',
            'nomor_induk' => 'required|unique:users,nomor_induk',
            'email'       => 'nullable|email|unique:users,email',
            'password'    => 'nullable|min:6',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $email = $request->email ?? ($request->nomor_induk . '@sekolah.sch.id');
                $password = $request->password ? Hash::make($request->password) : Hash::make($request->nomor_induk);

                $user = User::create([
                    'name'        => $request->name,
                    'username'    => $request->username ?? $request->nomor_induk,
                    'email'       => $email,
                    'password'    => $password,
                    'jenis_user'  => $request->role,
                    'nomor_induk' => $request->nomor_induk,
                ]);

                $user->assignRole($request->role);

                if ($request->role === 'siswa') {
                    Student::create([
                        'user_id' => $user->id,
                        'nis'     => $request->nomor_induk,
                        'name'    => $request->name,
                    ]);
                } elseif (in_array($request->role, ['guru', 'piket'])) {
                    Teacher::create([
                        'user_id' => $user->id,
                        'nip'     => $request->nomor_induk,
                        'name'    => $request->name,
                    ]);
                }
            });

            return redirect()->route('user.all')->with([
                'swal_icon' => 'success',
                'swal_title' => 'Berhasil',
                'swal_text' => 'User baru berhasil ditambahkan!'
            ]);

        } catch (\Exception $e) {
            return back()->withInput()->with([
                'swal_icon' => 'error',
                'swal_title' => 'Gagal Menyimpan',
                'swal_text' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
 * Reset / Generate Password Baru untuk User
 */
public function generatePassword($id)
{
    try {
        $user = User::findOrFail($id);
        
        // Buat password baru (default: nomor_induk atau string acak 8 karakter)
        $newPassword = $user->nomor_induk ?? strtolower(Str::random(8));
        
        $user->update([
            'password' => Hash::make($newPassword)
        ]);

        return response()->json([
            'status' => 'success',
            'title'  => 'Password Berhasil Diperbarui!',
            'message' => "Password baru untuk <b>{$user->name}</b> adalah: <br><span class='badge bg-primary fs-6 mt-2'>{$newPassword}</span>",
            'new_password' => $newPassword
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'title'  => 'Gagal',
            'message' => 'Gagal meng-generate password.'
        ], 500);
    }
}

/**
 * Cetak Kartu / Informasi Akun User
 */
public function printAccount($id)
{
    $user = User::with(['student.classroom', 'teacher'])->findOrFail($id);
    
    return view('users.print_account', compact('user'));
}
}