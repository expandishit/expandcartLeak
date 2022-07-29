<?php

namespace ExpandCart\Foundation\Inventory\Clients;

use Closure;
use stdClass;
use ExpandCart\Foundation\Inventory\Exception\ClientException;
use ExpandCart\Foundation\Inventory\Services\Odoo\ApiClient\Client as ApiClient;

class Odoo extends Client
{

    /**
     * Default Models version
     */
    const DEFAULT_VERSION = 12;

    /**
     * Mapping models by versions
     */
    const MODELS_MAP = [
        11 => [
            'product' => 'product.product',
            'product_template' => 'product.template',
            'product_qantity' => 'stock.change.product.qty',
            'customer'=> 'res.partner',
            'order'=> 'sale.order',
            'order_products'=>'sale.order.line',
        ],
     
        12 => [
            'product' => 'product.product',
            'product_template' => 'product.template',
            'product_qantity' => 'stock.change.product.qty',
            'customer'=> 'res.partner',
            'order'=> 'sale.order',
            'order_products'=>'sale.order.line',
        ],
        13 => [
            'product' => 'product.product',
            'product_template' => 'product.template',
            'product_qantity' => 'stock.change.product.qty',
            'customer'=> 'res.partner',
            'order'=> 'sale.order',
            'order_products'=>'sale.order.line',
        ],
        14 => [
            'product' => 'product.product',
            'product_template' => 'product.template',
            'product_qantity' => 'stock.change.product.qty',
            'customer'=> 'res.partner',
            'order'=> 'sale.order',
            'order_products'=>'sale.order.line',
        ],
        15 => [
            'product' => 'product.product',
            'product_template' => 'product.template',
            'product_qantity' => 'stock.change.product.qty',
            'customer'=> 'res.partner',
            'order'=> 'sale.order',
            'order_products'=>'sale.order.line',
        ],
    ];

    private function resolveModelName($key): string
    {
        $version = $this->config['version'] ?: self::DEFAULT_VERSION;
        return array_key_exists($version, self::MODELS_MAP)
            ? self::MODELS_MAP[$version][$key]
            : self::MODELS_MAP[self::DEFAULT_VERSION][$key];
    }

    private function initClient()
    {
        return ApiClient::createFromConfig([
            'url' => $this->config['url'],
            'database' => $this->config['database'],
            'username' => $this->config['username'],
            'password' => $this->config['password'],
        ], $logger = null);
    }

    private function initClientWithCallable(Closure $callback)
    {
        try {
            $client = $this->initClient();
            $result = $callback($client);
            return $this->decodeResponse($result, !!$result);
        } catch (\Throwable $th) {
            return $this->decodeResponse($th->getMessage(), false);
        }
    }

    //************************************** Item *********************************************

