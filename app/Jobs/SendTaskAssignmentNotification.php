<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;

class SendTaskAssignmentNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private Task $task,
        private User $user
    ) {}


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->user->notify(new TaskAssignedNotification($this->task));
    }
}
