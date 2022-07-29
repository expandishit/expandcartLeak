<?php

namespace ExpandCart\Foundation\Support\Factories\PayPal\Traits\Payment;

use PayPal\Api\PaymentExecution;

trait Execute
{
    /**
     * The payer Id string.
     *
     * @var string
     */
    protected $payerId;

    /**
     * Set the payer Id string.
     *
     * @param string $payerId
     *
     * @return \ExpandCart\Foundation\Support\Factories\PayPal\Payment
     */
    public function setPayerId($payerId)
    {
        $this->payerId = $payerId;

        return $this;
    }

    /**
     * Execute the approved created payment.
     *
     * @return PayPal\Api\Payment|\PayPal\Exception\PayPalConnectionException|\Exception
     */
    public function execute()
    {
        $execution = new PaymentExecution();

        $execution->setPayerId($this->payerId);

        try {

            if (!$this->contextConfig) {
                $this->setContextConfig(false);
            }

            $result = $this->paymentInfo->execute($execution, $this->apiContext);

            return $result;

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
