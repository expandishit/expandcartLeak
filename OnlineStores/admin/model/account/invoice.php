<?php

class ModelAccountInvoice extends Model
{
    /**
     * @var string
     */
    private $apiUri = BILLING_API_URL;
//    private $apiUri = 'https://members.symbolar.com/includes/api.php';

    /**
     * @var string
     */
    private $username = BILLING_API_USERNAME;

    /**
     * @var string
     */
    private $password = BILLING_API_PASSWORD;

    /**
     * @var string
     */
    private $apiIdentifier = 'DPPwfOLuKT0CssjVfJFL8VC1Hh5x8gp4';

    /**
     * @var string
     */
    private $apiSecret = '0Qj7xx0othwVNOvhcVUJ2aoV4FoJ6ZOX';

    public function update_product(array $data)
    {
        $payment_method = $data['payment_method'];
        $serviceid = $data['serviceid'];
        $first_payment_amount = $data['first_payment_amount'];
        $recurring_amount = $data['recurring_amount'];
        $product_id = $data['product_id'];
        $bundle_id = $data['bundle_id'] ?? null;
        $billing_cycle = $data['billing_cycle'];
        $due_date = $data['due_date'] ? $data['due_date'] : null;

        $body = [
            'serviceid'         => $serviceid,
            'status'            => 'Active',
            'paymentmethod'     => $payment_method,
            'firstpaymentamount'=> $first_payment_amount,
            'recurringamount'   => $recurring_amount,
            'pid'               => $bundle_id ? $bundle_id : $product_id,
            'billingcycle'      => $billing_cycle
        ];

        if((int)$product_id == 3){
            $body['unset'] = ['subscriptionid'];
        }

        $newCustomFields = [];
        $clientProducts = $this->getClientsProducts();
        foreach ($clientProducts->products->product as $product) {
            if ($product->pid == PRODUCTID) {
                $CutsomFields = $product->customfields->customfield;
                $originalFields = $this->getCustomFields($product_id);
                foreach($CutsomFields as &$customField){
                    foreach($originalFields as $originalField){
                        if($customField->name == $originalField->fieldname){
                            $newCustomFields[$originalField->id] = $customField->value;
                            break;
                        }
                    }
                }

                $body['customfields'] = base64_encode(serialize($newCustomFields));
                break;
            }
        }

        if($due_date){
            $body['regdate'] = date('Y-m-d');
            $body['nextduedate'] = $due_date;
        }

        $product = $this->updateClientProduct($body);

        $productslimit = [
            '1' => 200,
            '2' => 300,
            '3' => 300,
            '52' => 0,
            '4' => 1000,
            '5' => 5000,
            '6' => 9999999,
            '8' => 9999999,
            '50' => 9999999,
            '53' => 9999999
        ];

        $this->ecusersdb->query(sprintf('UPDATE stores set productid = %s, productlimit = %s where storecode = "%s"', $product_id, $productslimit[(string)$product_id], STORECODE));

        return true;
    }

