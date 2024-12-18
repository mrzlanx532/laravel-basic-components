<?php

namespace Mrzlanx532\LaravelBasicComponents\Repositories\Definitions;

use Mrzlanx532\LaravelBasicComponents\Definition\Definition;

class AllDefinitionsRepository
{
    private array $definitions = [];

    private bool $localeEnabled = false;

    public function get(): array
    {
        $this->collectFromFolderRecursive(base_path('app/Definitions'));

        return $this->definitions;
    }

    public function enableLocale(): static
    {
        $this->localeEnabled = true;

        return $this;
    }

    private function collectFromFolderRecursive($folder)
    {
        $foldersOrFiles = scandir($folder);
        unset($foldersOrFiles[0]);
        unset($foldersOrFiles[1]);

        foreach ($foldersOrFiles as $folderOrFile)
        {
            $isDirectory = is_dir($folder . '/' . $folderOrFile);

            if ($isDirectory) {
                $this->collectFromFolderRecursive($folder . '/' . $folderOrFile);
                continue;
            }

            /* @var $definition Definition */
            $definition = new ($this->convertPathToClass($folder . '/' . $folderOrFile))();

            $localeEnabled = $this->localeEnabled;
            $this->definitions[$this->convertPathToKey($folder . '/'. $folderOrFile)] = $definition->getItems(associative:false, withLocale:$localeEnabled);
        }
    }

    private function convertPathToClass($path): string
    {
        $path = stristr($path, 'Definitions');
        $path = str_replace('/', '\\', $path);
        $path = str_replace('.php', '', $path);

        return 'App\\' . $path;
    }

    private function convertPathToKey($path): string
    {
        $path = stristr($path, 'Definitions');
        $path = str_replace('Definitions/', '', $path);
        $path = str_replace('/', '', $path);

        return str_replace('.php', '', $path);
    }
}
