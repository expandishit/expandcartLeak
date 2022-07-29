<?php

class ModelExtensionPayment extends Model
{

    /**
     * the payment methods table name
     *
     * @var string
     */
    private $paymentMethodsTable = DB_PREFIX . 'payment_methods';

    /**
     * the payment methods description table name
     *
     * @var string
     */
    private $paymentMethodsDescriptionTable = DB_PREFIX . 'payment_methods_description';

    /**
     * @deprecated
     */
    public function getInstalled($type)
    {
        $extension_data = array();

        $query = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "extension WHERE `type` = '" . $this->db->escape($type) . "'"
        );

        foreach ($query->rows as $result) {
            $extension_data[] = $result['code'];
        }

        return $extension_data;
    }

    /**
     * a helper method to get the count from the curreny query
     *
     * @param array $query
     * @param array $fields
     *
     * @return integer
     */
    public function getCount($query, $fields)
    {
        $query = str_replace(
            implode(',', $fields),
            'COUNT(*) as __count',
            implode(' ', $query)
        );

        $data = $this->ecusersdb->query($query);

        return $data->row['__count'];
    }

    /**
     * Get all payment methods
     *
     * @param array $data
     * @param integer $languageId
     *
     * @return array|void
     */
    public function getPaymentMethodsGrid($data, $languageId)
    {
        $query = $fields = [];

        $fields[] = '*';
        $fields[] = 'pm.id as pm_id';

        $query[] = 'SELECT ' . implode(',', $fields) . ' FROM ' . DB_PREFIX . 'payment_methods AS pm';
        $query[] = 'INNER JOIN ' . DB_PREFIX . 'payment_methods_description AS pmd';
        $query[] = 'ON pm.id=pmd.payment_method_id';
        $query[] = 'WHERE pmd.lang="' . $this->config->get('config_admin_language') . '"';
		
		//hide expandPay 
        $query[] = 'AND pm.code NOT IN ("expandpay", "paypal")';

        $total = $this->getCount($query, $fields);

        if (isset($data['installed']) && in_array($data['installed'], [0, 1])) {
            $query[] = 'AND pm.status="' . $this->db->escape($data['installed']) . '"';
        }

        if (STAGING_MODE != 1){
            $query[] = 'AND pm.published=1';
        }

        if (isset($data['types']) && count($data['types']) > 0) {
            foreach ($data['types'] as $key => &$type) {
                $type = htmlspecialchars($type);
            }
            $query[] = 'AND pm.type IN ("' . implode('", "', $data['types']) . '")';
        }

        if (isset($data['lookup']) && mb_strlen($data['lookup']) > 0) {
            $words = explode(' ', $data['lookup']);
            foreach ($words as $word) {
                $query[] = 'AND (pmd.title LIKE "%' . $this->db->escape($word) . '%"';
                $query[] = 'OR pmd.description LIKE "%' . $this->db->escape($word) . '%")';
            }
        }

        $totalFiltered = $this->getCount($query, $fields);

        if (isset($data['start']) && isset($data['limit'])) {
            $query[] = 'LIMIT ' . $data['start'] . ', ' . $data['limit'];
        }

        $data = $this->ecusersdb->query(implode(' ', $query));

        if ($data->num_rows) {
            return [
                'data' => $data->rows,
                'total' => $total,
                'totalFiltered' => $totalFiltered,
            ];
        }

        return false;
    }

    public function getPaymentMethodData($code)
    {
        $query = $fields = [];

        $fields[] = '*';
        $fields[] = 'pm.id as pm_id';

        $query[] = 'SELECT ' . implode(',', $fields) . ' FROM ' . DB_PREFIX . 'payment_methods AS pm';
        $query[] = 'INNER JOIN ' . DB_PREFIX . 'payment_methods_description AS pmd';
        $query[] = 'ON pm.id=pmd.payment_method_id';
        $query[] = 'WHERE pmd.lang="' . $this->config->get('config_admin_language') . '"';
        $query[] = ' AND pm.code="' . $code . '"';

        $data = $this->ecusersdb->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

    /**
     * Get recommended payment methods
     *
     * @return array|bool
     */
    public function getRecommendedPaymentMethods()
    {
        $registered_business =$this->config->get('registered_business');

        if($registered_business == "Yes"){
            $registered_business = 1;
        }else if($registered_business != null){
            $registered_business = 0;
        }

        $query = $fields = [];

        $country_code = $this->getCountryCode($this->config->get('config_country_id'));

        if ($country_code == null){
            $country_code="All";
        }

        $fields[] = '*';
        $fields[] = 'CASE WHEN `supported_countries` IS NULL THEN "'.$country_code.'" ELSE `supported_countries` END as `scf` ';
        $fields[] = 'pm.id as pm_id';

        $query[] = 'SELECT ' . implode(',', $fields) . ' FROM ' . DB_PREFIX.'payment_methods' . ' AS pm';
        $query[] = 'INNER JOIN ' . DB_PREFIX.'payment_methods_description' . ' AS pmd';
        $query[] = 'ON pm.id=pmd.payment_method_id';
        $query[] = 'WHERE pmd.lang="' . $this->config->get('config_admin_language') . '"';
        $query[] = 'AND type <> "offline_methods"';

		//hide expandPay 
        $query[] = 'AND pm.code NOT IN ("expandpay", "paypal")';
		
        if ($registered_business == 0 && !is_null($registered_business)){
            $query[] ='AND registered_business =0';
        }

        if (STAGING_MODE != 1){
            $query[] = 'AND pm.published=1';
        }

        $query[] ='order by scf like "%'.$country_code.'%" desc';

        $query[] =',special_rate desc';

        $query[] =',pmd.title asc LIMIT 4 ' ;

        $data = $this->ecusersdb->query(implode(' ', $query));

        if ($data->num_rows) {
            return [
                'data' => $data->rows,
            ];
        }

        return false;
    }

    /**
     * Get all payment methods codes
     *
     * @return array|bool
     */
    public function getCodes()
    {
        $query="";
        if (STAGING_MODE != 1){
            $query = ' WHERE published=1';
        }

        $data = $this->ecusersdb->query('SELECT id, code FROM ' . $this->paymentMethodsTable. $query );

        if ($data->num_rows) {
            return array_column($data->rows, 'code', 'id');
        }

        return false;
    }

    /**
     * Get all payment methods types
     *
     * @return array|bool
     */
    public function getTypes()
    {
        $data = $this->ecusersdb->query(
            'SELECT type,COUNT(type) as c FROM ' . $this->paymentMethodsTable . ' GROUP BY type'
        );

        if ($data->num_rows) {
            return array_column($data->rows, 'c', 'type');
        }

        return false;
    }

    /**
     * Install new payment method
     *
     * @param string $code
     * @param string $image
     * @param string $type
     * @param integer $status
     * @param array $description
     *
     * @return void
     */
    public function install($code, $image, $type, $status, $description)
    {
        $query = [];
        $query[] = 'INSERT INTO ' . $this->paymentMethodsTable . ' SET';
        $query[] = '`code` = "' . $this->db->escape($code) . '",';
        $query[] = '`image` = "' . $this->db->escape($image) . '",';
        $query[] = '`type` = "' . $this->db->escape($type) . '",';
        $query[] = '`status` = "' . $this->db->escape($status) . '"';


        $this->db->query(implode(' ', $query));

        $paymentMethodId = $this->db->getLastId();

        foreach ($descriptions as $key => $description) {
            $query = [];
            $query[] = 'INSERT INTO ' . $this->paymentMethodsDescriptionTable . ' SET';
            $query[] = '`title` = "' . $this->db->escape($description['title']) . '",';
            $query[] = '`description` = "' . $this->db->escape($description['description']) . '",';
            $query[] = '`image_alt` = "' . $this->db->escape($description['image_alt']) . '",';
            $query[] = '`payment_method_id` = "' . $paymentMethodId . '",';
            $query[] = '`language_id` = "' . $key . '",';

            $this->db->query(implode(' ', $query));
        }
    }

    /**
     * Uninstall payment method, this will remove from both payment methods and descriptions table
     *
     * @param string $code
     *
     * @return void
     */
    public function uninstall($code)
    {
        $this->db->query(
            'DELETE FROM ' . $this->paymentMethodsTable . ' WHERE `code`="' . $this->db->escape($code) . '"'
        );
    }

    /**
     * Delete payment method settings using the setting key.
     *
     * @param string $key
     *
     * return void
     */
    public function deleteSettings($key)
    {
        $query = [];

        $query[] = 'DELETE FROM setting';
        $query[] = 'WHERE (`group`="payment"';
        $query[] = 'AND `key`="' . $key . '")';
        $query[] = 'OR (`group`="'. $key . '")';

        $this->db->query(implode(' ', $query));
    }

    /**
     * @deprecated
     */
    public function sql($sql)
    {
        $query = '';

        foreach ($lines as $line) {
            if ($line && (substr($line, 0, 2) != '--') && (substr($line, 0, 1) != '#')) {
                $query .= $line;

                if (preg_match('/;\s*$/', $line)) {
                    $query = str_replace(
                        "DROP TABLE IF EXISTS `oc_", "DROP TABLE IF EXISTS `" . $data['db_prefix'],
                        $query
                    );
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
     * Check if payment extension is installed
     *
     * @param string $code
     *
     * @return bool
     */
    public function isInstalled($code) : bool
    {
        $data = $this->db->query(
            vsprintf('SELECT 1 FROM `extension` WHERE `type` = "%s" AND `code` = "%s"', [
                'payment',
                $code
            ])
        );

        if ($data->num_rows > 0) {
            return true;
        }

        return false;
    }

    /**
     * Get payment methods for dropdown
     */
    public function getPaymentMethods()
    {
        $query = $this->ecusersdb->query("SELECT payment_methods.code, payment_methods_description.title FROM " . DB_PREFIX . "payment_methods LEFT JOIN payment_methods_description ON payment_methods.id = payment_methods_description.payment_method_id WHERE  payment_methods.published=1 AND payment_methods_description.lang = '" . $this->config->get('config_admin_language') . "'");
        return $query->rows;
    }

    public function getCountryCode($country_id) {

        $query = $this->db->query("SELECT iso_code_2 FROM "
            . DB_PREFIX . "`country`  WHERE `country_id` = '" .
            $country_id . "'");
        return $query->row['iso_code_2'];
    }

    /**
     * Select payment method useing it's code.
     * this method select the payment method from the master database.
     *
     * @param string $code
     *
     * @return array|bool
     */
    public function selectByCode(string $code)
    {
        $query = $columns = [];
        $query[] = 'SELECT * FROM %s WHERE `code`="%s"';
        $data = $this->ecusersdb->query(vsprintf(implode(' ', $query), [
            $this->paymentMethodsTable,
            $code
        ]));

        if ($data->num_rows > 0) {
            return $data->row;
        }

        return false;
    }

    /**
     * Get payment method title in specific language
     */
    public function getPaymentTitleInLang($payment_code, $language_code='en')
    {
        $query = $this->ecusersdb->query("SELECT payment_methods.code, payment_methods_description.title FROM " . DB_PREFIX . "payment_methods INNER JOIN payment_methods_description ON payment_methods.id = payment_methods_description.payment_method_id WHERE  payment_methods.code='" . (string)$payment_code . "' AND payment_methods_description.lang = '" .$this->db->escape($language_code)  . "'");
        return $query->row;
    }

    /**
     * Get  installed payment methods title in specific language
     */
    public function getInstalledPaymentTitleInLang( $language_code='en')
    {
        $this->load->model('setting/extension');
        $extensions = $this->model_setting_extension->getInstalled('payment');
        foreach ($extensions as $key => $value) {
            // determine if the extension is installed or not.
            $settings = $this->config->get($value);
            $status = $this->config->get($value . '_status');
            if ($settings == null && $status == null)
                unset($extensions[$key]);
        }
        $query = $this->ecusersdb->query("SELECT payment_methods_description.title FROM " . DB_PREFIX . "payment_methods INNER JOIN payment_methods_description ON payment_methods.id = payment_methods_description.payment_method_id WHERE  payment_methods.code IN ('".implode("','", $extensions)."')  AND payment_methods_description.lang = '" .$this->db->escape($language_code)  . "'");
        $extensions_title = [];
        foreach($query->rows as $row)
            $extensions_title[]=$row['title'];
        return $extensions_title;
    }
}