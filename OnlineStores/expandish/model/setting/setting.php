<?php 
class ModelSettingSetting extends Model {
	public function getSetting($group, $store_id = 0) {
		$data = array(); 
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `group` = '" . $this->db->escape($group) . "'");
		
		foreach ($query->rows as $result) {
			if (!$result['serialized']) {
				$data[$result['key']] = $result['value'];
			} else {
				$data[$result['key']] = unserialize($result['value']);
			}
		}

		return $data;
	}

	/**
	 * Get stock status by id
	 *
	 * @author MohamedHassanWD <hassan.mohamed.sf@gmail.com>
	 * @copyright 2020 ExpandCart
	 * 
	 * @param integer $stock_status_id
	 * @return array|void
	 */
	public function getStockStatus($stock_status_id=0){
		if($stock_status_id){
			$sql = 'SELECT * FROM '.DB_PREFIX.'stock_status WHERE stock_status_id='.(int) $this->db->escape($stock_status_id);

			$query = $this->db->query($sql);

			return $query->row;
		}
    }
    
    public function insertUpdateSetting($group, $data, $store_id = 0)
    {
        foreach ($data as $key => $value) {
            $query = $this->db->query("SELECT 1 FROM " . DB_PREFIX . "setting WHERE `group` = '" . $this->db->escape($group) . "' AND `key` = '" . $this->db->escape($key) . "' AND store_id = '" . (int)$store_id . "'");

            $serialized = 0;
            if (is_array($value)) {
                $value = serialize($value);
                $serialized = 1;
            }

            if ($query->num_rows > 0) {
                $this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape($value) . "', serialized = '" . $serialized . "' WHERE `group` = '" . $this->db->escape($group) . "' AND `key` = '" . $this->db->escape($key) . "' AND store_id = '" . (int)$store_id . "'");
            } else {
                $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `group` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "', serialized = '" . $serialized . "'");
            }
        }
    }

	public function deleteSetting($group, $store_id = 0) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `group` = '" . $this->db->escape($group) . "'");
	}
	public function editSettingValue($group = '', $key = '', $value = '', $store_id = 0) {
		if (!is_array($value)) {
			$this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape($value) . "' WHERE `group` = '" . $this->db->escape($group) . "' AND `key` = '" . $this->db->escape($key) . "' AND store_id = '" . (int)$store_id . "'");
		} else {
			$this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape(serialize($value)) . "' WHERE `group` = '" . $this->db->escape($group) . "' AND `key` = '" . $this->db->escape($key) . "' AND store_id = '" . (int)$store_id . "' AND serialized = '1'");
		}
	}

	
   
}
?>
