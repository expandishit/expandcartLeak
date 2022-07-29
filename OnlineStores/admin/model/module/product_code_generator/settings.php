<?php

class ModelModuleProductCodeGeneratorSettings extends Model
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
            'product_code_generator', $inputs
        );

        return true;
    }

    public function install()
    {
        $this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "product_code_generator` (
		`product_code_generator_id` int(11) NOT NULL AUTO_INCREMENT,
		`code` varchar(250) NOT NULL,
		`product_id` int(11) NOT NULL,
		`is_used` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'check if code used before (1 = used & 0 = not used)',
		PRIMARY KEY (`product_code_generator_id`)) default CHARSET=utf8");
    }

    public function uninstall()
    {
        $this->db->query("DROP TABLE IF EXISTS`" . DB_PREFIX . "product_code_generator`");
    }

    public function getSettings()
    {
        return $this->config->get('product_code_generator');
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
