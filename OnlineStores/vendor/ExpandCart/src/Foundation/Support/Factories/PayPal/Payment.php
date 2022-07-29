<?php

namespace ExpandCart\Foundation\Support\Factories\PayPal;

use PayPal\Api\Item;
use PayPal\Api\Payer;
use PayPal\Api\Amount;
use PayPal\Api\Payment as PayPalPayment;
use PayPal\Api\Details;
use PayPal\Api\ItemList;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;

use ExpandCart\Foundation\Support\Factories\PayPal\Traits\Payment as PaymentTraits;
use ExpandCart\Foundation\Support\Factories\PayPal\Traits\ApiContext;

class Payment
{
    use ApiContext, PaymentTraits\Create,PaymentTraits\Get, PaymentTraits\Execute;

    /**
     * The payment object.
     *
     * @var PayPal\Api\Payment
     */
    protected $payment;

    /**
     * Set the payer key string.
     *
     * @param string $payer
     *
     * @return \ExpandCart\Foundation\Support\Factories\PayPal\Payment
     */
    public function setPayer($payer)
    {
        $this->payer = $payer;

        return $this;
    }
}