<?php

namespace App\Repositories\Eloquent;

use App\Models\Demo;
use App\Repositories\Contracts\DemoRepositoryInterface;
use Illuminate\Http\Request;

class DemoRepository extends BaseRepository implements DemoRepositoryInterface
{
    public function __construct(Demo $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(Request $request)
    {
        return $this->paginateWithRequest($request, sortable: ['id', 'title', 'created_at'], filterable: ['title', 'description']);
    }

    protected function allowedIncludes(): array
    {
        // Whitelist relations that can be eager loaded via ?include=rel1,rel2
        return [
            // e.g. 'author', 'comments', 'tags'
        ];
    }
}


