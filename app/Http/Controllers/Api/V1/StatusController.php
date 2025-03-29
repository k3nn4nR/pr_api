<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\Tag;
use App\Models\Brand;
use App\Models\Item;
use App\Models\Code;
use App\Models\Comment;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests\StoreStatus;
use App\Http\Requests\UpdateStatus;
use Carbon\Carbon;
use App\Http\Requests\StoreComment;
use App\Events\RegistrationEvent;
use Illuminate\Support\Facades\Storage;
use App\Models\File;
use App\Http\Requests\StoreFile;
use App\Http\Resources\Api\V1\StatusResource;
use App\Events\UpdateEvent;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = StatusResource::collection(Status::all());
        return compact('data');
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
    public function store(StoreStatus $request)
    {
        DB::beginTransaction();
        try {
            Status::create([
                'status' => mb_strtoupper($request->input('status')),
            ]);
            DB::commit();
            event(new RegistrationEvent(('Status Registered')));
            return response()->json(['message' => 'Status registered'], 200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Status $status)
    {
        return $status;
    }
    
    /**
     * Show the form for editing the specified resource.
     */
    public function edit($status)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStatus $request, Status $status)
    {
        DB::beginTransaction();
        try {
            $status->update(['status' => mb_strtoupper($request->input('status_new'))]);
            DB::commit();
            event(new RegistrationEvent('Status Registered'));
            return response()->json('Status registered',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Status $status)
    {
        DB::beginTransaction();
        try {
            $status->delete();
            DB::commit();
            event(new RegistrationEvent('Status Deleted'));
            return redirect('/status');
        } catch(\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('message', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function store_status_comment(StoreComment $request, Status $status)
    {
        DB::beginTransaction();
        try {
            $status->comments()->attach(Comment::create(['comment'=>$request->input('comment'),'user_id'=>auth()->id]));
            DB::commit();
            event(new RegistrationEvent('Comment registered'));
            return response()->json('Comment registered',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    public function destroy_status_comment(Status $status, Comment $comment)
    {
        DB::beginTransaction();
        try {
            $status->comments()->syncWithPivotValues([$comment->id],['deleted_at'=>Carbon::now()],false);
            DB::commit();
            event(new RegistrationEvent('Comment registered'));
        } catch(\Exception $e) {
            DB::rollBack();
        }
    }

    public function destroy_status_file(Status $status, File $file)
    {
        DB::beginTransaction();
        try {
            $deleted_at = Carbon::now();
            $exploded_file = explode('.', $file->file);
            $new_name = $exploded_file[0].$deleted_at.'.'.$exploded_file[1];
            $exploded_path = explode('/', $file->path);
            $new_path = '';
            array_pop($exploded_path);
            foreach($exploded_path as $link)
                $new_path.=$link.'/';
            Storage::move($new_path.$file->file,str_replace(":","_",$new_path.$new_name));
            $file->update(['file'=>$new_name,'path'=>$new_path.$new_name,'deleted_at'=>$deleted_at]);
            $status->files()->syncWithPivotValues([$file->id],['deleted_at'=>Carbon::now()],false);
            DB::commit();
        } catch(\Exception $e) {
            DB::rollBack();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function store_status_file(StoreFile $request, Status $status)
    {
        DB::beginTransaction();
        try {
            $path = Storage::putFileAs('storage/'.$status->status, $request->file('file'), $request->file('file')->getClientOriginalName());
            $status->files()->attach(File::create(['file'=>$request->file('file')->getClientOriginalName(),"path"=>$path]));            
            DB::commit();
            return response()->json('Comment registered',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    /**
     * Get the specified resource in storage.
     */
    public function get_status_tags(Status $status)
    {
        return $status->tags;
    }

    /**
     * Get the specified resource in storage.
     */
    public function get_status_codes(Status $status)
    {
        return $status->codes;
    }

    /**
     * Get the specified resource in storage.
     */
    public function get_status_comments(Status $status)
    {
        return $status->comments;
    }


    /**
     * Get the specified resource in storage.
     */
    public function get_status_statuses(Status $status)
    {
        return $status->statuses;
    }

    /**
     * Update tags of a specified model
     * 1) if $request->input('tag') is null means if there is any tag related to the entity it should be updated to none
     * 2) 
     */
    public function update_statuses(Request $request, $model)
    {
        DB::beginTransaction();
        try {
            $modelClass = $this->resolveModel($model);
            $entity = $modelClass::where($model,$request->input('model'))->get()->first();
            $statuses = Status::wherein('status',$request->input('statuses'))->pluck('id');
            $statuses_to_remove = $entity->statuses->pluck('id')->diff($statuses);
            if($statuses_to_remove->isNotEmpty())
                $entity->statuses()->syncWithPivotValues($statuses_to_remove,['deleted_at'=>Carbon::now()],false);
            $entity->statuses()->sync($statuses);
            DB::commit();
            event(new UpdateEvent('Status updated'));
            return response()->json(['message' => 'Status updated'], 200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    protected function resolveModel($model)
    {
        $models = [
            'brand' => Brand::class,
            'item' => Item::class,
            'tags' => Tag::class,
            // Add other models here
        ];

        return $models[$model] ?? abort(404, 'Model not found');
    }
}