    /**
     * @param array $params array of properties for the new item
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function createItem($params)
    { 
        $response = $this->initClientWithCallable(function ($client) use ($params) {
            // create item
            $itemId = $client->create($this->resolveModelName('product'), $item = $this->mapItemToOdoo($params));
            // set item quantity
            if($this->config['version']==14 || $this->config['version']==15)
            {
                $client->create('stock.quant', array(array(
                    'product_id'=>(int)$itemId,
                    'location_id'=>9,
                    'inventory_quantity'=>(float)$params['quantity'],
                    'on_hand'=>true,
                    'quantity'=>(float)$params['quantity'],
                    )
                ));
               
           }
           else
           {
            $this->setItemQuantity($client, $itemId, $params['quantity']);
           }
            // set item status
            $this->toggleItemStatus((int)$itemId, (int)$params['status'] === 1);
            return $itemId;
        });
      
        return $response;
    
    }


    private function mapItemToOdoo(array $data): array
    { 

        if($data['product_description'][1]){
            $name=$data['product_description'][1]['name'];
            $desc=$data['product_description'][1]['description'];
        }
        else{
            $name=$data['name'];
            $desc=$data['description'];
        }
        $version = $this->config['version'];
        if($version==14 || $version==15)
        $image_field='image_1920';
        else
        $image_field='image';
      
        if (!empty($data['image']))
            $data['image'] = base64_encode(file_get_contents(BASE_STORE_DIR . 'image/' . $data['image']));

        if (empty($data['cost_price']) || $data['cost_price']==0 )
        $data['cost_price']=$data['price'];

        if (empty( $data['barcode'] ))
        $data['barcode']=$this->generateRandomString();

        if (empty( $data['sku'] ))
        $data['sku']=$this->generateRandomString();          
        return [
            'name' => $name,
            'display_name' => $name,
            'standard_price' => (float)$data['cost_price'],
            'list_price' => (float)$data['price'],
            'type' => 'product',
            'description_sale' => $desc,
            'default_code' => $data['sku'],
            'barcode' =>  $data['barcode'],
            //'available_in_pos' => true,
            'active' => (int)$data['status'] === 1,
            $image_field=>$data['image'],
        ];
    }
   private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
    /**
     * @param string $item_id item to update
     * @param array $params array of new properties
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function updateItem($item_id, $data)
    {  
       

        $response = $this->initClientWithCallable(function (ApiClient $client) use ($item_id,$data) {
       
            
            $client->update($this->resolveModelName('product'), (int)$item_id,$this->mapItemToOdoo($data));
        
            // set item quantity
            if($this->config['version']==14 || $this->config['version']==15)
            {
                $res=$client->create('stock.quant', array(array(
                    'product_id'=>(int)$itemId,
                    'location_id'=>9,
                    'inventory_quantity'=>(float)$data['quantity'],
                    'on_hand'=>true,
                    'quantity'=>(float)$data['quantity'],
                    )
                ));
               
           }
           else
           {
            $this->setItemQuantity($client, $itemId, $data['quantity']);
           }

            // set item status
            $this->toggleItemStatus((int)$item_id, (int)$data['status'] === 1);
            return true;
        });
     
      
        return $response;
    }
     /**
     * Deletes an item
     * @param string $item_id item to delete
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function deleteItem(string $item_id , string $org_id)
    {
        $response = $this->initClientWithCallable(function (ApiClient $client) use ($item_id) {
            // @see https://www.odoo.com/fr_FR/forum/aide-1/unable-to-delete-product-from-inventory-160676
            // You cannot delete the product since there are lots of records 
            // which have reference of it like: Inventory, Manufacturing, Inventory, Locations, SO, PO, etc.
            // Better to Disable / Archive the product.
            $client->delete($this->resolveModelName('product'), (int)$item_id);
            // check if item exist!
            $response = $this->retrieveItem($item_id);
            if (!$response->status === true || !$response->result) return;

            // Set item stock to zer0
            $this->setItemQuantity($client, (int)$item_id, 0);

            // Archive item
            $this->inactiveItem((int)$item_id);
        });

        return $response;
    }

    /**
     * set item quantity
     *
     * @param ApiClient $client
     * @param integer $item_id
     * @param integer $quantity
     * @return void
     * @see https://stackoverflow.com/questions/35968674/update-a-product-field-quantity-on-hand-with-xmlrpc/37568715
     */
    private function setItemQuantity($client, int $item_id, int $quantity)
    {
      
        $change_id = $client->create($this->resolveModelName('product_qantity'), [
            'product_id' => (int)$item_id,
            'new_quantity' => (int)$quantity
        ]);

        return $client->call($this->resolveModelName('product_qantity'), 'change_product_qty', [[(int)$change_id]]);
    }

