<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * Interface CacheableInterface
 */
interface CacheableInterface
{
    public function setCacheRepository(CacheRepository $repository);

    public function getCacheRepository();

    public function getCacheKey($method, $args = null);

    public function getCacheMinutes();

    public function skipCache($status = true);
}
