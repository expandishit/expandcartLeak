<?php

class ModelModulePrintfulSettings extends Model
{
    /**
     * The settings key string.
     *
     * @var string
     */
    protected $settingsKey = 'printful';

    /**
     * @var string
     */
    protected $printfulToOrdersTable = 'printful_to_orders';

    /**
     * @var string
     */
    protected $printfulToProductsTable = 'printful_to_products';
    /**
     * @var string
     */
    protected $printfulToProductsToOrdersTable = 'printful_products_to_orders';
    /**
     * @var string
     */
    protected $printfulSellerKeyTable = 'printful_seller_key';

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var array
     */
    private static $settings = null;

    /**
     * update the module settings.
     *
     * @param array $inputs
     *
     * @return void
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->insertUpdateSetting(
            'module', [$this->settingsKey => $inputs]
        );
    }

    /**
     * Get payment settings.
     *
     * @return array|null
     */
    public function getSettings()
    {
        if (!self::$settings) {
            self::$settings = $this->config->get($this->settingsKey);
        }

        return self::$settings;
    }

    /**
     * Validate form inputs.
     *
     * @param array $data
     *
     * @return bool
     */
    public function validate($data)
    {
        return true;
    }

    /**
     * Push an error to the errors array.
     *
     * @param mixed $error
     *
     * @return void
     */
    public function setError($error)
    {
        $this->errors[] = $error;
    }

