<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Create a new UserController instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users",
     *     summary="List users",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=403, description="Forbidden", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Display a listing of users.
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        
        $users = User::paginate(15);
        $items = $users->through(fn ($user) => UserResource::make($user));
        return $this->paginated($items, 'Users retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/{id}",
     *     summary="Get user by id",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=403, description="Forbidden", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Display the specified user.
     */
    public function show(int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            $this->notFound('User');
        }
        
        $this->authorize('view', $user);
        
        return $this->ok(UserResource::make($user), 'User retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/users/{id}",
     *     summary="Update user",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=403, description="Forbidden", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Update the specified user.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            $this->notFound('User');
        }
        
        $this->authorize('update', $user);
        
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
        ]);
        
        $user->update($request->only(['name', 'email']));
        
        return $this->ok(UserResource::make($user), 'User updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/users/{id}",
     *     summary="Delete user",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="No Content"),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=403, description="Forbidden", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Remove the specified user from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            $this->notFound('User');
        }
        
        $this->authorize('delete', $user);
        
        $user->delete();
        
        return $this->noContent('User deleted successfully');
    }
}
