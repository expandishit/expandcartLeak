<?php
class ModelWkposUser extends Model {
	/**
	 * Adds a user for the POS purpose
	 * @param array $data contains the data of a user
	 */
	public function addUser($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . POS_USERS_TABLE."` SET username = '" . $this->db->escape($data['username']) . "', outlet_id = '" . (int)$data['outlet_id'] . "', salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', image = '" . $this->db->escape($data['image']) . "', status = '" . (int)$data['status'] . "', date_added = NOW()");

		return $this->db->getLastId();
	}

	/**
	 * Edits an existing user
	 * @param  int $user_id contains the user id
	 * @param  array $data    contains the data of a user
	 * @return null          none
	 */
	public function editUser($user_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . POS_USERS_TABLE."` SET username = '" . $this->db->escape($data['username']) . "', outlet_id = '" . (int)$data['outlet_id'] . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', image = '" . $this->db->escape($data['image']) . "', status = '" . (int)$data['status'] . "' WHERE user_id = '" . (int)$user_id . "'");

		if ($data['password']) {
			$this->db->query("UPDATE `" . DB_PREFIX . POS_USERS_TABLE."` SET salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "' WHERE user_id = '" . (int)$user_id . "'");
		}
	}

	/**
	 * Edits the password of a user
	 * @param  int $user_id  contains the id of a user
	 * @param  varchar $password contains the new password of the user
	 * @return null           none
	 */
	public function editPassword($user_id, $password) {
		$this->db->query("UPDATE `" . DB_PREFIX . POS_USERS_TABLE."` SET salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($password)))) . "', code = '' WHERE user_id = '" . (int)$user_id . "'");
	}

	public function editCode($email, $code) {
		$this->db->query("UPDATE `" . DB_PREFIX . POS_USERS_TABLE."` SET code = '" . $this->db->escape($code) . "' WHERE LCASE(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
	}

	/**
	 * Deletes an existing user
	 * @param  int $user_id contains the user id
	 * @return null          none
	 */
	public function deleteUser($user_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . POS_USERS_TABLE."` WHERE user_id = '" . (int)$user_id . "'");
	}

	/**
	 * Fetches the data of a user
	 * @param  int $user_id contains the id of a user
	 * @return array          returns the data of a user
	 */
	public function getUser($user_id) {
		$query = $this->db->query("SELECT *, (SELECT ug.name FROM `" . DB_PREFIX . "wkpos_outlet` ug WHERE ug.outlet_id = u.outlet_id) AS outlet FROM `" . DB_PREFIX . "wkpos_user` u WHERE u.user_id = '" . (int)$user_id . "'");

		return $query->row;
	}

	public function getUserByUsername($username) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . POS_USERS_TABLE."` WHERE username = '" . $this->db->escape($username) . "'");

		return $query->row;
	}

	public function getUserByCode($code) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . POS_USERS_TABLE."` WHERE code = '" . $this->db->escape($code) . "' AND code != ''");

		return $query->row;
	}

	public function getUsersDt($data = [], $filterData = []) {
		$query = $fields = [];

	    $fields = [
	      'u.*',
	      'o.name as outlet'
	    ];

	    $fields = sprintf(
	         implode(',', $fields)
	    );

	    $query[] = 'SELECT ' . $fields . ' FROM `' . DB_PREFIX . POS_USERS_TABLE.'` u LEFT JOIN `' . DB_PREFIX . 'wkpos_outlet` o ON (u.outlet_id = o.outlet_id)';

	    $query[] = 'WHERE u.user_id > "0"';

	    $total = $this->db->query(
	            'SELECT COUNT(*) AS totalData FROM ('.
	            str_replace($fields, '0 as fakeColumn', implode(' ', $query))
	            .') AS t'
	        )->row['totalData'];

	    if (isset($filterData['search'])) {

	      $filterData['search'] = $this->db->escape($filterData['search']);

	      $query[] = 'AND (';

	      if (trim($filterData['search'][0]) == '#') {
	          $user_id = preg_replace('/\#/', '', $filterData['search']);
	          $query[] = "u.user_id LIKE '%{$user_id}%'";
	      } else {
	          $query[] = "u.username LIKE '%{$filterData['search']}%'";
	          $query[] = 'OR o.name LIKE "%' . $filterData['search'] . '%"';
	      }
	      $query[] = ')';
	    }

	    $totalFiltered = $this->db->query(
	        'SELECT COUNT(*) AS totalData FROM ('.
	        str_replace($fields, '0 as fakeColumn', implode(' ', $query))
	        .') AS t'
        )->row['totalData'];

        $sort_data = array(
			'username',
			'status',
			'date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$query[] = " ORDER BY " . $data['sort'];
		} else {
			$query[] = " ORDER BY name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$query[] = " DESC";
		} else {
			$query[] = " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$query[] = " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query(implode(' ', $query));

	    return [
	      'data' => $query->rows,
	      'total' => $total,
	      'totalFiltered' => $totalFiltered
	    ];
	}

	/**
	 * Fetches the users as per filters
	 * @param  array  $data contains the filter data
	 * @return array       returns the users data
	 */
	public function getUsers($data = array()) {
		$sql = "SELECT u.*, o.name as outlet FROM `" . DB_PREFIX . POS_USERS_TABLE."` u LEFT JOIN `" . DB_PREFIX . "wkpos_outlet` o ON (u.outlet_id = o.outlet_id)";

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

	/**
	 * Fetches the total number of users
	 * @return int returns the total count of users
	 */
	public function getTotalUsers() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . POS_USERS_TABLE."`");

		return $query->row['total'];
	}

	public function getTotalUsersByGroupId($outlet_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . POS_USERS_TABLE."` WHERE outlet_id = '" . (int)$outlet_id . "'");

		return $query->row['total'];
	}

	public function getTotalUsersByEmail($email) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . POS_USERS_TABLE."` WHERE LCASE(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");

		return $query->row['total'];
	}
    public function checkUserLogin() {

            if (isset($this->session->data['user_login_id']) && $this->session->data['user_login_id']) {
                return $this->db->query("SELECT * FROM " . DB_PREFIX . POS_USERS_TABLE . " WHERE user_id = '" . $this->session->data['user_login_id'] . "'")->row;
            } else {
                return false;
            }
    }
}