    /**
     * Get all errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return array_merge([
            'warning' => $this->language->get('invalid_form_inputs')
        ], $this->errors);
    }

    /**
     * Get printful status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->getSettings()['status'];
    }

    /**
     * Get printful api key.
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->getSettings()['api_key'];
    }

    /**
     * Install and apply the required DB changes.
     *
     * @return void
     */
    public function install()
    {
        $query = "
        CREATE TABLE `$this->printfulToProductsTable` (
            `id` int NOT NULL AUTO_INCREMENT,
            `product_id` int unsigned NOT NULL,
            `variant_id` int unsigned NOT NULL,
            `image` varchar(255) DEFAULT NULL,
            `printful_status` tinyint NOT NULL DEFAULT '0',
            `retail_price` decimal(11,2) NOT NULL DEFAULT '0.00',
            `currency` char(3) NOT NULL DEFAULT 'USD',
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `expand_product_id` int unsigned DEFAULT NULL,
            `name` varchar(500) DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `index3` (`product_id`),
            KEY `product_id` (`product_id`)
          ) ENGINE=InnoDB;          
        ";

        $this->db->query($query);

        $columns = [];
        $columns[] = '`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT';
        $columns[] = '`order_id` int(10) UNSIGNED DEFAULT NULL';
        $columns[] = '`printful_id` int(10) UNSIGNED DEFAULT NULL';
        $columns[] = '`order_status` varchar(10) NOT NULL DEFAULT "draft"';
        $columns[] = '`created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP';
        $columns[] = '`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';
        $columns[] = '`canceled_at` datetime DEFAULT NULL';
        $columns[] = 'KEY(`id`, `order_id`, `printful_id`)';

        $query = 'CREATE TABLE `%s` (%s) ENGINE=InnoDB;';

        $this->db->query(sprintf($query, $this->printfulToOrdersTable, implode(',', $columns)));

        $columns = $constraint = [];
        $columns[] = '`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT';
        $columns[] = '`printful_order_id` int(11) NOT NULL';
        $columns[] = '`product_id` int(10) UNSIGNED DEFAULT NULL';
        $columns[] = '`quantity` int(10) UNSIGNED DEFAULT NULL';
        $columns[] = '`created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP';
        $columns[] = 'KEY(`printful_order_id`, `product_id`)';
        $constraint[] = 'CONSTRAINT `printful_products_to_orders_printful_orders_printful_order_id`';
        $constraint[] = 'FOREIGN KEY (`printful_order_id`) REFERENCES `%s`(`id`)';
        $constraint[] = 'ON DELETE CASCADE';
        $constraint[] = 'ON UPDATE CASCADE';
        $columns[] = sprintf(implode(' ', $constraint), $this->printfulToOrdersTable);

        $query = 'CREATE TABLE `%s` (%s) ENGINE=InnoDB;';

        $this->db->query(sprintf($query, $this->printfulToProductsToOrdersTable, implode(',', $columns)));

        $this->db->query('
            CREATE TABLE IF NOT EXISTS `'.$this->printfulSellerKeyTable.'` (
                `seller_id` INT UNSIGNED NOT NULL,
                `api_key` TEXT NOT NULL,
                `store_id` BIGINT UNSIGNED NOT NULL
            );
        ');
        
        $settings=array(
            'status' => '0',
            'api_key' => ''
        );
        $this->updateSettings($settings);
    }

    /**
     * To drop the application related changes.
     *
     * @return void
     */
    public function uninstall()
    {
        $this->db->query('DELETE FROM `setting` where `key` like "'.$this->settingsKey.'"');
        $this->db->query('DROP TABLE IF EXISTS `' . $this->printfulToProductsToOrdersTable . '`');
        $this->db->query('DROP TABLE IF EXISTS `' . $this->printfulToOrdersTable . '`');
        $this->db->query('DROP TABLE IF EXISTS `' . $this->printfulToProductsTable . '`');
        $this->db->query('DROP TABLE IF EXISTS `' . $this->printfulSellerKeyTable . '`');
    }

    /**
     * Check if the printful order is already exists.
     *
     * @param int $orderId
     *
     * @return array|bool
     */
    public function getPrintfulByOrderId($orderId)
    {
        $query = [];
        $query[] = 'SELECT * FROM ' . $this->printfulToOrdersTable;
        $query[] = 'WHERE order_id=%u';

        $data = $this->db->query(sprintf(implode(' ', $query), $orderId));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

    /**
     * Get printful product.
     *
     * @param int $productId
     *
     * @return array|bool
     */
    public function getProduct($productId)
    {
        $query = [];
        $query[] = 'SELECT * FROM ' . $this->printfulToProductsTable;
        $query[] = 'WHERE product_id=%u';

        $data = $this->db->query(sprintf(implode(' ', $query), $productId));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

    /**
     * Insert a new printful product.
     *
     * @param int $productId
     *
     * @return void
     */
    public function insertProduct($productId, $product)
    {
        $query = $fields = [];
        $query[] = 'INSERT INTO `%s` SET';
        $fields[] = 'product_id=%u';
        $fields[] = 'variant_id="%d"';
        $fields[] = 'image="%s"';
        $fields[] = 'printful_status="%d"';
        $query[] = vsprintf(implode(', ', $fields), [
            $productId,
            $product['variant_id'],
            $product['image'],
            $product['status'],
        ]);

        $this->db->query(sprintf(implode(' ', $query), $this->printfulToProductsTable));
    }

    /**
     * Update printful product.
     *
     * @param int $productId
     *
     * @return void
     */
    public function updateProduct($productId, $product)
    {
        $query = $fields = [];
        $query[] = 'UPDATE `%s` SET';
        $fields[] = 'variant_id="%d"';
        $fields[] = 'image="%s"';
        $fields[] = 'printful_status="%d"';
        $query[] = vsprintf(implode(', ', $fields), [
            $product['variant_id'],
            $product['image'],
            $product['status'],
        ]);
        $query[] = 'WHERE product_id=%u';

        $this->db->query(sprintf(implode(' ', $query), $this->printfulToProductsTable, $productId));
    }

    /**
     * Insert a printful order.
     *
     * @param array $shipment
     * @param int $orderId
     *
     * @return int
     */
    public function insertOrder($shipment, $orderId)
    {
        $query = $fields = [];
        $query[] = 'INSERT INTO `%s` SET';
        $fields[] = 'order_id=%u';
        $fields[] = 'printful_id=%u';
        $fields[] = 'order_status="%s"';
        $query[] = vsprintf(implode(', ', $fields), [
            $orderId,
            $shipment['id'],
            $shipment['status']
        ]);

        $this->db->query(sprintf(implode(' ', $query), $this->printfulToOrdersTable));

        return $this->db->getLastId();
    }

    /**
     * Insert a printful order product.
     *
     * @param int $printfulOrderId
     * @param array $product
     *
     * @return void
     */
    public function insertOrderProduct($printfulOrderId, $product)
    {
        $query = $fields = [];
        $query[] = 'INSERT INTO `%s` SET';
        $fields[] = 'printful_order_id=%u';
        $fields[] = 'product_id=%u';
        $fields[] = 'quantity="%s"';
        $query[] = vsprintf(implode(', ', $fields), [
            $printfulOrderId,
            $product['product_id'],
            $product['quantity']
        ]);

        $this->db->query(sprintf(implode(' ', $query), $this->printfulToProductsToOrdersTable));
    }
}
