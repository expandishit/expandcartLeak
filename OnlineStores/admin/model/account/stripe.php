<?php

class ModelAccountStripe extends Model
{
    /**
     * @var string
     */
    private $accessToken = '';

     /**
     * @var object
     */
    private $sourceId = null;

     /**
     * @var array
     */
    private $_errors = [];

    /**
     * Return all errors.
     *
     * @return array
     */
    public function getErrors() : array
    {
        return $this->_errors;
    }

    /**
     * Get token
     *
     * @param string $stripeToken
     *
     * @return string
     */
    public function retrieveToken(string $stripeToken)
    {
        try {
            $token = \Stripe\Token::retrieve($stripeToken);
        } catch (\Exception $e) {
            $this->_errors[] = $e->getMessage();
            return false;
        }

        $this->accessToken = $token->id;

        return $token;
    }

    /**
     * Create token
     *
     * @param array $card
     *
     * @return object
     */
    public function createToken(array $card)
    {
        try {
            $token = Stripe\Token::create(["card" => $card]);
            if (STAGING_MODE == 1){
                $this->log($token);
            }
        } catch (\Exception $e) {
            $this->_errors[] = $e->getMessage();
            return false;
        }

        $this->accessToken = $token->id;

        return $token;
    }

    /**
     * Create source
     *
     * @param array $body
     *
     * @return object
     */
    public function createSource(array $body)
    {
        try {
            $source = Stripe\Source::create($body);
            if (STAGING_MODE == 1){
                $this->log($source);
            }
        } catch (\Exception $e) {
            $this->_errors[] = $e->getMessage();
            return false;
        }

        $this->sourceId = $source->id;

        return $source;
    }

    /**
     * retrieve source
     *
     * @param string $source_id
     * @param array $options
     *
     * @return object
     */
    public function retrieveSource(string $id, array $options = [])
    {
        try {
            $source = Stripe\Source::retrieve($id, $options);
            if (STAGING_MODE == 1){
                $this->log($source);
            }
        } catch (\Exception $e) {
            $this->_errors[] = $e->getMessage();
            return false;
        }

        $this->sourceId = $source->id;

        return $source;
    }

    /**
     * Create the payment charge
     *
     * @param string $customerId
     * @param int $amount
     * @param string $currency
     * @param string $customer
     * @param string $storecode
     * @param string $orderId
     * @param string $invoiceNumber
     * @return object
     */
    public function charge(string $customerId, int $amount, string $currency, string $customer, string $storecode,string $orderId,string $invoiceNumber)
    {
        try {
            $charge = \Stripe\Charge::create([
                'amount' => $amount,
                'currency' => strtolower($currency),
                'customer' => $customerId,
                'description' => vsprintf('ExpandCart - %s رقم الفاتورة', [
                    $invoiceNumber
                ]),
                "metadata" => ["id" => $orderId, "invoiceNumber" => $invoiceNumber],
                'statement_descriptor'=>'EXPANDCART',
//                'ip'=>$_SERVER['REMOTE_ADDR']
            ]);

        } catch (\Exception $e) {
            $this->_errors[] = $e->getMessage();

            return false;
        }

        if (STAGING_MODE == 1){
            $this->log($charge);
        }

        return $charge;
    }

    public function retrieveTransaction(string $balance_transaction)
    {
        try {
            $transaction = \Stripe\BalanceTransaction::retrieve($balance_transaction);

        } catch (\Exception $e) {
            $this->_errors[] = $e->getMessage();

            return false;
        }

        if (STAGING_MODE == 1){
            $this->log($transaction);
        }

        return $transaction;
    }

