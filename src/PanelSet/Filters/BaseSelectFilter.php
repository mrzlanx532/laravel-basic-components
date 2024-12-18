<?php

namespace Mrzlanx532\LaravelBasicComponents\PanelSet\Filters;

use Mrzlanx532\LaravelBasicComponents\PanelSet\Exceptions\InvalidOptionsFormatException;

abstract class BaseSelectFilter extends BaseFilter
{
    protected array $options = [];

    public function setQuery($filterValueOrValues)
    {
        if ($this->isMultiple) {
            $this->panelSet->queryBuilder->whereIn($this->columnName, $filterValueOrValues);

            return;
        }

        $this->panelSet->queryBuilder->where($this->columnName, $filterValueOrValues[0]);
    }

    /**
     * @throws InvalidOptionsFormatException
     */
    public function setOptions(array $options): static
    {
        if (!$options) {
            $this->options = $options;

            return $this;
        }

        $errorMessage = 'Неверный формат для `options`. Каждый элемент массива `options` должен содержать `id` и `title`';

        if (!isset($options[0]['id']) || !isset($options[0]['title'])) {
            throw new InvalidOptionsFormatException($errorMessage);
        }

        if (!is_string($options[0]['id']) && !is_int($options[0]['id'])) {
            throw new InvalidOptionsFormatException($errorMessage);
        }

        if (!is_string($options[0]['title']) && !is_null($options[0]['title'])) {
            throw new InvalidOptionsFormatException($errorMessage);
        }

        $this->options = $options;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}