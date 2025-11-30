<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // For SQLite, we need to recreate the table to modify the enum
        if (DB::getDriverName() === 'sqlite') {
            // Drop the temporary table if it exists
            Schema::dropIfExists('tasks_temp');
            
            // Create a temporary table with the new schema
            Schema::create('tasks_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('title');
                $table->text('description')->nullable();
                $table->enum('category', ['work', 'personal', 'study', 'shopping', 'health', 'education', 'other'])->default('personal');
                $table->boolean('is_completed')->default(false);
                $table->date('deadline')->nullable();
                $table->string('priority')->default('medium');
                $table->date('due_date')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });

            // Copy data from old table to new table, handling the column mismatch
            $columns = [
                'id', 'user_id', 'title', 'description', 'is_completed', 
                'deadline', 'priority', 'due_date', 'created_at', 'updated_at', 'deleted_at',
                'category' // This will be handled specially
            ];
            
            // First, copy all rows with valid categories
            DB::statement("INSERT INTO tasks_temp (" . implode(', ', $columns) . ") 
                SELECT " . implode(', ', array_diff($columns, ['category'])) . ", 
                CASE 
                    WHEN category IN ('work', 'personal', 'study', 'shopping', 'health', 'education', 'other') 
                    THEN category 
                    ELSE 'other' 
                END as category 
                FROM tasks");

            // Drop old table and rename new one
            Schema::dropIfExists('tasks');
            Schema::rename('tasks_temp', 'tasks');
            
            // Recreate indexes and foreign keys
            Schema::table('tasks', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        } else {
            // For other databases, we can use the modify method
            Schema::table('tasks', function (Blueprint $table) {
                $table->enum('category', ['work', 'personal', 'study', 'shopping', 'health', 'education', 'other'])
                    ->default('personal')
                    ->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Revert back to the original enum values if needed
        if (DB::getDriverName() === 'sqlite') {
            // Drop the temporary table if it exists
            Schema::dropIfExists('tasks_temp');
            
            // Similar process as up() but with original values
            Schema::create('tasks_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('title');
                $table->text('description')->nullable();
                $table->enum('category', ['work', 'personal', 'study', 'shopping', 'other'])->default('personal');
                $table->boolean('is_completed')->default(false);
                $table->date('deadline')->nullable();
                $table->string('priority')->default('medium');
                $table->date('due_date')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });

            // Only copy rows that match the original enum values, defaulting to 'other' for invalid categories
            $columns = [
                'id', 'user_id', 'title', 'description', 'is_completed', 
                'deadline', 'priority', 'due_date', 'created_at', 'updated_at', 'deleted_at',
                'category' // This will be handled specially
            ];
            
            DB::statement("INSERT INTO tasks_temp (" . implode(', ', $columns) . ") 
                SELECT " . implode(', ', array_diff($columns, ['category'])) . ", 
                CASE 
                    WHEN category IN ('work', 'personal', 'study', 'shopping', 'other') 
                    THEN category 
                    ELSE 'other' 
                END as category 
                FROM tasks");

            Schema::dropIfExists('tasks');
            Schema::rename('tasks_temp', 'tasks');
            
            // Recreate indexes and foreign keys
            Schema::table('tasks', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        } else {
            Schema::table('tasks', function (Blueprint $table) {
                $table->enum('category', ['work', 'personal', 'study', 'shopping', 'other'])
                    ->default('personal')
                    ->change();
            });
        }
    }
};
