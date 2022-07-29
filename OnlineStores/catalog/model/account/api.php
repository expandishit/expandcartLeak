<?php
class ModelAccountApi extends Model {
	public function login($username, $password) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "api` WHERE username = '" . $this->db->escape($username) . "' AND password = '" . $this->db->escape($password) . "' AND status = '1'");

		return $query->row;
	}
    public function getAPiData()
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "api` ");
        return $query->rows;

    }

    public function insertToken($token) {
	    $sessionData = serialize($this->session->data);//base64_encode(serialize($this->session->data));
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "token` WHERE token_key = '" . $this->db->escape($token) . "' AND status = '1'");

        if($query->row)
            $token_id = $query->row['token_id'];
        else {
            $this->db->query("INSERT INTO " . DB_PREFIX . "token SET token_key = '" . $this->db->escape($token) . "', sessionobject = '" . $this->db->escape($sessionData) . "', status ='1',  date_added = NOW(), date_modified = NOW()");
            $token_id = $this->db->getLastId();
        }
        return $token_id;
    }

    public function restoreSession($token) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "token` WHERE token_key = '" . $this->db->escape($token) . "' AND status = '1'");
        if($query->row) {
            $sessionData = unserialize($query->row['sessionobject']);//unserialize(base64_decode($query->row['sessionobject']));//json_decode($query->row['sessionobject'], true);//$this->mb_unserialize(base64_decode($query->row['sessionobject']));
            //var_dump($query->row['sessionobject']);
            $this->session->data = $sessionData;
        }
    }
    public function updateSession($token) {
	    $sessionData = serialize($this->session->data);//base64_encode(serialize($this->session->data));//json_encode($this->utf8_string_array_encode($this->session->data));//base64_encode(serialize($this->session->data));

        $query = $this->db->query("UPDATE " . DB_PREFIX . "token SET sessionobject = '" . $this->db->escape($sessionData) . "', date_modified = NOW() WHERE token_key = '" . $this->db->escape($token) . "'");
    }

}



