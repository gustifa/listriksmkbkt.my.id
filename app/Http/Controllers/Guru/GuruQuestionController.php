<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use App\Imports\QuestionsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\QuestionsExport; // Pastikan import ini ditambahkan

class GuruQuestionController extends Controller
{
    /**
     * Menampilkan form input soal manual
     */
    public function create(Exam $exam)
    {
        return view('guru.questions.create', compact('exam'));
    }

    public function store(Request $request, Exam $exam)
{
    // Tipe dari form HTML
    $formType = $request->input('type', 'pilihan_ganda');

    // Mapping tipe soal agar sesuai dengan CHECK constraint PostgreSQL
    $typeMapping = [
        'pilihan_ganda'   => 'single',    // Menggunakan 'single' (sesuai 'Tipe: SINGLE' di detail ujian)
        'multiple_choice' => 'multiple',  // Menggunakan 'multiple'
        'essay'           => 'essay',     // Menggunakan 'essay'
    ];

    $questionType = $typeMapping[$formType] ?? $formType;

    // Rules validasi
    $rules = [
        'question_text' => 'required|string',
        'score'         => 'required|numeric|min:1',
    ];

    if ($formType === 'pilihan_ganda') {
        $rules['options_pg.A'] = 'required|string';
        $rules['options_pg.B'] = 'required|string';
        $rules['correct_answer_single'] = 'required|in:A,B,C,D,E';
    } elseif ($formType === 'multiple_choice') {
        $rules['options_mc.A'] = 'required|string';
        $rules['options_mc.B'] = 'required|string';
        $rules['correct_answer_multi'] = 'required|array|min:1';
    } elseif ($formType === 'essay') {
        $rules['essay_answer'] = 'required|string';
    }

    $request->validate($rules, [
        'question_text.required'         => 'Pertanyaan wajib diisi.',
        'options_pg.A.required'          => 'Opsi A wajib diisi.',
        'options_pg.B.required'          => 'Opsi B wajib diisi.',
        'options_mc.A.required'          => 'Opsi A wajib diisi.',
        'options_mc.B.required'          => 'Opsi B wajib diisi.',
        'correct_answer_single.required' => 'Pilih 1 kunci jawaban yang benar.',
        'correct_answer_multi.required'  => 'Pilih minimal 1 kunci jawaban yang benar.',
        'essay_answer.required'          => 'Pedoman / Kunci Jawaban Essay wajib diisi.',
        'score.required'                 => 'Bobot nilai wajib diisi.',
    ]);

    DB::beginTransaction();
    try {
        $correctAnswerData = null;
        $optionsData = null;

        if ($formType === 'pilihan_ganda') {
            $correctAnswerData = [strtoupper($request->correct_answer_single)];

            $rawOptions = $request->input('options_pg', []);
            $formattedOptions = [];
            foreach ($rawOptions as $key => $val) {
                if (!is_null($val) && trim($val) !== '') {
                    $formattedOptions[] = [
                        'key'  => $key,
                        'text' => trim($val)
                    ];
                }
            }
            $optionsData = $formattedOptions;

        } elseif ($formType === 'multiple_choice') {
            $correctAnswerData = array_map('strtoupper', $request->correct_answer_multi);

            $rawOptions = $request->input('options_mc', []);
            $formattedOptions = [];
            foreach ($rawOptions as $key => $val) {
                if (!is_null($val) && trim($val) !== '') {
                    $formattedOptions[] = [
                        'key'  => $key,
                        'text' => trim($val)
                    ];
                }
            }
            $optionsData = $formattedOptions;

        } elseif ($formType === 'essay') {
            $correctAnswerData = [$request->essay_answer];
            $optionsData = null;
        }

        // Simpan ke tabel questions
        Question::create([
            'exam_id'        => $exam->id,
            'question_type'  => $questionType, // Nilai 'single', 'multiple', atau 'essay'
            'question_text'  => $request->question_text,
            'options'        => $optionsData,
            'correct_answer' => $correctAnswerData,
            'score_weight'   => (int) $request->score,
        ]);

        DB::commit();

        return redirect()->route('guru.exams.show', $exam->id)
                         ->with('success', 'Berhasil menambahkan soal baru!');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withInput()->with('error', 'Gagal menyimpan soal: ' . $e->getMessage());
    }
}

