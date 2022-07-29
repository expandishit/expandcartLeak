<?php

class ModelSaleNewsletterSubscriber extends Model
{
	public function getUnregisteredSubscribers($customer_group = null)
	{
		if($customer_group )
			return $this->db->query("SELECT 
				newsletter_subscriber_id as customer_id,
				'' as telephone,
				`name`, email ,'".$customer_group."' as customer_group, `status`, '' as ip,
				created_at as date_added,'unregistered' AS is_registered
				FROM newsletter_subscriber")->rows;
		return $this->db->query("SELECT *, newsletter_subscriber_id as customer_id,'unregistered' AS is_registered  FROM newsletter_subscriber")->rows;
	}

	public function getRegisteredSubscribers($customer_group = null)
	{
		if($customer_group )		
			return $this->db->query("
				SELECT * , cgd.name AS customer_group, concat(firstname, ' ', lastname) as `name`,'registered' AS is_registered  
				FROM customer JOIN customer_group_description cgd ON cgd.customer_group_id = customer.customer_group_id
				WHERE newsletter = 1 and cgd.language_id = ". $this->config->get('config_language_id'))->rows;
		return $this->db->query("SELECT customer_id as newsletter_subscriber_id , concat(firstname, ' ', lastname) as `name`, email, date_added as created_at, updated_at,'registered' AS is_registered  FROM customer WHERE newsletter = 1")->rows;
		// return $this->db->query("SELECT customer_id as newsletter_subscriber_id , concat(firstname, ' ', lastname) as `name`, email, date_added as created_at, updated_at,'registered' AS is_registered  FROM customer WHERE newsletter = 1")->rows;
	}

	public function getAllSubscribers($filter, $customer_group = null)
	{
		//UnRegistered Ones
		$sql = "SELECT * , created_at as date_added, '-' AS customer_id, '' as telephone , '' as ip , 'unregistered' AS is_registered " . ($customer_group ? ",'" .$customer_group. "'as customer_group " : "") . "FROM newsletter_subscriber WHERE 1=1";
		if($filter['name']){
			$sql .= " AND `name` LIKE '%" . $this->db->escape($filter['name']) . "%'";
		}
		if($filter['email']){
			$sql .= " AND `email` LIKE '%" . $this->db->escape($filter['email']) . "%'";
		}
		if($filter['date_added']){
			$sql .= " AND date(`created_at`) = '" . $this->db->escape($filter['date_added']) . "'";
		}
		if(isset($filter['status'])){
			$sql .= " AND `status` = " . (int)$filter['status'];
		}
		if($filter['search']){
			$sql .= " AND `name` LIKE '%" . $this->db->escape($filter['search']) . "%'
			OR email LIKE '%" . $this->db->escape($filter['search']) . "%'";
		}

		$filter['start'] = $filter['start'] ?: 0;
		$filter['limit'] = $filter['limit'] ?: 200;
		$sql .= " LIMIT " . (int)$filter['start'] . ($filter['limit'] ? "," . $filter['limit']:"" );	
		$unregistered = $this->db->query($sql)->rows;


		//Registered Ones
		$sql = "SELECT *,'-' AS newsletter_subscriber_id , 
		concat(firstname, ' ', lastname) as `name`,
		cgd.name AS customer_group
		FROM customer JOIN customer_group_description cgd ON cgd.customer_group_id = customer.customer_group_id
		WHERE newsletter=1 AND cgd.language_id = ". $this->config->get('config_language_id');
		if($filter['name']){
			$sql .= " AND (customer.firstname LIKE '%" . $this->db->escape($filter['name']) . "%' OR customer.lastname LIKE '%" . $this->db->escape($filter['name'])."%')";
		}
		if($filter['email']){
			$sql .= " AND `email` LIKE '%" . $this->db->escape($filter['email']) . "%'";
		}
		if($filter['date_added']){
			$sql .= " AND date(`created_at`) = '" . $this->db->escape($filter['date_added']) . "'";
		}
		if(isset($filter['status'])){
			$sql .= " AND `status` = " . (int)$filter['status'];
		}
		if($filter['search']){
			$sql .= " AND (CONCAT(customer.firstname, ' ', customer.lastname) LIKE '%" . $this->db->escape($filter['search']) . "%'
			OR customer.email LIKE '%" . $this->db->escape($filter['search']) . "%'
			OR customer.telephone LIKE '%" . $this->db->escape($filter['search']) . "%')";
		}

		$filter['start'] = $filter['start'] ?: 0;
		$sql .= " LIMIT " . (int)$filter['start'] . ($filter['limit'] ? "," . $filter['limit']:"" );		
		return array_merge($this->db->query($sql)->rows, $unregistered);
	}
	public function deleteSubscriber($subscriber_id)
	{
		$this->db->query("DELETE FROM newsletter_subscriber WHERE newsletter_subscriber_id = " . (int)$subscriber_id);
	}
}
