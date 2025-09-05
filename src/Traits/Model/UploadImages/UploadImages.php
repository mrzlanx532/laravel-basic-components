<?php

namespace Mrzlanx532\LaravelBasicComponents\Traits\Model\UploadImages;

use Mrzlanx532\LaravelBasicComponents\Helpers\FileHelper\Exceptions\ResizeTypeDoesNotExists;
use Mrzlanx532\LaravelBasicComponents\Traits\Model\UploadImages\Exceptions\InvalidFilePropertiesWithSettingsPropertyConfiguration;
use Mrzlanx532\LaravelBasicComponents\Helpers\FileHelper\FileHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait UploadImages
{
    /**
     * Из \Illuminate\Database\Eloquent\Model
     * Вызывается магический метод bootTraits
     *
     * @throws InvalidFilePropertiesWithSettingsPropertyConfiguration
     * @uses \Illuminate\Database\Eloquent\Model
     */
    protected static function booted()
    {
        static::saving(function ($model) {
            FileHelper::validateFilePropertiesWithSettingsConfig($model, 'filePropertiesWithSettings');

            foreach (static::$filePropertiesWithSettings as $filePropertyName => $filePropertySettings) {

                switch ($model->determineModelAction($model)) {
                    case FileHelper::MODEL_CREATING:
                        $model->saveNewFile($model, $filePropertyName, $filePropertySettings);
                        break;
                    case FileHelper::MODEL_UPDATING:
                        $model->removePreviousFilesIfExists($model, $filePropertyName, $filePropertySettings);
                        $model->saveNewFile($model, $filePropertyName, $filePropertySettings);
                        break;
                }
            }
        });

        static::deleting(function ($model) {

            if (!defined(get_class($model).'UPLOAD_FILE_TRAIT_DELETING_FILES')) {
                return;
            }

            $uploadFileDeletingFiles = get_class($model).'UPLOAD_FILE_TRAIT_DELETING_FILES';

            if ($uploadFileDeletingFiles) {
                FileHelper::validateFilePropertiesWithSettingsConfig($model, 'filePropertiesWithSettings');

                foreach (static::$filePropertiesWithSettings as $filePropertyName => $filePropertySettings) {
                    $model->removePreviousFilesIfExists($model, $filePropertyName, $filePropertySettings);
                }
            }
        });
    }

    /**
     * Удаляем основной файл и thumbnails, если они есть
     *
     * @param Model $model
     * @param $filePropertyName
     * @param $filePropertySettings
     */
    protected function removePreviousFilesIfExists(Model $model, $filePropertyName, $filePropertySettings)
    {
        $fullPathOfPreviousFile = "images/{$model->getOriginal($filePropertyName)}";

        if ($fullPathOfPreviousFile === null) {
            return;
        }

        if (!$model->isDirty($filePropertyName)) {
            return;
        }

        if (Storage::disk('public')->exists($fullPathOfPreviousFile)) {
            Storage::disk('public')->delete($fullPathOfPreviousFile);
        }

        if (is_array($filePropertySettings)) {

            foreach ($filePropertySettings as $filePropertySettingsKey => $filePropertySettingsParams) {

                $thumbnailExtension = FileHelper::getExtensionFromFullPath($fullPathOfPreviousFile);

                if ($filePropertySettingsParams[2] === FileHelper::RESIZE_TYPE_FIT_INTO_AREA_WITH_PROPORTIONS_AND_COLOR_CANVAS) {
                    $thumbnailExtension = 'jpg';
                }

                $fullPathOfThumbnailForPreviousFile = implode('', [
                    FileHelper::getFullPathWithFileNameWithoutExtension($fullPathOfPreviousFile),
                    "_$filePropertySettingsKey.",
                    $thumbnailExtension
                ]);

                if (Storage::disk('public')->exists($fullPathOfThumbnailForPreviousFile)) {
                    Storage::disk('public')->delete($fullPathOfThumbnailForPreviousFile);
                }
            }
        }
    }

    /**
     * @throws ResizeTypeDoesNotExists
     */
    protected function saveNewFile(Model $model, $filePropertyName, $filePropertySettings)
    {
        /* @var $file UploadedFile|null */
        $file = $model->$filePropertyName;

        if ($file === null) {
            return;
        }

        if (is_string($file)) {
            return;
        }

        $folderPathByCurrentDate = FileHelper::generateFolderPathByCurrentDate();

        $file->storeAs("images/$folderPathByCurrentDate", $file->hashName(), 'public');
        $model->$filePropertyName = $folderPathByCurrentDate . '/' . $file->hashName();

        if (is_array($filePropertySettings)) {
            foreach ($filePropertySettings as $filePropertySettingsKey => $filePropertySettingsParams) {

                $fileStreamAfterResize = FileHelper::resizeFileAndGetStreamOfFile($file, $filePropertySettingsParams);

                $extension = FileHelper::getExtensionFromFullPath($file->hashName());

                if ($filePropertySettingsParams[2] === FileHelper::RESIZE_TYPE_FIT_INTO_AREA_WITH_PROPORTIONS_AND_COLOR_CANVAS) {
                    $extension = 'jpg';
                }

                $fileNameWithPathForThumbnail = implode('', [
                    "images/$folderPathByCurrentDate/",
                    FileHelper::getFullPathWithFileNameWithoutExtension($file->hashName()),
                    "_$filePropertySettingsKey.",
                    $extension
                ]);

                Storage::disk('public')->put($fileNameWithPathForThumbnail, $fileStreamAfterResize);
            }
        }
    }

    /**
     * Определяем модель создается или обновляется
     * @param Model $model
     * @return string
     */
    private function determineModelAction(Model $model): string
    {
        if ($model->getKey()) {
            return FileHelper::MODEL_UPDATING;
        }

        return FileHelper::MODEL_CREATING;
    }

    /**
     * В зависимости от конфигурации $filePropertiesWithSettings в модели
     * отдаем либо:
     *  1. оригинальный файл
     * [
     *     "original": "/storage/images/2021/09/15/gA3bePmbSmyfmsbV9sO1IdD1TEXrMZjUyhuKldnG.png"
     * ]
     *  2. массив путей с thumbnails, в формте:
     *  [
     *     "original": "/storage/images/2021/09/15/gA3bePmbSmyfmsbV9sO1IdD1TEXrMZjUyhuKldnG.png",
     *     "200": "/storage/images/2021/09/15/gA3bePmbSmyfmsbV9sO1IdD1TEXrMZjUyhuKldnG_200.png",
     *     "400": "/storage/images/2021/09/15/gA3bePmbSmyfmsbV9sO1IdD1TEXrMZjUyhuKldnG_400.png",
     *     "600": "/storage/images/2021/09/15/gA3bePmbSmyfmsbV9sO1IdD1TEXrMZjUyhuKldnG_600.png"
     *  ]
     *
     * @throws InvalidFilePropertiesWithSettingsPropertyConfiguration
     */
    public function getFileLinksBySettings($property, $customDomain = null): ?array
    {
        FileHelper::validateFilePropertiesWithSettingsConfig($this, 'filePropertiesWithSettings');

        if ($this->$property === null) {
            return null;
        }

        if (preg_match('/^(http|https):\/\//', $this->$property)) {
            return [
                'original' => $this->$property
            ];
        }

        if (!Storage::disk('public')->exists("images/{$this->$property}")) {
            return null;
        }

        $symbolicLink = '/storage/images/';

        $uploadFileGlobalUrl = config('laravel_basic_components.upload_file_domain');

        if (is_null($customDomain)) {
            if ($uploadFileGlobalUrl) {
                $symbolicLink = $uploadFileGlobalUrl . $symbolicLink;
            }
        } else {
            $symbolicLink = $customDomain . $symbolicLink;
        }

        if (!array_key_exists($property, static::$filePropertiesWithSettings)) {
            return null;
        }

        $fileLinks = [];
        $fileLinks['original'] = $symbolicLink . $this->$property;

        if (static::$filePropertiesWithSettings[$property] === null) {
            return $fileLinks;
        }

        if (is_array(static::$filePropertiesWithSettings[$property])) {

            foreach (static::$filePropertiesWithSettings[$property] as $filePropertiesWithSettingParamsKey => $filePropertiesWithSettingParamsItem) {

                $extension = FileHelper::getExtensionFromFullPath($this->$property);

                if ($filePropertiesWithSettingParamsItem[2] === FileHelper::RESIZE_TYPE_FIT_INTO_AREA_WITH_PROPORTIONS_AND_COLOR_CANVAS) {
                    $extension = 'jpg';
                }

                $fileLinks[$filePropertiesWithSettingParamsKey] = implode('', [
                    $symbolicLink,
                    FileHelper::getFullPathWithFileNameWithoutExtension($this->$property),
                    "_$filePropertiesWithSettingParamsKey.",
                    $extension
                ]);
            }

            return $fileLinks;
        }

        return null;
    }
}
