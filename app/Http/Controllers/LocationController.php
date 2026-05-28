<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Models\Code;
use App\Models\Tag;
use App\Models\Status;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateCodes;
use App\Http\Requests\UpdateStatuses;
use App\Http\Requests\UpdateTags;
use Illuminate\Support\Facades\DB;
use App\Events\StoreEvent;
use App\Http\Resources\LocationCollection;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new LocationCollection(Location::all());
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
    public function store(StoreLocationRequest $request)
    {
        DB::beginTransaction();
        try {
            Location::create([
                'location' => mb_strtoupper($request->input('location')),
            ]);
            DB::commit();
            event(new StoreEvent('location Registered: '.$request->input('location')));
            return response()->json('location Registered',200);
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
    public function show(Location $location)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Location $location)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLocationRequest $request, Location $location)
    {
        DB::beginTransaction();
        try {
            $location->update(['location' => mb_strtoupper($request->input('location'))]);
            DB::commit();
            event(new StoreEvent('location Updated'));
            return response()->json('location Updated',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location)
    {
        DB::beginTransaction();
        try {
            $location->delete();
            DB::commit();
            event(new StoreEvent('location Deleted'));
            return response()->json('location Deleted',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('message', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update_code_codes(UpdateCodes $request, Location $location)
    {
        DB::beginTransaction();
        try {
            if(!$request->input('codes')){
                $location->codes()->syncWithPivotValues($location->codes->pluck('id')->toArray(),['deleted_at'=>Carbon::now()],false);
                DB::commit();
                return redirect('/tag');
            }
            if($location->codes->pluck('id')->diff(Code::wherein('code',$request->input('codes'))->pluck('id'))->isNotEmpty())
                $location->codes()->syncWithPivotValues($location->codes->pluck('id')->diff(Code::wherein('code',$request->input('codes'))->pluck('id')),['deleted_at'=>Carbon::now()],false);
            if(Code::wherein('code',$request->input('codes'))->pluck('id')->diff($location->codes->pluck('id')->isNotEmpty()))
                $location->codes()->sync(Code::wherein('code',$request->input('codes'))->pluck('id')->diff($location->codes->pluck('id')),false);
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
    public function update_code_statuses(UpdateStatuses $request, Location $location)
    {
        DB::beginTransaction();
        try {
            if(!$request->input('statuses')){
                $location->statuses()->syncWithPivotValues($location->statuses->pluck('id')->toArray(),['deleted_at'=>Carbon::now()],false);
                DB::commit();
                return redirect('/code');
            }
            if($location->statuses->pluck('id')->diff(Status::wherein('status',$request->input('statuses'))->pluck('id'))->isNotEmpty())
                $location->statuses()->syncWithPivotValues($location->statuses->pluck('id')->diff(Status::wherein('status',$request->input('statuses'))->pluck('id')),['deleted_at'=>Carbon::now()],false);
            if(Status::wherein('status',$request->input('statuses'))->pluck('id')->diff($location->statuses->pluck('id')->isNotEmpty()))
                $location->statuses()->sync(Status::wherein('status',$request->input('statuses'))->pluck('id')->diff($location->statuses->pluck('id')),false);
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
    public function update_code_tags(UpdateTags $request, Location $location)
    {
        DB::beginTransaction();
        try {
            if(!$request->input('tags')){
                $location->tags()->syncWithPivotValues($location->tags->pluck('id')->toArray(),['deleted_at'=>Carbon::now()],false);
                DB::commit();
                return redirect('/code');
            }
            if($location->tags->pluck('id')->diff(Tag::wherein('tag',$request->input('tags'))->pluck('id'))->isNotEmpty())
                $location->tags()->syncWithPivotValues($location->tags->pluck('id')->diff(Tag::wherein('tag',$request->input('tags'))->pluck('id')),['deleted_at'=>Carbon::now()],false);
            if(Tag::wherein('tag',$request->input('tags'))->pluck('id')->diff($location->tags->pluck('id')->isNotEmpty()))
                $location->tags()->sync(Tag::wherein('tag',$request->input('tags'))->pluck('id')->diff($location->tags->pluck('id')),false);

            DB::commit();
            event(new StoreEvent('Tags Updated'));
            return redirect('/code');
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }
}
