<?php

use ExpandCart\Foundation\Providers\Extension;
/**
*   Model for custom_fees_for_payment_method app.
*   @author Michael
*/
class ModelModuleCustomFeesForPaymentMethod extends Model 
{
    /**
    *   Add an array of settings to the database.
    *
    *   @param array $data the data of the array to be saved in the database.
    *   @param int $store_id the store id.
    *   @param string $group the group name.
    *   @return boolean whether the operation was successful or not.
    */
    public function add_settings($data = array(), $store_id = 0)
    {

        try 
        {

            
            foreach ($data as $key => $data) 
            {

                $sql = "DELETE FROM " . DB_PREFIX . "cffpm WHERE 
                        `store_id` = '" . (int)$store_id . "' AND 
                        `code` = '" . $this->db->escape($key) . "'
                        ;";

                $this->db->query($sql);

                $default_value = 0;

                $fees = $data['fees'] ? $data['fees'] : $default_value;
                $fees .= ':';

                if ( $data['fees_pcg'] && $data['fees_pcg'] == 'yes' )
                {
                    $fees .= 'pcg';
                }

                $show_range_min = $data['show_range_min'] ? $data['show_range_min'] : $default_value;
                $show_range_max = $data['show_range_max'] ? $data['show_range_max'] : $default_value;

                $show_range = $show_range_min . ':'. $show_range_max;

                $fees_range_min = $data['fees_range_min'] ? $data['fees_range_min'] : $default_value;
                $fees_range_max = $data['fees_range_max'] ? $data['fees_range_max'] : $default_value;

                $fees_range = $fees_range_min . ':'. $fees_range_max;

                $catgs_ids  = $data['catgs_ids'] ? serialize($data['catgs_ids']) : NULL;
                $catgs_case = $catgs_ids ? ($data['catgs_case'] ? 'in' : 'notin') : NULL;

                $tax_class_id = $data['tax_class_id'] ? $data['tax_class_id'] : $default_value;


                $sql = "INSERT INTO " . DB_PREFIX . "cffpm SET 
                        `store_id`   = '" . (int)$store_id . "',
                        `code`       = '" . $this->db->escape($key) . "',
                        `fees`       = '" . $this->db->escape($fees) . "',
                        `show_range` = '" . $this->db->escape($show_range) . "',
                        `fees_range` = '" . $this->db->escape($fees_range) . "',
                        `catgs_ids`  = '" . $this->db->escape($catgs_ids) . "',
                        `catgs_case`  = '" . $this->db->escape($catgs_case) . "',
                        `tax_class_id`   = '" . (int)$tax_class_id . "'
                        ;";
                $this->db->query($sql);
            }

            return true;

        } 
        catch (Exception $e) 
        {
            return false;
        }

    }

    /**
    *   Add a setting to the setting table not the cffpm table.
    *
    *   @param string $key the key to insert.
    *   @param string $value the value to insert.
    *   @param int $store_id pretty self-explainatory.
    *   @param string $group defaults to custom_fees_for_payment_method.
    *   @return bool whether successful or not
    */
    public function add_setting_to_setting($key, $value, $store_id = 0, $group = 'cffpm')
    {
        try 
        {

            $sql = " DELETE FROM " . DB_PREFIX . "setting WHERE 
            `key` = '" . $this->db->escape($key) . "' AND 
            `group` = '" . $this->db->escape($group) . "' AND 
            `store_id` = '" . $this->db->escape($store_id) . "'
            ";

            $this->db->query($sql);

            $sql = " INSERT INTO " . DB_PREFIX . "setting SET 
                    `key`='" . $this->db->escape($key) . "', 
                    `value`='" . $this->db->escape($value) . "', 
                    `store_id`='". $this->db->escape($store_id) ."', 
                    `group`='" . $this->db->escape($group) . "'
                    ";

            $this->db->query($sql);

            return true;
        } 
        catch (Exception $e) 
        {
            return false;
        }
    }

    /**
    *   retrieve an existing value from the database
    *
    *   @param string $key the key of the setting to be retrieved
    *   @param int $store_id the store id
    *   @return array $query->row the row of the table.
    *
    */
    public function get_setting($key, $store_id = 0)
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "cffpm WHERE 
                `code` = '" . $this->db->escape($key) . "' AND 
                `store_id` = '" . $this->db->escape($store_id) . "' 
                LIMIT 1";

        $query = $this->db->query($sql);

