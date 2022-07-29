<?php

class ModelMultisellerSellerGroup extends Model
{
    /**
     * Get all seller groups from database
     *
     * @param array $data
     * @param array $sort
     * @param array $cols
     *
     * @return array
     */

    public function getSellerGroups($data = array(), $sort = array(), $cols = array())
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
					*
					FROM " . DB_PREFIX . "ms_seller_group msg 
					LEFT JOIN " . DB_PREFIX . "ms_seller_group_description msgd 
						ON (msg.seller_group_id = msgd.seller_group_id) 
					WHERE msgd.language_id = '" . (int)$this->config->get('config_language_id') . "'"
            . $filters
            . (isset($sort['order_by']) ? " ORDER BY {$sort['order_by']} {$sort['order_way']}" : '')
            . (isset($sort['limit']) ? " LIMIT " . (int)$sort['offset'] . ', ' . (int)($sort['limit']) : '');
        $res = $this->db->query($sql);

        $total = $this->db->query("SELECT FOUND_ROWS() as total");
        if ($res->rows) $res->rows[0]['total_rows'] = $total->row['total'];

        return $res->rows;
    }
}
