<?php
class ModelModuleStoreLocations extends Model {
	
	public function createModuleTables() {
	}	

	public function isModuleInstalled()
	{
		$sql = "SHOW TABLES LIKE '%store_locations%'";
		$query = $this->db->query($sql);

		return $query->num_rows ? true : false;
	}
	
	public function getList($start, $limit) {
		if($start == 0 && $limit == 0) {
			$query = $this->db->query("select * from " . DB_PREFIX . "store_locations order by sort_order");
		}
		else {
			$query = $this->db->query("select * from " . DB_PREFIX . "store_locations order by sort_order limit " . $start . "," . $limit);
			
		}
		return $query->rows;
	}
	
	public function getListNearBy($start, $limit, $cords, $distance_unit) {
		
		if( !isset($cords['lat']) || !isset($cords['lon']) ) {		//if geo code failed and there are no cords set, then return the empty set
			return array();
		}
		
		if($start == 0 && $limit == 0) {
			$query = $this->db->query("SELECT *, ( 3959 * acos( cos( radians(".$this->db->escape($cords['lat']).") ) * cos( radians( lat ) ) * cos( radians( lon ) - radians(".$this->db->escape($cords['lon']).") ) + sin( radians(".$this->db->escape($cords['lat']).") ) * sin( radians( lat ) ) ) ) AS distance from " . DB_PREFIX . "store_locations having distance <= " . $distance_unit . " order by distance");
		}
		else {
			$query = $this->db->query("SELECT *, ( 3959 * acos( cos( radians(".$this->db->escape($cords['lat']).") ) * cos( radians( lat ) ) * cos( radians( lon ) - radians(".$this->db->escape($cords['lon']).") ) + sin( radians(".$this->db->escape($cords['lat']).") ) * sin( radians( lat ) ) ) ) AS distance from " . DB_PREFIX . "store_locations having distance <= " . $distance_unit . " order by distance limit " . $start . "," . $limit);
			
		}
		return $query->rows;
	}
	
	public function getLocation($id) {
		$query = $this->db->query("select * from " . DB_PREFIX . "store_locations where ID =" . $this->db->escape($id));
		return $query->rows[0];
	}
	
	public function getLocationImages($id) {
		$query = $this->db->query("select * from " . DB_PREFIX . "store_locations_images where location_id =" . $this->db->escape($id) . " order by sort_order");
		return $query->rows;
	}
	
	public function searchAddress($address) {
		$query = $this->db->query("select * from " . DB_PREFIX . "store_locations where Name like '%" . $this->db->escape($address) . "%' or Address like '%" . $this->db->escape($address) . "%'");
		if(count($query->rows) > 0) {
			return $query->rows;
		}else {
			return;
		}
		
	}
	
	public function getTotalLocations() {
		$query = $this->db->query("select count(*) as total from " . DB_PREFIX . "store_locations");
		return $query->rows[0]['total'];
	}
	
}

?>