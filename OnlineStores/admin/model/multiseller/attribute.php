<?php

class ModelMultisellerAttribute extends Model
{
    /**
     * Get all balance entries from database
     *
     * @param array $data
     * @param array $sort
     *
     * @return array
     */
    public function getAttributes($data = array(), $sort = array())
    {
        $filtersData = [];
        if (isset($sort['filters'])) {
            foreach ($sort['filters'] as $k => $v) {
                $filtersData[] = "{$k} LIKE '%" . $this->db->escape($v) . "%'";
            }
        }

        $filters = '';
        if (count($filtersData) > 0) {
            $filters = ' AND (' . implode(' OR ', $filtersData) . ') ';
        }

        $sql = "SELECT
					SQL_CALC_FOUND_ROWS
					*,
					ma.attribute_type as 'ma.attribute_type',
					ma.sort_order as 'ma.sort_order',
					ma.enabled as 'ma.enabled',
					mad.name as 'mad.name'
				FROM " . DB_PREFIX . "ms_attribute ma
				LEFT JOIN " . DB_PREFIX . "ms_attribute_description mad
					ON (ma.attribute_id = mad.attribute_id)
				WHERE 1 = 1 "
            . (isset($data['language_id']) ? " AND language_id =  " . (int)$data['language_id'] : "AND language_id =  " . (int)$this->config->get('config_language_id'))
            . (isset($data['multilang']) ? " AND multilang =  " . (int)$data['multilang'] : '')
            . (isset($data['enabled']) ? " AND enabled =  " . (int)$data['enabled'] : '')
            . $filters
            . (isset($sort['order_by']) ? " ORDER BY {$sort['order_by']} {$sort['order_way']}" : '')
            . (isset($sort['limit']) ? " LIMIT " . (int)$sort['offset'] . ', ' . (int)($sort['limit']) : '');

        $res = $this->db->query($sql);

        $total = $this->db->query("SELECT FOUND_ROWS() as total");
        if ($res->rows) $res->rows[0]['total_rows'] = $total->row['total'];

        return $res->rows;
    }
}
