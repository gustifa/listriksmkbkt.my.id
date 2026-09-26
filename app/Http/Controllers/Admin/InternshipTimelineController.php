<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InternshipTimeline;

class InternshipTimelineController extends Controller
{
    public function index()
    {
        $timelines = InternshipTimeline::orderBy('start_date', 'asc')->get();
        return view('admin.internships.timeline', compact('timelines'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        InternshipTimeline::create($request->all());

        return redirect()->back()->with('success', 'Agenda kegiatan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $timeline = InternshipTimeline::findOrFail($id);
        $timeline->update($request->all());

        return redirect()->back()->with('success', 'Agenda kegiatan diperbarui.');
    }

    public function destroy($id)
    {
        InternshipTimeline::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Agenda dihapus.');
    }
}