    /**
     * Create order in the whmcs api
     *
     * @param int $pid
     * @param string $paymentMethod
     *
     * @return mixed
     */
    public function createOrder(int $pid, string $paymentMethod, string $planType = null,$serviceid = null)
    {
        $request = array(
            'action' => 'AddOrder',
            // See https://developers.whmcs.com/api/authentication
            'username' => $this->username,
            'password' => md5($this->password),
            //'identifier' => $this->apiIdentifier,
            //'secret' => $this->apiSecret,
            'clientid' => WHMCS_USER_ID,
            'pid' => [$pid],
            /*'domain' => array('domain1.com', 'domain2.com'),
            'addons' => array('1,3,9', ''),
            'customfields' => array(
                base64_encode(serialize(array("1" => "Google"))),
                base64_encode(serialize(array("1" => "Google")))
            ),
            'configoptions' => array(
                base64_encode(serialize(array("1" => 999))),
                base64_encode(serialize(array("1" => 999)))
            ),
            'domaintype' => array('register', 'register'),
            'regperiod' => array(1, 2),
            'dnsmanagement' => array(0 => false, 1 => true),
            'nameserver1' => 'ns1.demo.com',
            'nameserver2' => 'ns2.demo.com',*/
            'paymentmethod' => $paymentMethod,
            'responsetype' => 'json',
        );

        if($serviceid){
            $request['serviceid'] = $serviceid;
        }

        if($planType){
            $request['billingcycle'] = [$planType];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUri);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(
                $request
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        if ($cer = curl_error($ch)) {
            $response = new stdClass;
            $response->error =$cer;

//            if (STAGING_MODE == 1){
                $this->log($response,"AddOrder",$request);
//            }

            return  $response;
        }
        curl_close($ch);

        $response = json_decode($response);

        $this->log($response,"AddOrder",$request);

        if ($response->result == 'success') {

            $this->acceptOrder($response->orderid, $serviceid);

            return $response;
        }

        return false;
    }

    public function createInvoice(string $paymentMethod, string $price, string $desc, $notes = null)
    {
        $request = array(
            'action' => 'CreateInvoice',
            // See https://developers.whmcs.com/api/authentication
            'username' => $this->username,
            'password' => md5($this->password),
            //'identifier' => $this->apiIdentifier,
            //'secret' => $this->apiSecret,
            'userid' => WHMCS_USER_ID,
            'sendinvoice' => '1',
            'itemamount1'=>$price,
            'itemdescription1' => $desc,
            'paymentmethod' => $paymentMethod,
            'date' => date('Y-m-d'),
            'duedate' => date('Y-m-d'),
            'autoapplycredit'=>'1',
            'responsetype' => 'json',
        );

        if($notes){
            $request['notes'] = $notes;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUri);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(
                $request
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        if ($cer = curl_error($ch)) {
            $response->error =$cer;

//            if (STAGING_MODE == 1){
            $this->log($response,"CreateInvoice",$request);
//            }

            return  $response;
        }
        curl_close($ch);

        $response = json_decode($response);

        $this->log($response,"CreateInvoice",$request);

        if ($response->result == 'success') {
            return $response;
        }

        return false;
    }

    /**
     * update invoice in the whmcs api
     *
     * @param int $invoice_id
     * @param array $body
     *
     * @return mixed
     */
    public function updateInvoice(int $invoice_id, array $body)
    {
        $request = array(
            'action' => 'UpdateInvoice',
            'username' => $this->username,
            'password' => md5($this->password),
            'invoiceid' => $invoice_id,
            'responsetype' => 'json',
        );

        $request = array_merge($request, $body);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUri);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(
                $request
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        if ($cer = curl_error($ch)) {
            $response = new stdClass;
            $response->error =$cer;

            $this->log($response,"UpdateInvoice",$request);

            return  $response;
        }
        
        curl_close($ch);

        $response = json_decode($response);

        $this->log($response,"UpdateInvoice",$request);

        if ($response->result == 'success') {
            return $response;
        }

        return false;
    }

    /**
     * Create order in the whmcs api
     *
     * @param string $paymentMethod
     * @param string $transactionId
     * @param float $amount
     * @param int $invoiceId
     * @param float $fees
     *
     * @return mixed
     */
    public function createTransaction(string $paymenthMethod, string $transactionId, float $amount, int $invoiceId, float $fees)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUri);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(
                array(
                    'action' => 'AddTransaction',
                    // See https://developers.whmcs.com/api/authentication
                     'username' => $this->username,
                     'password' => md5($this->password),
                    //'identifier' => $this->apiIdentifier,
                    //'secret' => $this->apiSecret,
                    'paymentmethod' => $paymenthMethod,
                     'userid' => WHMCS_USER_ID,
                    'transid' => $transactionId,
                    // 'date' => date('d/m/Y'),
                    // 'description' => 'A sample API payment',
                    'amountin' => $amount,
                    'invoiceid' => $invoiceId,
                    'fees' => $fees,
                    // 'fees' => '0.89',
                    // 'rate' => '1.00000',
                    'responsetype' => 'json',
                )
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        if ($cer = curl_error($ch)) {

//            if (STAGING_MODE == 1){
                $this->log($cer,'AddTransaction');
//            }
            return $cer;
        }
        curl_close($ch);

        $response = json_decode($response);

//        if (STAGING_MODE == 1){
            $this->log($response,'AddTransaction');
//        }

        if ($response->result == 'success') {
            return $response;
        }

        return false;
    }

    /**
     * Update whmcs client
     *
     * @param array $body
     *
     * @return mixed
     */
    public function updateClient(array $body)
    {
        $fullBody = [
            'action' => 'UpdateClient',
            //'identifier' => $this->apiIdentifier,
            //'secret' => $this->apiSecret,
            'username' => $this->username,
            'password' =>  md5($this->password),
            'clientid' => WHMCS_USER_ID,
            'responsetype' => 'json',
        ];

        $fullBody = array_merge($fullBody, $body);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUri);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($fullBody));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        if ($cer = curl_error($ch)) {
//            if (STAGING_MODE == 1){
                $this->log($body,'UpdateClient');
                $this->log($cer,'UpdateClient');
//            }
            return $cer;
        }
        curl_close($ch);

