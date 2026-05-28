<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Code;
use App\Models\Tag;
use App\Models\Status;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateCodes;
use App\Http\Requests\UpdateStatuses;
use App\Http\Requests\UpdateTags;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreBrand;
use App\Http\Requests\UpdateBrandRequest;
use App\Events\StoreEvent;
use App\Http\Resources\BrandCollection;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new BrandCollection(Brand::all());
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
    public function store(StoreBrand $brand)
    {
        DB::beginTransaction();
        try {
            Brand::create([
                'brand' => mb_strtoupper($brand->input('brand')),
            ]);
            DB::commit();
            event(new StoreEvent('Brand Registered: '.$brand->input('brand')));
            return response()->json('Brand Registered',200);
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
    public function show(Request $brand)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brand $brand)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        DB::beginTransaction();
        try {
            $brand->update(['brand' => mb_strtoupper($request->input('brand'))]);
            DB::commit();
            event(new StoreEvent('Brand Updated'));
            return response()->json('Brand Updated',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        DB::beginTransaction();
        try {
            $brand->delete();
            DB::commit();
            event(new StoreEvent('Brand Deleted'));
            return response()->json('Brand Deleted',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('message', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update_code_codes(UpdateCodes $request, Brand $brand)
    {
        DB::beginTransaction();
        try {
            if(!$request->input('codes')){
                $brand->codes()->syncWithPivotValues($brand->codes->pluck('id')->toArray(),['deleted_at'=>Carbon::now()],false);
                DB::commit();
                return redirect('/tag');
            }
            if($brand->codes->pluck('id')->diff(Code::wherein('code',$request->input('codes'))->pluck('id'))->isNotEmpty())
                $brand->codes()->syncWithPivotValues($brand->codes->pluck('id')->diff(Code::wherein('code',$request->input('codes'))->pluck('id')),['deleted_at'=>Carbon::now()],false);
            if(Code::wherein('code',$request->input('codes'))->pluck('id')->diff($brand->codes->pluck('id')->isNotEmpty()))
                $brand->codes()->sync(Code::wherein('code',$request->input('codes'))->pluck('id')->diff($brand->codes->pluck('id')),false);
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
    public function update_code_statuses(UpdateStatuses $request, Brand $brand)
    {
        DB::beginTransaction();
        try {
            if(!$request->input('statuses')){
                $brand->statuses()->syncWithPivotValues($brand->statuses->pluck('id')->toArray(),['deleted_at'=>Carbon::now()],false);
                DB::commit();
                return redirect('/code');
            }
            if($brand->statuses->pluck('id')->diff(Status::wherein('status',$request->input('statuses'))->pluck('id'))->isNotEmpty())
                $brand->statuses()->syncWithPivotValues($brand->statuses->pluck('id')->diff(Status::wherein('status',$request->input('statuses'))->pluck('id')),['deleted_at'=>Carbon::now()],false);
            if(Status::wherein('status',$request->input('statuses'))->pluck('id')->diff($brand->statuses->pluck('id')->isNotEmpty()))
                $brand->statuses()->sync(Status::wherein('status',$request->input('statuses'))->pluck('id')->diff($brand->statuses->pluck('id')),false);
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
    public function update_code_tags(UpdateTags $request, Brand $brand)
    {
        DB::beginTransaction();
        try {
            if(!$request->input('tags')){
                $brand->tags()->syncWithPivotValues($brand->tags->pluck('id')->toArray(),['deleted_at'=>Carbon::now()],false);
                DB::commit();
                return redirect('/code');
            }
            if($brand->tags->pluck('id')->diff(Tag::wherein('tag',$request->input('tags'))->pluck('id'))->isNotEmpty())
                $brand->tags()->syncWithPivotValues($brand->tags->pluck('id')->diff(Tag::wherein('tag',$request->input('tags'))->pluck('id')),['deleted_at'=>Carbon::now()],false);
            if(Tag::wherein('tag',$request->input('tags'))->pluck('id')->diff($brand->tags->pluck('id')->isNotEmpty()))
                $brand->tags()->sync(Tag::wherein('tag',$request->input('tags'))->pluck('id')->diff($brand->tags->pluck('id')),false);

            DB::commit();
            event(new StoreEvent('Tags Updated'));
            return redirect('/code');
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }
}
