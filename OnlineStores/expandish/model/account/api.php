<?php
class ModelAccountApi extends Model {
	public function login($username, $password) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "api` WHERE username = '" . $this->db->escape($username) . "' AND password = '" . $this->db->escape($password) . "' AND status = '1'");

		return $query->row;
	}

    public function insertToken($token) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "token` WHERE token_key = '" . $this->db->escape($token) . "' AND status = '1'");

        if($query->row)
            $token_id = $query->row['token_id'];
        else {
            $this->db->query("INSERT INTO " . DB_PREFIX . "token SET token_key = '" . $this->db->escape($token) . "', sessionobject = '" . $this->db->escape(serialize($this->session->data)) . "', status ='1',  date_added = NOW(), date_modified = NOW()");
            $token_id = $this->db->getLastId();
        }
        return $token_id;
    }

    public function restoreSession($token) {
        $query = $this->db->query("SELECT sessionobject FROM `" . DB_PREFIX . "token` WHERE token_key = '" . $this->db->escape($token) . "' AND status = '1'");
        if($query->row)
            $this->customer->loginByCustomerArray(unserialize($query->row['sessionobject']));

    }

    public function updateSession($token) {
        $query = $this->db->query("UPDATE " . DB_PREFIX . "token SET sessionobject = '" . $this->db->escape(serialize($this->session->data)) . "', date_modified = NOW() WHERE token_key = '" . $this->db->escape($token) . "'");
    }

    public function getDropnaClient()
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM `api_clients`';
        $queryString[] = 'WHERE target="dropna"';
        $queryString[] = 'AND store_code="' . STORECODE. '"';

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows > 0) {
            return $data->row;
        }

        return false;
    }
}
