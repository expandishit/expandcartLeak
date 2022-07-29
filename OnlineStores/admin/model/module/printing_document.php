<?php

class ModelModulePrintingDocument extends Model
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
            'printing_document', $inputs
        );

        return true;
    }

    public function install()
    {
        $alterQueries = [];
        $alterQueries[] = 'ALTER TABLE `order_product` ADD `printing_document` text DEFAULT NULL AFTER `reward`';
        $alterQueries[] = 'ALTER TABLE `product` ADD `printable` tinyint DEFAULT 0 AFTER `price`';

        foreach ($alterQueries as $alterQuery) {
            $this->db->query($alterQuery);
        }
    }

    public function uninstall()
    {
        $alterQueries = [];
        $alterQueries[] = 'ALTER TABLE `order_product` DROP COLUMN `printing_document`';
        $alterQueries[] = 'ALTER TABLE `product` DROP COLUMN `printable`';

        foreach ($alterQueries as $alterQuery) {
            $this->db->query($alterQuery);
        }
    }

    public function getSettings()
    {
        return $this->config->get('printing_document_module');
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
