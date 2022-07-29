<?php
/**
*   Auto Meta Tags Model File
*   @author Michael
*/
class ModelModuleAutoMetaTags extends Model 
{
    /**
    *   Add a new value to the database
    *
    *   @param string $key the key of the setting to be added
    *   @param string $value the value of the setting to be added
    *   @param int $store_id the store id
    *   @param string $group the group of the setting to be added
    *   @return boolean whether the operation was successful or not.   
    *
    */
    public function add_setting($key, $value, $store_id = 0, $group = 'auto_meta_tags')
    {
        $sql = "DELETE FROM " . DB_PREFIX . "setting WHERE `store_id` = '" . (int)$store_id . "' AND `group` = '" . $this->db->escape($group) . "' AND `key` = '" . $this->db->escape($key) . "'";

        $this->db->query($sql);

        $sql = "INSERT INTO " . DB_PREFIX . "setting SET `store_id` = '" . (int)$store_id . "', `group` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'";

        try {
            $this->db->query($sql);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
    *   retrieve an existing value from the database
    *
    *   @param string $key the key of the setting to be retrieved
    *   @param int $store_id the store id
    *   @param string $group the group of the setting to be retrieved
    *   @return string $value the value of the key.
    *
    */
    public function get_setting($key, $store_id = 0, $group = 'auto_meta_tags')
    {
        $sql = "SELECT `value` FROM " . DB_PREFIX . "setting WHERE `key` = '".$this->db->escape($key)."' AND `store_id` = '" . $this->db->escape($store_id) . "' AND `group` = '".$this->db->escape($group)."' LIMIT 1";

        $query = $this->db->query($sql);

        return $query->row['value'];
    }
}
