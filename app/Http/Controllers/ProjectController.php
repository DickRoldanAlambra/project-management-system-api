<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProjectController extends Controller
{
    //display the project list list
    public function index(Request $request)
    {
        //create cacheKey
        $cacheKey = 'projects:' . md5($request->fullUrl());

        //put inside the cache memory
        $projects = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($request) {
            $query = Project::query();
            //filter by status
            if ($request->has('status')) {
                $query->filterByStatus($request->status);
            }
            //search by title
            if ($request->has('title')) {
                $query->searchByTitle($request->title);
            }

            return $query->paginate();
        });

        return ProjectResource::collection($projects);
    }

    //Store new project data
    public function store(StoreProjectRequest $request)
    {
        //validate before storing the request data
        $project = Project::create($request->validated());

        //delete the cache of proejct list
        Cache::flush();

        //Return only project object
        return ProjectResource::make($project)
            ->response()
            ->setStatusCode(201);
    }
    //Show/Get only specific data
    public function show(Project $project)
    {
        return ProjectResource::make($project);
    }
    //Update/Edit data
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $project->update($request->validated());

        Cache::flush();

        return ProjectResource::make($project);
    }
    //delete project
    public function destroy(Project $project)
    {
        $project->delete();

        Cache::flush();

        return response()->noContent();
    }
}
