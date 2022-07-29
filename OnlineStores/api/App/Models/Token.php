<?php

namespace Api\Models;

class Token extends ParentModel
{
    /**
     * The api_token table name string
     *
     * @var string
     */
    private $tokensTable = DB_PREFIX . 'api_tokens';

    /**
     * The required OAuth2 credentials
     *
     * @var array
     */
    protected static $canonicalCredentials = [
        'client_secret',
        'client_id',
        'grant_type',
    ];

    /**
     * The available grant types
     *
     * @var array
     */
    protected static $grantTypes = [
        'client_credentials'
    ];

    /**
     * The expiration in seconds
     *
     * @var int
     */
    protected $expiration = 100000;

    /**
     * Generate new token string
     *
     * @param int $length
     *
     * @return string
     */
    public static function generateToken($length)
    {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }

    /**
     * validate the given authentication header
     *
     * @param string $token
     *
     * @return bool|string
     */
    public static function resolveToken($token)
    {
        if (preg_match('#Bearer\s(\S+)#', $token, $bearer)) {

            if (isset($bearer[1])) {
                return $bearer[1];
            }

            return false;

        }

        return false;
    }

    /**
     * validate the given secret & client id to check if this credentials is authorized to access the resources
     *
     * @param array $parameters
     *
     * @return bool
     */
    public static function validateTokenCredentials($parameters)
    {
        if (is_array($parameters) === false) {
            return false;
        }

        if (array_diff_key(array_flip(self::$canonicalCredentials), $parameters)) {
            return false;
        }

        if (!isset($parameters['grant_type']) || in_array($parameters['grant_type'], self::$grantTypes) === false) {
            return false;
        }

        if (
            !isset($parameters['client_id']) ||
            preg_match('#^([A-Z0-9]{3,9})([0-9]{3})([A-Z0-9]{4})$#', $parameters['client_id']) === false
        ) {
            return false;
        }

        $decryptedSecret = self::decrypt($parameters['client_secret'], STORECODE);

        if (
            !isset($parameters['client_secret']) ||
            $decryptedSecret === false ||
            $decryptedSecret != $parameters['client_id']
        ) {
            return false;
        }

        return true;
    }

    /**
     * Decrypt a given secret key.
     *
     * @param int $clientId
     * @param string $key
     *
     * @return string|bool
     */
    public static function decrypt($clientId, $key)
    {

        $decrypt = function ($ciphertext, $key) {
            $c = base64_decode($ciphertext);
            $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
            $iv = substr($c, 0, $ivlen);
            $hmac = substr($c, $ivlen, $sha2len = 32);
            $ciphertext_raw = substr($c, $ivlen + $sha2len);
            $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
            $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
            if (hash_equals($hmac, $calcmac)) {
                return $original_plaintext;
            }

            return false;
        };

        return $decrypt($clientId, $key);
    }

    /**
     * Insert the given token into the database
     *
     * @param string $token
     * @param int $clientId
     *
     * @return void
     */
    public function storeToken($token, $clientId, $expiration=null)
    {
        $queryString = $fields = [];
        $queryString[] = 'INSERT INTO `' . $this->tokensTable . '` SET';

        $fields[] = 'client_id="' . $clientId . '"';
        $fields[] = 'token="' . $token . '"';
        $fields[] = 'expiration="' . (time() + ($expiration ?? $this->expiration) ) . '"';

        $queryString[] = implode(',', $fields);

        $this->client->query(implode(' ', $queryString));
    }

    /**
     * Retrieve api_token entry using the token string
     *
     * @param string $token
     *
     * @return array|bool
     */
    public function getToken($token)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM `' . $this->tokensTable . '` WHERE';
        $queryString[] = 'token = "' . $token . '"';

        $data = $this->client->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

    /**
     * Retrieve api_token entry using the client id
     *
     * @param int $clientId
     *
     * @return array|bool
     */
    public function getTokenByClient($clientId)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM `' . $this->tokensTable . '` WHERE';
        $queryString[] = 'client_id = "' . $clientId . '" order by token_id desc';

        $data = $this->client->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

    /**
     * Update the token expiration period
     *
     * @param string $token
     *
     * @return array|bool
     */
    public function updateTokenExpiration($token)
    {
        $queryString = $fields = [];
        $queryString[] = 'UPDATE `' . $this->tokensTable . '` SET';

        $fields[] = 'expiration="' . (time() + $this->expiration) . '"';

        $queryString[] = implode(',', $fields);
        $queryString[] = 'WHERE token="' . $token['token'] . '"';
        $queryString[] = 'AND client_id="' . $token['client_id'] . '"';

        $this->client->query(implode(' ', $queryString));
    }

    /**************  Delete token if exist  ******************/
    public function deleteTokenIfExist($clientId)
    {
        $queryString = [];
        $queryString[] = 'DELETE FROM `' . $this->tokensTable . '` WHERE';
        $queryString[] = 'client_id = "' . $clientId . '"';

        $this->client->query(implode(' ', $queryString));
    }
}
