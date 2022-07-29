<?php

class ModelModulePayThem extends Model  {	
	
	public function isPayThemProduct($product_id)
    {
        $query = "SELECT `OEM_PRODUCT_ID` FROM `" . DB_PREFIX . "product_to_paythem` ";
		$query .= "WHERE `product_id`='{$product_id}'";
        return $this->db->query($query)->row;
	}

}
