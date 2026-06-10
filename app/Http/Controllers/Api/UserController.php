<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorizeManageUsers($request);

        $users = $this->userService->list($request->user(), $request->all());

        return response()->json([
            'success' => true,
            'data'    => $users,
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $this->authorizeManageUsers($request);

        $user = $this->userService->getById($request->user(), $id);

        return response()->json([
            'success' => true,
            'data'    => $user,
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'data'    => $user,
        ], 201);
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = $this->userService->update($request->user(), $id, $request->validated());

        return response()->json([
            'success' => true,
            'data'    => $user,
        ]);
    }

    public function toggleActive(Request $request, int $id): JsonResponse
    {
        $this->authorizeManageUsers($request);

        $user = $this->userService->toggleActive($request->user(), $id);

        return response()->json([
            'success' => true,
            'data'    => $user,
        ]);
    }

    public function bookings(Request $request, int $id): JsonResponse
    {
        $this->authorizeManageUsers($request);

        $bookings = $this->userService->bookings($request->user(), $id, $request->all());

        return response()->json([
            'success' => true,
            'data'    => $bookings,
        ]);
    }

    private function authorizeManageUsers(Request $request): void
    {
        abort_unless($request->user()?->role <= User::ROLE_ADMIN, 403);
    }
}
