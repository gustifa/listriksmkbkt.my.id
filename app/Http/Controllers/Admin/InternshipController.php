<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Internship;
use App\Models\Industry;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Classroom;

class InternshipController extends Controller
{
    public function index(Request $request)
    {
        $query = Internship::with(['student.classroom', 'industry', 'advisor']);

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by Industry
        if ($request->filled('industry_id')) {
            $query->where('industry_id', $request->industry_id);
        }

        $internships = $query->latest()->paginate(20)->withQueryString();
        
        $industries = Industry::orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->get();
        $teachers = Teacher::orderBy('name')->get();

        // Mengambil siswa yang belum PKL atau sudah selesai
        $students = Student::whereDoesntHave('internships', function($q) {
            $q->whereIn('status', ['pending', 'active']);
        })->orderBy('name')->get();

        return view('admin.internships.index', compact('internships', 'industries', 'students', 'teachers', 'classrooms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'industry_id' => 'required|exists:industries,id',
            'advisor_id' => 'nullable|exists:teachers,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Cek Kuota Industri
        $industry = Industry::findOrFail($request->industry_id);
        $activeInternsCount = Internship::where('industry_id', $industry->id)
                                        ->whereIn('status', ['pending', 'active'])
                                        ->count();

        if ($activeInternsCount >= $industry->quota && $industry->quota > 0) {
            return redirect()->back()->with('error', "Kuota di {$industry->name} sudah penuh! (Maks: {$industry->quota})");
        }

        Internship::create($request->all());

        return redirect()->back()->with('success', 'Siswa berhasil ditempatkan di tempat PKL.');
    }

    public function assignAdvisor(Request $request, $id)
    {
        $request->validate([
            'advisor_id' => 'required|exists:teachers,id'
        ]);


        $internship = Internship::findOrFail($id);
       
        $internship->update([
            'advisor_id' => $request->advisor_id,
            'advisor_status' => 'approved' // Langsung Approved karena Admin yang set
        ]);


        return redirect()->back()->with('success', 'Guru Pembimbing berhasil ditetapkan.');
    }


    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,active,completed,cancelled'
        ]);

        $internship = Internship::findOrFail($id);
        $internship->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Status PKL berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Internship::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Data penempatan PKL dihapus.');
    }


}

// <?php

// namespace App\Http\Controllers\Admin;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use App\Models\Internship;
// use App\Models\Industry;
// use App\Models\Student;
// use App\Models\Teacher;
// use App\Models\Classroom;

// class InternshipController extends Controller
// {
//     public function index(Request $request)
//     {
//         $query = Internship::with(['student.classroom', 'industry', 'advisor']);

//         // Filter by Status
//         if ($request->filled('status')) {
//             $query->where('status', $request->status);
//         }

//         // Filter by Industry
//         if ($request->filled('industry_id')) {
//             $query->where('industry_id', $request->industry_id);
//         }

//         $internships = $query->latest()->paginate(20)->withQueryString();
        
//         $industries = Industry::orderBy('name')->get();
//         $classrooms = Classroom::orderBy('name')->get();
//         $teachers = Teacher::orderBy('name')->get();

//         // Mengambil siswa yang belum PKL atau sudah selesai
//         $students = Student::whereDoesntHave('internships', function($q) {
//             $q->whereIn('status', ['pending', 'active']);
//         })->orderBy('name')->get();

//         return view('admin.internships.index', compact('internships', 'industries', 'students', 'teachers', 'classrooms'));
//     }

//     public function store(Request $request)
//     {
//         $request->validate([
//             'student_id' => 'required|exists:students,id',
//             'industry_id' => 'required|exists:industries,id',
//             'advisor_id' => 'nullable|exists:teachers,id',
//             'start_date' => 'required|date',
//             'end_date' => 'required|date|after_or_equal:start_date',
//         ]);

//         // Cek Kuota Industri
//         $industry = Industry::findOrFail($request->industry_id);
//         $activeInternsCount = Internship::where('industry_id', $industry->id)
//                                         ->whereIn('status', ['pending', 'active'])
//                                         ->count();

//         if ($activeInternsCount >= $industry->quota && $industry->quota > 0) {
//             return redirect()->back()->with('error', "Kuota di {$industry->name} sudah penuh! (Maks: {$industry->quota})");
//         }

//         // Siapkan data
//         $data = $request->all();
        
//         // Jika admin langsung memilih pembimbing saat input, set status ke approved
//         if ($request->filled('advisor_id')) {
//             $data['advisor_status'] = 'approved';
//         }

//         Internship::create($data);

//         return redirect()->back()->with('success', 'Siswa berhasil ditempatkan di tempat PKL.');
//     }

//     public function updateStatus(Request $request, $id)
//     {
//         $request->validate([
//             'status' => 'required|in:pending,active,completed,cancelled'
//         ]);

//         $internship = Internship::findOrFail($id);
//         $internship->update(['status' => $request->status]);

//         return redirect()->back()->with('success', 'Status PKL berhasil diperbarui.');
//     }

//     /**
//      * FITUR BARU: Admin Menentukan/Menyetujui Pembimbing
//      */
//     public function assignAdvisor(Request $request, $id)
//     {
//         $request->validate([
//             'advisor_id' => 'required|exists:teachers,id'
//         ]);

//         $internship = Internship::findOrFail($id);
        
//         $internship->update([
//             'advisor_id' => $request->advisor_id,
//             'advisor_status' => 'approved' // Langsung Approved karena Admin yang set
//         ]);

//         return redirect()->back()->with('success', 'Guru Pembimbing berhasil ditetapkan.');
//     }

//     public function destroy($id)
//     {
//         Internship::findOrFail($id)->delete();
//         return redirect()->back()->with('success', 'Data penempatan PKL dihapus.');
//     }
// }