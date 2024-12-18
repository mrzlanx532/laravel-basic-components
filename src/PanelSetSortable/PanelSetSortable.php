<?php

namespace Mrzlanx532\LaravelBasicComponents\PanelSetSortable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

abstract class PanelSetSortable
{
    protected string $model;
    public string $resource;
    protected bool $isNested = false;
    public Builder $queryBuilder;
    protected string $orderField = 'order_index';
    protected string $parentField = 'parent_id';
    protected string $identifierField = 'id';

    public function handle(): array
    {
        $this->setQueryBuilder();

        return $this->prepareResponse();
    }

    private function prepareResponse(): array
    {
        $response['config'] = [
            'nested' => $this->isNested,
        ];

        $response['items'] = $this->isNested ?
            $this->buildTree($this->queryBuilder->get()) :
            $this->resource::collection($this->queryBuilder->get());

        return $response;
    }

    function buildTree(Collection $collection, $parentId = 0): \Illuminate\Support\Collection
    {
        $branch = new Collection();

        foreach ($collection as $elementKey => $element) {

            $collection[$elementKey] = new $this->resource($element);

            if ($element->{$this->parentField} == $parentId) {
                $items = $this->buildTree($collection, $element->{$this->identifierField});
                $element->items = new Collection();
                if ($items->isNotEmpty()) {
                    $element->items->push(...$items);
                }

                $branch->push(new $this->resource($element));
            }
        }

        return $branch;
    }

    private function setQueryBuilder()
    {
        $this->queryBuilder = $this->model::query();

        if (method_exists($this, 'setDefaultQuery')) {
            $this->setDefaultQuery();
        }

        $this->queryBuilder->orderBy($this->orderField);
    }

    public function getQueryBuilder(): Builder
    {
        $this->setQueryBuilder();

        return $this->queryBuilder;
    }

    public function getIsNested(): bool
    {
        return $this->isNested;
    }

    public function getOrderField(): string
    {
        return $this->orderField;
    }

    public function getParentField(): string
    {
        return $this->parentField;
    }

    public function getIdentifierField(): string
    {
        return $this->identifierField;
    }
}
