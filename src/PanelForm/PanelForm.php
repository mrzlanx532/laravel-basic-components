<?php

namespace Mrzlanx532\LaravelBasicComponents\PanelForm;

use Mrzlanx532\LaravelBasicComponents\PanelForm\Exceptions\InvalidPanelFormConfigurationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Throwable;

abstract class PanelForm
{
    protected Request $request;
    protected array $result = [];
    protected string $requestEntityIdentifier = 'id';
    protected mixed $arbitraryEntityIdentifierValue = null;
    protected Model|null $entity = null;
    public Builder|null $queryBuilder;

    protected string $model;
    protected string|null $resource = null;
    protected array $with = [];

    abstract protected function getInputs(): array;

    /**
     * @throws InvalidPanelFormConfigurationException
     */
    public function __construct(Request $request)
    {
        $this->validateProperties();
        $this->request = $request;
    }

    protected function setModelDataByResourceToEntity(): void
    {
        /* @var $modelInstance Model */
        $modelInstance = new $this->model;

        if (!$this->request->filled($this->requestEntityIdentifier) && is_null($this->arbitraryEntityIdentifierValue)) {
            return;
        }

        $this->queryBuilder = $modelInstance::query();

        if (method_exists($this,'setDefaultQuery')) {
            $this->setDefaultQuery();
        }

        $this->entity = $this->queryBuilder
            ->with($this->with)
            ->where(
                $modelInstance->getTable() . '.' . $this->requestEntityIdentifier,
                $this->arbitraryEntityIdentifierValue ?? $this->request->get($this->requestEntityIdentifier)
            )
            ->firstOrFail();

        if (method_exists($this, 'afterGetEntityHook')) {
            $this->afterGetEntityHook();
        }

        $this->result['entity'] = is_null($this->resource) ? $this->entity : new $this->resource($this->entity);
    }

    private function fillInputs(): void
    {
        $passedInputs = $this->getInputs();

        foreach ($passedInputs as $key => $input) {
            $this->result[$key] = $input;
        }
    }

    public function get(): array
    {
        $this->setModelDataByResourceToEntity();
        $this->fillInputs();

        return $this->result;
    }

    /**
     * 1. Свойство $model не является строкой, из которой можно создать экземляр модели (Illuminate\Database\Eloquent\Model)
     * 2. Свойство $model не является строкой, из которой можно создать экземляр модели (Illuminate\Database\Eloquent\Model)
     * 3. Свойство $resource не является строкой, из которой можно создать экземляр ресурса (Illuminate\Http\Resources\Json\JsonResource)
     *
     * @throws InvalidPanelFormConfigurationException
     */
    private function validateProperties(): void
    {
        try {
            $modelInstance = new $this->model();
        } catch (Throwable) {
            throw new InvalidPanelFormConfigurationException('Свойство $model не является строкой, из которой можно создать экземляр модели (Illuminate\Database\Eloquent\Model)');
        }

        if (!$modelInstance instanceof Model) {
            throw new InvalidPanelFormConfigurationException('Свойство $model не является строкой, из которой можно создать экземляр модели (Illuminate\Database\Eloquent\Model)');
        }

        if (is_null($this->resource)) {
            return;
        }

        $resourceInstance = new $this->resource([]);

        if (!$resourceInstance instanceof JsonResource) {
            throw new InvalidPanelFormConfigurationException('Свойство $resource не является строкой, из которой можно создать экземляр ресурса (Illuminate\Http\Resources\Json\JsonResource)');
        }
    }

    protected function setArbitraryEntityIdentifierValue($value)
    {
        $this->arbitraryEntityIdentifierValue = $value;
    }
}
