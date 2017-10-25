<?php

namespace App\Validators\Base;

use Illuminate\Support\MessageBag;
use App\Exceptions\ValidatorException;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use Log;

/**
 * Class BaseValidator
 */
class BaseValidator
{
    const RULE_CREATE = 'create';
    const RULE_UPDATE = 'update';

    protected $id = null;
    protected $validator;
    protected $data = array();
    protected $rules = array();
    protected $messages = array();
    protected $attributes = array();
    protected $errors = array();

    public function __construct(Factory $validator)
    {
        $this->validator = $validator;
        $this->errors = new MessageBag();
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function with(array $data)
    {
        $this->data = $data;

        return $this;
    }

    public function errors()
    {
        return $this->errorsBag()->all();
    }

    public function errorsBag()
    {
        return $this->errors;
    }

    public function passes($action = null)
    {
        $this->sanitize();
        $rules = $this->getRules($action);
        $messages = $this->getMessages();
        $attributes = $this->getAttributes();
        $validator = $this->validator->make($this->data, $rules, $messages, $attributes);

        if ($validator->fails()) {
            $this->errors = $validator->messages();
            return false;
        }

        return true;
    }

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ValidatorException($this->errorsBag());
        }

        return true;
    }

    // implements the same method that Laravel's own class use with
    // Laravel default validators
    public function validate()
    {
        $this->sanitize();
        $rules = $this->getRules();
        $messages = $this->getMessages();
        $attributes = $this->getAttributes();
        $validator = $this->validator->make($this->data, [], [], []);

        if (!$this->passes()){
            $validator->getMessageBag()->merge($this->errors);
            throw new ValidationException($validator);
        }
    }

    public function throwProcessFailureValidation($errors)
    {
        $validator = $this->validator->make([], [], [], []);

        $messageBag = new MessageBag();
        foreach ($errors as $errorKey => $errorMessage){
            $messageBag->add($errorKey, $errorMessage);
        }

        $validator->getMessageBag()->merge($messageBag);
        throw new ValidationException($validator);
    }


    public function getRules($action = null)
    {

        $rules = $this->rules;

        // if there is an action try to get action specific rules
        if (isset($action)){
            if (isset($this->rules[$action])) {
                $rules = $this->rules[$action];
            } else {
                // default to create rules if they exist
                if (isset($this->rules['create'])) {
                    $rules = $this->rules['create'];
                }
            }
        } else {
            // if the rules have action specific values and no action was passed in
            // default to the create rules
            if (isset($this->rules['create'])) {
                $rules = $this->rules['create'];
            }
        }


        return $this->parserValidationRules($rules, $this->id);
    }

    public function setRules(array $rules)
    {
        $this->rules = $rules;
        return $this;
    }

    public function getMessages()
    {

        return $this->messages;
    }

    public function setMessages(array $messages)
    {
        $this->messages = $messages;
        return $this;
    }

    public function getAttributes()
    {

        return $this->attributes;
    }

    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    protected function parserValidationRules($rules, $id = null)
    {

        if ($id === null) {
            return $rules;
        }

        array_walk($rules, function (&$rules, $field) use ($id) {
            if (!is_array($rules)) {
                $rules = explode("|", $rules);
            }

            // Handle the unique rule since it compares to existing persistent data, rather than just some
            // deterministic operation on the data.
            // Unique has the form unique:connection.table,field_to_check,except,id_column
            // We want to explicitly add the field if it is not in the unique clause and we want to
            // explicitly add the id if it was passed in. This gives us the information we then need
            // to do the validation against whatever backend the repository uses
            foreach ($rules as $ruleIdx => $rule) {
                // get the rule name and parameters
                @list($ruleName, $params) = array_pad(explode(":", $rule), 2, null);

                if (strtolower($ruleName) != "unique") {
                    continue;
                }

                $paramList = array_map("trim", explode(",", $params));

                if (!isset($paramList[1])) {
                    $paramList[1] = $field;
                }

                $paramList[2] = $id;

                $params = implode(",", $paramList);
                $rules[$ruleIdx] = $ruleName . ":" . $params;
            }
        });

        return $rules;
    }

    protected function sanitize()
    {
        foreach ($this->data as $dataKey => $dataValue){
            $this->data[$dataKey] = filter_var($dataValue, FILTER_SANITIZE_STRING);
        }
    }
}