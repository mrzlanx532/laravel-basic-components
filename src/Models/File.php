<?php

namespace Mrzlanx532\LaravelBasicComponents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * \Mrzlanx532\LaravelBasicComponents\Models
 *
 * @property Carbon created_at
 * @property string filename
 * @property string filepath
 * @property int id
 * @property string original_filename
 *
 * @property-read string $extension
 * @property-read string $filename_without_extension
 *
 * @method static Builder|File query()
 * @method static File|null find($id)
 * @method static File findOrFail($id)
 *
 * @mixin Model
 */
class File extends Model
{
    protected $table = 'files';

    const UPDATED_AT = null;

    protected function extension(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => pathinfo($value, PATHINFO_EXTENSION)
        );
    }

    protected function filenameWithoutExtension(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => pathinfo($value, PATHINFO_FILENAME)
        );
    }
}
