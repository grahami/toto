<?php

namespace App\Repositories\Traits;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use App\Helpers\CacheKeysHelper;
use ReflectionObject;
use Exception;

/**
 * Trait CacheableRepository
 */
trait CacheableRepository
{

    protected $cacheRepository = null;

    public function setCacheRepository(CacheRepository $repository)
    {
        $this->cacheRepository = $repository;
        return $this;
    }

    public function getCacheRepository()
    {
        if (is_null($this->cacheRepository)) {
            $this->cacheRepository = app(config('repository.cache.repository', 'cache'));
        }

        return $this->cacheRepository;
    }

    public function skipCache($status = true)
    {
        $this->cacheSkip = $status;
        return $this;
    }

    public function isSkippedCache()
    {
        $skipped = isset($this->cacheSkip) ? $this->cacheSkip : false;
        $request = app('Illuminate\Http\Request');
        $skipCacheParam = config('repository.cache.params.skipCache', 'skipCache');

        if ($request->has($skipCacheParam) && $request->get($skipCacheParam)) {
            $skipped = true;
        }

        return $skipped;
    }

    protected function allowedCache($method)
    {
        $cacheEnabled = config('repository.cache.enabled', true);

        if (!$cacheEnabled) {
            return false;
        }

        $cacheOnly = isset($this->cacheOnly) ? $this->cacheOnly : config('repository.cache.allowed.only', null);
        $cacheExcept = isset($this->cacheExcept) ? $this->cacheExcept : config('repository.cache.allowed.except', null);

        if (is_array($cacheOnly)) {
            return in_array($method, $cacheOnly);
        }

        if (is_array($cacheExcept)) {
            return !in_array($method, $cacheExcept);
        }

        if (is_null($cacheOnly) && is_null($cacheExcept)) {
            return true;
        }

        return false;
    }

    public function getCacheKey($method, $args = null)
    {

        $request = app('Illuminate\Http\Request');
        $args = serialize($args);
        $key = sprintf('%s@%s-%s', $this->getCacheId(), $method, md5($args . $request->fullUrl()));
        if (!config("repository.cache.clean.redis", false)) {
            //The implementation of CacheKeysHelper uses a local file and is slow so accelerate for redis
            CacheKeysHelper::putKey(get_called_class(), $key);
        }

        return $key;
    }


    public function getCacheMinutes()
    {
        $cacheMinutes = isset($this->cacheMinutes) ? $this->cacheMinutes : config('repository.cache.minutes', 30);

        return $cacheMinutes;
    }

    public function all()
    {
        if (!$this->allowedCache('all') || $this->isSkippedCache()) {
            return parent::all();
        }

        $key = $this->getCacheKey('all', func_get_args());
        $minutes = $this->getCacheMinutes();
        $value = $this->getCacheRepository()->remember($key, $minutes, function () {
            return parent::all();
        });

        return $value;
    }

    public function find($id)
    {
        if (!$this->allowedCache('find') || $this->isSkippedCache()) {
            return parent::find($id);
        }

        $key = $this->getCacheKey('find', func_get_args());
        $minutes = $this->getCacheMinutes();
        $value = $this->getCacheRepository()->remember($key, $minutes, function () use ($id) {
            return parent::find($id);
        });

        return $value;
    }

    public function findWhere(array $where)
    {
        if (!$this->allowedCache('findWhere') || $this->isSkippedCache()) {
            return parent::findWhere($where);
        }

        $key = $this->getCacheKey('findWhere', func_get_args());
        $minutes = $this->getCacheMinutes();
        $value = $this->getCacheRepository()->remember($key, $minutes, function () use ($where) {
            return parent::findWhere($where);
        });

        return $value;
    }

}
