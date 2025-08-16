<?php

namespace Mrzlanx532\LaravelBasicComponents\Traits\Model\UploadFile;

class UploadFileConfig
{
    /**
     * Если true, то сохраняем в storage/app/private/<$filepathPrefix>/...,
     * если false - storage/app/public/<$filepathPrefix>/...
     */
    private bool $isPrivate = true;
    private string $privateDisk = 'local';
    private string $publicDisk = 'public';
    private string $filepathPrefix = 'files';
    private string $foreignKey = 'file_id';

    public static function create(): self
    {
        return new self;
    }

    public function setAsPublic(): static
    {
        $this->isPrivate = false;

        return $this;
    }

    public function setForeignKey(string $foreignKey): self
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    public function isPrivate(): bool
    {
        return $this->isPrivate;
    }

    public function isPublic(): bool
    {
        return !$this->isPrivate;
    }

    public function getPrivateDisk(): string
    {
        return $this->privateDisk;
    }

    public function getPublicDisk(): string
    {
        return $this->publicDisk;
    }

    public function getFilepathPrefix(): string
    {
        return $this->filepathPrefix;
    }

    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }
}
