<?php

namespace Mrzlanx532\LaravelBasicComponents\Service;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

abstract class Service
{
    protected array $params = [];
    protected array $rules = [];
    protected array $errorMessageAttributes = [];
    protected array $errorMessages = [];
    protected array $errors = [];
    protected bool $isUnsetParams = true;

    abstract public function handle();

    /**
     * @param array|Request $paramsOrRequest
     * @return Service
     * @throws ValidationException
     */
    public function setParams(array|Request $paramsOrRequest = []): Service
    {
        $this->params = $paramsOrRequest instanceof Request ? $paramsOrRequest->all() : $paramsOrRequest;

        if (method_exists($this, 'getPreparedParamsBeforeValidation')) {
            $this->params = array_merge($this->params, $this->getPreparedParamsBeforeValidation());
        }

        $this->validate();

        if ($this->isUnsetParams) {
            $this->unsetParams();
        }

        return $this;
    }

    /**
     * @throws ValidationException
     */
    public function validate()
    {
        if (method_exists($this, 'getRules')) {
            $this->rules = $this->getRules();
        }

        $validator = Validator::make($this->params, $this->rules, $this->getErrorMessages(), $this->errorMessageAttributes);

        foreach ($validator->errors()->all() as $index => $message) {
            $this->errors[$index] = $message;
        }

        $validator->validate();
    }

    protected function getErrorMessages(): array
    {
        return $this->errorMessages;
    }

    private function unsetParams()
    {
        foreach ($this->params as $paramKey => $paramValue) {

            if (is_array($paramValue)) {
                continue;
            }

            if (!isset($this->rules[$paramKey])) {
                unset($this->params[$paramKey]);
            }
        }
    }
}
