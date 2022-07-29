<?php
class ModelModuleProductNotifyMe extends Model {
	/**
	 * Deletes notif request
	 * @param  int $id contains the md_product_notify_me id
	 * @return null                none
	 */
	public function deleteRequests($id, $is_list = false) {
	    if($is_list)
		    return $this->db->query("DELETE FROM " . DB_PREFIX . "md_product_notify_me WHERE id IN (" . $id . ")");

        return $this->db->query("DELETE FROM " . DB_PREFIX . "md_product_notify_me WHERE id = '" . (int)$id . "'");
	}

    /**
     * Deletes notified request
     */
    public function clearNotified() {
        return $this->db->query("DELETE FROM " . DB_PREFIX . "md_product_notify_me WHERE is_notified = '1'");
    }

    /**
     * Fetches notification products
     */
	public function getProductsDt($data = [], $filterData = []) {
        $language_id = $this->config->get('config_language_id') ?: 1;

		$query = [];

	    $query[] = 'SELECT pnm.id, p.product_id, pd.name as product_name, p.quantity, count(pnm.product_id) as request_count FROM `' . DB_PREFIX . 'md_product_notify_me` pnm';
        $query[] = 'LEFT JOIN `' . DB_PREFIX . 'product` p ON (p.product_id = pnm.product_id)';
        $query[] = 'LEFT JOIN `' . DB_PREFIX . 'product_description` pd ON (pd.product_id = pnm.product_id)';

	    $query[] = 'WHERE pd.language_id =\''. $language_id.'\' AND pnm.is_notified = 0 ';

	    $total = $this->db->query(
	            'SELECT COUNT(DISTINCT(product_id)) AS totalData FROM ('.
	            str_replace('*', '0 as fakeColumn', implode(' ', $query))
	            .') AS t'
	        )->row['totalData'];

	    if (isset($filterData['search'])) {

	      $filterData['search'] = $this->db->escape($filterData['search']);

	      $query[] = 'AND (';

	      if (trim($filterData['search'][0]) == '#') {
	          $id = preg_replace('/\#/', '', $filterData['search']);
	          $query[] = "id LIKE '%{$id}%'";
	      } else {
	          $query[] = "pd.name LIKE '%{$filterData['search']}%'";
	      }
	      $query[] = ')';
	    }

	    $totalFiltered = $this->db->query(
	            'SELECT COUNT(DISTINCT(product_id)) AS totalData FROM ('.
	            str_replace('*', '0 as fakeColumn', implode(' ', $query))
	            .') AS t'
        )->row['totalData'];

        $sort_data = array(
			'name',
			'status'
		);

        $query[] = 'GROUP BY pnm.product_id';

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$query[] = " ORDER BY " . $data['sort'];
		} else {
			$query[] = " ORDER BY id";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$query[] = " DESC";
		} else {
			$query[] = " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$query[] = " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query(implode(' ', $query));

	    return [
	      'data' => $query->rows,
	      'total' => $total,
	      'totalFiltered' => $totalFiltered
	    ];
	}

