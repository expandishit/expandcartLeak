<?php

class ModelModuleEbayDropshippingSettings extends Model
{

    /**
     * Application settings key.
     *
     * @var string
     */
    protected $settingsKey = 'ebay_dropshipping';

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

    /**
     * Install and apply the required DB changes.
     *
     * @return void
     */
    public function install()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."commerce_ebay_product` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `ebay_product_id` double NOT NULL,
            `product_id` int(11) NOT NULL,
            `product_url` varchar(1000) NOT NULL,
            `name` varchar(1000) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");

        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."commerce_ebay_product_option` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `oc_option_id` int(11) NOT NULL,
                `ebay_option_id` int(11) NOT NULL,
                `value` varchar(200) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");

        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."commerce_ebay_product_option_value` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `oc_option_id` int(11) NOT NULL,
                `ebay_option_id` int(11) NOT NULL,
                `oc_option_value_id` int(11) NOT NULL,
                `ebay_option_value_id` int(11) NOT NULL,
                `value` varchar(200) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");

        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."commerce_ebay_product_variation` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `variation_text` varchar(400) NOT NULL,
                `variation_name` varchar(400) NOT NULL,
                `price` float NOT NULL,
                `price_prefix` varchar(10) NOT NULL,
                `quantity` int(11) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");

        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."commerce_ebay_product_variation_option` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `variation_id` int(11) NOT NULL,
                `option_value_id` int(11) NOT NULL,
                `product_id` int(11) NOT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");
    }

    /**
     * To drop the application related changes.
     *
     * @return void
     */
    public function uninstall()
    {
        $this->model_setting_setting->deleteSetting($this->settingsKey);
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."commerce_ebay_product`");
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."commerce_ebay_product_option`");
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."commerce_ebay_product_option_value`");
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."commerce_ebay_product_variation`");
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."commerce_ebay_product_variation_option`");
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
     * [addEbayAttrGroup creates attribute group for aliexpress products ]
     * @param  [type] $language [aleexpress language id]
     * @return [type]             [integer]
     */
    public function addEbayAttrGroup($language_id){
        $this->db->query("INSERT INTO " . DB_PREFIX . "attribute_group SET sort_order = '0'");

        $attribute_group_id = $this->db->getLastId();

        $this->db->query("INSERT INTO " . DB_PREFIX . "attribute_group_description SET attribute_group_id = '" . (int)$this->db->escape($attribute_group_id) . "', language_id = '" . (int)$this->db->escape($language_id) . "', name = 'Ebay'");

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
