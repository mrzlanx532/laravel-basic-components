<?php

namespace Mrzlanx532\LaravelBasicComponents\PanelSet\Filters;

use Mrzlanx532\LaravelBasicComponents\PanelSet\PanelSet;
use Illuminate\Support\Carbon;

class DatetimeFilter extends BaseFilter
{
    private bool $isTimestamp = true;
    private bool $isConvertToUTCZero = true;

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

        $this->panelSet->queryBuilder->where(
            $this->columnName,
            $this->createCarbon($filterValueOrValues[0])->toDateTimeString(),
        );
    }

    private function createCarbon($value): Carbon
    {
        if ($this->getIsTimestamp()) {
            return Carbon::createFromTimestamp($value);
        }

        if ($this->isConvertToUTCZero) {
            return (Carbon::createFromFormat('d.m.Y H:i:sP', $value)->utc());
        }

        return Carbon::createFromFormat('d.m.Y H:i:sP', $value);
    }

    /**
     * (!) Условие работает только при `notTimestamp()`
     *
     * При передаче даты в формате: 01.12.2025 00:00:00+03:00,
     * в условие попадает дата 01.12.2025 00:00:00 без конвертации в UTC+0:00
     */
    public function notConvertToUTCZero(): static
    {
        $this->isConvertToUTCZero = false;

        return $this;
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
