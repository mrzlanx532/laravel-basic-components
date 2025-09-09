<?php

namespace Mrzlanx532\LaravelBasicComponents\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Заменяет строки "__null__" значения на null при отправке FormData
 */
class ReplaceNullValuesInFormData
{
    public function handle(Request $request, Closure $next)
    {
        $request->merge(
            $this->replaceNullStrings(
                $request->all()
            )
        );

        return $next($request);
    }

    private function replaceNullStrings($data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->replaceNullStrings($value);
            } elseif ($value === '__null__') {
                $data[$key] = null;
            }
        }

        return $data;
    }
}
