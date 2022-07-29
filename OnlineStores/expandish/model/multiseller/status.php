<?php

class ModelMultisellerStatus extends Model
{
    //Check MS Installed
    public function is_installed()
    {
        // $queryMultiseller = $this->db->query("SELECT extension_id FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

        if(\Extension::isInstalled('multiseller')) {
            return true;
        }

        return false;
    }

    //Check MS Messaging Installed
    public function is_messaging_installed()
    {
        $multisellerMessaging = $this->config->get("multiseller_advanced");

        if($multisellerMessaging['status'] && $multisellerMessaging['messaging_seller']) {
            return true;
        }

        return false;
    }

    //Check MS Replace Add To Cart Installed
    public function is_replace_addtocart()
    {
        $multisellerReplcAddtoCart = $this->config->get("multiseller_advanced");

        if($multisellerReplcAddtoCart['status'] && $multisellerReplcAddtoCart['replace_addtocart']) {
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

    //Check MS Replace Contact Form Installed
    public function is_replace_contactform()
    {
        $multisellerReplcAddtoCart = $this->config->get("multiseller_advanced");

        if($multisellerReplcAddtoCart['status'] && $multisellerReplcAddtoCart['replace_contactform']) {
            return true;
        }

        return false;
    }

    //Check Custom Invoice Installed
    public function is_custom_invice_installed($returnVal = false)
    {
        $multisellerCtmInv = $this->config->get("multiseller_advanced");

        if($multisellerCtmInv['status'] && $multisellerCtmInv['custom_invoice']) {
            if($returnVal)
                return $multisellerCtmInv['custom_invoice_ostatus'];
            else
                return true;
        }

        return false;
    }
}
