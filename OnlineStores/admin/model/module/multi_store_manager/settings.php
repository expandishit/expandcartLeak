<?php

class ModelModuleMultiStoreManagerSettings extends Model
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
            'multi_store_manager', $inputs
        );

        return true;
    }
    

    public function getSettings()
    {
        return $this->config->get('multi_store_manager');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1) {
            return true;
        }

        return false;
    }

    public function getStoreCodes(){
        $sql = 'SELECT * FROM PaidUsers pu WHERE pu.USERID = '.WHMCS_USER_ID;
        $sql .= ' GROUP BY `STORECODE` ';
        $query = $this->ecusersdb->query($sql);
        return $query->rows;
    }

    public function getStoreCodesArray(){
        $sql = 'SELECT * FROM stores s WHERE s.whmcs_user_id = '.WHMCS_USER_ID;
        $sql .= ' GROUP BY `STORECODE` ';

        $query = $this->ecusersdb->query($sql);
        $storeCodesArray = [];
        foreach ($query->rows as $code){
            $storeCodesArray[] = $code['storecode'];
        }
        return $storeCodesArray;
    }
}
