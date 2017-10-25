<?php
/**
 * The base repository uses Eloquent as a simple example but could use a stored procedure for MySQL or could go to a
 * NoSQL database or some other data retrieval such as a queue for FIFO data
 */

namespace App\Repositories\Base;

use Illuminate\Container\Container as Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Events\RepositoryEntityCreated;
use App\Repositories\Events\RepositoryEntityDeleted;
use App\Repositories\Events\RepositoryEntityUpdated;
use App\Repositories\Events\RepositoryFlush;
use App\Validators\Base\BaseValidator;
use App\Exceptions\ValidatorException;
use App\Exceptions\RepositoryException;
use DB;

/**
 * Class BaseRepository
 */
abstract class BaseRepository implements RepositoryInterface
{

    protected $app;

    protected $model;
    protected $modelClass;

    protected $validator;
    protected $validatorClass;

    protected $rules = null;

    /**
     * BaseRepository constructor. Sets the model class and validator that the repository makes use of
     * @param string $modelClass
     * @param string $validatorClass
     */
    public function __construct($modelClass = '', $validatorClass = '')
    {
        $this->app = app();
        if (strlen($modelClass) > 0) {
            $this->setModelClass($modelClass);
        }
        $this->makeModel();
        if (strlen($validatorClass) > 0) {
            $this->setValidatorClass($validatorClass);
        }
        $this->makeValidator();
        $this->boot();
    }

    /**
     * Set the model class for use later
     * @param string $modelClass The name of the model class
     */
    public function setModelClass($modelClass)
    {
        $this->modelClass = $modelClass;
    }

    /**
     * Set the Validator class for use layer - validators are not currently used in this sample but
     * are included as an example
     * @param string $validatorClass
     */
    public function setValidatorClass($validatorClass)
    {
        $this->validatorClass = $validatorClass;
    }

    public function boot()
    {

    }

    /**
     * Get the modelClass for the repository
     * @return string The modelClass for the repository
     */
    public function modelClass()
    {
        return $this->modelClass;
    }

    /**
     * Return the short version of the model class without full qualification
     * @return bool|string False if no model class is set, otherwise the non qualified model class name
     */
    public function shortModelClass()
    {
        $returnVal = $this->modelClass;
        // remove the full qualification to only have the shortname
        $lastSlash = strrpos($returnVal, '\\');
        if ($lastSlash !== false) {
            $returnVal = substr($returnVal, $lastSlash + 1);
        }
        return $returnVal;
    }

    /**
     * Return the Validator for the repository
     * @return mixed|null The validator if it is set
     */
    public function getValidator()
    {
        if (isset($this->rules) && !is_null($this->rules) && is_array($this->rules) && !empty($this->rules)) {
            $validator = app('App\Validators\Base\BaseValidator');
            if ($validator instanceof BaseValidator) {
                $validator->setRules($this->rules);

                return $validator;
            }
        }

        return null;
    }

