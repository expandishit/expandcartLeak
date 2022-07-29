<?php
/*
  @Model: Fit and shop Model.
  @Author: Hoda Sheir.
  @Version: 1.1.0
*/
class ModelModuleFitAndShop extends Model
{

  public function updateSettings($inputs)
  {
    $this->load->model('setting/setting');
    $this->model_setting_setting->insertUpdateSetting(
      'fit_and_shop', $inputs
    );
    return true;
  }


  public function getSettings()
  {
    return $this->config->get('fit_and_shop');
  }

  public function install()
  {
    // create measurements categories table
    $this->db->query("
      CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "fit_shop_measurements_categories` (
      `measurement_category_id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(200) NOT NULL,
      `date_modified` DATETIME,
      `date_added` DATETIME,
      PRIMARY KEY (`measurement_category_id`)) default CHARSET=utf8");

    // createcategories measurments product table
    $this->db->query("
      CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "fit_shop_product_measurment_category` (
      `measurment_category_product_id` int(11) NOT NULL AUTO_INCREMENT,
      `product_id` int(11) NOT NULL,
      `measurment_category_id` int(11) NOT NULL,
      `collection_sku`  varchar(255) NOT NULL,
      `fit_status` int(11) NOT NULL,
      `date_modified` DATETIME,
      `date_added` DATETIME,
      PRIMARY KEY (`measurment_category_product_id`)) default CHARSET=utf8");  
  }

  public function uninstall()
  {
    // drop table
    $this->db->query("DROP TABLE IF EXISTS`" . DB_PREFIX . "fit_shop_measurements_categories`");
    $this->db->query("DROP TABLE IF EXISTS`" . DB_PREFIX . "fit_shop_product_measurment_category`");
  }

  public function checkCategoryExists($category_id){
    $query = $this->db->query("SELECT count(measurement_category_id ) as total FROM  " . DB_PREFIX . "fit_shop_measurements_categories WHERE measurement_category_id  = '" . $category_id . "' ");
    return ($query->row['total'] > 0) ? TRUE : FALSE;
  }

  public function add_category($data){
    $this->db->query("INSERT INTO " . DB_PREFIX . "fit_shop_measurements_categories SET measurement_category_id = '" . (int)$data['id'] . "', name = '".$data['name']."' ,date_modified = NOW(), date_added = NOW()");
  }

  public function get_categories(){
    $res = $this->db->query("SELECT * FROM " . DB_PREFIX . "fit_shop_measurements_categories");
    return $res->rows;

  }

  public function insert_product_measurement($data){ 
    $this->db->query("INSERT INTO " . DB_PREFIX . "fit_shop_product_measurment_category SET `product_id` = '" . (int)$data['product_id'] . "', `measurment_category_id` = '" . (int)$data['measurment_category_id']. "', `collection_sku` = '" . $data['collection_sku'] . "',  `fit_status` = '" . (int)$data['fit_status'] . "', `date_modified` = NOW(), `date_added` = NOW()");
  }

  public function get_product_measurment($product_id){ 
    $res = $this->db->query("SELECT * FROM " . DB_PREFIX . "fit_shop_product_measurment_category  WHERE `product_id` = ".$product_id);
    return $res->row;
  }

  public function getCollections($settingData){
    $apikey = $settingData['apikey'];
    $status = $settingData['status'];
        if($apikey && $status){
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "http://app.fitandshop.me/apiv2/Store/get_store_collections",
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'version:1.1',
                    'x-api-key: '.$apikey,
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response,true);
            if($settingData['flag']){
              return $response['collections'];
            }
            $json_data = '';
            if( is_array($response) && count($response['collections']) > 0){
                foreach($response['collections'] as $key=>$collection){
                    $json_data.= '<option value="'.$collection['code'].'">'.$collection['title_en'].'</option>';
                }
            }
            echo $json_data; exit;
        }
  }

  public function update_product_measurement($data){
    $this->db->query("UPDATE " . DB_PREFIX . "fit_shop_product_measurment_category  SET `measurment_category_id` = '" . (int)$data['measurment_category_id']. "',  `collection_sku` = '" . $data['collection_sku'] . "',  `fit_status` = '" . (int)$data['fit_status'] . "', `date_modified` = NOW() WHERE  `measurment_category_product_id` = '".$data['measurment_category_product_id']."'");
  }

  public function delete_product_measurement($measurment_category_product_id){
    $this->db->query("DELETE FROM " . DB_PREFIX . "fit_shop_product_measurment_category  WHERE `measurment_category_product_id` = '".$measurment_category_product_id ."'");
  }
} 

?>