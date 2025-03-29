<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreBrand;
use App\Events\RegistrationEvent;
use App\Http\Resources\Api\V1\BrandResource;
class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = BrandResource::collection(Brand::all());
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
    public function store(StoreBrand $request)
    {
        DB::beginTransaction();
        try {
            Brand::create([
                'brand' => mb_strtoupper($request->input('brand')),
            ]);
            DB::commit();
            event(new RegistrationEvent('Brand Registered'));
            return response()->json('Brand registered',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($brand)
    {
        return Brand::where('brand',$brand)->get()->first()->load('tags','statuses','code','files','comments');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($brand)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreBrand $request,Brand $brand)
    {
        DB::beginTransaction();
        try {
            $brand->update(['brand' => mb_strtoupper($request->input('brand'))]);
            DB::commit();
            event(new RegistrationEvent('Brand Updated'));
            return response()->json('Brand updated',200);
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
        //
    }
}
