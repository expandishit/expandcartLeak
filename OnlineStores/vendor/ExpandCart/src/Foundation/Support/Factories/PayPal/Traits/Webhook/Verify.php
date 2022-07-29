<?php

namespace ExpandCart\Foundation\Support\Factories\PayPal\Traits\Webhook;

use \PayPal\Api\VerifyWebhookSignature;

trait Verify
{

    /**
     * The array of headers sent by paypal to verify the request.
     *
     * @var array
     */
    protected $verifyHeaders = [];

    /**
     * The json request body.
     *
     * @var string
     */
    protected $verifyRequestBody;

    /**
     * The key string for the webhook.
     *
     * @var string
     */
    protected $webhookString;

    /**
     * Set $verifyHeaders proberty.
     *
     * @param array $headers
     *
     * @return \ExpandCart\Foundation\Support\Factories\PayPal\Webhook
     */
    public function setVerifyHeaders($headers)
    {
        $this->verifyHeaders = $headers;

        return $this;
    }

    /**
     * Set $verifyRequestBody proberty.
     *
     * @param string $body
     *
     * @return \ExpandCart\Foundation\Support\Factories\PayPal\Webhook
     */
    public function setVerifyRequestBody($body)
    {
        $this->verifyRequestBody = $body;

        return $this;
    }

    /**
     * Set $webhookString proberty.
     *
     * @param string $webhook
     *
     * @return \ExpandCart\Foundation\Support\Factories\PayPal\Webhook
     * @alias self::setWebhookString
     * @see self::setWebhookString
     */
    public function setWebHook($webhook)
    {
        return $this->setWebhookString($webhook);
    }

    /**
     * Set $webhookString proberty.
     *
     * @param string $webhook
     *
     * @return \ExpandCart\Foundation\Support\Factories\PayPal\Webhook
     */
    public function setWebhookString($webhook)
    {
        $this->webhookString = $webhook;

        return $this;
    }

    /**
     * Verify a given webhook event request.
     *
     * @return \PayPal\Api\Webhook|\PayPal\Exception\PayPalConnectionException|\Exception
     */
    public function verify()
    {
        $headers = $this->verifyHeaders;

        $signatureVerification = new \PayPal\Api\VerifyWebhookSignature();
        $signatureVerification->setAuthAlgo($headers['PAYPAL-AUTH-ALGO']);
        $signatureVerification->setTransmissionId($headers['PAYPAL-TRANSMISSION-ID']);
        $signatureVerification->setCertUrl($headers['PAYPAL-CERT-URL']);
        $signatureVerification->setWebhookId($this->webhookString);
        $signatureVerification->setTransmissionSig($headers['PAYPAL-TRANSMISSION-SIG']);
        $signatureVerification->setTransmissionTime($headers['PAYPAL-TRANSMISSION-TIME']);
        $signatureVerification->setRequestBody($this->verifyRequestBody);


        try {

            if (!$this->contextConfig) {
                $this->setContextConfig(false);
            }

            $output = $signatureVerification->post($this->apiContext);

            return $output;
        } catch (\PayPal\Exception\PayPalConnectionException $p) {
            return [
                'status' => 'error',
                'exception' => '\PayPal\Exception\PayPalConnectionException',
                'message' => $p->getData()
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'exception' => '\Exception',
                'message' => $e->getMessage()
            ];
        }
    }
}