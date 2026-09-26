<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UserImport;

class UserImportController extends Controller
{
    public function index()
    {
        return view('users.import');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new UserImport, $request->file('file'));
            return redirect()->back()->with('success', 'Data User & Guru Berhasil Diimport!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal Import: ' . $e->getMessage());
        }
    }
}
