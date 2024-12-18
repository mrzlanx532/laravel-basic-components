<?php

namespace Mrzlanx532\LaravelBasicComponents\PanelSet\Filters;

class BooleanFilter extends BaseFilter
{
    private bool $isInversed = false;

    public function setQuery($filterValueOrValues)
    {
        $this->panelSet->queryBuilder->where($this->columnName, $this->isInversed ? '!=' : '=' ,$filterValueOrValues[0]);
    }

    public function getType(): string
    {
        return 'BOOLEAN';
    }

    public function getOptions(): array|null
    {
        return null;
    }

    public function inversed(): static
    {
        $this->isInversed = true;

        return $this;
    }
}