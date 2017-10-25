<?php

namespace App\Models\IPAddress;

use App\Models\Base\BaseModel;

class IPAddress extends BaseModel
{
    static $dbTable = 'ipaddress';

    static $persisted = ['ip_start', 'ip_end', 'geoname_id', 'postcode', 'latitude', 'longitude', 'accuracy'];

    static $autoCreate = [];

    static $nullable = [''];

    static $validatorClass = 'App\Validators\IPAddress\IPAddressValidator';

    protected $location; // The location model that is referred to by the geoname_id of the ipAddress

    protected $table = 'ipaddress';

    public function getLocation() {
        if (!isset($this->location)){
            $this->location = Location::findEntityWhere([['geoname_id', '=', $this->geoname_id]])->first();
        }
         return $this->location;
    }

}
