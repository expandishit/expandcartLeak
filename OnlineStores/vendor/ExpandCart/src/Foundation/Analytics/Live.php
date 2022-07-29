<?php

namespace ExpandCart\Foundation\Analytics;

class Live extends Base
{
    protected $module = 'Live';

    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    public function setLastMinutes($lastMinutes)
    {
        $this->queryString['getCounters']['lastMinutes'] = $lastMinutes;

        return $this;
    }

    public function setVisitorId($visitorId)
    {
        $this->queryString['getVisitorProfile']['visitorId'] = $visitorId;

        return $this;
    }
}
