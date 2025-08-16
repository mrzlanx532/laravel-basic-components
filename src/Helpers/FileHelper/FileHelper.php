<?php

namespace Mrzlanx532\LaravelBasicComponents\Helpers\FileHelper;

use Mrzlanx532\LaravelBasicComponents\Helpers\FileHelper\Exceptions\ResizeTypeDoesNotExists;
use Mrzlanx532\LaravelBasicComponents\Traits\Model\UploadImages\Exceptions\InvalidFilePropertiesWithSettingsPropertyConfiguration;
use Mrzlanx532\LaravelBasicComponents\Traits\Model\UploadFile\UploadFileConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Psr\Http\Message\StreamInterface;

class FileHelper
{
    /* @Action
     *
     * Изображение будет вырезано из самого центра картинки (по высоте и по ширине) до указанного размера
     */
    const RESIZE_TYPE_SMART = 'RESIZE_TYPE_SMART';

    /* @Action
     *
     * Изображение будет вписано в область указанного размера,
     * (если изображение меньше указанного размера, то останется оригинал)
     */
    const RESIZE_TYPE_FIT_INTO_AREA_WITH_PROPORTIONS = 'RESIZE_TYPE_FIT_INTO_AREA_WITH_PROPORTIONS';

    /* @Action
     *
     * Изображение будет вписано в область указанного размера пропорционально, остальное зальется указанным цветом
     * (если изображение меньше указанного размера, то останется оригинал)
     */
    const RESIZE_TYPE_FIT_INTO_AREA_WITH_PROPORTIONS_AND_COLOR_CANVAS = 'RESIZE_TYPE_FIT_INTO_AREA_WITH_PROPORTIONS_AND_COLOR_CANVAS';

    const MODEL_CREATING = 'MODEL_CREATING';
    const MODEL_UPDATING = 'MODEL_UPDATING';

    /**
     * 1. Отсутсвует свойство `filePropertiesWithSettings` в текущей модели
     * 2. Свойство `filePropertiesWithSettings` пустое в текущей модели
     * 3. Неверный формат конфигурации файла
     * 4. Неверный формат конфигурации файла
     *
     * @throws InvalidFilePropertiesWithSettingsPropertyConfiguration
     */
    public static function validateFilePropertiesWithSettingsConfig($model, $filePropertiesWithSettingsPropertyName)
    {
        if (!property_exists($model, $filePropertiesWithSettingsPropertyName)) {
            throw new InvalidFilePropertiesWithSettingsPropertyConfiguration(
                'В модели `' . get_class($model) . '` отсутствует статическое свойство `filePropertiesWithSettings`'
            );
        }

        if (empty($model::$$filePropertiesWithSettingsPropertyName)) {
            throw new InvalidFilePropertiesWithSettingsPropertyConfiguration(
                'В модели `' . get_class($model) . '` свойство `filePropertiesWithSettings` не может быть пустым'
            );
        }

        foreach ($model::$$filePropertiesWithSettingsPropertyName as $filePropertyName => $filePropertyValue) {
            if (is_int($filePropertyName)) {
                throw new InvalidFilePropertiesWithSettingsPropertyConfiguration(
                    'Ключ `' . $filePropertyValue . '` должен содержать в себе либо `null` (если thumbnails не нужны), либо массив для thumbnails'
                );
            }

            if ($filePropertyValue !== null && !is_array($filePropertyValue)) {
                throw new InvalidFilePropertiesWithSettingsPropertyConfiguration(
                    'Ключ `' . $filePropertyValue . '` должен содержать в себе либо `null` (если thumbnails не нужны), либо массив для thumbnails'
                );
            }
        }
    }

