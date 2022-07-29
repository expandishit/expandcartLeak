<?php
class ModelSettingExtension extends Model {
    public function getInstalled($type) {
        $extension_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `type` = '" . $this->db->escape($type) . "'");

        foreach ($query->rows as $result) {
            $extension_data[] = $result['code'];
        }

        return $extension_data;
    }

    public function install($type, $code) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "extension SET `type` = '" . $this->db->escape($type) . "', `code` = '" . $this->db->escape($code) . "'");

        return $this->db->getLastId();
    }

    /**
     * Get application info.
     *
     * @param string $code
     *
     * return array|bool
     */
    public function getApplication($code)
    {
        $query = [];
        $query[] = 'SELECT * FROM appservice as ap';
        //$query[] = 'LEFT JOIN extension as e';
        //$query[] = 'ON ap.name=e.code';
        $query[] = 'WHERE ap.`name`="' . $code . '"';
        $query[] = 'AND ap.`type`=1';
        $data = $this->ecusersdb->query(implode(' ', $query));
        if ($data->num_rows) {
            return $data->row;
        }
        return false;
    }

    public function getApplicationExtension($code)
    {
        $query = [];
        $query[] = 'SELECT * FROM `extension`';
        $query[] = 'WHERE `extension`.`code`="' . $code . '"';
        $data = $this->db->query(implode(' ', $query));
        if ($data->num_rows) {
            return $data->row;
        }
        return false;
    }
    /**
     * Insert new trial.
     *
     * @param int $applicationId
     * @param int $extensionId
     * @param string $extensionCode
     *
     * return int
     */
    public function addTrial($applicationId, $extensionId, $extensionCode)
    {
        $query = $fields = [];
        $query[] = 'INSERT INTO applications_trial SET';
        $fields[] = 'application_id="' . $applicationId . '"';
        $fields[] = 'extension_id="' . $extensionId . '"';
        $fields[] = 'extension_code="' . $extensionCode . '"';
        $query[] = implode(',', $fields);
        $this->db->query(implode(' ', $query));
        return $this->db->getLastId();
    }

    public function uninstall($type, $code) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "extension WHERE `type` = '" . $this->db->escape($type) . "' AND `code` = '" . $this->db->escape($code) . "'");
    }

    public function sql($sql) {
        $query = '';

        foreach($lines as $line) {
            if ($line && (substr($line, 0, 2) != '--') && (substr($line, 0, 1) != '#')) {
                $query .= $line;

                if (preg_match('/;\s*$/', $line)) {
                    $query = str_replace("DROP TABLE IF EXISTS `oc_", "DROP TABLE IF EXISTS `" . $data['db_prefix'], $query);
                    $query = str_replace("CREATE TABLE `oc_", "CREATE TABLE `" . $data['db_prefix'], $query);
                    $query = str_replace("INSERT INTO `oc_", "INSERT INTO `" . $data['db_prefix'], $query);

                    $result = mysql_query($query, $connection);

                    if (!$result) {
                        die(mysql_error());
                    }

                    $query = '';
                }
            }
        }
    }

    /**
     * Get active Trial info.
     *
     * return array|bool
     */
    public function getActiveTrials()
    {
		//TO:DO |  later we need to add the max_trial_day with the app data itself 
		
        $query	 = [];
        $query[] = 'SELECT * FROM applications_trial';
        $query[] = 'WHERE ( created_at > (DATE(NOW()) - INTERVAL 7 DAY) and extension_code <> "lableb" )';
        $query[] = 'OR  ( created_at > (DATE(NOW()) - INTERVAL 14 DAY) and extension_code  = "lableb" )';
        
		$data = $this->db->query(implode(' ', $query));
        if ($data->num_rows) {
            return $data->rows;
        }
        return false;
    }

    /**
     * Get in-active Trial info.
     *
     * return array|bool
     */
    public function getInActiveTrials()
    {
		//TO:DO |  later we need to add the max_trial_day with the app data itself 
		
        $query   = [];
        $query[] = 'SELECT * FROM applications_trial';
        $query[] = 'WHERE ((created_at <= (DATE(NOW()) - INTERVAL 7 DAY) and extension_code <> "lableb")
						OR ( created_at <= (DATE(NOW()) - INTERVAL 14 DAY) and extension_code = "lableb"))';
		$query[] = 'AND deleted_at IS NULL';
        
		$data = $this->db->query(implode(' ', $query));
        if ($data->num_rows) {
            return $data->rows;
        }
        return false;
    }

    /**
     * Check if the givven extension is a trial by it's code
     *
     * @param string $extensionCode
     *
     * return bool
     */
    public function isTrial($extensionCode)
    {
        $query = [];
        $query[] = 'SELECT id FROM applications_trial';
        $query[] = 'WHERE extension_code="' . $this->db->escape($extensionCode) . '"';
        $data = $this->db->query(implode(' ', $query));
        if ($data->num_rows) {
            return true;
        }
        return false;
    }

    /**
     * Remove a trial using it's extension code
     *
     * @param string $extensionCode
     *
     * return void
     */
    public function removeTrial($extensionCode)
    {
        $query = [];
        $query[] = 'UPDATE applications_trial';
        $query[] = 'SET deleted_at=DATE(NOW())';
        $query[] = 'WHERE extension_code="' . $this->db->escape($extensionCode) . '"';
        $this->db->query(implode(' ', $query));
    }
}
?>
