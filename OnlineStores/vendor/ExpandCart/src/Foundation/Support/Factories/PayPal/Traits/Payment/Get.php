<?php

namespace ExpandCart\Foundation\Support\Factories\PayPal\Traits\Payment;

use PayPal\Api\Payment;

trait Get
{
    /**
     * The payment Id string.
     *
     * @var string
     */
    protected $paymentId;

    /**
     * The Webhook URL string.
     *
     * @var string
     */
    protected $paymentInfo;

    /**
     * Set the payment Id string.
     *
     * @param string $paymentId
     *
     * @return \ExpandCart\Foundation\Support\Factories\PayPal\Payment
     */
    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    /**
     * Get the payment details.
     *
     * @return PayPal\Api\Payment|\PayPal\Exception\PayPalConnectionException|\Exception
     */
    public function get($paymentId = null)
    {
        if ($paymentId) {
            $this->setPaymentId($paymentId);
        }

        $this->payment = new Payment();

        try {

            if (!$this->contextConfig) {
                $this->setContextConfig(false);
            }

            $this->paymentInfo = $this->payment->get($this->paymentId, $this->apiContext);

            return $this->paymentInfo;

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
