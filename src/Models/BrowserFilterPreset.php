<?php

namespace Mrzlanx532\LaravelBasicComponents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * Mrzlanx532\LaravelBasicComponents\Models;
 *
 * @property int $id
 * @property string $title
 * @property string $ident
 * @property string $filters
 *
 * @method static Builder|BrowserFilterPreset query()
 *
 * @mixin Builder
 */
class BrowserFilterPreset extends Model
{
    protected $table = 'mrzlanx532_browser_filters_presets';

    public $timestamps = false;
}
