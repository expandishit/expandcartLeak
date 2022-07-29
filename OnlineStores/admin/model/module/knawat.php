<?php

class ModelModuleKnawat extends Model
{
    private const  BASE_URL = "https://mp.knawat.io/api/";
    private const APP_CODE = 'knawat';

    public $app_version = 2;

    public function getSettings(): array
    {
        return $this->config->get('knawat') ?? [];
    }

    public function isInstalled(): bool
    {

        return Extension::isInstalled('knawat');
    }

    public function install()
    {
        //add knawat settings
        $this->load->model('setting/setting');
        $this->model_setting_setting->insertUpdateSetting('knawat', [
            'knawat' => [
                'install_completed' => false,
                'status' => false,
                'store' => [],
                'push_order_status_id' => '',
                'token' => '',
                'access_token' => '',
                'total_imported_products' => 0,
                'knawat_version' => $this->app_version,
            ],
        ]);

        // create imported knawat products table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "knawat_products` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `sku` varchar(255) NOT NULL,
            `product_id` int(11) NOT NULL,
            `updated_at` TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");

        // create imported knawat orders table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "knawat_orders` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `knawat_order_id` varchar(50) NOT NULL,
            `order_id` int(11) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");

        // create webhook logs table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "knawat_logs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `webhook_topic` varchar(50) NOT NULL,
            `status` enum('0','1') NOT NULL DEFAULT '0',
            `target_id` varchar(50)  NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");

        // create knawat category table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "knawat_categories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `knawat_category_id` int(11) NOT NULL,
            `category_id` int(11) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");

        //install product sku app
        if (!\Extension::isInstalled('productsoptions_sku')) {
            $this->installProductOptionsSku();
        }


    }

    public function updateSettings($inputs): bool
    {
        $this->load->model('setting/setting');
        $settings = $this->getSettings();

        /*update webhooks for this account if changed and save new store data */
        if (isset($inputs['token'])) {

            //get all older user webhooks and delete them
            $url = self::BASE_URL . 'webhooks';
            $all_webhooks = $this->sendKnawatRequest('GET', $url, [], $settings['token']);
            if ($all_webhooks)
                $this->deleteWebHook($all_webhooks->rows, $settings['token']);

            //get all new user webhooks if exist and delete them
            $all_webhooks = $this->sendKnawatRequest('GET', $url, [], $inputs['token']);
            if ($all_webhooks)
                $this->deleteWebHook($all_webhooks->rows, $inputs['token']);

            //register new webhooks
            foreach ($this->webhookData() as $hook)
                $this->sendKnawatRequest('POST', $url, $hook, $inputs['token']);

            //get store data
            $url = self::BASE_URL . 'stores/me';
            $inputs['store'] = $this->sendKnawatRequest('GET', $url, [], $inputs['token']);

        }

        foreach ($inputs as $key => $value)
            $settings[$key] = $value;
        $this->model_setting_setting->insertUpdateSetting('knawat', ['knawat' => $settings]);
        return true;
    }

    public function uninstall($store_id = 0, $group = 'knawat')
    {
        //remove current token webhooks
        $settings = $this->getSettings();

        if ($settings['token']) {

            $url = self::BASE_URL . 'webhooks';
            $all_webhooks = $this->sendKnawatRequest('GET', $url, [], $settings['token']);
            if ($all_webhooks)
                $this->deleteWebHook($all_webhooks->rows, $settings['token']);
        }

        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting($group);
//        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "knawat_products`");
//        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "knawat_orders`");
//        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "knawat_logs`");

    }

    private function deleteWebHook($webhooks = [], $token)
    {
        foreach ($webhooks as $hook) {
            $url = self::BASE_URL . 'webhooks/' . $hook->id;
            $this->sendKnawatRequest('DELETE', $url, [], $token);
        }

    }

    /***************** get Token ***************/
    public function getToken(): string
    {
        return $this->getSettings()['token'] ?? '';
    }

    /***************** generate Token ***************/
    public function generateToken($data = []): string
    {
        $url = self::BASE_URL . "token";
        $body = [
            'consumerKey' => $data['consumer_key'],
            'consumerSecret' => $data['consumer_secret'],
        ];
        $token_response = $this->sendKnawatRequest('POST', $url, $body);
        return $token_response ? $token_response->channel->token : '';
    }

