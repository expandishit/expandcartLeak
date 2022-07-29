<?php
class ModelEcflashsaleFlashsale extends Model {
	public function getFlashsale($ecflashsale_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'ecflashsale_id=" . (int)$ecflashsale_id . "') AS keyword FROM " . DB_PREFIX . "ecflashsale f LEFT JOIN " . DB_PREFIX . "ecflashsale_description fd2 ON (f.ecflashsale_id = fd2.ecflashsale_id) LEFT JOIN " . DB_PREFIX . "ecflashsale_to_store f2s ON (f.ecflashsale_id = f2s.ecflashsale_id) WHERE f.ecflashsale_id = '" . (int)$ecflashsale_id . "' AND fd2.language_id = '" . (int)$this->config->get('config_language_id') . "' AND f2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND f.status = '1'");
		
		return $query->row;
	}

	public function getFlashsaleByProduct( $product_id = 0){
		if($product_id){
			$query = $this->db->query("SELECT `ecflashsale_id` FROM ".DB_PREFIX."product_special WHERE `product_id`=".(int)$product_id);

			if($query->num_rows){
				$ecflashsale_id = $query->row['ecflashsale_id'];
				if($ecflashsale_id){
					return $this->getFlashsale( $ecflashsale_id );
				}
			}
		}
		return false;
	}
	public function getCategrories($product_id = 0){
		if($product_id){
			$query = $this->db->query("SELECT category_id FROM ".DB_PREFIX."product_to_category WHERE product_id=".(int)$product_id);

			if($query->num_rows > 0){
				return $query->rows;
			}
		}
		return false;
	}

	public function checkProductFlashSale($product_id = 0, $flashsale = array()){
		$existed = false;
		if(empty($flashsale)){
			$flashsale = $this->getFlashsaleByProduct( $product_id );
		}
		if(!empty($product_id) && $flashsale){

			if( $flashsale['source_from'] == 'category'){
					$categories = $this->getCategrories($product_id);
					$config_categories = $flashsale['category'];
					$config_categories = is_array($config_categories)?$config_categories:explode(",", $config_categories);

					if(!empty($categories)){
						foreach($categories as $category){
							if(is_array($config_categories) && in_array($category['category_id'], $config_categories)){
								$existed = true;
								break;
							}else{
								if($category['category_id'] == $config_categories)
								{
									$existed = true;
									break;
								}
							}
						}
					}
				}elseif( $flashsale['source_from'] == 'product'){
					$products = is_array($flashsale['products'])?$flashsale['products']:explode(",",$flashsale['products']);
					if(!empty($products)){
						$tmp = array();
						foreach($products as $product){
							$tmp[] = is_array($product)?(int)$product['product_id']:(int)$product;
						}
						if(in_array($product_id, $tmp))
							$existed = true;
					}
				}

		}
		return $existed;
	}

	public function getFlashsales($data = array()) {
		$sql = "SELECT DISTINCT f.*,fd.name, fd.description, fd.meta_description, fd.meta_keyword, fd.tag FROM " . DB_PREFIX . "ecflashsale f
				LEFT JOIN ".DB_PREFIX."ecflashsale_description fd ON f.ecflashsale_id = fd.ecflashsale_id
				LEFT JOIN ".DB_PREFIX."ecflashsale_to_store f2s ON f.ecflashsale_id = f2s.ecflashsale_id
			";

		$sql .= " WHERE fd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND f2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND f.status = '1' ";
		
		if (!empty($data['filter_name'])) {
			$sql .= " AND fd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (isset($data['filter_featured']) && $data['filter_featured'] != "") {
			$sql .= " AND f.featured = " . (int)$data['filter_featured'];
		}


		$sort_data = array(
			'featured',
			'fd.name',
			'source_from',
			'status',
			'date_start',
			'date_end',
			'added_date',
			'date_modified',
			'sort_order',
			'hits',
			'ecflashsale_id'
		);	
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY f.sort_order";	
		}
		
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}
	
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}				

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
		
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);
	
		return $query->rows;
	}

	public function getTotalFlashsales($data = array()) {
		$sql = "SELECT COUNT(DISTINCT f.ecflashsale_id) AS total FROM " . DB_PREFIX . "ecflashsale f
				LEFT JOIN ".DB_PREFIX."ecflashsale_description fd ON f.ecflashsale_id = fd.ecflashsale_id
				LEFT JOIN ".DB_PREFIX."ecflashsale_to_store f2s ON f.ecflashsale_id = f2s.ecflashsale_id
			";
		$sql .= " WHERE fd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND f2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND f.status = '1' ";

		if (!empty($data['filter_name'])) {
			$sql .= " AND fd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_featured'])) {
			$sql .= " AND f.featured = " . (int)$data['filter_featured'];
		}

		$query = $this->db->query($sql);
		
		return $query->row['total'];
	}

	public function updateHits($ecflashsale_id){
		return $this->db->query("UPDATE ".DB_PREFIX."ecflashsale SET hits=hits+1 WHERE ecflashsale_id=".(int)$ecflashsale_id);
	}

	public function dateDiff( $date ){
		 $todays_date = date("Y-m-d"); 
		 $today = strtotime($todays_date); 
		 $expiration_date = strtotime($date);
		 $diff = $expiration_date - $today;
		 return $diff;
	}

}