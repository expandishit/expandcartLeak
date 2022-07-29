<?php

class ModelModuleStoreReviews extends Model
{
    public function install()
    {

        $query = "CREATE TABLE IF NOT EXISTS `" .DB_PREFIX. "store_reviews` (
            `id` int(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `customer_id` int(11) NOT NULL,
            `ip_address` varchar(255) NOT NULL,
            `rate` FLOAT DEFAULT 0,
            `name` VARCHAR(255) NOT NULL ,
            `rate_description` TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->db->query($query);

        $this->db->query($query);
        
        return true;
    }

    public function uninstall()
    {
        $query = "DROP TABLE IF EXISTS `" .DB_PREFIX. "store_reviews`";
        $this->db->query($query);
        
        return true;
    }


    public function getReviews() {

        $query = "SELECT * FROM " . DB_PREFIX . "store_reviews sr LEFT JOIN customer c ON (c.customer_id=sr.customer_id) where rate>0";
        return $this->db->query($query)->rows;
    }

}