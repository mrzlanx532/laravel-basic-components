<?php

namespace Mrzlanx532\LaravelBasicComponents\PanelSet\Filters;

use Mrzlanx532\LaravelBasicComponents\PanelSet\PanelSet;
use Closure;

abstract class BaseFilter
{
    protected string $columnName;
    protected string $filterParamName;
    protected string $title;
    protected PanelSet $panelSet;
    protected bool $isHidden = false;
    protected bool $isMultiple = false;
    protected bool $isRange = false;
    protected bool $isRequired = false;
    protected bool $isFiltering = false;
    protected Closure|null $customQueryClosure = null;

    abstract public function setQuery(array $filterValueOrValues);

    abstract public function getType(): string;

    abstract public function getOptions(): array|null;

    public function __construct(PanelSet $panelSet, string $columnName, string $title = null)
    {
        $this->panelSet = $panelSet;
        $this->columnName = $columnName;
        $this->filterParamName = $columnName;
        $this->title = $title ?: $columnName;
    }

    public function setColumnName($columnName): static
    {
        $this->columnName = $columnName;

        return $this;
    }

    public function setFilterParamName($filterParamName): static
    {
        $this->filterParamName = $filterParamName;

        return $this;
    }

    public function getColumnName(): string
    {
        return $this->columnName;
    }

    public function getFilterParamName(): string
    {
        return $this->filterParamName;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getIsHidden(): bool
    {
        return $this->isHidden;
    }

    public function getIsRequired(): bool
    {
        return $this->isRequired;
    }

    public function getIsMultiple(): bool
    {
        return $this->isMultiple;
    }

    public function getIsRange(): bool
    {
        return $this->isRange;
    }

    public function getIsFiltering(): bool
    {
        return $this->isFiltering;
    }

    public function multiple(): static
    {
        $this->isMultiple = true;

        return $this;
    }

    public function hidden(): static
    {
        $this->isHidden = true;

        return $this;
    }

    public function range(): static
    {
        $this->isRange = true;

        return $this;
    }

    public function required(): static
    {
        $this->isRequired = true;

        return $this;
    }

    public function filtering(): static
    {
        $this->isFiltering = true;

        return $this;
    }

    public function setCustomQueryClosure(Closure $closure): static
    {
        $this->customQueryClosure = $closure;

        return $this;
    }

    public function getCustomQueryClosure(): Closure|null
    {
        return $this->customQueryClosure;
    }
}