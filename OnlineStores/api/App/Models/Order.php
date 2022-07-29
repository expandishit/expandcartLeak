<?php

namespace Api\Models;

use Psr\Container\ContainerInterface;

class Order
{
    private $load;
    private $registry;
    private $languagecodes;
    private $languageids;
    private $config;
    private $currency;

    public function __construct(ContainerInterface $container)
    {
        $this->registry = $container['registry'];
        $this->config = $container['config'];
        $this->load = $container['loader'];
        $this->languagecodes = $container['languagecodes'];
        $this->languageids = $container['languageids'];
        $this->currency = $container['currency'];
    }

    public function getAll($data)
    {
        $this->load->model('sale/order');
        $orders = $this->registry->get('model_sale_order')->getOrdersForApi($data);
        return $orders;
    }
	
	public function getOrderProducts($order_id,$product_designer=false)
    {
        $this->load->model('sale/order');
        $orders = $this->registry->get('model_sale_order')->getOrderProducts($order_id,$product_designer);
        return $orders;
    }

    public function getOrderForApi($order_id)
    {
        $this->load->model('sale/order');
        return $this->registry->get('model_sale_order')->getOrderForApi($order_id);
    }
	
    public function deleteOrder($order_id,$action)
    {
		$this->load->model('sale/order');
		$this->load->model('module/zoho_inventory');
		
         if ( $this->registry->get('model_sale_order')->deleteOrder( (int) $order_id, $action ) )
            {
                 $this->registry->get('model_module_zoho_inventory')->deleteOrder( (int) $order_id, $action );
                return true;
            }

      return false;
    }

    public function getStatuses()
    {
        $this->load->model('localisation/order_status');
        return $this->registry->get('model_localisation_order_status')->getOrderStatuses();
    }

    public function changeStatus($data)
    {
        $this->load->model('sale/order');
        return $this->registry->get('model_sale_order')->addOrderHistory($data['order_id'], $data);
    }

    public function getOrdersStatistics()
    {
        $this->load->model('report/home');
        $today = $this->registry->get('model_report_home')->getOrdersTotal('today') ?? 0;
        $unhandled = $this->registry->get('model_report_home')->unhandledOrdersCount() ?? 0;
        return ['today' => $today, 'unhandled' => $unhandled];
    }

