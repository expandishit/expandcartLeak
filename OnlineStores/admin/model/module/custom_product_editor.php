<?php

class ModelModuleCustomProductEditor extends Model
{

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
            'custom_product_editor',
            $inputs
        );

        return true;
    }

    public function getSettings()
    {
        $settings =  $this->config->get('custom_product_editor') ?? [];
        return array_merge(['status' => 0], $settings);
    }

    /**
     * Check if app installed
     *
     * @return boolean
     */
    public function isInstalled()
    {
        return \Extension::isInstalled('custom_product_editor');
    }

    /**
     * Check is App Active
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->isInstalled() && (int) $this->getSettings()['status'] === 1;
    }

    /**
     *   Install the required values for the application.
     *
     *   @return boolean whether successful or not.
     */
    public function install($store_id = 0)
    {
        try {

            $sql = "ALTER TABLE `" . DB_PREFIX . "product_description` ADD `custom_html` LONGTEXT DEFAULT NULL AFTER `description`;";
            $this->db->query($sql);

            $sql = "ALTER TABLE `" . DB_PREFIX . "product` ADD `custom_html_status` tinyint(1) NOT NULL DEFAULT 0 AFTER `status`;";

            $this->db->query($sql);

            $sql = "ALTER TABLE `" . DB_PREFIX . "product` ADD `display_main_page_layout` tinyint(1) NOT NULL DEFAULT 0 AFTER `status`;";

            $this->db->query($sql);

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
    public function uninstall($store_id = 0, $group = 'product_management')
    {
        try {

            $sql = "ALTER TABLE `" . DB_PREFIX . "product_description` DROP COLUMN `custom_html`;";
            $this->db->query($sql);

            $sql = "ALTER TABLE `" . DB_PREFIX . "product` DROP COLUMN `custom_html_status`;";
            $this->db->query($sql);

            $sql = "ALTER TABLE `" . DB_PREFIX . "product` DROP COLUMN `display_main_page_layout`;";
            $this->db->query($sql);

            return true;
        } catch (Exception $th) {
            return false;
        }
    }
}
