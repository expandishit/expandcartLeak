<?php

namespace ExpandCart\Foundation\Exceptions;

abstract class Exception extends \Exception
{
    abstract public function debug();
}
