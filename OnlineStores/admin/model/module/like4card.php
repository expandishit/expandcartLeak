<?php

class ModelModuleLike4card  extends Model
{
    public function install()
    {
        $this->db->query("CREATE TABLE likeforcard_to_product (
            product_like4card_id int(11) UNSIGNED PRIMARY KEY,
            product_id int(11) NULL);");

        $this->db->query("CREATE TABLE likeforcard_to_cat (
            cat_like4card_id int(11) UNSIGNED PRIMARY KEY,
            cat_like4card_parent_id int(11) DEFAULT 0,
            cat_id int(11) DEFAULT NULL,
            parent_id int(11) DEFAULT NULL);");  
    }
    
    public function uninstall()
    {
        $this->db->query("DROP TABLE likeforcard_to_product");
        $this->db->query("DROP TABLE likeforcard_to_cat");
    }

    public function insertLike4cardCat($like4card_cat_id, $like4card_parent_id, $expand_cat_id, $expand_parent_id)
    {
        $sql = "INSERT IGNORE INTO " . DB_PREFIX . "likeforcard_to_cat SET 
            cat_like4card_id = '".(int)$like4card_cat_id.
        "', cat_like4card_parent_id = '" .$like4card_parent_id.
        "', cat_id = '" .(int)$expand_cat_id.
        "', parent_id = '" .(int)$expand_parent_id."'";
        $this->db->query($sql);
    }

    public function getExpandCatId($cat_like4card_id)
    {

        $query = $this->db->query("SELECT cat_id FROM " . DB_PREFIX . "likeforcard_to_cat  
         WHERE cat_like4card_id =".$cat_like4card_id);
        return $query->row['cat_id'];

    }

    public function isCatExist($cat_like4card_id)
    {
      $expand_cat_id = $this->getExpandCatId($cat_like4card_id);
      if($expand_cat_id){
        $query = $this->db->query("SELECT count(*) as count FROM " . DB_PREFIX . "category  
                WHERE category_id =".$expand_cat_id);
        if($query->row['count'])
          return $expand_cat_id;
        else
          return 0;
      }
      return 0;
    }

    public function getExpandProductId($product_like4card_id)
    {
        $query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "likeforcard_to_product  
         WHERE product_like4card_id ='".$product_like4card_id."'");
        return $query->row['product_id'];

    }

    public function updateExpandProductId($expand_id, $product_like4card_id)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "likeforcard_to_product SET product_id = '" . (int)$expand_id ."' WHERE product_like4card_id ='".$product_like4card_id."'");
    }

    public function insertLike4cardProduct($data)
    {
        $sql = "INSERT IGNORE INTO " . DB_PREFIX . "likeforcard_to_product SET product_like4card_id = '" . (int)$data['product_like4card_id'] ."'";
        $this->db->query($sql);
    }


    public function getCats()
    {
        // get cats in arabic
        $data = 'deviceId='.$this->config->get('like4card_device_id').'&email='.$this->config->get('like4card_email').'&password='. $this->config->get('like4card_password').'&securityCode='. $this->config->get('like4card_security_code').'&langId=2';

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://taxes.like4app.com/online/categories",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $data,
          CURLOPT_HTTPHEADER => array(
            "Content-Type: application/x-www-form-urlencoded"
          ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);
        return $response;
    }

    public function getLike4cardProducts()
    {
        $query = $this->db->query("SELECT product_like4card_id FROM " . DB_PREFIX . "likeforcard_to_product");
        return $query->rows;

    }
    public function getProducts()
    {
        // get products in arabic
        $productsToRetrieve = $this->getLike4cardProducts();
        $product_ids = "";
        foreach ($productsToRetrieve as $product) {
            $product_ids .= '&ids[]='.$product['product_like4card_id'];
        }
        $data = 'deviceId='.$this->config->get('like4card_device_id').'&email='.$this->config->get('like4card_email').'&password='. $this->config->get('like4card_password').'&securityCode='. $this->config->get('like4card_security_code').'&langId=2'.$product_ids;
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://taxes.like4app.com/online/products",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $data,
          CURLOPT_HTTPHEADER => array(
            "Content-Type: application/x-www-form-urlencoded"
          ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);
        return $response;
    }

    public function getBalance()
    {
        $data = 'deviceId='.$this->config->get('like4card_device_id').'&email='.$this->config->get('like4card_email').'&password='. $this->config->get('like4card_password').'&securityCode='. $this->config->get('like4card_security_code').'&langId=1';

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://taxes.like4app.com/online/check_balance/",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $data,
          CURLOPT_HTTPHEADER => array(
            "Content-Type: application/x-www-form-urlencoded"
          ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);
        return $response;
    }
}