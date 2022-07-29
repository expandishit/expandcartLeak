<?php

class ModelModuleDeliverySlotSettings extends Model
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
            'delivery_slot', $inputs
        );

        return true;
    }

    public function install()
    {

        // create slot table
        $this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ds_delivery_slot` (
		`ds_delivery_slot_id` int(11) NOT NULL AUTO_INCREMENT,
		`ds_day_id` int(11) NOT NULL,
        `seller_id` int(11) NOT NULL DEFAULT 0,
		`delivery_slot` varchar(200) NOT NULL,
		`total_orders` int(11) NOT NULL,
		`time_start` int(11) NOT NULL,
		`time_end` int(11) NOT NULL,
		`status` tinyint(1) NOT NULL DEFAULT '1',
		PRIMARY KEY (`ds_delivery_slot_id`)) default CHARSET=utf8");


        // create slot order table
        $this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ds_delivery_slot_order` (
		`ds_delivery_slot_order_id` int(11) NOT NULL AUTO_INCREMENT,
		`ds_delivery_slot_id` int(11) NULL,
		`order_id` int(11) NOT NULL,
		`ds_day_id` int(11) NULL,
		`slot_description` varchar(200) NULL,
		`delivery_date` varchar(200) NULL,
		`day_name` varchar(200) NULL,
		PRIMARY KEY (`ds_delivery_slot_order_id`)) default CHARSET=utf8");

    }

    public function uninstall()
    {
        // drop tables
        $this->db->query("DROP TABLE IF EXISTS`" . DB_PREFIX . "ds_delivery_slot`");
        $this->db->query("DROP TABLE IF EXISTS`" . DB_PREFIX . "ds_delivery_slot_order`");
    }

    public function getSettings()
    {
        return $this->config->get('delivery_slot');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1) {
            return true;
        }

        return false;
    }

    public function isCutOff()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['cutoff'] == 1) {
            return true;
        }

        return false;
    }

}
