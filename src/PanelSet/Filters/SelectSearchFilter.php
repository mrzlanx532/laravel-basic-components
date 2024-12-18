<?php

namespace Mrzlanx532\LaravelBasicComponents\PanelSet\Filters;

class SelectSearchFilter extends BaseSelectFilter
{
    private string $url = "";
    
    public function getType(): string
    {
        return 'SELECT_SEARCH';
    }
    
    public function setUrl($url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
