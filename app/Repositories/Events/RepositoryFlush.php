<?php
namespace App\Repositories\Events;

/**
 * Class RepositoryFlush
 */
class RepositoryFlush extends RepositoryEventBase
{
    protected $action = "flush";
}
