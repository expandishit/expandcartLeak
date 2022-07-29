<?php

namespace ExpandCart\Foundation\Support\Factories\PayPal\Traits\Payment;

use PayPal\Api\Item;
use PayPal\Api\Payer;
use PayPal\Api\Amount;
use PayPal\Api\Payment as PayPalPayment;
use PayPal\Api\Details;
use PayPal\Api\ItemList;
use PayPal\Api\Transaction;
use PayPal\Rest\ApiContext;
use PayPal\Api\RedirectUrls;
use PayPal\Auth\OAuthTokenCredential;

/*
 * TODO
 * Move this to the factories directory which should located under \App\Factories namespace;
 */

trait Create
{
    /**
     * The currency string.
     *
     * @var string
     */
    protected $currency;

    /**
     * The total amount.
     *
     * @var float
     */
    protected $total;

    /**
     * The invoice number string.
     *
     * @var string
     */
    protected $invoiceNum = null;

    /**
     * The description string.
     *
     * @var string
     */
    protected $description = null;

    /**
     * The item lit.
     *
     * @var array
     */
    protected $items = [];

    /**
     * The return url string.
     *
     * @var string
     */
    protected $returnUrl;

    /**
     * The cancel url string.
     *
     * @var string
     */
    protected $cancelUrl;

    /**
     * The details data.
     *
     * @var array
     */
    protected $details = [];

    /**
     * The subtotal ammount.
     *
     * @var float
     */
    protected $subTotal;


    /**
     * Set the currency string.
     *
     * @param string $currency
     *
     * @return \ExpandCart\Foundation\Support\Factories\PayPal\Payment
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Add an item to the item list.
     *
     * @param array $product
     *
     * @return \ExpandCart\Foundation\Support\Factories\PayPal\Payment
     */
    public function addItem($product)
    {
        $item = new Item();
        $item->setName($product['name']);
        $item->setCurrency($this->currency);
        $item->setQuantity($product['quantity']);
        $item->setPrice($product['price']);

        $this->items[] = $item;

        return $this;
    }

    /**
     * Add an item to the item list.
     *
     * @param array $product
     *
     * @return \ExpandCart\Foundation\Support\Factories\PayPal\Payment
     * @alias self::addItem()
     */
    public function addProduct($product)
    {
        return $this->addItem($product);
    }

    /**
     * Add an item to the item list.
     *
     * @param array $product
     *
     * @return \ExpandCart\Foundation\Support\Factories\PayPal\Payment
     * @alias self::addItem()
     */
    public function setItem($item)
    {
        return $this->addItem($item);
    }

    /**
     * Set the total.
     *
     * @param flaot $total
     *
     * @return \ExpandCart\Foundation\Support\Factories\PayPal\Payment
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Set the return url string.
     *
     * @param string $returnUrl
     *
     * @return \ExpandCart\Foundation\Support\Factories\PayPal\Payment
     */
    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;

        return $this;
    }

    /**
     * Set the cancel url string.
     *
     * @param string $cancelUrl
     *
     * @return \ExpandCart\Foundation\Support\Factories\PayPal\Payment
     */
    public function setCancelUrl($cancelUrl)
    {
        $this->cancelUrl = $cancelUrl;

        return $this;
    }

    /**
     * Set the invoice string.
     *
     * @param string $invoice
     *
     * @return \ExpandCart\Foundation\Support\Factories\PayPal\Payment
     */
    public function setInvoice($invoice)
    {
        $this->invoiceNum = $invoice;

        return $this;
    }

    /**
     * Set the description string.
     *
     * @param string $description
     *
     * @return \ExpandCart\Foundation\Support\Factories\PayPal\Payment
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set the details shipping cost.
     *
     * @param float $shipping
     *
     * @return \ExpandCart\Foundation\Support\Factories\PayPal\Payment
     */
    public function setShipping($shipping)
    {
        $this->details['shipping'] = $shipping;

        return $this;
    }

    /**
     * Set the details taxes cost.
     *
     * @param float $tax
     *
     * @return \ExpandCart\Foundation\Support\Factories\PayPal\Payment
     */
    public function setTax($tax)
    {
        $this->details['tax'] = $tax;

        return $this;
    }

    /**
     * Set the details sub total cost.
     *
     * @param float $subTotal
     *
     * @return \ExpandCart\Foundation\Support\Factories\PayPal\Payment
     */
    public function setSubTotal($subTotal)
    {
        $this->subTotal = $subTotal;

        return $this;
    }

    /**
     * Creates the payment.
     *
     * @return PayPal\Api\Payment|\PayPal\Exception\PayPalConnectionException|\Exception
     */
    public function create()
    {
        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        $itemList = new ItemList();
        $itemList->setItems($this->items);

        if ($this->details) {
            $details = new Details;

            if (isset($this->details['shipping'])) {
                $details->setShipping($this->details['shipping']);
            }

            if (isset($this->details['tax'])) {
                $details->setTax($this->details['tax']);
            }

            $details->setSubtotal($this->subTotal);
        }

        $amount = new Amount();
        $amount->setCurrency($this->currency)
            ->setTotal($this->total)
            ->setDetails($details)
        ;

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription(($this->description ?: ("Product Num " . $this->invoiceNum)))
            ->setInvoiceNumber($this->invoiceNum);

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($this->returnUrl)
            ->setCancelUrl($this->cancelUrl);

        $payment = new PayPalPayment();
        $payment->setIntent("sale")
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions(array($transaction));

        try {

            if (!$this->contextConfig) {
                $this->setContextConfig(false);
            }

            $payment->create($this->apiContext);

            return $payment->getApprovalLink();
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
