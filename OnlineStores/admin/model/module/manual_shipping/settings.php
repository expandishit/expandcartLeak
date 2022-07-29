<?php

use ExpandCart\Foundation\Support\Facades\GetResponseFactory as GetResponse;

class ModelModuleManualShippingSettings extends Model
{
    /**
     * The settings key string
     *
     * @var string
     */
    protected $settingsKey = 'manual_shipping';

    /**
     * @var array
     */
    protected $errors = null;

    /**
     * Update the module settings.
     *
     * @param array $inputs
     *
     * @return bool|\Exception
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->insertUpdateSetting(
            'module', [$this->settingsKey => $inputs]
        );

        return true;
    }

    /**
     * Get settings.
     *
     * @return array|bool
     */
    public function getSettings()
    {
        return ($this->config->get($this->settingsKey) ?: []);
    }

    /**
     * validate inputs
     *
     * @param array $data
     *
     * return bool
     */
    public function validate($data)
    {
        $this->errors = [];

        foreach ($data as $key => $value) {
            if (mb_strlen($value['title']) < 1) {
                $this->errors[] = 'Title field is required in all languages';
            }
        }

        if ($this->errors) {
            return false;
        }

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
        return is_array($this->errors) ? $this->errors : [];
    }

    /**
     * Install and apply the required DB changes.
     *
     * @return void
     */
    public function install()
    {
        $columns = [];
        $columns[] = '`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY';
        $columns[] = '`status` TINYINT NOT NULL DEFAULT "1"';
        $columns[] = '`created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP';
        $columns[] = '`updated_at` DATETIME NULL DEFAULT NULL';
        $this->db->query(sprintf(
            'CREATE TABLE `%s` (%s) ENGINE = InnoDB CHARSET=utf8 COLLATE=utf8_general_ci',
            'manual_shipping_gateways',
            implode(', ', $columns)
        ));

        $columns = [];
        $columns[] = '`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY';
        $columns[] = '`title` VARCHAR(255) NOT NULL';
        $columns[] = '`description` TEXT NULL';
        $columns[] = '`language_id` INT NOT NULL';
        $columns[] = '`shipping_gateway_id` INT NOT NULL';
        $this->db->query(sprintf(
            'CREATE TABLE `%s` (%s) ENGINE = InnoDB CHARSET=utf8 COLLATE=utf8_general_ci',
            'manual_shipping_gateways_description',
            implode(', ', $columns)
        ));

        $this->db->query('ALTER TABLE `manual_shipping_gateways_description` ADD INDEX(`shipping_gateway_id`)');
        $this->db->query('ALTER TABLE `manual_shipping_gateways_description`
            ADD CONSTRAINT `msg_shipping_gateway_id_foreign`
            FOREIGN KEY (`shipping_gateway_id`) REFERENCES `manual_shipping_gateways`(`id`)
            ON DELETE CASCADE ON UPDATE CASCADE;');

        $this->db->query(sprintf(
            'ALTER TABLE `order` ADD COLUMN `%s` TINYINT NULL DEFAULT NULL AFTER shipping_gateway_id',
            'manual_shipping_gateway'
        ));

        $columns = [];
        $columns[] = '`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY';
        $columns[] = '`product_id` INT NOT NULL';
        $columns[] = '`order_id` INT NOT NULL';
        $columns[] = '`shipping_gateway_id` INT NOT NULL';

        $this->db->query(sprintf(
            'CREATE TABLE `%s` (%s) ENGINE = InnoDB CHARSET=utf8 COLLATE=utf8_general_ci',
            'manual_shipping_order_products',
            implode(', ', $columns)
        ));
    }

    /**
     * To drop the application related changes.
     *
     * @return void
     */
    public function uninstall()
    {
        $this->db->query(sprintf(
            'ALTER TABLE `%s` DROP FOREIGN KEY `msg_shipping_gateway_id_foreign`',
            'manual_shipping_gateways_description'
        ));

        $this->db->query('DROP TABLE IF EXISTS `manual_shipping_gateways_description`');
        $this->db->query('DROP TABLE IF EXISTS `manual_shipping_gateways`');
        $this->db->query('DROP TABLE IF EXISTS `manual_shipping_order_products`');

        $this->db->query('ALTER TABLE `order` DROP COLUMN `manual_shipping_gateway`');
    }

    public function insertShipmentData(array $data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "manual_shipping_order_products SET product_id = " . (int)$data['product_id'] . ", order_id = " . (int)$data['order_id'] .", shipping_gateway_id = ".(int)$data['shipping_gateway_id']." ");
        return true;
    }

    public function get_shipped_data_by_order_id($order_id){
        $language_id = $this->config->get('config_language_id') ?: 1;

        $sql = "SELECT msp.*,msd.title,pd.name  FROM " . DB_PREFIX ."manual_shipping_order_products as msp ";
        $sql .= " LEFT JOIN `manual_shipping_gateways_description` as msd ON (msp.shipping_gateway_id = msd.shipping_gateway_id)";
        $sql .= " LEFT JOIN `product_description` as pd ON (msp.product_id = pd.product_id)";
        $sql .= " WHERE pd.language_id = " . $language_id . " AND msd.language_id = ".$language_id;
        $sql .= " AND msp.order_id = ".$order_id ;

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function deleteShipment($shipment_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "manual_shipping_order_products WHERE id = '" . (int)$shipment_id . "'");

        return true;
    }
}
