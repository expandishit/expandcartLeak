<?php

use GuzzleHttp\Psr7;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

class ModelShippingSaeeShipment extends Model
{
    /**
     * @var string
     */
    protected $env;

    /**
     * Available paths based on the environment
     *
     * @var array
     */
    protected $base = [
        'live' => 'http://www.saee.sa/',
        'test' => 'http://www.k-w-h.com /',
    ];

    /**
     * Saee api secret key
     *
     * @var string
     */
    protected $secret;

    /**
     * The response of the api.
     *
     * @var string
     */
    protected $response;

    /**
     * The shipment products, also known as description as defined in saee api.
     *
     * @var array
     */
    protected $products = [];

    /**
     * Set the environment variable.
     *
     * @param mixed $env
     *
     * return \ModelShippingSaeeShipment
     */
    public function setEnv($env)
    {
        if ($env === 0) {
            $env = 'live';
        } else {
            $env = 'test';
        }

        $this->env = $env;

        return $this;
    }

    /**
     * Return the environment string.
     *
     * return string
     */
    public function getEnv()
    {
        return $this->env;
    }

    /**
     * Return the base url.
     *
     * return string
     */
    public function getBase()
    {
        return $this->base[$this->getEnv()];
    }

    /**
     * Set the api secret key.
     *
     * @param string $secret
     *
     * return \ModelShippingSaeeShipment
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * Return the secret string.
     *
     * return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Return the response string.
     *
     * return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Add new product/description to the products array.
     *
     * @param array $product
     *
     * return \ModelShippingSaeeShipment
     */
    public function addProduct($product)
    {
        $this->products[] = [
            'comment' => $product[''],
            'quantity' => $product['quantity'],
        ];

        return $this;
    }

    /**
     * a facade for the self::addProduct() method.
     *
     * @param array $product
     *
     * return \ModelShippingSaeeShipment
     */
    public function addDescription($product)
    {
        return $this->addProduct($product);
    }

    /**
     * Return the products list.
     *
     * return array
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Return the description [products list] as a json string.
     *
     * return string
     */
    public function getDescription()
    {
        return json_encode($this->getProducts());
    }

    /**
     * Create a shipment.
     *
     * @param array $data
     *
     * return bool
     */
    public function create($data)
    {
        $client = new GuzzleClient([
            'base_uri' => $this->getBase()
        ]);

        try {

            $result = $client->post('deliveryrequest/new', [
                'form_params' => array_merge($data, [
                    'secret' => $this->getSecret(),
                    'description' => $this->getDescription()
                ])
            ]);

            $this->response = $result->getBody();

            return true;
        } catch(RequestException $e) {
            return false;
        }
    }
}
