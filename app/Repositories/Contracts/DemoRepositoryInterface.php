<?php

namespace App\Repositories\Contracts;

use Illuminate\Http\Request;

/**
 * Interface DemoRepositoryInterface
 */
interface DemoRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Paginate with filters.
     *
     * @param Request $request
     * @return mixed
     */
    public function paginateWithFilters(Request $request);
}


