<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Resources\Api\V1\CompanyResource;
use Illuminate\Support\Facades\DB;
use App\Events\RegistrationEvent;
use App\Http\Requests\StoreCompany;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = CompanyResource::collection(Company::all());
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
    public function store(StoreCompany $request)
    {
        DB::beginTransaction();
        try {
            Company::create([
                'company' => mb_strtoupper($request->input('company')),
            ]);
            DB::commit();
            event(new RegistrationEvent('Company Registered'));
            return response()->json('company registered',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($company)
    {
        return Company::where('company',$company)->get()->first()->load('tags','statuses','codes','files','comments');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($company)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreCompany $request, Company $company)
    {
        DB::beginTransaction();
        try {
            $company->update(['company' => mb_strtoupper($request->input('company'))]);
            DB::commit();
            event(new RegistrationEvent('company Updated'));
            return response()->json('Company updated',200);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        //
    }
}
