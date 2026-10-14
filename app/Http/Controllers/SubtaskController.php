<?php

namespace App\Http\Controllers;

use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Http\Request;

class SubtaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request, Task $task)
    {
        if ($task->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $subtask = $task->subtasks()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_completed' => false,
            'order' => $task->subtasks()->count(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subtask berhasil ditambahkan!',
            'data' => $subtask
        ]);
    }

    public function update(Request $request, Subtask $subtask)
    {
        if ($subtask->task->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $subtask->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Subtask berhasil diperbarui!',
            'data' => $subtask->fresh()
        ]);
    }

    public function toggle(Subtask $subtask)
    {
        if ($subtask->task->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $subtask->update([
            'is_completed' => !$subtask->is_completed
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status subtask berhasil diperbarui!',
            'data' => $subtask->fresh()
        ]);
    }

    public function destroy(Subtask $subtask)
    {
        if ($subtask->task->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $subtask->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subtask berhasil dihapus!'
        ]);
    }
}
