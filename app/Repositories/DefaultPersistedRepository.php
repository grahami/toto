<?php

namespace App\Repositories;

use App\Repositories\Base\BaseRepository;
use App\Repositories\Contracts\DefaultPersistedInterface;

/**
 * Class DefaultPersistedRepository
 */
class DefaultPersistedRepository extends BaseRepository implements DefaultPersistedInterface
{

    public function getValidator()
    {
        return $this->validatorClass;
    }
}
