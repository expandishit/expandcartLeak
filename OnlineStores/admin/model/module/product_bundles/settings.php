<?php

class ModelModuleProductBundlesSettings extends Model
{
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
            'product_bundles', $inputs
        );

        return true;
    }

    public function install()
    {

        $this->db->query("
        CREATE TABLE IF NOT EXISTS  `" . DB_PREFIX . "product_bundles`
        (
            `bundle_id` int(11) NOT NULL AUTO_INCREMENT,
            `main_product_id` int(11) NOT NULL,
            `bundle_product_id` int(11) NOT NULL,
            `discount` decimal(4,2) NOT NULL DEFAULT '0.00',
            PRIMARY KEY (`bundle_id`)

        ) 
        ENGINE=InnoDB DEFAULT COLLATE=utf8_general_ci;");

        $this->db->query("
        CREATE TABLE IF NOT EXISTS  `" . DB_PREFIX . "order_product_bundle`
        (
            `order_product_bundle_id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` int(11) NOT NULL,
            `order_product_id` int(11) NOT NULL, 
            `bundle_product_id` int(11) NOT NULL,
            `quantity` int(11) NOT NULL,
            `price` decimal NOT NULL,
            `discount` decimal(4,2) NOT NULL DEFAULT '0.00',
            PRIMARY KEY (`order_product_bundle_id`)
        ) 
        ENGINE=InnoDB DEFAULT COLLATE=utf8_general_ci;"); 
        // bundle_product_id is the bundled product id 

    }

    public function uninstall()
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "product_bundles`;");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "order_product_bundle`;");
    }

    public function getSettings()
    {
        return $this->config->get('product_bundles');
    }

    public function isActive()
    {
        $settings = $this->getSettings();
        if (isset($settings) && $settings['status'] == 1) {
            return true;
        }

        return false;
    }
}
