<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Status;
use App\Models\Tag;
use App\Models\Code;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Comment;
use App\Http\Requests\StoreFile;
use App\Http\Requests\UpdateFileTags;
use App\Http\Requests\UpdateFileCodes;
use App\Http\Requests\UpdateFileStatuses;
use App\Http\Requests\StoreComment;
use Carbon\Carbon;
use App\Events\RegistrationEvent;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(StoreFile $request)
    {
        dd($request->all());
        DB::beginTransaction();
        try {
            File::create([
                'file' => mb_strtoupper($request->input('file')),
                'path' => $request->input('path'),
            ]);
            DB::commit();
            event(new RegistrationEvent('File Registered'));
            return response()->json('File registered',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(File $file)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(File $file)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, File $file)
    {
        // eliminar de manera logica el archivo y vincular el archivo al fileable
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($file)
    {
        DB::beginTransaction();
        try {
            $file = File::where('file',$file)->get()->first()->delete();
            DB::commit();
            event(new RegistrationEvent('File Deleted'));
            return redirect('/code');
        } catch(\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('message', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update_file_codes(UpdateFileCodes $request, File $file)
    {
        DB::beginTransaction();
        try {
            if(!$request->input('codes')){
                $file->codes()->syncWithPivotValues($file->codes->pluck('id')->toArray(),['deleted_at'=>Carbon::now()],false);
                DB::commit();
                event(new RegistrationEvent('Files Registered'));
                return response()->json('Tag registered',200);
            }
            if($file->codes->pluck('id')->diff(Code::wherein('code',$request->input('codes'))->pluck('id'))->isNotEmpty())
                $file->codes()->syncWithPivotValues($file->code->pluck('id')->diff(Code::wherein('code',$request->input('codes'))->pluck('id')),['deleted_at'=>Carbon::now()],false);
            if(File::wherein('File',$request->input('codes'))->pluck('id')->diff($file->codes->pluck('id')->isNotEmpty()))
                $file->codes()->sync(Code::wherein('code',$request->input('codes'))->pluck('id')->diff($file->codes->pluck('id')),false);
            DB::commit();
            event(new RegistrationEvent('Files Registered'));
            return response()->json('Tag registered',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update_file_statuses(UpdateFileStatuses $request, File $file)
    {
        DB::beginTransaction();
        try {
            if(!$request->input('statuses')){
                $file->statuses()->syncWithPivotValues($file->statuses->pluck('id')->toArray(),['deleted_at'=>Carbon::now()],false);
                DB::commit();
                return redirect()->back();
            }
            if($file->tags->pluck('id')->diff(Status::wherein('status',$request->input('statuses'))->pluck('id'))->isNotEmpty())
                $file->statuses()->syncWithPivotValues($file->statuses->pluck('id')->diff(Status::wherein('status',$request->input('statuses'))->pluck('id')),['deleted_at'=>Carbon::now()],false);
            if(Status::wherein('status',$request->input('tags'))->pluck('id')->diff($file->statuses->pluck('id')->isNotEmpty()))
                $file->statuses()->sync(Status::wherein('status',$request->input('statuses'))->pluck('id')->diff($file->statuses->pluck('id')),false);
            DB::commit();
            event(new RegistrationEvent('Statuses Updated'));
            return response()->json('Statuses registered',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update_file_tags(UpdateFileTags $request, File $file)
    {
        DB::beginTransaction();
        try {
            if(!$request->input('tags')){
                $file->tags()->syncWithPivotValues($file->tags->pluck('id')->toArray(),['deleted_at'=>Carbon::now()],false);
                DB::commit();
                return redirect()->back();
            }
            if($file->tags->pluck('id')->diff(Tag::wherein('tag',$request->input('tags'))->pluck('id'))->isNotEmpty())
                $file->tags()->syncWithPivotValues($file->tags->pluck('id')->diff(Tag::wherein('tag',$request->input('tags'))->pluck('id')),['deleted_at'=>Carbon::now()],false);
            if(Tag::wherein('tag',$request->input('tags'))->pluck('id')->diff($file->tags->pluck('id')->isNotEmpty()))
                $file->tags()->sync(Tag::wherein('tag',$request->input('tags'))->pluck('id')->diff($file->tags->pluck('id')),false);
            DB::commit();
            event(new RegistrationEvent('Tags Updated'));
            return response()->json('Tags Updated',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function store_file_comment(StoreComment $request, File $file)
    {
        DB::beginTransaction();
        try {
            $file->comments()->attach(Comment::create(['comment'=>$request->input('comment'),'user_id'=>auth()->id]));
            DB::commit();
            event(new RegistrationEvent('Comment registered'));
            return response()->json('Comment registered',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

        /**
     * Get the specified resource in storage.
     */
    public function get_file_tags(File $file)
    {
        return $file->tags;
    }

    /**
     * Get the specified resource in storage.
     */
    public function get_file_codes(File $file)
    {
        return $file->codes;
    }

    /**
     * Get the specified resource in storage.
     */
    public function get_file_comments(File $file)
    {
        return $file->comments;
    }


    /**
     * Get the specified resource in storage.
     */
    public function get_file_statuses(File $file)
    {
        return $file->statuses;
    }
}
