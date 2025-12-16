<?php

namespace Mrzlanx532\LaravelBasicComponents\PanelSet\Filters;

use Mrzlanx532\LaravelBasicComponents\PanelSet\PanelSet;
use Illuminate\Support\Carbon;

class DateFilter extends BaseFilter
{
    const STRATEGY_TIMESTAMP = 'timestamp';
    const STRATEGY_DATETIME_WITH_TZ = 'datetime_with_tz';
    const STRATEGY_DATETIME_WITH_TZ_CONVERTLESS = 'datetime_with_tz_convertless';
    const STRATEGY_DATE = 'date';

    /**
     * @var 'timestamp' | 'datetime_with_tz' | 'datetime_with_tz_convertless' | 'date'
     */
    private string $strategy = self::STRATEGY_TIMESTAMP;

    public function __construct(PanelSet $panelSet, string $columnName, string $title = null)
    {
        parent::__construct($panelSet, $columnName, $title);
    }

    public function getType(): string
    {
        return 'DATE';
    }

    public function setQuery($filterValueOrValues): void
    {
        match ($this->getStrategy()) {
            /** Для timestamp переданного в UTC+00:00. */
            self::STRATEGY_TIMESTAMP => $this->setQueryByTimestampStrategy($filterValueOrValues),
            /** Для типа колонки date (mysql, postgresql). */
            self::STRATEGY_DATE => $this->setQueryByDateStrategy($filterValueOrValues),
            /** Для типа колонки datetime (mysql, postgresql), приводим переданный datetime к UTC+00:00. */
            self::STRATEGY_DATETIME_WITH_TZ,
            /** Для типа datetime (mysql, postgresql), не приводим переданный datetime к UTC+00:00. */
            self::STRATEGY_DATETIME_WITH_TZ_CONVERTLESS => $this->setQueryByDatetimeWithTZStrategy($filterValueOrValues),
        };
    }

    private function createCarbon($value): Carbon
    {
        return match ($this->strategy) {
            self::STRATEGY_TIMESTAMP => Carbon::createFromTimestamp($value),
            self::STRATEGY_DATE => Carbon::createFromFormat('d.m.Y', $value),
            self::STRATEGY_DATETIME_WITH_TZ => Carbon::createFromFormat('d.m.Y H:i:sP', $value)->utc(),
            self::STRATEGY_DATETIME_WITH_TZ_CONVERTLESS => Carbon::createFromFormat('d.m.Y H:i:sP', $value),
        };
    }

    /**
     * Пример входящего значения: 1764536400 (начало дня по UTC+0)
     */
    public function setQueryByTimestampStrategy($filterValueOrValues): void
    {
        if ($this->getIsRange()) {
            if ($filterValueOrValues[0] && $filterValueOrValues[1]) {
                $this->panelSet->queryBuilder->whereBetween($this->columnName, [
                    $this->createCarbon($filterValueOrValues[0])->toDateTimeString(),
                    $this->createCarbon($filterValueOrValues[1])
                        ->add(23, 'hours')
                        ->add(59, 'minutes')
                        ->add(59, 'seconds')
                        ->toDateTimeString()
                ]);
                return;
            }

            if ($filterValueOrValues[0]) {
                $this->panelSet->queryBuilder->where($this->columnName, '>=', $this->createCarbon($filterValueOrValues[0])->toDateTimeString());
                return;
            }

            if ($filterValueOrValues[1]) {
                $this->panelSet->queryBuilder->where(
                    $this->columnName,
                    '>=',
                    $this->createCarbon($filterValueOrValues[1])->add(23, 'hours')
                        ->add(59, 'minutes')
                        ->add(59, 'seconds')
                        ->toDateTimeString());
            }
            return;
        }

        $this->panelSet->queryBuilder->whereBetween($this->columnName, [
            $this->createCarbon($filterValueOrValues[0])->toDateTimeString(),
            $this->createCarbon($filterValueOrValues[0])
                ->add(23, 'hours')
                ->add(59, 'minutes')
                ->add(59, 'seconds')
                ->toDateTimeString(),
        ]);
    }

    /**
     * Пример входящего значение: 30.11.2025
     */
    public function setQueryByDateStrategy($filterValueOrValues): void
    {
        if ($this->getIsRange()) {
            if ($filterValueOrValues[0] && $filterValueOrValues[1]) {
                $this->panelSet->queryBuilder->whereBetween($this->columnName, [
                    $this->createCarbon($filterValueOrValues[0])->toDateString(),
                    $this->createCarbon($filterValueOrValues[1])->toDateString(),
                ]);
                return;
            }

            if ($filterValueOrValues[0]) {
                $this->panelSet->queryBuilder->where($this->columnName, '>=', $this->createCarbon($filterValueOrValues[0])->toDateString());
                return;
            }

            if ($filterValueOrValues[1]) {
                $this->panelSet->queryBuilder->where($this->columnName, '<=', $this->createCarbon($filterValueOrValues[1])->toDateString());
            }

            return;
        }


        $this->panelSet->queryBuilder->where($this->columnName, $this->createCarbon($filterValueOrValues[0])->toDateString());
    }

    /**
     * Пример входящего значение: 01.12.2025 00:00:00+03:00
     */
    public function setQueryByDatetimeWithTZStrategy($filterValueOrValues): void
    {
        if ($this->getIsRange()) {

            if ($filterValueOrValues[0] && $filterValueOrValues[1]) {
                $this->panelSet->queryBuilder->whereBetween($this->columnName, [
                    $this->createCarbon($filterValueOrValues[0])->toDateTimeString(),
                    $this->createCarbon($filterValueOrValues[1])
                        ->add(23, 'hours')
                        ->add(59, 'minutes')
                        ->add(59, 'seconds')
                        ->toDateTimeString(),
                ]);
                return;
            }

            if ($filterValueOrValues[0]) {
                $this->panelSet->queryBuilder->where($this->columnName, '>=', $this->createCarbon($filterValueOrValues[0])->toDateTimeString());
                return;
            }

            if ($filterValueOrValues[1]) {
                $this->panelSet->queryBuilder->where(
                    $this->columnName,
                    '<=',
                    $this->createCarbon($filterValueOrValues[1])
                        ->add(23, 'hours')
                        ->add(59, 'minutes')
                        ->add(59, 'seconds')
                        ->toDateTimeString());
                return;
            }

            return;
        }

        $this->panelSet->queryBuilder->whereBetween($this->columnName, [
            $this->createCarbon($filterValueOrValues[0])->toDateTimeString(),
            $this->createCarbon($filterValueOrValues[0])
                ->add(23, 'hours')
                ->add(59, 'minutes')
                ->add(59, 'seconds')
                ->toDateTimeString(),
        ]);
    }

    public function setStrategy(string $strategy): static
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