    /**
     * Create the payment charge using a 3D secure source
     *
     * @param string $sourceid
     * @param int $amount
     * @param string $currency
     * @param string $storecode
     * @param string $invoiceNumber
     * @return object
     */
    public function sourceCharge(string $sourceid, string $customerid, int $amount, string $currency, string $storecode, string $invoiceNumber)
    {
        try {
            $charge = \Stripe\Charge::create([
                'amount' => $amount,
                'currency' => strtolower($currency),
                'source' => $sourceid,
                'customer' => $customerid,
                'description' => vsprintf('ExpandCart - %s رقم الفاتورة', [
                    $invoiceNumber
                ]),
                "metadata" => ["id" => $orderId, "invoiceNumber" => $invoiceNumber],
                'statement_descriptor'=>'EXPANDCART',
//                'ip'=>$_SERVER['REMOTE_ADDR']
            ]);

        } catch (\Exception $e) {
            $this->_errors[] = $e->getMessage();

            return false;
        }

        if (STAGING_MODE == 1){
            $this->log($charge);
        }

        return $charge;
    }

    /**
     * Create Subscription
     *
     * @param string $customerId
     * @param string $item_price
     *
     * @return false|object
     */
    public function createSubscription(string $customerId,string $item_price)
    {
        try {
            $subscribe = \Stripe\Subscription::create([
                'customer' => $customerId,
                'items' => [
                    ['price' => $item_price],
                ],
            ]);

        } catch (\Exception $e) {
            $this->_errors[] = $e->getMessage();

            return false;
        }

        return $subscribe;
    }


    /**
     * Create customer
     *
     * @param int $id
     * @param string $customer
     * @param string $email
     * @param string $country
     * @return object
     */
    public function createCustomer(int $id, string $customer, string $email,string $country="")
    {
        try {
            $customer = Stripe\Customer::create([
                "source" => $this->sourceId,
                "description" => "Customer for " . $customer . " (" . $email . ")",
                "email" => $email,
                "name"=>$customer,
                "address"=>["country"=>$country],
                "metadata" => ["id" => $id, "fullName" => $customer, "email" => $email]
            ]);
        } catch (\Exception $e) {
            $this->_errors[] = $e->getMessage();
            return false;
        }

        if (STAGING_MODE == 1){
            $this->log($customer);
        }

        return $customer;
    }

    /**
     * Create customer by stripe customer id
     *
     * @param string $customerId
     *
     * @return object
     */
    public function retrieveCustomer(string $customerId)
    {
        try {
            $customer = Stripe\Customer::retrieve($customerId);
        } catch (\Exception $e) {
            $this->_errors[] = $e->getMessage();
            return false;
        }

        if (STAGING_MODE == 1){
            $this->log($customer);
        }

        return $customer;
    }

    /**
     * Validate a given card
     *
     * @param array $card
     *
     * @return bool
     */
    public function validateCard(array $card) : bool
    {
        $errors = [];
        if (!isset($card['number']) || empty($card['number'])) {
            $errors['card-number'] = 'empty';
        }elseif (!preg_match('#^[0-9]{15,16}$#', $card['number'])) {
            $errors['card-number'] = 'wrong';
        }


        if (  empty($card['exp_month']) || empty($card['exp_year']) ) {
            $errors['expiry'] = 'empty';
        }else{
            $card['exp_month'] = (int)$card['exp_month'];

            if ($card['exp_month'] < 1 || $card['exp_month'] > 12) {
                $errors['expiry'] = 'wrong';
            }

            $card['exp_year'] = (int)$card['exp_year'];

            if ($card['exp_year'] < date('Y')) {
                $errors['expiry'] = 'wrong';
            }elseif ( ($card['exp_year'] == date('Y')) && ( $card['exp_month'] < date('m') ) ){
                $errors['expiry'] = 'wrong';
            }
        }

        if ( empty($card['cvc']) ) {
            $errors['cvc'] = 'empty';
        }elseif (!preg_match('#^[0-9]{3,}$#', $card['cvc'])) {

            $errors['cvc'] = 'wrong';
        }

        if (count($errors) == 0) {
            return true;
        }

        $this->_errors = $errors;

        return false;
    }

    public function log($msg){
        $log_array=array(
            "StoreCode" => STORECODE,
            "DateTime" => date("Y-m-d h:i:s A"),
            "Msg" => $msg,
            "type"=>"stripe"
        );
        $log_in_json=json_encode($log_array).", \r\n";
        file_put_contents(ONLINE_STORES_PATH."/OnlineStores/system/logs/payment.json",$log_in_json,FILE_APPEND);

    }
}
