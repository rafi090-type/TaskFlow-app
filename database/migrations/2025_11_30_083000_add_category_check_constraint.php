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
        // For SQLite, we need to use raw SQL to add the check constraint
        if (DB::getDriverName() === 'sqlite') {
            // Create a new table with the check constraint
            DB::statement('CREATE TABLE tasks_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title VARCHAR NOT NULL,
                description TEXT,
                category VARCHAR NOT NULL CHECK(category IN ("work", "personal", "study", "shopping", "health", "education", "other")) DEFAULT "personal",
                is_completed TINYINT(1) NOT NULL DEFAULT 0,
                deadline DATE,
                priority VARCHAR NOT NULL DEFAULT "medium",
                due_date DATE,
                created_at DATETIME,
                updated_at DATETIME,
                deleted_at DATETIME,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )');

            // Copy data from old table to new table
            DB::statement('INSERT INTO tasks_new SELECT * FROM tasks');

            // Drop the old table
            Schema::drop('tasks');

            // Rename new table to original name
            Schema::rename('tasks_new', 'tasks');
        } else {
            // For other databases, use the modify method
            DB::statement("
                ALTER TABLE tasks 
                MODIFY COLUMN category ENUM('work', 'personal', 'study', 'shopping', 'health', 'education', 'other') 
                NOT NULL DEFAULT 'personal'
            ")   ;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // For SQLite, we need to recreate the table without the check constraint
        if (DB::getDriverName() === 'sqlite') {
            // Create a new table without the check constraint
            DB::statement('CREATE TABLE tasks_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title VARCHAR NOT NULL,
                description TEXT,
                category VARCHAR NOT NULL DEFAULT "personal",
                is_completed TINYINT(1) NOT NULL DEFAULT 0,
                deadline DATE,
                priority VARCHAR NOT NULL DEFAULT "medium",
                due_date DATE,
                created_at DATETIME,
                updated_at DATETIME,
                deleted_at DATETIME,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )');

            // Copy data from old table to new table
            DB::statement('INSERT INTO tasks_new SELECT * FROM tasks');

            // Drop the old table
            Schema::drop('tasks');

            // Rename new table to original name
            Schema::rename('tasks_new', 'tasks');
        } else {
            // For other databases, revert to the original enum values
            DB::statement("
                ALTER TABLE tasks 
                MODIFY COLUMN category ENUM('work', 'personal', 'study', 'shopping', 'other') 
                NOT NULL DEFAULT 'personal'
            ")   ;
        }
    }
};
