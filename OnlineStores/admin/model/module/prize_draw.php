<?php

class ModelModulePrizeDraw extends Model
{
    /**
     * update the module settings.
     *
     * @param array $inputs
     *
     * @return bool|\Exception
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->editSetting(
            'prize_draw', $inputs
        );

        return true;
    }

    public function install()
    {
        $query = "CREATE TABLE IF NOT EXISTS `" .DB_PREFIX. "prdw_prize_draw` (
            `id` int(10) NOT NULL AUTO_INCREMENT,
            `image` text NOT NULL,
            `start_date` Date NULL,
            `end_date` Date NULL,
            `status` TINYINT default '0',
            `dates_status` TINYINT default '0',
            `consumed` int(10) NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->db->query($query);

        $query = "CREATE TABLE `" .DB_PREFIX. "prdw_prize_draw_description` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `language_id` int(11) NOT NULL,
                  `prize_draw_id` int(11) NOT NULL,
                  `title` varchar(255) NOT NULL,
                  `short_description` text NOT NULL,
                  `description` text NOT NULL,
                  PRIMARY KEY (`id`,`language_id`),
                  KEY `title` (`title`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->db->query($query);

        $query = "CREATE TABLE `" .DB_PREFIX. "prdw_customer_to_prize` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `customer_id` int(11) NOT NULL,
                  `prize_draw_id` int(11) NOT NULL,
                  `product_id` int(11) NOT NULL,
                  `order_id` int(11) NOT NULL,
                  `code` varchar(50) NOT NULL,
                  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->db->query($query);

        $query = "CREATE TABLE `" .DB_PREFIX. "prdw_product_to_prize` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `prize_draw_id` int(11) NOT NULL,
                  `product_id` int(11) NOT NULL,
                  PRIMARY KEY (`id`),
                  INDEX idx1 (`prize_draw_id`, `product_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->db->query($query);

        //ALTER TABLE `prdw_customer_to_prize` add order_id int(10) NOT NULL AFTER product_id;
        //ALTER TABLE `prdw_prize_draw` add dates_status tinyint(0) default 0 AFTER status;
    }

    public function uninstall()
    {
        $query = "DROP TABLE IF EXISTS `" .DB_PREFIX. "prdw_prize_draw`";
        $this->db->query($query);

        $query = "DROP TABLE IF EXISTS `" .DB_PREFIX. "prdw_prize_draw_description`";
        $this->db->query($query);

        $query = "DROP TABLE IF EXISTS `" .DB_PREFIX. "prdw_customer_to_prize`";
        $this->db->query($query);

        $query = "DROP TABLE IF EXISTS `" .DB_PREFIX. "prdw_product_to_prize`";
        $this->db->query($query);
    }

    public function getSettings()
    {
        return $this->config->get('prize_draw_module');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1) {
            return true;
        }

        return false;
    }

    public function getPrizes() {
        $today = date('Y-m-d');

        $query = $this->db->query("SELECT p.*, pd.title FROM " . DB_PREFIX . "prdw_prize_draw p LEFT JOIN " . DB_PREFIX . "prdw_prize_draw_description pd on (p.id = pd.prize_draw_id) WHERE pd.language_id = '" . (int) $this->config->get('config_language_id') . "' AND p.start_date <= '" . $today . "' AND end_date >= '" . $today . "' AND status=1");

        return $query->rows;
    }

    public function getPrize($prize_id) {

        $prize_description_data = array();

        $query = $this->db->query("SELECT p.*,prd.title FROM " . DB_PREFIX . "prdw_prize_draw p LEFT JOIN " . DB_PREFIX . "prdw_prize_draw_description prd ON (p.id = prd.prize_draw_id)  WHERE p.id = '" . (int)$prize_id . "' AND prd.language_id = '" . (int) $this->config->get('config_language_id') . "'");

        $prize_description_data = $query->row;

        /*$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "prdw_prize_draw_description WHERE prize_draw_id = '" . (int)$prize_id . "'");
        
        foreach ($query->rows as $result) {
            $prize_description_data[$result['language_id']] = array(
                'title'             => $result['title'],
                'short_description'             => $result['short_description'],
                'description'      => $result['description']
            );
        }*/
        
