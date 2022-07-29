<?php
/**
*   Model class for Custom Fees For Payment Method Application
*   
*   @author Michael
*/
class ModelModuleCustomFeesForPaymentMethod extends Model 
{
    /**
    *   Check if the app is installed
    *
    *   @return bool whether the module is installed or not.
    */
    public function is_module_installed()
    {
        try {
            $sql = "SHOW TABLES LIKE 'cffpm'";
            $query = $this->db->query($sql);
            if (!empty($query->row) && count($query->rows) > 0) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
    *   Get all settings from cffpm
    *
    *   @return array $settings all rows in cffpm.
    */
    public function get_all_settings()
    {
        try {
            $sql = "SELECT * FROM " . DB_PREFIX . "cffpm;";
            $query = $this->db->query($sql);

            return $query->rows;

        } catch (Exception $e) {
            return array();
        }
    }

    /**
    *   Get setting from cffpm using payment method code.
    *
    *   @param string $code the code of the payment method
    *   @param int $store_id defaults to 0
    *   @return array $data the data of the payment method
    */
    public function get_setting($code, $store_id = 0)
    {
        try {
            $sql = "SELECT * FROM " . DB_PREFIX ."cffpm WHERE 
            `code` = '" . $this->db->escape($code) . "' AND
            `store_id` = ' " . $this->db->escape($store_id) . "'
            LIMIT 1;";

            $query = $this->db->query($sql);
            return $query->row;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
    *   Get setting from the setting table.
    *
    *   @param string $key the key to select with.
    *   @param int $store_id defaults to 0.
    *   @param string $group defaults to 'custom_fees_for_payment_method'.
    *   @return array $data the data of the row in an array format.
    */
    public function get_setting_from_setting($key, $store_id = 0, $group = 'cffpm')
    {
        try {
            
            $sql = "SELECT * FROM " . DB_PREFIX . "setting WHERE
            `key` = '" . $this->db->escape($key) . "' AND
            `group` = '" . $this->db->escape($group) . "' AND
            `store_id` = '" . $this->db->escape($store_id) . "'
            LIMIT 1;";

            $query = $this->db->query($sql);
            
            return $query->row;
        } catch (Exception $e) {
            return false;
        }
    }
}
