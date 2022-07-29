<?php
class ModelUserUser extends Model {

    public function dtHandler($start=0, $length=10, $search = null, $orderColumn="username", $orderType="ASC")
    {
        $query = "SELECT `user_id`, u.`user_group_id`, `username`, `password`, `salt`, `firstname`, `lastname`, `email`, `code`, `ip`, `status`, `date_added` , ug.name as group_name FROM " . DB_PREFIX . "user as u LEFT JOIN user_group as ug";
        $query .= ' ON u.user_group_id = ug.user_group_id';
        //$query = ;
        $total = $totalFiltered = $this->db->query($query)->num_rows;
        $where = "";
        if (!empty($search)) {
            $where .= "(u.username like '%" . $this->db->escape($search) . "%')";
            $query .= " WHERE " . $where;
            $totalFiltered = $this->db->query($query)->num_rows;
        }

        if ($orderColumn){
			$query .= " ORDER by {$orderColumn} {$orderType}";
		}

        if($length && $length != -1) {
            $query .= " LIMIT " . $start . ", " . $length;
        }
        //$data = array_merge($this->db->query($query)->rows, array($totalFiltered));

        $results = $this->db->query($query)->rows;

        $data = array (
            'data' => $results,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        );
        //$data = $this->db->query($query)->rows;
        return $data;
    }

	public function addUser($data) {

        if($data['image']) {
            $parts = explode(',', $data['image']);
            $image = $parts[0] . ',' . $this->resizeImage($parts[1], 70, 70);
        }

		$this->db->query("INSERT INTO `" . DB_PREFIX . "user` SET username = '" . $this->db->escape($data['username']) . "', salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', user_group_id = '" . (int)$data['user_group_id'] . "', status = '" . $data['status'] . "', image='$image', date_added = NOW()");
        $user_id = $this->db->getLastId();

		if (isset($data['outlet_id']) && $user_id) {
			$this->db->query("UPDATE `" . DB_PREFIX . "user` SET outlet_id = '" . $this->db->escape($data['outlet_id']) . "' WHERE user_id = '" . (int)$user_id . "'");
		}

		return $user_id;
    }
	
	public function editUser($user_id, $data) {

		$this->db->query("UPDATE `" . DB_PREFIX . "user` SET username = '" . $this->db->escape($data['username']) . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', user_group_id = '" . (int)$data['user_group_id'] . "', status = '" . $data['status'] . "' WHERE user_id = '" . (int)$user_id . "'");
		
		if ($data['password']) {
			$this->db->query("UPDATE `" . DB_PREFIX . "user` SET salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "' WHERE user_id = '" . (int)$user_id . "'");
		}

		if (isset($data['outlet_id'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "user` SET outlet_id = '" . $this->db->escape($data['outlet_id']) . "' WHERE user_id = '" . (int)$user_id . "'");
		}

		if($data['image']) {
		    $parts = explode(',', $data['image']);
		    $image = $parts[0] . ',' . $this->resizeImage($parts[1], 70, 70);
            $this->db->query("UPDATE `" . DB_PREFIX . "user` SET image='" . $image . "' WHERE user_id = '" . (int)$user_id . "'");
		}
		return $user_id;
	}

	public function changeStatus($id,$status){
		$this->db->query("UPDATE `" . DB_PREFIX . "user` SET " . " status = '" . $status . "' WHERE user_id = '" . (int)$id . "'"
		);
		return $id;
	}

	public function disableTrialUsers($limit){

		$sql = "UPDATE " . DB_PREFIX . "user SET
            status = '" . 0 . "'".
			" WHERE user_id IN (
            SELECT user_id FROM (
            SELECT user_id FROM user 
            LIMIT 18446744073709551610 OFFSET ".(int) $limit."
             ) tmp
        )";
		$this->db->query($sql);
	}

	public function getLastUserInLimitId($users_limit=1){
		$query = $this->db->query("select user_id FROM " . DB_PREFIX . "user limit 1 offset ".($users_limit - 1) );
		return $query->row['user_id'];
	}

	public function editPassword($user_id, $password) {
		$this->db->query("UPDATE `" . DB_PREFIX . "user` SET salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($password)))) . "', code = '' WHERE user_id = '" . (int)$user_id . "'");
	}

	public function editCode($email, $code) {
		$this->db->query("UPDATE `" . DB_PREFIX . "user` SET code = '" . $this->db->escape($code) . "' WHERE LCASE(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
	}
			
	public function deleteUser($user_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "user` WHERE user_id = '" . (int)$user_id . "'");
	}
	
	public function getUser($user_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "user` WHERE user_id = '" . (int)$user_id . "'");
	
		return $query->row;
	}
	
	public function getUserByUsername($username) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "user` WHERE username = '" . $this->db->escape($username) . "'");
	
		return $query->row;
	}
		
