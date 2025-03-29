<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Item;
use App\Models\Currency;
use App\Models\Company;
use App\Models\Code;
use App\Models\Tag;
use App\Models\File;
use App\Models\Status;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreCode;
use App\Http\Requests\UpdateCodeTags;
use App\Http\Requests\UpdateCodeCodes;
use App\Http\Requests\UpdateCodeStatuses;
use App\Http\Requests\StoreFile;
use App\Events\RegistrationEvent;
use App\Events\UpdateEvent;
use Carbon\Carbon;
use App\Http\Requests\StoreComment;
use Illuminate\Support\Facades\Storage;

class CodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

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
    public function store(StoreCode $request)
    {
        DB::beginTransaction();
        try {
            Code::create([
                'code' => mb_strtoupper($request->input('code')),
            ]);
            DB::commit();
            event(new RegistrationEvent('Code Registered'));
            return response()->json('Code registered',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Code $code)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Code $code)
    {
        
    }

    /**
     * Update the specified resource in storage.
     * falta actualizar Request->StoreCode, code debe ser un arreglo para que lo pueda recibir el front end
     */
    // public function update(StoreCode $request, $model)
    public function update(Request $request, $model)
    {
        DB::beginTransaction();
        try {
            $modelClass = $this->resolveModel($model);
            $entity = $modelClass::where($model,$request->input('model'))->get()->first();
            $codes = $entity->code->pluck('id');
            if($codes->isNotEmpty())
                $entity->code()->delete();
            $entity->code()->create([
                'code' => $request->input('code')
            ]);
            event(new UpdateEvent('Code updated'));
            DB::commit();
            return response()->json('Tags updated',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Code $code)
    {
        DB::beginTransaction();
        try {
            $code->delete();
            DB::commit();
            event(new RegistrationEvent('Code Deleted'));
            return redirect('/code');
        } catch(\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('message', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update_code_codes(UpdateCodeCodes $request, Code $code)
    {
        DB::beginTransaction();
        try {
            if(!$request->input('codes')){
                $code->codes()->syncWithPivotValues($code->codes->pluck('id')->toArray(),['deleted_at'=>Carbon::now()],false);
                DB::commit();
                return redirect('/tag');
            }
            if($code->codes->pluck('id')->diff(Code::wherein('code',$request->input('codes'))->pluck('id'))->isNotEmpty())
                $code->codes()->syncWithPivotValues($code->codes->pluck('id')->diff(Code::wherein('code',$request->input('codes'))->pluck('id')),['deleted_at'=>Carbon::now()],false);
            if(Code::wherein('code',$request->input('codes'))->pluck('id')->diff($code->codes->pluck('id')->isNotEmpty()))
                $code->codes()->sync(Code::wherein('code',$request->input('codes'))->pluck('id')->diff($code->codes->pluck('id')),false);
            DB::commit();
            event(new RegistrationEvent('Codes Registered'));
            return response()->json('Tag registered',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update_code_statuses(UpdateCodeStatuses $request, Code $code)
    {
        DB::beginTransaction();
        try {
            if(!$request->input('statuses')){
                $code->statuses()->syncWithPivotValues($code->statuses->pluck('id')->toArray(),['deleted_at'=>Carbon::now()],false);
                DB::commit();
                return redirect('/code');
            }
            if($code->statuses->pluck('id')->diff(Status::wherein('status',$request->input('statuses'))->pluck('id'))->isNotEmpty())
                $code->statuses()->syncWithPivotValues($code->statuses->pluck('id')->diff(Status::wherein('status',$request->input('statuses'))->pluck('id')),['deleted_at'=>Carbon::now()],false);
            if(Status::wherein('status',$request->input('statuses'))->pluck('id')->diff($code->statuses->pluck('id')->isNotEmpty()))
                $code->statuses()->sync(Status::wherein('status',$request->input('statuses'))->pluck('id')->diff($code->statuses->pluck('id')),false);
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
    public function update_code_tags(UpdateCodeTags $request, Code $code)
    {
        DB::beginTransaction();
        try {
            if(!$request->input('tags')){
                $code->tags()->syncWithPivotValues($code->tags->pluck('id')->toArray(),['deleted_at'=>Carbon::now()],false);
                DB::commit();
                return redirect('/code');
            }
            if($code->tags->pluck('id')->diff(Tag::wherein('tag',$request->input('tags'))->pluck('id'))->isNotEmpty())
                $code->tags()->syncWithPivotValues($code->tags->pluck('id')->diff(Tag::wherein('tag',$request->input('tags'))->pluck('id')),['deleted_at'=>Carbon::now()],false);
            if(Tag::wherein('tag',$request->input('tags'))->pluck('id')->diff($code->tags->pluck('id')->isNotEmpty()))
                $code->tags()->sync(Tag::wherein('tag',$request->input('tags'))->pluck('id')->diff($code->tags->pluck('id')),false);
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
    public function store_code_comment(StoreComment $request, Code $code)
    {
        DB::beginTransaction();
        try {
            $code->comments()->attach(Comment::create(['comment'=>$request->input('comment'),'user_id'=>auth()->id]));
            DB::commit();
            event(new RegistrationEvent('Comment registered'));
            return response()->json('Comment registered',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function store_code_file(StoreFile $request, Code $code)
    {
        DB::beginTransaction();
        try {
            $path = Storage::putFileAs('storage/'.$code->code, $request->file('file'), $request->file('file')->getClientOriginalName());
            $code->files()->attach(File::create(['file'=>$request->file('file')->getClientOriginalName(),"path"=>$path]));
            DB::commit();
            return response()->json('Comment registered',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    public function destroy_code_comment(Code $code, Comment $comment)
    {
        DB::beginTransaction();
        try {
            $code->comments()->syncWithPivotValues([$comment->id],['deleted_at'=>Carbon::now()],false);
            DB::commit();
            event(new RegistrationEvent('Comment registered'));
        } catch(\Exception $e) {
            DB::rollBack();
        }
    }

    public function destroy_code_file(Code $code, File $file)
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
            $code->files()->syncWithPivotValues([$file->id],['deleted_at'=>Carbon::now()],false);
            DB::commit();
            event(new RegistrationEvent('Comment registered'));
        } catch(\Exception $e) {
            DB::rollBack();
        }
    }

    /**
     * Get the specified resource in storage.
     */
    public function get_code_tags(Code $code)
    {
        return $code->tags;
    }

    /**
     * Get the specified resource in storage.
     */
    public function get_code_codes(Code $code)
    {
        return $code->codes;
    }

    /**
     * Get the specified resource in storage.
     */
    public function get_code_comments(Code $code)
    {
        return $code->comments;
    }


    /**
     * Get the specified resource in storage.
     */
    public function get_code_statuses(Code $code)
    {
        return $code->statuses;
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