    /*****************Send knawat request ***************/
    private function sendKnawatRequest($type, $url, $body = [], $token = '', $json_body = false)
    {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $type,
            CURLOPT_POSTFIELDS => ($json_body ? json_encode($body) : http_build_query($body)),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: ' . ($json_body ? 'application/json' : 'application/x-www-form-urlencoded'),
                'Authorization: Bearer ' . $token
            ),
        ));

        $response = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($http_status != 200) {
            $errors = json_decode($response, true);
            return [
                'status' => 'ERR',
                'errors' => $errors['errors'],
            ];
        }

        return json_decode($response);
    }

    /***************** get webhook data ***************/
    private function webhookData(): array
    {
        if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1')))
            $base = "https://" . DOMAINNAME;
        else
            $base = "http://" . DOMAINNAME;
        return [
            [
                'url' => $base . '/api/v1/knawat/push-product',
                'topic' => 'products.push',
            ],
            [
                'url' => $base . '/api/v1/knawat/delete-product',
                'topic' => 'products.delete',
            ],
            [
                'url' => $base . '/api/v1/knawat/store',
                'topic' => 'stores.update',
            ],
            [
                'url' => $base . '/api/v1/knawat/update-profit',
                'topic' => 'stores.update.profit',
            ],
            [
                'url' => $base . '/api/v1/knawat/update-order',
                'topic' => 'orders.update',
            ],
            [
                'url' => $base . '/api/v1/knawat/cancel-order',
                'topic' => 'orders.cancel',
            ],
        ];
    }

    /***************** get webhook data ***************/
    public function logWebhookCall($topic = null, $status = 0, $target = null): int
    {
        if (!$topic)
            return 0;
        $this->db->query("INSERT INTO `" . DB_PREFIX . "knawat_logs` (`webhook_topic`, `status`, `target_id`) VALUES ('" . $this->db->escape($topic) . "', '" . (int)$status . "', '" . $this->db->escape($target) . "')");
        return $this->db->getLastId();
    }

    /***************** update log webhook status ***************/
    public function updateLogStatus($id, $status = 1): void
    {
        $this->db->query("UPDATE " . DB_PREFIX . "knawat_logs SET `status` = '" . $this->db->escape($status) . "' WHERE id = " . (int)$id);
    }

    /**
     * Get a list of products skus to sync the clean up service
     *
     * @return bool
     */
    public function asyncKnawatProducts()
    {
        $token = $this->getToken();
        $page = 1;
        if (!$token) {
            return [
                'status' => 'ERR',
                'errors' => [
                    'Token is not valid'
                ]
            ];
        }

        $url = 'https://mp.knawat.io/api/catalog/products/count?hasExternalId=1&archived=0&hideOutOfStock=1';
        $count = $this->sendKnawatRequest('GET', $url, [], $token);

        if (!isset($count->total) && isset($count['status']) && $count['status'] == 'ERR') {
            return $count;
        }

        if (isset($count->total) == false) {
            return [
                'status' => 'ERR',
                'errors' => [
                    'Invalid request'
                ]
            ];
        }

        $server = EC_MONITORING['server'];
        $port = EC_MONITORING['port'];

        if (!($sock = socket_create(AF_INET, SOCK_DGRAM, 0))) {
            throw new \Exception(socket_strerror($errorcode));
        }

        $data = [
            'token' => $token,
            'total' => $count->total,
            'storeCode' => STORECODE

        ];

        $body = bin2hex(gzencode(json_encode($data)));

        $inputs = [
            'headers' => [
                'auth' => time(),
                'method' => 'POST',
                'action' => 'os/knawat/products',
                'content-encoding' => 'gzip',
                'udp-version' => '1.0'
            ],
            'body' => $body
        ];

        $inputs = json_encode($inputs);

        socket_sendto($sock, $inputs, strlen($inputs), 0 , $server , $port);

        $this->updateSettings([
            'sync' => [
                'status' => 0,
                'total_count' => $count->total,
                'total_synced' => 0
            ],

        ]);

        return [
            'status' => 'OK',
        ];
    }


    /////////////////////////////////////////////////////////////
    ////////////////////// Product Functions ///////////////////
    /////////////////////////////////////////////////////////////

    /***************** sync all knawat exists products ***************/
    public function syncKnawatProducts($updated = 1): int
    {
        $token = $this->getToken();
        $page = 1;
        if (!$token)
            return 0;

        $products_count = 0;

        $imported_products_sku_array = [];

        while (true) {
            $url = self::BASE_URL . "catalog/products?hasExternalId=" . $updated . "&archived=0&hideOutOfStock=1&limit=100&page=" . $page++;
            $products = $this->sendKnawatRequest('GET', $url, [], $token);
            $products_count = $products->total ?? 0;
            //update with current products
            foreach ($products->products as $product) {
                $imported_products_sku_array[] = $product->variations[0]->sku;
                //check if product not exist then add if not exist and update if exist
                $localKnawatProduct = $this->getProductBySku($product->sku);
                // getLast update product form knawat
                try{
                    $updatedAt = new DateTime($product->updated);
                }catch(Exception $e){
                    $updatedAt = new DateTime();
                }

                if (!$localKnawatProduct['product_id']){
                    $this->saveKnawatProduct($product);
                }else{

                    // check if product stock = out of stock (knawat return archive = true if the product out of stock)
                    if($product->archive){
                        $this->deleteKnawatProductBySku($product->sku);
                    }elseif($localKnawatProduct['updated_at'] !== $updatedAt->format('Y-m-d H:i:s')){
                        $this->updateKnawatProduct($localKnawatProduct['product_id'], $product);
                    }
                }
            }
            if (count($products->products) < 100)
                break;
        }

        // now we need to delete the products that removed from knawat catalog
        $products_ids = array_map('current',$this->getProductsIdsNotINArrayBySkuArray($imported_products_sku_array));
        $this->load->model('catalog/product');

        foreach ($products_ids as $id){
            $this->model_catalog_product->deleteProduct($id);
        }

        // update total imported products
        $settings = $this->getSettings();
        $settings['total_imported_products'] = $products_count;
        $this->updateSettings($settings);

        return $products_count;
    }

    /***************** check if order status is equal to chosen status and order not inserted to knawat before ***************/
    public function checkOrderStatusForInsertToKnawat($order_id): bool
    {
        if (!$this->getToken())
            return false;

        //get order data
        $order = $this->db->query("SELECT  payment_code, order_status_id  FROM `" . DB_PREFIX . "order`  WHERE order_id = '" . $order_id . "' LIMIT 1");;
        if (!isset($order->row['order_status_id']))
            return false;

        //check if order not paid or status now allow to add to knawat
        if ($this->getSettings()['push_order_status_id'] != $order->row['order_status_id'] ||$order->row['payment_code'] == 'cod' )
            return false;

        //check if order inserted before
        $exist_orders = $this->db->query("SELECT  knawat_order_id FROM `" . DB_PREFIX . "knawat_orders`  WHERE order_id = '" . $order_id . "' LIMIT 1");
        if ($exist_orders->num_rows)
            return false;

        return true;
    }

    /***************** check if order has knawat products ***************/
    public function checkIfKnawatOrder($order_id): bool
    {
        $order_products = $this->db->query("SELECT COUNT(*) AS order_products_count  FROM `" . DB_PREFIX . "order_product` WHERE order_id = '" . $order_id . "' GROUP BY product_id ");
        $knawat_products = $this->db->query("SELECT COUNT(*) AS knawat_products_count  FROM `" . DB_PREFIX . "knawat_products` WHERE product_id IN (SELECT product_id  FROM `" . DB_PREFIX . "order_product` WHERE order_id = '" . $order_id . "')");
        return $order_products->row['order_products_count'] == $knawat_products->row['knawat_products_count'] && $order_products->row['order_products_count'] != 0;
    }

    /***************** Insert order into knawat ***************/
    public function insertOrderIntoKnawat($order_id)
    {
        //get order and shipping country code
        $order = $this->db->query("SELECT  *  FROM `" . DB_PREFIX . "order`  LEFT JOIN country ON order.shipping_country_id = country.country_id WHERE order_id = '" . $order_id . "' LIMIT 1");;

        if ($order->row) {
            $body = $this->formatKnawatOrder($order->row);
            $url = self::BASE_URL . "orders";
            $order = $this->sendKnawatRequest('POST', $url, $body, $this->getToken(), true);
            if ($order->status && $order->status == 'success')
                $this->db->query("INSERT INTO " . DB_PREFIX . "knawat_orders SET order_id = '" . (int)$order_id . "', knawat_order_id = '" . $this->db->escape($order->data->id) . "'");

        }
        return null;
    }

    /***************** format knawat order ***************/
    public function formatKnawatOrder($order): array
    {
        $products = $this->knawatOrderProducts($order['order_id']);
        $formatted_order['id'] = $order['order_id'];

        $formatted_order['items'] = $this->formatKnawatOrderProducts($products);

        $firstName = isset($order['shipping_firstname']) ? $order['shipping_firstname'] : NULL;
        $lastName = isset($order['shipping_lastname']) ? $order['shipping_lastname'] : NULL;

        // check if empty last name explode first name
        if(empty($lastName) || $lastName == NULL){
            $firstNameExp = explode(" ",$firstName);
            if(count($firstNameExp) > 1){
                $firstName = $firstNameExp[0];
                $lastName = $firstNameExp[1];
            }else{
                $lastName = $firstName;
            }
        }

        $formatted_order['shipping'] = array(
            'first_name' => isset($order['shipping_firstname']) ? $order['shipping_firstname'] : '',
            'last_name' => (isset($order['shipping_lastname']) && !empty($order['shipping_lastname']))? $order['shipping_lastname'] : $order['shipping_firstname'],
            'company' => isset($order['shipping_company']) ? $order['shipping_company'] : '',
            'address_1' => isset($order['shipping_address_1']) ? $order['shipping_address_1'] : '',
            'address_2' => isset($order['shipping_address_2']) ? $order['shipping_address_2'] : '',
            'city' => (isset($order['shipping_city']) && !empty($order['shipping_city'])) ? $order['shipping_city'] : 'No City',
            'state' => (isset($order['shipping_zone']) && !empty($order['shipping_zone'])) ? $order['shipping_zone'] : 'No state',
            'postcode' => isset($order['shipping_postcode']) ? $order['shipping_postcode'] : '',
            'country' => isset($order['iso_code_2']) ? $order['iso_code_2'] : substr($order['shipping_country'], 0, 2),
            'email' => (isset($order['email']) && !empty($order['email']) ) ? $order['email'] : 'noEmail@expand.com',
            'phone' => isset($order['telephone']) ? $order['telephone'] : ''
        );

        $formatted_order['total'] = (float)$order['total'];
        $formatted_order['externalId'] = $order['order_id'];
        $formatted_order['notes'] = $order['comment'];
        $formatted_order['taxTotal'] = (float)$order['total'];
        $formatted_order['status'] = 'pending';
        $formatted_order['financialStatus'] = 'paid';
        $formatted_order['fulfillmentStatus'] = 'pending';

        return $formatted_order;
    }

    /***************** format knawat order  products***************/
    public function formatKnawatOrderProducts($products): array
    {
        $items = [];
        foreach ($products as $product) {
            $items[] = [
                'sku' => $product['knawat_sku'] ?? '',
                'name' => $product['name'],
                'description' => $product['product_description'],
                'quantity' => (int)$product['quantity'],
                'total' => (float)$product['total'],
                'tax' => $product['tax'],
            ];
        }
        return $items;
    }

    /***************** get knawat order products ***************/
    private function knawatOrderProducts($order_id)
    {

        //get products with knawat sku
        $query = $fields = [];
        $fields[] = 'order_product.*';
        $fields[] = 'product_description.name as product_name';
        $fields[] = 'product_description.description as product_description';
        $fields[] = 'knawat_products.sku as knawat_sku';

        $fields = implode(',', $fields);

        $query[] = 'SELECT ' . $fields . ' FROM ' . DB_PREFIX . 'order_product';
        $query[] = 'LEFT JOIN knawat_products ON order_product.product_id = knawat_products.product_id';
        $query[] = 'LEFT JOIN product_description ON order_product.product_id = product_description.product_id';
        $query[] = 'AND product_description.language_id="' . (int)$this->config->get('config_language_id') . '"';
        $query[] = 'WHERE order_id = "' . (int)$order_id . '"';

        $data = $this->db->query(implode(' ', $query));

        return $data->rows ?? [];
    }


    /***************** validate webhook ***************/
    public function validateWebhook($body, $knawat_hash = ''): bool
    {
        if (!$knawat_hash)
            return false;

        if (!isset($this->getSettings()['store']->external_data))
            return false;

        $generatedHash = base64_encode(hash_hmac('sha256', $body, $this->getSettings()['store']->consumer_secret, true));
        if ($generatedHash !== $knawat_hash)
            return false;
        return true;
    }

    /***************** get knawat Product Details ***************/
    public function getKnawatProductDetails($sku)
    {
        $token = $this->getToken();
        if (!$token)
            return null;

        $url = self::BASE_URL . "catalog/products/" . $sku;
        return $this->sendKnawatRequest('GET', $url, [], $token);
    }


    /***************** save knawat Product  ***************/
    public function saveKnawatProduct($product)
    {
        $data = $this->reformatProductDetails($product);

        $this->load->model('catalog/product');
        $product_id = $this->model_catalog_product->addProduct($data);

        $this->db->query("INSERT INTO " . DB_PREFIX . "knawat_products SET product_id = '" . (int)$product_id . "', sku = '" . $this->db->escape($data['knawat_sku']) . "'");

        //upload other images
        foreach ($product->images as $key => $value) {
            if ($key) {
                $image_name = 'data/products/' . time() . rand() . '.jpg';
                $this->saveProductImage($value, 'image/' . $image_name);
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($image_name) . "', sort_order = '" . (int)($key - 1) . "'");
            }
        }
        return $product_id;
    }

    /***************** update knawat Product  ***************/
    public function updateKnawatProduct($product_id, $product): void
    {
        //set product data format
        $data = $this->reformatProductDetails($product, $product_id, false);

        //delete old options and option variations
        $this->deleteProductOptions($product_id);

        //unset category

        //update product full data
        $this->load->model('module/advanced_product_attributes/attribute');
        $this->model_module_advanced_product_attributes_attribute->addProductAttributes($product_id, $data['product_advanced_attribute']);

        $this->load->model('catalog/product');
        $this->model_catalog_product->editProduct($product_id, $data , true);
        try{
            $updatedAt = new DateTime($product->updated);
        }catch(Exception $e){
            $updatedAt = new DateTime();
        }
        $this->setProductUpdateAt($product_id, $updatedAt->format('Y-m-d H:i:s'));

    }

    private function setProductUpdateAt($productId, $updatedAt){
        $this->db->query("UPDATE `knawat_products` SET `updated_at`='{$updatedAt}' WHERE `product_id`='{$productId}'");
    }

    /***************** delete knawat Product  ***************/
    public function deleteKnawatProductBySku($sku = "")
    {
        $product_id = $this->getProductIdBySku($sku);
        $this->load->model('catalog/product');
        $product_id = $this->model_catalog_product->deleteProduct($product_id);
        $this->db->query("DELETE FROM " . DB_PREFIX . "knawat_products WHERE product_id = '" . (int)$product_id . "'");
    }

    public function deleteKnawatProductByKnawatSku($sku = '')
    {
        $result = $this->db->query("SELECT product_id FROM `" . DB_PREFIX . "knawat_products` WHERE sku = '" . $this->db->escape($sku) . "' LIMIT 1");
        if (isset($result->row['product_id']) == false) {
            return false;
        }

        $product_id = $result->row['product_id'];
        $this->load->model('catalog/product');
        $product_id = $this->model_catalog_product->deleteProduct($product_id);
        $this->db->query("DELETE FROM " . DB_PREFIX . "knawat_products WHERE product_id = '" . (int)$product_id . "'");
    }


    /*** Get Product ID based on sku*/
    public function getProductIdBySku($sku = '')
    {
//        $result = $this->db->query("SELECT knawat_products.product_id FROM `" . DB_PREFIX . "knawat_products` INNER JOIN product ON knawat_products.product_id = product.product_id WHERE knawat_products.sku = '" . $this->db->escape($sku) . "' LIMIT 1");
        $result = $this->db->query("SELECT knawat_products.product_id FROM `" . DB_PREFIX . "knawat_products` INNER JOIN product ON knawat_products.product_id = product.product_id WHERE product.sku = '" . $this->db->escape($sku) . "' LIMIT 1");
        return !empty($result->row['product_id']) ? $result->row['product_id'] : null;
    }

    public function getProductBySku($sku = '')
    {
        $result = $this->db->query("SELECT knawat_products.product_id, knawat_products.updated_at  FROM `" . DB_PREFIX . "knawat_products` INNER JOIN product ON knawat_products.product_id = product.product_id WHERE product.sku = '" . $this->db->escape($sku) . "' LIMIT 1");
        return !empty($result->row['product_id']) ? $result->row : null;
    }

    public function getProductsIdsNotINArrayBySkuArray(array $sku)
    {
        $arrayText = "'" . implode("','",$sku). "'";
        $result = $this->db->query("SELECT product_id FROM `" . DB_PREFIX . "knawat_products` WHERE sku NOT IN($arrayText)  ");
        return $result->rows;
    }

    /***************** reformat product details to save ***************/
    public function reformatProductDetails($data, $product_id = null, $allow_category=true): array
    {

        //get product variation and options
        $product_options = $this->setProductOptions($data);
        //get product Atrributes
        $attributes = $this->setProductAttributes($data);
        //set all empty value for name and description
        $data->name = $this->setEmptyLanguagesValue($data->name);
        $data->description = $this->setEmptyLanguagesValue($data->description);
        if($product_id == null){
            $tax_class_id = $this->config->get('default_tax_class');
        }else{
            $tax_class_id = $data->tax_class_id;
        }

        $status = $data->archive ? 0 : 1;

        $product_data = [
            'price' => (float)$data->variations[0]->sale_price,
            'cost_price' => (float)$data->variations[0]->cost_price,
            'quantity' => $product_options['total_product_quantity'],
            'knawat_sku' => $data->variations[0]->sku,
            'sku' => $data->sku,
            'tax_class_id' => $tax_class_id,
            'barcode' => $data->supplier,
            'weight' => $data->variations[0]->weight,
            'status' => $status,
            'subtract' => 1,
            'shipping' => 1,
            'product_store' => [$this->config->get('config_store_id') ?: 0],
            'product_option' => $product_options['product_options'],
            'num_options' => $product_options['num_options'],
            'product_options_variations' => $product_options['product_options_variations'],
            'product_advanced_attribute' => $attributes,
            'product_description' => $this->reformatProductNameAndDescriptionForAllLanguages($data->name,$data->description),
            'upc' => '',
            'ean' => '',
            'jan' => '',
            'isbn' => '',
            'mpn' => '',
            'unlimited' => '',
            'model' => '',
            'minimum' => '',
            'maximum' => '',
            'date_available' => date('Y-m-d', time() - 86400),
            'location' => '',
            'is_simple' => 1,
            'points' => '',
            'weight_class_id' => $this->config->get('config_weight_class_id'),
            'length' => '',
            'width' => '',
            'height' => '',
            'length_class_id' => $this->config->get('config_length_class_id'),
            'stock_status_id' => $this->config->get('config_stock_status_id'),
            'manufacturer_id' => 0,
            'sort_order' => 1,
            'keyword' => ''
        ];

        //check if we need to update category with knawat default categories
        if($allow_category)
            $product_data ['product_category'] = $this->getProductCategories($data->categories);

        if (!$product_id) {
            //get main image path and save it
            $image_name = 'data/products/' . time() . rand() . '.jpg';
            $this->saveProductImage($data->images[0], 'image/' . $image_name);
            $product_data['image'] = $image_name;
        } else {
            $this->load->model('catalog/product');
            $product_data['image'] = $this->model_catalog_product->getProductCover($product_id);
            $product_data['subtract'] = 1;
            // get product images for send it to edit product function as we delete product images on edit product
            $product_images = $this->model_catalog_product->getProductImages($product_id);
            if(is_array($product_images)){
                foreach ($product_images as $productImage){
                    $product_data['images'][] = $productImage['image'];
                }
            }

        }
        return $product_data;

    }

    /************** reformat product name and description for all installed languages  ***************/
    private function reformatProductNameAndDescriptionForAllLanguages($name, $description): array
    {
        //get all installed languages sorted by code and set empty values
        $this->load->model('localisation/language');
        $languages =  $this->model_localisation_language->getLanguages(['sort'=>'code']);

        //new complete object
        $result = [];

        foreach($languages as $language){
            $language_code = $language['code'];
            $result[$language['language_id']]=[
                'name'              => $name->$language_code,
                'description'       => $description->$language_code,
                'meta_keyword'      => '',
                'meta_description'  => '',
                'tag'               => '',
            ];
        }

        return $result;
    }

    public function saveImageToStore($image, $product_id)
    {
        $name = 'data/products/' . time() . rand() . '.jpg';
        $this->saveProductImage($image['item_id'], 'image/' . $name);
        $this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $name . "' WHERE product_id = " . $product_id);
    }

    private function saveProductImage($image_url, $save_to): void
    {

        set_time_limit(0);
        $ch = curl_init($image_url);
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



    /////////////////////////////////////////////////////////////
    ////////////////////// Order Functions ///////////////////
    /////////////////////////////////////////////////////////////


    /***************** format knawat order id if exists***************/
    public function getKnawatOrderId($knawat_order_id)
    {
        $result = $this->db->query("SELECT order_id FROM `" . DB_PREFIX . "knawat_orders` WHERE knawat_order_id = '" . $this->db->escape($knawat_order_id) . "' LIMIT 1");
        return !empty($result->row['order_id']) ? $result->row['order_id'] : null;
    }

    /**
     * Prepare the Attributes to add it to the system
     * @param $product
     * @return array
     */
    public function setProductAttributes($product): array
    {
        $isInstalled =  \Extension::isInstalled('advanced_product_attributes');
        // install advanced attribute app if it's not installed
        if (!$isInstalled){
            $this->load->model('module/advanced_product_attributes/settings');
            $this->model_module_advanced_product_attributes_settings->install();
            $this->load->model('setting/extension');
            $this->model_setting_extension->install('module', 'advanced_product_attributes');
        }

        $this->load->model('localisation/language');
        // get all languages in the system
        $languages = $this->model_localisation_language->getLanguages();

        $attributes = [];

        $this->load->model('module/advanced_product_attributes/attribute');
        // loop on attributes from knawat product
        foreach ($product->attributes as $attribute){

            $attributeName = (array) $attribute->name;
            $attributeValues = (array) $attribute->options;

            $getAttribute = $this->model_module_advanced_product_attributes_attribute->getAttributeByName($attributeName);

            // add the values to the attribute for every language or add the attribute then add the values
            if ($getAttribute->num_rows > 0){
                $attributes[$getAttribute->row['advanced_attribute_id']] = [
                    'advanced_attribute_id' => $getAttribute->row['advanced_attribute_id'],
                    'type' => 'text'
                ];

                foreach ($languages as $key => $value){

                    $attributeValue = implode(", ", array_column($attributeValues, array_key_first($attributeName)));
                    $attributes[$getAttribute->row['advanced_attribute_id']]['product_attribute_description'][$value['language_id']] = ['value' => $attributeValue, 'name' => $attributeValue, 'text' => $attributeValue];

                    if ($attributeName[$key]) {
                        $attributeValue = implode(", ", array_column($attributeValues, $key));
                        $attributes[$getAttribute->row['advanced_attribute_id']]['product_attribute_description'][$value['language_id']] = ['value' => $attributeValue, 'name' => $attributeValue, 'text' => $attributeValue];
                    }
                }

            }else{
                // add new attribute
                $newAttribute = [
                    'attribute_group_id' => 1,
                    'type' => 'text',
                    'sort_order' => 0,
                    'glyphicon' => 'fa fa-check-circle'
                ];

                foreach ($languages as $key => $value){
                    $attributeValue = implode(", ", array_column($attributeValues, array_key_first($attributeName)));

                    $newAttribute['attribute_description'][$value['language_id']] = ['name' => $attributeName[array_key_first($attributeName)], 'text' => $attributeValue];

                    if ($attributeName[$key]) {
                        $attributeValue = implode(", ", array_column($attributeValues, $key));
                        $newAttribute['attribute_description'][$value['language_id']] = ['name' => $attributeName[$key], 'text' => $attributeValue];
                    }
                }
                $newAttributeId = $this->model_module_advanced_product_attributes_attribute->add($newAttribute);

                $attributes[$newAttributeId] = [
                    'advanced_attribute_id' => $newAttributeId,
                    'product_attribute_description' => $newAttribute['attribute_description'],
                    'type' => 'text'
                ];

            }
        }

        return $attributes;


    }

    /***************** format product options***************/
    public function setProductOptions($product): array
    {
        $options = [];
        $added_option_values = [];
        $product_options = [];
        $product_options_variations = [];
        $total_product_quantity = 0;
        foreach ($product->variations as $variation) {

            $product_variation = [
                'sku' => $variation->sku,
                'quantity' => $variation->quantity,
                'price' => $variation->market_price,
                'barcode'=> '',
                'options' => []
            ];

            foreach ($variation->attributes as $option) {
                //insert option if not exist
                $option_name = $option->name->en ?? ($option->name->ar ??$option->name->tr);
                $option_id = $this->getOptionIdByName($option_name);

                if (!$option_id)
                    $option_id = $this->createOption($this->setEmptyLanguagesValue($option->name));

                if (!isset($options[$option_id]))
                    $options[$option_id] = ['option_id' => $option_id, 'type' => 'select','sort_order'=>1, 'required' => 1, 'product_option_value' => []];


                //insert option value if not exist
                $option_value_name = $option->option->en ?? $option->option->ar ?? $option->option->tr;
                $option_value_id = $this->getOptionValueIdByName($option_value_name, $option_id);
                if (!$option_value_id)
                    $option_value_id = $this->createOptionValue($this->setEmptyLanguagesValue($option->option), $option_id);

                //format data into options array
                if (!in_array($option_value_id, $added_option_values)) {
                    $options[$option_id]['product_option_value'][] = [
                        'option_value_id' => $option_value_id,
                        'quantity' => $variation->quantity,
                        'price' => 0,
                        'weight' => $variation->weight,
                        'subtract' => 1,
                        'points'=> 0,
                        'sort_order'=> 1,
                        'points_prefix'=> '',
                    ];
                    $added_option_values[] = $option_value_id;
                }else{

                    //increase option value quantity
                    foreach($options[$option_id]['product_option_value'] as $key=>$value){
                        if($value['option_value_id'] == $option_value_id ){
                            $options[$option_id]['product_option_value'][$key]['quantity'] += $variation->quantity;
                            break;
                        }
                    }
                }

                //insert option to variation
                $product_variation['options'][] = $option_value_id;
            }

            $product_options_variations[] = $product_variation;

            //count total product quantity
            $total_product_quantity +=$variation->quantity;
        }

        foreach ($options as $key => $value)
            $product_options[] = $value;

        return [
            'product_options' => $product_options,
            'product_options_variations' => $product_options_variations,
            'num_options' => count($options),
            'total_product_quantity'=>$total_product_quantity
        ];
    }

    /*************** Get Option ID by name ****************/
    public function getOptionIdByName($option_name)
    {
        $result = $this->db->query("SELECT option_id FROM " . DB_PREFIX . "option_description  WHERE name = '" . $this->db->escape($option_name) . "'");
        return !empty($result->row['option_id']) ? $result->row['option_id'] : false;
    }

    /************** Create Option ***************/
    public function createOption($option_names)
    {
        if (empty($option_names))
            return false;

        $this->load->model('catalog/option');
        $this->load->model('localisation/language');
        $option = array(
            'option_description' => array(),
            'type' => 'select',
            'sort_order' => '0'
        );
        foreach ($option_names as $key => $value) {
            $language_id = $this->model_localisation_language->getLanguageByCode($key);
            if ($language_id)
                $option['option_description'][$language_id['language_id']] = ['name' => $value];
        }
        return $this->model_catalog_option->addOption($option);
    }

    /*************** Get option value ID by Name ***************/
    public function getOptionValueIdByName($option_value_name, $option_id)
    {
        $result = $this->db->query("SELECT option_value_id FROM " . DB_PREFIX . "option_value_description  WHERE name = '" . $this->db->escape($option_value_name) . "' AND option_id = " . (int)$option_id);
        return !empty($result->row['option_value_id']) ? $result->row['option_value_id'] : false;
    }

    /*************** Create Option Value ****************/
    public function createOptionValue($option_value_names, $option_id)
    {
        if (empty($option_value_names) || !$option_id)
            return false;

        $this->load->model('localisation/language');

        $option_value['option_value_description'] = array();

        foreach ($option_value_names as $key => $value) {
            $language_id = $this->model_localisation_language->getLanguageByCode($key);
            if ($language_id)
                $option_value['option_value_description'][$language_id['language_id']] = ['name' => $value];
        }

        $this->db->query("INSERT INTO " . DB_PREFIX . "option_value SET option_id = '" . (int)$option_id . "', image = '', sort_order = 0");
        $option_value_id = $this->db->getLastId();

        foreach ($option_value['option_value_description'] as $language_id => $option_value_description)
            $this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description SET option_value_id = '" . (int)$option_value_id . "', language_id = '" . (int)$language_id . "', option_id = '" . (int)$option_id . "', name = '" . $this->db->escape($option_value_description['name']) . "'");

        return $option_value_id;
    }

    /************** Delete all product Option ***************/
    public function deleteProductOptions($product_id): void
    {
        ecTargetLog( [
            'backtrace' => debug_backtrace(),
            'uri' => $_SERVER['REQUEST_URI']
        ]);
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_variations WHERE product_id = '" . (int)$product_id . "'");
    }

    /************** Delete product from sku table ***************/
    public function deleteSyncProduct($product_id): void
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "knawat_products WHERE product_id = '" . (int)$product_id . "'");
    }

    /*************** Get on progress order numbers ****************/
    public function getOnProgressOrdersCount(): int
    {
        $result = $this->db->query("SELECT COUNT(knawat_orders.order_id) AS orders_count FROM `" . DB_PREFIX . "knawat_orders` INNER JOIN `order` ON knawat_orders.order_id = order.order_id WHERE order.order_status_id NOT IN ( '5', '7')");
        return $result->row['orders_count'] ?? 0;
    }

    /*************** Get current imported products count ****************/
    public function getImportedProductsCount(): int
    {
        $totalProducts = 0;
        if (isset($this->getSettings()['total_imported_products'])) {
            $totalProducts = $this->getSettings()['total_imported_products'];
        } else {
            $result = $this->db->query("SELECT COUNT(knawat_products.product_id) AS products_count FROM `" . DB_PREFIX . "knawat_products` INNER JOIN `product` ON knawat_products.product_id = product.product_id");
            $totalProducts =  $result->row['products_count'];
        }
        return $totalProducts;
    }

    /************** Install sku option app ***************/
    private function installProductOptionsSku()
    {

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "product_variations` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `product_id` int(11) NOT NULL,
            `option_value_ids` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
            `product_sku` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `product_barcode` VARCHAR(255) NULL DEFAULT NULL,
            `num_options` int(11) NOT NULL,
            `product_price` DOUBLE DEFAULT NULL,
            `product_quantity` DOUBLE DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");

        $this->db->query("INSERT INTO `" . DB_PREFIX . "extension` SET `type` = 'module', `code` = 'productsoptions_sku'");
        $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` (`store_id`, `group`, `key`, `value`, `serialized`) VALUES (0, 'productsoptions_sku', 'productsoptions_sku_status', 1, 0)");
        $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` (`store_id`, `group`, `key`, `value`, `serialized`) VALUES (0, 'productsoptions_sku', 'productsoptions_sku_relational_status', 0, 0)");
        $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` (`store_id`, `group`, `key`, `value`, `serialized`) VALUES (0, 'productsoptions_sku', 'productsoptions_sku_option_mapping', '', 0)");

    }

    /************** set empty language values with default value  ***************/
    private function setEmptyLanguagesValue($data): stdClass
    {

        //get all installed languages sorted by code and set empty values
        $this->load->model('localisation/language');
        $languages =  $this->model_localisation_language->getLanguages(['sort'=>'code']);

        //new complete object
        $result = new stdClass();

        //get first key as default value
//        $default_value = reset($data);
        $default_value = '-';

        //set default to empty language values
        foreach($languages as $language){
            $key = $language['code'];
            $result->$key = array_key_exists($language['code'],$data) ? $data->$key : $default_value;
        }

        return $result;
    }

    /*************** get id for knawat products ****************/
    public function getKnawatProductsId() : array
    {
        $products = $this->db->query("SELECT product_id FROM knawat_products");
        $product_ids = [];

        foreach ($products->rows as $product)
         $product_ids[]=$product['product_id'];

        return $product_ids;
    }

    /**
     * Get a list of products skus to sync the clean up service
     *
     * @return array
     */
    public function getKnawatProductsSku() : array
    {
        $products = $this->db->query("SELECT sku FROM knawat_products");
        $data = [];

        foreach ($products->rows as $product) {
            $data[] = $product['sku'];
        }

        return $data;
    }

    /*************** update product and product variation price when profit change ****************/
    public function updateProductsPriceOnProfitChange($old_profit_price=1.7, $new_profit_price=2)
    {
        //get knawat products id
        $products_id = $this->getKnawatProductsId();

        //set conditional nd price calculation query for product and product_variation price
        $update_product_query = [];
        $update_product_variation_query = [];
        foreach ($products_id as $id) {
            $update_product_query[] = "WHEN " . $id . " THEN cost_price * " . (float)$new_profit_price ;
            $update_product_variation_query[] = "WHEN " . $id . " THEN (product_price / ". (float) $old_profit_price." ) *" . (float)$new_profit_price ;
        }

        //update product with new price
        $condition_query = implode(' ', $update_product_query);
        $this->db->query("UPDATE " . DB_PREFIX . "product SET price = (CASE product_id ".$condition_query." END) WHERE product_id IN (".implode(',', $products_id).")");

        //update product variation price
        $condition_query = implode(' ', $update_product_variation_query);
        $this->db->query("UPDATE " . DB_PREFIX . "product_variations SET product_price = (CASE product_id ".$condition_query." END) WHERE product_id IN (".implode(',', $products_id).")");

    }



    /////////////////////////////////////////////////////////////
    ////////////////////// Category Functions ///////////////////
    /////////////////////////////////////////////////////////////

    /*************** return categories array for product create or update ****************/
    function getProductCategories($categories=[]){

        if(empty($categories))
            return [];

        //sort categories by level
        usort($categories,function($a, $b) {return $a->treeNodeLevel > $b->treeNodeLevel;});

        $top_category_id    = 0;
        $parent_id          = 0;
        //insert product to database if not exist
        $this->load->model('catalog/category');
        foreach($categories as $category){

            //check if category exist
            $top_category_id = $this->getCategoryId($category->id);

            if($top_category_id){
                $parent_id = $top_category_id;
                continue;
            }

            //check if there is category with such name in case category not exist
            $top_category_id = $this->getCategoryIdWithName($category->name);

            //insert category if not exist
            if(!$top_category_id)
                $top_category_id    = $this->model_catalog_category->addCategory($this->formatCategoryInput($category->name, $parent_id));

            $this->createKnawatCategoryOrUpdateIfExist($category->id, $top_category_id);
            $parent_id          = $top_category_id;

        }
      return [$top_category_id];

    }

    /*************** get category id by knawat category if exist ****************/
    function getCategoryId($knawat_category_id){

        $result = $this->db->query("SELECT category.category_id as id, knawat_categories.category_id, knawat_categories.knawat_category_id  FROM `" . DB_PREFIX . "category` INNER JOIN knawat_categories ON category.category_id = knawat_categories.category_id WHERE knawat_categories.knawat_category_id = '" . (int)$knawat_category_id. "' LIMIT 1");
        return $result->row['id'] ?? false;
    }


    /*************** format category data to create ****************/
    function formatCategoryInput($name, $parent_id){

        //get all installed languages sorted by code and set empty values
        $this->load->model('localisation/language');
        $languages =  $this->model_localisation_language->getLanguages(['sort'=>'code']);

        $description = [];
        foreach($languages as $language){
            $language_code = $language['code'];
            $description[$language['language_id']]=['name' => $name->$language_code ?? $name->en];
        }
        return [
            'status'=>1,
            'parent_id'=>$parent_id,
            'category_description'=>$description
        ];
    }


    /*************** update knawat categories wit category id if exist or create if ****************/
    function createKnawatCategoryOrUpdateIfExist($knawat_category_id, $category_id){

        $result = $this->db->query("SELECT knawat_categories.id FROM `" . DB_PREFIX . "knawat_categories` WHERE knawat_category_id = '" . (int)$knawat_category_id. "' LIMIT 1");
        if($result->row['id'])
            $this->db->query("UPDATE " . DB_PREFIX . "knawat_categories SET `category_id` = '" . (int)$category_id . "' WHERE id = " . (int)$result->row['id']);
        else
            $this->db->query("INSERT INTO `" . DB_PREFIX . "knawat_categories` (`knawat_category_id`, `category_id`) VALUES ('" . (int)$knawat_category_id . "', '" . (int)$category_id ."')");

    }

    /*************** get category with such name if exist ****************/
    function getCategoryIdWithName($name){
        $result = $this->db->query("SELECT category_id FROM `" . DB_PREFIX . "category_description` WHERE name IN (".implode(',',array_map(function($c) {return  '"$c"';},(array)$name)).")  LIMIT 1");
        return $result->row['category_id'] ?? false;
    }

    public function updateKnwatVersion()
    {
        $settings = $this->getSettings();

        $version = $settings['knawat_version'] ?? 0;

        $updateSettings = false;

        if ($version < 1) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "knawat_categories` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `knawat_category_id` int(11) NOT NULL,
                `category_id` int(11) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");

            $updateSettings = true;
        }

        if ($version < 2) {
            $this->db->query("alter table knawat_products CHANGE sku sku VARCHAR(255) NULL");
            $updateSettings = true;
        }

        if (!$updateSettings) {
            return;
        }

        $settings['knawat_version'] = $this->app_version;

        $this->load->model('setting/setting');
        $this->model_setting_setting->insertUpdateSetting('knawat', [
            'knawat' => $settings
        ]);
    }



}
