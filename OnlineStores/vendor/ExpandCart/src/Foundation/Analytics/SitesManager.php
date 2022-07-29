<?php

namespace ExpandCart\Foundation\Analytics;

class SitesManager extends Base
{
    protected $module = 'SitesManager';

    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    public function setSiteName($siteName)
    {
        $this->queryString['siteName'] = $siteName;

        return $this;
    }

    public function setUrls($urls)
    {
        $this->queryString['urls'] = $urls;

        return $this;
    }

    public function addSite()
    {
        $defaults = [
            'ecommerce' => true
        ];

        if (!$this->method) {
            $this->method = 'addSite';
        }

        $this->queryString = array_merge($this->queryString, $defaults);

        return $this->send();
    }
}
