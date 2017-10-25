<?php

namespace App\Repositories;

use App\Repositories\Base\BaseRepository;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;
use App\Repositories\Contracts\DefaultCacheableInterface;

/**
 * Class DefaultCacheableRepository
 */
class DefaultCacheableRepository extends BaseRepository implements DefaultCacheableInterface, CacheableInterface
{
    use CacheableRepository;

    public function getValidator()
    {
        return $this->validatorClass;
    }

    public function getCacheId()
    {
        $returnVal = parent::getCacheId();
        return $returnVal;
    }
}
