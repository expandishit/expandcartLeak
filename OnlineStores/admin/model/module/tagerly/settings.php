<?php

class ModelModuleTagerlySettings extends Model
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

        $this->model_setting_setting->insertUpdateSetting(
            'tagerly', $inputs
        );

        return true;
    }


    public function getSettings()
    {
        return $this->config->get('tagerly');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1) {
            return true;
        }

        return false;
    }

    public function getTagerlyOrder($order_id){
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "tg_tagerly_order  WHERE order_id = '" . (int)$order_id . "' ORDER BY tg_tagerly_order_id DESC LIMIT 1 ");
        return $query->row;
    }

    public function install()
    {
        // create tagerly order table
        $this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "tg_tagerly_order` (
		`tg_tagerly_order_id` int(11) NOT NULL AUTO_INCREMENT,
		`order_id` int(11) NOT NULL,
		`tagerly_order_id` int(11) NOT NULL,
		`earnings` varchar(200) NOT NULL,
		PRIMARY KEY (`tg_tagerly_order_id`)) default CHARSET=utf8");

    }

    public function addTagrlyOrder($data)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "tg_tagerly_order SET order_id = '" . (int)$data['expand_order_id'] . "',	tagerly_order_id = '" . (int)$data['order_id'] . "', earnings = '" . $this->db->escape($data['earnings']) . "'");

    }

    public function uninstall()
    {
        // drop table
        $this->db->query("DROP TABLE IF EXISTS`" . DB_PREFIX . "tg_tagerly_order`");
    }


}
