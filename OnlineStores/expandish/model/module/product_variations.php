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
     * Get the product option values array.
     *
     * @param array $valuesIds
     *
     * @return array|bool
     */
    public function getOptionValuesIds($valuesIds)
    {
        $valuesIds = implode(',', $valuesIds);
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
     * Check if the sku application is purchased or not.
     *
     * @return bool
     */
    public function isPurchased()
    {
        return $this->ecusersdb->query("SELECT count(ap.id) as count FROM " . DB_PREFIX . "appservice ap 
                                INNER JOIN storeappservice stap ON ap.id = stap.appserviceid 
                                where ap.name='productsoptions_sku'")->row['count'];
    }

    /**
     * Update the variation quantity using product id and values ids.
     *
     * @param int $productId
     * @param array $valuesIds
     * @param int $quantity
     *
     * @return void
     */
    public function updateVariationQuantityByValuesIds($productId, $valuesIds, $quantity)
    {
        $valuesIds = implode(',', $valuesIds);
        $queryString = [];
        $queryString[] = 'UPDATE ' . $this->productVariationsTable . ' SET';
        $queryString[] = 'product_quantity = (product_quantity - ' . $quantity . ')';
        $queryString[] = 'WHERE product_id = "' . $productId . '"';
        $queryString[] = 'AND option_value_ids = "' . $valuesIds . '"';
        $queryString[] = 'AND product_quantity > -1';

        $this->db->query(implode(' ', $queryString));
    }
}
