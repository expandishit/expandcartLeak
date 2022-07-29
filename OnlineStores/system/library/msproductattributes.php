<?php
class msProductAttributes extends Model {
	public function getAttributes($data = array(), $sort = array()) {
		$sql = "SELECT
					SQL_CALC_FOUND_ROWS
					*,
					(SELECT agd.name FROM " . DB_PREFIX . "attribute_group_description agd WHERE agd.attribute_group_id = a.attribute_group_id AND 
					agd.language_id = '".  (int)$this->config->get('config_language_id') ."') AS attribute_group
				FROM `" . DB_PREFIX . "attribute` a
				LEFT JOIN " . DB_PREFIX . "attribute_description ad
					ON (a.attribute_id = ad.attribute_id)
				WHERE 1 = 1 "
				. (isset($data['attribute_id']) ? " AND a.attribute_id =  " .  (int)$data['attribute_id'] : '')
				. (isset($data['language_id']) ? " AND ad.language_id =  " .  (int)$data['language_id'] : " AND ad.language_id =  " .  (int)$this->config->get('config_language_id'))
				. (isset($sort['order_by']) ? " ORDER BY {$sort['order_by']} {$sort['order_way']}" : '')
				. (isset($sort['limit']) ? " LIMIT ".(int)$sort['offset'].', '.(int)($sort['limit']) : '');

		$res = $this->db->query($sql);

		$total = $this->db->query("SELECT FOUND_ROWS() as total");
		if ($res->rows) $res->rows[0]['total_rows'] = $total->row['total'];

		return ($res->num_rows == 1 && isset($data['single']) ? $res->row : $res->rows);
	}
    public function getAttributeValues($data) {

        $attribute_value_data = array();
        $sql = "SELECT *
			FROM " . DB_PREFIX . "product_attribute 
			WHERE attribute_id = " . (int)$data['attribute_id']
            .((isset($data['product_id']) && !empty($data['product_id'])) ? " AND product_id = ".$data['product_id'] : " ")
		;

        $attribute_value_query = $this->db->query($sql);

        foreach ($attribute_value_query->rows as $attribute_value) {
            $attribute_value_data[] = array(
                'attribute_id' => $attribute_value['option_value_id'],
                'text'            => $attribute_value['text'],
                'language_id'      => $attribute_value['language_id']
            );
        }

        return $attribute_value_query;
    }

}
?>