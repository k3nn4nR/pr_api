<?php

namespace App\Http\Controllers;

use App\Models\Type;
use App\Http\Requests\StoreTypeRequest;
use App\Http\Requests\UpdateTypeRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Events\StoreEvent;
use App\Http\Resources\TypeCollection;

class TypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new TypeCollection(Type::all());
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
    public function store(StoreTypeRequest $type)
    {
        DB::beginTransaction();
        try {
            Type::create([
                'type' => mb_strtoupper($type->input('type')),
                'brand_id' => $type->input('brand_id'),
            ]);
            DB::commit();
            event(new StoreEvent('Model Registered: '.$type->input('type')));
            return response()->json('Model Registered',200);
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
    public function show(Type $type)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Type $type)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTypeRequest $request, Type $type)
    {
        DB::beginTransaction();
        try {
            $type->update(['type' => mb_strtoupper($request->input('type_new'))]);
            DB::commit();
            event(new StoreEvent('Type Updated'));
            return response()->json('Type Updated',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Type $type)
    {
        DB::beginTransaction();
        try {
            $type->delete();
            DB::commit();
            event(new StoreEvent('Type Deleted'));
            return response()->json('Type Deleted',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('message', $e->getMessage());
        }
    }
}
