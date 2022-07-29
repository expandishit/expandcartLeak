<?php

namespace ExpandCart\Foundation\Support\Factories\Payments\FastpayCash;

class FastpayCash
{
    /**
     * The environment mode.
     *
     * @var string
     */
    private $mode = 'test';

    /**
     * The final end point url.
     *
     * @var string
     */
    private $apiEndPoint = null;

    /**
     * The end point base url for both live and test environments.
     *
     * @var array
     */
    private $endPoints = [
        'live' => 'https://secure.fast-pay.cash/',
        'test' => 'https://dev.fast-pay.cash/'
    ];

    /**
     * The merchang phone number.
     *
     * @var string
     */
    private $merchantNo;

    /**
     * The merchant store password.
     *
     * @var string
     */
    private $storePassword;

    /**
     * The bill ammunt.
     *
     * @var float
     */
    private $amount;

    /**
     * The order id which must be unique.
     *
     * @var string
     */
    private $orderId;

    /**
     * The success url.
     *
     * @var string
     */
    private $successUrl;

    /**
     * The fail url.
     *
     * @var string
     */
    private $failUrl;

    /**
     * The cancel url.
     *
     * @var string
     */
    private $cancelUrl;

    /**
     * Gets the base url based on the mode.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->endPoints[$this->mode];
    }

    /**
     * Sets the environment mode.
     *
     * @param string $mode
     *
     * @return \ExpandCart\Foundation\Support\Factories\Payments\FastpayCash\FastpayCash
     */
    public function setMode($mode)
    {
        $this->mode = ($mode == 'test' || $mode == 1 ? 'test' : 'live');

        return $this;
    }

    /**
     * Set the endpoint api url.
     *
     * @param string $url
     *
     * @return \ExpandCart\Foundation\Support\Factories\Payments\FastpayCash\FastpayCash
     */
    public function setEndPoint($url)
    {
        $this->apiEndPoint = $this->endPoints[$this->mode] . ltrim($url, '/');

        return $this;
    }

    /**
     * Sets the merchant phone number.
     *
     * @param string $merchantNo
     *
     * @return \ExpandCart\Foundation\Support\Factories\Payments\FastpayCash\FastpayCash
     */
    public function setMerchantNo($merchantNo)
    {
        $this->merchantNo = $merchantNo;

        return $this;
    }

    /**
     * Sets the merchant store password.
     *
     * @param string $storePassword
     *
     * @return \ExpandCart\Foundation\Support\Factories\Payments\FastpayCash\FastpayCash
     */
    public function setStorePassword($storePassword)
    {
        $this->storePassword = $storePassword;

        return $this;
    }

    /**
     * Sets the bill amount.
     *
     * @param float $amount
     *
     * @return \ExpandCart\Foundation\Support\Factories\Payments\FastpayCash\FastpayCash
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Sets the order id.
     *
     * @param string $orderId
     *
     * @return \ExpandCart\Foundation\Support\Factories\Payments\FastpayCash\FastpayCash
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * Set the success url.
     *
     * @param string $successUrl
     *
     * @return \ExpandCart\Foundation\Support\Factories\Payments\FastpayCash\FastpayCash
     */
    public function setSuccessUrl($successUrl)
    {
        $this->successUrl = $successUrl;

        return $this;
    }

    /**
     * Set the fail url.
     *
     * @param string $failUrl
     *
     * @return \ExpandCart\Foundation\Support\Factories\Payments\FastpayCash\FastpayCash
     */
    public function setFailUrl($failUrl)
    {
        $this->failUrl = $failUrl;

        return $this;
    }

    /**
     * Set the cacnel url.
     *
     * @param string $cancelUrl
     *
     * @return \ExpandCart\Foundation\Support\Factories\Payments\FastpayCash\FastpayCash
     */
    public function setCancelUrl($cancelUrl)
    {
        $this->cancelUrl = $cancelUrl;

        return $this;
    }

    /**
     * Create the payment and return the API response.
     *
     * @return string
     */
    public function payment()
    {
        $http = new Http();

        $http->setUrl(
            ($this->apiEndPoint ?: ($this->endPoints[$this->mode] . 'merchant/generate-payment-token'))
        );

        $http->setMethod('post')->setPostData([
            'merchant_mobile_no' => $this->merchantNo,
            'store_password' => $this->storePassword,
            'order_id' => $this->orderId,
            'bill_amount' => $this->amount,
            'success_url' => $this->successUrl,
            'fail_url' => $this->failUrl,
            'cancel_url' => $this->cancelUrl,
        ]);

        $response = $http->run();

        return $response;
    }

    /**
     * Validate a givin payment.
     *
     * @return string
     */
    public function validate()
    {
        $http = new Http();

        $http->setUrl(
            ($this->apiEndPoint ?: ($this->endPoints[$this->mode] . 'merchant/payment/validation'))
        );

        $http->setMethod('post')->setPostData([
            'merchant_mobile_no' => $this->merchantNo,
            'store_password' => $this->storePassword,
            'order_id' => $this->orderId,
        ]);

        $response = $http->run();

        return $response;
    }
}
