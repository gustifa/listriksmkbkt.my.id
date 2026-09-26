<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classroom;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentExport;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentController extends Controller
{
    /**
     * Tampilkan Daftar Siswa (dengan Pencarian & Pagination)
     */
    public function index(Request $request)
    {
        $query = Student::with('classroom');

        // Logika Pencarian
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('nis', 'LIKE', "%{$search}%");
            });
        }

        // Filter Kelas (Opsional)
        if ($request->has('classroom_id') && $request->classroom_id != '') {
            $query->where('classroom_id', $request->classroom_id);
        }

        // Ambil data dengan pagination (10 per halaman)
        //$students = $query->orderBy('classroom_id')->orderBy('name')->paginate(10);
        $students = $query->orderBy('classroom_id')->orderBy('name')->get()->all();

        // Ambil daftar kelas untuk filter
        $classrooms = Classroom::orderBy('name')->get();

        return view('students.index', compact('students', 'classrooms'));
    }

    /**
     * Form Edit Siswa
     */
    public function edit($id)
    {
        $student = Student::findOrFail($id);
        $classrooms = Classroom::orderBy('name')->get();

        return view('students.edit', compact('student', 'classrooms'));
    }

    /**
     * Update Data Siswa
     */
    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $request->validate([
            'nis' => 'required|unique:students,nis,' . $id, // Unique kecuali punya sendiri
            'name' => 'required|string|max:255',
            'classroom_id' => 'required|exists:classrooms,id',
            'phone' => 'nullable|numeric',
        ]);

        $student->update([
            'nis' => $request->nis,
            'name' => $request->name,
            'classroom_id' => $request->classroom_id,
            'phone' => $request->phone,
        ]);

        return redirect()->route('students.index')->with('success', 'Data siswa berhasil diperbarui!');
    }

    /**
     * Hapus Siswa
     */
    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return redirect()->route('students.index')->with('success', 'Data siswa berhasil dihapus!');
    }

    public function removeClassroom($id)
    {
        $student = Student::findOrFail($id);
        $student->update(['classroom_id' => null]); // Set kelas jadi null
        return back()->with('success', "Siswa {$student->name} berhasil dikeluarkan dari kelas.");
    }

    public function removeClass($id)
    {
        try {
            $student = Student::findOrFail($id);
            $namaKelas = $student->classroom->name ?? '-';

            // Set classroom_id menjadi null (tanpa menghapus siswa dari database)
            $student->update(['classroom_id' => null]);

            return redirect()->back()->with('success', "Siswa {$student->name} berhasil dikeluarkan dari kelas {$namaKelas}.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function export()
    {
        return Excel::download(new StudentExport, 'Data-Siswa.xlsx');
    }

    /**
     * Cetak ID Card Siswa (Ukuran 9cm x 4cm).
     */
    public function printIdCard($id)
    {
        // Ambil data siswa beserta kelasnya
        $student = Student::with('classroom')->findOrFail($id);

        return view('students.print_id_card', compact('student'));
    }


    public function printIdCardPdf($id)
{
    $student = \App\Models\Student::with('classroom')->findOrFail($id);

    // Set ukuran kertas kustom sesuai ID Card (9cm x 5.5cm atau 9cm x 4cm)
    // array(0, 0, width_in_points, height_in_points) -> 1cm ≈ 28.35 points
    $customPaper = array(0, 0, 255.1, 113.4); // ~9cm x 4cm

    $pdf = Pdf::loadView('students.pdf_id_card', compact('student'))
                ->setPaper($customPaper, 'landscape');

    return $pdf->stream('ID-CARD-' . $student->nis . '.pdf');
}



}
