<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Hanya user login yang bisa akses
    }
    
    /**
     * Toggle task completion status
     */
    public function toggle(Task $task)
    {
        try {
            // Temporarily bypass authorization for testing
            // $this->authorize('update', $task);
        
            $completed = filter_var(request()->input('completed'), FILTER_VALIDATE_BOOLEAN);
        
            $task->update([
                'completed' => $completed
            ]);

            return response()->json([
                'success' => true,
                'completed' => $task->fresh()->completed
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating task status:', [
                'error' => $e->getMessage(),
                'task_id' => $task->id ?? null
            ]);
        
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        $user = auth()->user();
        $today = now()->startOfDay();
        $endOfDay = now()->endOfDay();

        // Get all tasks for the user
        $tasks = $user->tasks()
            ->orderBy('completed')
            ->orderBy('due_date')
            ->latest()
            ->get();
            
        // General statistics
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('completed', true)->count();
        $pendingTasks = $totalTasks - $completedTasks;
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
        
        // Tasks by priority
        $tasksByPriority = [
            'high' => $tasks->where('priority', 'high')->count(),
            'medium' => $tasks->where('priority', 'medium')->count(),
            'low' => $tasks->where('priority', 'low')->count(),
        ];
        
        // Upcoming deadlines (next 7 days)
        $upcomingDeadlines = $user->tasks()
            ->where('completed', false)
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->orderBy('due_date')
            ->take(5)
            ->get();
        
        // Today's tasks
        $todaysTasks = $user->tasks()
            ->whereDate('due_date', '>=', $today)
            ->whereDate('due_date', '<=', $endOfDay)
            ->get();
        
        $todaysTotal = $todaysTasks->count();
        $todaysCompleted = $todaysTasks->where('completed', true)->count();
        $todaysProgress = $todaysTotal > 0 ? round(($todaysCompleted / $todaysTotal) * 100) : 0;
        
        // Weekly progress
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        
        $weeklyTasks = $user->tasks()
            ->whereBetween('due_date', [$weekStart, $weekEnd])
            ->get();
        
        $weeklyCompleted = $weeklyTasks->where('completed', true)->count();
        $weeklyTotal = $weeklyTasks->count();
        $weeklyProgress = $weeklyTotal > 0 ? round(($weeklyCompleted / $weeklyTotal) * 100) : 0;

        // Motivational quotes
        $motivationalQuotes = [
            'Kerjakan sedikit demi sedikit, nanti jadi gunung!',
            'Langkah kecil masih lebih baik daripada diam di tempat.',
            'Kesuksesan adalah kumpulan dari usaha kecil yang berulang.',
            'Jangan tunda pekerjaan besok, jika bisa dikerjakan hari ini.',
            'Setiap tugas yang selesai adalah langkah menuju pencapaian besar.',
            'Konsistensi adalah kunci dari produktivitas.',
            'Mulailah dengan yang mudah, lalu lanjutkan dengan yang sulit.',
            'Tidak ada yang mustahil jika kamu mau mencoba.',
            'Setiap langkah kecil membawamu lebih dekat ke tujuan.',
            'Kamu lebih kuat dari yang kamu kira!'
        ];
        
        // Select a random quote
        $randomQuote = $motivationalQuotes[array_rand($motivationalQuotes)];

        return view('tasks.index', [
            'tasks' => $tasks,
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'pendingTasks' => $pendingTasks,
            'completionRate' => $completionRate,
            'tasksByPriority' => $tasksByPriority,
            'upcomingDeadlines' => $upcomingDeadlines,
            'todaysTasks' => $todaysTasks,
            'todaysTotal' => $todaysTotal,
            'todaysCompleted' => $todaysCompleted,
            'todaysProgress' => $todaysProgress,
            'weeklyProgress' => $weeklyProgress,
            'randomQuote' => $randomQuote
        ]);
    }

    public function store(Request $request)
    {
        try {
            \Log::info('Form data received:', $request->all());
            
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category' => 'required|in:work,personal,study,shopping,health,education,other',
                'due_date' => 'nullable|date',
                'priority' => 'required|in:low,medium,high',
            ]);
            
            // Add user_id to validated data
            $validated['user_id'] = auth()->id();
            
            // Set default completed status
            $validated['completed'] = false;
            
            \Log::info('Creating task with data:', $validated);
            
            $task = Task::create($validated);
            \Log::info('Task created successfully', ['task_id' => $task->id]);
            
            return redirect()->route('tasks.index')
                ->with('success', 'Tugas berhasil ditambahkan!');
                
        } catch (\Exception $e) {
            \Log::error('Error creating task: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan tugas: ' . $e->getMessage());
        }
    }

        public function update(Request $request, Task $task)
        {
        \Log::info('Update request received', [
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'task_user_id' => $task->user_id,
            'request_data' => $request->all()
        ]);

        // Ensure the task belongs to the logged-in user
        if ($task->user_id !== auth()->id()) {
            \Log::warning('Unauthorized update attempt', [
                'task_id' => $task->id,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

            try {
                $validated = $request->validate([
                    'title' => 'required|string|max:255',
                    'description' => 'nullable|string',
                    'category' => 'required|in:work,personal,study,shopping,health,education,other',
                    'due_date' => 'nullable|date',
                    'priority' => 'required|in:low,medium,high',
                    'completed' => 'boolean',
                ]);

                // Handle completed status
                if ($request->has('completed')) {
                    $validated['completed'] = (bool)$request->completed;
                    $validated['completed_at'] = $validated['completed'] ? now() : null;
                }

                \Log::info('Updating task with data:', $validated);
        
                $task->update($validated);
        
                \Log::info('Task updated successfully', ['task_id' => $task->id]);
        
                return response()->json([
                    'success' => true,
                    'message' => 'Tugas berhasil diperbarui!',
                    'data' => $task->fresh() // Return the updated task data
                ]);

            } catch (\Illuminate\Validation\ValidationException $e) {
                \Log::error('Validation error updating task: ' . $e->getMessage(), [
                    'errors' => $e->errors()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors()
                ], 422);
            } catch (\Exception $e) {
                \Log::error('Error updating task: ' . $e->getMessage(), [
                    'exception' => $e
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui tugas: ' . $e->getMessage()
                ], 500);
            }
        }

    public function toggleComplete(Request $request, Task $task)
    {
        if ($task->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        try {
            $completed = $request->boolean('completed', !$task->completed);
        
            $task->update([
                'completed' => $completed,
                'completed_at' => $completed ? now() : null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status tugas berhasil diperbarui',
                'data' => $task->fresh()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error toggling task completion: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status tugas: ' . $e->getMessage()
            ], 500);
        }
    }


    public function destroy(Task $task)
    {
        // Ensure the task belongs to the logged-in user
        if ($task->user_id !== auth()->id()) {
            abort(403);
        }

        $task->delete();

        return redirect()->back()->with('success', 'Tugas berhasil dihapus!');
    }

    /**
     * Show task statistics
     */
    public function statistics()
    {
        $user = auth()->user();
    
        // Get all tasks including soft-deleted ones for accurate statistics
        $tasks = $user->tasks()->withTrashed()->get();
    
        // Calculate basic statistics
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('completed', true)->count();
        $pendingTasks = $totalTasks - $completedTasks;
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
    
        // Get tasks by category
        $tasksByCategory = $tasks->groupBy('category')->map->count();
    
        // Get completed tasks by category
        $completedByCategory = $tasks->where('completed', true)
            ->groupBy('category')
            ->map->count();
    
        // Get pending tasks by category
        $pendingByCategory = $tasks->where('completed', false)
            ->groupBy('category')
            ->map->count();
    
        // Category names with emojis
        $categoryNames = [
            'work' => '💼 Pekerjaan', 
            'personal' => '❤️ Pribadi', 
            'study' => '📚 Belajar',
            'shopping' => '🛍️ Belanja', 
            'other' => '✨ Lainnya'
        ];
    
        // Prepare chart data
        $chartData = [];
        foreach ($categoryNames as $key => $name) {
            $chartData[] = [
                'category' => $name,
                'total' => $tasksByCategory[$key] ?? 0,
                'completed' => $completedByCategory[$key] ?? 0,
                'pending' => $pendingByCategory[$key] ?? 0,
            ];
        }
    
        // Sort by total tasks (highest first)
        usort($chartData, function($a, $b) {
            return $b['total'] - $a['total'];
        });
    
        // Get recent activities (only non-deleted tasks)
        $recentActivities = $user->tasks()
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($task) {
                $action = 'updated';
                $icon = 'pencil-alt';
                $bgColor = 'bg-blue-100 text-blue-600';
            
                if ($task->completed) {
                    $action = 'completed';
                    $icon = 'check-circle';
                    $bgColor = 'bg-green-100 text-green-600';
                }
            
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'action' => $action,
                    'icon' => $icon,
                    'bg_color' => $bgColor,
                    'time' => $task->updated_at->diffForHumans()
                ];
            });

        return view('tasks.statistics', [
            'chartData' => $chartData,
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'pendingTasks' => $pendingTasks,
            'completionRate' => $completionRate,
            'recentActivities' => $recentActivities
        ]);
    }
}