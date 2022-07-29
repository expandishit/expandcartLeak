<?php

class ModelModuleProductsOrdersProduct extends Model
{
    /**
     * @var string $table
     */
    protected $table = 'product';

    /**
     * Get manual shipping gateway id by order id.
     *
     * @param int $barcodes
     *
     * @return mixed
     */
    public function getProductsByBarcodes($barcodes)
    {
        if (count($barcodes) < 1) {
            return false;
        }

        $data = $this->db->query(sprintf(
            'SELECT product_id, barcode, quantity FROM `%s` WHERE barcode IN ("%s")',
            $this->table,
            implode('","', $barcodes)
        ));

        if ($data->num_rows > 0) {
            return $data->rows;
        }

        return false;
    }

    public function updateQuantities($products)
    {
        $query = $cases = [];
        $query[] = 'UPDATE %s SET';
        $query[] = 'quantity = (CASE';

        $barcodes = [];
        foreach ($products as $product) {
            $barcodes[] = $product['barcode'];
            $cases[] = sprintf('WHEN barcode = "%s" THEN "%s"', $product['barcode'], $product['quantity']);
        }

        $query[] = implode(' ', $cases);

        $query[] = 'END)';
        $query[] = 'WHERE barcode IN ("%s")';

        $query = vsprintf(implode(' ', $query), [
            $this->table,
            implode('","', $barcodes)
        ]);

        try {
            $this->db->query($query);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public function getProductsByIds($params)
    {
        if (count($params['products']) < 1) {
            return false;
        }

        $data = [
            'data' => [],
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        ];

        $query = [];

        $subQuery = 'SELECT name FROM product_description pd WHERE pd.product_id = p.product_id AND language_id = %d';
        $subQuery = sprintf($subQuery, $params['language_id']);

        $fields = sprintf('product_id, barcode, quantity , (%s) as name', $subQuery);

        $query[] = 'SELECT ' . $fields . ' FROM `%s` p WHERE product_id IN (%s)';

        $total = $this->db->query(str_replace($fields, 'COUNT(*) as dc', sprintf(
            implode(' ', $query),
            $this->table,
            implode(',', $params['products'])
        )))->row['dc'];

        if (isset($params['start']) || isset($params['limit'])) {
            if ($params['start'] < 0) {
                $params['start'] = 0;
            }

            if ($params['limit'] < 1) {
                $params['limit'] = 20;
            }

            $query[] = sprintf("LIMIT %d,%d", (int)$params['start'], (int)$params['limit']);
        }

        $queryData = $this->db->query(sprintf(
            implode(' ', $query),
            $this->table,
            implode(',', $params['products'])
        ));

        if ($queryData->num_rows > 0) {
            $data['data'] = $queryData->rows;
            $data['recordsTotal'] = $queryData->num_rows;
            $data['recordsFiltered'] = intval($total);
        }

        return $data;
    }
}
