<?php

namespace App\Services\Contracts;

use App\Data\Demo\DemoData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface DemoServiceInterface
{
    public function list(Request $request): LengthAwarePaginator;

    public function create(DemoData $data): Model;

    public function find(int $id): ?Model;

    public function update(int $id, DemoData $data): ?Model;

    public function delete(int $id): bool;
}


