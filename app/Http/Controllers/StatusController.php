<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Http\Requests\StoreStatusRequest;
use App\Http\Requests\UpdateStatusRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Events\StoreEvent;
use App\Http\Resources\StatusCollection;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new StatusCollection(Status::all());
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
    public function store(StoreStatusRequest $request)
    {
        DB::beginTransaction();
        try {
            Status::create([
                'status' => mb_strtoupper($request->input('status')),
            ]);
            DB::commit();
            event(new StoreEvent('Status Registered: '.$request->input('status')));
            return response()->json('Status Registered',200);
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
    public function show(Status $status)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Status $status)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStatusRequest $request, Status $status)
    {
        DB::beginTransaction();
        try {
            $status->update(['status' => mb_strtoupper($request->input('status'))]);
            DB::commit();
            event(new StoreEvent('Status Updated'));
            return response()->json('Status Updated',200);
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
            event(new StoreEvent('Status Deleted'));
            return response()->json('Status Deleted',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('message', $e->getMessage());
        }
    }
}
