<?php

namespace Api\Http\Middlewares;

use Api\Models\Token;

class VodafoneAccess extends Middleware implements MiddlewareInterface
{
    /**
     * @inherit
     */
    protected function setUp()
    {
        if (empty($this->getRequest()->getParsedBody()['token'])) {
            return $this->getResponse()->withJson([
                'status' => 'error',
                'error_code' => 'access_denied',
                'error_description' => 'You are not authorized to access this area, due to your invalid headers'
            ], 400);
        }

        if($this->getRequest()->getParsedBody()['token'] !== EC_VODAFONE_ACCESS){
            return $this->getResponse()->withJson([
                'status' => 'error',
                'error_code' => 'invalid_token',
                'error_description' => 'Please make sure that you are passing the required headers correctly'
            ], 400);
        }
    }

    /**
     * @inherit
     */
    protected function shutDown()
    {
        return true;
    }
}
