<?php

namespace Mrzlanx532\LaravelBasicComponents\QueryBuilder;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

abstract class QueryBuilder
{
    protected array $params = [];
    protected array $rules = [];
    protected array $errorMessageAttributes = [];
    protected array $errorMessages = [];
    protected array $errors = [];
    protected Request $request;

    public function __construct()
    {
        $this->request = request();
    }

    abstract public function handle();

    /**
     * @param array|Request $paramsOrRequest
     * @return QueryBuilder
     * @throws ValidationException
     */
    public function setParams(array|Request $paramsOrRequest = []): QueryBuilder
    {
        $this->params = $paramsOrRequest instanceof Request ? $paramsOrRequest->all() : $paramsOrRequest;

        $this->validate();

        $this->unsetParams();

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