    /**
     * @param string $item_id to retrieve, leave empty to list all items
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function retrieveItem(string $item_id = null)
    {
        $response = $this->initClientWithCallable(function (ApiClient $client) use ($item_id) {
            $response = $client->read($this->resolveModelName('product'), (int) $item_id);
            return empty($response) ? null : $response[0];
        });

        return $response;
    }

    /**
     * List all items
     * @param array $filters an extra option used in zoho web application allows you to filter items by specific fields, leave empty to list all items
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function listItems(array $filters = [])
    {
        return $this->decodeResponse($this->initClient()->search($this->resolveModelName('product'), array(array(array('id', '!=', 0))), $filters['paging']));
    }
    public function readItems(array $ids=[],array $fields = [])
    { 
        return $this->decodeResponse($this->initClient()->read($this->resolveModelName('product'),$ids, array('fields'=> $fields)));
    }

    /**
     * an extra function allows you to search items by text portion
     * @param string $search_text
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function searchItem($search_text) 
    {
        return $this->decodeResponse($this->initClient()->COUNT($this->resolveModelName($search_text['module']), array(array(
            array('__last_update', '>=', $filters['date'])))));
    }
  
    
    /**
     * Change status of the item to <strong>active</strong>
     * @param string $item_id
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function activeItem($item_id)
    {
        return $this->toggleItemStatus($item_id, true);
    }

    /**
     * Change status of the item to <strong>i]]
     * 
     * 
     * 
     * nactive</strong>
     * @param string $item_id
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function inactiveItem($item_id)
    {
        return $this->toggleItemStatus($item_id, !true);
    }

    private function toggleItemStatus(int $item_id, bool $status)
    {
        $response = $this->initClientWithCallable(function ($client) use ($item_id, $status) {
            // check if item exist
            $response = $this->retrieveItem($item_id);
            if (!$response->status === true || !$response->result) return;

            $currentStatus = (bool) $response->result['active'];
            if ($status === $currentStatus) return true;

            $client->call($this->resolveModelName('product'), 'toggle_active', [[$item_id]]);

            return true;
        });

        return $response;
    }

    //************************************** Purchase Order *********************************************

    /**
     * Create a purchase order
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function createPurchaseOrder(array $params, $ignore = false)
    {
    }

    /**
     * Update details of an existing purchase order
     * @param string $purchaseorder_id to update
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function updatePurchaseOrder($purchaseorder_id, array $params, $ignore = false)
    {
    }

    /**
     * Retrieve a purchase order
     * @param string $purchaseorder_id to retrieve, leave empty to list all purchase orders
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function retrievePurchaseOrder($purchaseorder_id = null)
    {
    }

    /**
     * List all purchase order
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function listPurchaseOrders()
    {
    }

    /**
     * Delete an purchase order
     * @param string $purchaseorder_id to be deleted
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function deletePurchaseOrder($purchaseorder_id)
    {
    }

    /**
     * Mark as issued
     * @param string $purchaseorder_id
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function issuePurchaseOrder($purchaseorder_id)
    {
    }

    /**
     * Mark as cancelled
     * @param string $purchaseorder_id
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function cancelPurchaseOrder($purchaseorder_id)
    {
    }

    //************************************** Sales Order *********************************************

    /**
     * Create a sales order
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function createSalesOrder(array $params = [], $ignore = false)
    {
        $response = $this->initClientWithCallable(function ($client) use ($params) {
            // create item
            $itemId = $client->create($this->resolveModelName('order'), $params);
           
          
            return $itemId;
        });
 
        return $response;
    }
    
    public function createSalesOrderProducts(array $params = [], $ignore = false)
    {
        $response = $this->initClientWithCallable(function ($client) use ($params) {
            // create item
            $itemId = $client->create($this->resolveModelName('order_products'), $params);
           
          
            return $itemId;
        });
 
        return $response;
    }

    /**
     * Update details of an existing sales order
     * @param string $salesorder_id to update
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function updateSalesOrder($salesorder_id, array $params, $ignore = false)
    {
    }

    /**
     * Retrieve a sales order
     * @param string $salesorder_id to retrieve, leave empty to list all purchase orders
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function retrieveSalesOrder($salesorder_id = null)
    {
    }

    /**
     * List all sales order
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    
    public function listSalesOrders(array $filters = [])
    {
        return $this->decodeResponse($this->initClient()->search($this->resolveModelName('order'), array(array(array('id', '!=', 0))), $filters['paging']));
    }
    public function readSalesOrders(array $ids=[],array $fields = [])
    { 
        return $this->decodeResponse($this->initClient()->read($this->resolveModelName('order'),$ids, array('fields'=> $fields)));
    }
    public function readSalesOrdersProducts(array $ids=[],array $fields = [])
    { 
        return $this->decodeResponse($this->initClient()->read($this->resolveModelName('order_products'),$ids, array('fields'=> $fields)));
    }


    /**
     * Delete an sales order
     * @param string $salesorder_id to be deleted
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function deleteSalesOrder($salesorder_id)
    {
    }

    /**
     * Mark as void
     * @param string $salesorder_id
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function voidSalesOrder($salesorder_id)
    {
    }

    /**
     * Mark as confirmed
     * @param string $salesorder_id
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function confirmSalesOrder($salesorder_id)
    {
    }

    //************************************** Invoices *********************************************

    /**
     * create invoice
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return bool|mixed|string
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function createInvoice(array $params = [], $ignore = false)
    {
    }

    /**
     * update Invoice
     * @param $invoice_id
     * @param array $params
     * @param bool $ignore ignore auto number generation, if true you must specify the new id, default is false
     * @return bool|mixed|string
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function updateInvoice($invoice_id, array $params, $ignore = false)
    {
    }

    /**
     * retrieve a single invoice
     * @param null $invoice_id
     * @return bool|mixed|string
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function retrieveInvoice($invoice_id = null)
    {
    }

    /**
     * @param string $invoice_id contact id to delete
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function deleteInvoice($invoice_id)
    {
    }

    /**
     * Mark as void
     * @param string $invoice_id
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function voidInvoice($invoice_id)
    {
    }

    /**
     * list all Invoice
     * @return bool|mixed|string
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function listInvoices()
    {
    }

    //************************************** Customers/Contacts *********************************************

    /**
     * @param array $params
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function createContact(array $params = [])
    {
        $response = $this->initClientWithCallable(function ($client) use ($params) {
            // create item
            $itemId = $client->create($this->resolveModelName('customer'), $params);
           
          
            return $itemId;
        });
 
        return $response;
    }

    /**
     * @param string $contact_id contact id to update
     * @param array $params
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function updateContact($contact_id, array $params = [])
    {
        
        $response = $this->initClientWithCallable(function (ApiClient $client) use ($contact_id,$params) {
         $client->update($this->resolveModelName('customer'), (int)$contact_id,$params);
            // set customer status
            $this->toggleContactStatus((int)$contact_id, (int)$params['status'] === 1);
            return true;
        });
     
        return $response;
    }
     /**
     * @param string $contact_id contact id to delete
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function deleteContact($contact_id)
    {
        $response = $this->initClientWithCallable(function (ApiClient $client) use ($contact_id) {
          
            $client->delete($this->resolveModelName('customer'), (int)$contact_id);
            // check if customer exist!
            $response = $this->retrieveContact((int)$contact_id);
            if (!$response->status === true || !$response->result) return;
            // Archive customer
            $this->inactiveContact((int)$item_id);
        });

        return $response;

    }

    /**
     * @param string $contact_id contact id to active
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function activeContact($contact_id)
    {
        return $this->toggleContactStatus($contact_id, true);
    }

    /**
     * @param string $contact_id contact id to inactive
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function inactiveContact($contact_id)
    {
        return $this->toggleContactStatus($contact_id, !true);
    }
    private function toggleContactStatus(int $contact_id, bool $status)
    {
        $response = $this->initClientWithCallable(function ($client) use ($contact_id, $status) {
            // check if item exist
            $response = $this->retrieveContact((int)$contact_id);
            if (!$response->status === true || !$response->result) return;

            $currentStatus = (bool) $response->result['active'];
            if ($status === $currentStatus) return true;

            $client->call($this->resolveModelName('customer'), 'toggle_active', [[$contact_id]]);

            return true;
        });

        return $response;
    }

    /**
     * @param string $contact_id the phone number 
     * @param array $filters filters array, use constants STATUS_.. to filter by contact type
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */

