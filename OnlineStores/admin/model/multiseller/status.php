<?php

class ModelMultisellerStatus extends Model
{
    //Check MS Installed
    public function is_installed()
    {
        $queryMultiseller = $this->db->query("SELECT extension_id FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

        if($queryMultiseller->num_rows) {
            return true;
        }

        return false;
    }
    //Check disable price
    public function disable_price()
    {
        $multiseller_advanced_status = $this->config->get("multiseller_advanced");
        if($multiseller_advanced_status['status'] && $multiseller_advanced_status['disable_price']) {
            return true;
        }
        return false;
    }
}
