<?php

class ModelModuleDropna extends Model
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
            'dropna', $inputs
        );

        return true;
    }

    public function getSettings()
    {
        return $this->config->get('dropna');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1) {
            return true;
        }

        return false;
    }

    public function getDropnaClient()
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM `api_clients`';
        $queryString[] = 'WHERE target="dropna"';
        $queryString[] = 'AND store_code="' . STORECODE. '"';

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows > 0) {
            return $data->row;
        }

        return false;
    }

    public function getMappedProducts($ids, $ids_as_array = false)
    {
        $dropna_ids = array(); 
        if(is_array($ids) && count($ids) > 0){
            $ids = implode(',', $ids);
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_dropna WHERE product_id IN (" . $ids . ")");
        }else{
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_dropna WHERE product_id = '" . (int)$ids . "'");
        }

        foreach ($query->rows as $result) {
          $dropna_ids[$result['product_id']] = $result['dropna_product_id'];
        } 

        return $dropna_ids;
    }

    public function getMappedOrder($order_id)
    {
        $query = $this->db->query("SELECT dropna_order_id FROM " . DB_PREFIX . "order_to_dropna WHERE order_id = '" . (int)$order_id . "'");

        foreach ($query->rows as $result) {
          $dropna_ids[] = $result['dropna_order_id'];
        } 

        return $dropna_ids;
    }
}
