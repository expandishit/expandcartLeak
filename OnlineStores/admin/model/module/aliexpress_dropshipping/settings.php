<?php

class ModelModuleAliexpressDropshippingSettings extends Model
{
    /**
     * Abandoned cart send emails table.
     *
     * @var string
     */
    protected $emailedAbandonedOrdersTable = 'emailed_abandoned_orders';

    /**
     * Application settings key.
     *
     * @var string
     */
    protected $settingsKey = 'aliexpress_dropshipping';

    /**
     * update the module settings.
     *
     * @param array $inputs
     *
     * @return bool|\Exception
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->editSetting(
            $this->settingsKey, $inputs
        );

        return true;
    }

    public function checkForUpdate() {

        $status = false;

        $sql = "SELECT * FROM information_schema.tables WHERE table_schema = '".DB_DATABASE."' AND table_name = '".DB_PREFIX."warehouse_aliexpress_product_option' ";
        $result = $this->db->query($sql)->row;
        if($result) {
            $status = true;
        }

        if($status) {
            $check_quantity = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '".DB_DATABASE."' AND TABLE_NAME = '".DB_PREFIX."warehouse_aliexpress_product_variation' AND COLUMN_NAME = 'quantity'")->row;

            if (!$check_quantity) {
                $status = false;
            }
        }

        return $status;
    }

    /**
     * Install and apply the required DB changes.
     *
     * @return void
     */
    public function install()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."warehouse` (
            `warehouse_id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `status` int(11) NOT NULL,
            `warehouse_code` varchar(200) NOT NULL,
            `title` varchar(200) NOT NULL,
            `description` varchar(500) NOT NULL,
            `country_id` int(50) NOT NULL,
            `zone_id` int(50) NOT NULL,
            `city` varchar(200) NOT NULL,
            `postal_code` varchar(200) NOT NULL,
            `street` varchar(200) NOT NULL,
            `longitude` varchar(200) NOT NULL,
            `latitude` varchar(200) NOT NULL,
            PRIMARY KEY (`warehouse_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");

        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."warehouse_aliexpress_seller` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `seller_name` varchar(100) NOT NULL,
            `date_added` date NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");

        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."warehouse_aliexpress_order` (
            `order_id` int(11) NOT NULL,
            `customer_name` varchar(100) NOT NULL,
            `status` tinyint(1) NOT NULL
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");

        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."warehouse_aliexpress_product` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `ali_product_id` double NOT NULL,
            `aliexpress_seller_id` int(11) NOT NULL,
            `product_id` int(11) NOT NULL,
            `product_url` varchar(1000) NOT NULL,
            `name` varchar(1000) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");

        if(!$this->checkForUpdate()) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."warehouse_aliexpress_product_option` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `oc_option_id` int(11) NOT NULL,
                `alix_option_id` int(11) NOT NULL,
                `value` varchar(200) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");

            $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."warehouse_aliexpress_product_option_value` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `oc_option_id` int(11) NOT NULL,
                `alix_option_id` int(11) NOT NULL,
                `oc_option_value_id` int(11) NOT NULL,
                `alix_option_value_id` int(11) NOT NULL,
                `value` varchar(200) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");

            $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."warehouse_aliexpress_product_variation` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `variation_text` varchar(400) NOT NULL,
                `variation_name` varchar(400) NOT NULL,
                `price` float NOT NULL,
                `price_prefix` varchar(10) NOT NULL,
                `quantity` int(11) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");