	public function updateCustomerInfo($order_id,$customer)
    {
		$this->load->model('sale/order');
		
        if ( $this->registry->get('model_sale_order')->updateCustomerInfo( (int)$order_id,$customer ) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

	public function updateCustomerAddresses($order_id,$customer)
    {
		$this->load->model('sale/order');
		
        if ( $this->registry->get('model_sale_order')->updateCustomerAddresses( (int)$order_id,$customer ) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

	public function updateOrderManualGateway($order_id, $gatway_id, $isBundled)
    {
		$this->load->model('module/manual_shipping/order');
		
        if ( $this->registry->get('model_module_manual_shipping_order')->updateOrderManualGateway((int)$order_id, $gatway_id, $isBundled) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

	public function updateShippingTrackingURL($order_id, $url)
    {
		$this->load->model('sale/order');
		
        if ( $this->registry->get('model_sale_order')->updateShippingTrackingURL((int)$order_id, $url) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
	
	public function updateOrderProducts($order_id, $data)
    {
		$this->load->model('sale/order');
        $this->load->model('catalog/product');

        $newOrderProductsArray = [];
        $newOrderTotalArray = [];
        $counter = 0;
        $counter2 = 0;

        foreach ($data['order_products'] as $orderProduct) {
            $productInfo = $this->registry->get('model_catalog_product')->getProduct($orderProduct['product_id']);
            $newOrderProductsArray[$counter] = $orderProduct;
            $newOrderProductsArray[$counter]['name'] = $orderProduct['name'];
            $newOrderProductsArray[$counter]['model'] = $orderProduct['model'];
            $newOrderProductsArray[$counter]['price'] = $orderProduct['price'];
            $newOrderProductsArray[$counter]['tax'] = $this->config->get('config_tax_number');
            $newOrderProductsArray[$counter]['added_by_user_type'] = 'admin';
            $counter++;
        }

        foreach ($data['order_total'] as $orderTotal) {
            
            $newOrderTotalArray[$counter2] = $orderTotal;
            $newOrderTotalArray[$counter2]['text'] = $this->currency->format($orderTotal['value'],$this->config->get('config_currency'));
            $counter2++;
        }

        $data['order_products'] = $newOrderProductsArray;
        $data['order_total'] = $newOrderTotalArray;

        if ( $this->registry->get('model_sale_order')->updateOrderProducts((int)$order_id, $data) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
	
	public function get_shipping_methods($order_id)
	{
		$this->load->model('extension/shipping');
		$this->load->model('sale/order');
		
            $bundledShippingMethods = $this->registry->get('model_extension_shipping')->getEnabled();
            $manualShippingInfo = $this->registry->get('model_sale_order')->getManualShippingGatewayId($order_id);

            $shippingMethods = [];
            foreach ($bundledShippingMethods as $bundledShippingMethod) {

                if (
                    $manualShippingInfo['is_manual'] == 0 &&
                    $manualShippingInfo['id'] == $bundledShippingMethod['id']
                ) {
                    $this->data['manualShipping']['gateway'] = $bundledShippingMethod;
                }

                $shippingMethods[] = [
                    'id' => $bundledShippingMethod['id'],
                    'title' => $bundledShippingMethod['title'],
                    'bundled' => $bundledShippingMethod['bundled'],
                    'code' => $bundledShippingMethod['code'],
                ];
            }
			
				
            if (\Extension::isInstalled('manual_shipping')) {
                $manualShipping = $this->config->get('manual_shipping');

                if (isset($manualShipping['status']) && $manualShipping['status'] == 1) {
					$this->load->model('module/manual_shipping/gateways');
					
                    $manualShippingGateways =  $this->registry->get('model_module_manual_shipping_gateways')
													->getCompactShippingGateways([
														'start' => -1,
														'status' => 1,
														'language_id' => $this->config->get('config_language_id'),
													]);

                    $manualShipping_gateways = $manualShippingGateways['data'];
					
                    foreach ($manualShipping_gateways as $msgGateway) {
                        $shippingMethods[] = [
                            'id' => $msgGateway['id'],
                            'title' => $msgGateway['title'],
                            'bundled' => 0,
                            'code' => 'manual',
                        ];
                    }
                }
            }
			
		   return $shippingMethods;	
			
	}

    public function createInvoiceNoAdminApi($order_id) {
		if (isset($order_id)) {
			$this->load->model('sale/order');

			$invoice_no = $this->registry->get('model_sale_order')->createInvoiceNo($order_id);
        }

        if ($invoice_no) {
            $json['invoice_no'] = $invoice_no;
        }
     
      return $invoice_no;
    }

    public function getinvoice($order_id){
        if (isset($order_id)) {

            $this->load->model('sale/order');

            $orderData = $this->registry->get('model_sale_order')->getOrder($order_id);
    
            $order_string = base64_encode( $orderData['order_id'] .'___'. $orderData['customer_id'] .'___'. $orderData['date_added'] );
    
            $invoice_url = 'index.php?route=module/invoice/index&order_id='.$order_string;
    
        }
     
      return $invoice_url;

    }


    public function getProviderOrder($order_id)
    {
        $this->load->model('sale/order');
        return $this->registry->get('model_sale_order')->getProviderOrder($order_id);
    }
    public function createProviderOrder($data=[]){
        $this->load->model('sale/order');
        return $this->registry->get('model_sale_order')->createProviderOrder($data);
    }
    public function updateProviderOrder($id,$data=[])
    {
        $this->load->model('sale/order');
        return $this->registry->get('model_sale_order')->updateProviderOrder($id,$data);
    }


}