    public function retrieveContact($contact_id = null, array $filters = [])
    {
        $response = $this->initClientWithCallable(function (ApiClient $client) use ($contact_id) {
            $response = $client->read($this->resolveModelName('customer'),$contact_id, array('fields'=>array('name','id','active','email')));
            return empty($response) ? null : $response[0];
        });

        return $response;
       
    }

    /**
     * @param array $filters filters array, use constants STATUS_.. to filter by contact type
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
   
    public function listContacts(array $filters = [])
    {
        return $this->decodeResponse($this->initClient()->search($this->resolveModelName('customer'), array(array(array('id', '!=', 0))), $filters['paging']));
    }
    public function readContacts(array $ids=[],array $fields = [])
    { 
        return $this->decodeResponse($this->initClient()->read($this->resolveModelName('customer'),$ids, array('fields'=> $fields)));
    }

   

    //************************************** Currency *********************************************
    /**
     * List all Currencies
     * @param array $filters an extra option used in zoho web application allows you to filter Currencies by specific fields, leave empty to list all Currencies
     * @return \stdClass
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     */
    public function listCurrencies(array $filters = [])
    {
    }

    //************************************** Others *********************************************

    /**
     * configuration validately
     * @throws \ExpandCart\Foundation\Inventory\Exception\ClientException
     * @return boolean
     */
    public function validateConfiguration()
    {
        if (!isset($this->config['url'])) {
            throw new ClientException('You have to set config \'url\' to use microSoft-dynamics client');
        }

        if (!isset($this->config['database'])) {
            throw new ClientException('You have to set config \'database\' to use microSoft-dynamics client');
        }

        if (!isset($this->config['username'])) {
            throw new ClientException('You have to set config \'username\' to use microSoft-dynamics client');
        }

        if (!isset($this->config['password'])) {
            throw new ClientException('You have to set config \'password\' to use microSoft-dynamics client');
        }

        return true;
    }

    /**
     * Retrieves the array of authentication configs
     * @return array
     */
    protected function getAuthParams()
    {
        return [];
    }

    protected function resolveAuthorization(&$queryParams, &$formParams, &$headers)
    {
        $headers = array_merge($headers, $this->getAuthParams());
    }

    protected function decodeResponse($response, bool $status = null)
    {
        $data = new stdClass;
        $data->status = (bool)$status;
        $data->result = $response;

        return $data;
    }
    public function validateAuth()
    {
       if( $this->initClient()->authenticate()==false)
       return false;
       else return true;    
       
    }
}
