<?php
namespace App\Repositories\Events;

use Illuminate\Database\Eloquent\Model;
use App\Repositories\Contracts\RepositoryInterface;

/**
 * Class RepositoryEventBase
 */
abstract class RepositoryEventBase
{
    protected $model;

    protected $repository;

    protected $action;

    public function __construct(RepositoryInterface $repository, Model $model)
    {
        $this->repository = $repository;
        $this->model = $model;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function getRepository()
    {
        return $this->repository;
    }

    public function getAction()
    {
        return $this->action;
    }
}
