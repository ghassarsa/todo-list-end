<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Subtask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SubTaskController extends Controller
{
    public function index() {
        $id = request()->query('task_id');

        if (!$id) {
            return response()->json([
                'massage' => 'Task ID is Required'
            ], 400);
        }

        $user = Auth::user();
        $task = $user->tasks()->findOrFail($id);
        if (Auth::id() !== $task->user_id) {
            return response()->json([
                'massage' => 'Unauthorized'
            ], 403);
        }

        return response()->json($task->subtasks()->get());
    }

    public function store(Request $request) {
        $id = $request->query('task_id');

        if (!$id) {
            return response()->json([
                'massage' => 'Task ID is Required'
            ], 400);
        }

        $user = Auth::user();
        $task = $user->tasks()->findOrFail($id);
        if (Auth::id() !== $task->user_id) {
            return response()->json([
                'massage' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $subtask = $task->subtasks()->create([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return response()->json($subtask, 201);
    }

    public function update(Request $request, string $id) {
        $subtask = Subtask::findOrFail($id);
        $task = $subtask->task;
        if (AuTH::id() !== $task->user_id) {
            return response()->json([
                'massage' => 'Unauthorized or Invalid Resource'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $subtask->update($request->only(['title', 'description']));
        return response()->json($subtask);
    }

    public function destroy(string $id) {
    $subtask = Subtask::findOrFail($id);
    $task = $subtask->task;

    if (Auth::id() !== $task->user_id || $subtask->task_id !== $task->id) {
        return response()->json([
            'massage' => 'Unauthorized or Invalid Resource' 
        ], 403);
    }
            
        $subtask->delete();
        return response()->json([
            'massage' => 'Subtask Deleted Successfully'
        ]);
    }

    public function changeStatus(Request $request) {
        $id = $request->query('subtask_id');
        $subtask = Subtask::findOrFail($id);
        $task = $subtask->task;

        if (Auth::id() !== $task->user_id) {
            return response()->json([
                'massage' => 'Unauthorized or Invalid Resource'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $subtask->status = $request->status;
        $subtask->save();

        return response()->json($subtask);
    }
}
