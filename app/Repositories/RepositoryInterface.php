<?php

namespace Raank\Repositories;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @category repositories-interface
 * @package Raank\Repositories
 * @subpackage RepositoryInterface
 * @version 1.0.0
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
interface RepositoryInterface
{
    /**
     * Getting all documents.
     *
     * @param integer $perPage
     *
     * @return LengthAwarePaginator
     */
    public function all(int $perPage = 20): LengthAwarePaginator;

    /**
     * Storing the document.
     *
     * @param array $data
     *
     * @return mixed
     */
    public function store(array $data);

    /**
     * Retrieve specified a document.
     *
     * @param int $itemId
     *
     * @return mixed
     */
    public function find(int $itemId);

    /**
     * Updating specified a document.
     *
     * @param int $itemId
     * @param array $data
     *
     * @return mixed
     */
    public function update(int $itemId, array $data);

    /**
     * Destroy specified a document.
     *
     * @param int $itemId
     *
     * @return mixed
     */
    public function destroy(int $itemId);

    /**
     * Filtering the documents.
     *
     * @param Request $request
     *
     * @return LengthAwarePaginator
     */
    public function search(Request $request): LengthAwarePaginator;
}