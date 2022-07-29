<?php


class ModelModuleStoreLocations extends Model {
	
	
	public function createModuleTables() {
		
		$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "store_locations (
		  `ID` int(11) NOT NULL AUTO_INCREMENT,
		  `Name` varchar(150) COLLATE utf8_bin NOT NULL,
		  `Details` text COLLATE utf8_bin NOT NULL,
		  `Address` varchar(200) COLLATE utf8_bin NOT NULL,
		  `Email` varchar(100) COLLATE utf8_bin NOT NULL,
		  `Phone` varchar(50) COLLATE utf8_bin NOT NULL,
		  `SpecialOffers` varchar(150) COLLATE utf8_bin NOT NULL,
		  `Timing` varchar(50) COLLATE utf8_bin NOT NULL,
		  `sort_order` int(11) NOT NULL,
		  `lon` double DEFAULT NULL,
		  `lat` double DEFAULT NULL,
		  PRIMARY KEY (`ID`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;
		");
		
		$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "store_locations_images (
		  `location_image_id` int(11) NOT NULL AUTO_INCREMENT,
		  `location_id` int(11) NOT NULL,
		  `image` varchar(255) NOT NULL,
		  `sort_order` int(11) NOT NULL,
		  PRIMARY KEY (`location_image_id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;
		");
			
	}	
	
	public function dropModuleTables() {
		$this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "store_locations");
		$this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "store_locations_images");
	}
	
	public function getList() {
		$query = $this->db->query("select * from " . DB_PREFIX . "store_locations order by sort_order");
		return $query->rows;
	}
	
	public function addLocation($data,$cords) {
			
		$this->db->query("INSERT INTO " . DB_PREFIX . "store_locations( Name, Details, Address, Email, Phone, SpecialOffers, Timing, sort_order, lon, lat) VALUES ('" .  $this->db->escape($data['Name']) . "','" . $this->db->escape($data['Details']) . "','" . $this->db->escape($data['Address']) . "','" . $this->db->escape($data['Email']) . "','" . $this->db->escape($data['Phone']) . "','" . $this->db->escape($data['SpecialOffers']) . "','" . $this->db->escape($data['Timing']) . "','" . $this->db->escape($data['sort_order']) . "','" . $this->db->escape($cords['lon']) . "','" . $this->db->escape($cords['lat']) .  "')" );
		
		$id = $this->db->getLastId();
		
		if(isset($data['location_image'])) {
			foreach($data['location_image'] as $loc_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "store_locations_images (location_id, image, sort_order) values (" . $id . ",'" . $loc_image['image'] . "'," . $this->db->escape($loc_image['sort_order']) . ")");
				
			}
		}
		
	}
	
	public function editLocation($data, $id, $cords) {
		
		if($cords == false) {
		 	$this->db->query("UPDATE " . DB_PREFIX . "store_locations SET Name='" . $this->db->escape($data['Name']) . "', Details= '" . $this->db->escape($data['Details']) . "',  Address='" . $this->db->escape($data['Address']) . "', Email='" . $this->db->escape($data['Email']) . "',Phone='" . $this->db->escape($data['Phone']) . "', SpecialOffers='" .  $this->db->escape($data['SpecialOffers']) . "', Timing='" . $this->db->escape($data['Timing']) . "', sort_order='" . $this->db->escape($data['sort_order']) . "' WHERE ID=" . $id );
		}
		else {
	
			$this->db->query("UPDATE " . DB_PREFIX . "store_locations SET Name='" . $this->db->escape($data['Name']) . "', Details= '" . $this->db->escape($data['Details']) . "',  Address='" . $this->db->escape($data['Address']) . "', Email='" . $this->db->escape($data['Email']) . "',Phone='" . $this->db->escape($data['Phone']) . "', SpecialOffers='" .  $this->db->escape($data['SpecialOffers']) . "', Timing='" . $this->db->escape($data['Timing']) . "', lon='" . $this->db->escape($cords['lon']) . "', lat='" . $this->db->escape($cords['lat']) . "', sort_order='" . $this->db->escape($data['sort_order']) . "' WHERE ID=" . $id );
		}
		
		$this->db->query("delete from " . DB_PREFIX . "store_locations_images where location_id =" . $id );
		
		if(isset($data['location_image'])) {
			foreach($data['location_image'] as $loc_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "store_locations_images (location_id, image, sort_order) values (" . $id . ",'" . $loc_image['image'] . "'," . $this->db->escape($loc_image['sort_order']) . ")");
				
			}
		}
		
	}
	
	public function deleteLocations($data) {
		$this->db->query("delete from " . DB_PREFIX . "store_locations where ID in (" . $data . ")" );
		
		$this->db->query("delete from " . DB_PREFIX . "store_locations_images where location_id =" . $data );
	}
	
	public function getLocation($id) {
		$query = $this->db->query("select * from " . DB_PREFIX . "store_locations where ID =" . $this->db->escape($id));
		return $query->rows[0];
	}
	
	public function getLocationImages($id) {
		$query = $this->db->query("select * from " . DB_PREFIX . "store_locations_images where location_id =" . $this->db->escape($id) . " order by sort_order");
		return $query->rows;
	}
}
?>