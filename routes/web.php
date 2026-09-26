<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Http\Controllers\StudentImportController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DailyReportController;
use App\Models\Student;

use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;

use App\Http\Controllers\UserImportController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\FaceController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ProfileSignatureController;

use App\Http\Controllers\DailyAttendanceController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DocumentController;

use App\Http\Controllers\UpdateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ScheduleImportController;
use App\Http\Controllers\TeacherImportController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\MajorController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\StudentAreaController;
use App\Http\Controllers\TranscriptController;
use App\Http\Controllers\TeachingAssignmentController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\ScannerDeviceController;
use App\Http\Controllers\TeachingJournalController;
use App\Http\Controllers\RoomController;

use App\Http\Controllers\StudentPermissionController;
use App\Http\Controllers\RecapController;
use App\Http\Controllers\TeacherDashboardController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\PiketDashboardController;
use App\Http\Controllers\SiswaDashboardController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\Pm2Controller;
use App\Http\Controllers\WhatsappGatewayController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryLoanController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\PrayerMonitoringController;
use App\Http\Controllers\AbsensiKegiatanController;
use App\Http\Controllers\AdminPermissionController;
use App\Http\Controllers\Guru\LaporanGuruController;
use App\Http\Controllers\Guru\InternshipAssessmentController;
use App\Http\Controllers\Guru\DashboardGuruController;
use App\Http\Controllers\Guru\IndustryGuruController;
use App\Http\Controllers\Guru\MonitoringController;
use App\Http\Controllers\Admin\PrayerSettingController;
use App\Http\Controllers\Admin\MbgController;
use App\Http\Controllers\Admin\StudentPermitController;
use App\Http\Controllers\Admin\IndustryController;
use App\Http\Controllers\Admin\InternshipController;
use App\Http\Controllers\Admin\InternshipTimelineController;
use App\Http\Controllers\Admin\PKLMappingController;
use App\Http\Controllers\Admin\GuidanceController;
use App\Http\Controllers\Admin\ViolationTypeController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\InventoryAdminController;
use App\Http\Controllers\Admin\CapacityReportController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Student\InternshipStudentController;
use App\Http\Controllers\Student\InternshipAttendanceController;
use App\Http\Controllers\Student\DashboardStudentController;
use App\Http\Controllers\Student\PrayerStudentController;
use App\Http\Controllers\Student\RamadanJournalStudentController;
use App\Http\Controllers\Student\GuidanceStudentController;
use App\Http\Controllers\Student\TahfizController;
use App\Services\GithubVersionChecker; // Service Pengecekan Versi

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


// Route untuk Halaman Blokir jika aplikasi kedaluwarsa (Fitur GitHub Check)
Route::get('/update-required', function () {
    $checker = new GithubVersionChecker();
    if (!$checker->isOutdated()) {
        return redirect('/');
    }
    return view('errors.update_required');
})->name('app.update_required');

require __DIR__.'/auth.php';

Route::get('/logout', [UserController::class, 'Logout'])->name('user.logout');
Route::get('/profile', [UserController::class, 'Profile'])->name('user.profile');
Route::post('/profile/store', [UserController::class, 'profileStore'])->name('profile.store');
Route::get('/change/password', [UserController::class, 'password'])->name('user.password');
Route::post('/update/password', [UserController::class, 'updatePassword'])->name('update.password');

// =========================================================================
// GROUP : MONITORING REALTIME (Semua User Login)
// =========================================================================
Route::middleware(['auth'])->group(function () {
    // Halaman Monitor Dashboard (AJAX Polling)
    //Route::get('/daily-attendance/monitor', [DailyAttendanceController::class, 'monitor'])->name('daily.monitor');
    Route::get('/daily-attendance/monitor-kelas', [DailyAttendanceController::class, 'monitorKelas'])->name('daily.monitor.kelas');
    // API Data JSON untuk Monitor
    Route::get('/daily-attendance/api/latest', [DailyAttendanceController::class, 'getRealtimeData'])->name('daily.api.latest');

    // --- DASHBOARD REDIRECTOR (UPDATED) ---
    // Logika pengalihan user ke halaman yang sesuai role-nya
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    // Route untuk menu Tanda Tangan
    Route::get('/profil/tanda-tangan', [ProfileSignatureController::class, 'edit'])->name('profile.signature.edit');
    Route::post('/profil/tanda-tangan', [ProfileSignatureController::class, 'update'])->name('profile.signature.update');


    Route::get('/laporan-kegiatan', [ReportController::class, 'cetakLaporan'])->name('laporan.kegiatan');
});


