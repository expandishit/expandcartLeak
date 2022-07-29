<?php

namespace Api\Http\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Api\Models\Token;
use Api\Models\User;

class TokenController extends Controller
{
    /**
     * The expiration in seconds
     *
     * @var int
     */
    protected $expiration = 30000;

    public function index(Request $request, Response $response)
    {
        $parameters = $request->getParsedBody();

        if (!(Token::validateTokenCredentials($parameters))) {
            return $response->withJson([
                'status' => 'error',
                'error_code' => 'invalid_credentials',
                'error_description' => 'Invalid credentials, please checkout your credentials'
            ], 401);
        }

        $user = (new User)->setClient($this->container->db);

        $user = $user->getUser($parameters['client_secret'], $parameters['client_id']);

        if (!$user) {
            return $response->withJson([
                'status' => 'error',
                'error_code' => 'access_denied',
                'error_description' => 'You are not authorized to access this area, please checkout your credentials'
            ], 401);
        }

        $token = (new Token())->setClient($this->container->db);

        $existToken = $token->getTokenByClient($user['id']);

        if ($existToken && time() <= $existToken['expiration']) {

            $tokenString = $existToken['token'];

            $token->updateTokenExpiration($existToken);
        } else {

            $tokenString = Token::generateToken(25);

            $token->storeToken($tokenString, $user['id']);
        }

        return $response->withJson([
            'status' => 'ok',
            'access_token' => $tokenString,
            'redirect_url' => '',
            'grant_type' => '',
            'token_type' => 'Bearer',
            'scope' => 'read',
        ], 200);
    }

    public function checkToken(Request $request, Response $response)
    {
        $parameters = $request->getParsedBody();
        $user = (new User)->setClient($this->container->db);
        $user = $user->getUser($parameters['client_secret'], $parameters['client_id']);

        $token = (new Token())->setClient($this->container->db);

        $existToken = $token->getTokenByClient($user['id']);

        if ($existToken && time() <= $existToken['expiration']) {

            return $response->withJson([
                'status' => '200',
                "message" => "Token is valid"
            ], 200);
        } else {

            return $response->withJson([
                'status' => '403',
                "message" => "Expired Token"
            ], 403);
        }

    }

    public function generateWebViewToken(Request $request, Response $response)
    {
        $parameters = $request->getParsedBody();
        if (!isset($parameters['user_id']) || empty($parameters['user_id']))
        {
            return $response->withJson([
                'status' => '403',
                "message" => "Token is valid"
            ], 403);
        }

        $user = (new User)->setContainer($this->container);
        $user_data = $user->profile($parameters['user_id']);
        if (!$user_data)
        {
            return $response->withJson([
                'status' => '405',
                "message" => "User not found"
            ], 405);
        }

        $userInfo['user_id'] = $user_data['user_id'];
        $userInfo['username'] = $user_data['username'];
        $userInfo['email'] = $user_data['email'];
        $userInfo['expiration'] = time() + $this->expiration;

        // Start generating token
        $key = EC_WV_SECRET_KEY;
        $cipher = "AES-256-CBC";
        $iv_length = openssl_cipher_iv_length($cipher);
        $iv = substr(hash('sha256', $key), 0, $iv_length);
        $options = 0;
        $string = json_encode($userInfo);
        $encryption = base64_encode(openssl_encrypt($string, $cipher, $key, $options, $iv));

        return $response->withJson([
            'status' => '200',
            "message" => $encryption
        ], 200);

    }
}