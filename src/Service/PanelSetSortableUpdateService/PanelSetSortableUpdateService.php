<?php

namespace Mrzlanx532\LaravelBasicComponents\Service\PanelSetSortableUpdateService;

use Mrzlanx532\LaravelBasicComponents\PanelSetSortable\PanelSetSortable;
use Mrzlanx532\LaravelBasicComponents\Service\PanelSetSortableUpdateService\Exceptions\FailedToCreateAnInstanceFromThePassedClass;
use Mrzlanx532\LaravelBasicComponents\Service\PanelSetSortableUpdateService\Exceptions\PassedClassDoesNotInheritPanelSetSortable;
use Mrzlanx532\LaravelBasicComponents\Service\Service;
use Throwable;

class PanelSetSortableUpdateService extends Service
{
    private PanelSetSortable $panelSetSortable;

    /**
     * @throws PassedClassDoesNotInheritPanelSetSortable
     * @throws FailedToCreateAnInstanceFromThePassedClass
     */
    public function __construct(string $panelSetSortable)
    {
        try {
            $this->panelSetSortable = new $panelSetSortable;
        } catch (Throwable) {
            throw new FailedToCreateAnInstanceFromThePassedClass;
        }

        if ($this->panelSetSortable instanceof PanelSetSortable === false) {
            throw new PassedClassDoesNotInheritPanelSetSortable;
        }
    }

    public function getRules(): array
    {
        return [
            'items' => 'required|array'
        ];
    }

    public function handle()
    {
        $oneDimensionalArray = $this->buildOneDimensionalArray($this->params['items']);

        foreach ($oneDimensionalArray as $newItem) {

            $whereValue = $this->panelSetSortable->getIdentifierField();

            if (str_contains($this->panelSetSortable->getIdentifierField(), '.')) {
                $whereValue = explode('.', $this->panelSetSortable->getIdentifierField())[1];
            }

            $updateData = [
                $this->panelSetSortable->getOrderField() => $newItem[$this->panelSetSortable->getOrderField()]
            ];

            if ($this->panelSetSortable->getIsNested()) {
                $updateData[$this->panelSetSortable->getParentField()] = $newItem[$this->panelSetSortable->getParentField()];
            }

            $this->panelSetSortable->getQueryBuilder()
                ->where($this->panelSetSortable->getIdentifierField(), $newItem[$whereValue])
                ->update($updateData);
        }
    }

    private function buildOneDimensionalArray($array, &$accumulator = [], $parentId = 0): array
    {
        foreach ($array as $itemKey => $item) {

            $item[$this->panelSetSortable->getParentField()] = $parentId;
            $item[$this->panelSetSortable->getOrderField()] = $itemKey;
            $accumulator[] = $item;

            if ($this->panelSetSortable->getIsNested() === false) {
                continue;
            }

            if (!isset($item['items'])) {
                continue;
            }

            if (!$item['items']) {
                continue;
            }

            $this->buildOneDimensionalArray($item['items'], $accumulator, $item[$this->panelSetSortable->getIdentifierField()]);
        }

        return $accumulator;
    }
}