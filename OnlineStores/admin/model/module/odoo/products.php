<?php

use ExpandCart\Foundation\Inventory\Inventory;
use ExpandCart\Foundation\Providers\Extension;
use ExpandCart\Foundation\Inventory\Clients\Odoo;

class ModelModuleOdooProducts extends Model
{
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->module = $this->load->model("module/odoo/settings", ['return' => true]);
    }
    /**
     * @param $product_id
     * @param $data
     * @return false|stdClass
     */
    public function createProduct($product_id, $data)
    {
        if (!$this->module->isActive()) return false;
        $result = $this->module->getInventory()->createItem($data);

        if ($result->status === true) {
            $this->linkProduct($product_id, $result->result, true);
        }
    
        return $result;
    }

    public function updateProduct(int $product_id, array $data)
    {
       
        if (!$this->module->isActive()) return false;

        $link = $this->selectLinkProduct($product_id);
        if ($link) {
            $result = $this->module->getInventory()->updateItem((int)$link['odoo_product_id'], $data);

            return $result;
        }

        // save new product to inventory
        return $this->createProduct($product_id, $data);
    }
    public function deleteProduct($product_id, $org_id)
    {
        if (!$this->module->isActive()) return false;

        $link = $this->selectLinkProduct($product_id);

        if ($link) {
            $this->unLinkProduct($product_id, $link['odoo_product_id']);
            $this->module->getInventory()->deleteItem($link['odoo_product_id'], '');
        }
    }

    public function changeProductStatus(int $product_id, int $status)
    {
        if (!$this->module->isActive()) return false;

        $link = $this->selectLinkProduct($product_id);

        if ($link) {
            if ((int)$status == 1) {
                $this->module->getInventory()->activeItem($link['odoo_product_id']);
            } else {
                $this->module->getInventory()->inactiveItem($link['odoo_product_id']);
            }
        }
    }
    public function syncProducts()
    {   
        if (!$this->module->isActive()) return false;
        //Sync from expand to odoo
        if($this->retrieveUnSyncedProducts())
        {  
            $this->load->model('catalog/product');
            foreach ($this->retrieveUnSyncedProducts() as $product)
            {
                $data = $this->model_catalog_product->getProduct($product['product_id']);
                $this->createProduct($product['product_id'], $data);
                
            }
        }
            $limit = 200;
            $lastSyncDate= $this->module->selectlastSync('products')['last_sync_date'];
            $filterData=['module'=>'product','date'=>$lastSyncDate];
            $resultCount = $this->module->getInventory()->searchItem($filterData);
            $productsCount=$resultCount->result;
           
          ///Start sync from odoo to expand
           for($offset=0;$offset< $productsCount; $offset+=$limit)
            { 
               $this->syncProductsFromOdoo($offset, $limit,$lastSyncDate);
                if( $productsCount-$offset <= $limit )
                {
                    $limit=$productsCount-$offset; 
                    $this->syncProductsFromOdoo($offset, $limit,$lastSyncDate);
                    break;
                }
            }
            $syncedProductsCount=count($this->retrieveSyncedProducts());
            $syncedProductsCount=0;
            ///Update last Sync Date
            if($syncedProductsCount==$productsCount)
            {
            $this->module->updatelastSync('products',date('d/m/Y'));   
            }
        // end sync from odoo to expand
        
    }
    private function syncProductsFromOdoo(int $offset,int $limit,$lastSyncDate)
    {
        
        $model_product = $this->load->model('catalog/product', ['return' => true]);
        $system_language_id =1;
        $system_language_id2 =2;
        $synced_products_ids = $this->retrieveSyncedProducts();

        $odoo_products=  $this->retrieveOdooProducts($offset, $limit,$lastSyncDate);
    
        // products divided into two parts [new|exist]
        
        $products_batch = array_reduce($odoo_products, function ($accumulator, $odoo_product) use ($system_language_id, $synced_products_ids) {
    
            $formatted_product = $this->mapOdooProductData($odoo_product);
          
            $product_index = array_search($odoo_product['id'], array_column($synced_products_ids, 'odoo_product_id'));

            if ($product_index === false) {
                // push product to new
                $accumulator['new'][] = $formatted_product;
            } else {
                // push to exist
                $formatted_product['product_id'] = (int)$synced_products_ids[$product_index]['product_id'];
                $accumulator['exist'][] = $formatted_product; // if true update
            }
            return $accumulator;
        }, ['new' => [], 'exist' => []]);

        // save new products to db
        foreach ($products_batch['new'] as $product) {
           $product_id = $model_product->addProduct($product); // default save method
            $this->linkProduct($product_id, $product['odoo_product_id'], true); // save the product link
        }
        //update products with odoo values
        
        foreach ($products_batch['exist'] as $product) {

            //update current sku, price, quantity, cost_price value with odoo values
          
            $updated_data = [
                ['column' => 'price',       'value' => $product['price']],
                ['column' => 'quantity',    'value' => $product['quantity']],
                ['column' => 'sku',         'value' => $product['sku']],
                ['column' => 'cost_price',  'value' => $product['cost_price']],
                ['column' => 'status',      'value' => $product['status']],
                ['column' => 'image',      'value'  => $product['image']]
            ];
            $model_product->updateProductMultipleValues($product['product_id'], $updated_data);
           
            //update name and description with odoo values
            $model_product->updateProductDescription($product['product_id'], $system_language_id, $product);
            $model_product->updateProductDescription($product['product_id'], $system_language_id2, $product);
        }

        unset($products_batch);
    }
    private function retrieveOdooProducts(int $offset, int $limit,$lastSyncDate)
    {   
        $filterData=['paging'=>['offset' => $offset, 'limit' => $limit],'date'=>$lastSyncDate];      
        $productsIDs = $this->module->getInventory()->listItems($filterData);
        $productsIDsArr=json_decode(json_encode($productsIDs->result), true);
        $version = $this->config->get('odoo')['version'];
        if($version==14 || $version==15)
        $image_field='image_1920';
        else
        $image_field='image';
      
        $odooProductsFields= array('id','name','display_name','standard_price','default_code','barcode',
        'list_price','description_sale','currency_id','qty_available','active', $image_field,'create_date','__last_update');
        
        $result = $this->module->getInventory()->readItems($productsIDsArr,$odooProductsFields);
        return $result->result;
    }
    
    public function mapOdooProductData(array $data)
    {
        $version = $this->config->get('odoo')['version'];
        if($version==14 || $version==15)
        $image_field='image_1920';
        else
        $image_field='image';

       if (!empty($data[$image_field]))
       {
         $image_name=$this->saveImagebase64($data[$image_field]);
       }else{
        $image_name="";
       }
        $language_id=1;
        $language_id_2=2;
        return [
            'odoo_product_id' => $data['id'],
            'model' => "",
            'sku' => $data['default_code'],
            'upc' => "",
            'ean' => "",
            'jan' =>  "",
            'isbn' =>"",
            'mpn' =>  "",
            'location' => "",
            'quantity' => $data['qty_available'],
            'minimum' =>  0,
            'preparation_days' => 0,
            'maximum' =>  1,
            'subtract' => 0,
            'notes' => '',
            'barcode' => $data['barcode'],
            'stock_status_id' => '',
            'date_available' => '',
            'manufacturer_id' => '',
            'shipping' => 0,
            'transaction_type' => 0,
            'external_video_url' => '',
            'price' => (float)$data['list_price'],
            'printable' => 0,
            'sls_bstr' => [
                'video' => '',
                'status' => 0,
                'free_html' => []
            ],
            'main_status' => 0,
            'main_unit' => null,
            'main_meter_price' => 0,
            'main_package_size' => 0,
            'main_price_percentage' => 0,
            'skirtings_status' => 0,
            'skirtings_meter_price' => 0,
            'skirtings_package_size' => 0,
            'skirtings_price_percentage' => 0,
            'metalprofile_status' => 0,
            'metalprofile_meter_price' => 0,
            'metalprofile_package_size' => 0,
            'metalprofile_price_percentage' => 0,
            'cost_price' =>(float) $data['standard_price'],
            'points' => 0,
            'weight' => 0,
            'weight_class_id' => 0,
            'length' => 0,
            'width' => 0,
            'height' => 0,
            'length_class_id' => 0,
            'status' => (isset($data['active']) && $data['active'] == 'active') ? 1 : 0,
            'tax_class_id' => 0,
            'sort_order' => 0,
            'affiliate_link' => null,
            'pd_is_customize' => 0,
            'pd_custom_min_qty' => 1,
            'pd_custom_price' => 0,
            'pd_back_image' => null,
            'start_time' => null,
            'end_time' => null,
            'max_price' => 0,
            'min_offer_step' => 0,
            'start_price' => 0,
            'date_added' => $data['create_date'] ?? "",
            'product_description' => [
                // system language id
                $language_id => [
                    'name' => $data['name'],
                    'description' => $data['description_sale'],
                    'meta_keyword' => '',
                    'meta_description' => '',
                    'tag' => '',
                ],
                $language_id_2 => [
                    'name' => $data['name'],
                    'description' => $data['description_sale'],
                    'meta_keyword' => '',
                    'meta_description' => '',
                    'tag' => '',
                ],

            ],
            'image' => $image_name,
            'product_store' => [$this->config->get('config_store_id') ?: 0],
        ];
    }
    private function retrieveSyncedProducts()
    {
        $query = $this->db->query("SELECT product_id, odoo_product_id FROM " . DB_PREFIX . "odoo_products WHERE 1");
        return $query->num_rows ? $query->rows : [];
    }

    // retrieve product id for all un sync products to odoo
    private function retrieveUnSyncedProducts()
    {
        $query = $this->db->query("SELECT t1.product_id FROM " . DB_PREFIX . "product t1 LEFT JOIN " . DB_PREFIX . "odoo_products t2 ON t2.product_id = t1.product_id WHERE t2.product_id IS NULL ");
        return $query->num_rows ? $query->rows : [];
    }

    public function selectLinkProduct($product_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "odoo_products WHERE product_id = '" . (int)$product_id . "'");
        return $query->num_rows ? $query->row : null;
    }
    public function selectOdooProduct($odoo_product_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "odoo_products WHERE odoo_product_id = '" . (int)$odoo_product_id . "'");
        return $query->num_rows ? $query->row : null;
    }

    public function linkProduct($product_id, $odoo_product_id, $without_check = false)
    {
        $link = $without_check ? false : $this->selectLinkProduct($product_id);

        if (!$link) {
            $this->db->query("INSERT INTO  " . DB_PREFIX . "odoo_products SET product_id = '" . (int)$product_id . "', odoo_product_id = '" . $odoo_product_id . "'");
        }
    }

    private function unLinkProduct($product_id, $odoo_product_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "odoo_products WHERE product_id = '" . (int)$product_id . "' AND odoo_product_id = '" . $odoo_product_id . "'");
    }
    public  function saveImagebase64($image)
    {
        
        $filename =   time() . '_' . md5(rand());
        $image = base64_decode($image);
        $imageName = 'data/products/' .$filename.'.png';
        file_put_contents(DIR_IMAGE .  $imageName, $image);
     
        return $imageName;
    }

    
  
}
