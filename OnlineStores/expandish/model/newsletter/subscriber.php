<?php

class ModelNewsletterSubscriber extends Model
{
	public function getSubscriberByEmail($email)
	{
		return $this->db->query("SELECT * 
			FROM newsletter_subscriber 
			WHERE email = '" . $this->db->escape(trim($email)) . "';")->row;
	}

	public function addNewSubscriber($email)
	{
		$name = $this->getNameFromEmail($email);
		
		$this->db->query("
			INSERT INTO newsletter_subscriber (`name`, `email`) 
			VALUES ( '" . $this->db->escape($name). "' , '" . $this->db->escape($email) . "' 
		)");

		return $this->db->getLastId();
	}

	private function getNameFromEmail($email)
	{		
		$customer_name = substr($email, 0, strpos($email, '@'));
		$customer_name = preg_replace("/[.|-|_]/", ' ', $customer_name);
		$customer_name = preg_replace('/[^A-Za-z0-9 ]/', '', $customer_name);		
		$names = explode(' ', $customer_name);
		return ucwords($names[0]) . ($names[1] ? ' ' . ucwords($names[1]) : '');
	}

	public function deleteSubscriber($email)
	{
    	$this->db->query("DELETE FROM newsletter_subscriber WHERE email = '" . $this->db->escape(trim($email)) . "';");		
	}
}
