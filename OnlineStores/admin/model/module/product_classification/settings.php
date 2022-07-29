<?php

class ModelModuleProductClassificationSettings extends Model
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
            'product_classification', $inputs
        );

        return true;
    }

    public function install()
    {
        // create brand table
        $this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pc_brand` (
		`pc_brand_id` int(11) NOT NULL AUTO_INCREMENT,
		`status` tinyint(1) NOT NULL DEFAULT '1',
		PRIMARY KEY (`pc_brand_id`)) default CHARSET=utf8");

        // create model table
        $this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pc_model` (
		`pc_model_id` int(11) NOT NULL AUTO_INCREMENT,
		`status` tinyint(1) NOT NULL DEFAULT '1',
		PRIMARY KEY (`pc_model_id`)) default CHARSET=utf8");

        // create year table
        $this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pc_year` (
		`pc_year_id` int(11) NOT NULL AUTO_INCREMENT,
		`name` varchar(64) NOT NULL,
		`status` tinyint(1) NOT NULL DEFAULT '1',
		PRIMARY KEY (`pc_year_id`)) default CHARSET=utf8");

        // create brand description table
        $this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pc_brand_description` (
		`pc_brand_id` int(11) NOT NULL,
		`name` varchar(80) NOT NULL,
		`language_id` int(11) NOT NULL,
		PRIMARY KEY (`pc_brand_id`,`language_id`)) default CHARSET=utf8");

        // create model description table
        $this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pc_model_description` (
		`pc_model_id` int(11) NOT NULL,
		`name` varchar(80) NOT NULL,
		`language_id` int(11) NOT NULL,
		PRIMARY KEY (`pc_model_id`,`language_id`)) default CHARSET=utf8");

        // create product brand mapping table
        $this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pc_product_brand_mapping` (
		`product_id` int(11) NOT NULL,
		`pc_brand_id` int(11) NOT NULL,
		`pc_model_id` int(11) NOT NULL,
		`pc_year_id` int(11) NOT NULL,
		`pc_row_key` int(11) NOT NULL,
		PRIMARY KEY (`product_id`,`pc_brand_id`,`pc_model_id`,`pc_year_id`)) default CHARSET=utf8");

        // create product classification relations table
        $this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pc_relations` (
		`pc_relations_id` int(11) NOT NULL AUTO_INCREMENT,
		`parent_id` int(11) NOT NULL,
		`sub_id` int(11) NOT NULL,
		`type` varchar(40) NOT NULL,
		PRIMARY KEY (`pc_relations_id`)) default CHARSET=utf8");

    }

    public function uninstall()
    {
        // drop tables
        $this->db->query("DROP TABLE IF EXISTS`" . DB_PREFIX . "pc_brand`");
        $this->db->query("DROP TABLE IF EXISTS`" . DB_PREFIX . "pc_model`");
        $this->db->query("DROP TABLE IF EXISTS`" . DB_PREFIX . "pc_year`");
        $this->db->query("DROP TABLE IF EXISTS`" . DB_PREFIX . "pc_brand_description`");
        $this->db->query("DROP TABLE IF EXISTS`" . DB_PREFIX . "pc_model_description`");
        $this->db->query("DROP TABLE IF EXISTS`" . DB_PREFIX . "pc_product_brand_mapping`");
        $this->db->query("DROP TABLE IF EXISTS`" . DB_PREFIX . "pc_relations`");
    }

    public function getSettings()
    {
        return $this->config->get('product_classification');
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
