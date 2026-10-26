<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // Import DB Facade untuk Transaction
use Spatie\Permission\Models\Role;
use App\DataTables\UserDataTable;

class UserController extends Controller
{
    public function logout(){
        Auth::guard('web')->logout();
        return redirect('/');
    }

    public function Profile(){
        $id = Auth::user()->id;
        $profileData = User::find($id);

        return view('users.profile', compact('profileData'));
    }

    public function profileStore(Request $request){
        $id = Auth::user()->id;
        $data = User::find($id);
        $data->name = $request->name;
        $data->username = $request->username;
        $data->email = $request->email;
        // $data->phone = $request->phone;
        // $data->address = $request->address;

        if ($request->file('photo')) {
            $file = $request->file('photo');
            @unlink(public_path('upload/admin_images/'.$data->photo));
            $filename = date('YmdHi').$file->getClientOriginalName();
            $file->move(public_path('upload/admin_images'),$filename);
            $data['photo'] = $filename;
        }

        $notification = array(
            'message' => 'Admin Profile Update Succesfully',
            'alert-type' => 'success',
        );

        $data->save();

        return redirect()->back()->with($notification);
    }

    public function password(){
        $id = Auth::user()->id;
        $profileData = User::find($id);
        return view('users.password', compact('profileData'));
    }

    public function updatePassword(Request $request){
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|confirmed',
        ]);

        if (!Hash::check($request->old_password, auth::user()->password)) {

            $notification = array(
                'message' => 'Old Password Not Match',
                'alert-type' => 'error',
            );
            return back()->with($notification);
        }
        User::whereId(auth::user()->id)->update([
            'password' => Hash::make($request->new_password)

        ]);

        $notification = array(
            'message' => 'Change Password Update Succesfully',
            'alert-type' => 'success',
        );
        return redirect('/login')->with($notification);
    }


    //  public function allUser(){
    //     $allUser = User::latest()->get();
    //     return view('users.all', compact('allUser'));
    // }/* End Method */

    public function allUser(UserDataTable $dataTable)
    {
        return $dataTable->render('users.all');
    }

    public function UpdateStatusUser(Request $request){
        $id = Auth::user()->id;
        $name = Auth::user()->name;
        $userId = $request->input('user');
        if($id != $userId ){
            $isChecked = $request->input('is_checked', 0);
            if($isChecked){
                $user = User::find($userId);
                if ($user) {
                    $user->status =  $isChecked;
                    $user->save();
                }
                return response()->json(['message'=>'Pengguna <b class="text-dark">'.$user->name.' </b>Berhasil diaktifkan']);
                // $notification = array(
                //     'message' => 'Kelas Berhasil ditambahkan',
                //     'alert-type' => 'success',
                // );
                // return redirect()->back()->with($notification);
            }else{
                $user = User::find($userId);
                if ($user) {
                    $user->status =  $isChecked;
                    $user->save();
                }
                return response()->json(['message'=>'Pengguna <b class="text-danger">'.$user->name.' </b>Berhasil dinonaktifkan']);
                // $notification = array(
                //     'message' => 'Kelas Berhasil ditambahkan',
                //     'alert-type' => 'success',
                // );
                // return redirect()->back()->with($notification);
            }
        }else{
            return response()->json(['message'=>'Anda Login sebagai <b class="text-danger">'.$name.' </b>Gagal Bro']);
        }
    }

    public function addUser(){
        return view('users.add_manual');
    }

    // Menyimpan Data User Baru
    public function storeUser(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:admin,guru,piket,siswa',
            'nomor_induk' => 'required|unique:users,nomor_induk', // Pastikan NIP/NISN unik
            // Email opsional, jika kosong kita buat dummy email
            'email' => 'nullable|email|unique:users,email',
            'password' => 'nullable|min:6',
        ]);

        // Gunakan Database Transaction agar data konsisten (Rollback jika salah satu gagal)
        DB::transaction(function () use ($request) {

            // 2. Tentukan Email Otomatis jika kosong (misal: nisn@sekolah.com)
            $email = $request->email;
            if (empty($email)) {
                $email = $request->nis . '@sekolah.sch.id';
            }

            // 3. Default Password jika kosong (misal: 123456 atau sama dengan nomor induk)
            $password = $request->password ? Hash::make($request->password) : Hash::make($request->nomor_induk);

            // 4. Simpan ke Database Utama (Users - untuk Login)
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $email,
                'password' => $password,
                'jenis_user' => $request->role,
                // 'nomor_induk' => $request->nomor_induk,
            ]);

            // 4. Assign Role Spatie
            // Pastikan Role sudah ada di database (jalankan RoleSeeder sebelumnya)
            $user->assignRole($request->role);

            // 5. Simpan ke Tabel Spesifik (Siswa / Guru) berdasarkan Role
            if ($request->role === 'siswa') {
                // Simpan ke tabel students
                Student::create([
                    'user_id' => $user->id,       // Relasi ke tabel users
                    'nis'    => $request->nomor_induk,
                    'name'    => $request->name,  // Redundan tapi sering berguna untuk query cepat
                    // 'kelas'   => 'X-1',           // Default kelas (bisa ditambahkan field di form create)
                    // 'nomor_induk' => $request->nomor_induk,
                    //'status'  => '0'
                ]);
            } elseif (in_array($request->role, ['guru', 'piket'])) {
                // Simpan ke tabel teachers
                Teacher::create([
                    'user_id' => $user->id,       // Relasi ke tabel users
                    'nip'     => $request->nomor_induk,
                    'name'    => $request->name,
                    // 'role_type'=> $request->role  // Membedakan Guru Mapel atau Piket di tabel teacher
                ]);
            }
            // Admin tidak perlu masuk tabel spesifik, cukup di users saja.
        });

        return redirect('/user/all')->with('success', 'User berhasil ditambahkan dan disinkronkan ke data induk!');
    }

}
