<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Imports\QuestionsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class GuruQuestionController extends Controller
{
    // Menampilkan halaman import soal
    public function showImportForm(Exam $exam)
    {
        return view('teacher.questions.import', compact('exam'));
    }

    // Mengolah file Excel yang diunggah
    public function import(Request $request, Exam $exam)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ], [
            'file.required' => 'Silakan pilih file Excel terlebih dahulu.',
            'file.mimes'    => 'Format file harus berupa .xlsx, .xls, atau .csv',
            'file.max'      => 'Ukuran file maksimal adalah 2MB.',
        ]);

        try {
            Excel::import(new QuestionsImport($exam->id), $request->file('file'));

            return redirect()->route('teacher.exams.show', $exam->id)
                             ->with('success', 'Soal berhasil diimpor dari file Excel!');
        } catch (\Exception $e) {
            return redirect()->back()
                             ->with('error', 'Gagal mengimpor soal: ' . $e->getMessage());
        }
    }
}
