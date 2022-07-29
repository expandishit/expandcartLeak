<?php

namespace ExpandCart\Foundation\Support\Factories\PayPal\Traits;

use PayPal\Rest\ApiContext as PayPalApiContext;
use PayPal\Auth\OAuthTokenCredential;

trait ApiContext
{

    /**
     * The paypal application client id.
     *
     * @var string
     */
    protected $clientId;

    /**
     * The paypal application client secret.
     *
     * @var string
     */
    protected $clientSecret;

    /**
     * The ApiContext object.
     *
     * @var PayPal\Rest\ApiContext
     */
    protected $apiContext;

    /**
     * The paypal environment mode.
     *
     * @var string
     */
    protected $mode = 'live';

    /**
     * The context config array.
     *
     * @var array
     */
    protected $contextConfig = null;

    /**
     * Set the environment mode.
     *
     * @param string $mode
     *
     * @return \ExpandCart\Foundation\Support\Factories\PayPal\Webhook
     */
    public function setMode($mode)
    {
        $this->mode = ($mode == 1 ? 'sandbox' : 'live');

        return $this;
    }

    /**
     * Gets the environment mode.
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Set api context object.
     *
     * @param array $context
     *
     * @return \ExpandCart\Foundation\Support\Factories\PayPal\Webhook
     */
    public function setContext($context)
    {
        $this->clientId = $context['client_id'];
        $this->clientSecret = $context['client_secret'];

        $this->apiContext = new PayPalApiContext(
            new OAuthTokenCredential(
                $this->clientId,
                $this->clientSecret
            )
        );

        return $this;
    }

    /**
     * Set api context config array.
     *
     * @param array $config
     *
     * @return \ExpandCart\Foundation\Support\Factories\PayPal\Webhook
     */
    public function setContextConfig($config = [])
    {
        if (!($this->contextConfig = $config) || is_array($config) === false || empty($config)) {
            $config = array(
                'mode' => $this->mode,
            );
        }
        $this->apiContext->setConfig($config);

        return $this;
    }


}