        $response = json_decode($response);

//        if (STAGING_MODE == 1){
            $this->log($cer,'UpdateClient');
//        }

        if ($response->result == 'success') {
            return $response;
        }

        return false;
    }

    public function getCustomFields($pid)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(
                array(
                    'action' => 'expandapi',
                    'sub_action' => 'getCustomFields',
                    'username' => $this->username,
                    'password' => md5($this->password),
                    'pid' => (string)$pid,
                    'responsetype' => 'json'
                )
            )
        );
        $response = curl_exec($ch);
        if ($cer = curl_error($ch)) {
            $response->error =$cer;
            $this->log($response,"getCustomFields");

            return  $response;
        }
        curl_close($ch);

        $response = json_decode($response);

        $this->log($response,"getCustomFields");

        if ($response->status == 'OK') {
            return $response->data;
        }

        return false;
    }

    public function updateTypeAndRelForInvoiceItems($serviceid, $invoiceid)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(
                array(
                    'action' => 'expandapi',
                    'sub_action' => 'updateTypeAndRelForInvoiceItems',
                    'username' => $this->username,
                    'password' => md5($this->password),
                    'serviceid' => (string)$serviceid,
                    'invoiceid' => (string)$invoiceid,
                    'responsetype' => 'json'
                )
            )
        );
        $response = curl_exec($ch);
        if ($cer = curl_error($ch)) {
            $response->error =$cer;
            $this->log($response,"updateTypeAndRelForInvoiceItems");

            return  $response;
        }
        curl_close($ch);

        $response = json_decode($response);

        $this->log($response,"updateTypeAndRelForInvoiceItems");

        if ($response->status == 'OK') {
            return $response->data->id;
        }

        return false;
    }

    public function getClientsProducts()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUri);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(
                array(
                    'action' => 'GetClientsProducts',
                    // See https://developers.whmcs.com/api/authentication
                    'username' => $this->username,
                    'password' => md5($this->password),
                    //'identifier' => $this->apiIdentifier,
                    //'secret' => $this->apiSecret,
                    'clientid' => WHMCS_USER_ID,
                    'stats' => true,
                    'responsetype' => 'json',
                )
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        if ($cer = curl_error($ch)) {
            $response->error =$cer;
            $this->log($response,"GetClientsProducts");

            return  $response;
        }
        curl_close($ch);

        $response = json_decode($response);

        $this->log($response,"GetClientsProducts");

        if ($response->result == 'success') {
            return $response;
        }

        return false;
    }

    public function getProducts($pid = null)
    {
        $request = array(
            'action' => 'GetProducts',
            // See https://developers.whmcs.com/api/authentication
            'username' => $this->username,
            'password' => md5($this->password),
            //'identifier' => $this->apiIdentifier,
            //'secret' => $this->apiSecret,
            'stats' => true,
            'responsetype' => 'json',
        );

        if($pid){
            $request['pid'] = $pid;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUri);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(
                $request
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        if ($cer = curl_error($ch)) {
            $response->error =$cer;
            $this->log($response,"GetProducts");

            return  $response;
        }
        curl_close($ch);

        $response = json_decode($response);

        $this->log($response,"GetProducts");

        if ($response->result == 'success') {
            return $response;
        }

        return false;
    }

    public function getPromotions()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUri);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(
                array(
                    'action' => 'GetPromotions',
                    // See https://developers.whmcs.com/api/authentication
                    'username' => $this->username,
                    'password' => md5($this->password),
                    //'identifier' => $this->apiIdentifier,
                    //'secret' => $this->apiSecret,
                    'stats' => true,
                    'responsetype' => 'json',
                )
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        if ($cer = curl_error($ch)) {
            $response->error =$cer;
            $this->log($response,"GetPromotions");

            return  $response;
        }
        curl_close($ch);

        $response = json_decode($response);

        $this->log($response,"GetPromotions");

        if ($response->result == 'success') {
            return $response;
        }

        return false;
    }

    public function getInvoice($invoiceid)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUri);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(
                array(
                    'action' => 'GetInvoice',
                    // See https://developers.whmcs.com/api/authentication
                    'username' => $this->username,
                    'password' => md5($this->password),
                    //'identifier' => $this->apiIdentifier,
                    //'secret' => $this->apiSecret,
                    'invoiceid' => $invoiceid,
                    'responsetype' => 'json',
                )
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        if ($cer = curl_error($ch)) {
            $response->error =$cer;
            $this->log($response,"GetInvoice");

            return  $response;
        }
        curl_close($ch);

        $response = json_decode($response);

        $this->log($response,"GetInvoice");

        if ($response->result == 'success') {
            return $response;
        }

        return false;
    }

    public function upgradeProduct(int $pid, string $paymentMethod, string $planType,int $serviceid,$calconly=false)
    {
        $request= array(
            'action' => 'UpgradeProduct',
            // See https://developers.whmcs.com/api/authentication
            'username' => $this->username,
            'password' => md5($this->password),
            //'identifier' => $this->apiIdentifier,
            //'secret' => $this->apiSecret,
            'serviceid' => $serviceid,
            'paymentmethod' => $paymentMethod,
            'newproductbillingcycle' => $planType,
            'type' => 'product',
            'newproductid' => $pid,
            'responsetype' => 'json',
        );

        if ($calconly) {
            $request['calconly'] = true;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUri);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(
                $request
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        if ($cer = curl_error($ch)) {
            $response->error =$cer;
            $this->log($response,"UpgradeProduct");
            return  $response;
        }
        curl_close($ch);

        $response = json_decode($response);

        $this->log($response,"UpgradeProduct");

        if ($response->result == 'success') {
            return $response;
        }

        return false;
    }

    public function updateClientProduct($body)
    {
        $request= array(
            'action' => 'UpdateClientProduct',
            // See https://developers.whmcs.com/api/authentication
            'username' => $this->username,
            'password' => md5($this->password),
            //'identifier' => $this->apiIdentifier,
            //'secret' => $this->apiSecret,
        );
        
        $request = array_merge($request, $body);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUri);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(
                $request
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        if ($cer = curl_error($ch)) {
            $response->error =$cer;
            $this->log($response,"UpdateClientProduct");
            return  $response;
        }
        curl_close($ch);

        $response = json_decode($response);

        $this->log($response,"UpdateClientProduct");

        if ($response->result == 'success') {
            return $response;
        }

        return false;
    }


    public function log($msg,$name="",$request=""){
        $log_array=array(
            "StoreCode" => STORECODE,
            "DateTime" => date("Y-m-d h:i:s A"),
            "Name"=>$name,
            "Request"=>$request,
            "Msg" => $msg,
            "type"=>"whmcs"
        );
        $log_in_json=json_encode($log_array).", \r\n";
        file_put_contents(ONLINE_STORES_PATH."/OnlineStores/system/logs/payment.json",$log_in_json,FILE_APPEND);

    }

    public function acceptOrder(int $orderId, $serviceid =null)
    {
        $request = array(
            'action' => 'AcceptOrder',
            'username' => $this->username,
            'password' => md5($this->password),
            'orderid' => $orderId,
            'responsetype' => 'json',
        );
        if ($serviceid){
            $request['serviceid']=$serviceid;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUri);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(
                $request
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        if ($cer = curl_error($ch)) {
            $response = new stdClass;
            $response->error =$cer;

            return  $response;
        }
        curl_close($ch);

        $response = json_decode($response);

        $this->log($response,"AcceptOrder",$request);

        if ($response->result == 'success') {
            return $response;
        }

        return false;
    }

    public function getCurrencies()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUri);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(
                array(
                    'action' => 'GetCurrencies',
                    // See https://developers.whmcs.com/api/authentication
                    'username' => $this->username,
                    'password' => md5($this->password),
                    //'identifier' => $this->apiIdentifier,
                    //'secret' => $this->apiSecret,
                    'stats' => true,
                    'responsetype' => 'json',
                )
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        if ($cer = curl_error($ch)) {
            $response->error =$cer;
            $this->log($response,"GetCurrencies");

            return  $response;
        }
        curl_close($ch);

        $response = json_decode($response);

        $this->log($response,"GetCurrencies");

        if ($response->result == 'success') {
            return $response;
        }

        return false;
    }
}
