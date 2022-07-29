<?php

use ExpandCart\Foundation\Providers\DedicatedDomains;

class ModelModuleDedicatedDomainsDomainPrices extends Model
{
    /**
     * dedicated domains table name.
     *
     * @var string
     */
    private $dedicatedDomainsTable = DB_PREFIX . 'dedicated_domains';

    /**
     * dedicated domains prices table name.
     *
     * @var string
     */
    private $dedicatedDomainsPricesTable = DB_PREFIX . 'dedicated_domains_prices';

    /**
     * Store a new bulk prices.
     *
     * @param int $productId
     * @param array $domains
     *
     * @return void
     */
    public function insertDedicatedPrices($productId, $domains)
    {

        $domains = $this->filterDomainsData($domains);

        foreach ($domains as $data) {
            $queryString = $fields = [];

            $queryString[] = 'INSERT INTO `' . $this->dedicatedDomainsPricesTable . '` SET';
            $fields[] = 'domain_id=' . $data['domain_id'];
            $fields[] = 'price="' . $data['price'] . '"';
            $fields[] = 'product_id=' . $productId;
            $queryString[] = implode(', ', $fields);

            $this->db->query(implode(' ', $queryString));
        }
    }

    /**
     * Store a new price.
     *
     * @param int $productId
     * @param array $domain
     *
     * @return void
     */
    public function insertDedicatedPrice($productId, $domain)
    {
        $queryString = $fields = [];

        if (is_numeric($domain['price'])) {
            $queryString[] = 'INSERT INTO `' . $this->dedicatedDomainsPricesTable . '` SET';
            $fields[] = 'domain_id=' . $domain['domain_id'];
            $fields[] = 'price="' . $domain['price'] . '"';
            $fields[] = 'product_id=' . $productId;
            $queryString[] = implode(', ', $fields);

            $this->db->query(implode(' ', $queryString));
        }
    }

    /**
     * a helper method to filter data and return only entries with domain id.
     *
     * @param array $data
     *
     * @return array
     */
    private function filterDomainsData($data)
    {
        $data = array_filter(array_map(function ($domain) {
            if (isset($domain['domain_id'])) {
                return $domain;
            }
        }, $data));

        return $data;
    }

    /**
     * Deletes a domain price its domain id.
     *
     * @param int $domainId
     *
     * @return void
     */
    public function deleteDedicatedPricesById($domainId)
    {
        $queryString = 'DELETE FROM `' . $this->dedicatedDomainsPricesTable . '` WHERE product_id=' . $domainId;

        $this->db->query($queryString);
    }

    public function getDomainPricesByProductId($productId)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM `' . $this->dedicatedDomainsPricesTable . '` as prices';
        $queryString[] = 'INNER JOIN `' . $this->dedicatedDomainsTable . '` as domains';
        $queryString[] = 'ON prices.domain_id=domains.domain_id';
        $queryString[] = 'WHERE product_id=' . $productId;

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->rows;
        } else {
            return false;
        }
    }

    /**
     * Check if the dedicated domain app is enabled.
     *
     * @return array|bool
     *
     * @note this is a redundant method, and should be removed after implementing a new implementation to handle
     * the apps statuses
     */
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
