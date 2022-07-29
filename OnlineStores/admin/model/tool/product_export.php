<?php
class ModelToolProductexport extends Model {
	
	private $import_files_tb = 'import_files';
	
	public function getmaxproducts(){
		$query = $this->db->query("SELECT product_id FROM ".DB_PREFIX."product ORDER BY product_id DESC LIMIT 0, 1");
		if(!empty($query->row['product_id'])){
			return $query->row['product_id'];
		}else{
			return 0;
		}
	}
	
	public function getminiproducts(){
		$query = $this->db->query("SELECT product_id FROM ".DB_PREFIX."product ORDER BY product_id ASC LIMIT 0, 1");
		if(!empty($query->row['product_id'])){
			return $query->row['product_id'];
		}else{
			return 0;
		}
	}
	
	public function getProducts($data = array()){

		if($data['filter_language_id'] == "all"){
			$sql = "SELECT p.*, pd.*,l.code as `language` FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
			join `language` as l on l.language_id = pd.language_id"; 
		}
		else{
			$language_id = (int)$data['filter_language_id'];
			$sql = "SELECT *,(SELECT code FROM ".DB_PREFIX."language WHERE language_id = ".$language_id.") as language FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";	
		}
		
		if(isset($data['filter_store'])){
		 $sql .=" LEFT JOIN ".DB_PREFIX."product_to_store p2s ON(p.product_id=p2s.product_id)";
		}
		
		if(isset($data['filter_categories'])){
		 $sql .=" LEFT JOIN ".DB_PREFIX."product_to_category p2c ON(p.product_id=p2c.product_id)";
		}
		
		if($data['filter_language_id'] != "all"){
			$sql .= " WHERE pd.language_id = '" . (int)$data['filter_language_id'] . "'";
		}
		else{
			$sql .=" WHERE 1=1 ";
		}
		if(isset($data['filter_store'])){
			$sql .="  AND p2s.store_id = '".$data['filter_store']."'";
		}
		
		if(isset($data['filter_categories'])){
			$sql .="  AND p2c.category_id = '".$data['filter_categories']."'";
		}

		if (!empty($data['filter_name'])){
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}

		if (isset($data['filter_price_form']) && isset($data['filter_price_to'])) {
            $sql .= " AND p.price BETWEEN '" . $this->db->escape($data['filter_price_form']) . "' AND '".$this->db->escape($data['filter_price_to'])."'";
		}
		
		if (isset($data['filter_quantity_form']) && isset($data['filter_quantity_to'])){
            $sql .= " AND p.quantity BETWEEN '" . $this->db->escape($data['filter_quantity_form']) . "' AND '".$this->db->escape($data['filter_quantity_to'])."'";
		}


        if (isset($data['filter_status']) && $data['filter_status'] != '') {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}
		
		if (isset($data['filter_manufacturer']) && !is_null($data['filter_manufacturer'])) {
			$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer'] . "'";
		}
		
		if (isset($data['filter_stock_status']) && !is_null($data['filter_stock_status'])) {
			$sql .= " AND p.stock_status_id = '" . (int)$data['filter_stock_status'] . "'";
		}
		
		if(!empty($data['filter_product_id']) && !empty($data['filter_endproduct_id'])){
		    $sql .=" AND p.product_id BETWEEN '".(int)$data['filter_product_id']."' AND '".(int)$data['filter_endproduct_id']."'";
		}
		
		if(!empty($data['filter_product_id']) && empty($data['filter_endproduct_id'])){
		    $sql .=" AND p.product_id = '".(int)$data['filter_product_id']."'";
		}
		
		if(empty($data['filter_product_id']) && !empty($data['filter_endproduct_id'])){
		    $sql .=" AND p.product_id = '".(int)$data['filter_endproduct_id']."'";
		}
		
		if($data['filter_language_id'] != "all"){
			$sql .= " GROUP BY p.product_id";
		}
		
		$sort_data = array(
			'pd.name',
			'p.model',
			'p.price',
			'p.quantity',
			'p.status',
			'p.sort_order'
		);

		if(isset($data['sort']) && in_array($data['sort'], $sort_data)){
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY p.product_id, p.model, pd.language_id";
		}
		
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}		

			if ( isset($data['limit']) && $data['limit'] < 1) {
				$data['limit'] = 20;
			}

			if($data['filter_language_id'] == "all"){
				$count = $this->getLanguagesCount();
				$data['limit'] = $data['limit'] * $count;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		
		$query = $this->db->query($sql);

		return $query->rows;
	}
	
