<?php

class ModelModuleDedicatedDomainsProductDomain extends Model
{
    /**
     * product to domain table name.
     *
     * @var string
     */
    private $productToDomainTable = DB_PREFIX . 'product_to_domain';

    /**
     * Get products domains using a product id.
     *
     * @param int $productId
     *
     * @return bool|array
     */
    public function getProductDomainsByProductId($productId)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM `' . $this->productToDomainTable . '`';
        $queryString[] = 'WHERE product_id="' . (int)$productId . '"';

        $data = $this->db->query(implode(' ', $queryString));

        if (!$data->num_rows) {
            return false;
        }

        return $data->rows;
    }

    /**
     * Extract domains ids from a domains array.
     *
     * @param array $domains
     *
     * @return array
     */
    public function getProductDomainIds($domains)
    {
        return array_column($domains, 'domain_id');
    }
}
