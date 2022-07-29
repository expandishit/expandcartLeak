<?php

namespace Api\Models\Module;

use Psr\Container\ContainerInterface;

class Knawat
{
    private $load;
    private $registry;
    private $config;
    private $knawat_model;

    public function __construct(ContainerInterface $container)
    {
        $this->registry = $container['registry'];
        $this->config = $container['config'];
        $this->load = $container['loader'];

        $this->load->model('module/knawat');
        $this->knawat_model =$this->registry->get('model_module_knawat');
    }

    /*** Get Product ID based on sku*/
    public function isInstalled():bool
    {
        return $this->knawat_model->isInstalled();
    }

    /*** Install Knawat */
    public function install() : void
    {
        $this->knawat_model->install();
    }

    /*** Update knawat settings */
    public function updateSettings($data=[]) : void
    {
        $this->knawat_model->updateSettings($data);
    }

    /*** Get knawat settings */
    public function getSettings() : array
    {
        return $this->knawat_model->getSettings();
    }

    /*** Generate Token */
    public function generateToken($data=[]) : string
    {
        return $this->knawat_model->generateToken($data);
    }

    /*** Log webhook call */
    public function logWebhookCall($topic, $status=0,$target=null) : int
    {
        return $this->knawat_model->logWebhookCall($topic, $status,$target);
    }

    /*** change Log webhook status */
    public function updateLogStatus($id, $status=1) : void
    {
        $this->knawat_model->updateLogStatus($id, $status);
    }

    /*** Get Product ID based on sku*/
    public function getProductIdBySku($sku = '')
    {
        return $this->knawat_model->getProductIdBySku($sku);
    }


    /***************** get knawat Product Details ***************/
    public function getKnawatProductDetails($sku)
    {
        return $this->knawat_model->getKnawatProductDetails($sku);
    }

    /***************** save knawat Product  ***************/
    public function saveKnawatProduct($product) : int
    {
        return $this->knawat_model->saveKnawatProduct($product);
    }

    /***************** save knawat Product  ***************/
    public function updateKnawatProduct($product_id, $product) : void
    {
        $this->knawat_model->updateKnawatProduct($product_id, $product);
    }

    /***************** update knawat Products price with new profits  ***************/
    public function updateKnawatProductsPriceWithNewProfit($old_profit_price, $new_profit_price) : void
    {
        $this->knawat_model->updateProductsPriceOnProfitChange($old_profit_price, $new_profit_price);
    }

    /***************** delete knawat Product  ***************/
    public function deleteKnawatProductBySku($sku = "") :void
    {
        $this->knawat_model->deleteKnawatProductBySku($sku);
    }

    /***************** delete knawat Product  ***************/
    public function deleteKnawatProductByKnawatSku($sku = "") :void
    {
        $this->knawat_model->deleteKnawatProductByKnawatSku($sku);
    }

    /***************** format knawat order id if exists***************/
    public function getKnawatOrderId($knawat_order_id)
    {
        return $this->knawat_model->getKnawatOrderId($knawat_order_id);
    }

    /***************** update order status***************/
    public function updateOrderStatus($order_id, $data=[])
    {
        $this->load->model('sale/order');
        return $this->registry->get('model_sale_order')->addOrderHistory($order_id, $data);
    }

    /***************** get client secret by client id***************/
    public function getClientSecretByClientId($client_id)
    {
        $this->load->model('api/clients');
        return $this->registry->get('model_api_clients')->getClientByClientId($client_id);
    }

    /***************** set product options***************/
    public function setProductOptions($product)
    {
        $this->knawat_model->setProductOptions($product);
    }

    /***************** get product view url ***************/
    public function getProductUrl($product_id)
    {
        if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1')))
            $base = "https://" . DOMAINNAME;
        else
            $base = "http://" . DOMAINNAME;
        return $base ."/index.php?route=product/product&product_id=" .$product_id;
    }

    /**
     * a proxy method to get the required data from the actual model
     *
     * @return array
     */
    public function getKnawatProductsSku()
    {
        return $this->knawat_model->getKnawatProductsSku();
    }
}
