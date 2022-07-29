<?php

class ModelModuleProductVariations extends Model
{
    /**
     * Product option value table name.
     *
     * @var string
     */
    private $productOptionValueTable = '`' . DB_PREFIX . 'product_option_value`';

    /**
     * Product variation table name.
     *
     * @var string
     */
    private $productVariationsTable = '`' . DB_PREFIX . 'product_variations`';

    /**
     * The available operations.
     *
     * @var array
     */
    private $operations = [
        'add' => '+',
        'sum' => '+',
        '+' => '+',
        'sub' => '-',
        'subtract' => '-',
    ];

    public function getProductVariationByValuesIds($productId, $valuesIds)
    {
        sort($valuesIds);

        $query = 'SELECT * FROM %s WHERE option_value_ids = "%s" AND product_id = %d';

        $data = $this->db->query(vsprintf($query, [
            $this->productVariationsTable,
            implode(',', $valuesIds),
            $productId
        ]));

        if ($data->num_rows ) {
            return $data->row;
        }

        return false;
    }

    /**
     * Get the product option values array.
     *
     * @param array $valuesIds
     *
     * @return array|bool
     */
    public function getOptionValuesIds($valuesIds)
    {
        $valuesIds = implode(',', $valuesIds);
        if (!$valuesIds) {
            return false;
        }
        $queryString = [];
        $queryString[] = 'SELECT * FROM ' . $this->productOptionValueTable;
        $queryString[] = 'WHERE product_option_value_id IN (' . $valuesIds . ')';
        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }

    /**
     * Check if the sku application is enabled ot not.
     *
     * @return bool
     */
    public function isActive()
    {
        $productsoptions_sku_status = $this->config->get('productsoptions_sku_status');

        if (isset($productsoptions_sku_status) && $productsoptions_sku_status == 1) {
            return true;
        }

        return false;
    }

    /**
     * Update the variation quantity using product id and values ids.
     *
     * @param int $productId
     * @param array $valuesIds
     * @param int $quantity
     * @param string $operation
     *
     * @return void
     */
    public function updateVariationQuantityByValuesIds($productId, $valuesIds, $quantity, $operation = 'add')
    {
        if (isset($this->operations[$operation]) == false) {
            return false;
        }

        $operation = $this->operations[$operation];

        $valuesIds = implode(',', $valuesIds);
        $queryString = [];
        $queryString[] = 'UPDATE ' . $this->productVariationsTable . ' SET';
        $queryString[] = 'product_quantity = (product_quantity ' .  $operation . ' ' . $quantity . ')';
        $queryString[] = 'WHERE product_id = "' . $productId . '"';
        $queryString[] = 'AND option_value_ids = "' . $valuesIds . '"';

        $this->db->query(implode(' ', $queryString));
    }

    /**
     * an alias method to add the variation quantity using product id and values ids.
     *
     * @param int $productId
     * @param array $valuesIds
     * @param int $quantity
     *
     * @return void
     */
    public function addVariationQuantityByValuesIds($productId, $valuesIds, $quantity)
    {
        $this->updateVariationQuantityByValuesIds($productId, $valuesIds, $quantity, 'add');
    }

    public function subtractVariationQuantityByValuesIds($productId, $valuesIds, $quantity)
    {
        $this->updateVariationQuantityByValuesIds($productId, $valuesIds, $quantity, 'subtract');
    }
}
