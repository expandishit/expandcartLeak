<?php
class ModelTextplodeTextplode extends Model {

	private $tp;

	public function __construct($registry){
		parent::__construct($registry);
		require_once('textplode.class.php');
		$this->tp = new Textplode($this->config->get('textplode_apikey'));
	}

	public function get_instance(){
		return $this->tp;
	}

	public function get_order($order_id){
		return $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . $order_id . "'")->row;
	}

	public function getTemplateFromId($id){
		return $this->db->query("SELECT * FROM `" . DB_PREFIX . "textplode_templates` WHERE template_id=" . $this->db->escape($id) . " LIMIT 1");
	}

	public function getTemplateFromStatusName($name){
		$templateId = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key`='textplode_template_" . $this->db->escape($name) . "'");
		if($templateId->num_rows == 0)
			return null;
		return $this->db->query("SELECT * FROM `" . DB_PREFIX . "textplode_templates` WHERE `template_id`=" . $templateId->row['value'])->row;
	}

	public function getStatusNameFromId($id, $include_code = false){
		$status = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_status` WHERE order_status_id=" . $this->db->escape($id));

		$status_name = strtolower($status->row['name']);
		$language = $status->row['language_id'];

		if($include_code){
			$language_code = strtolower($this->db->query("SELECT `code` FROM `" . DB_PREFIX . "language` WHERE language_id=" . $this->db->escape($language))->row['code']);
			return $status_name . '_' . $language_code;
		}
		return $status_name;
	}

	public function getAdminNumber(){
		return $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key`='textplode_admin_number'")->row['value'];
	}

	public function getFromName(){
		return $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key`='textplode_from_name'")->row['value'];
	}

	// Returns whether or not a "status hook" is active to determine whether or not to send message on change
	public function isActive($status){
		$result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key`='textplode_active_" . $status . "'");
		if(isset($result->row['value'])){
			if($result->row['value'] == "on"){
				return true;
			}
		}
		return false;
	}

	private function get_merge_data($order_id){
		$result = $this->db->query("SELECT * FROM `order` where `order`.order_id = {$order_id}")->rows;

		$merge = array(
			'{order_id}' => $order_id,
			'{fname}' => $result[0]['firstname'],
			'{lname}' => $result[0]['lastname'],
			'{email}' => $result[0]['email'],
            '{confirm_code}' => $result[0]['smsverifcode'],
			'{date}' => date($this->language->get('date_format_short'), strtotime($result[0]['date_added'])),
		);

		return $merge;
	}

	public function sendMessage($to, $message, $order_id = false, $from = 'TEXTPLODE'){
		$this->tp->messages->reset();
		$this->tp->messages->set_from($from);
		$this->tp->messages->set_message($message);

		if($order_id){
			$this->tp->messages->add_recipient($to, $this->get_merge_data($order_id));
		}else{
			$this->tp->messages->add_recipient($to);
		}

		$sent = $this->tp->messages->send();
		if(!$order_id){
			$this->log("Message Sent to Admin - New Customer");
		}else{
			$this->log("Message Sent to Admin - New Order (Order ID: {$order_id})");
		}
	}

	public function setSmsNotifications($customer_id, $enabled){
        $queryTextplode = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'textplode'");

        if($queryTextplode->num_rows) {
            if($enabled){
                $exists = $this->db->query("SELECT * FROM `" . DB_PREFIX . "textplode_sms_notifications` WHERE `customer_id`= " . $customer_id);
                if($exists->num_rows == 0){
                    $this->db->query("INSERT INTO `" . DB_PREFIX . "textplode_sms_notifications` VALUES (" . $customer_id . ")");
                }
            }else{
                $this->db->query("DELETE FROM `" . DB_PREFIX . "textplode_sms_notifications` WHERE `customer_id`=" . $customer_id);
            }
        }
	}

	public function getOptInOut(){
		$this->load->model('setting/setting');
		return $this->config->get('textplode_opt_in_out');
	}

	public function log($message){
		$timestamp = date('Y-m-d H:i:s') . ': ';
		file_put_contents(DIR_SYSTEM . 'logs/textplode.log', $timestamp . $message . "\r\n", FILE_APPEND);
	}

	public function logError($function, $error){
		// $timestamp = date('Y-m-d H:i:s') . ': ';
		// $function = ($function == '') ? '' : '[' . $function . '()] ';
		// file_put_contents(DIR_SYSTEM . 'logs/textplode.log', $timestamp . $function . $error . "\r\n", FILE_APPEND);
	}

}

?>