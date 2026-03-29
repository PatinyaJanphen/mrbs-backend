<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreResourceRequest;
use App\Services\ResourceService;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function __construct(
        protected ResourceService $resourceService
    ) {}

    public function index(Request $request)
    {
        return $this->resourceService->list(
            $request->user()->company_id,
            $request->all()
        );
    }

    public function show(Request $request, int $id)
    {
        return $this->resourceService->getById(
            $request->user()->company_id,
            $id
        );
    }

    public function store(StoreResourceRequest $request)
    {
        $resource = $this->resourceService->create(
            $request->user()->company_id,
            $request->validated()
        );

        return response()->json($resource, 201);
    }
}
