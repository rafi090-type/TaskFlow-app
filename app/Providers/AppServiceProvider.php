<?php

namespace App\Providers;

use App\Models\Task;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        // Register the TaskPolicy
        Task::class => TaskPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}