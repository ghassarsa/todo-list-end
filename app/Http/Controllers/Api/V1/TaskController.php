<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index() {
        $user = Auth::user();

        $tasks = $user->tasks()->get();
        return response()->json($tasks);
    }

    public function store(Request $request) {
        $user = Auth::user();
        $plan = $user-> plan;

        if ($plan && $plan->task_limit > 0 && $user->tasks()->count() >= $plan->task_limit) {
            return response()->json([
                'massage' => 'You have reached the maximum number of tasks allowed for your plan.'
            ], 429);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video' => 'nullable|string',
            'image' => 'nullable|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $task = $user->tasks()->create([
            'title' => $request->title,
            'description' => $request->description,
            'video' => $request->video ?? null,
        ]);

        $image = $request->file('image');
        if ($image) {
            $imagePath = $user->email . '/tasks/' . $task->title;
            Storage::disk('public')->put($imagePath, $image->getContent());
            $imagePath = Storage::url($imagePath);
            $task->image = $imagePath;
            $task->save();
        }

        $data = $task;
        $data['image'] = $task->image == null ? null : asset($task->image);
        return response()->json($data, 201);
    }

    public function show(string $id) {
        $user = Auth::user();
        $task = $user->tasks()->findOrFail($id);
        if (Auth::id() !== $task->user_id) {
            return response()->json([
                'massage' => 'Unauthorized'
            ], 403);
        }

        $task->load('subtasks');
        return response()->json($task);
    }

    public function update(Request $request, string $id) {
        $user = Auth::user();
        $task = $user->tasks()->findOrFail($id);
        if (Auth::id() !== $task->user_id) {
            return response()->json([
                'massage' => 'Unauthorized',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'video' => 'sometimes|nullable|string',
            'image' => 'sometimes|nullable|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $task->update($request->only(['title', 'description', 'video']));
        $image = $request->file('image');

        if ($image) {
            $imagePath = $user->email . '/tasks/' . $task->title;
            Storage::disk('public')->put($imagePath, $image->getContent());
            $imagePath = Storage::url($imagePath);
            $task->image = $imagePath;
            $task->save();
        }

        $data = $task;
        $data['image'] = $task->image == null ? null : asset($task->image);
        return response()->json($data);
    }

    public function destroy(string $id) {
        $user = Auth::user();
        $task = $user->tasks()->findOrFail($id);
        if (Auth::id() !== $task->user_id) {
            return response()->json([
                'massage' => 'Unauthorized'
            ], 403);
        }

        $task->delete();
        return response()->json(['massage' => 'Task Deleted Successfully']);
    }

    public function validation($id) {
        $task = Task::findOrFail($id);
        $task->completed = 'yes';
        $saved = $task->save();

        if (!$saved) {
            return response()->json(['message' => 'Gagal untuk mengubah status dari tugas anda']);
        }

        return response()->json([
            'message' => 'Task berhasil diselesaikan',
            'task' => $task
        ]);
    }

    public function restoreTask($id) {
        $task = Task::findOrFail($id);
        $task->completed = 'no';
        $saved = $task->save();

        if (!$saved) {
            return response()->json(['message' => 'Gagal untuk mengubah status dari tugas anda']);
        }

        return response()->json([
            'message' => 'Task berhasil diselesaikan',
            'task' => $task
        ]);
    }
}
