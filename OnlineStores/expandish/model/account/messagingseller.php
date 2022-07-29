<?php
class ModelAccountMessagingseller extends Model {

	public function getCustomersbyIds($customers_ids) {

		$customers_ids = implode(',', $customers_ids);
		$query = "SELECT c.customer_id, CONCAT(c.firstname, ' ', c.lastname) nickname, c.email, c.telephone, c.fax, ms.* ";
		$query .= "FROM `" . DB_PREFIX . "customer` c ";
		$query .= "LEFT JOIN `" . DB_PREFIX . "ms_seller` ms ON (c.customer_id = ms.seller_id)";
		$query .= "WHERE ms.seller_id IN ('$customers_ids')";

		return $this->db->query($query)->rows;
	}

	public function getCustomerbyId($customer_id) {

		$query = "SELECT c.customer_id, CONCAT(c.firstname, ' ', c.lastname) name, c.email, c.telephone, c.fax, ms.* ";
		$query .= "FROM `" . DB_PREFIX . "customer` c ";
		$query .= "LEFT JOIN `" . DB_PREFIX . "ms_seller` ms ON (c.customer_id = ms.seller_id)";
		$query .= "WHERE ms.seller_id = ('$customer_id')";

		return $this->db->query($query)->row;
	}

	public function getTotalSellers() {
      	$sql = "SELECT COUNT(*) as total FROM " . DB_PREFIX . "ms_seller WHERE seller_status = 1";

		$res = $this->db->query($sql);

		return $res->row['total'];
	}

	public function getMessages($user1_id, $user2_id, $latest_id = null) {
		$query = "SELECT * FROM " . DB_PREFIX . "ms_messaging WHERE ";
		$query .= "(user1_id = '{$user1_id}' AND user2_id = '{$user2_id}') OR ";
		$query .= "(user1_id = '{$user2_id}' AND user2_id = '{$user1_id}')";
		$conversation = $this->db->query($query);
		
		$product_id = 0;
		$subject = '';
		$messages = false;

		if ($conversation->num_rows) {

			$product_id   = $conversation->row['product_id'];
			$subject      = $conversation->row['subject'];
			$messaging_id = (int)$conversation->row['id'];

			$conv_msg_query = "SELECT * FROM " . DB_PREFIX . "ms_messaging_msgs";
			if($latest_id)
				$conv_msg_query .= " WHERE messaging_id = '{$messaging_id}' AND id > '{$latest_id}'";
			else
				$conv_msg_query .= " WHERE messaging_id = '{$messaging_id}'";

			$conv_msgs = $this->db->query($conv_msg_query);

			if ($conv_msgs->num_rows) {
				$messages = $conv_msgs->rows;
				$query = "UPDATE " . DB_PREFIX . "ms_messaging_msgs SET `read` = 1 WHERE owner_id='{$user2_id}'";
				$this->db->query($query);
			}
		}

		return ['subject' => $subject, 'messages' => $messages, 'product_id' => $product_id];
	}


	public function addMessage($data){

		$chated_user_id = (int)$data['user2_id'];
		$current_user_id = (int)$data['user1_id'];

		$query = "SELECT * FROM " . DB_PREFIX . "ms_messaging WHERE ";
		$query .= "(user1_id = '{$chated_user_id}'  AND user2_id = '{$current_user_id}') OR ";
		$query .= "(user2_id = '{$chated_user_id}'  AND user1_id = '{$current_user_id}')";

		$conversation = $this->db->query($query);

		if ($conversation->num_rows) {
			$messaging_id = (int)$conversation->row['id'];
		} else {

			$user1_id = $current_user_id;
			$user2_id = $chated_user_id;
			$user1_type = $data['user1_type'];
			$user2_type = $data['user2_type'];
			$subject = $data['subject'];
			$product_id = (int)$data['product_id'];

			$query = "INSERT INTO " . DB_PREFIX . "ms_messaging SET ";
			$query .= "user1_id = '{$user1_id}', user2_id = '{$user2_id}', ";
			$query .= "user1_type = '{$user1_type}', user2_type = '{$user2_type}', ";
			$query .= "subject = '{$subject}', product_id = '{$product_id}', status = 1";
			$this->db->query($query);

			$messaging_id = (int)$this->db->getLastId();
		}

		$message_id = 0;

		if($messaging_id) {

			$owner = $data['user1_type'];
			$owner_id = (int)$data['user1_id'];
			$message = $this->db->escape($data['msg']);

			$query = "INSERT INTO " . DB_PREFIX . "ms_messaging_msgs SET ";
			$query .= "messaging_id = {$messaging_id}, owner_id = '{$owner_id}', owner = '{$owner}', message = '{$message}'";
			$this->db->query($query);

			$message_id =  $this->db->getLastId();
		}

		return $message_id;
	}

