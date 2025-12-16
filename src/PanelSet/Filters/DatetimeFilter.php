<?php

namespace Mrzlanx532\LaravelBasicComponents\PanelSet\Filters;

use Mrzlanx532\LaravelBasicComponents\PanelSet\PanelSet;
use Illuminate\Support\Carbon;

class DatetimeFilter extends BaseFilter
{
    const STRATEGY_TIMESTAMP = 'timestamp';
    const STRATEGY_DATETIME_WITH_TZ = 'datetime_with_tz';
    const STRATEGY_DATETIME_WITH_TZ_CONVERTLESS = 'datetime_with_tz_convertless';

    /**
     * @var 'timestamp' | 'datetime_with_tz' | 'datetime_with_tz_convertless'
     */
    private string $strategy = self::STRATEGY_TIMESTAMP;

    public function __construct(PanelSet $panelSet, string $columnName, string $title = null)
    {
        parent::__construct($panelSet, $columnName, $title);
    }

    public function getType(): string
    {
        return 'DATETIME';
    }

    public function setQuery($filterValueOrValues): void
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
                return;
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
        return match ($this->strategy) {
            /** Для timestamp переданного в UTC+00:00. */
            self::STRATEGY_TIMESTAMP => Carbon::createFromTimestamp($value),
            /** Для типа колонки datetime (mysql, postgresql), приводим переданный datetime к UTC+00:00. */
            self::STRATEGY_DATETIME_WITH_TZ => Carbon::createFromFormat('d.m.Y H:i:sP', $value)->utc(),
            /** Для типа колонки datetime (mysql, postgresql), не приводим переданный datetime к UTC+00:00. */
            self::STRATEGY_DATETIME_WITH_TZ_CONVERTLESS => Carbon::createFromFormat('d.m.Y H:i:sP', $value),
        };
    }

    public function setStrategy($strategy): static
    {
        $this->strategy = $strategy;

        return $this;
    }

    public function getStrategy(): string
    {
        return $this->strategy;
    }

    public function getOptions(): array|null
    {
        return null;
    }
}
