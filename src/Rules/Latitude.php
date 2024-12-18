<?php

namespace Mrzlanx532\LaravelBasicComponents\Rules;

use Illuminate\Contracts\Validation\Rule;

class Latitude implements Rule
{
    public function passes($attribute, $value): bool
    {
        if ($this->valueIsInteger($value)) {
            $reducedValueToInteger = (int)$value;

            if ($reducedValueToInteger > 90 || $reducedValueToInteger < -90) {
                return false;
            }

            return true;
        }

        return (bool)preg_match('/^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/', $value);
    }

    public function message(): string
    {
        return trans('validation.latitude');
    }

    private function valueIsInteger($value): bool
    {
        return (bool)preg_match('/[-]?[0-9]+/', $value);
    }
}
