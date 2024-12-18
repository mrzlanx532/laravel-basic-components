<?php

namespace Mrzlanx532\LaravelBasicComponents\Definition;

use Illuminate\Support\Facades\Lang;

abstract class Definition
{
    static protected array $items = [];

    public static function items(): array
    {
        return static::$items;
    }

    static public function getItems($associative = true, $withConstantProperty = false, $withLocale = false, $specifiedLocale = null, array $exclude = []): array
    {
        $items = [];
        $numericIndex = 0;

        foreach (static::items() as $constantIndex => $constant) {

            if (in_array($constantIndex, $exclude)) {
                continue;
            }

            $constantName = $constantIndex;

            if (!$associative) {
                $constantIndex = $numericIndex;
            }

            foreach ($constant as $constantPropertyKey => $constantProperty) {

                if ($constantPropertyKey === 'title' && self::needAndPossibleToTranslateTitle($constantName, $withLocale, $specifiedLocale)) {
                    $items[$constantIndex][$constantPropertyKey] = self::translateTitle($constantName, $specifiedLocale);
                    continue;
                }

                $items[$constantIndex][$constantPropertyKey] = $constantProperty;
            }

            if ($withConstantProperty) {
                $items[$constantIndex]['const'] = $constantName;
            }

            $numericIndex++;
        }

        return $items;
    }

    static public function getItemByConst($const, $withLocale = false, $specifiedLocale = null)
    {
        $preparedConst = static::items()[$const] ?? null;

        if (!self::needAndPossibleToTranslateTitle($const, $withLocale, $specifiedLocale)) {
            return $preparedConst;
        }

        $preparedConst['title'] = self::translateTitle($const, $specifiedLocale);

        return $preparedConst;
    }

    static private function translateTitle($const, $specifiedLocale)
    {
        if (!$specifiedLocale) {
            return Lang::get('definitions.' . static::class . '__' . $const);
        }

        return Lang::get('definitions.' . static::class . '__' . $const, [], $specifiedLocale);
    }

    static private function needAndPossibleToTranslateTitle($const, $withLocale, $specifiedLocale): bool
    {
        if (!$withLocale) {
            return false;
        }

        if (!$specifiedLocale) {
            return Lang::has('definitions.' . static::class . '__' . $const);
        }

        return Lang::hasForLocale('definitions.' . static::class . '__' . $const, $specifiedLocale);
    }

    static public function getValues(): array
    {
        $values = [];

        foreach (static::getItems() as $item) {
            $values[] = $item['id'];
        }

        return $values;
    }

    static public function getValuesThroughComma(array $exclude = []): string
    {
        $valuesThroughComma = '';

        $lastIndex = count(static::getItems(false)) - 1;

        foreach (static::getItems(false) as $itemIndex => $item) {

            if (in_array($item['id'], $exclude)) {
                continue;
            }

            $valuesThroughComma .= $item['id'];
            $valuesThroughComma .= $itemIndex !== $lastIndex ? ',' : '';

        }

        return $valuesThroughComma;
    }
}
