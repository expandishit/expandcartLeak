<?php

namespace Api\Http\Middlewares;

class Auth extends Middleware implements MiddlewareInterface
{
    /**
     * @inherit
     */
    protected function setUp()
    {
        return true;
    }

    /**
     * @inherit
     */
    protected function shutDown()
    {
        return true;
    }
}
