<?php

class ModelModulePaythem extends Model
{
    public function install()
    {
        $this->db->query("CREATE TABLE product_to_paythem (
            product_id int(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            OEM_ID VARCHAR(20) NULL DEFAULT '',
            OEM_Name VARCHAR(255) NULL DEFAULT '',
            OEM_PRODUCT_ID VARCHAR(20) NULL DEFAULT '',
            OEM_PRODUCT_VVSSKU VARCHAR(255) NULL DEFAULT '');");
    }

    public function uninstall()
    {
        $this->db->query("DROP TABLE product_to_paythem");
    }

}