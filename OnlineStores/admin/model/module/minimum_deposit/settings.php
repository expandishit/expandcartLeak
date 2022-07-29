<?php

class ModelModuleMinimumDepositSettings extends Model
{
    /**
     *
     * update the module settings.
     *
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->editSetting(
            'minimum_deposit', $inputs
        );

        return true;
    }

    public function install()
    {
        $this->db->query("ALTER TABLE `product` ADD COLUMN `minimum_deposit_price` FLOAT NULL");
        $this->db->query("ALTER TABLE `order_product` ADD COLUMN `main_price` FLOAT NULL");
        $this->db->query("ALTER TABLE `order_product` ADD COLUMN `remaining_amount` FLOAT NULL");
    }

    public function uninstall()
    {
        $this->db->query("ALTER TABLE `product` DROP COLUMN `minimum_deposit_price`");
        $this->db->query("ALTER TABLE `order_product` DROP COLUMN `main_price`");
        $this->db->query("ALTER TABLE `order_product` DROP COLUMN `remaining_amount`");

    }

    public function getSettings()
    {
        return $this->config->get('minimum_deposit');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['md_status'] == 1) {
            return true;
        }

        return false;
    }
}
