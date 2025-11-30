<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tasks = [
            [
                'title' => 'Complete Project Proposal',
                'description' => 'Draft and finalize the project proposal document',
                'priority' => 'high',
                'category' => 'work',
                'due_date' => Carbon::now()->addDays(3),
                'completed' => false,
                'user_id' => 1
            ],
            [
                'title' => 'Team Meeting',
                'description' => 'Weekly team sync-up meeting',
                'priority' => 'medium',
                'category' => 'work',
                'due_date' => Carbon::now()->addDay(),
                'completed' => false,
                'user_id' => 1
            ],
            [
                'title' => 'Code Review',
                'description' => 'Review pull requests from the development team',
                'priority' => 'high',
                'category' => 'work',
                'due_date' => Carbon::now()->addHours(4),
                'completed' => false,
                'user_id' => 1
            ],
            [
                'title' => 'Update Documentation',
                'description' => 'Update API documentation for the new endpoints',
                'priority' => 'low',
                'category' => 'education',
                'due_date' => Carbon::now()->addDays(2),
                'completed' => false,
                'user_id' => 1
            ],
            [
                'title' => 'Deploy to Staging',
                'description' => 'Deploy the latest changes to staging environment',
                'priority' => 'high',
                'category' => 'work',
                'due_date' => Carbon::now()->addDays(1),
                'completed' => false,
                'user_id' => 1
            ]
        ];

        foreach ($tasks as $task) {
            Task::create($task);
        }
    }
}
