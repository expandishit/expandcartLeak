<?php

namespace ExpandCart\Foundation\Analytics;

class VisitTime extends Base
{
    protected $module = 'VisitTime';

    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }
}
