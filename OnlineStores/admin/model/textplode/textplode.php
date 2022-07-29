<?php
class ModelTextplodeTextplode extends Model {

	private $tp;
	private $plugin_version = '0.2.2';

	public function __construct($registry){
		parent::__construct($registry);
		require_once('textplode.class.php');
		$this->tp = new Textplode($this->config->get('textplode_apikey'));
	}

	public function get_instance(){
		return $this->tp;
	}

	public function get_service_status(){
		return $this->tp->get_service_status();
	}

	public function get_service_messages(){
		$data = array(
			'platform_name' => 'OpenCart',
			'platform_version' => VERSION,
			'platform_host' => $_SERVER['HTTP_HOST'],
			'plugin_version' => $this->plugin_version,
			'language' => $this->config->get('config_admin_language'),
			'email' => (!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')),
			'name' => $this->config->get('config_name'),
			'owner' => $this->config->get('config_owner'),
		);
		return $this->tp->get_service_messages($data);
	}

	public function getFromName(){
		return $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key`='textplode_from_name'")->row['value'];
	}

	public function install() {

		// Create our own table for storing templates
		$query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "textplode_templates'");
		if($query->num_rows == 0){

			$this->db->query("CREATE TABLE `" . DB_PREFIX . "textplode_templates` ( `template_id` int(11) unsigned NOT NULL AUTO_INCREMENT, `template_name` varchar(255) NOT NULL DEFAULT '', `template_content` varchar(306) NOT NULL DEFAULT '', `language_id` int(11) NOT NULL DEFAULT 1 , PRIMARY KEY (`template_id`) ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;");

			$this->db->query("REPLACE INTO `" . DB_PREFIX . "textplode_templates` (`template_name`, `template_content`) VALUES 
				('Cancelled Order', 'Hi {fname}, your order (#{order_id}) has been cancelled'),
				('Processing', 'We are currently processing your order (#{order_id}). We will notify you when your order has been shipped.'),
				('Shipped', 'Your order has been shipped and should reach you within X working days.'),
				('Phone Verification', 'Your verification code is {confirm_code}');");

		}

		// Migrate from 0.1.2 to 0.1.3
		$query = $this->db->query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "textplode_templates' and column_name = 'language_id'");
		if($query->num_rows == 0){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "textplode_templates` ADD `language_id` int(11) DEFAULT 1 NOT NULL  AFTER `template_content`;");
		}

		// Migrate from 0.1.4 to 0.1.5
		$query = $this->db->query("SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "textplode_templates' and COLUMN_NAME = 'template_content'");
		if($query->row['DATA_TYPE'] == 'varchar'){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "textplode_templates` CHANGE `template_content` `template_content` LONGTEXT  CHARACTER SET utf8  NOT NULL;");
		}

		// Create a table to store customers who wish to receive updates
		$query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "textplode_sms_notifications'");
		if($query->num_rows == 0){

			$this->db->query("CREATE TABLE `" . DB_PREFIX . "textplode_sms_notifications` ( `customer_id` int(11) unsigned NOT NULL, PRIMARY KEY (`customer_id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

		}

		$store_name = substr($this->db->query("SELECT value from `" . DB_PREFIX . "setting` WHERE `key`='config_name'")->row['value'], 0 , 11);

		// Insert default settings - This gets done when we save for the first time, but it'd be nice to have something prepopulated
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key` = 'textplode_from_name'");
		if($query->num_rows == 0){
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` (`store_id`, `group`, `key`, `value`, `serialized`) VALUES 
				(0, 'textplode', 'textplode_from_name', '".$store_name."', 0), 
				(0, 'textplode', 'textplode_status', '0', 0), 
				(0, 'textplode', 'textplode_template_shipped_en', 3, 0), 
				(0, 'textplode', 'textplode_template_processing_en', 2, 0), 
				(0, 'textplode', 'textplode_template_cancelled_en', 1, 0),
				(0, 'textplode', 'textplode_template_verification_en', 4, 0)");
		}

