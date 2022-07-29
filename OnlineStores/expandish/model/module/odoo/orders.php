<?php

use ExpandCart\Foundation\Inventory\Inventory;
use ExpandCart\Foundation\Providers\Extension;
use ExpandCart\Foundation\Inventory\Clients\Odoo;

class ModelModuleOdooOrders extends Model
{
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->module = $this->load->model("module/odoo/settings", ['return' => true]);
    }
    /**
     * @param $customer_id
     * @param $data
     * @return false|stdClass
     */

    public function createOrder($order_id, array $order)
    {  
        if (!$this->module->isActive()) return false;

         $this->load->model('module/odoo/customers');

        if (!isset($order_id)) return false;

        if (!isset($order['customer_id'])) return false;

        $customer_link = $this->model_module_odoo_customers->selectLinkCustomer((int)$order['customer_id']);
 
          if($customer_link)
          {
            $customer_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$order['customer_id'] . "'");
            if (!$customer_query->num_rows) {
                return false;
            }

            $customer_data = $customer_query->row;

        $customer_data['delivery'] = [
                'street' => $order['shipping_address_1'],
                'city' => $order['shipping_city'],
                'zip' => $order['shipping_postcode'],
            ];

            $customer_data['invoice'] = [

                'street' => $order['payment_address_1'],
                'city' => $order['payment_city'],
                'zip' => $order['payment_postcode'],

            ];
       }else
       {
            // create new customer
            $odoo_customer_result =  $this->model_module_odoo_customers->createCustomer($order['customer_id'], $customer_data);
            if ($odoo_customer_result->status === true)
                {
                    return $this->createOrder($order_id, $order);
                } 
            else {
                // cannot create new odoo customer!
                return false;
             }
        } 
      
        $odoo_order = $this->mapOrderData($customer_link['odoo_customer_id'], $order);  

        // save order
        if (!empty($odoo_order)) 
        {
            $order_result = $this->module->getInventory()->createSalesOrder($odoo_order, true);
       
            if ($order_result->status === true) 
            {
                $odoo_order_id=$order_result->result;

                $this->linkOrder($order_id,  $odoo_order_id);

                $products_data= array_reduce( $order['products'], function ($accumulator, $product) use ($odoo_order_id) {
                    $valid_product = $this->mapOrderProducts($odoo_order_id, $product);
                    if ($valid_product) array_push($accumulator, $valid_product);
                    return $accumulator;
                }, []);
                
               /// add products to odoo Order
              if($products_data)
              {
                foreach($products_data as $product)
                {
                  $order_products = $this->module->getInventory()->createSalesOrderProducts($product, true);  
                }
              }

           }

            return $order_result;
       }

        return false;
    }

    private function mapOrderData($odoo_order_id, $order)
    {
        $order_data = [
            'partner_id' => (int)$odoo_order_id,
            'partner_invoice_id' =>(int) $odoo_order_id,
            'partner_shipping_id' => (int)$odoo_order_id,
            'amount_total' => (float)$order['total'],
            'confirmation_date'=>$order['date_added'] ?? "",
            'requested_date'=>$order['date_added'] ?? "",
            'state' =>'sale',   
        ];

        return $order_data;
    }
  

    private function mapOrderProducts(int $odoo_order_id, array $product)
    {
           
        if (!isset($product['product_id'])) return null;
        $this->load->model('module/odoo/products');
        // check if product saved in odoo
        $language_id=1;
        $language_id2=2;
        $product_link = $this->model_module_odoo_products->selectLinkProduct($product['product_id']);
        // select system product
        $product_query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product['product_id'] . "' AND pd.language_id = '" . (int)$language_id . "'");

        if (!$product_query->num_rows)  return null;

        $product_data = $product_query->row;
        // save new product in odoo
        if (!$product_link) 
        {
            $product_data['product_description'] = [
                $language_id => [
                    'name' => $product_data['name'],
                    'description' => $product_data['description']
                ],
                $language_id2 => [
                    'name' => $product_data['name'],
                    'description' => $product_data['description']
                ]
            ];
              
            $odoo_product_result = $this->model_module_odoo_products->createProduct($product_data['product_id'], $product_data);
    
            if ($odoo_product_result->status === true) {
                return $this->mapOrderProducts($odoo_order_id, $product);
            } else {
                // cannot create new odoo product!
                return null;
            }
        }
           
        $has_tax = $this->config->get('config_tax');
          
        // format product to odoo item basic data
    
        $product_data =  [
            'order_id'=> (int)$odoo_order_id,
            'product_id'=> (int)$product_link['odoo_product_id'],
            'price_unit'=>(float)$product['price'],
            'price_subtotal'=>(float)$product['total'] + ($has_tax ? ($product['tax'] * $product['quantity']) : 0),
            'product_uom_qty'=>(float)$product['quantity'],
            'name'=>$product['name'], 
        ];
        return $product_data;
    }
    private function calculateOrderShippingCharge($total, $products)
    {
        $has_tax = $this->config->get('config_tax');

        $shipping_charge = array_reduce($products, function ($accumulator, $product) use ($has_tax) {
            return $accumulator -= ((float)$product['total'] + ($has_tax ? ($product['tax'] * $product['quantity']) : 0));
        }, (float)$total);

        return $shipping_charge;
    }

    private function linkOrder($order_id, $odoo_order_id)
    {
        $link = $this->selectLinkOrder($order_id);

        if (!$link) {
            $this->db->query("INSERT INTO  " . DB_PREFIX . "odoo_orders SET order_id = '" . (int)$order_id . "', odoo_order_id = '" . $odoo_order_id . "'");
        }
    }

    private function selectLinkOrder($order_id)
    {
        $query = $this->db->query("SELECT order_id, odoo_order_id FROM " . DB_PREFIX . "odoo_orders WHERE order_id = '" . (int)$order_id . "'");
        return $query->num_rows ? $query->row : null;
    }
}
