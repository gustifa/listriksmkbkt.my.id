<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Room;
use App\Models\TimeSlot;
use App\Models\AcademicYear;
use App\Rules\ActiveAcademicYearExists;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    private function getTeacher()
    {
        return Teacher::where('user_id', Auth::id())->first();
    }

    public function index()
    {
        $teacher = $this->getTeacher();

        if (!$teacher) {
            return redirect()->back()->with('error', 'Akun Anda tidak terdaftar sebagai Guru.');
        }

        $schedules = Schedule::with(['classroom', 'subject', 'room'])
            ->withCount(['attendances' => function ($query) {
                $query->whereDate('created_at', Carbon::today());
            }])
            ->where('teacher_id', $teacher->id)
            ->get();

        $daysOrder = [
            'Senin' => 1, 'Selasa' => 2, 'Rabu' => 3,
            'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7
        ];

        $schedules = $schedules->sortBy(function ($schedule) use ($daysOrder) {
            return ($daysOrder[$schedule->day] ?? 99) * 10000 + (int)str_replace(':', '', $schedule->start_time);
        });

        $timeSlots = TimeSlot::orderBy('sort_order', 'asc')->orderBy('start_time', 'asc')->get();

        return view('guru.schedule.index', compact('schedules', 'timeSlots'));
    }

    public function create()
    {
        $teacher = $this->getTeacher();
        if (!$teacher) abort(403, 'Akses khusus Guru');

        $assignments = TeachingAssignment::with(['classroom', 'subject'])
                        ->where('teacher_id', $teacher->id)
                        ->get();

        $classrooms = $assignments->pluck('classroom')->unique('id')->sortBy('name');
        $rooms = Room::orderBy('name')->get();
        
        // Tambahkan pengambil data TimeSlot
        $timeSlots = TimeSlot::orderBy('sort_order', 'asc')->orderBy('start_time', 'asc')->get();

        return view('guru.schedule.create', compact('classrooms', 'assignments', 'rooms', 'timeSlots'));
    }

    public function edit($id)
    {
        $teacher = $this->getTeacher();

        $schedule = Schedule::where('id', $id)
                    ->where('teacher_id', $teacher->id)
                    ->firstOrFail();

        $assignments = TeachingAssignment::with(['classroom', 'subject'])
                        ->where('teacher_id', $teacher->id)
                        ->get();

        $classrooms = $assignments->pluck('classroom')->unique('id')->sortBy('name');
        $rooms = Room::orderBy('name')->get();
        
        // Tambahkan pengambil data TimeSlot
        $timeSlots = TimeSlot::orderBy('sort_order', 'asc')->orderBy('start_time', 'asc')->get();

        return view('guru.schedule.edit', compact('schedule', 'classrooms', 'assignments', 'rooms', 'timeSlots'));
    }

    public function store(Request $request)
    {
        // Ekstrak start_time dan end_time jika mengirim time_slot_id
        if ($request->filled('time_slot_id')) {
            $slot = TimeSlot::find($request->time_slot_id);
            if ($slot) {
                $request->merge([
                    'start_time' => Carbon::parse($slot->start_time)->format('H:i'),
                    'end_time'   => Carbon::parse($slot->end_time)->format('H:i'),
                ]);
            }
        }

        $request->validate([
            'classroom_id' => ['required', 'exists:classrooms,id', new ActiveAcademicYearExists],
            'subject_id'   => 'required|exists:subjects,id',
            'room_id'      => 'nullable|exists:rooms,id',
            'day'          => 'required',
            'start_time'   => 'required',
            'end_time'     => 'required|after:start_time',
        ]);

        $teacher = $this->getTeacher();

        $isValidMapping = TeachingAssignment::where('teacher_id', $teacher->id)
                    ->where('classroom_id', $request->classroom_id)
                    ->where('subject_id', $request->subject_id)
                    ->exists();

        if (!$isValidMapping) {
            return back()->withErrors(['subject_id' => 'Anda tidak terdaftar mengajar mapel ini di kelas tersebut.'])->withInput();
        }

        if ($this->checkConflict($teacher->id, $request)) {
            return back()->withErrors(['start_time' => 'Jadwal bentrok dengan agenda lain!'])->withInput();
        }

        Schedule::create([
            'teacher_id'   => $teacher->id,
            'classroom_id' => $request->classroom_id,
            'subject_id'   => $request->subject_id,
            'room_id'      => $request->room_id,
            'time_slot_id' => $request->time_slot_id ?? null,
            'day'          => $request->day,
            'start_time'   => $request->start_time,
            'end_time'     => $request->end_time,
        ]);

        return redirect()->route('schedule.index')->with('success', 'Jadwal Berhasil Dibuat!');
    }

    // public function edit($id)
    // {
    //     $teacher = $this->getTeacher();

    //     $schedule = Schedule::where('id', $id)
    //                 ->where('teacher_id', $teacher->id)
    //                 ->firstOrFail();

    //     $assignments = TeachingAssignment::with(['classroom', 'subject'])
    //                     ->where('teacher_id', $teacher->id)
    //                     ->get();

    //     $classrooms = $assignments->pluck('classroom')->unique('id')->sortBy('name');
    //     $rooms = Room::orderBy('name')->get();

    //     return view('guru.schedule.edit', compact('schedule', 'classrooms', 'assignments', 'rooms'));
    // }

    public function update(Request $request, $id)
    {
        if ($request->filled('time_slot_id')) {
            $slot = TimeSlot::find($request->time_slot_id);
            if ($slot) {
                $request->merge([
                    'start_time' => Carbon::parse($slot->start_time)->format('H:i'),
                    'end_time'   => Carbon::parse($slot->end_time)->format('H:i'),
                ]);
            }
        }

        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id'   => 'required|exists:subjects,id',
            'room_id'      => 'nullable|exists:rooms,id',
            'day'          => 'required',
            'start_time'   => 'required',
            'end_time'     => 'required|after:start_time',
        ]);

        $teacher = $this->getTeacher();
        $schedule = Schedule::where('id', $id)->where('teacher_id', $teacher->id)->firstOrFail();

        $isValidMapping = TeachingAssignment::where('teacher_id', $teacher->id)
                    ->where('classroom_id', $request->classroom_id)
                    ->where('subject_id', $request->subject_id)
                    ->exists();

        if (!$isValidMapping) {
            return back()->withErrors(['subject_id' => 'Mapping tidak valid.'])->withInput();
        }

        if ($this->checkConflict($teacher->id, $request, $id)) {
            return back()->withErrors(['start_time' => 'Jadwal bentrok dengan jadwal lain!'])->withInput();
        }

        $schedule->update([
            'classroom_id' => $request->classroom_id,
            'subject_id'   => $request->subject_id,
            'room_id'      => $request->room_id,
            'time_slot_id' => $request->time_slot_id ?? null,
            'day'          => $request->day,
            'start_time'   => $request->start_time,
            'end_time'     => $request->end_time,
        ]);

        return redirect()->route('schedule.index')->with('success', 'Jadwal Berhasil Diperbarui!');
    }

    public function show($id)
    {
        $teacher = $this->getTeacher();

        $schedule = Schedule::with(['classroom', 'subject', 'room'])
                    ->where('id', $id)
                    ->where('teacher_id', $teacher->id)
                    ->firstOrFail();

        $attendances = Attendance::with('student')
                        ->where('schedule_id', $id)
                        ->whereDate('created_at', Carbon::today())
                        ->latest()
                        ->get();

        return view('guru.schedule.show', compact('schedule', 'attendances'));
    }

    public function destroy($id)
    {
        $teacher = $this->getTeacher();

        $schedule = Schedule::where('id', $id)
                    ->where('teacher_id', $teacher->id)
                    ->firstOrFail();

        $schedule->delete();

        return redirect()->route('schedule.index')->with('success', 'Jadwal Berhasil Dihapus!');
    }

    // --- METHOD ADMIN ---

    public function allSchedules(Request $request)
    {
        $query = Schedule::with(['teacher', 'classroom', 'subject', 'room']);

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        $schedules = $query->get();
        $teachers = Teacher::orderBy('name')->get();
        $allAssignments = TeachingAssignment::with(['classroom', 'subject'])->get();
        $rooms = Room::orderBy('name')->get();
        $timeSlots = TimeSlot::orderBy('sort_order', 'asc')->orderBy('start_time', 'asc')->get();

        return view('guru.schedule.all', compact('schedules', 'teachers', 'allAssignments', 'rooms', 'timeSlots'));
    }

    public function storeAsAdmin(Request $request)
    {
        if ($request->filled('time_slot_id')) {
            $slot = TimeSlot::find($request->time_slot_id);
            if ($slot) {
                $request->merge([
                    'start_time' => Carbon::parse($slot->start_time)->format('H:i'),
                    'end_time'   => Carbon::parse($slot->end_time)->format('H:i'),
                ]);
            }
        }

        $request->validate([
            'teacher_id'   => 'required|exists:teachers,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id'   => 'required|exists:subjects,id',
            'room_id'      => 'nullable|exists:rooms,id',
            'day'          => 'required',
            'start_time'   => 'required',
            'end_time'     => 'required|after:start_time',
        ]);

        if ($this->checkConflict($request->teacher_id, $request)) {
             return back()->withErrors(['start_time' => 'Jadwal bentrok! Guru, Kelas, atau Ruangan sedang digunakan.'])->withInput();
        }

        Schedule::create($request->all());

        return redirect()->back()->with('success', 'Jadwal berhasil ditambahkan oleh Admin!');
    }

    public function updateAsAdmin(Request $request, $id)
    {
        if ($request->filled('time_slot_id')) {
            $slot = TimeSlot::find($request->time_slot_id);
            if ($slot) {
                $request->merge([
                    'start_time' => Carbon::parse($slot->start_time)->format('H:i'),
                    'end_time'   => Carbon::parse($slot->end_time)->format('H:i'),
                ]);
            }
        }

        $request->validate([
            'teacher_id'   => 'required|exists:teachers,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id'   => 'required|exists:subjects,id',
            'room_id'      => 'nullable|exists:rooms,id',
            'day'          => 'required',
            'start_time'   => 'required',
            'end_time'     => 'required|after:start_time',
        ]);

        $schedule = Schedule::where('id', $id)->firstOrFail();

        if ($this->checkConflict($request->teacher_id, $request, $id)) {
            return back()->withErrors(['start_time' => 'Jadwal bentrok! Guru, Kelas, atau Ruangan sedang digunakan.'])->withInput();
        }

        $schedule->update($request->all());

        return redirect()->back()->with('success', 'Jadwal berhasil diperbarui oleh Admin!');
    }

    public function destroyAsAdmin($id)
    {
        $schedule = Schedule::where('id', $id)->firstOrFail();
        $schedule->delete();

        return redirect()->back()->with('success', 'Jadwal berhasil dihapus oleh Admin!');
    }

    private function checkConflict($teacherId, $request, $ignoreId = null) 
    {
        $queryTeacher = Schedule::where('teacher_id', $teacherId)
            ->where('day', $request->day)
            ->where('start_time', '<', $request->end_time)
            ->where('end_time', '>', $request->start_time);

        if ($ignoreId) $queryTeacher->where('id', '!=', $ignoreId);

        $queryClass = Schedule::where('classroom_id', $request->classroom_id)
            ->where('day', $request->day)
            ->where('start_time', '<', $request->end_time)
            ->where('end_time', '>', $request->start_time);

        if ($ignoreId) $queryClass->where('id', '!=', $ignoreId);

        $roomConflict = false;
        if ($request->filled('room_id')) {
             $queryRoom = Schedule::where('room_id', $request->room_id)
                ->where('day', $request->day)
                ->where('start_time', '<', $request->end_time)
                ->where('end_time', '>', $request->start_time);

             if ($ignoreId) $queryRoom->where('id', '!=', $ignoreId);
             $roomConflict = $queryRoom->exists();
        }

        return $queryTeacher->exists() || $queryClass->exists() || $roomConflict;
    }
}