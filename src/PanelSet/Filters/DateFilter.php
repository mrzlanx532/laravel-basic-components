<?php

namespace Mrzlanx532\LaravelBasicComponents\PanelSet\Filters;

use Mrzlanx532\LaravelBasicComponents\PanelSet\PanelSet;
use Illuminate\Support\Carbon;

class DateFilter extends BaseFilter
{
    private bool $isInclusive = false;

    private string $timezone;

    public function __construct(PanelSet $panelSet, string $columnName, string $title = null)
    {
        parent::__construct($panelSet, $columnName, $title);

        $this->timezone = config('app.timezone');
    }

    public function getType(): string
    {
        return 'DATE';
    }

    public function setQuery($filterValueOrValues)
    {
        if ($this->getIsRange()) {

            if ($filterValueOrValues[0]) {
                $this->panelSet->queryBuilder
                    ->whereDate(
                        $this->columnName,
                        $this->getFirstValueComparisonSign(),
                        Carbon::parse($filterValueOrValues[0])->setTimezone($this->getTimezone())->toDateTimeString()
                    );
            }

            if ($filterValueOrValues[1]) {
                $this->panelSet->queryBuilder
                    ->whereDate(
                        $this->columnName,
                        $this->getSecondValueComparisonSign(),
                        Carbon::parse($filterValueOrValues[1])->setTimezone($this->getTimezone())->toDateTimeString()
                    );
            }

            return;
        }

        $this->panelSet->queryBuilder->whereDate(
            $this->columnName,
            '=',
            Carbon::createFromTimestamp($filterValueOrValues[0], $this->getTimezone())->toDateTimeString()
        );
    }

    private function getFirstValueComparisonSign(): string
    {
        if ($this->getIsInclusive()) {
            return '>=';
        }

        return '>';
    }

    public function setTimezone($timezone): static
    {
        $this->timezone = $timezone;

        return $this;
    }

    private function getSecondValueComparisonSign(): string
    {
        if ($this->getIsInclusive()) {
            return '<=';
        }

        return '<';
    }

    public function getOptions(): array|null
    {
        return null;
    }

    public function getIsInclusive(): bool
    {
        return $this->isInclusive;
    }

    public function inclusive(): static
    {
        $this->isInclusive = true;

        return $this;
    }

    private function getTimezone(): string
    {
        return $this->timezone;
    }
}
