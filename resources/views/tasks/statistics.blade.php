<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Task Statistics - Daily Task Manager</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    
    <!-- Charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Styles -->
    @vite(['resources/css/app.css'])
    
    <style>
        .animate-fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .progress-bar {
            transition: width 1s ease-in-out;
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
                        <a href="{{ route('tasks.index') }}" class="flex items-center px-4 py-3 text-blue-100 hover:bg-blue-700 rounded-lg transition-colors">
                            <i class="fas fa-tasks mr-3"></i>
                            My Tasks
                        </a>
                        <a href="{{ route('tasks.statistics') }}" class="flex items-center px-4 py-3 text-white bg-blue-700 rounded-lg">
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
                    <button class="text-gray-500 hover:text-gray-700 focus:outline-none">
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

            <!-- Page content -->
            <div class="flex-1 overflow-y-auto p-6">
                <div class="max-w-6xl mx-auto">
                    <!-- Header -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">Task Statistics</h1>
                            <p class="text-gray-600">Insights and analytics about your tasks</p>
                        </div>
                        <a href="{{ route('tasks.index') }}" class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Tasks
                        </a>
                    </div>

                    <!-- Stats Overview -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                            <div class="flex items-center">
                                <div class="p-3 rounded-lg bg-blue-50 text-blue-600">
                                    <i class="fas fa-tasks text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Total Tasks</p>
                                    <p class="text-2xl font-semibold text-gray-800">{{ $totalTasks }}</p>
                                </div>
                                <div class="mt-4">
                                    <div class="flex justify-between text-sm mb-1">
                                        <span>Completion</span>
                                        <span>{{ $completionRate }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $completionRate }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                        <!-- Category Distribution -->
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Task Distribution by Category</h2>
                            <div class="h-80">
                                <canvas id="categoryChart"></canvas>
                            </div>
                        </div>

                        <!-- Completion Rate -->
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Completion Status</h2>
                            <div class="h-80">
                                <canvas id="completionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Category Distribution Chart
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            const categoryData = {
                labels: {!! json_encode(array_map(function($item) { return $item['category']; }, $chartData)) !!},
                datasets: [{
                    data: {!! json_encode(array_map(function($item) { return $item['total']; }, $chartData)) !!},
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',  // blue
                        'rgba(236, 72, 153, 0.8)',  // pink
                        'rgba(16, 185, 129, 0.8)',  // green
                        'rgba(139, 92, 246, 0.8)',  // purple
                        'rgba(234, 179, 8, 0.8)'    // yellow
                    ],
                    borderColor: [
                        'rgba(59, 130, 246, 1)',
                        'rgba(236, 72, 153, 1)',
                        'rgba(16, 185, 129, 1)',
                        'rgba(139, 92, 246, 1)',
                        'rgba(234, 179, 8, 1)'
                    ],
                    borderWidth: 2
                }]
            };

            // Completion Status Chart
            const completionCtx = document.getElementById('completionChart').getContext('2d');
            const completionData = {
                labels: ['Completed', 'Pending'],
                datasets: [{
                    data: [{{ $completedTasks }}, {{ $pendingTasks }}],
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.8)',  // green
                        'rgba(239, 68, 68, 0.8)'    // red
                    ],
                    borderColor: [
                        'rgba(16, 185, 129, 1)',
                        'rgba(239, 68, 68, 1)'
                    ],
                    borderWidth: 2
                }]
            };

            // Common chart options
            const chartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100) || 0;
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            };

            // Initialize Category Chart
            new Chart(categoryCtx, {
                type: 'doughnut',
                data: categoryData,
                options: {
                    ...chartOptions,
                    plugins: {
                        ...chartOptions.plugins,
                        title: {
                            display: true,
                            text: 'Tasks by Category',
                            font: {
                                size: 16,
                                weight: 'bold'
                            }
                        }
                    }
                }
            });

            // Initialize Completion Chart
            new Chart(completionCtx, {
                type: 'doughnut',
                data: completionData,
                options: {
                    ...chartOptions,
                    plugins: {
                        ...chartOptions.plugins,
                        title: {
                            display: true,
                            text: 'Task Completion',
                            font: {
                                size: 16,
                                weight: 'bold'
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
