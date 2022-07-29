<?php 
/******************************************************
 * @package Pav blog module for Opencart 1.5.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2013 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/

/**
 * class ModelPavblogcategory 
 */
class ModelPavquickviewproduct extends Model {		
	
	/**
	 * Get List Admin Users
	 */
	public function getUriFromKeyword($keyword = ""){
		if(empty($keyword)) return "";
		$sql = "SELECT `query` FROM `" . DB_PREFIX . "url_alias` WHERE `keyword`='".$keyword."'";
		$query = $this->db->query( $sql );
		$output = "";
		if ($query->num_rows > 0) {
			$output = $query->row['query'];
		}
		return $output;
	}
}
?>
