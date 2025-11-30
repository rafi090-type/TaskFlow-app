<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Tasks - Daily Task Manager</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .task-card {
            transition: all 0.3s ease;
        }
        
        .completed-task {
            opacity: 0.7;
            background-color: #f9fafb;
        }

        .completed-task .task-title {
            text-decoration: line-through;
            color: #6b7280 !important;
        }

        .task-title {
            transition: all 0.3s ease;
        }
        .task-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .priority-high { border-left: 4px solid #EF4444; }
        .priority-medium { border-left: 4px solid #F59E0B; }
        .priority-low { border-left: 4px solid #10B981; }
        .completed-task {
            opacity: 0.75;
        }
        .completed-task .task-title {
            text-decoration: line-through;
            color: #9CA3AF;
        }
        .modal-overlay {
            transition: opacity 0.3s ease-in-out;
        }
        .modal-content {
            transform: translateY(20px);
            transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
        }
        .modal-open .modal-overlay {
            opacity: 1;
            pointer-events: auto;
        }
        .modal-open .modal-content {
            transform: translateY(0);
            opacity: 1;
        }
    </style>
</head>
<body class="h-full">
    <div class="flex h-screen bg-gray-50">
        <!-- Sidebar -->
        <div class="hidden md:flex md:flex-shrink-0">
            <div class="flex flex-col w-64 bg-gradient-to-b from-blue-600 to-blue-700">
                <div class="flex items-center justify-center h-16 px-4 bg-blue-700">
                    <span class="text-white text-xl font-bold">TaskFlow</span>
                </div>
                <div class="flex flex-col flex-grow px-4 py-6 overflow-y-auto">
                    <nav class="flex-1 space-y-2">
                        <a href="{{ route('tasks.index') }}" class="flex items-center px-4 py-3 text-white bg-blue-700 rounded-lg">
                            <i class="fas fa-tasks mr-3"></i>
                            My Tasks
                        </a>
                        <a href="{{ route('tasks.statistics') }}" class="flex items-center px-4 py-3 text-blue-100 hover:bg-blue-700 rounded-lg transition-colors">
                            <i class="fas fa-chart-pie mr-3"></i>
                            Statistics
                        </a>
                    </nav>
                </div>
                <div class="p-4 border-t border-blue-500">
                    <div class="flex items-center">
                        <img class="w-10 h-10 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=random" alt="User avatar">
                        <div class="ml-3">
                            <p class="text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                            <a href="{{ route('profile.edit') }}" class="text-xs text-blue-200 hover:text-white">View Profile</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="flex flex-col flex-1 overflow-hidden">
            <!-- Top navigation -->
            <div class="flex items-center justify-between h-16 px-6 bg-white border-b border-gray-200">
                <div class="flex items-center md:hidden">
                    <button class="text-gray-500 hover:text-gray-700 focus:outline-none" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                <div class="flex items-center space-x-4">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-600 hover:text-blue-600 text-sm font-medium">
                            <i class="fas fa-sign-out-alt mr-1"></i> Logout
                        </button>
                    </form>
                </div>
            </div>

            <!-- Mobile sidebar (hidden by default) -->
            <div id="mobileSidebar" class="md:hidden hidden bg-blue-600 text-white">
                <div class="px-4 py-3 space-y-1">
                    <a href="{{ route('tasks.index') }}" class="block px-3 py-2 rounded-md text-base font-medium bg-blue-700">My Tasks</a>
                    <a href="{{ route('tasks.statistics') }}" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700">Statistics</a>
                </div>
            </div>

            <main class="flex-1 overflow-y-auto p-6">
                <!-- Header -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">My Tasks</h1>
                        <p class="text-gray-500">Manage your daily tasks efficiently</p>
                    </div>
                    <button onclick="openAddTaskModal()" 
                            class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                        <i class="fas fa-plus mr-2"></i> Add New Task
                    </button>
                </div>

                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Tasks Card -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                                <i class="fas fa-tasks text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Tasks</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $totalTasks }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Completed Tasks Card -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                                <i class="fas fa-check-circle text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Completed</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $completedTasks }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Tasks Card -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                                <i class="fas fa-clock text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Pending</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $pendingTasks }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Completion Rate Card -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                                <i class="fas fa-chart-line text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Completion Rate</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $completionRate }}%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm">{{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Tasks List -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">My Tasks</h2>
                    </div>
                    
                    @if($tasks->count() > 0)
                        <ul class="divide-y divide-gray-200">
                            @foreach($tasks as $task)
                                <li class="task-card {{ $task->completed ? 'completed-task bg-gray-50' : 'bg-white' }} hover:bg-gray-50 transition-colors duration-150">
                                    <div class="px-6 py-4">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center flex-1">
                                                <!-- Checkbox -->
                                                <div class="flex items-center h-5 mr-3">
                                                    <input id="task-{{ $task->id }}" 
                                                           type="checkbox" 
                                                           class="h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500 task-complete"
                                                           data-task-id="{{ $task->id }}"
                                                           {{ $task->completed ? 'checked' : '' }}>
                                                </div>
                                                
                                                <!-- Task Title and Description -->
                                                <div class="flex-1 min-w-0">
                                                    <label for="task-{{ $task->id }}" class="block text-sm font-medium text-gray-900 cursor-pointer">
                                                        <span class="task-title {{ $task->completed ? 'line-through text-gray-500' : 'text-gray-900' }}">
                                                            {{ $task->title }}
                                                        </span>
                                                    </label>
                                                    @if($task->description)
                                                        <p class="text-sm text-gray-500 mt-1">{{ $task->description }}</p>
                                                    @endif
                                                    
                                                    <!-- Task Meta -->
                                                    <div class="mt-2 flex flex-wrap items-center text-xs text-gray-500 space-x-3">
                                                        @if($task->due_date)
                                                            <span class="flex items-center">
                                                                <i class="far fa-calendar-alt mr-1"></i>
                                                                {{ $task->due_date->format('M d, Y') }}
                                                            </span>
                                                        @endif
                                                        
                                                        @if($task->priority)
                                                            @php
                                                                $priorityClasses = [
                                                                    'high' => 'bg-red-100 text-red-800',
                                                                    'medium' => 'bg-yellow-100 text-yellow-800',
                                                                    'low' => 'bg-green-100 text-green-800'
                                                                ][$task->priority] ?? 'bg-gray-100 text-gray-800';
                                                            @endphp
                                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $priorityClasses }}">
                                                                {{ ucfirst($task->priority) }} Priority
                                                            </span>
                                                        @endif
                                                        
                                                        @if($task->category)
                                                            <span class="flex items-center">
                                                                <i class="fas fa-tag mr-1 text-xs"></i>
                                                                {{ $task->category }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Actions -->
                                            <div class="ml-4 flex items-center space-x-2">
                                                <button onclick="openEditModal(this)" 
                                                        data-task-id="{{ $task->id }}"
                                                        data-title="{{ $task->title }}"
                                                        data-description="{{ $task->description }}"
                                                        data-priority="{{ $task->priority }}"
                                                        data-category="{{ $task->category }}"
                                                        data-due-date="{{ $task->due_date ? $task->due_date->format('Y-m-d') : '' }}"
                                                        data-completed="{{ $task->completed ? 'true' : 'false' }}"
                                                        class="p-1.5 text-gray-400 hover:text-blue-600 transition-colors duration-150 rounded-full hover:bg-blue-50"
                                                        title="Edit">
                                                    <i class="fas fa-pencil-alt text-sm"></i>
                                                </button>
                                                
                                                <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this task?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 transition-colors duration-150 rounded-full hover:bg-red-50" title="Delete">
                                                        <i class="fas fa-trash-alt text-sm"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="px-6 py-12 text-center">
                            <div class="text-gray-400 mb-4">
                                <i class="fas fa-tasks text-4xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-1">No tasks yet</h3>
                            <p class="text-gray-500 mb-4">Get started by creating a new task</p>
                            <button onclick="openAddTaskModal()" 
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                                <i class="fas fa-plus mr-2"></i> Add Your First Task
                            </button>
                        </div>
                    @endif
                </div>
            </main>
        </div>
    </div>

    <!-- Add Task Modal -->
    <div id="addTaskModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div id="addTaskModalBackdrop" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeAddTaskModal()"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="absolute top-0 right-0 pt-4 pr-4">
                    <button type="button" onclick="closeAddTaskModal()" class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <span class="sr-only">Close</span>
                        <i class="fas fa-times h-6 w-6"></i>
                    </button>
                </div>
                
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Add New Task
                        </h3>
                        <div class="mt-4">
                            <form action="{{ route('tasks.store') }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700">Title <span class="text-red-500">*</span></label>
                                    <input type="text" name="title" id="title" required
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea id="description" name="description" rows="3"
                                              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                                </div>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                                        <select id="priority" name="priority"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="low">Low</option>
                                            <option value="medium" selected>Medium</option>
                                            <option value="high">High</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                                        <select id="category" name="category"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="">Select a category</option>
                                            <option value="work">💼 Work</option>
                                            <option value="personal">👤 Personal</option>
                                            <option value="shopping">🛒 Shopping</option>
                                            <option value="health">❤️ Health</option>
                                            <option value="education">📚 Education</option>
                                            <option value="other">🔹 Other</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
                                    <input type="date" name="due_date" id="due_date"
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                
                                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                    <button type="submit"
                                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:col-start-2 sm:text-sm">
                                        Add Task
                                    </button>
                                    <button type="button"
                                            onclick="closeAddTaskModal()"
                                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Task Modal -->
    <div id="editTaskModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="edit-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeEditModal()"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="absolute top-0 right-0 pt-4 pr-4">
                    <button type="button" onclick="closeEditModal()" class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <span class="sr-only">Close</span>
                        <i class="fas fa-times h-6 w-6"></i>
                    </button>
                </div>
                
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="edit-modal-title">
                            Edit Task
                        </h3>
                        <div class="mt-4">
                            <form id="editTaskForm" method="POST" class="space-y-4">
                                @csrf
                                @method('PUT')
                                <div>
                                    <label for="edit_title" class="block text-sm font-medium text-gray-700">Title <span class="text-red-500">*</span></label>
                                    <input type="text" name="title" id="edit_title" required
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                
                                <div>
                                    <label for="edit_description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea id="edit_description" name="description" rows="3"
                                              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                                </div>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="edit_priority" class="block text-sm font-medium text-gray-700">Priority</label>
                                        <select id="edit_priority" name="priority"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="low">Low</option>
                                            <option value="medium">Medium</option>
                                            <option value="high">High</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label for="edit_category" class="block text-sm font-medium text-gray-700">Category</label>
                                        <select id="edit_category" name="category"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="work">💼 Work</option>
                                            <option value="personal">👤 Personal</option>
                                            <option value="shopping">🛒 Shopping</option>
                                            <option value="health">❤️ Health</option>
                                            <option value="education">📚 Education</option>
                                            <option value="other">🔹 Other</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="edit_due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
                                    <input type="date" name="due_date" id="edit_due_date"
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                
                                <div class="flex items-center">
                                    <input id="edit_completed" name="completed" type="checkbox" 
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="edit_completed" class="ml-2 block text-sm text-gray-700">
                                        Mark as completed
                                    </label>
                                </div>
                                
                                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                    <button type="submit"
                                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:col-start-2 sm:text-sm">
                                        Update Task
                                    </button>
                                    <button type="button"
                                            onclick="closeEditModal()"
                                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let editTaskModal = null;
        let editTaskForm = null;
        let currentTaskId = null;
        
        // Handle task completion
        document.addEventListener('change', async function(e) {
            if (e.target.matches('.task-complete')) {
                const checkbox = e.target;
                const taskId = checkbox.dataset.taskId;
                const isCompleted = checkbox.checked;
                const taskItem = checkbox.closest('.task-card');
                
                // Show loading state
                checkbox.disabled = true;
                
                try {
                    const response = await fetch(`/tasks/${taskId}/toggle`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            completed: isCompleted
                        })
                    });

                    const data = await response.json();
                    
                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Gagal memperbarui status tugas');
                    }

                    // Update UI
                    taskItem.classList.toggle('completed-task', data.completed);
                    taskItem.classList.toggle('bg-gray-50', data.completed);
                    
                    const taskTitle = taskItem.querySelector('.task-title');
                    if (taskTitle) {
                        taskTitle.classList.toggle('line-through', data.completed);
                        taskTitle.classList.toggle('text-gray-500', data.completed);
                        taskTitle.classList.toggle('text-gray-900', !data.completed);
                    }
                    
                    // Show success message
                    showToast('Status tugas berhasil diperbarui', 'success');
                    
                    // Update statistics
                    if (typeof updateTaskStatistics === 'function') {
                        updateTaskStatistics();
                    }
                    
                } catch (error) {
                    console.error('Error:', error);
                    // Revert checkbox state on error
                    checkbox.checked = !isCompleted;
                    showToast(error.message || 'Gagal memperbarui status tugas', 'error');
                } finally {
                    // Re-enable checkbox
                    checkbox.disabled = false;
                }
            }
        });
        
        // Function to open add task modal
        function openAddTaskModal() {
            const modal = document.getElementById('addTaskModal');
            if (modal) {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
                // Focus on the first input field when modal opens
                const firstInput = modal.querySelector('input, textarea, select');
                if (firstInput) firstInput.focus();
            }
        }
        
        // Function to close add task modal
        function closeAddTaskModal() {
            const modal = document.getElementById('addTaskModal');
            if (modal) {
                modal.classList.add('opacity-0');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    modal.classList.remove('opacity-0');
                    document.body.classList.remove('overflow-hidden');
                }, 300);
            }
        }
        
        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.id === 'addTaskModalBackdrop') {
                closeAddTaskModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAddTaskModal();
                closeEditModal();
            }
        });

        // Initialize modal when the page loads
        function initEditModal() {
            // Get elements
            editTaskModal = document.getElementById('editTaskModal');
            editTaskForm = document.getElementById('editTaskForm');
            
            if (!editTaskModal || !editTaskForm) {
                console.error('Modal elements not found');
                return;
            }
            
            // Initialize any event listeners for the edit modal here
            editTaskModal.addEventListener('click', function(e) {
                if (e.target === editTaskModal) {
                    closeEditModal();
                }
            });

            // Close modal when clicking outside
            editTaskModal.addEventListener('click', function(e) {
                if (e.target === editTaskModal) {
                    closeEditModal();
                }
            });

            // Toggle task completion
            document.addEventListener('change', async function(e) {
                if (!e.target.matches('.task-complete')) return;
                
                const checkbox = e.target;
                const taskId = checkbox.dataset.taskId;
                const isCompleted = checkbox.checked;
                const taskItem = checkbox.closest('.border-b');
                
                // Find the title element more reliably
                const taskTitle = taskItem ? taskItem.querySelector('.text-lg.font-medium') : null;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                
                if (!taskId || !taskItem || !csrfToken) {
                    console.error('Required elements not found');
                    checkbox.checked = !isCompleted; // Revert checkbox state
                    return;
                }

                // Show loading state
                checkbox.disabled = true;
                
                try {
                    // Convert boolean to string to ensure proper form data handling
                    const formData = new FormData();
                    formData.append('_method', 'PATCH');
                    formData.append('completed', isCompleted.toString());
                    
                    const response = await fetch(`/tasks/${taskId}/toggle`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();
                    
                    if (!response.ok) {
                        throw new Error(data.message || 'Gagal memperbarui status tugas');
                    }

                    // Toggle the completed class on the task item
                    taskItem.classList.toggle('opacity-75', isCompleted);
                    
                    // Only try to update title if it exists
                    if (taskTitle) {
                        taskTitle.classList.toggle('line-through', isCompleted);
                        taskTitle.classList.toggle('text-gray-400', isCompleted);
                        taskTitle.classList.toggle('text-gray-800', !isCompleted);
                    }

                    showToast(
                        isCompleted ? 'Tugas berhasil ditandai selesai!' : 'Tugas ditandai belum selesai',
                        'success'
                    );

                    // Update statistics
                    updateTaskStatistics();

                } catch (error) {
                    console.error('Error:', error);
                    checkbox.checked = !isCompleted; // Revert checkbox if error
                    showToast(
                        error.message || 'Gagal memperbarui status tugas', 
                        'error'
                    );
                } finally {
                    checkbox.disabled = false;
                }
            });

            // Handle click events for edit buttons and modals
            document.addEventListener('click', function(e) {
                // Handle edit button click
                const editButton = e.target.closest('.edit-task');
                if (editButton) {
                    e.preventDefault();
                    openEditModal(editButton);
                    return;
                }

                // Close modal when clicking outside or cancel button
                if (e.target.matches('.cancel-edit, #editTaskModal')) {
                    closeEditModal();
                }
            });

            // Handle edit form submission
            editTaskForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                if (!currentTaskId) return;
                
                const formData = new FormData(this);
                const submitButton = this.querySelector('button[type="submit"]');
                const originalButtonText = submitButton.innerHTML;
                
                // Show loading state
                submitButton.disabled = true;
                submitButton.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Menyimpan...';
                
                try {
                    // Add _method for Laravel's form method spoofing
                    formData.append('_method', 'PUT');
                    
                    const response = await fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (!response.ok) {
                        throw new Error(data.message || 'Gagal memperbarui tugas');
                    }
                    
                    if (data.success) {
                        // Close modal and show success message
                        closeEditModal();
                        showToast('Tugas berhasil diperbarui', 'success');
                        
                        // Refresh the page to show updated data
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        throw new Error(data.message || 'Gagal memperbarui tugas');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast(error.message || 'Terjadi kesalahan saat memperbarui tugas', 'error');
                } finally {
                    // Reset button state
                    submitButton.disabled = false;
                    submitButton.innerHTML = 'Perbarui Tugas';
                }
            });

            // Handle delete button clicks
            document.addEventListener('click', function(e) {
                if (e.target.matches('.delete-task') || e.target.closest('.delete-task')) {
                    e.preventDefault();
                    const button = e.target.matches('.delete-task') ? e.target : e.target.closest('.delete-task');
                    const taskId = button.dataset.taskId;
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                    
                    if (confirm('Apakah Anda yakin ingin menghapus tugas ini?')) {
                        fetch(`/tasks/${taskId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-HTTP-Method-Override': 'DELETE'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Reload the page to see changes
                                window.location.reload();
                            } else {
                                alert('Gagal menghapus tugas. ' + (data.message || ''));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan saat menghapus tugas. Silakan coba lagi.');
                        });
                    }
                }
            });
        }

        // Function to open edit modal
        function openEditModal(button) {
            // Initialize modal elements if not already done
            if (!editTaskModal || !editTaskForm) {
                editTaskModal = document.getElementById('editTaskModal');
                editTaskForm = document.getElementById('editTaskForm');
                
                if (!editTaskModal || !editTaskForm) {
                    console.error('Edit modal elements not found');
                    return;
                }
            }
    
            // Get task data from button data attributes
            const taskId = button.getAttribute('data-task-id');
            const title = button.getAttribute('data-title');
            const description = button.getAttribute('data-description') || '';
            const priority = button.getAttribute('data-priority') || 'medium';
            const category = button.getAttribute('data-category') || 'other';
            const dueDate = button.getAttribute('data-due-date') || '';
            const completed = button.getAttribute('data-completed') === 'true';
    
            // Set form action with task ID
            editTaskForm.action = `/tasks/${taskId}`;
    
            // Set form values
            const titleInput = editTaskForm.querySelector('input[name="title"]');
            const descriptionInput = editTaskForm.querySelector('textarea[name="description"]');
            const prioritySelect = editTaskForm.querySelector('select[name="priority"]');
            const categorySelect = editTaskForm.querySelector('select[name="category"]');
            const dueDateInput = editTaskForm.querySelector('input[name="due_date"]');
            const completedCheckbox = editTaskForm.querySelector('input[name="completed"]');
    
            if (titleInput) titleInput.value = title;
            if (descriptionInput) descriptionInput.value = description;
            if (prioritySelect) prioritySelect.value = priority;
            if (categorySelect) categorySelect.value = category;
            if (dueDateInput && dueDate) dueDateInput.value = dueDate;
            if (completedCheckbox) completedCheckbox.checked = completed;
    
            // Store current task ID
            currentTaskId = taskId;
    
            // Show the modal
            editTaskModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
    
            // Focus on the first input field
            if (titleInput) titleInput.focus();
    
            // Add animation class after a short delay to trigger the transition
            setTimeout(() => {
                editTaskModal.classList.add('opacity-100');
            }, 10);
        }

        // Function to close edit modal
        function closeEditModal() {
            if (!editTaskModal) return;
            
            // Remove the animation class first
            editTaskModal.classList.remove('opacity-100');
            
            // Then hide the modal after the animation completes
            setTimeout(() => {
                editTaskModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
                currentTaskId = null;
                
                // Reset form
                if (editTaskForm) {
                    editTaskForm.reset();
                }
            }, 300);
            if (editTaskModal) {
                editTaskModal.classList.remove('opacity-100');
                setTimeout(() => {
                    editTaskModal.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                    currentTaskId = null;
                }, 300);
            }
        }

        // Function to show toast notification
        function showToast(message, type = 'success') {
            // Create toast element if it doesn't exist
            let toast = document.getElementById('toast-notification');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'toast-notification';
                toast.className = 'fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white transition-all duration-300 transform translate-y-10 opacity-0';
                document.body.appendChild(toast);
            }

            // Set toast content and style based on type
            toast.textContent = message;
            toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white transition-all duration-300 transform ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            } translate-y-10 opacity-0`;

            // Show toast
            setTimeout(() => {
                toast.classList.remove('translate-y-10', 'opacity-0');
                toast.classList.add('translate-y-0', 'opacity-100');
                
                // Hide after 3 seconds
                setTimeout(() => {
                    toast.classList.add('opacity-0');
                    setTimeout(() => {
                        toast.classList.add('translate-y-10');
                    }, 300);
                }, 3000);
            }, 100);
        }

        // Initialize the modal when the DOM is fully loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initEditModal);
        } else {
            // DOM is already loaded
            initEditModal();
        }
    </script>
    
    <script>
        // Function to update task statistics
        async function updateTaskStatistics() {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const response = await fetch('{{ route("tasks.statistics") }}', {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) return;
                
                const data = await response.json();
                
                // Update the progress display
                const progressElement = document.querySelector('.progress-bar');
                const progressText = document.querySelector('.progress-percentage');
                const completedCount = document.getElementById('completed-count');
                const totalCount = document.getElementById('total-count');
                
                if (progressElement) progressElement.style.width = `${data.completionRate}%`;
                if (progressText) progressText.textContent = `${Math.round(data.completionRate)}%`;
                if (completedCount) completedCount.textContent = data.completedTasks;
                if (totalCount) totalCount.textContent = data.totalTasks;
                
            } catch (error) {
                console.error('Error updating statistics:', error);
            }
        }
    </script>
</body>
</html>
