<?php

use GuzzleHttp\Psr7;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

class ModelModulePrintfulOrder
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $baseUri = 'https://api.printful.com/';

    /**
     * @var array
     */
    protected $errors;

    /**
     * Set the apiKey.
     *
     * @param string $apiKey
     *
     * @return void
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Prepare the recipient array.
     *
     * @param array $order
     *
     * @return array
     */
    public function getRecipient($order)
    {
        return [
            'name' => $order['shipping_firstname'] . ' ' . $order['shipping_lastname'],
            'address1' => $order['shipping_address_1'],
            'city' => $order['shipping_city'],
            'state_code' => $order['shipping_zone_code'],
            'country_code' => $order['shipping_iso_code_2'],
            'zip' => $order['shipping_postcode'],
        ];
    }

    /**
     * Prepare the items array.
     *
     * @param array $order
     *
     * @return array
     */
    public function getItems($order)
    {
        $items = [];

        foreach ($order['products'] as $key => $product) {

            $items[$key] = [
                'variant_id' => (int)$product['printful']['variant_id'],
                'quantity' => (int)$product['quantity'],
                'files' => [
                    ['url' => \Filesystem::getUrl('image/' . $product['printful']['image'])]
                ]
            ];

        }

        return $items;
    }

    /**
     * Create a new order.
     *
     * @param array $order
     *
     * @return array|bool
     */
    public function createOrder($order)
    {
        $client = new GuzzleClient([
            'base_uri' => $this->baseUri,
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->apiKey)
            ]
        ]);

        try {
            $result = $client->post('orders', [
                'json' => [
                    'recipient' => $this->getRecipient($order),
                    'items' => $this->getItems($order)
                ]
            ]);

            return json_decode($result->getBody()->getContents(), true);

        } catch (RequestException $e) {

            $error = json_decode($e->getResponse()->getBody()->getContents(), true);

            $this->setError($error);

            return false;
        }
    }

    /**
     * Check status for a given order.
     *
     * @param int $orderId
     *
     * @return array|bool
     */
    public function orderStatus($orderId)
    {
        $client = new GuzzleClient([
            'base_uri' => $this->baseUri,
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->apiKey)
            ]
        ]);

        try {
            $result = $client->get('orders/' . $orderId);

            return json_decode($result->getBody()->getContents(), true);

        } catch (RequestException $e) {

            $error = json_decode($e->getResponse()->getBody()->getContents(), true);

            $this->setError($error);

            return false;
        }
    }

    /**
     * Push an error to the errors array.
     *
     * @param mixed $error
     *
     * @return void
     */
    public function setError($error)
    {
        $this->errors[] = $error;
    }

    /**
     * Get all errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