	public function getUserByCode($code) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "user` WHERE code = '" . $this->db->escape($code) . "' AND code != ''");
	
		return $query->row;
	}
		
	public function getUsers($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . POS_USERS_TABLE . "`";
			
		$sort_data = array(
			'username',
			'status',
			'date_added'
		);	
			
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY username";	
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

	public function getTotalUsers() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "user`");
		
		return $query->row['total'];
	}

	public function getTotalUsersByGroupId($user_group_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "user` WHERE user_group_id = '" . (int)$user_group_id . "'");
		
		return $query->row['total'];
	}
	
	public function getTotalUsersByEmail($email) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "user` WHERE LCASE(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
		
		return $query->row['total'];
	}

	private function resizeImage($imageString, $width, $height) {
        $data = base64_decode($imageString);
        $im = imagecreatefromstring($data);
        $newim = imagescale($im, $width, $height);

        // start buffering
        ob_start();
        imagepng($newim);
        $contents =  ob_get_contents();
        ob_end_clean();

        $newimagestr = base64_encode($contents);

        imagedestroy($im);
        imagedestroy($newim);

        return $newimagestr;
    }

    //Add outlet column, assign default outlet to admin for the first time
	public function posUserInit($uid, $outlet_id){
		$this->checkAddOutletCol();
		$this->db->query("UPDATE " . DB_PREFIX . "user SET outlet_id = $outlet_id WHERE user_id = $uid");
	}

	//check if outlet_id column exists
	public function isOutletExists(){
		$this->checkAddOutletCol();
		return true;
	}

	//add outlet_id if not exists
	private function checkAddOutletCol(){
		$outlet_id_col = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "user` LIKE 'outlet_id'");
		if(!$outlet_id_col->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "user` ADD COLUMN `outlet_id` int(10) NULL AFTER `status`");
		}
	}

	//POS Auto login from ControllerWkposMain->index()
	public function POSlogin($user_id) {

		$user_query = $this->db->query("SELECT * FROM " . DB_PREFIX . POS_USERS_TABLE . " wu LEFT JOIN " . DB_PREFIX . "wkpos_outlet wo ON (wu.outlet_id = wo.outlet_id) WHERE wu.user_id = '" . $this->db->escape($user_id) . "' AND wu.status = '1' AND wo.status = '1' ");

		if ($user_query->num_rows) {
			$this->session->data['user_login_id'] = $user_query->row['user_id'];
			$this->session->data['wkpos_outlet'] = $user_query->row['outlet_id'];

			$data['name'] = $user_query->row['firstname'] . ' ' . $user_query->row['lastname'];

			$this->load->model('tool/image');

			if ($user_query->row['image']) {
				$data['image'] = $this->model_tool_image->resize($user_query->row['image'], 100, 100);
			} else {
				$data['image'] = $this->model_tool_image->resize('no_image.png', 100, 100);
			}

			$data['user_id'] = $user_query->row['user_id'];
			$data['firstname'] = $user_query->row['firstname'];
			$data['lastname'] = $user_query->row['lastname'];
			$data['email'] = $user_query->row['email'];
			$data['username'] = $user_query->row['username'];

			$outlet_query = $this->db->query("SELECT name FROM " . DB_PREFIX . "wkpos_outlet WHERE outlet_id = '" . (int)$user_query->row['outlet_id'] . "'");

			$data['group_name'] = $outlet_query->row['name'];

			return $data;
		} else {
			return false;
		}
	}
}
?>
