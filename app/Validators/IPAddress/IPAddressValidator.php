<?php

namespace app\Validators\IPAddress;

use App\Validators\Base\BaseValidator;

class IPAddressValidator extends BaseValidator
{
    protected $rules = [
        'ip_start' => 'require|integer',
        'ip_end' => 'require|integer',
        'geoname_id' => 'require|integer',
    ];


    public function passes($action = null)
    {
        $returnVal = parent::passes($action);

        return $returnVal;
    }

}