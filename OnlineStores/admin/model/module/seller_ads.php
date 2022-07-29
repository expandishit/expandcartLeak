<?php

class ModelModuleSellerAds extends Model
{
    public function install()
    {

        $query = "CREATE TABLE IF NOT EXISTS `" .DB_PREFIX. "seller_ads_pacakages` (
            `id` int(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `type` varchar(255) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->db->query($query);

        $query = "CREATE TABLE IF NOT EXISTS `" .DB_PREFIX. "seller_ads_seller_ads` (
                  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                  `seller_ads_package_id` int(11) NOT NULL,
                  `seller_id` int(11) NOT NULL,
                  `start_date` date NOT NULL,
                  `expire_date` date NOT NULL,
                  `title` varchar(255) NULL,
                  `link` varchar(255) NOT NULL,
                  `image` varchar(255) NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->db->query($query);

        $query = "INSERT INTO `" .DB_PREFIX. "seller_ads_pacakages` (`type`) VALUES ('square')";
        $this->db->query($query);

        $query = "INSERT INTO `" .DB_PREFIX. "seller_ads_pacakages` (`type`) VALUES ('banner')";
        $this->db->query($query);

    }

    public function uninstall()
    {
        $query = "DROP TABLE IF EXISTS `" .DB_PREFIX. "seller_ads_pacakages`";
        $this->db->query($query);

        $query = "DROP TABLE IF EXISTS `" .DB_PREFIX. "seller_ads_seller_ads`";
        $this->db->query($query);
    }


    public function getSubscribers() {

        $query = "SELECT *, DATEDIFF(sa.expire_date, NOW()) ad_remaining_days FROM " . DB_PREFIX . "seller_ads_seller_ads sa LEFT JOIN seller_ads_pacakages sap ON (sap.id=sa.seller_ads_package_id) LEFT JOIN ms_seller ms ON (sa.seller_id=ms.seller_id) LEFT JOIN customer c ON (c.customer_id=ms.seller_id)";
        return $this->db->query($query)->rows;
    }

}