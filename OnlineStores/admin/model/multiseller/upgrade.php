<?php
class ModelMultisellerUpgrade extends Model {
	public function __construct($registry) {
		parent::__construct($registry);
		$this->load->model('localisation/language');
	}

	public function getDbVersion() {
		$res = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "ms_db_schema'");
		if (!$res->num_rows) return '0.0.0.0';

		$res = $this->db->query("SELECT * FROM `" . DB_PREFIX . "ms_db_schema` ORDER BY schema_change_id DESC LIMIT 1");

		if ($res->num_rows)
			return $res->row['major'] . '.' . $res->row['minor'] . '.' . $res->row['build'] . '.' . $res->row['revision'];
		else
			return '0.0.0.0';
	}

	public function isDbLatest() {
		$current = $this->getDbVersion();
		if ($this->MsLoader->dbVer == $current) return true;
		return false;
	}

	private function _createSchemaEntry($version) {
		$schema = explode(".", $version);
		$this->db->query("INSERT INTO `" . DB_PREFIX . "ms_db_schema` (major, minor, build, revision, date_applied) VALUES({$schema[0]},{$schema[1]},{$schema[2]},{$schema[3]}, NOW())");
	}

	public function upgradeDb() {
		$version = $this->getDbVersion();

		if (version_compare($version, '2.1.0.0') < 0) {
            $this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_db_schema` (
				`schema_change_id` int(11) NOT NULL AUTO_INCREMENT,
				`major` TINYINT NOT NULL,
				`minor` TINYINT NOT NULL,
				`build` TINYINT NOT NULL,
				`revision` SMALLINT NOT NULL,
				`date_applied` DATETIME NOT NULL,
			PRIMARY KEY (`schema_change_id`)) default CHARSET=utf8");

            /*$sql = "
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_seller_tax_class` (
            `seller_id` int(11) NOT NULL,
            `tax_class_id` int(11) NOT NULL,
            PRIMARY KEY (`seller_id`),
            UNIQUE KEY `seller_id` (`seller_id`)
            ) DEFAULT CHARSET=utf8";
            $this->db->query($sql);*/


            $this->db->query("
		ALTER TABLE `" . DB_PREFIX . "ms_seller` ADD `subscription_plan` INT(11) NOT NULL
		");

            $this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_subscriptions` (
		`plan_id` int(11) NOT NULL AUTO_INCREMENT,
		`plan_status` int(2) NOT NULL DEFAULT '0',
		`price` decimal(15,4) NOT NULL DEFAULT '0.0000',
		`discount` decimal(15,4) NOT NULL DEFAULT '0.0000',
		PRIMARY KEY (`plan_id`)
		) DEFAULT CHARSET=utf8");
			
            $this->db->query("
		ALTER TABLE `" . DB_PREFIX . "ms_subscriptions` ADD `format` INT(11) NOT NULL DEFAULT '3'
		");
            $this->db->query("
		ALTER TABLE `" . DB_PREFIX . "ms_subscriptions` ADD `period` INT(11) NOT NULL DEFAULT '1'
		");

            $this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_subscriptions_description` (
		`plan_id` int(11) NOT NULL,
		`language_id` int(11) NOT NULL,
		`title` VARCHAR(255) NOT NULL,
		`description` TEXT NULL,
		PRIMARY KEY (`plan_id`, `language_id`)
		) DEFAULT CHARSET=utf8");

            $this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_seller_payments` (
		`payment_id` int(11) NOT NULL AUTO_INCREMENT,
		`plan_id` int(11) NOT NULL,
		`seller_id` int(11) NOT NULL,
		`amount` int(11) NOT NULL DEFAULT '1',
		`payment_method` int(11) NOT NULL DEFAULT '1',
		`payment_status` int(1) NOT NULL DEFAULT '0',
		`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`payment_id`, `plan_id`, `seller_id`)
		) DEFAULT CHARSET=utf8");

            $this->_createSchemaEntry('2.1.0.0');
        }
	}
}
