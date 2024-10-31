<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        return Task::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'status' => 'in:pending,in-progress,completed',
        ]);

        return Task::create($request->all());
    }

    public function show($id)
    {
        return Task::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'status' => 'in:pending,in-progress,completed',
        ]);
        $task = Task::findOrFail($id);
        $task->update($request->all());

        return $task;
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->noContent();
    }
}
