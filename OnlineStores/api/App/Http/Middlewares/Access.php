<?php

namespace Api\Http\Middlewares;

use Api\Models\Token;

class Access extends Middleware implements MiddlewareInterface
{
    /**
     * @inherit
     */
    protected function setUp()
    {
        if ($this->getRequest()->hasHeader('HTTP_AUTHORIZATION') === false) {
            return $this->getResponse()->withJson([
                'status' => 'error',
                'error_code' => 'access_denied',
                'error_description' => 'You are not authorized to access this area, due to your invalid headers'
            ], 400);
        }

        $bearer = $this->getRequest()->getHeader('HTTP_AUTHORIZATION');

        $tokenString = Token::resolveToken($bearer[0]);

        if (!$tokenString) {
            return $this->getResponse()->withJson([
                'status' => 'error',
                'error_code' => 'invalid_token',
                'error_description' => 'Please make sure that you are passing the required headers correctly'
            ], 400);
        }

        $token = (new Token)->setClient($this->container->db);

        $token = $token->getToken($tokenString);

        if (!$token) {
            return $this->getResponse()->withJson([
                'status' => 'error',
                'error_code' => 'invalid_token',
                'error_description' => 'You are not authorized to access this area, no tokens found'
            ], 401);
        }

        if ($token['expiration'] <= time()) {
            return $this->getResponse()->withJson([
                'status' => 'error',
                'error_code' => 'expired_token',
                'error_description' => 'You are not authorized to access this area, you token has been expired'
            ], 401);
        }

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
