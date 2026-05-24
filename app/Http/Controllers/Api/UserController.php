<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorizeManageUsers($request);

        $users = $this->userService->list($request->user(), $request->all());

        return UserResource::collection($users);
    }

    public function show(Request $request, int $id): UserResource
    {
        $this->authorizeManageUsers($request);

        $user = $this->userService->getById($request->user(), $id);

        return new UserResource($user);
    }

    public function store(StoreUserRequest $request): UserResource
    {
        $user = $this->userService->create($request->user(), $request->validated());

        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, int $id): UserResource
    {
        $user = $this->userService->update($request->user(), $id, $request->validated());

        return new UserResource($user);
    }

    public function toggleActive(Request $request, int $id): UserResource
    {
        $this->authorizeManageUsers($request);

        $user = $this->userService->toggleActive($request->user(), $id);

        return new UserResource($user);
    }

    public function bookings(Request $request, int $id): AnonymousResourceCollection
    {
        $this->authorizeManageUsers($request);

        $bookings = $this->userService->bookings($request->user(), $id, $request->all());

        return BookingResource::collection($bookings);
    }

    private function authorizeManageUsers(Request $request): void
    {
        abort_unless($request->user()?->role <= User::ROLE_ADMIN, 403);
    }
}
