<?php

class Request
{

	public $get = array();
	public $post = array();
	public $cookie = array();
	public $files = array();
	public $server = array();

	public function __construct($db = null)
	{
		if ($db) {
			$this->db = $db;
		}

		/*		$_GET = $this->clean($_GET);
                $_POST = $this->clean($_POST);
                $_REQUEST = $this->clean($_REQUEST);
                $_COOKIE = $this->clean($_COOKIE);
                $_FILES = $this->clean($_FILES);
                $_SERVER = $this->clean($_SERVER);
        */
		$_GET = $this->sanitize($this->clean($_GET));
		$_POST = $this->sanitize($this->clean($_POST));
		$_REQUEST = $this->sanitize($this->clean($_REQUEST));
		$_COOKIE = $this->sanitize($this->clean($_COOKIE));
		$_FILES = $this->clean($_FILES);
		$_SERVER = $this->clean($_SERVER);

		$this->get = $_GET;
		$this->post = $_POST;
		$this->request = $_REQUEST;
		$this->cookie = $_COOKIE;
		$this->files = $_FILES;
		$this->server = $_SERVER;
	}

	public function clean($data)
    {
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				unset($data[$key]);

				$data[$this->clean($key)] = $this->clean($value);
			}
		} else {
			$data = htmlspecialchars($data, ENT_COMPAT, 'UTF-8');
		}

		return $data;
	}

	public function sanitize($data)
	{
		$allowedFormInput = ['SECURED' => true, 'COMBINED' => true];
		if (
			isset($_SERVER['HTTP_X_EC_FORM_INPUTS']) &&
			isset($allowedFormInput[$_SERVER['HTTP_X_EC_FORM_INPUTS']])
		) {
			return $data;
		}

        if (!$this->db) {
            return $data;
        }

		if (is_array($data)) {
			foreach ($data as $key => $value) {
				unset($data[$key]);

				$data[$key] = $this->sanitize($value);
			}
		} else {
			$data = $this->db->escape($data);
		}
		return $data;
	}

	/**
	 * Get the client's real IP if there is a proxy server
	 * CLONED from /system/library/customer.php
	 */
	public function ip()
	{
		$ip = $tmpIp = $this->server['REMOTE_ADDR'];

		if (isset($this->server['HTTP_CLIENT_IP']) && $this->server['HTTP_CLIENT_IP'] != '') {
			$ip = $this->server['HTTP_CLIENT_IP'];
		}
		if (isset($this->server['HTTP_X_FORWARDED_FOR']) && $this->server['HTTP_X_FORWARTDED_FOR'] != '') {
			$ip = $this->server['HTTP_X_FORWARDED_FOR'];
		}
		if (isset($this->server['HTTP_X_REAL_IP']) && $this->server['HTTP_X_REAL_IP'] != '') {
			$ip = $this->server['HTTP_X_REAL_IP'];
		}
		if (isset($this->server["HTTP_CF_CONNECTING_IP"]) && $this->server['HTTP_CF_CONNECTING_IP'] != '') {
			$ip = $this->server["HTTP_CF_CONNECTING_IP"];
		}

		if (!filter_var($ip, FILTER_VALIDATE_IP)) {
			$ip = $tmpIp;
		}

		return $ip;
	}
}
?>
