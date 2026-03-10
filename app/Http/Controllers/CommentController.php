<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Task;

class CommentController extends Controller
{
    //get all comments with users
    public function index(Task $task)
    {
        $comments = $task->comments()->with('user')->paginate();

        return CommentResource::collection($comments);
    }
    // store or post comment
    public function store(StoreCommentRequest $request, Task $task)
    {
        $comment = $task->comments()->create([
            'body' => $request->validated('body'),
            'user_id' => $request->user()->id,
        ]);

        return CommentResource::make($comment)
            ->response()
            ->setStatusCode(201);
    }
}
