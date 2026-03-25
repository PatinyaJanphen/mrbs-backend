<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;

class CompanyController extends Controller
{
    protected CompanyService $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $companies = $this->companyService->getAllCompanies();
        
        return response()->json([
            'success' => true,
            'data' => $companies
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $company = $this->companyService->createCompany($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'สร้างบริษัทสำเร็จ!',
            'data' => $company
        ], 201);
    }
}
