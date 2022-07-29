<?php

class ModelModuleRotate360 extends Model
{
    public function uninstall() {
        return $this->db->query("DROP TABLE IF EXISTS `rotate360_images`");
    }

    public function install()
    {
        $sql = "CREATE TABLE `rotate360_images` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `product_id` int(11) NOT NULL,
                  `image_name` tinytext,
                  `image_path` tinytext,
                  `image_order` int(11) DEFAULT NULL,
                  PRIMARY KEY (`id`)
                );";

            $query = $this->db->query( $sql );

            return $query;
    }

    public function installed()
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `type` = 'module' AND `code` = 'rotate360'");
        return $query->num_rows;
    }

    public function getImagesByProductId($product_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "rotate360_images WHERE `product_id` = $product_id ORDER BY `image_order` ASC");
        if ($query->num_rows) {
            return $query->rows;
        } else {
            return false;
        }
    }

    public function insertImage($image)
    {
        if ($image) {
            $this->db->query("INSERT INTO  " . DB_PREFIX . "rotate360_images SET `product_id` ='" . $image['product_id'] . "',`image_path`='" . $image["image_path"] . "',`image_name`='" . $image["image_name"] . "',`image_order`='" . $image["image_order"] . "' ");
        return true;
        }
        return false;
    }

    public function deleteImage($image)
    {
        if ($image) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "rotate360_images WHERE `product_id` ='" . $image['product_id'] . "',`image_path`='" . $image["image_path"] . "',`image_name`='" . $image["image_name"] ."' ");
            return true;
        }
        return false;
    }

}