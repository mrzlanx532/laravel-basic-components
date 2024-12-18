<?php

namespace Mrzlanx532\LaravelBasicComponents\Service\BrowserFilterPreset;

use Mrzlanx532\LaravelBasicComponents\Models\BrowserFilterPreset;
use Illuminate\Database\Eloquent\Model;

class BrowserFilterPresetDeleteService extends BrowserFilterPresetBaseService
{
    public function getRules(): array
    {
        return [
            'id' => 'required|exists:' . (new BrowserFilterPresetBaseService::$browserFilterPresetModel)->getTable() . ',id'
        ];
    }

    public function handle(): Model
    {
        /* @var $browserFilterPreset BrowserFilterPreset */
        $browserFilterPreset = BrowserFilterPresetBaseService::$browserFilterPresetModel::query()->find($this->params['id']);
        $browserFilterPreset->delete();

        return $browserFilterPreset;
    }
}