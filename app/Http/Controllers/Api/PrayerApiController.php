<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PrayerAttendance;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class PrayerApiController extends Controller
{
    /**
     * Export data absensi untuk ditarik oleh Server B
     */
    public function exportAttendance(Request $request)
    {
        // 1. Ambil Key yang valid dari database Server A
        $validKey = Setting::where('key', 'server_sync_key')->value('value');

        // 2. Ambil Key dari Request (Bisa dari Header atau Query Parameter)
        $providedKey = $request->header('X-Server-Key') ?? $request->query('key');

        // 3. Validasi Key
        if (!$validKey || $providedKey !== $validKey) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: API Key tidak valid atau belum diatur di Server A.'
            ], 401);
        }

        // 4. Validasi Parameter Tanggal
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        if (!$startDate || !$endDate) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Parameter start_date dan end_date diperlukan.'
            ], 400);
        }

        try {
            // 5. Ambil data absensi dengan Join ke tabel Students untuk mendapatkan NIS
            // NIS digunakan sebagai identifier unik antar server
            $data = DB::table('prayer_attendances')
                ->join('students', 'prayer_attendances::student_id', '=', 'students.id')
                ->whereBetween('prayer_attendances.date', [$startDate, $endDate])
                ->select(
                    'students.nis',
                    'prayer_attendances.date',
                    'prayer_attendances.prayer_name',
                    'prayer_attendances.check_in_time',
                    'prayer_attendances.status',
                    'prayer_attendances.latitude',
                    'prayer_attendances.longitude'
                )
                ->get();

            return response()->json([
                'status' => 'success',
                'total'  => $data->count(),
                'data'   => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