    /**
     * Instantiate an instance of the model class
     * @return Model|mixed
     */
    public function makeModel()
    {
        $model = $this->app->make($this->modelClass());

        if (!$model instanceof Model) {
            throw new RepositoryException("Class {$this->modelClass()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /**
     * Instantiate or use a custom validator that is passed in. This allows for custom validation from a controller
     * while also making use of the default validator for the repository/model
     * @param null $validator An optional validator passed in from the calling code
     * @return BaseValidator|null
     */
    public function makeValidator($validator = null)
    {
        $validator = !is_null($validator) ? $validator : $this->getValidator();

        if (!is_null($validator)) {
            $this->validator = is_string($validator) ? $this->app->make($validator) : $validator;

            if (!$this->validator instanceof BaseValidator) {
                throw new RepositoryException("Class {$validator} must be an instance of App\\Validators\\Base\\BaseValidator");
            }

            return $this->validator;
        }

        return null;
    }

    /**
     * Return all model records for the repository
     * @return collection
     */
    public function all()
    {
        $modelClass = $this->modelClass();

        $results = $modelClass::all();

        return $results;
    }

    /**
     * Find a single model record using the unique ID
     * @param integer $id The unique ID
     * @return mixed The model if found or null
     */
    public function find($id)
    {
        $modelClass = $this->modelClass();

        $model = $modelClass::find($id);

        return $model;
    }

    /**
     * Use a prepared SQL statement for handling a potentially complex where condition
     * @param array $where An array of arrays containing the field, condition and value to use in building
     *                      a where clause
     * @return \Illuminate\Support\Collection
     */
    public function findWhere(array $where)
    {
        $modelClass = $this->modelClass();
        $tableName = $modelClass::$dbTable;

        $whereClause = '';
        $whereJoin = ' WHERE ';
        $whereValues = [];
        foreach ($where as $field => $value) {
            if (is_array($value)) {
                list($field, $condition, $whereValue) = $value;
                $value = $whereValue;
            } else {
                $condition = '=';
            }
            //sanitize every value every time
            $value = filter_var($value, FILTER_SANITIZE_STRING);

            $whereClause .= $whereJoin . '`' . $field . '` ' . $condition . ' :' . $field;
            $whereValues[$field] = $value;
            $whereJoin = ' AND ';
        }
        $rawObjects = DB::select('SELECT * FROM ' . $tableName . $whereClause, $whereValues);

        $results = array();
        foreach ($rawObjects as $rawObject) {
            $returnAttributes = $rawObject;
            $model = $modelClass::hydrate([$returnAttributes])->first();
            $results[] = $model;
        }
        return collect($results);
    }

    /**
     * A Custom find method that uses a stored procedure in this case, but could support any appropriate
     * functionality
     * @param string $param An optional parameter for identifying the required model or models
     * @return \Illuminate\Support\Collection
     */
    public function customFind($param = '')
    {
        $modelClass = $this->modelClass();
        $tableName = $modelClass::$dbTable;

        $rawObjects = DB::select('CALL '.$tableName . 'Find(:param)', ['param' => $param]);

        $results = array();
        foreach ($rawObjects as $rawObject) {
            $returnAttributes = $rawObject;
            $model = $modelClass::hydrate([$returnAttributes])->first();
            $results[] = $model;
        }
        return collect($results);
    }

    /**
     * Create a new model instance record - not implemented but just a stub
     * @param array $attributes The values for the persisted attributes of the model
     * @return mixed|null Would return the model instance that was created
     */
    public function create(array $attributes)
    {
        $modelClass = $this->modelClass();

        $model = null;

        return $model;
    }

    /**
     * Update an existing model instance record - not implemented but just a stub
     * @param array $attributes The values for the persisted attributes of the model
     * @param integer $id The unique id of the model instance
     * @return mixed|null Would return the model instance that was updated with new values in place
     */
    public function update(array $attributes, $id)
    {
        $modelClass = $this->modelClass();

        $model = null;

        return $model;

    }

    /**
     * Delete an existing model instance record - not implemented but just a stub
     * @param integer $id The unique id of the model instance
     * @return mixed|null Would return the model instance that was deleted
     */
    public function delete($id)
    {
        $modelClass = $this->modelClass();

        $returnVal = true;

        return $returnVal;
    }

    /**
     * Flush any cached data if the repository in use is a cacheable repository descended from this base class
     * @return int
     */
    public function flush()
    {
        event(new RepositoryFlush($this, $this->model));
        return 0;
    }

    /**
     * Order the resulting rows by a column name - useful for in app as opposed to in DB ordering
     * @param string $column The column to order by
     * @param string $direction asc = Ascending, desc = Descending
     * @return $this
     */
    public function orderBy($column, $direction = 'asc')
    {
        $this->model = $this->model->orderBy($column, $direction);

        return $this;
    }

    /**
     * Get the ID to use for the cache key. This method can be overridden if the class shortname is not appropriate
     * for some reason
     *
     * @return string   The ID for the cache key, defaulted to the called_class
     */
    public function getCacheId()
    {
        $returnVal = $this->shortModelClass();
        return $returnVal;
    }

}
