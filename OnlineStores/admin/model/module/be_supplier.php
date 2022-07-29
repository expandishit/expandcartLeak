<?php

class ModelModuleBeSupplier extends Model
{ 
    public function getImportDate($reference){
        $results = $this->db->query("SELECT count(*) as total, (SELECT count(b.status) FROM " . DB_PREFIX . "product_to_dropna_schedule b where status = 'success' AND `reference` = '".$reference."') as success, (SELECT count(b.status) FROM " . DB_PREFIX . "product_to_dropna_schedule b where status = 'wait' AND `reference` = '".$reference."') as wait FROM " . DB_PREFIX . "product_to_dropna_schedule a WHERE `reference` = '".$reference."'");

        if ($results->row['total'] > 0) {
            return $results->row;
        }

        return false;
    }
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
            'be_supplier', $inputs
        );

        return true;
    }

    public function updateDropnaAlert(){
        $this->load->model('setting/setting');

        $this->model_setting_setting->editSetting(
            'be_supplier', $inputs
        );

        return true;
    }

    public function getSettings()
    {
        return $this->config->get('be_supplier');
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
