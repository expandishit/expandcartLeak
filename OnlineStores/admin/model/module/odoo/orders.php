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
     * @param $order_id
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
        $version = $this->config->get('odoo')['version'];
        if($version==14 || $version==15)
        $date_field='date_order';
        else
        $date_field='requested_date';

        $order_data = [
            'partner_id' => (int)$odoo_order_id,
            'partner_invoice_id' =>(int) $odoo_order_id,
            'partner_shipping_id' => (int)$odoo_order_id,
            'amount_total' => (float)$order['total'],
            $date_field=>$order['date_added'] ?? "",
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
    public function syncOrders()
    {  
        if (!$this->module->isActive()) return false;
        $model_order = $this->load->model('sale/order', ['return' => true]);
         //Sync orders to odoo
         if($this->retrieveUnSyncedOrders())
         {
            foreach ($this->retrieveUnSyncedOrders() as $order) 
            {
                $data = $model_order->getOrder($order['order_id']);
                $productsdata = $model_order->getOrderProducts($order['order_id']);
                $data['products']= $productsdata;
                $this->createOrder((int)$order['order_id'], $data);
            } 
        }
            $limit = 200;
            $lastSyncDate= $this->module->selectlastSync('orders')['last_sync_date'];
             
            $filterData=['module'=>'order','date'=>$lastSyncDate];
            $resultCount = $this->module->getInventory()->searchItem($filterData);
            
            $ordersCount=$resultCount->result;
            
          ///Start sync from odoo to expand
        for($offset=0;$offset< $ordersCount; $offset+=$limit)
            {
               $this->syncOrdersFromOdoo($offset, $limit,$lastSyncDate);
                if( $ordersCount-$offset <= $limit )
                {
                    $limit=$ordersCount-$offset; 
                    $this->syncOrdersFromOdoo($offset, $limit,$lastSyncDate);
                    break;
                }
            }  // end sync from odoo to expand
            $syncedOrdersCount=count($this->retrieveSyncedOrders());

            ///Update last Sync Date
            if($syncedOrdersCount==$ordersCount)
            {
             $this->module->updatelastSync('orders',date('d/m/Y'));
            }       
      
    }

    private function syncOrdersFromOdoo(int $offset,int $limit,$lastSyncDate)
    {
        $model_order = $this->load->model('sale/order', ['return' => true]);
        
        $synced_orders_ids = $this->retrieveSyncedOrders();

        $odoo_orders=  $this->retrieveOdooOrders($offset, $limit,$lastSyncDate);
        // orders divided into two parts [new|exist]
          $orders_batch = array_reduce($odoo_orders, function ($accumulator, $odoo_order) use ($synced_orders_ids) {
           
            $formatted_order = $this->mapOdooOrderData($odoo_order);
      
            $order_index = array_search($odoo_order['id'], array_column($synced_orders_ids, 'odoo_order_id'));

            if ($order_index === false) {
                // push order to new
                $accumulator['new'][] = $formatted_order;
            } else {
                // push to exist
                $formatted_order['order_id'] = (int)$synced_orders_ids[$order_index]['order_id'];
                $accumulator['exist'][] = $formatted_order; // if true update
            }
            return $accumulator;
        }, ['new' => [], 'exist' => []]);

        
        // save new orders to db
        foreach ($orders_batch['new'] as $order) {
            $order_id = $model_order->addOrder($order); // default save method
            $this->linkOrder($order_id, $order['odoo_order_id']); // save the order link
        }
      
        unset($orders_batch);
    }
    
   
    private function retrieveOdooOrders(int $offset, int $limit,$lastSyncDate)
    {   
        $filterData=['paging'=>['offset' => $offset, 'limit' => $limit],'date'=>$lastSyncDate];      
        $ordersIDs = $this->module->getInventory()->listSalesOrders($filterData);
        $ordersIDsArr=json_decode(json_encode($ordersIDs->result), true);
        $odooOrdersFields= array('partner_id','partner_invoice_id','partner_shipping_id','order_line'
         ,'pricelist_id','amount_total','currency_id','create_date','write_date');
        $result = $this->module->getInventory()->readSalesOrders($ordersIDsArr,$odooOrdersFields);

        return $result->result;

    }
    private function retrieveOdooProductsOrders(array $id)
    {   
        $odooOrdersFields= array('order_id','product_id','name','price_unit'
        ,'product_uom_qty','create_date','write_date');
        $result = $this->module->getInventory()->readSalesOrdersProducts($id,$odooOrdersFields);
        return $result->result;
    }
    
    private function mapOdooOrderData(array $data)
    {  
        $this->load->model('module/odoo/products');
        $model_product = $this->load->model('catalog/product', ['return' => true]);
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "odoo_customers WHERE odoo_customer_id = '" . (int)$data['partner_id'][0] . "'");
        $customerID=$query->row['customer_id'];
         foreach($data['order_line'] as  $product) {

             ///add Odoo product if it not exist in expand
             $orderProductsData= $this->retrieveOdooProductsOrders(array((int)$product));
      
             $orderProductID=$orderProductsData[0]['product_id'][0];
             $product_link = $this->model_module_odoo_products->selectOdooProduct((int) $orderProductID);
            
             if(!$product_link)
             {
                $version = $this->config->get('odoo')['version'];
                if($version==14 || $version==15)
                $image_field='image_1920';
                else
                $image_field='image';
                
                $odooProductsFields= array('id','name','display_name','standard_price','default_code','barcode',
                'list_price','description_sale','currency_id','qty_available','active', $image_field,'create_date','__last_update');
                    
                $result = $this->module->getInventory()->readItems(array((int)$orderProductID),$odooProductsFields);
                if($result->result[0])
                {
                $productData = $this->model_module_odoo_products->mapOdooProductData($result->result[0]);
                $product_id = $model_product->addProduct($productData); // default save method
                $this->model_module_odoo_products->linkProduct($product_id, (int)$orderProductID, true); // save the product link
               }
             }
        }
        foreach ($data['order_line'] as  $product) 
        { 
            $orderProductsData= $this->retrieveOdooProductsOrders(array((int)$product));
            foreach ($orderProductsData as  $order_product_data) {      
            $formatted_product[]=$this->mapOdooOrderProductsData($order_product_data);
           
            }
          
        } 
        return [
            'odoo_order_id' => $data['id'],
            'customer_id' => $customerID,
            'firstname' => $data['partner_id'][1],
            'order_status_id'=>1,
            'total'=>$data['amount_total'],
            'order_total' => [
                0 => [
                    'title' => 'total',
                    'code' => 'total',
                    'text' => $data['amount_total'].$data['currency_id'][1],
                    'value'=>$data['amount_total'],
                    'sort_order'=>1,
                ]
            ],
            'currency_code' =>$data['currency_id'][1],
            'order_product'=> $formatted_product,
            'date_added' =>$data['create_date'],  
            'date_modified' =>$data['write_date']
        ];
    }

    private function mapOdooOrderProductsData(array $data)
    {    
        $this->load->model('module/odoo/products');
        $linkedProduct=$this->model_module_odoo_products->selectOdooProduct((int) $data['product_id'][0]);
        $product_id=$linkedProduct['product_id'];
        $linkedOrder=$this->selectOdooOrder($data['order_id'][0]);
        $order_id=$linkedOrder['order_id'];
        return [
            'order_id' =>  $order_id,
            'product_id' => $product_id,
            'name' => $data['name'],
            'price'=>$data['price_unit'],
            'quantity' =>$data['product_uom_qty'],];
            
    }
    private function retrieveSyncedOrders()
    {
        $query = $this->db->query("SELECT order_id, odoo_order_id FROM " . DB_PREFIX . "odoo_orders WHERE 1");
        return $query->num_rows ? $query->rows : [];
    }

    // retrieve order id for all un sync orders to odoo
    private function retrieveUnSyncedOrders()
    {
       
        $sql = "SELECT o.order_id FROM `" . DB_PREFIX . "order` o LEFT JOIN  `" . DB_PREFIX . "odoo_orders` odo ON odo.order_id = o.order_id WHERE odo.order_id IS NULL ";
        $query = $this->db->query($sql);

	    return $query->num_rows ? $query->rows : [];
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
    private function selectOdooOrder($odoo_order_id)
    {
        $query = $this->db->query("SELECT order_id, odoo_order_id FROM " . DB_PREFIX . "odoo_orders WHERE odoo_order_id = '" . (int)$odoo_order_id   . "'");
        return $query->num_rows ? $query->row : null;
    }
}
