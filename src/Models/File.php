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
 * @property boolean is_public
 *
 * @property-read string $extension
 * @property-read string $filename_without_extension
 * @property-read string $original_filename_without_extension
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

    public function getExtensionAttribute(): array|string
    {
        return pathinfo($this->filename, PATHINFO_EXTENSION);
    }

    public function getFilenameWithoutExtensionAttribute(): array|string
    {
        return pathinfo($this->filename, PATHINFO_FILENAME);
    }

    public function getOriginalFilenameWithoutExtensionAttribute(): array|string
    {
        return pathinfo($this->original_filename, PATHINFO_FILENAME);
    }
}