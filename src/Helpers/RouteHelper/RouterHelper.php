<?php

namespace Mrzlanx532\LaravelBasicComponents\Helpers\RouteHelper;

class RouterHelper
{
    public function addRoutesForBrowserPresets($route, $controller)
    {
        app()->get('router')->post("$route/preset/create", [$controller, 'browserPresetCreate']);
        app()->get('router')->post("$route/preset/update", [$controller, 'browserPresetUpdate']);
        app()->get('router')->post("$route/preset/delete", [$controller, 'browserPresetDelete']);
    }
}
