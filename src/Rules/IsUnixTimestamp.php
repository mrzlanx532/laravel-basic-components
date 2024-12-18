<?php

namespace Mrzlanx532\LaravelBasicComponents\Rules;

use Illuminate\Contracts\Validation\Rule;

class IsUnixTimestamp implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return is_numeric($value) && $value > 0 && $value < 2147483647;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return trans('validation.unix_timestamp');
    }
}
