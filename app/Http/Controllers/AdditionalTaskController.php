<?php

namespace App\Http\Controllers;

use App\Models\AdditionalTask;
use Illuminate\Http\Request;

class AdditionalTaskController extends Controller
{
    public function index()
    {
        $tasks = AdditionalTask::orderBy('type', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.additional_tasks.index', compact('tasks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'hours' => 'required|integer|min:0',
            'type'  => 'required|in:TTU,TTE',
        ]);

        AdditionalTask::create($request->all());

        return redirect()->back()->with('success', 'Master tugas tambahan berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'hours' => 'required|integer|min:0',
            'type'  => 'required|in:TTU,TTE',
        ]);

        $task = AdditionalTask::findOrFail($id);
        $task->update($request->all());

        return redirect()->back()->with('success', 'Master tugas tambahan berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $task = AdditionalTask::findOrFail($id);
        
        // Lepas relasi di guru sebelum dihapus jika ada
        $task->teachers()->update(['additional_task_id' => null]);
        $task->delete();

        return redirect()->back()->with('success', 'Master tugas tambahan berhasil dihapus!');
    }
}