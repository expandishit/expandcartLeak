<?php

use ExpandCart\Foundation\Providers\DedicatedDomains;

class ModelModuleDedicatedDomainsProductDomain extends Model
{
    /**
     * product to domain table name.
     *
     * @var string
     */
    private $productToDomainTable = DB_PREFIX . 'product_to_domain';

    /**
     * Fetch data using product id.
     *
     * @param int $productId
     *
     * @return array|bool
     */
    public function getProductDomains($productId)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM `' . $this->productToDomainTable . '`';
        $queryString[] = 'WHERE product_id=' . $productId;

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }

    /**
     * Return an array of domains id.
     *
     * @param array $domains
     *
     * @return array
     */
    public function getProductDomainsIds($domains)
    {
        return array_column($domains, 'domain_id');
    }

    /**
     * Store new row in the $productToDomainTable table.
     *
     * @param int $productId
     * @param int $domainId
     *
     * @return void
     */
    public function insertProductDomain($productId, $domainId)
    {
        $queryString = $fields = [];
        $queryString[] = 'INSERT INTO `' . $this->productToDomainTable . '` SET';
        $fields[] = 'product_id=' . $productId;
        $fields[] = 'domain_id=' . $domainId;

        $queryString[] = implode(',', $fields);

        $this->db->query(implode(' ', $queryString));
    }
    /**
     * Store bulk of products in the $productToDomainTable table.
     *
     * @param int $productId
     * @param int $domainId
     *
     * @return void
     */
    public function insertProductsDomain($productIds, $domainId)
    {
        $sql = array(); 
        foreach ($productIds as $product_id) {
            $sql[] = '('.$product_id.','.$domainId.')';
        }

        $queryString = $fields = [];
        $queryString[] = 'INSERT INTO `' . $this->productToDomainTable . '` (product_id,domain_id)';
        $queryString[] = 'VALUES ' . implode(',', $sql);

        $this->db->query(implode(' ', $queryString));
    }
    /**
     * Store a buld of rows in the $productToDomainTable table.
     *
     * @param int $productId
     * @param array $domains
     *
     * @return void
     */
    public function insertProductDomains($productId, $domains)
    {
        foreach ($domains as $domain) {
            $this->insertProductDomain($productId, $domain);
        }
    }

    /**
     * Delete row from $productToDomainTable table by productId.
     *
     * @param int $productId
     *
     * @return void
     */
    public function deleteProductDomains($productId)
    {
        $queryString = [];
        $queryString[] = 'DELETE FROM `' . $this->productToDomainTable . '`';
        $queryString[] = 'WHERE product_id=' . $this->db->escape($productId);

        $this->db->query(implode(' ', $queryString));
    }

    /**
     * Checks whether the application is enabled or not.
     *
     * @return bool
     **/
    public function isActive()
    {
        $dedicatedDomains = new DedicatedDomains;

        if ($dedicatedDomains->setRegistry($this->registry)->isActive()) {

            unset($dedicatedDomains);

            return true;
        }

        unset($dedicatedDomains);

        return false;
    }
}
