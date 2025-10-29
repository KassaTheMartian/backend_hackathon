<?php

namespace App\Repositories\Eloquent;

use App\Models\Demo;
use App\Repositories\Contracts\DemoRepositoryInterface;
use Illuminate\Http\Request;

/**
 * Class DemoRepository
 */
class DemoRepository extends BaseRepository implements DemoRepositoryInterface
{
    /**
     * Create a new repository instance.
     *
     * @param Demo $model
     */
    public function __construct(Demo $model)
    {
        parent::__construct($model);
    }

    /**
     * Paginate with filters.
     *
     * @param Request $request
     * @return mixed
     */
    public function paginateWithFilters(Request $request)
    {
        return $this->paginateWithRequest($request, sortable: ['id', 'title', 'created_at'], filterable: ['title', 'description']);
    }

    /**
     * Get allowed includes for eager loading.
     *
     * @return array
     */
    protected function allowedIncludes(): array
    {
        // Whitelist relations that can be eager loaded via ?include=rel1,rel2
        return [
            // e.g. 'author', 'comments', 'tags'
        ];
    }
}


