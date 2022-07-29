<?php

class ModelModuleSellerAds extends Model  {	
	

	public function getAdPackage($package_title) {
		$query = "SELECT `id`, `type` FROM `" . DB_PREFIX . "seller_ads_pacakages` WHERE `type` LIKE '%{$package_title}%'";
		return $this->db->query($query)->row;
	}

	public function getSellerAds($seller_id) {
        $query = "SELECT *, DATEDIFF(sa.expire_date, NOW()) ad_remaining_days FROM " . DB_PREFIX . "seller_ads_seller_ads sa LEFT JOIN seller_ads_pacakages sap ON (sap.id=sa.seller_ads_package_id) LEFT JOIN ms_seller ms ON (sa.seller_id=ms.seller_id) LEFT JOIN customer c ON (c.customer_id=ms.seller_id) WHERE sa.seller_id='{$seller_id}'";
		return $this->db->query($query)->rows;
	}

	public function getSellersAds() {
        $query = "SELECT *, DATEDIFF(sa.expire_date, NOW()) ad_remaining_days FROM " . DB_PREFIX . "seller_ads_seller_ads sa LEFT JOIN seller_ads_pacakages sap ON (sap.id=sa.seller_ads_package_id) LEFT JOIN ms_seller ms ON (sa.seller_id=ms.seller_id) LEFT JOIN customer c ON (c.customer_id=ms.seller_id) WHERE DATEDIFF(sa.expire_date, NOW()) >= 0";
		return $this->db->query($query)->rows;
	}


	public function getSellerAd($ad_id) {
        $query = "SELECT *, DATEDIFF(sa.expire_date, NOW()) ad_remaining_days FROM " . DB_PREFIX . "seller_ads_seller_ads sa LEFT JOIN seller_ads_pacakages sap ON (sap.id=sa.seller_ads_package_id) LEFT JOIN ms_seller ms ON (sa.seller_id=ms.seller_id) LEFT JOIN customer c ON (c.customer_id=ms.seller_id) WHERE  sa.id='{$ad_id}'";
		return $this->db->query($query)->row;
	}


	public function createNewAdSubscription($ad_data) {

		$query = "INSERT INTO `" . DB_PREFIX . "seller_ads_seller_ads` ";
		$query .= "(`seller_ads_package_id`, `seller_id`, `start_date`, `expire_date`, `title`, `link`, `image`) ";
		$query .= "VALUES('{$ad_data['seller_ads_package_id']}', '{$ad_data['seller_id']}', '{$ad_data['start_date']}', '{$ad_data['expire_date']}', '{$ad_data['title']}', '{$ad_data['link']}', '{$ad_data['image']}')";
		
		return $this->db->query($query);
	}

	
	public function updateAd($ad_data) {

		$query = "UPDATE `" . DB_PREFIX . "seller_ads_seller_ads` ";
		$query .= "SET `title`='{$ad_data['title']}', `link`='{$ad_data['link']}'";
		if ($ad_data['image']) {
			$query .= ", `image`='{$ad_data['image']}' ";
		}
		$query .= "WHERE `id`='{$ad_data['id']}'";
		
		return $this->db->query($query);
	}

}
