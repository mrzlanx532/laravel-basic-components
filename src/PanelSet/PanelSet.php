<?php

namespace Mrzlanx532\LaravelBasicComponents\PanelSet;

use Closure;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Mrzlanx532\LaravelBasicComponents\PanelSet\Exceptions\InvalidPanelSetConfigurationException;
use Mrzlanx532\LaravelBasicComponents\PanelSet\Exceptions\InvalidJsonFormatForFiltersParameterException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

abstract class PanelSet
{
    public int $page = 1;
    public int $perPage = 20;
    public string|null $searchString = null;
    public array $orderBy = [];

    public array $filters = [];
    protected string|null $preset;
    protected array|string $columns = ['*'];
    protected array $defaultOrderBy = [];
    public array $availableOrderBy = [];

    protected string $model;
    public string $resource;
    public string $browserId;

    public array $fieldsForDefaultSearchFilter = [];
    public array $metaCustomParams = [];

    public Builder $queryBuilder;
    public LengthAwarePaginator $lengthAwarePaginator;

    public PanelSetFiltersManager $filtersManager;
    private PanelSetFrontendAdapter $frontendAdapter;

    public function __construct()
    {
        $this->filtersManager = new PanelSetFiltersManager($this);
        $this->frontendAdapter = new PanelSetFrontendAdapter($this);
    }

    /**
     * @throws InvalidPanelSetConfigurationException
     * @throws InvalidJsonFormatForFiltersParameterException
     * @throws ValidationException
     */
    public function handle(): array
    {
        $this->validatePanelSet();
        $this->setDefaultParamsFromRequest();

        $this->queryBuilder = $this->model::query();

        if (config('laravel_basic_components.panel_set_debug')) {
            DB::enableQueryLog();
        }

        if (method_exists($this,'setDefaultQuery')) {
            $this->setDefaultQuery();
        }

        if (method_exists($this,'setFilters')) {
            $this->setFilters();
        }

        $this->filtersManager
            ->addWhereByDefaultSearchFilter()
            ->addWhereQueriesByFilters();

        $this->setOrderBy();

        $this->lengthAwarePaginator = $this->queryBuilder->paginate(
            $this->perPage,
            $this->columns,
            $this->columns,
            $this->page
        );

        if (method_exists($this,'afterPaginateAction')) {
            $this->afterPaginateAction();
        }

        if (config('laravel_basic_components.panel_set_debug')) {
            Log::debug('Panel set debug', DB::getQueryLog());
        }

        return $this->frontendAdapter->getResult();
    }

    /**
     * Устанавливаем параметры в соответствии с документацией фрондента по Browser-компоненту
     *
     * @throws InvalidJsonFormatForFiltersParameterException
     */
    protected function setDefaultParamsFromRequest()
    {
        $this->page = request()->get('page') ?? $this->page;
        $this->perPage = request()->get('per_page') ?? $this->perPage;
        $this->searchString = request()->get('search_string') ?? $this->searchString;
        $this->preset = request()->get('preset') ?? null;

        if ($sort = request()->get('sort')) {
            $this->orderBy = json_decode($sort, true);

            if (json_last_error()) {
                throw new InvalidJsonFormatForFiltersParameterException('Неправильный формат json в переданном параметре `sort`');
            }
        }
    }

    protected function setOrderBy()
    {
        if ($this->orderBy && $this->availableOrderBy) {
            $this->setOrderByFromRequest();
            return;
        }

        $this->setDefaultOrderBy();
    }

    private function setOrderByFromRequest()
    {
        $preparedAvailableOrderBy = [];

        foreach ($this->availableOrderBy as $availableOrderByKey => $availableOrderByValue) {

            if ($availableOrderByValue instanceof Closure) {
                $preparedAvailableOrderBy[$availableOrderByKey] = $availableOrderByValue;
                continue;
            }

            if (stristr($availableOrderByValue, ' as ')) {
                $explodedOrderBy = explode(' as ', $availableOrderByValue);

                $preparedAvailableOrderBy[$explodedOrderBy[0]] = $explodedOrderBy[1];
                continue;
            }

            $preparedAvailableOrderBy[$availableOrderByValue] = $availableOrderByValue;
        }

        if (isset($preparedAvailableOrderBy[$this->orderBy['field']])) {

            if ($preparedAvailableOrderBy[$this->orderBy['field']] instanceof Closure) {
                $preparedAvailableOrderBy[$this->orderBy['field']]($this->queryBuilder, $this->orderBy['field'], $this->orderBy['direction']);
                return;
            }

            $this->queryBuilder->orderBy(
                $preparedAvailableOrderBy[$this->orderBy['field']], $this->orderBy['direction']
            );
        }
    }

    private function setDefaultOrderBy()
    {
        if (!$this->defaultOrderBy) {
            return;
        }

        foreach ($this->defaultOrderBy as $orderByField => $orderByDirection) {

            if ($orderByDirection instanceof Closure) {
                $this->defaultOrderBy[$orderByField]($this->queryBuilder);
                continue;
            }

            if (stristr($orderByField, ' as ')) {
                $this->queryBuilder->orderBy(
                    explode(' as ', $orderByField)[1],
                    $orderByDirection
                );
                continue;
            }

            $this->queryBuilder->orderBy($orderByField, $orderByDirection);
        }
    }

    /**
     * 1. Не установлен параметр $model
     * 2. Параметр $model не является классом
     * 3. Параметр $model не является моделью \Illuminate\Database\Eloquent\Model
     * 4. Параметр $resource не является классом
     * 5. Параметр $resource не является ресурсом \Illuminate\Http\Resources\Json\JsonResource
     *
     * @throws InvalidPanelSetConfigurationException
     */
    protected function validatePanelSet()
    {
        if (is_null($this->model)) {
            throw new InvalidPanelSetConfigurationException('Параметр $model не установлен в классе `' . $this::class . '`');
        }

        try {
            $model = new $this->model();
        } catch (Throwable) {
            throw new InvalidPanelSetConfigurationException('Параметр $model в классе `' . $this::class . '` не является классом');
        }

        if (!$model instanceof Model) {
            throw new InvalidPanelSetConfigurationException('Параметр $model в классе `' . $this::class . '` не является моделью');
        }

        if (is_null($this->resource)) {
            return;
        }

        try {
            $resource = new $this->resource([]);
        } catch (Throwable) {
            throw new InvalidPanelSetConfigurationException('Параметр $resource в классе `' . $this::class . '` не является классом');
        }

        if (!$resource instanceof JsonResource) {
            throw new InvalidPanelSetConfigurationException('Параметр $resource в классе `' . $this::class . '` не является ресурсом');
        }
    }

    public function setMetaCustomParams(array $params = [])
    {
        $this->metaCustomParams = $params;
    }
}