	public function removeConvr($user1_id, $user2_id) {
		$query = "SELECT * FROM " . DB_PREFIX . "ms_messaging WHERE ";
		$query .= "(user1_id = {$user1_id}  AND user2_id = {$user2_id}) OR ";
		$query .= "(user2_id = {$user1_id}  AND user1_id = {$user2_id})";
		$conversation = $this->db->query($query);
		if ($conversation->num_rows) {
			$messaging_id = (int)$conversation->row['id'];

			$this->db->query("DELETE FROM " . DB_PREFIX . "ms_messaging WHERE id = '{$messaging_id}'");

			$this->db->query("DELETE FROM " . DB_PREFIX . "ms_messaging_msgs WHERE messaging_id = '{$messaging_id}'");
		}
	}


	public function getConversations($user_id)
	{
		$query = "SELECT convs.* ";
		$query .= ",c.customer_id customer_id, CONCAT(c.firstname, ' ', c.lastname) name, c.email email, c.telephone telephone, c.fax fax ";
		$query .= ",_c.customer_id _customer_id, CONCAT(_c.firstname, ' ', _c.lastname) _name, _c.email _email, _c.telephone _telephone, _c.fax _fax ";
		$query .= ",ms.seller_id seller_id, ms.company company, ms.website website, ms.avatar avatar, ms.nickname nickname ";
		$query .= ",_ms.seller_id _seller_id, _ms.company _company, _ms.website _website, _ms.avatar _avatar, _ms.nickname _nickname ";
		$query .= ",(SELECT COUNT(`read`) total_unread from ". DB_PREFIX ."ms_messaging_msgs WHERE `read`= 0 AND " . DB_PREFIX . "ms_messaging_msgs.messaging_id=convs.id AND owner_id != '{$user_id}') total_unread ";
		$query .= "FROM " . DB_PREFIX . "ms_messaging convs ";
		$query .= "LEFT JOIN customer c ON (c.customer_id = convs.user1_id AND convs.user1_id != '{$user_id}') ";
		$query .= "LEFT JOIN customer _c ON (_c.customer_id = convs.user2_id AND convs.user2_id != '{$user_id}') ";
		$query .= "LEFT JOIN ms_seller ms ON (ms.seller_id = convs.user1_id AND convs.user1_id != '{$user_id}') ";
		$query .= "LEFT JOIN ms_seller _ms ON (_ms.seller_id = convs.user2_id AND convs.user2_id != '{$user_id}') ";
		$query .= "WHERE convs.user1_id={$user_id} OR convs.user2_id={$user_id}";
		return $this->db->query($query)->rows;
	}


	public function getTotalUnreadForUser($user_id)
	{

		if (!$this->isAppUpgraded) {
			return;
		}

		$query = "SELECT COUNT(`read`) unread_count FROM ". DB_PREFIX ."ms_messaging_msgs msgs LEFT JOIN ms_messaging ms ON msgs.messaging_id = ms.id AND msgs.owner_id != '{$user_id}' AND (ms.user1_id='{$user_id}' OR ms.user2_id='{$user_id}') WHERE `read` = 0";
		return $this->db->query($query)->row;
	}


	

	public function getOldConversations()
	{
		$query = "SELECT * FROM " . DB_PREFIX . "ms_messaging";

		return $this->db->query($query)->rows;
	}


	public function getConversationMsgs($messaging_id)
	{
		$query = "SELECT * FROM " . DB_PREFIX . "ms_messaging_msgs WHERE messaging_id = '{$messaging_id}'";

		return $this->db->query($query)->rows;
	}


	public function migrateOldConversations($convs, $msgs)
	{

		if($convs != "") {
			$query = "INSERT INTO " . DB_PREFIX . "_ms_messaging (`user1_id`, `user2_id`, `user1_type`, `user2_type`, `subject`, `product_id`, `date_added`) ";
			$query .= "VALUES {$convs}";
			$this->db->query($query);
		}

		if($msgs != "") {
			$query = "INSERT INTO " . DB_PREFIX . "_ms_messaging_msgs (`messaging_id`, `owner_id`, `owner`, `message`, `read`, `date_added`) ";
			$query .= "VALUES {$msgs}";
			$this->db->query($query);
		}

		$this->db->query("DROP TABLE IF EXISTS ms_messaging_msgs");
		$this->db->query("DROP TABLE IF EXISTS ms_messaging");
		$this->db->query("RENAME TABLE _ms_messaging TO ms_messaging, _ms_messaging_msgs TO ms_messaging_msgs");
		
	}

	public function isAppUpgraded()
	{
		return $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "ms_messaging` LIKE 'user1_id'")->row['Field'];
	}
}
?>