            $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."warehouse_aliexpress_product_variation_option` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `variation_id` int(11) NOT NULL,
                `option_value_id` int(11) NOT NULL,
                `product_id` int(11) NOT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");
        }
        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."warehouse_order` (
            `warehouse_order_id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` int(100) NOT NULL,
            `warehouse_id` int(100) NOT NULL,
            `quantity` int(11) NOT NULL,
            `price` double NOT NULL,
            `warehouseAmount` double NOT NULL,
            `adminAmount` double NOT NULL,
            `product_id` int(11) NOT NULL,
            `total` double NOT NULL,
            `order_currency` varchar(50) NOT NULL,
            `paid_status` tinyint(4) NOT NULL,
            PRIMARY KEY (`warehouse_order_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");

        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."warehouse_order_shipping` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `warehouse_id` int(11) NOT NULL,
            `order_id` int(11) NOT NULL,
            `title` varchar(200) NOT NULL,
            `code` varchar(50) NOT NULL,
            `cost` double NOT NULL,
            `currency` varchar(50) NOT NULL,
            `paid` tinyint(1) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");

        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."warehouse_product` (
            `warehouse_product_id` int(11) NOT NULL AUTO_INCREMENT,
            `warehouse_id` int(100) NULL,
            `user_id` int(11) NOT NULL,
            `product_id` int(100) NULL,
            `quantity` int(11) NOT NULL,
            `price` double NOT NULL,
            `price_diff` double NOT NULL,
            `approved` tinyint(1) NOT NULL,
            PRIMARY KEY (`warehouse_product_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");

        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."warehouse_shipping` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `warehouse_id` int(100) NOT NULL,
            `country_code` varchar(200) NOT NULL,
            `zip_from` varchar(100) NOT NULL,
            `zip_to` varchar(100) NOT NULL,
            `price` varchar(100) NOT NULL,
            `weight_from` varchar(100) NOT NULL,
            `weight_to` varchar(100) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");

        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."warehouse_shippings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `warehouse_id` int(11) NOT NULL,
            `code` varchar(100) NOT NULL,
            `status` tinyint(1) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");

        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."warehouse_transaction` (
            `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `warehouse_id` int(11) NOT NULL,
            `total` double NOT NULL,
            `description` varchar(500) NOT NULL,
            `date_added` datetime NOT NULL,
            `status` tinyint(4) NOT NULL,
            PRIMARY KEY (`transaction_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");

        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."warehouse_transaction_details` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `transaction_id` int(11) NOT NULL,
            `warehouse_order_id` int(11) NOT NULL,
            `order_id` int(11) NOT NULL,
            `warehouse_id` int(11) NOT NULL,
            `product_id` int(11) NOT NULL,
            `amount` double NOT NULL,
            `description` varchar(500) NOT NULL,
            `date_added` datetime NOT NULL,
            `status` tinyint(4) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");

        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."price_rule` (
            `rule_id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `channel` varchar(10) NOT NULL,
            `category_id` int(11) NOT NULL,
            `category_relation` varchar(10) NOT NULL,
            `price_from` double NOT NULL,
            `price_to` double NOT NULL,
            `operation_type` varchar(10) NOT NULL,
            `method_type` varchar(10) NOT NULL,
            `amount` double NOT NULL,
            PRIMARY KEY (`rule_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");

        ///////////////////////////////////////////////////////////////////////

        $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse_aliexpress_order` (
        `order_id` int(11) NOT NULL,
        `customer_name` varchar(100) NOT NULL,
        `status` tinyint(1) NOT NULL
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');

        $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse_aliexpress_variation` (
        `option_id` int(11) NOT NULL,
        `option_value_id` int(11) NOT NULL,
        `aliexpress_variation` varchar(100) NOT NULL,
        `aliexpress_variation_text` text NOT NULL
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');

        $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse_aliexpress_product_option` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `oc_option_id` int(11) NOT NULL,
            `alix_option_id` int(11) NOT NULL,
            `value` varchar(200) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');

        $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse_aliexpress_product_option_value` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `oc_option_id` int(11) NOT NULL,
            `alix_option_id` int(11) NOT NULL,
            `oc_option_value_id` int(11) NOT NULL,
            `alix_option_value_id` int(11) NOT NULL,
            `value` varchar(200) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');

        $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse_aliexpress_product_variation` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `product_id` int(11) NOT NULL,
            `variation_text` varchar(400) NOT NULL,
            `variation_name` varchar(400) NOT NULL,
            `price` float NOT NULL,
            `price_prefix` varchar(10) NOT NULL,
            `quantity` int(11) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');

        $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse_aliexpress_product_variation_option` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `variation_id` int(11) NOT NULL,
            `option_value_id` int(11) NOT NULL,
            `product_id` int(11) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');

        $check_quantity = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '".DB_DATABASE."' AND TABLE_NAME = '".DB_PREFIX."warehouse_aliexpress_product_variation' AND COLUMN_NAME = 'quantity'")->row;

        if (!$check_quantity) {
            $this->db->query('ALTER TABLE `'.DB_PREFIX.'warehouse_aliexpress_product_variation` ADD COLUMN `quantity` int(11) NOT NULL DEFAULT '.$this->config->get('wk_dropship_aliexpress_quantity'));
        }

        $sql = "SELECT * FROM information_schema.tables WHERE table_schema = '".DB_DATABASE."' AND table_name = '".DB_PREFIX."warehouse_aliexpress_option_value' ";

        $result = $this->db->query($sql)->row;
        if ($result) {
            $result = $this->db->query('SELECT * FROM '.DB_PREFIX.'warehouse_aliexpress_option ')->rows;
            if ($result) {
                foreach ($result as $key => $value) {
                    if (isset($value['alix_option_id'])) {
                        $this->db->query('INSERT INTO '.DB_PREFIX."warehouse_aliexpress_product_option SET id = '".$value['id']."', oc_option_id = '".$value['oc_option_id']."', alix_option_id = '".$value['alix_option_id']."', value = '".$value['value']."' ");
                    }
                }
            }

            $result = $this->db->query('SELECT * FROM '.DB_PREFIX.'warehouse_aliexpress_option_value ')->rows;
            if ($result) {
                foreach ($result as $key => $value) {
                    if (isset($value['alix_option_id'])) {
                        $this->db->query('INSERT INTO '.DB_PREFIX."warehouse_aliexpress_product_option_value SET id = '".$value['id']."', oc_option_id = '".$value['oc_option_id']."', alix_option_id = '".$value['alix_option_id']."', oc_option_value_id = '".$value['oc_option_value_id']."', alix_option_value_id = '".$value['alix_option_value_id']."', value = '".$value['value']."' ");
                    }
                }
            }
        }
    }

    /**
     * To drop the application related changes.
     *
     * @return void
     */
    public function uninstall()
    {
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."warehouse`");
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."warehouse_aliexpress_product`");
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."warehouse_aliexpress_seller`");
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."warehouse_aliexpress_order`");
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."warehouse_aliexpress_option`");
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."warehouse_aliexpress_option_value`");
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."warehouse_aliexpress_product_option`");
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."warehouse_aliexpress_product_option_value`");
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."warehouse_aliexpress_product_variation`");
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."warehouse_aliexpress_product_variation_option`");
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."warehouse_aliexpress_variation`");
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."warehouse_order`");
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."warehouse_order_shipping`");
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."warehouse_product`");
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."warehouse_shipping`");
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."warehouse_shippings`");
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."warehouse_transaction`");
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."warehouse_transaction_details`");
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."price_rule`");
    }

    /**
     * Gets the application settings.
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->config->get($this->settingsKey);
    }

    /**
     * Checks if the application is active or not.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->isInstalled();
    }

    /**
     * Checks if the application is insalled or not.
     *
     * @return bool
     */
    public function isInstalled()
    {
        $application = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension
            WHERE `code` = '" . $this->settingsKey . "'");

        if ($application->num_rows > 0) {
            return true;
        }

        return false;
    }

    /**
     * [addAlliexpressAttrGroup creates attribute group for aliexpress products ]
     * @param  [type] $language [aleexpress language id]
     * @return [type]             [integer]
     */
    public function addAlliexpressAttrGroup($language_id){
        $this->db->query("INSERT INTO " . DB_PREFIX . "attribute_group SET sort_order = '0'");

        $attribute_group_id = $this->db->getLastId();

        $this->db->query("INSERT INTO " . DB_PREFIX . "attribute_group_description SET attribute_group_id = '" . (int)$this->db->escape($attribute_group_id) . "', language_id = '" . (int)$this->db->escape($language_id) . "', name = 'Aliexpress'");

        return $attribute_group_id;
    }

    /**
     * [checkAttributeGroupExists checks aliexpress attribute group exists  ]
     * @param  [type] $attribute_group_id [attribute group id]
     * @return [type]             [boolean]
     */
    public function checkAttributeGroupExists($attribute_group_id){
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "attribute_group WHERE attribute_group_id = '" . (int)$this->db->escape($attribute_group_id) . "'");

        if ($query->row) {
            return true;
        }else{
            return false;
        }
    }
}
