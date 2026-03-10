<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskAssignmentService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private TaskAssignmentService $taskAssignmentService
    ) {}

    public function index(Request $request, Project $project)
    {
        $query = $project->tasks();

        if ($request->has('status')) {
            $query->filterByStatus($request->status);
        }
        if ($request->has('title')) {
            $query->searchByTitle($request->title);
        }

        return TaskResource::collection($query->paginate());
    }

    public function store(StoreTaskRequest $request, Project $project)
    {
        $data = array_merge($request->validated(), ['project_id' => $project->id]);

        $task = $this->taskAssignmentService->assign($data);

        return TaskResource::make($task)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Task $task)
    {
        return TaskResource::make($task);
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $user = $request->user();

        // Only manager or the assigned user can update
        if ($user->role !== 'manager' && $user->id !== $task->assigned_to) {
            return response()->json([
                'message' => 'Forbidden. Only the manager or assigned user can update this task.',
            ], 403);
        }

        if ($request->has('assigned_to') && $request->assigned_to !== $task->assigned_to) {
            $task = $this->taskAssignmentService->reassign($task, $request->assigned_to);
            $task->update($request->safe()->except('assigned_to'));
        } else {
            $task->update($request->validated());
        }

        return TaskResource::make($task);
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return response()->noContent();
    }
}