        return $query->row;
    }

    /**
    *   retrieve an existing setting from the setting table.
    *
    *   @param string $key the key of the setting to be retrieved
    *   @param int $store_id the store id
    *   @param string $group defaults to custom_fees_for_payment_method
    *   @return array $query->row the row of the table.
    *
    */
    public function get_setting_from_setting($key, $store_id = 0, $group = 'cffpm')
    {
        try
        {
            $sql = "SELECT * FROM " . DB_PREFIX . "setting WHERE 
                    `key` = '" . $this->db->escape($key) . "' AND 
                    `store_id` = '" . $this->db->escape($store_id) . "' AND
                    `group` = '" . $this->db->escape($group) . "'
                    LIMIT 1";

            $query = $this->db->query($sql);
            return $query->row;
        }
        catch (Exception $e) 
        {
            return false;
        }
    }

    /**
    *   Update current table to reflect changes in payment methods.
    *   Requested by the boss.
    *
    *   @param int $store_id defaults to 0
    *   @return boolean whether successful or not.
    */
    function update_payment_methods_table($store_id = 0)
    {
        try 
        {
            // get all payment methods
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE
                                      `type` = '" . $this->db->escape('payment') . "'
                                      ;");

            $payment_methods = $query->rows;

            foreach ($payment_methods as $method) {
                try {
                    // try to select current payment method from the cffpm table.
                    $query = $this->get_setting($method['code']);

                    // if current payment does NOT exist. enter an entry for it.
                    if ( empty($query) || !count($query) ) {
                        $sql = "INSERT INTO " . DB_PREFIX . "cffpm SET 
                                `store_id` = '" . $this->db->escape((int)$store_id) . "',
                                `code` = '" . $this->db->escape($method['code']) . "'
                                ;";

                        $this->db->query($sql);
                    }

                // Errors. Yikes.
                } 
                catch (Exception $e) 
                {
                    return false;
                }
            }

        // Errors :(
        } catch(Exception $e) {
            return false;
        }
    }

    /**
    *   Install the required values for the application.
    *
    *   @return boolean whether successful or not.
    */
    public function install($store_id = 0)
    {
        try 
        {

            $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cffpm` 
                    (
                        `id` int(11) PRIMARY KEY AUTO_INCREMENT, 
                        `store_id` int(11) DEFAULT 0, 
                        `code` varchar(255) UNIQUE, 
                        `fees` varchar(255) NULL, 
                        `show_range` varchar(255) NULL, 
                        `fees_range` varchar(255) NULL,
                        `catgs_ids` text NULL,
                        `catgs_case` varchar(5) NULL,
                        `tax_class_id` int(11) NULL
                    )
                    ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";

            $this->db->query("INSERT INTO `" . DB_PREFIX . "extension`(`extension_id`, `type`, `code`) VALUES (NULL, 'total', 'cffpm')");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting`(`setting_id`,`store_id`,`group`,`key`,`value`,`serialized`) VALUES ( NULL,0,'cffpm','cffpm_status', 0, 0), (NULL, 0,'cffpm','cffpm_sort_order',(SELECT COUNT('total') FROM `". DB_PREFIX ."extension` WHERE `type` = 'total'),0)");

            $this->db->query($sql);

            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE
                                      `type` = '" . $this->db->escape('payment') . "'
                                      ;");

            $payment_methods = $query->rows;

            foreach ($payment_methods as $key) {
                $key = $key['code'];
                $value = 0;

                $sql = "INSERT INTO " . DB_PREFIX . "cffpm SET
                        `store_id` = '" . $this->db->escape((int)$store_id) . "', 
                        `code` = '" . $this->db->escape($key) . "'
                        ;";

                $this->db->query($sql);
            }
            

            return true;

        } 
        catch (Exception $e) 
        {
            return false;
        }
    }

    /**
    *   Remove the values from the database.
    *
    *   @return boolean whether successful or not.
    */
    public function uninstall($store_id = 0, $group = 'cffpm')
    {
        try
        {
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "cffpm`;");
            $this->db->query("DELETE FROM `" . DB_PREFIX . "extension` WHERE `type` = 'total' AND `code` = 'cffpm'");
            $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `group` = 'cffpm'");
            return true;
        } 
        catch (Exception $e)
        {
            return false;
        }
    }
    
    /**
    *   Check if the app is installed
    *
    *   @return bool whether the module is installed or not.
    */
    public function is_module_installed()
    {
        return Extension::isInstalled('custom_fees_for_payment_method');
    }
}
