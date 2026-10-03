<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Imports\QuestionsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class GuruQuestionController extends Controller
{
    public function showImportForm(Exam $exam)
    {
        return view('guru.questions.import', compact('exam'));
    }

    public function import(Request $request, Exam $exam)
    {
        $request->validate([
            'question_type' => 'required|in:pilihan_ganda,multiple_choice,essay',
            'file'          => [
                'required',
                'file',
                'max:5120', // Maksimal 5 MB
                // Memperluas MIME type & ekstensi yang diizinkan
                'mimes:xlsx,xls,csv,txt',
                'mimetypes:application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv,text/plain,application/csv'
            ],
        ], [
            'file.required'  => 'File Excel/CSV wajib diunggah.',
            'file.mimes'     => 'Format file harus berupa .xlsx, .xls, atau .csv.',
            'file.mimetypes' => 'Tipe MIME file tidak sesuai. Harap unggah file Excel/CSV yang valid.',
            'file.max'       => 'Ukuran file tidak boleh lebih dari 5 MB.',
        ]);

        $type = $request->question_type;

        try {
            Excel::import(new QuestionsImport($exam->id, $type), $request->file('file'));

            return redirect()->route('guru.exams.show', $exam->id)
                            ->with('success', 'Berhasil mengimpor soal ' . str_replace('_', ' ', $type) . ' ke dalam ujian.');
        } catch (\Exception $e) {
            return redirect()->back()
                            ->with('error', 'Gagal mengimpor file: ' . $e->getMessage());
        }
    }

    public function downloadTemplate(Request $request)
    {
        $type = $request->query('type', 'pilihan_ganda');

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="Template_Soal_' . strtoupper($type) . '.csv"',
        ];

        $callback = function () use ($type) {
            $file = fopen('php://output', 'w');

            if ($type === 'essay') {
                fputcsv($file, ['Soal Pertanyaan', 'Pedoman / Kunci Jawaban', 'Bobot Nilai']);
                fputcsv($file, ['Jelaskan proses terjadinya fotosintesis pada tumbuhan!', 'Proses fotosintesis memerlukan sinar matahari...', '10']);
            } elseif ($type === 'multiple_choice') {
                fputcsv($file, ['Soal Pertanyaan', 'Opsi A', 'Opsi B', 'Opsi C', 'Opsi D', 'Opsi E', 'Kunci Jawaban', 'Bobot Nilai']);
                fputcsv($file, ['Manakah di bawah ini yang merupakan organ pernapasan manusia?', 'Paru-paru', 'Jantung', 'Hidung', 'Lambung', 'Tenggorokan', 'A,C,E', '5']);
            } else {
                fputcsv($file, ['Soal Pertanyaan', 'Opsi A', 'Opsi B', 'Opsi C', 'Opsi D', 'Opsi E', 'Kunci Jawaban', 'Bobot Nilai']);
                fputcsv($file, ['Ibu kota negara Indonesia adalah...', 'Surabaya', 'Bandung', 'Jakarta', 'Medan', 'Makassar', 'C', '2']);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
