<?php

namespace app\Validators\IPAddress;

use App\Validators\Base\BaseValidator;

class LocationValidator extends BaseValidator
{
    protected $rules = [
        'geoname_id' => 'require|integer',
        'continent' => 'required',
    ];


    public function passes($action = null)
    {
        $returnVal = parent::passes($action);

        return $returnVal;
    }

}