        return $prize_description_data;
    }

    public function addPrize($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "prdw_prize_draw SET status = '" . (int)$data['status'] . "', dates_status = '" . (int)$data['dates_status'] . "', `start_date` = '" . $data['start_date'] . "', `end_date` = '" . $data['start_date'] . "'");

        $prize_id = $this->db->getLastId();

        if ( isset($data['image']) && !empty($data['image']) )
        {
            $this->db->query("UPDATE " . DB_PREFIX . "prdw_prize_draw SET image = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "' WHERE id = '" . (int)$prize_id . "'");
        }

        foreach ($data['prize_description'] as $language_id => $value) {
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "prdw_prize_draw_description SET
                prize_draw_id = '" . (int)$prize_id . "',
                language_id = '" . (int)$language_id . "',
                title = '" . $this->db->escape($value['title']) . "',
                short_description = '" . $this->db->escape($value['short_description']) . "',
                description = '" . $this->db->escape($value['description']) . "'
			");
        }

        if(isset($data['products']) && count($data['products']) > 0){
            foreach($data['products'] as $product){
                $this->db->query("INSERT INTO " . DB_PREFIX . "prdw_product_to_prize SET prize_draw_id = '" . (int)$prize_id . "', product_id = '" . (int)$product . "'");
            }
        }
    }

    public function editPrize($prize_id, $data)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "prdw_prize_draw SET status = '" . (int)$data['status'] . "', dates_status = '" . (int)$data['dates_status'] . "', `start_date` = '" . $data['start_date'] . "', `end_date` = '" . $data['end_date'] . "' WHERE id = '" . (int)$prize_id . "'");

        if (isset($data['image'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "prdw_prize_draw SET image = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "' WHERE id = '" . (int)$prize_id . "'");
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "prdw_prize_draw_description WHERE prize_draw_id = '" . (int)$prize_id . "'");

        foreach ($data['prize_description'] as $language_id => $value) {
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "prdw_prize_draw_description SET
                prize_draw_id = '" . (int)$prize_id . "',
                language_id = '" . (int)$language_id . "',
                title = '" . $this->db->escape($value['title']) . "',
                short_description = '" . $this->db->escape($value['short_description']) . "',
                description = '" . $this->db->escape($value['description']) . "'
			");
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "prdw_product_to_prize WHERE prize_draw_id = '" . (int)$prize_id . "'");

        if(isset($data['products']) && count($data['products']) > 0){
            foreach($data['products'] as $product){
                $this->db->query("INSERT INTO " . DB_PREFIX . "prdw_product_to_prize SET prize_draw_id = '" . (int)$prize_id . "', product_id = '" . (int)$product . "'");
            }
        }
    }

    public function getPrizeDescriptions($prize_id) {
        $prize_description_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "prdw_prize_draw_description WHERE prize_draw_id = '" . (int)$prize_id . "'");

        foreach ($query->rows as $result) {
            $prize_description_data[$result['language_id']] = array(
                'title'             => $result['title'],
                'short_description' => $result['short_description'],
                'description'      => $result['description']
            );
        }

        return $prize_description_data;
    }

    public function dtHandler($start=0, $length=10, $search = null, $orderColumn="title", $orderType="ASC")
    {

        $lang_id = (int) $this->config->get('config_language_id');

        $total = $this->db->query("SELECT * FROM " . DB_PREFIX . "prdw_prize_draw_description WHERE language_id='${lang_id}'")->num_rows;

        $fields = "c.*, cd1.title AS title";
        $queryString  = [];
        $queryString[] = "SELECT {$fields} FROM " . DB_PREFIX . "prdw_prize_draw c";
        $queryString[] = "LEFT JOIN " . DB_PREFIX . "prdw_prize_draw_description cd1 ON (c.id = cd1.prize_draw_id)";


        $where = "";
        $queryString[] = "WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        // $totalFiltered = $this->db->query(str_replace($fields, 'count(*) as dc', implode(' ', $queryString)))->row['dc'];

        if (!empty($search)) {
            $where .= "(cd1.title LIKE '%" . $this->db->escape($search) . "%')";
            $queryString[] = "AND " . $where;
        }

        //$queryString[] = "GROUP BY c.id";
        $queryString[] = " ORDER by {$orderColumn} {$orderType}";

        $totalFiltered = $this->db->query(implode(' ', $queryString))->num_rows;

        if($length != -1) {
            $queryString[] = " LIMIT " . $start . ", " . $length;
        }

        $data = array (
            'data' => $this->db->query(implode(' ', $queryString))->rows,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        );

        return $data;
    }

    /**
     * Update prize status.
     *
     * @param int $id
     * @param int status
     *
     * @return void
     */
    public function updatePrizeStatus($id, $status)
    {
        $query = [];

        $query[] = 'UPDATE ' . DB_PREFIX . 'prdw_prize_draw SET';
        $query[] = 'status=' . $this->db->escape($status);
        $query[] = 'WHERE id="' . $this->db->escape($id) . '"';

        $this->db->query(implode(' ', $query));
    }

    public function deletePrize($prize_id) {
        $query = $this->db->query("SELECT id FROM " . DB_PREFIX . "prdw_product_to_prize  WHERE prize_draw_id = '" . (int)$prize_id . "'");

        if(!$query->num_rows){
            $this->db->query("DELETE FROM " . DB_PREFIX . "prdw_prize_draw WHERE id = '" . (int)$prize_id . "'");
            $this->db->query("DELETE FROM " . DB_PREFIX . "prdw_prize_draw_description WHERE prize_draw_id = '" . (int)$prize_id . "'");

            return true;
        }

        return false;
    }

    // assigns product to prize
    /*public function assignProduct($prize_id, $product_id) {
        $prize_product = $this->db->query("SELECT id FROM " . DB_PREFIX . "prdw_product_to_prize WHERE prize_draw_id = '" . (int)$prize_id . "' AND product_id = '" . (int)$product_id . "'");

        if ($prize_product->num_rows == 0) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "prdw_product_to_prize SET prize_draw_id = '" . (int)$prize_id . "', product_id = '" . (int)$product_id . "'");
        }
    }*/
    public function getPrizeProducts($prize_id) {
        $query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "prdw_product_to_prize  WHERE prize_draw_id = '" . (int)$prize_id . "'");
        $products = $query->rows;

        return $products;
    }

    public function getProductPrize($product_id) {
        $today = date('Y-m-d');

        $query = $this->db->query("SELECT prize_draw_id FROM " . DB_PREFIX . "prdw_product_to_prize  WHERE product_id = '" . (int)$product_id . "'");
        $prize_draw = $query->row;

        if(isset($prize_draw['prize_draw_id'])){
            $query = $this->db->query("SELECT p.id, pd.title FROM " . DB_PREFIX . "prdw_prize_draw p LEFT JOIN " . DB_PREFIX . "prdw_prize_draw_description pd on (p.id = pd.prize_draw_id)  WHERE pd.language_id = '" . (int) $this->config->get('config_language_id') . "' AND p.id = '" . (int)$prize_draw['prize_draw_id'] . "' AND p.start_date <= '" . $today . "' AND end_date >= '" . $today . "' AND status=1");
            return $query->row;
        }

        return false;
    }

    public function getProducts($prize_id = 0)
    {

        $lang_id = $this->config->get('config_language_id') ? (int)$this->config->get('config_language_id') : 1;

        $sql = "SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";
        $sql .= " WHERE pd.language_id = '{$lang_id}'";
        if($prize_id)
            $sql .= " AND p.product_id NOT IN (SELECT product_id from " . DB_PREFIX . "prdw_product_to_prize WHERE prize_draw_id != ".$prize_id.")";
        else
            $sql .= " AND p.product_id NOT IN (SELECT product_id from " . DB_PREFIX . "prdw_product_to_prize)";
        $sql .= " GROUP BY p.product_id";
        $sql .= " ORDER BY pd.name";
        $sql .= " ASC";


        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function dtCstHandler($prize_id, $start=0, $length=10, $search = null, $orderColumn="name", $orderType="ASC")
    {
        $lang_id = (int) $this->config->get('config_language_id');

        $total = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "prdw_customer_to_prize  WHERE prize_draw_id = '" . (int)$prize_id . "'");

        $fields = "pc.*, CONCAT(cst.firstname, cst.lastname) as name, cst.email, cst.telephone as phone, pd.name as product";
        $queryString  = [];
        $queryString[] = "SELECT {$fields} FROM " . DB_PREFIX . "prdw_customer_to_prize pc";
        $queryString[] = "LEFT JOIN " . DB_PREFIX . "customer cst ON (pc.customer_id = cst.customer_id)";
        $queryString[] = "LEFT JOIN " . DB_PREFIX . "product_description pd ON (pc.product_id = pd.product_id)";

        $where = "";
        $queryString[] = "WHERE pd.language_id = ".(int) $this->config->get('config_language_id')." AND pc.prize_draw_id = '" . (int)$prize_id . "' ";

        // $totalFiltered = $this->db->query(str_replace($fields, 'count(*) as dc', implode(' ', $queryString)))->row['dc'];

        if (!empty($search)) {
            $where .= "(cst.name LIKE '%" . $this->db->escape($search) . "%')";
            $queryString[] = "AND " . $where;
        }

        //$queryString[] = "GROUP BY c.id";
        $queryString[] = " ORDER by {$orderColumn} {$orderType}";

        $totalFiltered = $this->db->query(implode(' ', $queryString))->num_rows;

        if($length != -1) {
            $queryString[] = " LIMIT " . $start . ", " . $length;
        }

        $data = array (
            'data' => $this->db->query(implode(' ', $queryString))->rows,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        );

        return $data;
    }

    public function getCard($id, $target){
        if($target == 'customer'){
            $where = " AND pc.id = '" . (int)$id . "'";
        }else if($target == 'prize'){
            $where = " AND pc.prize_draw_id = '" . (int)$id . "'";
        }

        $query = $this->db->query("SELECT pc.code, CONCAT(c.firstname, c.lastname) as name, c.email, c.telephone, prd.title as title FROM " . DB_PREFIX . "prdw_customer_to_prize pc 
                                   LEFT JOIN customer c ON (pc.customer_id = c.customer_id)
                                   LEFT JOIN prdw_prize_draw_description prd ON (prd.prize_draw_id = pc.prize_draw_id) 
                                   WHERE prd.language_id=".(int) $this->config->get('config_language_id').$where);

        return $query->rows;
    }
}
