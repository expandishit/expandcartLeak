<?php 
class ModelModulePopupWindow extends Model {

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
  	
  	public function getPopupWindow($popup_id, $group, $store_id = 0) {
	    $data = array(); 
	    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `group` = '" . $this->db->escape($group) . "'");
	    foreach ($query->rows as $result) {
	      if ($result['serialized'] && $result['key'] == $group) {
	        $data = unserialize($result['value']);
	      } 
	    } 

	    return $data[$group][$popup_id];
	}

	public function addSetting($group, $key, $data, $store_id = 0) {
	    // $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `group` = '" . $this->db->escape($group) . "'");
		$defaultData = array(
		                    'id' => '',
		                    'Enabled' => 'no',
		                    'method' => 0,
		                    'event' => 0,
		                    'url' => '',
		                    'excluded_urls' => '',
		                    'css_selector' => '',
		                    'time_interval' => 0,
		                    'start_time' => '00:00',
		                    'end_time' => '00:00',
		                    'scroll_percentage' => '',
		                    'repeat' => 0,
		                    'days' => 1,
		                    'date_interval' => 0,
		                    'start_date' => '',
		                    'end_date' => '',
		                    'seconds' => 0,
		                    'prevent_closing' => 0,
		                    'content' => '',
		                    'width' => '500',
		                    'height' => '500',
		                    'auto_resize' => false,
		                    'aspect_ratio' => false,
		                    'animation' => 'bounceIn',
		                    'customerGroups' =>''
						);
	    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `group` = '" . $this->db->escape($group) . "' AND `key` = '" . $this->db->escape($key) . "' limit 1");
	    // First time insert
	    if(count($query->rows) == 0){
	    	foreach ($data as $k => $value) {
		      if (!is_array($value)) {
		        $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `group` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($k) . "', `value` = '" . $this->db->escape($value) . "'");
		      } else {
		      	if($k == 'ids'){
		      		$k = $key;
		      		$defaultData['id'] = 1;
		      		$value = array( $k =>  array('1' => $defaultData));
		      	}

		        $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `group` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($k) . "', `value` = '" . $this->db->escape(serialize($value)) . "', serialized = '1'");
		      }
		    }
		// Update and delete
	    }else{
	    	$arrayData = unserialize($query->row['value']);
	    	$tempArr = array();
	    	foreach ($data['ids'] as $k => $val) {
	    	   if($arrayData[$key][$val]){
	    	   	 $tempArr[$key][$val] = $arrayData[$key][$val];
	    	   }else{
	    	   	 $defaultData['id'] = $val;
	    	   	 $tempArr[$key][$val]  = $defaultData;
	    	   }
		    }
	    	$this->db->query("UPDATE " . DB_PREFIX . "setting SET value='" . $this->db->escape(serialize($tempArr)) . "' WHERE store_id = '" . (int)$store_id . "' AND `group` = '" . $this->db->escape($group) . "' AND `key` = '" . $this->db->escape($key) . "'");
	    }
	}
  	public function editSetting($group, $key, $data, $popup_id, $store_id = 0) {
	    // $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `group` = '" . $this->db->escape($group) . "'");
	    // foreach ($data as $key => $value) {
	    //   if (!is_array($value)) {
	    //     $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `group` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
	    //   } else {
	    //     $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `group` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(serialize($value)) . "', serialized = '1'");
	    //   }
	    // }

	    $arrayData = array(); 
	    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `group` = '" . $this->db->escape($group) . "'");
	    foreach ($query->rows as $result) {
	      if ($result['serialized'] && $result['key'] == $key) {
	        $arrayData = unserialize($result['value']);
	      } 
	    }

	    $arrayData[$key][$popup_id] = $data;

	    $this->db->query("UPDATE " . DB_PREFIX . "setting SET value='" . $this->db->escape(serialize($arrayData)) . "' WHERE store_id = '" . (int)$store_id . "' AND `group` = '" . $this->db->escape($group) . "' AND `key` = '" . $this->db->escape($key) . "'");
	}

	public function getImpressions() {
		$query = $this->db->query("SELECT * FROM oc_popupwindow_stats");

		return $query->rows;
	}

	public function deletePopupID($popup_id) {
		$this->db->query("DELETE FROM oc_popupwindow_stats WHERE popup_id = '" . (int)$popup_id . "'");
	}
	
  	public function install() {
	  $this->db->query("CREATE TABLE IF NOT EXISTS `oc_popupwindow_stats` (
		`popup_id` int(11) NOT NULL ,
		`impressions` int(11) NOT NULL DEFAULT '0',
		PRIMARY KEY (`popup_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");
  	} 
  
  	public function uninstall() {
  		$this->db->query("DROP TABLE IF EXISTS `oc_popupwindow_stats`");
		// Uninstall Code
  	}
	
  }
?>