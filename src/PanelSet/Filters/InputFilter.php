<?php

namespace Mrzlanx532\LaravelBasicComponents\PanelSet\Filters;

class InputFilter extends BaseFilter
{
    private bool $isExclusive = false;
    private string|null $mask = null;

    public function setQuery(array $filterValueOrValues)
    {
        if ($this->getIsRange()) {

            if ($filterValueOrValues[0] !== '') {
                $this->panelSet->queryBuilder->where(
                    $this->columnName,
                    $this->getFirstValueComparisonSign(),
                    $filterValueOrValues[0]
                );
            }

            if ($filterValueOrValues[1] !== '') {
                $this->panelSet->queryBuilder->where(
                    $this->columnName,
                    $this->getSecondValueComparisonSign(),
                    $filterValueOrValues[1]
                );
            }

            return;
        }

        $this->panelSet->queryBuilder->where($this->columnName, $filterValueOrValues[0]);
    }

    private function getFirstValueComparisonSign(): string
    {
        if ($this->getIsExclusive()) {
            return '>';
        }

        return '>=';
    }

    private function getSecondValueComparisonSign(): string
    {
        if ($this->getIsExclusive()) {
            return '<';
        }

        return '<=';
    }

    public function getIsExclusive(): bool
    {
        return $this->isExclusive;
    }

    public function exclusive(): static
    {
        $this->isExclusive = true;

        return $this;
    }

    public function getMask(): string|null
    {
        return $this->mask;
    }

    public function setMask($mask): static
    {
        $this->mask = $mask;

        return $this;
    }

    public function getOptions(): array|null
    {
        return [];
    }

    public function getType(): string
    {
        return 'INPUT';
    }
}