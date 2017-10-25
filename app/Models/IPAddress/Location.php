<?php

namespace App\Models\IPAddress;

use App\Models\Base\BaseModel;

class Location extends BaseModel
{
    static $dbTable = 'location';

    static $persisted = ['geoname_id', 'continent', 'country', 'region', 'city'];

    static $autoCreate = [];

    static $nullable = [''];

    static $validatorClass = 'App\Validators\IPAddress\LocationValidator';

    protected $table = 'location';


}
