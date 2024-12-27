<?php

namespace Mrzlanx532\LaravelBasicComponents\PanelSet\Filters;

use Mrzlanx532\LaravelBasicComponents\PanelSet\PanelSet;
use Illuminate\Support\Carbon;

class DatetimeFilter extends BaseFilter
{
    private bool $isTimestamp = true;

    public function __construct(PanelSet $panelSet, string $columnName, string $title = null)
    {
        parent::__construct($panelSet, $columnName, $title);
    }

    public function getType(): string
    {
        return 'DATETIME';
    }

    public function setQuery($filterValueOrValues)
    {
        if ($this->getIsRange()) {
            if ($filterValueOrValues[0] && $filterValueOrValues[1]) {
                $this->panelSet->queryBuilder
                    ->whereBetween(
                        $this->columnName,
                        [
                            $this->createCarbon($filterValueOrValues[0])->toDateTimeString(),
                            $this->createCarbon($filterValueOrValues[1])->toDateTimeString(),
                        ]
                    );

                return;
            }

            if ($filterValueOrValues[0]) {
                $this->panelSet->queryBuilder->where($this->columnName, '>=', $this->createCarbon($filterValueOrValues[0])->toDateTimeString());
            }

            if ($filterValueOrValues[1]) {
                $this->panelSet->queryBuilder->where($this->columnName, '<=', $this->createCarbon($filterValueOrValues[1])->toDateTimeString());
            }

            return;
        }

        if ($this->getIsTimestamp()) {
            $this->panelSet->queryBuilder->whereBetween(
                $this->columnName,
                [
                    $this->createCarbon($filterValueOrValues[0])
                        ->toDateTimeString(),
                    $this->createCarbon($filterValueOrValues[0])
                        ->add(23, 'hours')
                        ->add(59, 'minutes')
                        ->add(59, 'seconds')
                        ->toDateTimeString(),
                ],
            );

            return;
        }

        $this->panelSet->queryBuilder->where(
            $this->columnName,
            $this->createCarbon($filterValueOrValues[0])->toDateTimeString(),
        );
    }

    private function createCarbon($value): Carbon
    {
        return $this->getIsTimestamp() ? Carbon::parse($value) : Carbon::createFromFormat('d.m.Y H:i', $value);
    }

    public function notTimestamp(): static
    {
        $this->isTimestamp = false;

        return $this;
    }

    public function getIsTimestamp(): bool
    {
        return $this->isTimestamp;
    }

    public function getOptions(): array|null
    {
        return null;
    }
}
