<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ModelModuleZapier extends Model
{

    public function getSettings()
    {
        return $this->config->get('zapier') ?? [];
    }

    /**
     * Check if app installed
     *
     * @return boolean
     */
    public function isInstalled()
    {

        return Extension::isInstalled('zapier');
    }

    /**************************************  Triggers *************************************/


    //new order trigger
    public function newOrderTrigger($data) : bool
    {
        $settings = $this->getSettings();

        //check if url not exist
        if (!isset($settings['new_order_trigger']))
            return false;

        $response = $this->sendZapierRequest($settings['new_order_trigger'],$data);
        return $response ? true : false;
    }

    
    //new customer trigger
    public function newCustomerTrigger($data) : bool
    {
        $settings = $this->getSettings();

        //check if url not exist
        if (!isset($settings['new_customer_trigger']))
            return false;

        $response = $this->sendZapierRequest($settings['new_customer_trigger'],$data);
        return $response ? true : false;
    }





    private function sendZapierRequest($url,$data=[]){

        $client = new Client();
        try{

            $response = $client->request('POST',$url,['form_params' => $data]);
            $response = $response->getBody()->getContents();
            $response= json_decode($response);

        } catch (RequestException | \Exception $e) {
            $msg = "Unable to complete request ";
            // Catch all 4XX errors 
            if ($e instanceof RequestException && $e->hasResponse())
                $msg = " : the response given " . $e->getResponse()->getBody()->getContents();
                // throw new Exception($msg);
        }

        
        return $response;
    }

}
