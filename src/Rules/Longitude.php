<?php

namespace Mrzlanx532\LaravelBasicComponents\Rules;

use Illuminate\Contracts\Validation\Rule;

class Longitude implements Rule
{
    public function passes($attribute, $value): bool
    {
        if ($this->valueIsInteger($value)) {
            $reducedValueToInteger = (int)$value;

            if ($reducedValueToInteger > 180 || $reducedValueToInteger < -180) {
                return false;
            }

            return true;
        }

        return (bool)preg_match('/^(\+|-)?(?:180(?:(?:\.0+)?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]+)?))$/', $value);
    }

    public function message(): string
    {
        return trans('validation.longitude');
    }

    private function valueIsInteger($value): bool
    {
        return (bool)preg_match('/^[0-8]?[0-9]$/', $value);
    }
}
