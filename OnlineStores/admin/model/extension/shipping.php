<?php

class ModelExtensionShipping extends Model
{

    /**
     * the shipping methods table name.
     *
     * @var string
     */
    private $shippingMethodsTable = DB_PREFIX . 'shipping_methods';

    /**
     * the shipping methods description table name.
     *
     * @var string
     */
    private $shippingMethodsDescriptionTable = DB_PREFIX . 'shipping_methods_description';

    /**
     * Get installed shipping methods.
     *
     * @return array
     */
    public function getInstalled()
    {
        $extension_data = [];

        $query = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "extension WHERE `type` = 'shipping'"
        );

        foreach ($query->rows as $result) {
            $extension_data[] = $result['code'];
        }

        return $extension_data;
    }

    /**
     * Get enabled shipping methods.
     *
     * @return array
     */
    public function getEnabled($gateways = null)
    {
        if (!$gateways) {
            $gateways = $this->getShippingMethodsGrid([], $this->config->get('config_language_id'));
            $gateways = $gateways['data'];
        }

        $allowed = [];
        foreach ($gateways as $gateway) {

            $gateway['bundled'] = 1;

            $settings = $this->config->get($gateway['code']);

            if ($settings && is_array($settings)) {
                $status = $settings['status'];
            } else {
                $status = $this->config->get($gateway['code'] . '_status');
            }

            if ($status) {
                $allowed[] = $gateway;
            }
        }

        return $allowed;
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
     * Get all shipping methods
     *
     * @param array $data
     * @param integer $languageId
     *
     * @return array|void
     */
    public function getShippingMethodsGrid($data, $languageId)
    {
        $query = $fields = [];

        $fields[] = '*';
        $fields[] = 'sm.id as sm_id';

        $query[] = 'SELECT ' . implode(',', $fields) . ' FROM ' . $this->shippingMethodsTable . ' AS sm';
        $query[] = 'INNER JOIN ' . $this->shippingMethodsDescriptionTable . ' AS smd';
        $query[] = 'ON sm.id=smd.shipping_method_id';
        $query[] = 'WHERE smd.lang="' . $this->config->get('config_admin_language') . '"';
        $query[] = 'AND sm.code!="' . 'seller_based' . '"';

        $total = $this->getCount($query, $fields);

        if (isset($data['installed']) && in_array($data['installed'], [0, 1])) {
            $query[] = 'AND sm.status="' . $this->db->escape($data['installed']) . '"';
        }

        if (isset($data['types']) && count($data['types']) > 0) {
            foreach ($data['types'] as $key => &$type) {
                $type = htmlspecialchars($type);
            }
            $query[] = 'AND sm.type IN ("' . implode('", "', $data['types']) . '")';
        }

        if (STAGING_MODE != 1){
            $query[] = 'AND sm.published=1 ';
        }

        if (isset($data['lookup']) && mb_strlen($data['lookup']) > 0) {
            $words = explode(' ', $data['lookup']);
            foreach ($words as $word) {
                $query[] = 'AND (smd.title LIKE "%' .  $this->db->escape($word) . '%"';
                $query[] = 'OR smd.description LIKE "%' .  $this->db->escape($word) . '%")';
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

    public function getShippingMethodData($code)
    {
        $query = $fields = [];

        $fields[] = '*';
        $fields[] = 'sm.id as sm_id';

        $query[] = 'SELECT ' . implode(',', $fields) . ' FROM ' . $this->shippingMethodsTable . ' AS sm';
        $query[] = 'INNER JOIN ' . $this->shippingMethodsDescriptionTable . ' AS smd';
        $query[] = 'ON sm.id=smd.shipping_method_id';
        $query[] = 'WHERE smd.lang="' . $this->config->get('config_admin_language') . '"';
        $query[] = ' AND sm.code="' . $code . '"';

        $data = $this->ecusersdb->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

    public function getRecommendedShippingMethods($data=null, $languageId=null)
    {
        $registered_business = $this->config->get('registered_business');

        if($this->config->get('registered_business') == "Yes"){
            $registered_business=  1;
        }else if($registered_business != null){
            $registered_business = 0;
        }

        $country_code = $this->getCountryCode($this->config->get('config_country_id'));

        if ($country_code == null){
            $country_code="All";
        }

        $query = $fields = [];

        $fields[] = '*';
        $fields[] = 'CASE WHEN `supported_countries` IS NULL THEN "'.$country_code.'" ELSE `supported_countries` END as `scf` ';
        $fields[] = 'sm.id as sm_id';

        $query[] = 'SELECT ' . implode(',', $fields) . ' FROM ' . $this->shippingMethodsTable . ' AS sm';
        $query[] = 'INNER JOIN ' . $this->shippingMethodsDescriptionTable . ' AS smd';
        $query[] = 'ON sm.id=smd.shipping_method_id';
        $query[] = 'WHERE smd.lang="' . $this->config->get('config_admin_language') . '"';
        $query[] = 'AND type ="delivery_companies"';

        if (STAGING_MODE != 1){
            $query[] = 'AND sm.published=1 ';
        }

        if ($registered_business == 0 && !is_null($registered_business)){
            $query[] ='AND registered_business =0';
        }

        $query[] ='order by scf like "%'.$country_code.'%" desc';

        $query[] =',special_rate desc';
        $query[] =',smd.title asc LIMIT 4' ;

        $data = $this->ecusersdb->query(implode(' ', $query));

        if ($data->num_rows) {
            return [
                'data' => $data->rows,
            ];
        }

        return false;
    }

    public function getCountryCode($country_id) {

        $query = $this->db->query("SELECT iso_code_2 FROM "
            . DB_PREFIX . "`country`  WHERE `country_id` = '" .
            $country_id . "'");
        return $query->row['iso_code_2'];
    }

    /**
     * Get all shipping methods codes
     *
     * @return array|bool
     */
    public function getCodes()
    {
        $data = $this->ecusersdb->query('SELECT id, code FROM ' . $this->shippingMethodsTable);

        if ($data->num_rows) {
            return array_column($data->rows, 'code', 'id');
        }

        return false;
    }

    /**
     * Get all shipping methods types
     *
     * @return array|bool
     */
    public function getTypes()
    {
        $data = $this->ecusersdb->query(
            'SELECT type,COUNT(type) as c FROM ' . $this->shippingMethodsTable . ' GROUP BY type'
        );

        if ($data->num_rows) {
            return array_column($data->rows, 'c', 'type');
        }

        return false;
    }

    /**
     * Install new shipping method
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
        $query[] = 'INSERT INTO ' . $this->shippingMethodsTable . ' SET';
        $query[] = '`code` = "' . $this->db->escape($code) . '",';
        $query[] = '`image` = "' . $this->db->escape($image) . '",';
        $query[] = '`type` = "' . $this->db->escape($type) . '",';
        $query[] = '`status` = "' . $this->db->escape($status) . '"';


        $this->db->query(implode(' ', $query));

        $shippingMethodId = $this->db->getLastId();

        foreach ($descriptions as $key => $description) {
            $query = [];
            $query[] = 'INSERT INTO ' . $this->shippingMethodsDescriptionTable . ' SET';
            $query[] = '`title` = "' . $this->db->escape($description['title']) . '",';
            $query[] = '`description` = "' . $this->db->escape($description['description']) . '",';
            $query[] = '`image_alt` = "' . $this->db->escape($description['image_alt']) . '",';
            $query[] = '`shipping_method_id` = "' . $shippingMethodId . '",';
            $query[] = '`language_id` = "' . $key . '",';

            $this->db->query(implode(' ', $query));
        }
    }

    /**
     * Uninstall shipping method, this will remove from both shipping methods and descriptions table
     *
     * @param string $code
     *
     * @return void
     */
    public function uninstall($code)
    {
        $this->db->query(
            'DELETE FROM ' . $this->shippingMethodsTable . ' WHERE `code`="' . $this->db->escape($code) . '"'
        );
    }

    /**
     * Delete shipping method settings using the setting key.
     *
     * @param string $key
     *
     * return void
     */
    public function deleteSettings($key)
    {
        $query = [];

        $query[] = 'DELETE FROM setting';
        $query[] = 'WHERE (`group`="shipping" or `group`="' . $key . '")';
        $query[] = 'AND `key`="' . $key . '"';

        $this->db->query(implode(' ', $query));

        //update user shipping gateways
        $this->load->model('setting/setting');
        $this->config->set($key,'');
        $this->model_setting_setting->updateTrackShipping();
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
     * Check if shipping extension is installed
     *
     * @param string $code
     *
     * @return bool
     */
    public function isInstalled($code) : bool
    {
        $data = $this->db->query(
            vsprintf('SELECT 1 FROM `extension` WHERE `type` = "%s" AND `code` = "%s"', [
                'shipping',
                $code
            ])
        );

        if ($data->num_rows > 0) {
            return true;
        }

        return false;
    }

    /**
     * Get shipping method title in specific language
     */
    public function getShippingTitleInLang($shipping_code, $language_code='en')
    {
        $query = $this->ecusersdb->query("SELECT shipping_methods.code, shipping_methods_description.title FROM " . DB_PREFIX . "shipping_methods INNER JOIN shipping_methods_description ON shipping_methods.id = shipping_methods_description.shipping_method_id WHERE  shipping_methods.code='" . (string)$shipping_code . "' AND shipping_methods_description.lang = '" .$this->db->escape($language_code)  . "'");
        return $query->row;
    }


    /**
     * Get installed shipping methods title in specific language
     */
    public function getInstalledShippingTitleInLang($language_code='en')
    {
        $this->load->model('setting/extension');
        $extensions = $this->model_setting_extension->getInstalled('shipping');
        foreach ($extensions as $key => $value) {
            // determine if the extension is installed or not.
            $settings = $this->config->get($value);
            $status = $this->config->get($value . '_status');

            if ($settings == null && $status == null)
                unset($extensions[$key]);
        }
        $query = $this->ecusersdb->query("SELECT shipping_methods_description.title  FROM " . DB_PREFIX . "shipping_methods INNER JOIN shipping_methods_description ON shipping_methods.id = shipping_methods_description.shipping_method_id WHERE  shipping_methods.code IN ('".implode("','", $extensions)."') AND shipping_methods_description.lang = '" .$this->db->escape($language_code)  . "'");
        $extensions_title = [];
        foreach($query->rows as $row)
            $extensions_title[]=$row['title'];
        return $extensions_title;
    }

}