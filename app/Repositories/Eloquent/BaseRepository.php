<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    protected function query(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * Override in child repositories to whitelist relations for eager loading.
     */
    protected function allowedIncludes(): array
    {
        return [];
    }

    /**
     * Resolve requested includes from the request with whitelist.
     */
    protected function resolveIncludes(Request $request): array
    {
        $requested = $request->string('include')->toString();
        if ($requested === '') {
            return [];
        }
        $parts = array_filter(array_map('trim', explode(',', $requested)));
        if (empty($parts)) {
            return [];
        }
        $allowed = $this->allowedIncludes();
        if (empty($allowed)) {
            return [];
        }
        return array_values(array_intersect($parts, $allowed));
    }

    public function all(): Collection
    {
        return $this->query()->orderByDesc('id')->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()->orderByDesc('id')->paginate($perPage);
    }

    public function paginateWithRequest(Request $request, array $sortable = [], array $filterable = []): LengthAwarePaginator
    {
        $query = $this->query();

        $includes = $this->resolveIncludes($request);
        if (!empty($includes)) {
            $query->with($includes);
        }

        foreach ($filterable as $field) {
            if ($request->filled($field)) {
                $value = $request->input($field);
                $query->where($field, 'like', "%{$value}%");
            }
        }

        foreach ($request->all() as $key => $value) {
            if (str_starts_with($key, 'is_') && $value !== null) {
                $query->where($key, filter_var($value, FILTER_VALIDATE_BOOL));
            }
        }

        $sort = $request->input('sort');
        $direction = strtolower($request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        if ($sort && in_array($sort, $sortable, true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderByDesc('id');
        }

        $perPage = (int) $request->input('per_page', 15);

        return $query->paginate($perPage)->appends($request->query());
    }

    public function find(int $id): ?Model
    {
        return $this->query()->find($id);
    }

    public function create(array $attributes): Model
    {
        return $this->query()->create($attributes);
    }

    public function update(int $id, array $attributes): ?Model
    {
        $entity = $this->find($id);
        if (!$entity) {
            return null;
        }
        $entity->fill($attributes);
        $entity->save();
        return $entity;
    }

    public function delete(int $id): bool
    {
        $entity = $this->find($id);
        if (!$entity) {
            return false;
        }
        return (bool) $entity->delete();
    }
}


