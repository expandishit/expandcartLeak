<?php

use ExpandCart\Foundation\Providers\DedicatedDomains;

class ModelModuleDedicatedDomainsDomains extends Model
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
     * product to domain table name.
     *
     * @var string
     */
    private $productToDomainTable = DB_PREFIX . 'product_to_domain';

    /**
     * Get all domains.
     *
     * @return array|bool
     */
    public function getDomains()
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM ' . $this->dedicatedDomainsTable;

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows > 0) {
            return $data->rows;
        } else {
            return false;
        }
    }

    /**
     * Get all domains with it's prices.
     *
     * @param int $productId
     *
     * @return array|bool
     */
    public function getDetailedDomains($productId)
    {
        $query = $fields = [];

        $fields[] = 'dd.*';
        $fields[] = 'ddp.price_id';
        $fields[] = 'ddp.price';
        // $fields[] = 'p2d.product_id';

        $query[] = 'SELECT ' . implode(',', $fields) . ' FROM ' . $this->dedicatedDomainsTable . ' AS dd';
        $query[] = 'LEFT JOIN ' . $this->dedicatedDomainsPricesTable . ' AS ddp';
        $query[] = 'ON dd.domain_id=ddp.domain_id';
        $query[] = 'AND ddp.product_id=' . $productId;
        // $query[] = 'LEFT JOIN ' . $this->productToDomainTable . ' AS p2d';
        // $query[] = 'ON dd.domain_id=p2d.domain_id';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows > 0) {
            return $data->rows;
        } else {
            return false;
        }
    }

    /**
     * Store a new domain.
     *
     * @param array $domain
     *
     * @return void
     */
    public function newDomain($domain)
    {
        $queryString = $fields = [];
        $queryString[] = 'INSERT INTO ' . $this->dedicatedDomainsTable . ' SET';
        $fields[] = '`domain`="' . $domain['name'] . '"';
        $fields[] = '`currency`="' . $domain['currency'] . '"';
        $fields[] = '`country`="' . $domain['country'] . '"';
        $fields[] = '`domain_status`="' . (isset($domain['domain_status']) ? $domain['domain_status'] : 1) . '"';
        $queryString[] = implode(', ', $fields);

        $this->db->query(implode(' ', $queryString));
        
        return $this->db->getLastId();
    }

    /**
     * Get domain by its name.
     *
     * @param string $name
     *
     * @return array|bool
     */
    public function getDomainByName($name)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM ' . $this->dedicatedDomainsTable;
        $queryString[] = 'WHERE domain="' . $name . '"';

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows > 0) {
            return $data->row;
        } else {
            return false;
        }
    }

    /**
     * Get domain by its domain id.
     *
     * @param int $id
     *
     * @return array|bool
     */
    public function getDomainById($domainId)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM ' . $this->dedicatedDomainsTable;
        $queryString[] = 'WHERE domain_id="' . $domainId . '"';

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows > 0) {
            return $data->row;
        } else {
            return false;
        }
    }

    /**
     * Get domain by its country.
     *
     * @param int $id
     *
     * @return array|bool
     */
    public function getDomainByCountry($countryId)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM ' . $this->dedicatedDomainsTable;
        $queryString[] = 'WHERE country="' . $countryId . '"';

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows > 0) {
            return $data->row;
        } else {
            return false;
        }
    }

    /**
     * Update a current domain.
     *
     * @param int $domainId
     * @param array $data
     *
     * @return void
     */
    public function updateDomain($domainId, $data)
    {
        $queryString = $fields = [];
        $queryString[] = 'UPDATE ' . $this->dedicatedDomainsTable . ' SET';
        $fields[] = '`domain`="' . $data['name'] . '"';
        $fields[] = '`currency`="' . $data['currency'] . '"';
        $fields[] = '`country`="' . $data['country'] . '"';
        $fields[] = '`domain_status`="' . $data['domain_status'] . '"';
        $queryString[] = implode(', ', $fields);
        $queryString[] = 'WHERE domain_id=' . $domainId;

        $this->db->query(implode(' ', $queryString));
    }

    /**
     * Deletes a domain by its domain id.
     *
     * @param int $domainId
     *
     * @return void
     */
    public function removeDomain($domainId)
    {
        $queryString = [];
        $queryString[] = 'DELETE FROM ' . $this->dedicatedDomainsTable;
        $queryString[] = 'WHERE domain_id=' . $domainId;
        $this->db->query(implode(' ', $queryString));
        
        $queryString = [];
        $queryString[] = 'DELETE FROM ' . $this->productToDomainTable;
        $queryString[] = 'WHERE domain_id=' . $domainId;
        $this->db->query(implode(' ', $queryString));

       
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
