<?php

namespace Mrzlanx532\LaravelBasicComponents\PanelSet;

use Closure;
use Mrzlanx532\LaravelBasicComponents\PanelSet\Exceptions\InvalidJsonFormatForFiltersParameterException;
use Mrzlanx532\LaravelBasicComponents\PanelSet\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class PanelSetFiltersManager
{
    private PanelSet $panelSet;

    public function __construct(PanelSet $panelSet)
    {
        $this->panelSet = $panelSet;
    }

    /**
     * @throws InvalidJsonFormatForFiltersParameterException
     * @throws ValidationException
     */
    public function addWhereQueriesByFilters()
    {
        $passedFilters = json_decode(request()->get('filters'));

        if (request()->get('filters') !== null && json_last_error()) {
            throw new InvalidJsonFormatForFiltersParameterException('Неправильный формат json в переданном параметре `filters`');
        }

        $this->validateRequiredFields($passedFilters);

        if (!$passedFilters || !$this->panelSet->filters) {
            return;
        }

        foreach ($this->panelSet->filters as $filterKey => $filter) {
            if (!isset($this->panelSet->filters[$filter->getFilterParamName()])) {
                continue;
            }

            if (!isset($passedFilters->$filterKey)) {
                continue;
            }

            /* @var BaseFilter */
            $filterInstance = $this->panelSet->filters[$filter->getFilterParamName()];

            $customQueryClosure = $filterInstance->getCustomQueryClosure();

            if ($customQueryClosure instanceof Closure) {
                $customQueryClosure($this->panelSet->queryBuilder, $passedFilters->$filterKey);
                continue;
            }

            $filterInstance->setQuery($passedFilters->$filterKey);
        }
    }

    /**
     * @throws ValidationException
     */
    private function validateRequiredFields($passedFilters)
    {
        $missingFields = [];

        foreach ($this->panelSet->filters as $filterKey => $filter) {

            if (!$filter->getIsRequired()) {
                continue;
            }

            if (!isset($passedFilters->$filterKey)) {
                $missingFields[] = "'".$filter->getFilterParamName()."'";
                continue;
            }

            if (!$passedFilters->$filterKey) {
                $missingFields[] = "'".$filter->getFilterParamName()."'";
                continue;
            }

            if (!is_array($passedFilters->$filterKey)) {
                $missingFields[] = "'".$filter->getFilterParamName()."'";
                continue;
            }

            if ($passedFilters->$filterKey[0] === "") {
                $missingFields[] = "'".$filter->getFilterParamName()."'";
            }
        }

        if ($missingFields) {
            throw ValidationException::withMessages([
                'filters' => 'Не хватает обязательных фильтров: ' . implode(',', $missingFields)
            ]);
        }
    }

    public function add($typeOfFilterClass, $columnName, $title = null, Closure $closure = null)
    {
        /* @var $filterInstance BaseFilter */
        $filterInstance = new $typeOfFilterClass($this->panelSet, $columnName, $title);

        if ($closure) {
            $closure($filterInstance);
        }

        $this->panelSet->filters[$filterInstance->getFilterParamName()] = $filterInstance;

        return $this;
    }

    public function addWhereByDefaultSearchFilter(): static
    {
        if (!$this->panelSet->fieldsForDefaultSearchFilter || !$this->panelSet->searchString)
        {
            return $this;
        }

        $this->panelSet->queryBuilder->where(function (Builder $query) {
            foreach ($this->panelSet->fieldsForDefaultSearchFilter as $field) {
                if ($field instanceof Closure) {
                    $field($query, $this->panelSet->searchString);
                    continue;
                }
                $query->orWhere($field, 'like', '%' . $this->panelSet->searchString . '%');
            }
        });

        return $this;
    }
}