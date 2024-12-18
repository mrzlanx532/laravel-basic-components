<?php

namespace Mrzlanx532\LaravelBasicComponents\Service\BrowserFilterPreset;

use Mrzlanx532\LaravelBasicComponents\Models\BrowserFilterPreset;
use Illuminate\Database\Eloquent\Model;

class BrowserFilterPresetCreateService extends BrowserFilterPresetBaseService
{
    public function getRules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'filters' => 'required|array',
            'browser_id' => 'required|string',
        ];
    }

    public function handle(): Model
    {
        /* @var $browserFilterPreset BrowserFilterPreset */
        $browserFilterPreset = new BrowserFilterPresetBaseService::$browserFilterPresetModel();

        $browserFilterPreset->title = $this->params['title'] ?? null;
        $browserFilterPreset->filters = json_encode($this->params['filters']) ?? null;
        $browserFilterPreset->ident = $this->params['browser_id'];

        $browserFilterPreset->save();

        return $browserFilterPreset;
    }
}