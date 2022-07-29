<?php
class ModelModuleRotate360 extends Model
{
    public function installed()
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `type` = 'module' AND `code` = 'rotate360'");
        return $query->num_rows;
    }

    public function getImagesByProductId($product_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "rotate360_images WHERE `product_id` = $product_id ORDER BY `image_order` ASC");
        if($query->num_rows){
            return $query->rows;
        }else{
            return false;
        }
    }

    public function getAppImage()
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "appservice WHERE `name` = 'rotate360'");
        if($query->num_rows){
            return $query->row['AppImage'];
        }else{
            return false;
        }
    }
}