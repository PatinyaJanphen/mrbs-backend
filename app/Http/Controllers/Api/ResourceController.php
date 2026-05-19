<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Resource\StoreResourceRequest;
use App\Services\ResourceService;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function __construct(
        protected ResourceService $resourceService
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $resources = $this->resourceService->list(
            $request->user()->company_id,
            $request->all()
        );

        return response()->json([
            'success' => true,
            'data' => $resources
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function show(Request $request, int $id)
    {
        $resource = $this->resourceService->getById(
            $request->user()->company_id,
            $id
        );

        return response()->json([
            'success' => true,
            'data' => $resource
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreResourceRequest $request)
    {
        $resource = $this->resourceService->create(
            $request->user()->company_id,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => $resource
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreResourceRequest $request, int $id)
    {
        $resource = $this->resourceService->update(
            $request->user()->company_id,
            $id,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => $resource
        ], 200);
    }
}
