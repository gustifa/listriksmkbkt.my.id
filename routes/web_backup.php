<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentImportController;
use App\Http\Controllers\UserImportController;
use App\Http\Controllers\TeacherImportController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\FaceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\DailyAttendanceController;
use App\Http\Controllers\ManualAttendanceController;
use App\Http\Controllers\StudentAreaController;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Student;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profil User (Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// =========================================================================
// GROUP 1: OPERASIONAL HARIAN (Guru, Admin, Satpam)
// =========================================================================
Route::middleware(['auth', 'role:guru|admin|satpam'])->group(function () {

    // --- ABSENSI HARIAN (GERBANG) ---
    // Scan Masuk & Pulang
    Route::get('/daily-attendance', [DailyAttendanceController::class, 'index'])->name('daily.index');
    Route::post('/daily-attendance', [DailyAttendanceController::class, 'store'])->name('daily.store');

    // Input Manual Harian (Jika Lupa Kartu)
    Route::get('/daily-attendance/manual', [DailyAttendanceController::class, 'create'])->name('daily.create');
    Route::post('/daily-attendance/manual', [DailyAttendanceController::class, 'storeManual'])->name('daily.storeManual');
});

// =========================================================================
// GROUP 2: KEGIATAN BELAJAR MENGAJAR (Guru & Admin)
// =========================================================================
Route::middleware(['auth', 'role:guru|admin'])->group(function () {

    // --- 1. MANAJEMEN JADWAL MENGAJAR ---
    Route::get('/my-schedule', [ScheduleController::class, 'index'])->name('schedule.index');
    Route::get('/my-schedule/create', [ScheduleController::class, 'create'])->name('schedule.create');
    Route::post('/my-schedule', [ScheduleController::class, 'store'])->name('schedule.store');
    Route::get('/my-schedule/{id}/attendances', [ScheduleController::class, 'show'])->name('schedule.show');
    Route::delete('/my-schedule/{id}', [ScheduleController::class, 'destroy'])->name('schedule.destroy');

    // --- 2. SCANNER ABSENSI KELAS (QR CODE) ---
    Route::get('/scan/{schedule_id}', [AttendanceController::class, 'index'])->name('scan.index');
    Route::post('/scan/store', [AttendanceController::class, 'store'])->name('scan.store');

    // --- 3. ABSENSI MANUAL KELAS (Izin/Sakit) ---
    // Route::get('/manual-attendance', [ManualAttendanceController::class, 'index'])->name('manual.index');
    // Route::get('/manual-attendance/{id}/create', [ManualAttendanceController::class, 'create'])->name('manual.create');
    // Route::post('/manual-attendance/{id}', [ManualAttendanceController::class, 'store'])->name('manual.store');

    // --- 4. SCANNER WAJAH (FACE ID) ---
    Route::get('/scan-face/{schedule_id}', [FaceController::class, 'scan'])->name('scan.face');
    Route::get('/face/descriptors/{schedule_id}', [FaceController::class, 'getDescriptors']);

    // --- 5. LAPORAN & PDF ---
    Route::get('/report', [ReportController::class, 'index'])->name('report.index');
    Route::post('/report/print', [ReportController::class, 'print'])->name('report.print');
    Route::get('/report/schedule/{id}', [ReportController::class, 'printSchedule'])->name('report.schedule'); // Laporan Mapel
    Route::get('/report/student/{id}', [ReportController::class, 'printStudent'])->name('report.student'); // Transkrip Siswa

    // --- 6. CETAK KARTU SISWA (GURU) ---
    // Dipindah kesini agar Guru bisa mencetak kartu siswa di kelasnya
    Route::get('/print-cards', [CardController::class, 'index'])->name('print.index');
    Route::get('/print-cards/class/{id}', [CardController::class, 'printByClass'])->name('print.class');
    Route::get('/print-cards/select/{id}', [CardController::class, 'selectStudents'])->name('print.select');
    Route::post('/print-cards/print-selected', [CardController::class, 'printSelected'])->name('print.selected');
    Route::get('/print-cards/all', [CardController::class, 'printAll'])->name('print.all');
    Route::get('/print-card/{id}', [CardController::class, 'printSingle'])->name('print.single');

});

// =========================================================================
// GROUP 3: KHUSUS ADMIN (Master Data & Settings)
// =========================================================================
Route::middleware(['auth', 'role:admin'])->group(function () {

    // --- 1. MASTER DATA (CRUD) ---
    Route::resource('subjects', SubjectController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
    Route::resource('classrooms', ClassroomController::class);

    // Manajemen Siswa
    Route::get('/students/export', [StudentController::class, 'export'])->name('students.export');
    Route::resource('students', StudentController::class)->except(['show', 'create', 'store']);
    Route::patch('/students/{id}/remove-class', [StudentController::class, 'removeClassroom'])->name('students.remove_class');

    // Manajemen Guru
    Route::get('/teachers/export', [TeacherController::class, 'export'])->name('teachers.export');
    Route::resource('teachers', TeacherController::class)->except(['create', 'store']); // Show, Edit, Update, Destroy Aktif

    // Manajemen Kelas Export
    Route::get('/classrooms/export', [ClassroomController::class, 'export'])->name('classrooms.export');

    // --- 2. PENGATURAN SEKOLAH (SETTINGS) ---
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

    // --- 3. IMPORT DATA (EXCEL) ---
    // Import Siswa
    Route::get('/import-students', [StudentImportController::class, 'index'])->name('students.import');
    Route::post('/import-students', [StudentImportController::class, 'store'])->name('students.import.store');

    // Import User (Akun)
    Route::get('/import-users', [UserImportController::class, 'index'])->name('users.import');
    Route::post('/import-users', [UserImportController::class, 'store'])->name('users.import.store');

    // Import Guru (Data Lengkap)
    Route::get('/import-teachers', [TeacherImportController::class, 'index'])->name('teachers.import');
    Route::post('/import-teachers', [TeacherImportController::class, 'store'])->name('teachers.import.store');

    // --- 4. WHATSAPP GATEWAY (MANUAL BROADCAST) ---
    Route::get('/whatsapp/test', [WhatsAppController::class, 'index'])->name('whatsapp.index');
    Route::post('/whatsapp/send', [WhatsAppController::class, 'store'])->name('whatsapp.store');

    Route::get('/whatsapp/broadcast', [WhatsAppController::class, 'broadcast'])->name('whatsapp.broadcast'); // Broadcast Kelas
    Route::post('/whatsapp/broadcast', [WhatsAppController::class, 'sendBroadcast'])->name('whatsapp.broadcast.send');

    // --- 5. REGISTRASI WAJAH SISWA ---
    Route::get('/face/register', [FaceController::class, 'index'])->name('face.index');
    Route::get('/face/register/{id}', [FaceController::class, 'register'])->name('face.register');
    Route::post('/face/register/{id}', [FaceController::class, 'store'])->name('face.store');

});

// =========================================================================
// GROUP 4: AREA KHUSUS SISWA (Self Service)
// =========================================================================
Route::middleware(['auth', 'role:siswa'])->group(function () {

    // Profil Siswa (Edit No HP & Alamat)
    Route::get('/my-profile', [StudentAreaController::class, 'profile'])->name('student.profile');
    Route::put('/my-profile', [StudentAreaController::class, 'updateProfile'])->name('student.profile.update');

    // Cetak Kartu Sendiri
    Route::get('/my-card', [StudentAreaController::class, 'printCard'])->name('student.print.card');

    // Riwayat Absensi
    Route::get('/my-history/subject', [StudentAreaController::class, 'historySubject'])->name('student.history.subject');
    Route::get('/my-history/daily', [StudentAreaController::class, 'historyDaily'])->name('student.history.daily');

});

require __DIR__.'/auth.php';
