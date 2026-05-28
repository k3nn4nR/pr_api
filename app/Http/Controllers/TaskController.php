<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Code;
use App\Models\Tag;
use App\Models\Status;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskCollection;
use App\Http\Requests\UpdateCodes;
use App\Http\Requests\UpdateStatuses;
use App\Http\Requests\UpdateTags;
use App\Events\StoreEvent;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new TaskCollection(Task::all());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        DB::beginTransaction();
        try {
            Task::create([
                'task' => mb_strtoupper($request->input('task')),
            ]);
            DB::commit();
            event(new StoreEvent('Task Registered: '.$request->input('task')));
            return response()->json('Task Registered',200);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error_message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
      DB::beginTransaction();
        try {
            $task->update(['task' => mb_strtoupper($request->input('task'))]);
            DB::commit();
            event(new StoreEvent('Task Updated'));
            return response()->json('Task Updated',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
      DB::beginTransaction();
        try {
            $task->delete();
            DB::commit();
            event(new StoreEvent('Task Deleted'));
            return response()->json('Task Deleted',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('message', $e->getMessage());
        }
    }

        /**
     * Update the specified resource in storage.
     */
    public function update_code_codes(UpdateCodes $request, Task $task)
    {
        DB::beginTransaction();
        try {
            if(!$request->input('codes')){
                $task->codes()->syncWithPivotValues($task->codes->pluck('id')->toArray(),['deleted_at'=>Carbon::now()],false);
                DB::commit();
                return redirect('/tag');
            }
            if($task->codes->pluck('id')->diff(Code::wherein('code',$request->input('codes'))->pluck('id'))->isNotEmpty())
                $task->codes()->syncWithPivotValues($task->codes->pluck('id')->diff(Code::wherein('code',$request->input('codes'))->pluck('id')),['deleted_at'=>Carbon::now()],false);
            if(Code::wherein('code',$request->input('codes'))->pluck('id')->diff($task->codes->pluck('id')->isNotEmpty()))
                $task->codes()->sync(Code::wherein('code',$request->input('codes'))->pluck('id')->diff($task->codes->pluck('id')),false);
            DB::commit();
            event(new StoreEvent('Codes Registered'));
            return response()->json('Tag registered',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update_code_statuses(UpdateStatuses $request, Task $task)
    {
        DB::beginTransaction();
        try {
            if(!$request->input('statuses')){
                $task->statuses()->syncWithPivotValues($task->statuses->pluck('id')->toArray(),['deleted_at'=>Carbon::now()],false);
                DB::commit();
                return redirect('/code');
            }
            if($task->statuses->pluck('id')->diff(Status::wherein('status',$request->input('statuses'))->pluck('id'))->isNotEmpty())
                $task->statuses()->syncWithPivotValues($task->statuses->pluck('id')->diff(Status::wherein('status',$request->input('statuses'))->pluck('id')),['deleted_at'=>Carbon::now()],false);
            if(Status::wherein('status',$request->input('statuses'))->pluck('id')->diff($task->statuses->pluck('id')->isNotEmpty()))
                $task->statuses()->sync(Status::wherein('status',$request->input('statuses'))->pluck('id')->diff($task->statuses->pluck('id')),false);
            DB::commit();
            event(new StoreEvent('Statuses Updated'));
            return redirect('/status');
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update_code_tags(UpdateTags $request, Task $task)
    {
        DB::beginTransaction();
        try {
            if(!$request->input('tags')){
                $task->tags()->syncWithPivotValues($task->tags->pluck('id')->toArray(),['deleted_at'=>Carbon::now()],false);
                DB::commit();
                return redirect('/code');
            }
            if($task->tags->pluck('id')->diff(Tag::wherein('tag',$request->input('tags'))->pluck('id'))->isNotEmpty())
                $task->tags()->syncWithPivotValues($task->tags->pluck('id')->diff(Tag::wherein('tag',$request->input('tags'))->pluck('id')),['deleted_at'=>Carbon::now()],false);
            if(Tag::wherein('tag',$request->input('tags'))->pluck('id')->diff($task->tags->pluck('id')->isNotEmpty()))
                $task->tags()->sync(Tag::wherein('tag',$request->input('tags'))->pluck('id')->diff($task->tags->pluck('id')),false);

            DB::commit();
            event(new StoreEvent('Tags Updated'));
            return redirect('/code');
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }
}
