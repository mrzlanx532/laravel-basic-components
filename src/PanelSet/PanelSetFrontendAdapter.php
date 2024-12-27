<?php

namespace Mrzlanx532\LaravelBasicComponents\PanelSet;

use Closure;
use Mrzlanx532\LaravelBasicComponents\PanelSet\Filters\BaseFilter;
use Mrzlanx532\LaravelBasicComponents\PanelSet\Filters\DateFilter;
use Mrzlanx532\LaravelBasicComponents\Service\BrowserFilterPreset\BrowserFilterPresetBaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class PanelSetFrontendAdapter
{
    private PanelSet $panelSet;

    public function __construct(PanelSet $panelSet)
    {
        $this->panelSet = $panelSet;
    }

    public function getResult(): array
    {
        return [
            'items' => $this->getItems($this->panelSet->lengthAwarePaginator),
            'meta' => $this->getMeta($this->panelSet->lengthAwarePaginator),
            'filters' => $this->getFilters($this->panelSet->filters),
            'preset_groups' => method_exists($this->panelSet, 'getPresetGroups') ? $this->panelSet->getPresetGroups() : [],
            'presets_default' => method_exists($this->panelSet, 'getDefaultPresets') ? $this->panelSet->getDefaultPresets() : [],
            'presets_user' => $this->getUserPresets(),
        ];
    }

    protected function getItems(LengthAwarePaginator $paginator): AnonymousResourceCollection|array
    {
        if ($this->panelSet->resource) {
            return $this->panelSet->resource::collection($paginator->getCollection());
        }

        return $paginator->items();
    }

    protected function getMeta(LengthAwarePaginator $paginator): array
    {
        $sort = [];

        foreach ($this->panelSet->availableOrderBy as $availableOrderByKey => $availableOrderByValue) {
            if ($availableOrderByValue instanceof Closure) {
                $sort[] = $availableOrderByKey;
                continue;
            }

            if (stristr($availableOrderByValue, ' as ')) {
                $sort[] = explode(' as ', $availableOrderByValue)[0];
                continue;
            }

            $sort[] = $availableOrderByValue;
        }

        return [
            'browser_id' => $this->panelSet->browserId,
            'count' => $paginator->total(),
            'pages' => $paginator->lastPage(),
            'per_page' => $this->panelSet->perPage,
            'page' => $this->panelSet->page,
            'searchable' => (bool)$this->panelSet->fieldsForDefaultSearchFilter,
            'sort' => $sort,
            'custom' => $this->panelSet->metaCustomParams
        ];
    }

    protected function getFilters($filters): array
    {
        if (!$filters) {
            return [];
        }

        $preparedFilters = [];
        $index = 0;

        foreach ($filters as $filter) {

            /* @var $filter BaseFilter */
            $config = [
                'hidden' => $filter->getIsHidden(),
                'multiple' => $filter->getIsMultiple(),
                'range' => $filter->getIsRange(),
                'url' =>  method_exists($filter, 'getUrl') ? $filter->getUrl() : "",
                'filter' => $filter->getIsFiltering(),
                'mask' => method_exists($filter, 'getMask') ? $filter->getMask() : null,
                'nullable' => $filter->getIsNullable()
            ];

            if ($filter->getType() === 'DATE') {
                /* @var $filter DateFilter */
                $config['is_timestamp'] = $filter->getIsTimestamp();
            }

            $preparedFilters[$index]['id'] = $filter->getFilterParamName();
            $preparedFilters[$index]['title'] = $filter->getTitle();
            $preparedFilters[$index]['options'] = $filter->getOptions();
            $preparedFilters[$index]['type'] = $filter->getType();
            $preparedFilters[$index]['config'] = $config;
            $index++;
        }

        return $preparedFilters;
    }

    private function getUserPresets(): Collection
    {
        if (!config('laravel_basic_components.use_presets')) {
            return new Collection;
        }

        /* @var $queryBuilder Builder */
        $queryBuilder = BrowserFilterPresetBaseService::$browserFilterPresetModel::query();

        $presets = $queryBuilder->where('ident', $this->panelSet->browserId)->get();

        foreach($presets as $preset) {
            $preset->filters = json_decode($preset->filters, true);
        }

        return $presets;
    }
}