Route::middleware(['auth'])->group(function () {
    Route::middleware(['role:guru'])->group(function () {
        // Halaman Scanner (Hanya bisa diakses Guru yang login)
        Route::get('/scan/{schedule_id}', [AttendanceController::class, 'index'])->name('scan.index');
        // Proses Data Scan (Ajax)
        Route::post('/scan/store', [AttendanceController::class, 'store'])->name('attendance.store_scan');
        // Scanner Wajah (API & View)
        Route::get('/face/descriptors/{schedule_id}', [FaceController::class, 'getDescriptors']);
        Route::get('/scan-face/{schedule_id}', [FaceController::class, 'scan'])->name('scan.face');
        // Route::resource('schedule', ScheduleController::class);
        Route::prefix('guru/schedule')->name('schedule.')->group(function() {
            Route::get('/', [ScheduleController::class, 'index'])->name('index');
            Route::get('/create', [ScheduleController::class, 'create'])->name('create');
            Route::post('/store', [ScheduleController::class, 'store'])->name('store');
            Route::get('/show/{id}', [ScheduleController::class, 'show'])->name('show');
            Route::get('/edit/{id}', [ScheduleController::class, 'edit'])->name('edit');
            Route::put('/update/{id}', [ScheduleController::class, 'update'])->name('update');
            Route::delete('/destroy/{id}', [ScheduleController::class, 'destroy'])->name('destroy');
        });

         // --- 3. ABSENSI MANUAL (BARU) ---
        Route::get('/schedule/manual/{id}', [AttendanceController::class, 'createManual'])->name('attendance.manual');
        Route::post('/schedule/manual/{id}', [AttendanceController::class, 'storeManual'])->name('attendance.storeManual');
        Route::get('/print-cards-siswa', [StudentAreaController::class, 'index'])->name('print.siswa.index');
        // Cetak Full 1 Kelas
        Route::get('/print-cards-siswa/class/{id}', [StudentAreaController::class, 'printByClass'])->name('print.siswa.class');

        // [BARU] Pilih Siswa Tertentu & Cetak
        Route::get('/print-cards-siswa/select/{id}', [StudentAreaController::class, 'selectStudents'])->name('print.siswa.select');
        Route::post('/print-cards-siswa/print-selected', [StudentAreaController::class, 'printSelected'])->name('print.siswa.selected');

        // Cetak Semua & Satuan
        Route::get('/print-cards-siswa/all', [StudentAreaController::class, 'printAll'])->name('print.siswa.all');
        Route::get('/print-card-siswa/{id}', [StudentAreaController::class, 'printSingle'])->name('print.siswa.single');

        // Route Khusus Dashboard Guru
        Route::get('/guru/dashboard', [DashboardGuruController::class, 'index'])->name('teacher.dashboard');

                // Fitur Set Lokasi PKL
        Route::get('/guru/industri/lokasi', [IndustryGuruController::class, 'index'])->name('teacher.industries.locations');
        Route::put('/industries/{id}/location', [IndustryGuruController::class, 'update'])->name('teacher.industries.update_location');

        // Monitoring Siswa
        Route::get('/guru/monitoring', [MonitoringController::class, 'index'])->name('teacher.monitoring.index');
        Route::get('/guru/monitoring/{classroomId}', [MonitoringController::class, 'show'])->name('teacher.monitoring.show');
        // Route Cetak Laporan Monitoring
        Route::post('/monitoring/print', [MonitoringController::class, 'printReport'])->name('teacher.monitoring.print');


    });
    // =========================================================================
    // GROUP 1: AREA GURU & ADMIN (Operasional Harian)
    // =========================================================================

    Route::middleware(['role:admin|guru|piket'])->group(function () {

        Route::get('/piket/dashboard', [PiketDashboardController::class, 'dashboard'])->name('piket.dashboard');
        Route::get('/laporan-pembelajaran', [ReportController::class, 'index'])->name('report.index');
        Route::post('/pembelajaran/print', [ReportController::class, 'print'])->name('report.print');
        // [BARU] Route Cetak Laporan Per Jadwal (Direct Link)
        Route::get('/report/schedule/{id}', [ReportController::class, 'printSchedule'])->name('report.schedule');
        Route::resource('subjects', SubjectController::class);

        // Route untuk mencetak Laporan Riwayat Siswa (Transkrip)
        // Memanggil method ReportController@printStudent
        Route::get('/report/student/{id}', [ReportController::class, 'printStudent'])->name('report.student');


        Route::get('/daily-attendance', [DailyAttendanceController::class, 'index'])->name('daily.index');
        Route::post('/daily-attendance', [DailyAttendanceController::class, 'store'])->name('daily.store');

        // 1. Route Menampilkan Form Manual (GET)
        // Sesuai dengan method create() di controller
        Route::get('/daily-attendance/manual', [DailyAttendanceController::class, 'create'])->name('daily.create');
        // 2. Route Menyimpan Data Manual (POST)
        // Sesuai dengan method storeManual() di controller
        Route::post('/daily-attendance/manual', [DailyAttendanceController::class, 'storeManual'])->name('daily.storeManual');

        //Route::get('/absensi/report', [DailyAttendanceController::class, 'report'])->name('daily_attendance.report');
        //Route::get('/absensi/laporan', [DailyAttendanceController::class, 'laporan'])->name('daily_attendance.report');
        Route::get('/report/absensi/student/{id}', [DailyReportController::class, 'printStudentAbsensi'])->name('report.absensi.student');
        Route::get('laporan-gerbang', [DailyReportController::class, 'reportDaily'])->name('daily_attendance.report');
        Route::post('/report-absensi-print', [DailyReportController::class, 'printAbsensi'])->name('report.print.absensi');


        Route::get('/api/realtime-stats', [DashboardController::class, 'getRealtimeStats'])->name('api.stats');

        // --- B. ABSENSI HARIAN (GERBANG WAJAH) [BARU] ---
    // Scan Masuk & Pulang via Wajah
        Route::get('/daily-face-scan', [FaceController::class, 'dailyScan'])->name('daily.face.scan');
        Route::get('/face/all-descriptors', [FaceController::class, 'getAllDescriptors'])->name('face.descriptors.all');

        // Route simpan absensi gerbang massal (Bantuan Guru)
        Route::post('/daily-attendance/bulk', [DailyAttendanceController::class, 'storeBulk'])->name('daily.store_bulk');

                // Routes Jurnal Pembelajaran
        Route::get('/journal', [TeachingJournalController::class, 'index'])->name('journal.index');
        Route::post('/journal/store', [TeachingJournalController::class, 'store'])->name('journal.store');
        Route::get('/journal/show/{schedule_id}', [TeachingJournalController::class, 'show'])->name('journal.show');

        // Fitur Peminjaman Guru
        Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
        Route::get('/loans/scan', [LoanController::class, 'scan'])->name('loans.scan');
        Route::post('/loans/store', [LoanController::class, 'store'])->name('loans.store');
        Route::post('/loans/return/{id}', [LoanController::class, 'returnItem'])->name('loans.return');
        Route::get('/report/attendance-list/{schedule}', [LaporanGuruController::class, 'printAttendanceList'])->name('report.attendance_list');

        // Penilaian PKL
        Route::get('/guru/internships', [InternshipAssessmentController::class, 'index'])->name('teacher.internships.index');
        Route::get('/guru/internships/{id}/assess', [InternshipAssessmentController::class, 'create'])->name('teacher.internships.assess');
        Route::post('/guru/internships/{id}/store', [InternshipAssessmentController::class, 'store'])->name('teacher.internships.store');
        Route::get('/guru/internships/{id}/certificate', [InternshipAssessmentController::class, 'printCertificate'])->name('teacher.internships.certificate');

        Route::get('/pembinaan', [GuidanceController::class, 'index'])->name('admin.guidance.index');
        Route::get('/pembinaan/buat', [GuidanceController::class, 'create'])->name('admin.guidance.create');
        Route::get('/pembinaan/{id}', [GuidanceController::class, 'show'])->name('admin.guidance.show');
        Route::put('/pembinaan/{id}', [GuidanceController::class, 'update'])->name('admin.violation.update');
        Route::delete('/pembinaan/{id}', [GuidanceController::class, 'destroy'])->name('admin.violation.destroy');

        // Simpan Pembinaan
        Route::post('/guidance/{id}/store', [GuidanceController::class, 'storeGuidance'])->name('admin.guidance.store');

        // Simpan Pelanggaran
        Route::post('/guidance/{id}/violation', [GuidanceController::class, 'storeViolation'])->name('admin.violation.store');
        Route::get('/pelanggaran', [ViolationTypeController::class, 'index'])->name('admin.violation-types.index');
        Route::get('/pelanggaran/buat', [ViolationTypeController::class, 'create'])->name('admin.violation-types.create');
        Route::get('/pelanggaran/edit/{id}', [ViolationTypeController::class, 'edit'])->name('admin.violation-types.edit');
        Route::put('/pelanggaran/update/{id}', [ViolationTypeController::class, 'update'])->name('admin.violation-types.update');
        Route::post('/pelanggaran/store', [ViolationTypeController::class, 'store'])->name('admin.violation-types.store');
        Route::delete('/pelanggaran/destroy', [ViolationTypeController::class, 'destroy'])->name('admin.violation-types.destroy');

        Route::get('/pembinaan/{guidanceId}/print-agreement', [GuidanceController::class, 'printAgreement'])->name('admin.guidance.print_agreement');

        Route::post('/pembinaan/{id}/summon', [GuidanceController::class, 'sendSummon'])->name('admin.guidance.summon');

        Route::post('/izin/store', [StudentPermissionController::class, 'store'])->name('izin.store');
        Route::post('/izin/show', [StudentPermissionController::class, 'show'])->name('izin.show');
        Route::post('/izin/check', [StudentPermissionController::class, 'check'])->name('izin.check');
        Route::post('/izin/return', [StudentPermissionController::class, 'markReturn'])->name('izin.return');
        Route::get('/izin/print/{id}', [StudentPermissionController::class, 'print'])->name('izin.print');

        // Route Tahfiz
        Route::get('/tahfiz', [TahfizController::class, 'index'])->name('tahfiz.index');
        Route::post('/tahfiz', [TahfizController::class, 'store'])->name('tahfiz.store');
        Route::delete('/tahfiz/{id}', [TahfizController::class, 'destroy'])->name('tahfiz.destroy');

        Route::get('/kegiatan', [AbsensiKegiatanController::class, 'index'])->name('kegiatan.index');
        Route::get('/kegiatan/scan/{kode_unik}', [AbsensiKegiatanController::class, 'scanQr'])->name('kegiatan.scan');
        Route::get('/kegiatan/scan/', [AbsensiKegiatanController::class, 'scan'])->name('kegiatan.scan.camera');
        Route::post('/kegiatan/scan/proses', [AbsensiKegiatanController::class, 'proses'])->name('scan.proses');
        Route::get('/kegiatan/{id}', [AbsensiKegiatanController::class, 'showKegiatan'])->name('kegiatan.show');
        Route::post('/kegiatan/store', [AbsensiKegiatanController::class, 'storeKegiatan'])->name('kegiatan.store');
        Route::delete('/kegiatan/{id}', [AbsensiKegiatanController::class, 'destroy'])->name('kegiatan.destroy');
        Route::get('/kegiatan/print/{id}', [AbsensiKegiatanController::class, 'printKegiatan'])->name('kegiatan.print');
        Route::get('/api/kegiatan/total-hadir', [AbsensiKegiatanController::class, 'apiTotalHadir']);
        Route::get('/kegiatan/{id}/total-hadir', [AbsensiKegiatanController::class, 'getTotalHadir'])->name('kegiatan.total-hadir');
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/', [DocumentController::class, 'index'])->name('index');
            Route::get('/create', [DocumentController::class, 'create'])->name('create');
            Route::post('/', [DocumentController::class, 'store'])->name('store');
            // Route untuk pratinjau, download, dan hapus
            Route::get('/{id}', [DocumentController::class, 'show'])->name('show');
            Route::get('/{id}/download', [DocumentController::class, 'download'])->name('download');
            Route::delete('/{id}', [DocumentController::class, 'destroy'])->name('destroy');
        });
    });

    // =========================================================================
    // GROUP 2: KHUSUS ADMIN (Master Data & Settings)
    // =========================================================================
    Route::middleware(['role:admin'])->group(function () {

        // ... route import siswa yang lama ...

        // ROUTE BARU: IMPORT USER
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/import-users', [UserImportController::class, 'index'])->name('users.import');
        Route::post('/import-users', [UserImportController::class, 'store'])->name('users.import.store');

        // Role & Permission Routes
        Route::get('/admin/roles', [RolePermissionController::class, 'index'])->name('admin.roles.index');
        Route::post('/admin/roles', [RolePermissionController::class, 'storeRole'])->name('admin.roles.store');
        Route::get('/admin/roles/{id}/edit', [RolePermissionController::class, 'editRole'])->name('admin.roles.edit');
        Route::put('/admin/roles/{id}', [RolePermissionController::class, 'updateRolePermissions'])->name('admin.roles.update');
        Route::delete('/admin/roles/{id}', [RolePermissionController::class, 'destroyRole'])->name('admin.roles.destroy');

        // ROUTE MANAGE ROLE (Resourceful Route)
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);

        // Melihat daftar siswa yang sudah absen di jadwal tertentu
        //Route::get('/my-schedule/{id}/attendances', [ScheduleController::class, 'show'])->name('schedule.show');
        // Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
        // Route::get('/schedule/create', [ScheduleController::class, 'create'])->name('schedule.create');
        // Route::post('/schedule/store', [ScheduleController::class, 'store'])->name('schedule.store');
        // Route::post('/schedule/', [ScheduleController::class, 'destroy'])->name('schedule.destroy');

        Route::get('/student-qr/{id}', function ($id) {
                $student = Student::findOrFail($id);
                // QR Code berisi NIS siswa
                return QrCode::size(300)->generate($student->nis);
            });
        // Import Siswa
            Route::get('/import-students', [StudentImportController::class, 'index'])->name('students.import');
            Route::post('/import-students', [StudentImportController::class, 'store'])->name('students.import.store');

        // Route untuk melihat satu kartu siswa (untuk testing)
        Route::get('/print-card/{id}', function ($id) {
            $student = Student::findOrFail($id);
            // Generate QR Code NIS
            $qrcode = QrCode::size(120)->generate($student->nis);
            return view('print.single_card', compact('student', 'qrcode'));
        });

        // Route untuk mencetak SEMUA kartu siswa sekaligus (mass print)
        //Route::get('/print', [PrintController::class, 'index'])->name('print.index');
        // Route::get('/print-all-cards', function () {
        //     $students = Student::all();
        //     return view('print.all_cards', compact('students'));
        // });

        Route::get('/face/register', [FaceController::class, 'index'])->name('face.index');
        Route::get('/face/register/{id}', [FaceController::class, 'register'])->name('face.register');
        Route::post('/face/register/{id}', [FaceController::class, 'store'])->name('face.store');

        // --- 4. WHATSAPP GATEWAY (MANUAL BROADCAST) ---
        // Route::get('/whatsapp/test', [WhatsAppController::class, 'index'])->name('whatsapp.index');
        Route::post('/whatsapp/send', [WhatsAppController::class, 'store'])->name('whatsapp.store');
        // Route::get('/whatsapp/scan', [WhatsAppController::class, 'scan'])->name('whatsapp.scan');
        // ... di dalam group whatsapp ...
        Route::delete('/logs/clear', [WhatsAppController::class, 'clearLogs'])->name('whatsapp.logs.clear');
        Route::delete('/logs/{id}', [WhatsAppController::class, 'deleteLog'])->name('whatsapp.logs.delete');

        // --- 2. PENGATURAN SEKOLAH (SETTINGS) ---
        // Route ini diperlukan oleh form di settings/index.blade.php
        Route::prefix('settings')->name('settings.')->group(function() {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::put('/update', [SettingController::class, 'update'])->name('update');
            // Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
            // Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
            Route::get('/attendance', [SettingController::class, 'settingAttendance'])->name('attendance');
            Route::post('/attendance', [SettingController::class, 'updateAttendance'])->name('update.attendance');
        });
        Route::get('/user/all', [UserController::class, 'allUser'])->name('all.user');
        Route::get('/user/add', [UserController::class, 'addUser'])->name('add.user');
        Route::post('/user/store', [UserController::class, 'storeUser'])->name('store.user');
        Route::post('/update-user/status',[UserController::class, 'UpdateStatusUser'])->name('update.status.user');

        // Menampilkan halaman update
        Route::get('/system/update', [UpdateController::class, 'index'])->name('system.update.index');

        // Memproses update (POST)
        Route::post('/system/update', [UpdateController::class, 'doUpdate'])->name('system.update.run');

        // Import Skedule
        Route::get('/import-jadwal', [ScheduleImportController::class, 'index'])->name('jadwal.import');
        Route::post('/import-jadwal', [ScheduleImportController::class, 'store'])->name('jadwal.import.store');

        // Import Guru (Data Lengkap) - ROUTE BARU
        Route::get('/import-teachers', [TeacherImportController::class, 'index'])->name('teachers.import');
        Route::post('/import-teachers', [TeacherImportController::class, 'store'])->name('teachers.import.store');

        // [BARU] MANAJEMEN GURU (CRUD)
        // Ini menangani route teachers.index, teachers.edit, teachers.update, teachers.destroy
        // Route::resource('teachers', TeacherController::class);
        // Route::resource('teachers', TeacherController::class)->except(['create', 'store', 'show']);
        Route::resource('classrooms', ClassroomController::class);
        // Route::prefix('maping/rombel')->name('classrooms.')->group(function() {
        //     Route::get('/', [ClassroomController::class, 'index'])->name('index');
        //     Route::get('/create', [ClassroomController::class, 'create'])->name('create');
        //     Route::post('/store', [ClassroomController::class, 'store'])->name('store');
        //     Route::get('/edit/{id}', [ClassroomController::class, 'edit'])->name('edit');
        //     Route::put('/update/{id}', [ClassroomController::class, 'update'])->name('update');
        //     Route::delete('/destroy/{id}', [ClassroomController::class, 'destroy'])->name('destroy');
        // });
        // Route::resource('majors', MajorController::class);
        Route::prefix('setting/jurusan')->name('majors.')->group(function() {
            Route::get('/', [MajorController::class, 'index'])->name('index');
            Route::get('/create', [MajorController::class, 'create'])->name('create');
            Route::post('/store', [MajorController::class, 'store'])->name('store');
            Route::get('/edit/{id}', [MajorController::class, 'edit'])->name('edit');
            Route::put('/update/{id}', [MajorController::class, 'update'])->name('update');
            Route::delete('/destroy/{id}', [MajorController::class, 'destroy'])->name('destroy');
        });

        Route::patch('/students/{id}/remove-class', [StudentController::class, 'removeClassroom'])->name('students.remove_class');

         // Route khusus cetak ID Card (Fitur yang baru ditambahkan)
        Route::get('/students/{id}/print-id', [StudentController::class, 'printIdCard'])->name('students.print_id');
        // Route khusus cetak ID Card (PDF Download/Stream) - FITUR BARU
        Route::get('/students/{id}/print-id-pdf', [StudentController::class, 'printIdCardPdf'])->name('students.print_id_pdf');

        // Route untuk mengeluarkan siswa dari kelas (Unassign)
        Route::patch('/students/{id}/remove-class', [StudentController::class, 'removeClass'])->name('students.remove_class');
        // --- 6. WHATSAPP GATEWAY (BROADCAST) ---
        // Route::get('/whatsapp/test', [WhatsAppController::class, 'index'])->name('whatsapp.index'); // Manual 1 nomor
        Route::post('/whatsapp/send', [WhatsAppController::class, 'store'])->name('whatsapp.store');

        Route::get('/whatsapp/broadcast', [WhatsAppController::class, 'broadcast'])->name('whatsapp.broadcast'); // Broadcast Kelas
        Route::post('/whatsapp/broadcast', [WhatsAppController::class, 'sendBroadcast'])->name('whatsapp.broadcast.send');

        // Manajemen Siswa
        Route::get('/students/export', [StudentController::class, 'export'])->name('students.export'); // [BARU]
         // Manajemen Guru
        Route::get('/teachers/export', [TeacherController::class, 'export'])->name('teachers.export'); // [BARU]
        // Manajemen Kelas
        Route::get('/classrooms/export', [ClassroomController::class, 'export'])->name('classrooms.export'); // [BARU]

          // Route Cetak ID Card Massal per Kelas (PDF)
        Route::get('/classrooms/{id}/print-ids', [ClassroomController::class, 'printAllIdsPdf'])->name('classrooms.print_ids');
        Route::get('/teachers/export', [TeacherController::class, 'export'])->name('teachers.export');
        // PERBAIKAN: method 'show' tidak lagi di-exclude agar halaman detail guru bisa diakses
        // Pastikan baris ini ada di web.php
        // Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
        // Route::get('/teachers/show/{id}', [TeacherController::class, 'show'])->name('teachers.show');
        Route::get('/teachers/edit-json/{id}', [TeacherController::class, 'editJson'])->name('teachers.edit.json');
        // Route::get('teachers/edit-json/{id}', [TeacherController::class, 'editJson'])
        // ->name('teachers.edit.json')
        // ->where('id', '[a-f0-9-]+'); // Regex untuk menerima format UUID
        Route::resource('teachers', TeacherController::class);
        // Jika masih 404, tambahkan baris eksplisit ini DI ATAS resource:
    Route::get('teachers/{teacher}/edit', [App\Http\Controllers\TeacherController::class, 'edit'])->name('teachers.edit');
        // Route::resource('teachers', TeacherController::class)->except(['create', 'store']);
        // Route::resource('teachers', App\Http\Controllers\TeacherController::class)
        //   ->parameters(['teachers' => 'teacher']) // Menyamakan parameter
        //   ->where(['teacher' => '[a-f0-9-]+']); // Memaksa Laravel menerima format UUID

        // Route::resource('students', StudentController::class)->except(['create', 'show', 'store', 'index']);
        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        Route::get('/students/edit/{id}', [StudentController::class, 'edit'])->name('students.edit');
        Route::put('/students/update/{id}', [StudentController::class, 'update'])->name('students.update');
        Route::get('/students/destroy/{id}', [StudentController::class, 'destroy'])->name('students.destroy');

        // --- 4. MANAJEMEN KARTU SISWA ---
        Route::get('/print-cards', [CardController::class, 'index'])->name('print.index');

        // Cetak Full 1 Kelas
        Route::get('/print-cards/class/{id}', [CardController::class, 'printByClass'])->name('print.class');

        // [BARU] Pilih Siswa Tertentu & Cetak
        Route::get('/print-cards/select/{id}', [CardController::class, 'selectStudents'])->name('print.select');
        Route::post('/print-cards/print-selected', [CardController::class, 'printSelected'])->name('print.selected');

        // Cetak Semua & Satuan
        Route::get('/print-cards/all', [CardController::class, 'printAll'])->name('print.all');
        Route::get('/print-card/{id}', [CardController::class, 'printSingle'])->name('print.single');

        Route::get('/cards', [CardController::class, 'card'])->name('cards.card');
        Route::post('/cards/print', [CardController::class, 'print'])->name('cards.print');

        Route::get('/transkrip', [TranscriptController::class, 'index'])->name('reports.transcript.index');
        Route::get('/transkrip/cetak', [TranscriptController::class, 'show'])->name('reports.transcript.show');
        Route::get('/transkrip/cetak-kelas', [TranscriptController::class, 'printByClass'])->name('reports.transcript.class');

        // Route::resource('teaching-assignments', TeachingAssignmentController::class);
        Route::prefix('/teaching-assignments')->name('teaching-assignments.')->group(function() {
            Route::get('/', [TeachingAssignmentController::class, 'index'])->name('index');
            Route::get('/edit/{id}', [TeachingAssignmentController::class, 'index'])->name('edit');
            Route::get('/create', [TeachingAssignmentController::class, 'create'])->name('create');
            Route::post('/store', [TeachingAssignmentController::class, 'store'])->name('store');
            Route::put('/update/{id}', [TeachingAssignmentController::class, 'update'])->name('update');
            Route::delete('destroy/{id}', [TeachingAssignmentController::class, 'destroy'])->name('destroy');
        });

        Route::get('/reward/sertifikat', [CertificateController::class, 'index'])->name('certificates.index');
        Route::post('/reward/sertifikat/cetak', [CertificateController::class, 'generate'])->name('certificates.generate');
       // Route untuk Manajemen Perangkat Scanner & CCTV
        // Kita exclude 'create', 'store', 'show' karena registrasi dilakukan dari Frontend (Kiosk)
        // dan detail ditampilkan langsung di index/modal.
        Route::resource('scanner-devices', ScannerDeviceController::class)
            ->except(['create', 'show']);

        Route::get('/scan-camera', [ScannerDeviceController::class, 'scan'])->name('scan.camera');




        // --- REKAPITULASI MENYELURUH (MASTER RECAP) ---
        // Pastikan RecapController sudah dibuat
        Route::prefix('rekapitulasi')->name('recap.')->group(function() {
            Route::get('/', [RecapController::class, 'index'])->name('index');
            Route::get('/daily', [RecapController::class, 'dailyLog'])->name('daily');
            Route::get('/learning', [RecapController::class, 'learningLog'])->name('learning');
            Route::get('/students', [RecapController::class, 'studentsList'])->name('students');
            Route::get('/students/{id}', [RecapController::class, 'studentDetail'])->name('student.detail');
        });

        // Menu Jadwal Semua Guru (Master Schedule)
        Route::get('/schedule/all', [ScheduleController::class, 'allSchedules'])->name('schedule.all');
        Route::post('/schedule/admin/store', [ScheduleController::class, 'storeAsAdmin'])->name('schedule.store_admin');

        // --- PENGATURAN DATABASE (BACKUP & RESTORE) ---
        Route::get('/settings/database', [DatabaseController::class, 'index'])->name('database.index');
        Route::post('/settings/database/backup', [DatabaseController::class, 'backup'])->name('database.backup');
        Route::post('/settings/database/restore', [DatabaseController::class, 'restore'])->name('database.restore');

        Route::get('/pm2', [Pm2Controller::class, 'index'])->name('pm2.index');
        Route::post('/pm2/start', [Pm2Controller::class, 'start'])->name('pm2.start');
        Route::post('/pm2/stop', [Pm2Controller::class, 'stop'])->name('pm2.stop');
        Route::post('/pm2/restart', [Pm2Controller::class, 'restart'])->name('pm2.restart');
        Route::post('/pm2/delete', [Pm2Controller::class, 'delete'])->name('pm2.delete');
        Route::post('/pm2/monitor', [Pm2Controller::class, 'monitor'])->name('pm2.monitor');
        Route::post('/pm2/save', [Pm2Controller::class, 'save'])->name('pm2.save');
        Route::post('/pm2/install-service', [Pm2Controller::class, 'installService'])->name('pm2.install_service');
        Route::post('/pm2/uninstall-service', [Pm2Controller::class, 'uninstallService'])->name('pm2.uninstall_service');

        Route::prefix('whatsapp')->name('whatsapp.')->group(function() {
            Route::get('/', [WhatsappGatewayController::class, 'index'])->name('index');
            Route::post('/store', [WhatsappGatewayController::class, 'store'])->name('store');
            Route::get('/scan/{id}', [WhatsappGatewayController::class, 'scan'])->name('scan');
            Route::delete('/{id}', [WhatsappGatewayController::class, 'destroy'])->name('destroy');
            Route::get('/send', [WhatsappGatewayController::class, 'send'])->name('send');
            Route::post('/send', [WhatsappGatewayController::class, 'sendProcess'])->name('send_process');
        });

        Route::resource('rooms', RoomController::class);
        // Cetak Surat Tugas Guru
        Route::get('/report/surat-tugas/{teacher_id}', [ReportController::class, 'printSuratTugas'])->name('report.surat_tugas');
        // Cetak Surat Tugas SEMUA Guru
        Route::get('/report/surat-tugas-all', [ReportController::class, 'printAllSuratTugas'])->name('report.surat_tugas_all');

        Route::resource('inventory', InventoryController::class);
        Route::post('/inventory-loan', [InventoryLoanController::class, 'store'])->name('inventory-loan.store');
        // [PENTING] Tambahkan route ini untuk mengatasi error 404
        Route::get('/inventory-loan/active', [InventoryLoanController::class, 'activeLoans'])->name('inventory-loan.active');
        Route::put('/inventory-loan/{id}/return', [InventoryLoanController::class, 'returnItem'])->name('inventory-loan.return');
        // Tambahkan ini:
        Route::get('/inventory-loan/{id}/print', [InventoryLoanController::class, 'printProof'])->name('inventory-loan.print');
        // Route Cetak Barcode
        Route::get('/inventory/{id}/barcode', [InventoryController::class, 'printBarcode'])->name('inventory.barcode');

        Route::get('/monitoring-sholat', [PrayerMonitoringController::class, 'index'])->name('admin.prayer.monitoring');

        // Setting Lokasi Masjid
        Route::get('/prayer/settings', [PrayerSettingController::class, 'index'])->name('admin.prayer.settings');
        Route::put('/prayer/settings', [PrayerSettingController::class, 'update'])->name('admin.prayer.settings.update');
        Route::post('/prayer/sync', [PrayerSettingController::class, 'sync'])->name('admin.prayer.sync');
        Route::post('/prayer/settings/pull-attendance', [PrayerSettingController::class, 'pullAttendance'])->name('admin.prayer.pull_attendance');

        Route::get('/admin/mbg', [MbgController::class, 'index'])->name('admin.mbg.index');
        Route::get('/admin/mbg/scan', [MbgController::class, 'scan'])->name('admin.mbg.scan');
        // Proses Simpan Data (AJAX dari Scanner)
        Route::post('/admin/mbg/store', [MbgController::class, 'store'])->name('admin.mbg.store');

        Route::get('/admin/permit', [StudentPermitController::class, 'index'])->name('admin.permit.index');
        Route::get('/admin/permit/scan', [StudentPermitController::class, 'scan'])->name('admin.permit.scan');
        // Proses Simpan Data (AJAX dari Scanner)
        Route::post('/admin/permit/store', [StudentPermitController::class, 'store'])->name('admin.permit.store');

        // --- ROUTE PKL ---
        // Anda mungkin perlu membuat IndustryController terpisah untuk CRUD Master Data DUDI
        Route::resource('admin/industries', IndustryController::class)->except(['show']);

        // Route Penempatan PKL (Sesuai yang dibuat di atas)
        Route::get('admin/internships', [InternshipController::class, 'index'])->name('admin.internships.index');
        Route::post('admin/internships', [InternshipController::class, 'store'])->name('admin.internships.store');
        Route::patch('admin/internships/{id}/status', [InternshipController::class, 'updateStatus'])->name('admin.internships.status');
        Route::delete('admin/internships/{id}', [InternshipController::class, 'destroy'])->name('admin.internships.destroy');
        Route::get('admin/internships', [InternshipController::class, 'index'])->name('admin.internships.index');
        Route::post('admin/internships/assign/{id}', [InternshipController::class, 'assignAdvisor'])->name('admin.internships.assign');
        // Route untuk Import Industri
        Route::post('admin/industries/import', [IndustryController::class, 'import'])->name('industries.import');

        // Mapping Kelas PKL
        Route::get('/admin/pkl/mapping', [PKLMappingController::class, 'index'])->name('admin.pkl.mapping');
        Route::put('/admin/pkl/mapping', [PKLMappingController::class, 'update'])->name('admin.pkl.mapping.update');

        Route::get('/admin/pkl/timeline', [InternshipTimelineController::class, 'index'])->name('admin.timeline.index');
        Route::post('/admin/pkl/timeline', [InternshipTimelineController::class, 'store'])->name('admin.timeline.store');
        Route::put('/admin/pkl/timeline/{id}', [InternshipTimelineController::class, 'update'])->name('admin.timeline.update');
        Route::delete('/admin/pkl/timeline/{id}', [InternshipTimelineController::class, 'destroy'])->name('admin.timeline.destroy');

        Route::get('/admin/inventaris', [InventoryAdminController::class, 'index'])->name('admin.inventory.index');
        Route::post('/admin/inventaris/item', [InventoryAdminController::class, 'storeItem'])->name('admin.inventory.item.store');

        // Transaksi masuk keluar
        Route::get('/admin/inventaris/mutasi', [InventoryAdminController::class, 'transactions'])->name('admin.inventory.transaction.index');
        Route::get('/admin/transactions/create', [InventoryAdminController::class, 'createTransaction'])->name('admin.transactions.create');
        Route::post('/admin/inventaris/transaction', [InventoryAdminController::class, 'storeTransaction'])->name('admin.inventory.transaction.store');

        // Riwayat (Opsional, Anda bisa membuat view resources/views/inventory/history.blade.php terpisah)
        Route::get('/admin/inventaris/history', [InventoryAdminController::class, 'history'])->name('admin.inventory.history');

        Route::get('/admin/inventaris/template', [InventoryAdminController::class, 'downloadTemplate'])->name('admin.inventory.template');
        Route::post('/admin/inventaris/import', [InventoryAdminController::class, 'import'])->name('admin.inventory.import');

        Route::get('/usulan-daya-tampung', [CapacityReportController::class, 'index'])->name('laporan.daya_tampung');
        Route::get('/program', [ProgramController::class, 'index'])->name('programs.index');
        Route::get('/program/create', [ProgramController::class, 'create'])->name('programs.create');
        Route::get('/program/edit/{id}', [ProgramController::class, 'edit'])->name('programs.edit');
        Route::post('/program/store', [ProgramController::class, 'store'])->name('programs.store');
        Route::put('/program/{id}', [ProgramController::class, 'update'])->name('programs.update');
        Route::delete('/program/{id}', [ProgramController::class, 'destroy'])->name('programs.destroy');

        Route::get('/admin/izin', [AdminPermissionController::class, 'index'])->name('admin.izin');

    });

    Route::middleware(['role:siswa'])->group(function () {
        Route::prefix('student')->name('student.')->group(function() {

            // Profil Siswa (Edit No HP & Alamat)
            Route::get('/dashboard', [DashboardStudentController::class, 'index'])->name('dashboard');
            // Route::get('/dashboard', [SiswaDashboardController::class, 'dashboard'])->name('dashboard');
            Route::get('/my-profile', [StudentAreaController::class, 'profileStudent'])->name('profile');
            Route::put('/my-profile', [StudentAreaController::class, 'updateProfile'])->name('profile.update');
            // Riwayat Absensi
            Route::get('/my-history/subject', [StudentAreaController::class, 'historySubject'])->name('history.subject');
            Route::get('/my-history/daily', [StudentAreaController::class, 'historyDaily'])->name('history.daily');

            // [BARU] Cetak Kartu Sendiri
            // Cetak Kartu Sendiri
            Route::get('/my-card', [StudentAreaController::class, 'printCard'])->name('print.card');

            // Fitur Absensi Sholat
            Route::get('/prayer', [PrayerStudentController::class, 'index'])->name('prayer.index');
            Route::post('/prayer/store', [PrayerStudentController::class, 'store'])->name('prayer.store');

            // Pemilihan PKL Siswa
            Route::get('/internships', [InternshipStudentController::class, 'index'])->name('internships.index');
            Route::post('/internships/apply', [InternshipStudentController::class, 'apply'])->name('internships.apply');
            Route::get('/printConsentLetter', [InternshipStudentController::class, 'printConsentLetter'])->name('internships.agreement');
            Route::post('/upload', [InternshipStudentController::class, 'uploadConsent'])->name('internships.upload');

            Route::get('/internships/attendance', [InternshipAttendanceController::class, 'index'])->name('internships.attendance.index');

            Route::post('/internships/attendance', [InternshipAttendanceController::class, 'store'])->name('internships.attendance.store');

            Route::get('internships/transcript', [InternshipStudentController::class, 'transcript'])->name('internships.transcript');
            Route::get('internships/transcript/print', [InternshipStudentController::class, 'printTranscript'])->name('internships.print_transcript');
            // Jurnal Ramadhan
            Route::get('/ramadhan', [RamadanJournalStudentController::class, 'index'])->name('ramadan.index');
            Route::post('/ramadhan', [RamadanJournalStudentController::class, 'store'])->name('ramadan.store');

            Route::get('/guidance', [GuidanceStudentController::class, 'index'])->name('guidance.index');
            Route::post('/guidance/{id}/upload', [GuidanceStudentController::class, 'uploadAgreement'])->name('guidance.upload');


        });
    });
});
