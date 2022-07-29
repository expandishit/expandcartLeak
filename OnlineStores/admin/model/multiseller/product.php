<?php

class ModelMultisellerProduct extends Model
{
    /**
     * Get all products from database
     *
     * @param array $data
     * @param array $sort
     * @param array $cols
     *
     * @return array
     */
    public function getProducts($data = array(), $sort = array(), $cols = array())
    {
        $hFiltersData = $wFiltersData = [];
        if (isset($sort['filters'])) {
            $cols = array_merge($cols, array("`c.name`" => 1, "total_sales" => 1, "`ms.date_created`" => 1));
            foreach ($sort['filters'] as $k => $v)
            {
                if($k == "p.date_modified")
                {
                    if (!isset($cols[$k]))
                    {
                        $wFiltersData[] = "{$k} LIKE BINARY '%" . $this->db->escape($v) . "%'";
                    }
                    else
                    {
                        $hFiltersData[] = "{$k} LIKE BINARY '%" . $this->db->escape($v) . "%'";
                    }
                }
                else
                {
                    if (!isset($cols[$k]))
                    {
                        $wFiltersData[] = "{$k} LIKE '%" . $this->db->escape($v) . "%'";
                    }
                    else
                    {
                        $hFiltersData[] = "{$k} LIKE '%" . $this->db->escape($v) . "%'";
                    }
                }
            }
        }

        $wFilters = '';
        if (count($wFiltersData) > 0) {
            $wFilters = ' AND (' . implode(' OR ', $wFiltersData) . ') ';
        }

        $hFilters = '';
        if (count($hFiltersData) > 0) {
            $hFilters = ' AND (' . implode(' OR ', $hFiltersData) . ') ';
        }

        $advancedFilter = '';

        if (!empty($sort['filter_data'])) {            
            // seller
            if ((int) $sort['filter_data']['seller'] > 0) {
                $advancedFilter .= " AND `seller_id` = " . (int) $sort['filter_data']['seller'];
            } else if ($sort['filter_data']['seller'] == 0) {
                $advancedFilter .= " AND (`seller_id` = 0  OR `seller_id` IS NULL)";
            }

            if ((int) $sort['filter_data']['status'] > -1) {
                $advancedFilter .= " AND `product_status` = " . (int) $sort['filter_data']['status'];
            }

            if ((int) $sort['filter_data']['image'] > -1) {
                if ($sort['filter_data']['image'] == 0) {
                    $advancedFilter .= " AND `image` = ''";    
                } else {
                    $advancedFilter .= " AND `image` <> ''";
                }
            }
        }

        // todo validate order parameters
        $sql = "SELECT
					SQL_CALC_FOUND_ROWS "
            // additional columns
            . (isset($cols['product_earnings']) ? "
						(SELECT SUM(seller_net_amt) AS seller_total
						FROM " . DB_PREFIX . "order_product op
						INNER JOIN `" . DB_PREFIX . "ms_order_product_data` mopd
							ON (op.product_id = mopd.product_id)
						WHERE op.product_id = p.product_id) as product_earnings,
					" : "")

            . "p.product_id as 'product_id',
					p.image as 'p.image',
					p.price as 'p.price',
					pd.name as 'pd.name',
                    cd.name as category_name,
					ms.seller_id as 'seller_id',
					ms.nickname as 'ms.nickname',
					mp.product_status as 'mp.product_status',
					mp.product_approved as 'mp.product_approved',
					mp.number_sold as 'mp.number_sold',
					mp.list_until as 'mp.list_until',
					p.date_added as 'p.date_added',
					p.date_modified  as 'p.date_modified',
					pd.description as 'pd.description'
				FROM " . DB_PREFIX . "product p
				INNER JOIN " . DB_PREFIX . "product_description pd
					USING(product_id)
                LEFT JOIN " . DB_PREFIX . "product_to_category pc USING(product_id)
                LEFT JOIN " . DB_PREFIX . "category_description cd ON  (cd.category_id = pc.category_id )  
				LEFT JOIN " . DB_PREFIX . "ms_product mp
					USING(product_id)
				LEFT JOIN " . DB_PREFIX . "ms_seller ms
					USING (seller_id)
				WHERE 1 = 1"
            . (isset($data['seller_id']) ? " AND ms.seller_id =  " . (int)$data['seller_id'] : '')
            . (\Extension::isInstalled('trips') &&$this->config->get('trips')['status']==1  ? " AND p.product_id = mp.product_id ": '')
            . (isset($data['language_id']) ? " AND pd.language_id =  " . (int)$data['language_id'] : '')
            . (isset($data['product_status']) ? " AND product_status IN  (" . $this->db->escape(implode(',', $data['product_status'])) . ")" : '')

            . $advancedFilter
          
            . $wFilters

            . " GROUP BY p.product_id HAVING 1 = 1 "

            . $hFilters

            . (isset($sort['order_by']) ? " ORDER BY {$sort['order_by']} {$sort['order_way']}" : '')
            . (isset($sort['limit']) ? " LIMIT " . (int)$sort['offset'] . ', ' . (int)($sort['limit']) : '');

        $res = $this->db->query($sql);
        $total = $this->db->query("SELECT FOUND_ROWS() as total");
        if ($res->rows)
            $res->rows[0]['total_rows'] = $total->row['total'];
        return $res->rows;
    }

    /**
     * Get product seller id
     */
    public function getProductSellerId($product_id) {
        $query = $this->db->query("SELECT seller_id FROM " . DB_PREFIX . "ms_product WHERE product_id = ".$product_id." LIMIT 1");

        return $query->row;
    }

}
