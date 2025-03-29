<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Http\Resources\Api\V1\CurrencyResource;
use Illuminate\Support\Facades\DB;
use App\Events\RegistrationEvent;
use App\Http\Requests\StoreCurrency;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = CurrencyResource::collection(Currency::all());
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
    public function store(StoreCurrency $request)
    {
        DB::beginTransaction();
        try {
            currency::create([
                'currency' => mb_strtoupper($request->input('currency')),
            ]);
            DB::commit();
            event(new RegistrationEvent('currency Registered'));
            return response()->json('Currency registered',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($currency)
    {
        return Currency::where('currency',$currency)->get()->first()->load('tags','statuses','codes','files','comments');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($currency)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreCurrency $request, Currency $currency)
    {
        DB::beginTransaction();
        try {
            $currency->update(['currency' => mb_strtoupper($request->input('currency'))]);
            DB::commit();
            event(new RegistrationEvent('currency Updated'));
            return response()->json('Currency updated',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Currency $currency)
    {
        //
    }
}
