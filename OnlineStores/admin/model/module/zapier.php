<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ModelModuleZapier extends Model
{

    /**trigger types

     * new_order_trigger
     * update_order_status_trigger
     * new_product_trigger
     * update_product_trigger
     * delete_product_trigger
     * new_customer_trigger

     * */

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

    /**
     *   Install the required values for the application.
     *
     *   @return boolean whether successful or not.
     */
    public function install($store_id = 0)
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->insertUpdateSetting('zapier', ['zapier' => ['status'=>0]]);

        //create webhook table
        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."zapier_hooks` (
            `hook_id` int(11) NOT NULL AUTO_INCREMENT,
            `url` varchar(255) NOT NULL,
            `type` varchar(50) NOT NULL,
            PRIMARY KEY (`hook_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");
    }

    /* update the module settings.
     *
     * @param array $inputs
     *
     * @return bool
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->insertUpdateSetting('zapier', $inputs);
        return true;
    }

    /**
     *   Remove the values from the database.
     *
     *   @return boolean whether successful or not.
     */
    public function uninstall($store_id = 0, $group = 'zapier')
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting($group);
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."zapier_hooks`");

    }


    /************* Subscribe Hook **********
     * @param $type
     * @param $url
     * @return integer $hook_id
     */
    public function subscribeHook($type,$url)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "zapier_hooks SET url = '". $this->db->escape($url) ."', type = '". $this->db->escape($type) ."' ");
        return $this->db->getLastId();
    }


    /************* UnSubscribe Hook **********
     * @param $id
     */
    public function unSubscribeHook($id)
    {
        $this->getHooks('new_product_trigger');
        $this->db->query("DELETE FROM " . DB_PREFIX . "zapier_hooks WHERE hook_id = '" . (int)$id . "'");
    }

    /**************************************  Triggers *************************************/


    //new order trigger
    public function newOrderTrigger($data) : void
    {
        foreach ($this->getHooks('new_order_trigger') as $hook)
            $this->sendZapierRequest($hook['url'],$data);
    }

    
    //update order trigger
    public function updateOrderStatusTrigger($data) : void
    {
        foreach ($this->getHooks('update_order_status_trigger') as $hook)
            $this->sendZapierRequest($hook['url'],$data);
    }


    //new product trigger
    public function newProductTrigger($data) : void
    {
        foreach ($this->getHooks('new_product_trigger') as $hook)
            $this->sendZapierRequest($hook['url'],$data);
    }


    //update product trigger
    public function updateProductTrigger($data) : void
    {
        foreach ($this->getHooks('update_product_trigger') as $hook)
            $this->sendZapierRequest($hook['url'],$data);
    }


    //delete product trigger
    public function deleteProductTrigger($data) : void
    {
        foreach ($this->getHooks('delete_product_trigger') as $hook)
            $this->sendZapierRequest($hook['url'],$data);
    }


    //new customer trigger
    public function newCustomerTrigger($data) : void
    {
        foreach ($this->getHooks('new_customer_trigger') as $hook)
            $this->sendZapierRequest($hook['url'],$data);
    }


    //get all stored hooks for specific trigger
    public function getHooks($type){
        return $this->db->query("SELECT `url` FROM " . DB_PREFIX . "zapier_hooks WHERE `type` = '". $this->db->escape($type) ."'")->rows;

    }

    //fire trigger webhook
    private function sendZapierRequest($url,$data=[]){

        $settings = $this->getSettings();

        //check if url not exist
        if (!isset($settings['status']) || $settings['status'] == 0)
            return false;

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
