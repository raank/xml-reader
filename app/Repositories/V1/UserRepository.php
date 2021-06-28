<?php

namespace Raank\Repositories\V1;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Raank\Processors\BodyBuilder;
use Raank\Models\V1\User as Model;
use Illuminate\Support\Facades\Hash;
use Raank\Repositories\RepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @category repositories
 * @package Raank\Repositories
 * @subpackage V1\UserRepository
 * @version 1.0.0
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class UserRepository implements RepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function all(int $perPage = 20): LengthAwarePaginator
    {
        return Model::orderBy('created_at', 'DESC')
            ->paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function store(array $data)
    {
        return Model::create($data);
    }

    /**
     * @inheritDoc
     */
    public function find(int $itemId)
    {
        return Model::find($itemId);
    }

    /**
     * @inheritDoc
     */
    public function update(int $itemId, array $data)
    {
        return Model::find($itemId)
            ->update($data);
    }

    /**
     * @inheritDoc
     */
    public function destroy(int $itemId)
    {
        return Model::destroy($itemId);
    }

    /**
     * @inheritDoc
     */
    public function search(Request $request): LengthAwarePaginator
    {
        return (new BodyBuilder($request->all()))
            ->builder(Model::class);
    }

    /**
     * Find user by field and value.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return mixed
     */
    public function findByField(string $field, $value)
    {
        return Model::where($field, '=', $value)->first();
    }
}