	public function getLanguagesCount()
	{
		# code...
		$sql = [];
		$sql[] = "SELECT count(*) as count from language";

		$data = $this->db->query(implode(" ",$sql));
		return $data->row['count'];

	}
	public function getKeyword($product_id){
		$query = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product_id . "'");
		return (isset($query->row['keyword']) ? $query->row['keyword'] :'');
	}
	
	public function getLengthClass($length_class_id,$language_id){
		$sql = "SELECT title FROM " . DB_PREFIX . "length_class_description  WHERE length_class_id = '" . (int)$length_class_id . "'";
		if($language_id != "all"){
			$sql .= " AND language_id = '" . (int)$language_id . "'";
		}
		$query = $this->db->query($sql);
		return (isset($query->row['title']) ? $query->row['title'] :'');
	}
	
	public function getWeightClass($weight_class_id,$language_id){
		$sql = "SELECT title FROM " . DB_PREFIX . "weight_class_description  WHERE weight_class_id = '" . (int)$weight_class_id . "' ";
		if($language_id != "all"){
			$sql .= "AND language_id = '" . (int)$language_id . "'";
		}
		$query = $this->db->query($sql);
		return (isset($query->row['title']) ? $query->row['title'] :'');
	}
	
	public function getStockstatus($stock_status_id,$language_id){
		$sql = "SELECT name FROM " . DB_PREFIX . "stock_status  WHERE stock_status_id = '" . (int)$stock_status_id . "'";
		if($language_id != "all"){
			$sql .= " AND language_id = '" . (int)$language_id . "'";
		} 
		$query = $this->db->query($sql);
		return (isset($query->row['name']) ? $query->row['name'] :'');
	}
	
	public function getTaxClass($tax_class_id){
		$query = $this->db->query("SELECT title FROM " . DB_PREFIX . "tax_class WHERE tax_class_id = '" . (int)$tax_class_id . "'");
		return (isset($query->row['title']) ? $query->row['title'] :'');
	}
	
	public function getManufacturer($manufacturer_id){
		$query = $this->db->query("SELECT name FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
		return (isset($query->row['name']) ? $query->row['name'] :'');
	}
	
	public function getProductAttributes($product_id,$language_id){
		$product_attribute_data = array();

		$product_attribute_query = $this->db->query("SELECT attribute_id,text FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND language_id = '".$language_id."' GROUP BY attribute_id");
		
		foreach($product_attribute_query->rows as $product_attribute){
			$query = $this->db->query("SELECT ad.name as attribute,(SELECT name FROM ".DB_PREFIX."attribute_group_description WHERE attribute_group_id = a.attribute_group_id LIMIT 0,1) as attribute_group FROM ".DB_PREFIX."attribute a LEFT JOIN ".DB_PREFIX."attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE a.attribute_id = '".(int)$product_attribute['attribute_id']."'");
			if($query->row){
				$product_attribute_data[]= $query->row['attribute_group'].'::'.$query->row['attribute'].'::'.$product_attribute['text'];
			}
		}
		return $product_attribute_data;
	}
	
	public function getProductOptions($product_id,$language_id){
		$product_option_data = array();

		$product_option_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_option` po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$language_id . "'");
		
		foreach ($product_option_query->rows as $product_option){
		if($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image'){
			$product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value WHERE product_option_id = '" . (int)$product_option['product_option_id'] . "'");
				foreach($product_option_value_query->rows as $product_option_value){
					$option_descriptions = $this->db->query("SELECT name FROM ".DB_PREFIX."option_value_description WHERE option_value_id = '".(int)$product_option_value['option_value_id']."' AND language_id = '".(int)$language_id."'");
					
					$option_value  = (isset($option_descriptions->row['name']) ? $option_descriptions->row['name'] : '');
					
					$product_option_data[]= html_entity_decode($product_option['name']).'::'.$product_option['type'].'::'.$option_value.'~'.$product_option_value['quantity'].'~'.$product_option_value['subtract'].'~'.$product_option_value['price'].'~'.$product_option_value['points'].'~'.$product_option_value['weight'];
				}
			}else{
				$product_option_data[]= html_entity_decode($product_option['name']).'::'.$product_option['type'].'::'.$product_option['option_value'];
			}
		}
		return $product_option_data;
	}

    // get  product 's categories
    public function getCategory($category_id, $language_id) {
        $query = $this->db->query("SELECT DISTINCT *, (SELECT GROUP_CONCAT(cd1.name ORDER BY level SEPARATOR ' &gt; ') FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id AND cp.category_id != cp.path_id) WHERE cp.category_id = c.category_id AND cd1.language_id = '" . (int)$language_id . "' GROUP BY cp.category_id) AS path, (SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'category_id=" . (int)$category_id . "') AS keyword FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (c.category_id = cd2.category_id) WHERE c.category_id = '" . (int)$category_id . "' AND cd2.language_id = '" . (int)$language_id. "'");

        return $query->row;
    }

	public function getNewProductOptions($option_format_column,$product_id,$language_id,$selected_languages){
		$product_option_data = array();

		if($selected_languages == "all"){
			$default_language_id = $this->config->get('config_language_id');
			$product_option_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_option` po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" .  (int)$language_id  . "'");
		}
		else{
			$product_option_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_option` po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$language_id . "'");
		}
		$option_counter = 0;
		foreach ($product_option_query->rows as $product_option){
			if($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image'){
				$product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value WHERE product_option_id = '" . (int)$product_option['product_option_id'] . "'");

				$product_options_arr = [];
				
				foreach($product_option_value_query->rows as $product_option_value){
					$option_descriptions = $this->db->query("SELECT name FROM ".DB_PREFIX."option_value_description WHERE option_value_id = '".(int)$product_option_value['option_value_id']."' AND language_id = '".(int)$language_id."'");
					
					if(!isset($option_descriptions->row['name'])){
						continue;
					}
					$option_value = $option_descriptions->row['name'];
					if($option_format_column == 1){
						$product_options_arr[] = $option_value.'~'.$product_option_value['quantity'].'~'.$product_option_value['subtract'].'~'.$product_option_value['price'].'~'.$product_option_value['points'].'~'.$product_option_value['weight'].'~';
					}
					else{
						$product_option_data[$option_counter]['name']=html_entity_decode($product_option['name']);
						$product_option_data[$option_counter]['values'][]=$option_value .'~'.$product_option_value['quantity'].'~'.$product_option_value['subtract'].'~'.$product_option_value['price'].'~'.$product_option_value['points'].'~'.$product_option_value['weight'];
					}
				}
				if($option_format_column == 1){
					$product_option_data[] = html_entity_decode($product_option['name']).':'.implode(",",$product_options_arr);
				}
			}
			else{
				if($option_format_column == 1){
					$product_option_data[]= html_entity_decode($product_option['name']).':'.$product_option['option_value'];
				}
				else{
					$product_option_data[$option_counter]['name']=html_entity_decode($product_option['name']);
					$product_option_data[$option_counter]['values'][]=$product_option['option_value'];	
				}	
			}
			$option_counter++;
		}

		return $product_option_data;
	}

	public function getProductOptionsrequired($product_id,$language_id){
		$product_option_data = array();

		$product_option_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_option` po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$language_id . "'");
		foreach ($product_option_query->rows as $product_option){
			$product_option_data[] = $product_option['name'].':'.$product_option['type'].'='.$product_option['required'];
		}
		return $product_option_data;
	}
	
	/**Product Review Start**/
	public function getproductreview($data){
		$sql = "SELECT * FROM " . DB_PREFIX . "review r LEFT JOIN ".DB_PREFIX."product p ON(r.product_id = p.product_id) LEFT JOIN ".DB_PREFIX."product_description pd ON(p.product_id = pd.product_id) WHERE review_id > 0";
		
		if(isset($data['filter_status'])){
			$sql .= " AND r.status = '".(int)$data['filter_status']."'";
		}
		
		if(isset($data['filter_name'])){
			$sql .=" AND pd.name LIKE '%".$data['filter_name']."%'";
		}
		
		if(!empty($data['filter_product_id']) && !empty($data['filter_endproduct_id'])){
		  $sql .=" AND p.product_id BETWEEN '".(int)$data['filter_product_id']."' AND '".(int)$data['filter_endproduct_id']."'";
		}
		
		if(!empty($data['filter_product_id']) && empty($data['filter_endproduct_id'])){
		  $sql .=" AND p.product_id = '".(int)$data['filter_product_id']."'";
		}
		
		if(empty($data['filter_product_id']) && !empty($data['filter_endproduct_id'])){
		  $sql .=" AND p.product_id = '".(int)$data['filter_endproduct_id']."'";
		}
		
		if (isset($data['start']) || isset($data['limit'])){
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

	public function getLastImportedProductFile($different_file_mapping = true)
	{
		$sql = [];
		$sql[] = 'SELECT * from '.$this->import_files_tb;
		$sql[] = 'WHERE import_status=1';
		if($different_file_mapping){
			$sql[] = 'AND file_mapping not like "default"';
		}
		$sql[] = 'ORDER BY id desc LIMIT 1';			
		$data = $this->db->query(implode(' ',$sql));

		return $data->row;
	}
}
