<?php

namespace ExpandCart\Foundation;

abstract class PaymentController extends \Controller
{
    /**
     * This variable will be reflected in the view of the v2 checkout
     * When the end customer chooses this method, he will be shown a hint message 
     * that he or she will be directed to another server to complete the payment process.
     */
    protected static $isExternalPayment;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->data['is_external'] = static::$isExternalPayment && $this->isCheckoutV2Available();
    }

    /**
     * Check if v2 of checkout is available.
     *
     * @return boolean
     */
    public function isCheckoutV2Available(): bool
    {
        return ($this->identity->isStoreOnWhiteList()
            && defined('THREE_STEPS_CHECKOUT')
            && (int)THREE_STEPS_CHECKOUT === 1
            && (!\Extension::isInstalled('quickcheckout') || (\Extension::isInstalled('quickcheckout') && (int)$this->config->get('quickcheckout')['try_new_checkout'] === 1)));
    }
    
    /**
     * The main function 
     *
     * @return void
     */
    abstract public function index();
}
