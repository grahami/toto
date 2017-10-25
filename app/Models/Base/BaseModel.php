<?php
/**
 * A repository based model and has some extended methods on top of a standard Eloquent model
 */

namespace App\Models\Base;

use App\Exceptions\DBException;
use Illuminate\Database\Eloquent\Model;
use App\Library\EventHelper;
use DB;
use Auth;
use App;
use Carbon\Carbon;
use Log;

class BaseModel extends Model
{
    // nothing is guarded apart from the id so we can do quick assignments with the fill method
    protected $guarded = ['id'];

    // the keys that should be set to null if empty
    static $nullable = [];

    // the fields that the model has that are persisted. If empty then attributes must be passed to
    // updateEntity, otherwise uf the attributes is empty, use the persisted fields instead
    static $persisted = [];

    static $repositoryClass = 'App\Repositories\DefaultCacheableRepository';

    static $validatorClass = 'App\Validators\Base\BaseValidator';

    static $dbTable = '';

    public static function findAll()
    {
        $repository = new static::$repositoryClass(get_called_class(), static::$validatorClass);
        $returnVal = $repository->all();
        return $returnVal;
    }

    public static function findEntity($id)
    {
        $repository = new static::$repositoryClass(get_called_class(), static::$validatorClass);
        $returnVal = $repository->find($id);
        return $returnVal;
    }

    public static function findCustom($param)
    {
        $repository = new static::$repositoryClass(get_called_class(), static::$validatorClass);
        $returnVal = $repository->customFind($param);
        return $returnVal;
    }

    public static function findEntityWhere(array $where)
    {
        $repository = new static::$repositoryClass(get_called_class(), static::$validatorClass);
        $modelName = $repository->shortModelClass();
        $returnVal = $repository->findWhere($where);
        return $returnVal;
    }


    public static function createEntity($attributes)
    {
        $repository = new static::$repositoryClass(get_called_class(), static::$validatorClass);
        $returnVal = $repository->create($attributes);
        return $returnVal;
    }

    public function updateEntity($attributes=array())
    {
        $returnVal = null;
        $repository = new static::$repositoryClass(get_called_class(), static::$validatorClass);
        $idName = $this->getKeyName();
        if (count(get_called_class()::$persisted) > 0){
            foreach (get_called_class()::$persisted as $fieldName){
                if (!isset($attributes[$fieldName])) {
                    $attributes[$fieldName] = $this->$fieldName;
                }
            }
        }
        $returnVal = $repository->update($attributes, $this->$idName);
        return $returnVal;
    }

    public function deleteEntity()
    {
        $repository = new static::$repositoryClass(get_called_class(), static::$validatorClass);
        $idName = $this->getKeyName();

        $returnVal = $repository->delete($this->$idName);
        return $returnVal;
    }

    public static function getAttributesAsParameters($attributes, $id=null)
    {
        $parameters = array();
        if (isset($id)){
            $parameters['id'] = $id;
        }
        foreach (get_called_class()::$persisted as $fieldName){
            if (!in_array($fieldName, get_called_class()::$autoCreate)){
                if (isset($attributes[$fieldName])){
                    $parameters[$fieldName] = filter_var(trim($attributes[$fieldName]), FILTER_SANITIZE_STRING,
                        array('flags' => FILTER_FLAG_NO_ENCODE_QUOTES));
                } else {
                    if (in_array($fieldName, get_called_class()::$nullable)) {
                        $parameters[$fieldName] = null;
                    } else {
                        $parameters[$fieldName] = '';
                    }
                }
            }
        }
        return $parameters;
    }

    public function getAsParameters($includeAuto = false)
    {
        $parameters = array();
        $parameters['id'] = $this->id;
        foreach (get_called_class()::$persisted as $fieldName){
            if ($includeAuto || !in_array($fieldName, get_called_class()::$autoCreate)){
                $parameters[$fieldName] = filter_var($this->$fieldName, FILTER_SANITIZE_STRING);
                if (strlen($this->$fieldName) == 0){
                    if (in_array($fieldName, get_called_class()::$nullable)) {
                        $parameters[$fieldName] = null;
                    } else {
                        $parameters[$fieldName] = '';
                    }
                }
            }
        }
        return $parameters;
    }

    public static function getDefaults($action = 'ADD'){
        return array();
    }
}