    /**
     * Обрабатываем изображение с учетом переданного RESIZE_TYPE
     *
     * @throws ResizeTypeDoesNotExists
     */
    public static function resizeFileAndGetStreamOfFile(UploadedFile $file, $settings): StreamInterface
    {
        $image = Image::make($file);

        $width = $settings[0];
        $height = $settings[1];
        $typeOfResizing = $settings[2];

        switch ($typeOfResizing) {
            case FileHelper::RESIZE_TYPE_SMART:

                $image->fit($width, $height, function ($constraint) {
                    $constraint->upsize();
                });

                break;

            case FileHelper::RESIZE_TYPE_FIT_INTO_AREA_WITH_PROPORTIONS:

                if ($image->getHeight() > $height || $image->getWidth() > $width) {
                    $image->resize($width, $height, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                }

                break;

            case FileHelper::RESIZE_TYPE_FIT_INTO_AREA_WITH_PROPORTIONS_AND_COLOR_CANVAS:

                $color = $settings[3] ?? null;
                $position = $settings[4] ?? 'center';

                if ($image->getHeight() > $height || $image->getWidth() > $width) {

                    $image->resize($width, $height, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $image = Image::canvas($width, $height, $color)->insert($image, $position);
                }

                break;

            default:
                throw new ResizeTypeDoesNotExists("В модели указан неверный RESIZE_TYPE");
        }

        return $image->stream();
    }

    /**
     * Из полного пути (2021/08/30/9a5KhNS1wnFxb7yATmRjDKpi5pi5XMbC.png)
     * получаем путь без расширения файла (2021/08/30/9a5KhNS1wnFxb7yATmRjDKpi5wxjDKkkBm7hXMbC)
     *
     * @param $fullPath
     * @return string|null
     */
    public static function getFullPathWithFileNameWithoutExtension($fullPath): ?string
    {
        return explode('.', $fullPath)[0] ?? null;
    }

    /**
     * Из полного пути (2021/08/30/9a5KhNS1wnFxb7yATmRjDKpi5wxjDKkBm7hXMbC.png)
     * получаем только расширение файла (png)
     *
     * @param $fullPath
     * @return string|null
     */
    public static function getExtensionFromFullPath($fullPath): ?string
    {
        return explode('.', $fullPath)[1] ?? null;
    }

    /**
     * Получаем строку для пути файла по текущей дате.
     * Например: 2021/09/02
     *
     * @return string
     */
    public static function generateFolderPathByCurrentDate(): string
    {
        return date('Y') . '/' . date('m') . '/' . date('d');
    }

    /**
     * Автономный вариант: без использования трейта UploadFile
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
    public static function getFileLinksBySettings($modelClass, $property, $value, $customDomain = null): ?array
    {
        FileHelper::validateFilePropertiesWithSettingsConfig(new $modelClass, 'filePropertiesWithSettings');

        if ($value === null) {
            return null;
        }

        if (!Storage::exists("public/images/{$value}")) {
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

        if (!array_key_exists($property, $modelClass::$filePropertiesWithSettings)) {
            return null;
        }

        $fileLinks = [];
        $fileLinks['original'] = $symbolicLink . $value;

        if ($modelClass::$filePropertiesWithSettings[$property] === null) {
            return $fileLinks;
        }
        if (is_array($modelClass::$filePropertiesWithSettings[$property])) {

            foreach ($modelClass::$filePropertiesWithSettings[$property] as $filePropertiesWithSettingParamsKey => $filePropertiesWithSettingParamsItem) {

                $extension = FileHelper::getExtensionFromFullPath($value);

                if ($filePropertiesWithSettingParamsItem[2] === FileHelper::RESIZE_TYPE_FIT_INTO_AREA_WITH_PROPORTIONS_AND_COLOR_CANVAS) {
                    $extension = 'jpg';
                }

                $fileLinks[$filePropertiesWithSettingParamsKey] = implode('', [
                    $symbolicLink,
                    FileHelper::getFullPathWithFileNameWithoutExtension($value),
                    "_$filePropertiesWithSettingParamsKey.",
                    $extension
                ]);
            }

            return $fileLinks;
        }

        return null;
    }

    public static function getUploadFileConfig(Model $model): UploadFileConfig
    {
        if (method_exists($model, 'getUploadFileConfig')) {
            return $model->getUploadFileConfig();
        }

        return new UploadFileConfig;
    }

    public static function modelHasSoftDeletesTrait(Model $model): bool
    {
        return method_exists($model, 'trashed');
    }

    public static function isNeedToDeleteFileFromStorage(Model $model, string $disk): bool
    {
        if (!(Storage::disk($disk)->exists($model->file->filepath))) {
            return false;
        }

        if (FileHelper::modelHasSoftDeletesTrait($model)) {
            return $model->isForceDeleting();
        } else {
            return true;
        }
    }
}
