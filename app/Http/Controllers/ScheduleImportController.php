<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ScheduleImport;

class ScheduleImportController extends Controller
{
    public function index()
    {
        return view('schedule.import');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new ScheduleImport, $request->file('file'));
            return redirect()->back()->with('success', 'Data Jadwal Berhasil Diimport!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal Import: ' . $e->getMessage());
        }
    }
}