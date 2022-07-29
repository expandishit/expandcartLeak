<?php

namespace ExpandCart\Foundation\Support\Factories\PayPal;

ini_set('display_errors', 1);

use \PayPal\Api\Webhook as PayPalWebhook;
use \PayPal\Api\WebhookEventType;

use ExpandCart\Foundation\Support\Factories\PayPal\Traits\ApiContext;
use ExpandCart\Foundation\Support\Factories\PayPal\Traits\Webhook as WebhookTraits;

class Webhook
{
    use ApiContext, WebhookTraits\Verify;

    /**
     * The Webhook URL string.
     *
     * @var string
     */
    private $webhookUrl;

    /**
     * The array of available events types.
     *
     * @var array
     */
    private $webhookEventTypes = [];

    /**
     * The array of current supported events types.
     *
     * @var array
     */
    private $supportedEvents = [
        'PAYMENT.SALE.COMPLETED',
        'PAYMENT.SALE.DENIED',
        'PAYMENT.SALE.PENDING',
        'PAYMENT.SALE.REFUNDED',
        'PAYMENT.SALE.REVERSED',
    ];

    /**
     * The webhook object.
     *
     * @var \PayPal\Api\Webhook
     */
    protected $webhook;

    /**
     * Construct the Webhook object.
     *
     * @return void
     */
    public function __construct()
    {
        $this->webhook = new PayPalWebhook;
    }

    /**
     * Set the $webhookUrl string.
     *
     * @param string $url
     *
     * @return \ExpandCart\Foundation\Support\Factories\PayPal\Webhook
     */
    public function setUrl($url)
    {
        $this->webhookUrl = $url;

        return $this;
    }

    /**
     * Add event type to the $webhookEventTypes array.
     *
     * @param string $event
     *
     * @return void
     */
    private function setEventType($event)
    {
        $events = array_flip($this->supportedEvents);

        if (isset($events[$event])) {
            $this->webhookEventTypes[] = new WebhookEventType('{"name": "' . $event . '"}');
        }
    }

    /**
     * Add an event to the $webhookEventTypes array.
     *
     * @param string $event
     *
     * @return \ExpandCart\Foundation\Support\Factories\PayPal\Webhook
     */
    public function addEventType($event)
    {
        $this->setEventType($event);

        return $this;
    }

    /**
     * Add a set of events to the $webhookEventTypes array.
     *
     * @param array $events
     *
     * @return \ExpandCart\Foundation\Support\Factories\PayPal\Webhook
     */
    public function addEventTypes($events)
    {
        foreach ($events as $event) {
            $this->setEventType($event);
        }
    }

    /**
     * Create the webhook.
     *
     * @return \PayPal\Api\Webhook|\PayPal\Exception\PayPalConnectionException|\Exception
     */
    public function create()
    {
        $this->webhook->setUrl($this->webhookUrl);

        $this->webhook->setEventTypes($this->webhookEventTypes);

        try {

            if (!$this->contextConfig) {
                $this->setContextConfig(false);
            }

            $output = $this->webhook->create($this->apiContext);

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

    /**
     * Set $webhookId proberty.
     *
     * @param mixed $webhookId
     *
     * @return \ExpandCart\Foundation\Support\Factories\PayPal\Webhook
     */
    public function setWebhookId($webhookId)
    {
        $this->webhookId = $webhookId;

        return $this;
    }

    /**
     * Get webhook details.
     *
     * @return \PayPal\Api\Webhook|\PayPal\Exception\PayPalConnectionException|\Exception
     */
    public function get()
    {
        try {

            if (!$this->contextConfig) {
                $this->setContextConfig(false);
            }

            $output = $this->webhook->get($this->webhookId, $this->apiContext);
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