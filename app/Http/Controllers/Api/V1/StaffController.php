<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Staff\StaffResource;
use App\Services\Contracts\StaffServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function __construct(private readonly StaffServiceInterface $service)
    {
        //
    }

    /**
     * @OA\Get(
     *     path="/api/v1/staff",
     *     summary="List staff (public)",
     *     tags={"Staff"},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="sort", in="query", description="Sortable fields: id,rating,years_of_experience", @OA\Schema(type="string")),
     *     @OA\Parameter(name="direction", in="query", description="asc|desc", @OA\Schema(type="string", enum={"asc","desc"})),
     *     @OA\Parameter(name="include", in="query", description="Comma-separated relations: user,branch,services", @OA\Schema(type="string")),
     *     @OA\Parameter(name="position", in="query", description="Filter by position (LIKE)", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     *
     * Display a paginated listing of staff.
     */
    public function index(Request $request): JsonResponse
    {
        $items = $this->service->list($request)->through(fn ($model) => StaffResource::make($model));
        return $this->paginated($items, __('staff.list_retrieved'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/branches/{branch}/staff",
     *     summary="List staff by branch (public)",
     *     tags={"Staff"},
     *     @OA\Parameter(name="branch", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="sort", in="query", description="Sortable fields: id,rating,years_of_experience", @OA\Schema(type="string")),
     *     @OA\Parameter(name="direction", in="query", description="asc|desc", @OA\Schema(type="string", enum={"asc","desc"})),
     *     @OA\Parameter(name="include", in="query", description="Comma-separated relations: user,branch,services", @OA\Schema(type="string")),
     *     @OA\Parameter(name="position", in="query", description="Filter by position (LIKE)", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     *
     * Display a paginated listing of staff by branch.
     */
    public function byBranch(Request $request, int $branchId): JsonResponse
    {
        $items = $this->service->listForBranch($request, $branchId)->through(fn ($model) => StaffResource::make($model));
        return $this->paginated($items, __('staff.list_retrieved'));
    }
}


