<?php

class Whmcs{

    protected $data;

    protected function apiCall($userId,$action,&$postFields){

        $url = BILLING_API_URL; # URL to WHMCS API file
        $postFields["action"] = $action; #action performed by the [[API:Functions]]

        $username = BILLING_API_USERNAME; # Admin username goes here
        $password = BILLING_API_PASSWORD; # Admin password goes here

        $postFields["username"] = $username;
        $postFields["password"] = md5($password);

        $postFields["clientid"] = $userId; #action performed by the [[API:Functions]]

        $postFields["responsetype"] = "json"; #action performed by the [[API:Functions]]

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 200);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query( $postFields) );
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $this->data = curl_exec($ch);
        curl_close($ch);
    }
    /**
     * @param $userId
     * @return string
     */
    public function getClientPhone($userId)
    {
        $results =null;

        $this->apiCall($userId,"GetClientsDetails",$postFields);

        $results = json_decode($this->data,1);

        if ($results["result"]!="success") {
            //HandleError("Error occurred");
            $this->record_error_msg(json_encode($results));
            return "";
        }
        else {
            return "+".$results['client']['phonenumber'];
        }
    }

    public function getClientDetails($userId)
    {
        $results =null;

        $this->apiCall($userId,"GetClientsDetails",$postFields);

        $results = json_decode($this->data,1);

        if ($results["result"]!="success") {
            //HandleError("Error occurred");
            $this->record_error_msg(json_encode($results));
            return "";
        }
        else {
            return $results['client'];
        }
    }

    public function getRecurringBillingValues($userId,$invoice_id){
        $results =null;

        $postFields["sub_action"]="getRecurringBillingValues";
        $postFields['invoiceid'] = $invoice_id;
        $this->apiCall($userId,"expandapi",$postFields);

        $results = json_decode($this->data,1);

        if ($results["status"] != "OK") {
            //HandleError("Error occurred");
            $this->record_error_msg(json_encode($results));
            return "";
        }
        else {
            return $results['data'];
        }
    }

    public function getUpgradeRecurringValues($userId,$invoice_id){
        $results =null;

        $postFields["sub_action"]="getUpgradeRecurringValues";
        $postFields['invoiceid'] = $invoice_id;
        $this->apiCall($userId,"expandapi",$postFields);

        $results = json_decode($this->data,1);

        if ($results["status"] != "OK") {
            //HandleError("Error occurred");
            $this->record_error_msg(json_encode($results));
            return "";
        }
        else {
            return $results['data'];
        }
    }

    public function record_error_msg($msg){
        $errors=array(
            "StoreCode" => STORECODE,
            "DateTime" => date("Y-m-d h:i:s A"),
            "Msg" => $msg
        );
        $errors_in_json=json_encode($errors).", \r\n";
        file_put_contents(ONLINE_STORES_PATH."/OnlineStores/system/logs/whmcs_errors.json",$errors_in_json,FILE_APPEND);

    }

    public function updateClientPhone($userId,$clientPhone)
    {
        $postFields["phonenumber"]=$clientPhone;
        $this->apiCall($userId,"UpdateClient",$postFields);
    }
}