		$this->log("Ran Installer Script");

	}

	public function uninstall() {
		// Delete the tables from the database. Probably only cleanup that needs doing...
		$this->db->query("DROP TABLE `" . DB_PREFIX . "textplode_templates`");
		$this->db->query("DROP TABLE `" . DB_PREFIX . "textplode_sms_notifications`");

		$this->log("Ran Uninstaller Script");
	}

	public function newTemplate($data){
		$this->db->query("INSERT INTO `" . DB_PREFIX . "textplode_templates` (`template_name`, `template_content`, `language_id`) VALUES ('" . $this->db->escape($data['template_name']) . "', '" . $this->db->escape(html_entity_decode($data['template_content'])) . "', '" . $this->db->escape($data['language_id']) . "');");
		$this->log("Created New Template ({$this->db->getLastId()})");
	}

	public function editTemplate($id, $data){
		$this->db->query("UPDATE `" . DB_PREFIX . "textplode_templates` SET `template_name` = '" . $this->db->escape($data['template_name']) . "', `template_content` = '" . $this->db->escape(html_entity_decode($data['template_content'])) . "', `language_id` = '" . $this->db->escape($data['language_id']) . "' WHERE template_id = " . $this->db->escape($id) . ";");
		$this->log("Edited Template ({$this->db->escape($id)})");
	}

	public function deleteTemplate($id){
		$this->db->query("DELETE FROM `" . DB_PREFIX . "textplode_templates` WHERE `template_id`=" . $this->db->escape($id) . ";");
		$this->log("Deleted Template ({$this->db->escape($id)})");
	}

	public function getTemplateFromId($id){
		return $this->db->query("SELECT * FROM `" . DB_PREFIX . "textplode_templates` WHERE template_id=" . $this->db->escape($id) . " LIMIT 1");
	}

	public function getTemplateFromStatusName($name){
		$templateId = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key`='textplode_template_" . $name . "'");
		if($templateId->num_rows == 0)
			return null;
		return $this->db->query("SELECT * FROM `" . DB_PREFIX . "textplode_templates` WHERE `template_id`=" . $templateId->row['value'])->row;
	}

	public function getTemplates(){
		return $this->db->query("SELECT * FROM `" . DB_PREFIX . "textplode_templates`");
	}

	public function getStatuses(){
		return $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_status` ORDER BY order_status_id ASC");
	}

	public function getStatusNameFromId($id, $order_id){
        $lang_id = $this->db->query("SELECT language_id FROM `" . DB_PREFIX . "order` where `" . DB_PREFIX . "order`.order_id = " . $order_id)->row['language_id'];

		$code = $this->db->query("SELECT code FROM `" . DB_PREFIX . "language` where `" . DB_PREFIX . "language`.language_id = " . $lang_id)->row['code'];

		return str_replace(' ', '_', strtolower($this->db->query("SELECT `name` FROM `" . DB_PREFIX . "order_status` WHERE language_id = " . $lang_id . " AND order_status_id=" . $this->db->escape($id))->row['name'])) . '_' . $code;
	}

	public function getAdminNumber(){
		return $this->config->get('textplode_admin_number');
	}

	// Returns whether or not a "status hook" is active to determine whether or not to send message on change
	public function isActive($status){
		$this->log("textplode_active_" . $status . ' = ' . $this->config->get("textplode_active_" . $status));
		return ($this->config->get("textplode_active_" . $status) == "on");
	}

	public function getErrorLog(){
		$this->language->load('module/textplode');
		if(file_exists(DIR_SYSTEM . '/logs/textplode.log')){
			$contents = file_get_contents(DIR_SYSTEM . 'logs/textplode.log');
			if($contents){
				return $contents;
			}else{
				return $this->language->get('error_log_empty');
			}
		}else{
			return $this->language->get('error_log_empty');
		}
	}

	public function clearErrorLog(){
		file_put_contents(DIR_SYSTEM . 'logs/textplode.log', '');
	}

	public function getCredits(){
		return $this->tp->account->get_credits();
	}

	public function getGroups(){
		return $this->tp->groups->get_all();
	}

	public function isValidNumber($number){
		return true;
	}

	public function getEvents(){
		return $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key` LIKE 'textplode_active_%' or `key` LIKE 'textplode_template_%'")->rows;
	}

	public function hasApiKey(){
		$this->load->model('setting/setting');
		return ($this->config->get('textplode_apikey') != '');
	}

	public function getMobileUsers(){
        $queryTextplode = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'textplode'");

        if($queryTextplode->num_rows) {
            $mobile = $this->db->query("SELECT count(1) as 'count' FROM `" . DB_PREFIX . "customer` WHERE telephone <> ''")->row['count'];
            $customers = $this->db->query("SELECT count(1) as 'count' FROM `" . DB_PREFIX . "customer`")->row['count'];
            $enabled = $this->db->query("SELECT count(1) as 'count' FROM `" . DB_PREFIX . "textplode_sms_notifications`")->row['count'];
            $percentage = ($customers > 0) ? ($mobile / $customers) * 100 : 0;
            return array('mobile' => $mobile, 'customers' => $customers, 'percentage' => $percentage, 'enabled' => $enabled);
        }
        else
            return array('mobile' => 0, 'customers' => 0, 'percentage' => 0, 'enabled' => 0);
	}

	public function getLanguages(){
		$languages = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` ORDER BY sort_order ASC")->rows;
		return $languages;
	}

	public function sync($group){
		$this->load->model('setting/setting');

		$customers = $this->db->query("SELECT firstname, lastname, telephone FROM `" . DB_PREFIX . "customer`")->rows;

		foreach($customers as $customer => $value){
			$this->tp->contacts->add($value['firstname'], $value['lastname'], $value['telephone'], $group);
		}
		$_group = $this->tp->groups->get($group);
		$this->log("Store Customers Merged to '{$_group['name']}' (Group ID: {$group})");
	}

	private function get_merge_data($order_id){
		$result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` where " . DB_PREFIX . "order.order_id = {$order_id}")->rows;

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

	public function sendMessage($to, $message, $order_id, $from = 'TEXTPLODE'){
		if(!$to){
			$this->log("Message failed to send. Phone number missing!");
			return false;
		}
		if(!$message){
			$this->log("Message failed to send. Message missing!");
			return false;
		}
		if(!$order_id){
			$this->log("Message failed to send. Order ID missing!");
			return false;
		}
		$this->tp->messages->set_from($from);
		$this->tp->messages->set_message($message);
		$this->tp->messages->add_recipient($to, $this->get_merge_data($order_id));
		$sent = $this->tp->messages->send();
		$this->log("Message Sent to {$to} (Order ID: {$order_id})");
	}

	public function getSMS($customer_id){
        $queryTextplode = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'textplode'");

        if($queryTextplode->num_rows) {
            $query = $this->db->query("SELECT * FROM  `" . DB_PREFIX . "textplode_sms_notifications` WHERE `customer_id` = " . $customer_id);
            $this->load->model('setting/setting');
            if ($this->config->get('textplode_opt_in_out') == 0) return true;
            return ($query->num_rows == 1);
        }
        else
            return false;
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