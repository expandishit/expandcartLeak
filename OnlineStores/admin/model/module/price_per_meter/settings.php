<?php

class ModelModulePricePerMeterSettings extends Model
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
            'price_per_meter', $inputs
        );

        return true;
    }

    public function install()
    {
        $alterQueries = [];
        $alterQueries[] = 'ALTER TABLE `order_product` ADD COLUMN `price_meter_data` TEXT NULL AFTER `reward`';

        foreach ($alterQueries as $alterQuery) {
            $this->db->query($alterQuery);
        }
    }

    public function uninstall()
    {
        $alterQueries = [];
        $alterQueries[] = 'ALTER TABLE `order_product` DROP COLUMN `price_meter_data`';

        foreach ($alterQueries as $alterQuery) {
            $this->db->query($alterQuery);
        }
    }

    public function getSettings()
    {
        return $this->config->get('price_per_meter');
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
