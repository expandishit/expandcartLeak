<?php

class ModelModuleSalesBooster extends Model
{
    public function getSettings()
    {
        return $this->config->get('sales_booster_module');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1) {
            return true;
        }

        return false;
    }

    public function isForceApply()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['forceApply'] == 1) {
            return true;
        }

        return false;
    }

    public function getLayout($layout_id) {        
        $query = $this->db->query("SELECT description FROM " . DB_PREFIX . "sales_booster_layouts_description WHERE layout_id = '" . (int)$layout_id . "' AND language_id= '" . (int)$this->config->get('config_language_id') . "'");
        
        return $query->row;
    }
}
