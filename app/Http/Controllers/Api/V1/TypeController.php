<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreType;
use App\Http\Resources\Api\V1\TypeResource;
class TypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = TypeResource::collection(Type::all());
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
    public function store(StoreType $request)
    {
        DB::beginTransaction();
        try {
            Type::create([
                'type' => mb_strtoupper($request->input('type')),
                'brand_id'=>$request->input('brand_id')
            ]);
            DB::commit();
            // event(new RegistrationEvent('Brand Registered')); change for brand
            return response()->json('Type registered',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Type $type)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($type)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Type $type)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Type $type)
    {
        //
    }
}
