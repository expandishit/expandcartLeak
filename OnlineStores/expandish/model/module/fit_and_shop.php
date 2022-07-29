<?php
/*
  @Model: Fit and shop Model.
  @Author: Hoda Sheir.
  @Version: 1.1.0
*/
class ModelModuleFitAndShop extends Model
{
    public function get_product_measurment($product_id){ 
        $res = $this->db->query("SELECT * FROM " . DB_PREFIX . "fit_shop_product_measurment_category  WHERE `product_id` = ".$product_id);
        return $res->row;
    }



}


?>