<?php

namespace App\Repositories\Contracts;

use Illuminate\Http\Request;

interface DemoRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateWithFilters(Request $request);
}