    /**
     * Menampilkan form import Excel
     */
    public function showImportForm(Exam $exam)
    {
        return view('guru.questions.import', compact('exam'));
    }

    /**
     * Memproses import file Excel/CSV
     */
    // public function import(Request $request, Exam $exam)
    // {
    //     $request->validate([
    //         'question_type' => 'required|in:pilihan_ganda,multiple_choice,essay',
    //         'file'          => [
    //             'required',
    //             'file',
    //             'max:5120',
    //             'mimes:xlsx,xls,csv,txt',
    //             'mimetypes:application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv,text/plain,application/csv'
    //         ],
    //     ], [
    //         'file.required'  => 'File Excel/CSV wajib diunggah.',
    //         'file.mimes'     => 'Format file harus berupa .xlsx, .xls, atau .csv.',
    //         'file.mimetypes' => 'Tipe MIME file tidak sesuai. Harap unggah file Excel/CSV yang valid.',
    //         'file.max'       => 'Ukuran file tidak boleh lebih dari 5 MB.',
    //     ]);

    //     $type = $request->question_type;

    //     try {
    //         Excel::import(new QuestionsImport($exam->id, $type), $request->file('file'));

    //         return redirect()->route('guru.exams.show', $exam->id)
    //                         ->with('success', 'Berhasil mengimpor soal ' . str_replace('_', ' ', $type) . ' ke dalam ujian.');
    //     } catch (\Exception $e) {
    //         return redirect()->back()
    //                         ->with('error', 'Gagal mengimpor file: ' . $e->getMessage());
    //     }
    // }

