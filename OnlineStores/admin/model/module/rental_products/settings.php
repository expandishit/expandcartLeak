<?php

class ModelModuleRentalProductsSettings extends Model
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
            'rental_products', $inputs
        );

        return true;
    }

    public function install()
    {
        $this->db->query("
        CREATE TABLE IF NOT EXISTS  `" . DB_PREFIX . "order_product_rental`
        (
            `order_product_rental_id` int(11) NOT NULL AUTO_INCREMENT,
            `order_product_id` int(11) NOT NULL,
            `order_id` int(11) NOT NULL,
            `diff` int(11) NOT NULL,
            `from_date` date NOT NULL,
            `to_date` date NOT NULL,
            PRIMARY KEY (`order_product_rental_id`)
        ) 
        ENGINE=InnoDB DEFAULT COLLATE=utf8_general_ci;");
    }

    public function uninstall()
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "order_product_rental`;");
    }

    public function getSettings()
    {
        return $this->config->get('rental_products');
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
