<?php

class ModelModuleQoyodIntegrationSettings extends Model
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
            'qoyod_integration', $inputs
        );

        return true;
    }

    public function install()
    {
        $alterQueries = [];
        $alterQueries[] = 'ALTER TABLE `order` ADD COLUMN `qoyod_invoice` INT DEFAULT 0 AFTER `date_added`';

        foreach ($alterQueries as $alterQuery) {
            $this->db->query($alterQuery);
        }
    }

    public function uninstall()
    {
        $alterQueries = [];
        $alterQueries[] = 'ALTER TABLE `order` DROP COLUMN `qoyod_invoice`';

        foreach ($alterQueries as $alterQuery) {
            $this->db->query($alterQuery);
        }
    }

    public function getSettings()
    {
        return $this->config->get('qoyod');
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
