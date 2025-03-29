<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Events\RegistrationEvent;
use App\Events\UpdateEvent;
use App\Models\Brand;
use App\Models\Item;
use App\Models\Tag;
use App\Models\Comment;
use App\Models\Status;
use App\Models\Company;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreTag;
use App\Http\Requests\UpdateTag;
use App\Http\Requests\StoreComment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Models\File;
use App\Http\Requests\StoreFile;
use App\Http\Resources\Api\V1\TagResource;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = TagResource::collection(Tag::all());
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
    public function store(StoreTag $request)
    {
        DB::beginTransaction();
        try {
            Tag::create([
                'tag' => mb_strtoupper($request->input('tag')),
            ]);
            DB::commit();
            event(new RegistrationEvent('Tag Registered'));
            return response()->json('Tag registered',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($tag)
    {

    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTag $request, Tag $tag)
    {
        DB::beginTransaction();
        try {
            $tag->update(['tag' => mb_strtoupper($request->input('tag_new'))]);
            DB::commit();
            event(new RegistrationEvent('Tag Registered'));
            return response()->json('Tag registered',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        DB::beginTransaction();
        try {
            $tag->delete();
            DB::commit();
            event(new RegistrationEvent('Tag Deleted'));
            return redirect('/tag');
        } catch(\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('message', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function store_tag_comment(StoreComment $request, Tag $tag)
    {
        DB::beginTransaction();
        try {
            $tag->comments()->attach(Comment::create(['comment'=>$request->input('comment'),'user_id'=>auth()->id]));
            DB::commit();
            event(new RegistrationEvent('Comment registered'));
            return response()->json('Comment registered',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    public function destroy_tag_comment(Tag $tag, Comment $comment)
    {
        DB::beginTransaction();
        try {
            $tag->comments()->syncWithPivotValues([$comment->id],['deleted_at'=>Carbon::now()],false);
            DB::commit();
            event(new RegistrationEvent('Comment registered'));
        } catch(\Exception $e) {
            DB::rollBack();
        }
    }

    public function destroy_tag_file(Tag $tag, File $file)
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
            $tag->files()->syncWithPivotValues([$file->id],['deleted_at'=>Carbon::now()],false);
            DB::commit();
        } catch(\Exception $e) {
            DB::rollBack();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function store_tag_file(StoreFile $request, Tag $tag)
    {
        DB::beginTransaction();
        try {
            $path = Storage::putFileAs('storage/'.$tag->tag, $request->file('file'), $request->file('file')->getClientOriginalName());
            $tag->files()->attach(File::create(['file'=>$request->file('file')->getClientOriginalName(),"path"=>$path]));            
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
    public function get_tag_tags(Tag $tag)
    {
        return $tag->tags;
    }

    /**
     * Get the specified resource in storage.
     */
    public function get_tag_comments(Tag $tag)
    {
        return $tag->comments;
    }

    /**
     * Get the specified resource in storage.
     */
    public function get_tag_codes(Tag $tag)
    {
        return $tag->codes;
    }

    /**
     * Get the specified resource in storage.
     */
    public function get_tag_statuses(Tag $tag)
    {
        return $tag->statuses;
    }

    /**
     * Update tags of a specified model
     * 1) if $request->input('tag') is null means if there is any tag related to the entity it should be updated to none
     * 2) 
     */
    public function update_tags(Request $request, $model)
    {
        DB::beginTransaction();
        try {
            $modelClass = $this->resolveModel($model);
            $entity = $modelClass::where($model,$request->input('model'))->get()->first();
            $tags = Tag::wherein('tag',$request->input('tags'))->pluck('id');
            $tags_to_remove = $entity->tags->pluck('id')->diff($tags);
            if($tags_to_remove->isNotEmpty())
                $entity->tags()->syncWithPivotValues($tags_to_remove,['deleted_at'=>Carbon::now()],false);
            $entity->tags()->sync($tags);
            event(new UpdateEvent('Tags updated'));
            DB::commit();
            return response()->json(['message' => 'Tags updated'], 200);
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
            'status' => Status::class,
            'company' => Company::class,
            'currency' => Currency::class,
            // Add other models here
        ];

        return $models[$model] ?? abort(404, 'Model not found');
    }
}