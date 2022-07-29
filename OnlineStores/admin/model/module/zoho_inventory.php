<?php

use ExpandCart\Foundation\Inventory\Inventory;
use ExpandCart\Foundation\Providers\Extension;
use ExpandCart\Foundation\Inventory\Clients\Zoho;


class ModelModuleZohoInventory extends Model
{

    private $inventory;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->setInventory(new Inventory(new Zoho, $this->refreshToken($this->getSettings())));
    }

    /**
     * Get the value of inventory
     */
    public function getInventory(): Inventory
    {
        return $this->inventory;
    }

    /**
     * Set the value of inventory
     *
     * @return  self
     */
    public function setInventory(Inventory $inventory)
    {
        $this->inventory = $inventory;

        return $this;
    }

    /**
     * update the module settings.
     *
     * @param array $inputs
     *
     * @return bool
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->insertUpdateSetting(
            'zoho_inventory',
            $inputs
        );
        return true;
    }

    public function getSettings()
    {
        return array_merge(
            $this->getDefaultSettings(),
            $this->config->get('zoho_inventory') ?? [],
            [
                'redirect_url' => '' . $this->url->link('module/zoho_inventory/authorized', '', 'SSL'),
            ]
        );
    }

    /**
     * Check if app installed
     *
     * @return boolean
     */
    public function isInstalled()
    {
        return Extension::isInstalled('zoho_inventory');
    }

    /**
     * Check is App Active
     *
     * @return boolean
     */
    public function isActive()
    {
        $settings = $this->getSettings();

        return $this->isInstalled()
            && (int) $settings['status'] === 1
            && isset($settings['token'])
            && isset($settings['token']['access_token']);
    }

    /**
     *   Install the required values for the application.
     *
     *   @return boolean whether successful or not.
     */
    public function install($store_id = 0)
    {
        try {

            $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "zoho_customers` (
                `customer_id` INT UNSIGNED NOT NULL,
                `zoho_customer_id` VARCHAR(255) NOT NULL
            )");

            $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "zoho_products` (
                `product_id` INT UNSIGNED NOT NULL,
                `zoho_product_id` VARCHAR(255) NOT NULL
            )");

            $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "zoho_sales_orders` (
                `order_id` INT UNSIGNED NOT NULL,
                `zoho_order_id` VARCHAR(255) NOT NULL
            )");

            $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "zoho_invoices` (
                `order_id` INT UNSIGNED NOT NULL,
                `zoho_invoice_id` VARCHAR(255) NOT NULL
            )");

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     *   Remove the values from the database.
     *
     *   @return boolean whether successful or not.
     */
    public function uninstall($store_id = 0, $group = 'zoho_inventory')
    {
        try {
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "zoho_customers`");
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "zoho_products`");
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "zoho_sales_orders`");
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "zoho_invoices`");

            return true;
        } catch (Exception $th) {
            return false;
        }
    }

    private function getDefaultSettings()
    {
        return [
            'status' => 0,
            'authtoken' => '',
            'organization_id' => '',
            'can_syncable_new_products' => true,
            'client_id' => '',
            'client_secret' => '',
            'token' => [],
        ];
    }

    //************************************** products *********************************************

    /**
     * @param $product_id
     * @param $data
     * @return false|stdClass
     */
    public function createProduct($product_id, $data)
    {

        if (!$this->isActive()) return false;
        
        //get name for all language
        $this->load->model('catalog/product');
        $product_description_data=$this->model_catalog_product->getProductDescriptions($product_id);
        foreach ($product_description_data as $item) {
            $zoho_product=$this->getInventory()->searchItem(trim($item['name']));
            if(isset($zoho_product->items) && count($zoho_product->items)>0)
            {
               $this->linkProduct($product_id, $zoho_product->items[0]->item_id);
               return  $zoho_product;
            }
        }
         
         
        // if ($this->deleteItemIfExist($product_id)) {
        
        $result = $this->getInventory()->createItem($this->mapSystemProductData($data));

        if ($result->status === true) {
            $this->linkProduct($product_id, $result->item->item_id);
            $this->changeProductStatus($product_id, $data['status']);
            $this->updateImage($product_id, $result->item->item_id);
        }
        
        return $result;
        // }

        // $result = stdClass::class;
        // $result->status = false;

        // return $result;
    }

    /**
     * check if item exist in zoho or not
     *
     * @param $itemId
     * @return bool
     */
    private function deleteItemIfExist($itemId): bool
    {
        try {
            $this->getInventory()->deleteItem($itemId);
        } catch (Exception $exception) {
            return false;
        }

        return true;
    }

    public function updateProduct($product_id, $data)
    {
        if (!$this->isActive()) return false;


        $link = $this->selectLinkProduct($product_id);

        if ($link) {
            $tempData = $this->mapSystemProductData($data);
            unset($tempData['initial_stock']);
            $result = $this->getInventory()->updateItem($link['zoho_product_id'], $tempData);
            if ($result->status === true) {
                $this->changeProductStatus($product_id, $data['status']);
                // $this->updateImage($product_id,$link['zoho_product_id']);
            }

            return $result;
        }

        // save new product to inventory
        return $this->createProduct($product_id, $data);
    }

    public function deleteProduct($product_id, $org_id)
    {
        if (!$this->isActive()) return false;

        $link = $this->selectLinkProduct($product_id);

        if ($link) {
            $this->unLinkProduct($product_id, $link['zoho_product_id']);
            $this->getInventory()->deleteItem($link['zoho_product_id'], $org_id);
        }
    }

    public function changeProductStatus($product_id, $status)
    {
        if (!$this->isActive()) return false;

        $link = $this->selectLinkProduct($product_id);

        if ($link) {
            if ((int)$status == 1) {
                $this->getInventory()->activeItem($link['zoho_product_id']);
            } else {
                $this->getInventory()->inactiveItem($link['zoho_product_id']);
            }
        }
    }

    /**
     * @param int $batch_size
     * @return false|void
     */
    public function syncProducts(int $batch_size = 200)
    {
        if (!$this->isActive()) return false;

        // start sync to system
        $this->retrieveZohoProducts(1, $batch_size, function ($zoho_products) {
            static $model_product = null;
            static $system_language_id  = null;
            static $synced_products_ids = null;

            if ($model_product === null) {
                $model_product = $this->load->model('catalog/product', ['return' => true]);
            }

            if ($system_language_id === null) {
                $system_language_id = $this->selectSystemLanguageId();
            }

            if ($synced_products_ids === null) {
                $synced_products_ids = $this->retrieveSyncedProducts();
            }

            // batch size 200 products divided into two parts [new|exist]
            $products_batch = array_reduce($zoho_products, function ($accumulator, $zoho_product) use ($system_language_id, $synced_products_ids) {

                $formatted_product = $this->mapZohoProductData($system_language_id, $this->objectToArray($zoho_product));

                $product_index = array_search($zoho_product->item_id, array_column($synced_products_ids, 'zoho_product_id'));

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
                if ($product['thumb'])
                    $this->saveImageToStore($product['thumb'], $product_id);
                $this->linkProduct($product_id, $product['zoho_product_id'], true); // save the product link
            }

            //update products with zoho values
            foreach ($products_batch['exist'] as $product) {

                //update current sku, price, quantity, cost_price value with zoho values
                $updated_data = [
                    ['column' => 'price',       'value' => $product['price']],
                    ['column' => 'quantity',    'value' => $product['quantity']],
                    ['column' => 'sku',         'value' => $product['sku']],
                    ['column' => 'cost_price',  'value' => $product['cost_price']],
                    ['column' => 'status',      'value' => $product['status']],
                ];
                $model_product->updateProductMultipleValues($product['product_id'], $updated_data);

                //update name and description with zoho values
                $model_product->updateProductDescription($product['product_id'], $system_language_id, $product);
            }

            unset($products_batch);
        });
        // end sync to system

        //add un synced products to zoho
        $this->load->model('catalog/product');
        foreach ($this->retrieveUnSyncedProducts() as $product) {
            $data = $this->model_catalog_product->getProduct($product['product_id']);
            $this->createProduct($product['product_id'], $data);
        }
    }

    private function retrieveZohoProducts(int $page, int $per_page, Closure $callback)
    {
        $result = $this->getInventory()->listItems(['page' => $page, 'per_page' => $per_page]);
        if ($result->status === true) {

            $callback($result->items);

            if ($result->page_context->has_more_page === true) {
                $this->retrieveZohoProducts(++$page, $per_page, $callback);
            }
        }
    }

    private function retrieveSyncedProducts()
    {
        $query = $this->db->query("SELECT product_id, zoho_product_id FROM " . DB_PREFIX . "zoho_products WHERE 1");
        return $query->num_rows ? $query->rows : [];
    }

    // retrieve product id for all un sync products to zoho
    private function retrieveUnSyncedProducts()
    {
        $query = $this->db->query("SELECT t1.product_id FROM " . DB_PREFIX . "product t1 LEFT JOIN " . DB_PREFIX . "zoho_products t2 ON t2.product_id = t1.product_id WHERE t2.product_id IS NULL ");
        return $query->num_rows ? $query->rows : [];
    }

    private function selectSystemLanguageId()
    {
        $system_lang_code = $this->config->get('config_admin_language');
        $query = $this->db->query("SELECT language_id FROM " . DB_PREFIX . "language WHERE code = '" . $system_lang_code . "'");
        return $query->num_rows ? $query->row['language_id'] : null;
    }

    /**
     * @param array $data
     * @return array
     */
    private function mapSystemProductData(array $data): array
    {
        $product            = [];
        $description        = null;
        $system_language    = $this->selectSystemLanguageId();
        //check if product is recently added or just sync exists products
        if (isset($data['product_description']))
            foreach ($data['product_description'] as $language_id => $value) {
                if (isset($value['name']) && isset($value['description'])) {
                    $description = $value;
                    if ($system_language == $language_id) break;
                }
            }
        else
            $description = [
                'name'          => isset($data['name']) ? $data['name'] : '',
                'description'   => isset($data['description']) ? $data['description'] : ''
            ];

        $product['unit'] = $this->detectProductUnit($data['weight_class_id'] ?: $this->config->get('config_weight_class_id') ?: 0);
        $product['description'] = strip_tags($description ? $description['description'] : 'unknown');
        $product['description']=str_replace(array( '\'', '"', 
        ',' , ';', '<', '>','[',']','?','=','+','~','@','#','{','}','-','_','(',')','*','%','$','&',
        '^','!','`','¬','£' )," ",$product['description']);
            
        $product['name'] = $description ? $description['name'] : 'unknown';
        $product['sku'] = $data['sku'] ?? "";
        $product['upc'] = $data['upc'] ?? "";
        $product['ean'] = $data['ean'] ?? "";
        $product['isbn'] = $data['isbn'] ?? "";
        $product['jan'] = $data['jan'] ?? "";
        $product['mpn'] = $data['mpn'] ?? "";
        $product['rate'] = $data['price'] > 0  ? (float)$data['price'] : 1;
        $product['purchase_rate'] = (float)$data['cost_price'];
        $product['item_type'] = 'inventory';
        $product['product_type'] = 'goods';
        $product['package_details'] = [
            'length' => (float)$data['length'],
            'width' => (float)$data['width'],
            'height' => (float)$data['height'],
            'weight' => (float) $data['weight']
        ];

        $product['initial_stock'] = (int)$data['quantity'];
        $product['is_returnable'] = true; // true|false


        // $product['initial_stock_rate'] = (float)$data['price'] - (float)$data['cost_price'];

        return $product;
    }

    private function mapZohoProductData(int $language_id, array $data)
    {
        return [
            'zoho_product_id' => $data['item_id'],
            'model' => $data['brand'],
            'sku' => $data['sku'],
            'upc' => $data['upc'],
            'ean' => $data['ean'],
            'jan' => $data['jan'] ?? "",
            'isbn' => $data['isbn'],
            'mpn' => $data['mpn'] ?? "",
            'location' => $data['location'] ?? "",
            'quantity' => $data['available_stock'],
            'minimum' => $data['minimum'] ?? 0,
            'preparation_days' => 0,
            'maximum' => $data['maximum'] ?? 1,
            'subtract' => 0,
            'notes' => '',
            'barcode' => '',
            'stock_status_id' => '',
            'date_available' => '',
            'manufacturer_id' => '',
            'shipping' => 0,
            'transaction_type' => 0,
            'external_video_url' => '',
            'price' => (float)$data['rate'],
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
            'cost_price' => $data['purchase_rate'],
            'points' => 0,
            'weight' => 0,
            'weight_class_id' => 0,
            'length' => 0,
            'width' => 0,
            'height' => 0,
            'length_class_id' => 0,
            'status' => (isset($data['status']) && $data['status'] == 'active') ? 1 : 0,
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
            'date_added' => $data['created_time'] ?? "",
            'product_description' => [
                // system language id
                $language_id => [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'meta_keyword' => '',
                    'meta_description' => '',
                    'tag' => '',
                ]
            ],
            'thumb' => ($data['image_type'] != '' && $data['image_document_id'] != '') ? ['name' => $data['image_document_id'], 'item_id' => $data['item_id'], 'image_type' => $data['image_type']] : null,
            'product_store' => [$this->config->get('config_store_id') ?: 0],
        ];
    }

    private function detectProductUnit($weight_class_id)
    {
        $class_descriptions = $this->db->query("SELECT DISTINCT unit FROM " . DB_PREFIX . "weight_class_description WHERE weight_class_id = '" . (int)$weight_class_id . "'");
        if ($class_descriptions->num_rows) {
            return $class_descriptions->row['unit'];
        }

        return '';
    }

    private function linkProduct($product_id, $zoho_product_id, $without_check = false)
    {
        $link = $without_check ? false : $this->selectLinkProduct($product_id);

        if (!$link) {
            $this->db->query("INSERT INTO  " . DB_PREFIX . "zoho_products SET product_id = '" . (int)$product_id . "', zoho_product_id = '" . $zoho_product_id . "'");
        }
    }

    private function unLinkProduct($product_id, $zoho_product_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "zoho_products WHERE product_id = '" . (int)$product_id . "' AND zoho_product_id = '" . $zoho_product_id . "'");
    }

    private function selectLinkProduct($product_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zoho_products WHERE product_id = '" . (int)$product_id . "'");
        return $query->num_rows ? $query->row : null;
    }

    private function updateImage($product_id, $zoho_product_id)
    {
        // update zoho image with main image if exist
        try {
            $product = $this->model_catalog_product->getProduct($product_id);
            if (isset($product['image']) && !empty($product['image']) && file_exists($path = BASE_STORE_DIR . 'image/' . $product['image'])) {
                $this->getInventory()->updateItemImage($zoho_product_id, $path);
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function saveImageToStore($image, $product_id)
    {
        $name = 'data/products/' . $image['name'] . '.' . $image['image_type'];
        $this->getImageFromZoho($image['item_id'],  'image/' . $name);
        $this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $name . "' WHERE product_id = " . $product_id);
    }

    //************************************** Customers *********************************************

    public function createCustomer($customer_id, array $data = null)
    {
        if (!$data) return false;
        if (!$this->isActive()) return false;

        $result = $this->getInventory()->createContact($this->mapCustomerData($data));
        if ($result->status === true) {
            $this->linkCustomer($customer_id, $result->contact->contact_id);
            $this->changeCustomerStatus($customer_id, $data['status']);
        }

        return $result;
    }

    public function updateCustomer($customer_id, $data)
    {
        if (!$this->isActive()) return false;

        $link = $this->selectLinkCustomer($customer_id);
        if ($link) {
            $zoho_customer_result = $this->getInventory()->retrieveContact($link['zoho_customer_id']);

            if ($zoho_customer_result->status === true) {
                if (!empty($zoho_customer_result->contact->contact_persons)) {
                    $data['contact_person_id'] = $zoho_customer_result->contact->contact_persons[0]->contact_person_id;
                }

                $result = $this->getInventory()->updateContact($link['zoho_customer_id'], $this->mapCustomerData($data));

                if ($result->status === true) {
                    $this->changeCustomerStatus($customer_id, $data['status']);
                }

                return $result;
            }
        }
        // save new Customer to inventory
        return $this->createCustomer($customer_id, $data);
    }

    public function deleteCustomer($customer_id)
    {
        if (!$this->isActive()) return false;

        $link = $this->selectLinkCustomer($customer_id);
        if ($link) {
            $result = $this->getInventory()->deleteContact($link['zoho_customer_id']);
            if ($result->status === true) {
                $this->unLinkCustomer($customer_id, $link['zoho_customer_id']);
            }
        }
    }

    public function changeCustomerStatus($customer_id, $status)
    {
        if (!$this->isActive()) return false;
        $link = $this->selectLinkCustomer($customer_id);
        if ($link) {
            if ((int)$status === 1) {
                $this->getInventory()->activeContact($link['zoho_customer_id']);
            } else {
                $this->getInventory()->inactiveContact($link['zoho_customer_id']);
            }
        }
    }

    /**
     * map customer data
     *
     * @param array $data the original request data
     * @return array converted data
     * @see https://www.zoho.com/inventory/api/v1/#Contacts
     */
    private function mapCustomerData($data)
    {

        $customer_data = [
            'contact_name' => $data['firstname'] . ' ' . $data['lastname'], // required
            'contact_type' => 'customer', // optional allowed types [customer, vendor]
            'language_code' => $this->config->get('config_admin_language'), // optional
            'currency_id' => $this->detectCustomerCurrency(),
            'contact_persons' => [
                [
                    'salutation' => '',
                    'first_name' => $data['firstname'],
                    'last_name' => $data['lastname'],
                    'email' => $data['email'],
                    'phone' => $data['telephone'],
                    'mobile' => $data['telephone'],
                    'is_primary_contact' => true,
                ]
            ]
        ];

        if (isset($data['contact_person_id'])) $customer_data['contact_persons'][0]['contact_person_id'] = $data['contact_person_id'];
        if (isset($data['billing_address'])) $customer_data['billing_address'] = $data['billing_address'];
        if (isset($data['shipping_address'])) $customer_data['shipping_address'] = $data['shipping_address'];

        return $customer_data;
    }

    private function detectCustomerCurrency()
    {
        $query = $this->db->query("SELECT code FROM " . DB_PREFIX . "currency WHERE status = '1'");
        if ($query->num_rows) {
            $system_currency_code = strtoupper($query->row['code']);
            $zoho_currencies_result = $this->getInventory()->listCurrencies();
            if ($zoho_currencies_result->status === true) {
                $zoho_currencies = array_map(function ($currency) {
                    return $this->objectToArray($currency);
                }, $zoho_currencies_result->currencies);

                if ($key = array_search($system_currency_code, array_column($zoho_currencies, 'currency_code'))) {
                    return $zoho_currencies[$key]['currency_id'];
                }
            }
        }

        return null;
    }

    private function linkCustomer($customer_id, $zoho_customer_id)
    {
        $link = $this->selectLinkCustomer($customer_id);

        if (!$link) {
            $this->db->query("INSERT INTO  " . DB_PREFIX . "zoho_customers SET customer_id = '" . (int)$customer_id . "', zoho_customer_id = '" . $zoho_customer_id . "'");
        }
    }

    private function unLinkCustomer($customer_id, $zoho_customer_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "zoho_customers WHERE customer_id = '" . (int)$customer_id . "' AND zoho_customer_id = '" . $zoho_customer_id . "'");
    }

    private function selectLinkCustomer($customer_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zoho_customers WHERE customer_id = '" . (int)$customer_id . "'");
        return $query->num_rows ? $query->row : null;
    }

    //************************************** Orders *********************************************

    private const ORDER_CONFIRMED_STATUSES = [5, 25, 26];

    public function createOrder($order_id, array $order, array $products)
    {
        if (!$this->isActive()) return false;

        if (!isset($order_id)) return false;

        if (!isset($order['customer_id'])) return false;

        $customer_link = $this->selectLinkCustomer($order['customer_id']);

        $customer_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$order['customer_id'] . "'");

        if (!$customer_query->num_rows) {
            return false;
        }

        $customer_data = $customer_query->row;

        $customer_data['shipping_address'] = [
            'attention' => $order['shipping_firstname'],
            'address' => $order['shipping_address_1'],
            'city' => $order['shipping_city'],
            'state' => $order['shipping_zone_code'],
            'zip' => $order['shipping_postcode'],
            'country' => $order['shipping_country'],
            'phone' => $order['shipping_telephone'],
        ];

        $customer_data['billing_address'] = [
            'attention' => $order['payment_firstname'],
            'address' => $order['payment_address_1'],
            'city' => $order['payment_city'],
            'state' => $order['payment_zone_code'],
            'zip' => $order['payment_postcode'],
            'country' => $order['payment_country'],
            'phone' => $order['payment_telephone'],
        ];

        if (!$customer_link) {
            // create new customer
            $zoho_customer_result = $this->createCustomer(
                $order['customer_id'],
                $customer_data
            );

            if ($zoho_customer_result->status === true) {
                return $this->createOrder($order_id, $order, $products);
            } else {
                // cannot create new zoho customer!
                return false;
            }
        } else {
            $zoho_customer_result = $this->updateCustomer($order['customer_id'], $customer_data);
        }

        $zoho_order = $this->mapOrderData($customer_link['zoho_customer_id'], $order, $products);

        // save order
        if (!empty($zoho_order['line_items'])) {

            $order_result = $this->getInventory()->createSalesOrder($zoho_order, true);

            if ($order_result->status === true) {
                $this->linkOrder($order_id, $order_result->salesorder->salesorder_id);
                // mark as confirmed 
                if (in_array($order['order_status_id'], self::ORDER_CONFIRMED_STATUSES)) {
                    $this->getInventory()->confirmSalesOrder($order_result->salesorder->salesorder_id);
                }
                // create order invoice
                $this->createInvoice($order_id);
            }

            return $order_result;
        }

        return false;
    }

    private function mapOrderData($zoho_customer_id, $order, $products)
    {
        return [
            'customer_id' => $zoho_customer_id,
            'salesorder_number' => $order['invoice_no'],
            'reference_number' => $order['invoice_prefix'],
            'delivery_method' => $order['payment_method'],
            'shipping_charge' => $this->calculateOrderShippingCharge($order['total'], $products),
            'line_items' => array_reduce($products, function ($accumulator, $product) use ($order) {
                $valid_product = $this->prepareProductOrder($order['language_id'], $product);
                if ($valid_product) array_push($accumulator, $valid_product);
                return $accumulator;
            }, [])
        ];
    }

    private function calculateOrderShippingCharge($total, $products)
    {
        $has_tax = $this->config->get('config_tax');

        $shipping_charge = array_reduce($products, function ($accumulator, $product) use ($has_tax) {
            return $accumulator -= ((float)$product['total'] + ($has_tax ? ($product['tax'] * $product['quantity']) : 0));
        }, (float)$total);

        return $shipping_charge;
    }

    private function prepareProductOrder($language_id, array $product)
    {
        if (!isset($product['product_id'])) return null;

        // check if product saved in zoho
        $product_link = $this->selectLinkProduct($product['product_id']);

        // select system product
        $product_query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product['product_id'] . "' AND pd.language_id = '" . (int)$language_id . "'");

        if (!$product_query->num_rows) {
            return null;
        }

        $product_data = $product_query->row;

        // save new product in zoho
        if (!$product_link) {
            $product_data['product_description'] = [
                $language_id => [
                    'name' => $product_data['name'],
                    'description' => $product_data['description']
                ]
            ];

            $zoho_product_result = $this->createProduct($product['product_id'], $product_data);

            if ($zoho_product_result->status === true) {
                return $this->prepareProductOrder($language_id, $product);
            } else {
                // cannot create new zoho product!
                return null;
            }
        }

        $has_tax = $this->config->get('config_tax');

        // format product to zoho item basic data
        return [
            'item_id' => $product_link['zoho_product_id'],
            'name' => $product_data['name'],
            'description' => strip_tags($product_data['description']),
            'rate' => (float)$product['price'] + ($has_tax ? $product['tax'] : 0),
            'quantity' => (int)$product['quantity'],
            'item_total' => (float)$product['total'] + ($has_tax ? ($product['tax'] * $product['quantity']) : 0),
        ];
    }

    private function linkOrder($order_id, $zoho_order_id)
    {
        $link = $this->selectLinkOrder($order_id);

        if (!$link) {
            $this->db->query("INSERT INTO  " . DB_PREFIX . "zoho_sales_orders SET order_id = '" . (int)$order_id . "', zoho_order_id = '" . $zoho_order_id . "'");
        }
    }

    private function unLinkOrder($order_id, $zoho_order_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "zoho_sales_orders WHERE order_id = '" . (int)$order_id . "' AND zoho_order_id = '" . $zoho_order_id . "'");
    }

    private function selectLinkOrder($order_id)
    {
        $query = $this->db->query("SELECT order_id, zoho_order_id FROM " . DB_PREFIX . "zoho_sales_orders WHERE order_id = '" . (int)$order_id . "'");
        return $query->num_rows ? $query->row : null;
    }

    public function deleteOrder($order_id, $action = 'delete')
    {
        if (!$this->isActive()) return false;

        $order_link = $this->selectLinkOrder($order_id);
        if ($order_link) {
            // delete|archive order invoice
            $this->deleteInvoice($order_id, $action);
            switch ($action) {
                case 'archive':
                    $result = true;
                    // $result = $this->getInventory()->voidSalesOrder($order_link['zoho_order_id']);
                    break;
                default:
                    $result = $this->getInventory()->deleteSalesOrder($order_link['zoho_order_id']);
                    if ($result->status === true) {
                        $this->unLinkOrder($order_id, $order_link['zoho_order_id']);
                    }
                    break;
            }
            return $result;
        }

        return false;
    }

    //************************************** Invoices *********************************************

    public function createInvoice($system_order_id)
    {
        if (!$this->isActive()) return false;

        $order_link = $this->selectLinkOrder($system_order_id);

        if (!$order_link) return false;

        $invoice_link = $this->selectLinkInvoice($system_order_id);

        if ($invoice_link) {
            // the invoice already created for this order
            return true;
        }

        // retrieve the order;
        $order_result = $this->getInventory()->retrieveSalesOrder($order_link['zoho_order_id']);

        if ($order_result->status === true) {
            $invoice_result = $this->getInventory()->createInvoice($invoice_data = $this->mapInvoiceData($order_result->salesorder));
            if ($invoice_result->status === true) {
                $this->linkInvoice($system_order_id, $invoice_result->invoice->invoice_id);
            }

            return $invoice_result;
        }

        // can not find zoho order
        return false;
    }

    private function mapInvoiceData(stdClass $zoho_order)
    {
        $zoho_order = $this->objectToArray($zoho_order);
        $contact_persons = !empty($zoho_order['contact_persons'])
            ? $zoho_order['contact_persons']
            : $zoho_order['contact_person_details'];

        if (!empty($contact_persons) && isset($contact_persons[0]['contact_person_id'])) {
            $contact_persons = [$contact_persons[0]['contact_person_id']];
        } else {
            $contact_persons = [];
        }

        return [
            'customer_id' => $zoho_order['customer_id'],
            'contact_persons' => $contact_persons,
            'salesperson_name' => $zoho_order['salesperson_name'],
            'line_items' => array_map(function ($item) {
                return [
                    'item_order' => $item['item_order'],
                    'item_id' => $item['item_id'],
                    'rate' => (float)$item['rate'],
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'quantity' => (int)$item['quantity'],
                    'discount' => $item['discount'] ?: 0,
                    'tax_id' => $item['tax_id'],
                    'salesorder_item_id' => $item['line_item_id'],
                    'unit' => $item['unit'],
                    'bcy_rate' => (float)$item['bcy_rate'],
                    'discount_amount' => $item['discount_amount'] ?: 0,
                    'tax_name' => $item['tax_name'],
                    'tax_type' => $item['tax_type'],
                    'tax_percentage' => $item['tax_percentage'],
                    'item_total' => $item['item_total'],
                ];
            }, $zoho_order['line_items']),
            'billing_address_id' => $zoho_order['billing_address_id'],
            'shipping_address_id' => $zoho_order['shipping_address_id'],
            'shipping_charge' => $zoho_order['shipping_charge'],
        ];
    }

    private function selectLinkInvoice($order_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zoho_invoices WHERE order_id = '" . (int)$order_id . "'");
        return $query->num_rows ? $query->row : null;
    }

    private function linkInvoice($order_id, $zoho_invoice_id)
    {
        $link = $this->selectLinkInvoice($order_id);

        if (!$link) {
            $this->db->query("INSERT INTO  " . DB_PREFIX . "zoho_invoices SET order_id = '" . (int)$order_id . "', zoho_invoice_id = '" . $zoho_invoice_id . "'");
        }
    }

    private function unLinkInvoice($order_id, $zoho_invoice_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "zoho_invoices WHERE order_id = '" . (int)$order_id . "' AND zoho_invoice_id = '" . $zoho_invoice_id . "'");
    }

    public function deleteInvoice($order_id, string $action = 'delete')
    {
        if (!$this->isActive()) return false;

        $link = $this->selectLinkInvoice($order_id);
        if ($link) {
            switch ($action) {
                case 'archive':
                    $result = true;
                    // $result = $this->getInventory()->voidInvoice($link['zoho_invoice_id']);
                    break;
                default:
                    $result = $this->getInventory()->deleteInvoice($link['zoho_invoice_id']);
                    if ($result->status === true) {
                        $this->unLinkInvoice($order_id, $link['zoho_invoice_id']);
                    }
                    break;
            }

            return $result;
        }

        // no invoices created for this order
        return false;
    }

    //************************************** Others *********************************************

    private function objectToArray(stdClass $obj)
    {
        return json_decode(json_encode($obj), true);
    }

    private function getImageFromZoho($item_id, $save_to): void
    {
        $url = $this->getInventory()->getImageUrl($item_id);

        set_time_limit(0);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 400);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $raw = curl_exec($ch);
        curl_close($ch);

        if (\Filesystem::isExists($save_to)) {
            \Filesystem::deleteFile($save_to);
        }
        \Filesystem::setPath($save_to)->put($raw);
    }

    public function getAuthorizationUrl($settings)
    {
        return sprintf(
            "https://accounts.zoho.com/oauth/v2/auth?scope=ZohoInventory.FullAccess.all&client_id=%s&state=expand&response_type=code&redirect_uri=%s&access_type=offline&prompt=consent",
            $settings['client_id'],
            $settings['redirect_url']
        );
    }

    public function generateToken($data)
    {
        $postFields = [
            'code' => $data['code'],
            'client_id' => $data['client_id'],
            'client_secret' => $data['client_secret'],
            'redirect_uri' => $this->url->link('module/zoho_inventory/authorized', '', 'SSL'),
            'grant_type' => 'authorization_code',
            'scope' => 'ZohoInventory.FullAccess.all',
            'state' => $data['state'],
        ];

        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => "https://accounts.zoho.com/oauth/v2/token",
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 1000,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
        ));

        $results = curl_exec($ch);
        curl_close($ch);
        $results = json_decode($results, true);

        if ($results['expires_in'])
            $results['expires_in'] = (time() + $results['expires_in']);

        return $results;
    }

    private function refreshToken($settings)
    {
        if (
            isset($settings['token']['refresh_token'])
            && time() > $settings['token']['expires_in']
        ) {
            $postFields = [
                'refresh_token' => $settings['token']['refresh_token'],
                'client_id' => $settings['client_id'],
                'client_secret' => $settings['client_secret'],
                'redirect_uri' => $settings['redirect_url'],
                'grant_type' => 'refresh_token',
            ];

            $ch = curl_init();

            curl_setopt_array($ch, array(
                CURLOPT_URL => "https://accounts.zoho.com/oauth/v2/token",
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 1000,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postFields,
            ));

            $results = curl_exec($ch);
            curl_close($ch);
            $results = json_decode($results, true);

            if ($results['expires_in']) $results['expires_in'] = (time() + $results['expires_in']);

            if (isset($results['access_token'])) {
                $settings['token']['access_token'] = $results['access_token'];
                $settings['token']['expires_in'] = $results['expires_in'];
            }

            $this->updateSettings(['zoho_inventory' => $settings]);
        }

        return $settings;
    }
}
