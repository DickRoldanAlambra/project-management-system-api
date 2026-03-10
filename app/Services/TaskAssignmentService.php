<?php

namespace App\Services;

use App\Jobs\SendTaskAssignmentNotification;
use App\Models\Task;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class TaskAssignmentService
{
    public function assign(array $data): Task
    {
        $user = User::findOrFail($data['assigned_to']);

        $this->validateAssignment($user, $data);

        $task = Task::create($data);

        SendTaskAssignmentNotification::dispatch($task, $user);

        return $task;
    }

    public function reassign(Task $task, int $newUserId): Task
    {
        $user = User::findOrFail($newUserId);

        $this->validateAssignment($user, $task->toArray());

        $oldUserId = $task->assigned_to;
        $task->update(['assigned_to' => $newUserId]);

        if ($oldUserId !== $newUserId) {
            SendTaskAssignmentNotification::dispatch($task->fresh(), $user);
        }

        return $task->fresh();
    }

    private function validateAssignment(User $user, array $data): void
    {
        if ($user->role === 'admin') {
            throw ValidationException::withMessages([
                'assigned_to' => 'Cannot assign tasks to admin users.',
            ]);
        }

        $existingTaskCount = Task::where('assigned_to', $user->id)
            ->where('status', '!=', 'done')
            ->count();

        if ($existingTaskCount >= 10) {
            throw ValidationException::withMessages([
                'assigned_to' => 'User already has 10 active tasks. Complete some before assigning more.',
            ]);
        }

        if (isset($data['due_date']) && strtotime($data['due_date']) < strtotime('today')) {
            throw ValidationException::withMessages([
                'due_date' => 'Cannot assign a task with a past due date.',
            ]);
        }
    }
}
