<?php
class ModelModuleQuickcheckoutFields extends Model {
	/**
	 * Adds custom field
	 * @param int $data returns the field id
	 */
	public function addField($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "qchkout_fields SET `section` = '" . $this->db->escape($data['section']) . "', `field_type` = '" . $this->db->escape($data['field_type']) . "'");

        $field_id = $this->db->getLastId();

        //Set the first non-empty language
        $setLanguage = [];
        foreach ($data['field_description'] as $language_id => $value) {
            //Set the setLanguage if the current is not empty
            if($value['field_title']){
                $setLanguage = $value;
                break;
            }
        }

        foreach ($data['field_description'] as $language_id => $value) {
            if(!$value['field_title']){
                $value['field_title'] = $setLanguage;
            }

            $this->db->query("
                INSERT INTO " . DB_PREFIX . "qchkout_fields_description SET
                field_id = '" . (int)$field_id . "',
                language_id = '" . (int)$language_id . "',
                field_title = '" . $this->db->escape($value['field_title']) . "',
                field_error = '" . $this->db->escape($value['field_error']) . "',
                field_tooltip = '" . $this->db->escape($value['field_tooltip']) . "'
			");
        }

        if (isset($data['option_value'])) {
            foreach ($data['option_value'] as $option_value) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "qchkout_field_options SET field_id = '" . (int)$field_id . "', sort_order = '" . (int)$option_value['sort_order'] . "'");

                $option_value_id = $this->db->getLastId();

                foreach ($option_value['option_value_description'] as $language_id => $option_value_description) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "qchkout_field_options_description SET field_option_id = '" . (int)$option_value_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($option_value_description['title']) . "'");
                }
            }
        }

		return $field_id;
	}

	/**
	 * edits field
	 * @param  int $id field id
	 * @param  array $data form data for field
	 * @return null                none
	 */
	public function editField($id, $data)
	{
        $this->db->query("UPDATE " . DB_PREFIX . "qchkout_fields SET `section` = '" . $this->db->escape($data['section']) . "', `field_type` = '" . $this->db->escape($data['field_type']) . "' WHERE id = '" . (int)$id . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "qchkout_fields_description WHERE field_id = '" . (int)$id . "'");

        //Set the first non-empty language
        $setLanguage = [];
        foreach ($data['field_description'] as $language_id => $value) {
            //Set the setLanguage if the current is not empty
            if($value['field_title']){
                $setLanguage = $value;
                break;
            }
        }

        foreach ($data['field_description'] as $language_id => $value) {
            if(!$value['field_title']){
                $value['field_title'] = $setLanguage;
            }

            $this->db->query("
                INSERT INTO " . DB_PREFIX . "qchkout_fields_description SET
                field_id = '" . (int)$id . "',
                language_id = '" . (int)$language_id . "',
                field_title = '" . $this->db->escape($value['field_title']) . "',
                field_error = '" . $this->db->escape($value['field_error']) . "',
                field_tooltip = '" . $this->db->escape($value['field_tooltip']) . "'
			");
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "qchkout_field_options_description WHERE field_option_id IN (SELECT id FROM " . DB_PREFIX . "qchkout_field_options WHERE field_id = " . (int)$id .")");
        $this->db->query("DELETE FROM " . DB_PREFIX . "qchkout_field_options WHERE field_id = '" . (int)$id . "'");

        if (isset($data['option_value'])) {
            foreach ($data['option_value'] as $option_value) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "qchkout_field_options SET field_id = '" . (int)$id . "', sort_order = '" . (int)$option_value['sort_order'] . "'");

                $option_value_id = $this->db->getLastId();

                foreach ($option_value['option_value_description'] as $language_id => $option_value_description) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "qchkout_field_options_description SET field_option_id = '" . (int)$option_value_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($option_value_description['title']) . "'");
                }
            }
        }

        return true;
	}

    /**
     * get field options
     * @param  int $id field id
     * @return array
     */
    public function getOptionValueDescriptions($id) {
        $option_value_data = array();

        $option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "qchkout_field_options WHERE field_id = '" . (int)$id . "'");

        foreach ($option_value_query->rows as $option_value) {
            $option_value_description_data = array();

            $option_value_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "qchkout_field_options_description WHERE field_option_id = '" . (int)$option_value['id'] . "'");

            foreach ($option_value_description_query->rows as $option_value_description) {
                $option_value_description_data[$option_value_description['language_id']] = array('title' => $option_value_description['title']);
            }

            $option_value_data[] = array(
                'field_option_id'          => $option_value['field_option_id'],
                'option_value_description' => $option_value_description_data,
                'sort_order'               => $option_value['sort_order'],
                'valuable_type'            => $option_value['valuable_type'],
                'valuable_id'              => $option_value['valuable_id']
            );
        }

        usort($option_value_data, function($a, $b) {
            return $a['sort_order'] - $b['sort_order'];
        });

        return $option_value_data;
    }

	/**
	 * Deletes field
	 * @param  int $id field id
	 * @return null                none
	 */
	public function deleteField($id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "qchkout_fields WHERE id = '" . (int)$id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "qchkout_fields_description WHERE field_id = '" . (int)$id . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "qchkout_field_options_description WHERE field_option_id IN (SELECT id FROM " . DB_PREFIX . "qchkout_field_options WHERE field_id = " . (int)$id .")");
        $this->db->query("DELETE FROM " . DB_PREFIX . "qchkout_field_options WHERE field_id = '" . (int)$id . "'");

        //Delete the custom field from quickcheckout settings, so it will not have a record in each order
        $quickcheckout_settings = $this->config->get('quickcheckout');
        unset($quickcheckout_settings['option']['guest']['payment_address']['fields']['custom_'.$id]);
        unset($quickcheckout_settings['option']['register']['payment_address']['fields']['custom_'.$id]);
        unset($quickcheckout_settings['option']['logged']['payment_address']['fields']['custom_'.$id]);
        unset($quickcheckout_settings['step']['payment_address']['fields']['custom_'.$id]);

        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('quickcheckout', ['quickcheckout' => $quickcheckout_settings]);    
    }

	/**
	 * Fetches the content of field
	 * @param  int $id field id
	 * @return array                returns the content of field
	 */
	public function getField($id) {
		$field = array();
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "qchkout_fields WHERE id = '" . (int)$id . "'");
		if ($query->num_rows) {
            $field = $query->row;
		}

		return $field;
	}

    /**
     * Fetches all fields for a grid
     * @param  array $data
     * @param  array $filterData
     * @return array returns all fields
     */
	public function getFieldsDt($data = [], $filterData = []) {
		$query = $fields = [];
        $language_id = $this->config->get('config_language_id');

	    $fields = [
	      'qf.*',
          'qfd.field_title',
          'qfd.field_error',
          'qfd.field_tooltip'
	    ];

	    $fields = sprintf(
	          implode(',', $fields)
	    );

	    $query[] = 'SELECT ' . $fields . ' FROM `' . DB_PREFIX . 'qchkout_fields` qf';
        $query[] = 'LEFT JOIN `' . DB_PREFIX . 'qchkout_fields_description` qfd ON (qf.id = qfd.field_id)';

	    $query[] = 'WHERE qfd.language_id = '.(int)$language_id;

	    $total = $this->db->query(
	            'SELECT COUNT(*) AS totalData FROM ('.
	            str_replace($fields, '0 as fakeColumn', implode(' ', $query))
	            .') AS t'
	        )->row['totalData'];

	    if (isset($filterData['search'])) {

	      $filterData['search'] = $this->db->escape($filterData['search']);

	      $query[] = 'AND (';
            $query[] = "qfd.field_title LIKE '%{$filterData['search']}%'";
            $query[] = "OR qf.section LIKE '%{$filterData['search']}%'";
            $query[] = "OR qf.type LIKE '%{$filterData['search']}%'";
	      $query[] = ')';
	    }

	    $totalFiltered = $this->db->query(
	            'SELECT COUNT(*) AS totalData FROM ('.
	            str_replace($fields, '0 as fakeColumn', implode(' ', $query))
	            .') AS t'
        )->row['totalData'];

        $sort_data = array(
			'field_title'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$query[] = " ORDER BY " . $data['sort'];
		} else {
			$query[] = " ORDER BY qf.id";
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
     * Fetches all fields for checkout section
     * @return array returns all fields
     */
    public function getFields() {
        $query = $fields = [];
        $language_id = $this->config->get('config_language_id');

        $fields = [
            'qf.*',
            'qfd.field_title',
            'qfd.field_error',
            'qfd.field_tooltip'
        ];

        $fields = sprintf(
            implode(',', $fields)
        );

        $query[] = 'SELECT ' . $fields . ' FROM `' . DB_PREFIX . 'qchkout_fields` qf';
        $query[] = 'LEFT JOIN `' . DB_PREFIX . 'qchkout_fields_description` qfd ON (qf.id = qfd.field_id)';

        $query[] = 'WHERE qfd.language_id = '.(int)$language_id;

        $sort_data = array(
            'field_title'
        );

        $query[] = " ORDER BY qfd.field_title";

        $query = $this->db->query(implode(' ', $query));

        return $query->rows ?? [];
    }

	/**
	 * Fetches field description
	 * @param  int  $id
	 * @return array       field data
	 */
    public function getFieldDescription($id) {
        $field_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "qchkout_fields_description WHERE field_id = '" . (int)$id . "'");

        foreach ($query->rows as $result) {
            $field_data[$result['language_id']] = array(
                'field_title'             => $result['field_title'],
                'field_error'     => $result['field_error'],
                'field_tooltip' => $result['field_tooltip']
            );
        }

        return $field_data;
    }

    /**
     * Get custom fields of an order
     * @param int $order_id
     * @param null $lang_id
     * @return array
     */
    public function getOrderCustomFields($order_id, $lang_id = null) {
        $field_data = array();
        $order_id = $order_id;
        /*
         * this logic is for get custom fields if the end user not add custom fields values
         * the custom fields values will be empty in this case so we will get the last order contain custom field values
         *
         * */
       
        $this->load->model('sale/order');
        $orderInfo = $this->model_sale_order->getOrder($order_id);


        if($orderInfo['customer_id']!=0){
            $order_id = $this->model_sale_order->getCustomerLastOrderIdWIthCustomFields($orderInfo);

        }


      
        if($lang_id == null)
            $lang_id = $this->config->get('config_language_id') ? (int)$this->config->get('config_language_id') : 1;

        $query = $this->db->query("SELECT ocf.field_id, ocf.value, ocf.section, qfd.field_title, qf.field_type FROM " . DB_PREFIX . "order_custom_fields ocf
                                   LEFT JOIN " . DB_PREFIX . "qchkout_fields qf ON (ocf.field_id = qf.id) 
                                   LEFT JOIN " . DB_PREFIX . "qchkout_fields_description qfd ON (ocf.field_id = qfd.field_id) 
                                   WHERE ocf.order_id = '" . (int)$order_id . "'
                                   AND qfd.language_id = ".$lang_id);

        if(!$query->num_rows){
            return false;
        }

        $skipTypes = ['label'];

        foreach ($query->rows as $field){
            if($field['field_type'] == 'checkbox')
                $field['value'] = ($field['value']) ? 'Yes' : 'No';

            if(in_array($field['field_type'], $skipTypes))
                continue;

            $field_data[$field['section']][] = $field;
        }

        return $field_data;
    }
}
