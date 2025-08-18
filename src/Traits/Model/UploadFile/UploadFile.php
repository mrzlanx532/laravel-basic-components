<?php

namespace Mrzlanx532\LaravelBasicComponents\Traits\Model\UploadFile;

use Mrzlanx532\LaravelBasicComponents\Models\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mrzlanx532\LaravelBasicComponents\Helpers\FileHelper\FileHelper;

trait UploadFile
{
    protected static function booted(): void
    {
        /** @var Model & object{uploadFile: UploadedFile|null} $model */
        static::saving(function (Model $model) {

            $config = FileHelper::getUploadFileConfig($model);
            $filepath = $config->getFilepathPrefix() . '/' . FileHelper::generateFolderPathByCurrentDate();
            $disk = $config->isPrivate() ? $config->getPrivateDisk() : $config->getPublicDisk();

            if ($model->exists) {
                if (Storage::disk($disk)->exists($model->file->filepath)) {
                    Storage::disk($disk)->delete($model->file->filepath);
                }

                $file = $model->file;
            } else {
                $file = new File;
            }

            $file->filename = $model->uploadFile->hashName();
            $file->filepath = "$filepath/$file->filename";
            $file->original_filename = preg_replace('/[^\p{L}0-9_\-\. ]/u', '', $model->uploadFile->getClientOriginalName());
            $file->is_public = $config->isPublic();
            $file->save();

            $model->{$config->getForeignKey()} = $file->id;

            $model->uploadFile->storeAs(
                $filepath,
                $file->filename,
                $disk
            );

            unset($model->uploadFile);
        });

        static::deleting(function (Model $model) {
            $config = FileHelper::getUploadFileConfig($model);
            $disk = $config->isPrivate() ? $config->getPrivateDisk() : $config->getPublicDisk();

            if (FileHelper::isNeedToDeleteFileFromStorage($model, $disk)) {
                Storage::disk($disk)->delete($model->file->filepath);
                $model->file->delete();
            }
        });
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}