    /**
     * Fetches notification requests
     */
    public function getRequestsDt($data = [], $filterData = []) {
        $language_id = $this->config->get('config_language_id') ?: 1;

        $query = [];

        $query[] = 'SELECT pnm.*, pd.name as product_name, p.quantity FROM `' . DB_PREFIX . 'md_product_notify_me` pnm';
        $query[] = 'LEFT JOIN `' . DB_PREFIX . 'product` p ON (p.product_id = pnm.product_id)';
        $query[] = 'LEFT JOIN `' . DB_PREFIX . 'product_description` pd ON (pd.product_id = pnm.product_id)';

        $query[] = 'WHERE pd.language_id =\''. $language_id.'\'';

        $total = $this->db->query(
            'SELECT COUNT(*) AS totalData FROM ('.
            str_replace('*', 'id as fakeColumn', implode(' ', $query))
            .') AS t'
        )->row['totalData'];

        if (isset($filterData['search'])) {

            $filterData['search'] = $this->db->escape($filterData['search']);

            $query[] = 'AND (';

            if (trim($filterData['search'][0]) == '#') {
                $id = preg_replace('/\#/', '', $filterData['search']);
                $query[] = "id LIKE '%{$id}%'";
            } else {
                $query[] = "pd.name LIKE '%{$filterData['search']}%'";
                $query[] = "OR pnm.name LIKE '%{$filterData['search']}%'";
                $query[] = "OR pnm.email LIKE '%{$filterData['search']}%'";
                $query[] = "OR pnm.phone LIKE '%{$filterData['search']}%'";
            }
            $query[] = ')';
        }

        $totalFiltered = $this->db->query(
            'SELECT COUNT(*) AS totalData FROM ('.
            str_replace('*', 'id as fakeColumn', implode(' ', $query))
            .') AS t'
        )->row['totalData'];

        $sort_data = array(
            'name',
            'status'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $query[] = " ORDER BY " . $data['sort'];
        } else {
            $query[] = " ORDER BY id";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $query[] = " DESC";
        } else {
            $query[] = " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $query[] = " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query(implode(' ', $query));

        return [
            'data' => $query->rows,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        ];
    }

    /**
     * Fetches notification records
     */
    public function getEmails($ids, $col) {
        $language_id = $this->config->get('config_language_id') ?: 1;

        $query = [];

        $query[] = 'SELECT pnm.*, p.price, p.image as product_image, pd.name as product_name, p.quantity as product_quantity FROM `' . DB_PREFIX . 'md_product_notify_me` pnm';
        $query[] = 'LEFT JOIN `' . DB_PREFIX . 'product` p ON (p.product_id = pnm.product_id)';
        $query[] = 'LEFT JOIN `' . DB_PREFIX . 'product_description` pd ON (pd.product_id = pnm.product_id)';

        $query[] = 'WHERE pd.language_id =\''. $language_id.'\' AND pnm.'.$col.' IN ('.$ids.') AND pnm.is_notified = 0';

        $query = $this->db->query(implode(' ', $query));

        return $query->rows;
    }

    /**
     * Update request is notified status
     */
    public function updateNotifyStatus($item){
        if(isset($item['id']) && isset($item['product_id']) && isset($item['address'])){
            //Prevent duplication
            $this->db->query("DELETE FROM `" . DB_PREFIX . "md_product_notify_me` WHERE product_id = '".$item['product_id']."' AND email='".$item['address']."' AND is_notified = 1");

            $this->db->query("UPDATE `" . DB_PREFIX . "md_product_notify_me` SET is_notified = 1 WHERE id = ".$item['id']);
        }
    }

    /**
     * check if a product has requests
     * @param $product_id
     */
    public function productHasRequests($product_id){
        $rqs = $query = $this->db->query("SELECT count(id) as cnt FROM `" . DB_PREFIX . "md_product_notify_me` WHERE product_id='".$product_id."' AND is_notified = 0");

        if($rqs->num_rows && $rqs->row['cnt'] > 0)
            return true;
        return false;
    }

	public function getSettings()
    {
        return $this->config->get('product_notify_me');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1) {
            return true;
        }

        return false;
    }

    public function isAutoNotify()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['auto_notify'] == 1) {
            return true;
        }

        return false;
    }

	/**
    *   Install the required values for the application.
    *
    *   @return boolean whether successful or not.
    */
    public function install($store_id = 0)
    {
        try 
        {
            $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "md_product_notify_me` 
                    (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `customer_id` int(11) NULL DEFAULT 0,
                      `product_id` int(11) NOT NULL,
                      `name` varchar(191) NOT NULL,
                      `email` varchar(191) NOT NULL,
                      `phone` varchar(191) NOT NULL,
                      `is_notified` int(1) DEFAULT 0,
                      PRIMARY KEY (`id`),
                      UNIQUE KEY `cst_unique` (`product_id`,`email`,`is_notified`)
                    ) 
                    ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            $this->db->query($sql);

            return true;

        } 
        catch (Exception $e) 
        {
            return false;
        }
    }

    /**
    *   Remove the values from the database.
    *
    *   @return boolean whether successful or not.
    */
    public function uninstall($store_id = 0)
    {
        try
        {
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "md_product_notify_me`;");
            return true;
        } 
        catch (Exception $e)
        {
            return false;
        }
    }

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
            'product_notify_me', $inputs
        );

        return true;
    }
}
