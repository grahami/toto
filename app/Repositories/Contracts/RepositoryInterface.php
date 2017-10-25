<?php

namespace App\Repositories\Contracts;

/**
 * Interface RepositoryInterface
 */
interface RepositoryInterface
{
    public function setModelClass($modelClass);

    public function setValidatorClass($validatorClass);

    public function all();

    public function customFind($param);

    public function find($id);

    public function findWhere(array $where);

    public function create(array $attributes);

    public function update(array $attributes, $id);

    public function delete($id);

    public function flush();

    public function orderBy($column, $direction = 'asc');

    public function getCacheId();
}
