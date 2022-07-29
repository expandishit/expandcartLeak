<?php

class ModelReportProductsLive extends Model
{
    /**
     * Get compact view for categories and products stats
     *
     * @param array $params
     *
     * @return array
     */
    public function getTree($params)
    {
        $languageId = $this->config->get('config_language_id');
        $queryString  = $fields = [];

        $fields[] = 'cp.category_id AS category_id';
        $fields[] = 'c_img.status as cstatus';
        $fields[] = 'GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR " &gt; ") AS name';
        $fields[] = 'c.parent_id';
        $fields[] = 'c.sort_order';
        $fields[] = 'c.status';
        $fields[] = 'c_img.image';
        $fields[] = '(select count(*) from product_to_category as ptc left join product p on (p.product_id = ptc.product_id) where ptc.category_id = cp.category_id AND (p.status != 1 OR p.quantity < 1 OR date_available > NOW())) as paused_count';

        $fields = implode(', ', $fields);

        $queryString[] = "SELECT %s";
        $queryString[] = "FROM category_path cp";
        $queryString[] = "LEFT JOIN category as c ON (cp.path_id = c.category_id)";
        $queryString[] = "LEFT JOIN category as c_img ON (cp.category_id = c_img.category_id)";
        $queryString[] = "LEFT JOIN category_description cd1 ON (c.category_id = cd1.category_id)";
        $queryString[] = "LEFT JOIN category_description cd2 ON (cp.category_id = cd2.category_id)";

        $queryString[] = "WHERE cd1.language_id = '%d'";
        $queryString[] = "AND cd2.language_id = '%d'";

        $queryString[] = "GROUP BY cp.category_id";

        $total = $this->db->query(
            vsprintf(implode(' ', $queryString), ['COUNT(*) as _c', $languageId, $languageId])
        )->num_rows;

        if($params['length'] != -1) {
            $queryString[] = " LIMIT " . $params['start'] . ", " . $params['length'];
        }

        $allCategories = $this->db->query(vsprintf(implode(' ', $queryString), [
            $fields,
            $languageId,
            $languageId,
        ]))->rows;

        foreach ($allCategories as &$category) {

            $category['live_count'] = $category['live_quantity'] = 0;

            $query = [];

            $query[] = 'SELECT SUM(quantity) as _s, COUNT(quantity) as _c';
            $query[] = 'FROM product p LEFT JOIN product_to_category p2c ON p.product_id=p2c.product_id';
            $query[] = sprintf('WHERE p2c.category_id = %d', $category['category_id']);
            $query[] = 'AND (p.status = 1 AND p.date_available < NOW() AND p.quantity > 0)';
            $data = $this->db->query(implode(' ', $query));
            if ($data->num_rows > 0) {
                $category['live_count'] = $data->row['_c'];
                $category['live_quantity'] = $data->row['_s'];
            }
        }

        return [
            'data' => $allCategories,
            'total' => count($allCategories),
            'totalFiltered' => $total,
        ];
    }
}
