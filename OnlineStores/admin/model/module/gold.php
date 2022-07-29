<?php

class ModelModuleGold extends Model
{
    /**
     * get Calibers list.
     *
     * @return array
     */
    public function getCalibers()
    {
        return $this->db->query("SELECT * FROM `" .DB_PREFIX. "gold`")->rows;
    }

    /**
     * get Caliber
     *
     * @return array
     */
    public function getProductCaliber($product_id)
    {
        return $this->db->query("SELECT * FROM `" .DB_PREFIX. "gold_to_product` gtp WHERE gtp.product_id = $product_id")->rows[0] ?? false;
    }

    /**
     * insert/update Calibers list.
     *
     * @return array
     */
    public function insertUpdate($data)
    {
        foreach ($data as $caliber){
            if($caliber['caliber'] && $caliber['price'] && $caliber['manuf_price']){
                if($caliber['id']){

                    //Check of price changes to update related product ids
                    $old_price = $this->db->query("SELECT price FROM `" .DB_PREFIX. "gold` gtp WHERE gtp.id =". $caliber['id'])->rows[0]['price'] ?? false;
                    if($old_price != $caliber['price']){
                        $products_to_caliber = $this->db->query("SELECT * FROM `" .DB_PREFIX. "gold_to_product` gtp WHERE gtp.gold_id =". $caliber['id'])->rows ?? [];
                        if(count($products_to_caliber)){
                            foreach ($products_to_caliber as $product_caliber){
                                // for now there are two types for manuf_on  (gram ,  product)
                                if($product_caliber['manuf_on'] == 'gram'){
                                    $price = ((float)$caliber['price'] * (float)$product_caliber['weight']) +  ((float)$product_caliber['manuf_price'] * (float)$product_caliber['weight']);
                                }else if($product_caliber['manuf_on'] == 'product') {
                                    $price = ((float)$caliber['price'] * (float)$product_caliber['weight']) +  (float)$product_caliber['manuf_price'];
                                }
                                $this->db->query("UPDATE `" .DB_PREFIX. "product` SET price='".$price."' WHERE product_id=".$product_caliber['product_id']);
                            }
                        }
                    }
                    //////////////////////////////////////////////////////

                    $this->db->query("UPDATE `" .DB_PREFIX. "gold` SET caliber='".$caliber['caliber']."', price='".$caliber['price']."', manuf_price='".$caliber['manuf_price']."' WHERE id=".$caliber['id']);
                }else{
                    $this->db->query("INSERT INTO `" .DB_PREFIX. "gold` SET caliber='".$caliber['caliber']."', price='".$caliber['price']."', manuf_price='".$caliber['manuf_price']."'");
                }
            }
        }
        return true;
    }

    /**
     * Delete Caliber
     *
     * @return array
     */
    public function deleteCaliber($id)
    {
        if($this->db->query("SELECT id FROM `" .DB_PREFIX. "gold_to_product` WHERE gold_id = $id")->num_rows > 0)
            return 2;

        return $this->db->query("DELETE FROM `" .DB_PREFIX. "gold` WHERE id = $id") ? 1 : 0;
    }

    /**
     * get settings.
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->config->get('gold');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1 && \Extension::isInstalled('gold')) {
            return true;
        }

        return false;
    }

    /**
    *   Install the required values for the application.
    *
    *   @return boolean whether successful or not.
    */
    public function install()
    {
        $query = "CREATE TABLE IF NOT EXISTS `gold` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `caliber` varchar(191) NOT NULL,
                  `price` decimal(15,4) NOT NULL DEFAULT 0.0000,
                  `manuf_on` varchar(50) NOT NULL DEFAULT 'product',
                  `manuf_price` decimal(15,4) NOT NULL DEFAULT 0.0000,
                  PRIMARY KEY (`id`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        $this->db->query($query);

        $query = "CREATE TABLE IF NOT EXISTS `gold_to_product` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `gold_id` int(11) NOT NULL,
                  `product_id` int(11) NOT NULL,
                  `manuf_on` varchar(50) NOT NULL,
                  `manuf_price` decimal(15,4) DEFAULT 0.0000,
                  `weight` decimal(15,8) NOT NULL DEFAULT 0.00000000,
                  PRIMARY KEY (`id`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        $this->db->query($query);
    }

    /**
    *   Remove the values from the database.
    *
    *   @return boolean whether successful or not.
    */
    public function uninstall()
    {
        $this->db->query("DROP TABLE IF EXISTS `" .DB_PREFIX. "gold`");
        $this->db->query("DROP TABLE IF EXISTS `" .DB_PREFIX. "gold_to_product`");
    }
}
