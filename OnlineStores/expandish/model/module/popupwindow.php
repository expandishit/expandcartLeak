<?php
class ModelModulePopupWindow extends Model {
  	public function getSetting($group, $store_id) {
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

    public function updateImpressions ($popup_id) {
      $query = $this->db->query("SELECT * FROM  `oc_popupwindow_stats` WHERE popup_id = '" . (int)$popup_id . "'");
      if($query->num_rows < 1) {
        $this->db->query("INSERT INTO `oc_popupwindow_stats` (popup_id, impressions) VALUES ('" . (int)$popup_id . "', 1)");
      } else
        $this->db->query("UPDATE `oc_popupwindow_stats` SET impressions = impressions + 1 WHERE popup_id = '" . (int)$popup_id . "'");
        
    }

}
?>