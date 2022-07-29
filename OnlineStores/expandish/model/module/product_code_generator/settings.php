<?php

class ModelModuleProductCodeGeneratorSettings extends Model
{

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

    public function addCode($product_id,array $data) {
        if(is_array($data))
        {
            foreach ($data as $key=>$value){
                if(!empty($value) && $value != null)
                {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_code_generator SET product_id = '" . (int)$product_id. "', code = '" . trim($value) . "'");
                }
            }
            return true;
        }
        return false;
    }

    public function getCodesByProductId($product_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_code_generator WHERE product_id = '" . (int)$product_id . "'");
        return $query->rows;
    }

    public function deleteProductCodes($product_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_code_generator WHERE product_id = '" . (int)$product_id . "'");
        return true;
    }
}