    /**
 * Proses Upload dan Import Excel
 */
public function import(Request $request, Exam $exam)
{
    $request->validate([
        'file' => [
            'required',
            'file',
            'max:5120',
            'mimes:xlsx,xls,csv',
            'mimetypes:application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv,text/plain,application/csv'
        ],
    ], [
        'file.required'  => 'File Excel wajib diunggah.',
        'file.mimes'     => 'Format file harus berupa .xlsx, .xls, atau .csv.',
        'file.mimetypes' => 'Tipe MIME file tidak sesuai. Harap unggah file Excel yang valid.',
        'file.max'       => 'Ukuran file tidak boleh lebih dari 5 MB.',
    ]);

    try {
        Excel::import(new QuestionsImport($exam->id), $request->file('file'));

        return redirect()->route('guru.exams.show', $exam->id)
                        ->with('success', 'Berhasil mengimpor seluruh soal dari file Excel!');
    } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
        $failures = $e->failures();
        $errorMessages = [];
        foreach ($failures as $failure) {
            $errorMessages[] = "Baris ke-{$failure->row()}: " . implode(', ', $failure->errors());
        }
        return redirect()->back()->with('error', 'Gagal mengimpor: ' . implode(' | ', $errorMessages));
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Gagal mengimpor file: ' . $e->getMessage());
    }
}

    /**
     * Menampilkan form edit soal
     */
    public function edit(Question $question)
    {
        $exam = $question->exam;

        // Mapping dari value database ('single', 'multiple', 'essay') ke value form HTML
        $typeMapping = [
            'single'   => 'pilihan_ganda',
            'multiple' => 'multiple_choice',
            'essay'    => 'essay',
        ];

        $formType = $typeMapping[$question->question_type] ?? $question->question_type;

        return view('guru.questions.edit', compact('question', 'exam', 'formType'));
    }

    /**
     * Memperbarui data soal di database
     */
    public function update(Request $request, Question $question)
    {
        $formType = $request->input('type', 'pilihan_ganda');

        // Mapping tipe soal untuk disesuaikan dengan CHECK constraint PostgreSQL ('single', 'multiple', 'essay')
        $typeMapping = [
            'pilihan_ganda'   => 'single',
            'multiple_choice' => 'multiple',
            'essay'           => 'essay',
        ];

        $questionType = $typeMapping[$formType] ?? $formType;

        // Rules Validasi
        $rules = [
            'question_text' => 'required|string',
            'score'         => 'required|numeric|min:1',
        ];

        if ($formType === 'pilihan_ganda') {
            $rules['options_pg.A'] = 'required|string';
            $rules['options_pg.B'] = 'required|string';
            $rules['correct_answer_single'] = 'required|in:A,B,C,D,E';
        } elseif ($formType === 'multiple_choice') {
            $rules['options_mc.A'] = 'required|string';
            $rules['options_mc.B'] = 'required|string';
            $rules['correct_answer_multi'] = 'required|array|min:1';
        } elseif ($formType === 'essay') {
            $rules['essay_answer'] = 'required|string';
        }

        $request->validate($rules, [
            'question_text.required'         => 'Pertanyaan wajib diisi.',
            'options_pg.A.required'          => 'Opsi A wajib diisi.',
            'options_pg.B.required'          => 'Opsi B wajib diisi.',
            'options_mc.A.required'          => 'Opsi A wajib diisi.',
            'options_mc.B.required'          => 'Opsi B wajib diisi.',
            'correct_answer_single.required' => 'Pilih 1 kunci jawaban yang benar.',
            'correct_answer_multi.required'  => 'Pilih minimal 1 kunci jawaban yang benar.',
            'essay_answer.required'          => 'Pedoman / Kunci Jawaban Essay wajib diisi.',
            'score.required'                 => 'Bobot nilai wajib diisi.',
        ]);

        DB::beginTransaction();
        try {
            $correctAnswerData = null;
            $optionsData = null;

            if ($formType === 'pilihan_ganda') {
                $correctAnswerData = [strtoupper($request->correct_answer_single)];

                $rawOptions = $request->input('options_pg', []);
                $formattedOptions = [];
                foreach ($rawOptions as $key => $val) {
                    if (!is_null($val) && trim($val) !== '') {
                        $formattedOptions[] = [
                            'key'  => $key,
                            'text' => trim($val)
                        ];
                    }
                }
                $optionsData = $formattedOptions;

            } elseif ($formType === 'multiple_choice') {
                $correctAnswerData = array_map('strtoupper', $request->correct_answer_multi);

                $rawOptions = $request->input('options_mc', []);
                $formattedOptions = [];
                foreach ($rawOptions as $key => $val) {
                    if (!is_null($val) && trim($val) !== '') {
                        $formattedOptions[] = [
                            'key'  => $key,
                            'text' => trim($val)
                        ];
                    }
                }
                $optionsData = $formattedOptions;

            } elseif ($formType === 'essay') {
                $correctAnswerData = [$request->essay_answer];
                $optionsData = null;
            }

            // Update record soal
            $question->update([
                'question_type'  => $questionType,
                'question_text'  => $request->question_text,
                'options'        => $optionsData,
                'correct_answer' => $correctAnswerData,
                'score_weight'   => (int) $request->score,
            ]);

            DB::commit();

            return redirect()->route('guru.exams.show', $question->exam_id)
                            ->with('success', 'Berhasil memperbarui soal!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui soal: ' . $e->getMessage());
        }
    }

    /**
     * Download template CSV/Excel untuk import
     */
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

    /**
     * Export soal ujian ke file Excel
     */
    public function export(Exam $exam)
    {
        if ($exam->questions()->count() === 0) {
            return redirect()->back()->with('error', 'Tidak ada soal yang dapat diexport.');
        }

        $safeTitle = preg_replace('/[^A-Za-z0-9_\-]/', '_', $exam->title);
        $filename = 'Export_Soal_' . $safeTitle . '_' . date('Ymd_His') . '.xlsx';

        return Excel::download(new QuestionsExport($exam), $filename);
    }

    /**
 * Duplikat / Copy Soal yang sudah ada
 */
