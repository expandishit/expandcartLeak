<?php

class ModelModuleCategoryDroplist extends Model
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
            'category_droplist', $inputs
        );

        return true;
    }

    public function getSettings()
    {
        return $this->config->get('category_droplist');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1) {
            return true;
        }

        return false;
    }

    /**
    *   Install the required values for the application.
    *
    *   @return boolean whether successful or not.
    */
    public function install()
    {
        $checkColumn = $this->db->query('SHOW COLUMNS FROM `category` LIKE "droplist_show"');
        
        if (!$checkColumn->num_rows) {
            $this->db->query('ALTER TABLE `category` ADD `droplist_show` TINYINT(1) DEFAULT 1 AFTER `status`');
        }
    }

    /**
    *   Remove the values from the database.
    *
    *   @return boolean whether successful or not.
    */
    public function uninstall()
    {
        $checkColumn = $this->db->query('SHOW COLUMNS FROM `category` LIKE "droplist_show"');
        
        if ($checkColumn->num_rows) {
            $this->db->query('ALTER TABLE `category` DROP COLUMN `droplist_show`');
        }
    }
}