public function duplicate(Question $question)
{
    DB::beginTransaction();
    try {
        // Buat salinan record soal
        $newQuestion = $question->replicate();

        // Buat UUID baru untuk ID
        $newQuestion->id = (string) \Illuminate\Support\Str::uuid();

        // Tambahkan penanda salinan jika diinginkan
        $newQuestion->question_text = $question->question_text . ' (Salinan)';

        $newQuestion->save();

        DB::commit();

        return redirect()->route('guru.exams.show', $question->exam_id)
                         ->with('success', 'Berhasil menduplikasi soal!');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Gagal menduplikasi soal: ' . $e->getMessage());
    }
}

/**
 * Hapus Soal dari database
 */
public function destroy(Question $question)
{
    DB::beginTransaction();
    try {
        $examId = $question->exam_id;
        $question->delete();

        DB::commit();

        return redirect()->route('guru.exams.show', $examId)
                         ->with('success', 'Berhasil menghapus soal!');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Gagal menghapus soal: ' . $e->getMessage());
    }
}

/**
 * Hapus beberapa soal sekaligus (Bulk Delete)
 */
public function bulkDestroy(Request $request)
{
    $request->validate([
        'question_ids'   => 'required|array|min:1',
        'question_ids.*' => 'exists:questions,id',
    ], [
        'question_ids.required' => 'Pilih minimal satu soal yang ingin dihapus.',
    ]);

    DB::beginTransaction();
    try {
        $ids = $request->input('question_ids');

        // Hapus batch soal
        Question::whereIn('id', $ids)->delete();

        DB::commit();

        return redirect()->back()->with('success', 'Berhasil menghapus ' . count($ids) . ' soal yang dipilih!');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Gagal menghapus soal terpilih: ' . $e->getMessage());
    }
}

/**
 * Memperbarui bobot secara massal/rata untuk seluruh soal dalam ujian
 */
public function updateBulkWeight(Request $request, Exam $exam)
{
    $request->validate([
        'target_total_score'   => 'nullable|numeric|min:1',
        'weight_per_question'  => 'nullable|numeric|min:0.1',
    ]);

    $totalQuestions = $exam->questions()->count();

    if ($totalQuestions === 0) {
        return redirect()->back()->with('error', 'Belum ada soal pada ujian ini.');
    }

    if ($request->filled('target_total_score')) {
        // Opsi A: Bagi Rata Target Total Nilai (Misal: Total 100 / 10 Soal = Bobot 10)
        $weightPerQuestion = round($request->target_total_score / $totalQuestions, 2);
    } elseif ($request->filled('weight_per_question')) {
        // Opsi B: Bobot Spesifik per soal
        $weightPerQuestion = $request->weight_per_question;
    } else {
        return redirect()->back()->with('error', 'Pilih salah satu metode pengisian bobot.');
    }

    // Update kolom score_weight untuk seluruh soal
    $exam->questions()->update([
        'score_weight' => $weightPerQuestion
    ]);

    return redirect()->back()->with('success', "Bobot berhasil diperbarui! Setiap soal memiliki bobot {$weightPerQuestion}.");
}

/**
 * Memperbarui bobot untuk 1 soal secara spesifik
 */
public function updateSingleWeight(Request $request, Question $question)
{
    $request->validate([
        'score_weight' => 'required|numeric|min:0.1'
    ]);

    $question->update([
        'score_weight' => $request->score_weight
    ]);

    return redirect()->back()->with('success', 'Bobot soal berhasil diperbarui.');
}


}
