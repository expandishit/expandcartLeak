<?php

class ModelModuleNetworkMarketingAgencies extends Model
{
    private $agenciesTable = DB_PREFIX . 'nm_agencies';
    private $customerTable = DB_PREFIX . 'customer';

    public $numRows = 0;

    private $errors = [];

    public function listAgencies($parent = 0)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM ' . $this->agenciesTable . ' AS n';
        $queryString[] = 'INNER JOIN ' . $this->customerTable . ' AS c';
        $queryString[] = 'ON n.customer_id=c.customer_id';
        $queryString[] = 'WHERE parent=' . $parent;

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return [
                'data' => $data->rows,
                'count' => $data->num_rows
            ];
        } else {
            return false;
        }
    }

    public function viewAgency($agencyId)
    {
        $queryString = [];

        $queryString[] = 'SELECT * FROM ' . $this->agenciesTable;
        $queryString[] = 'WHERE agency_id=' . $agencyId;

        $data = $this->db->query(implode(' ', $queryString));

        return $data->row;
    }

    public function setError($error)
    {
        $this->errors[] = $error;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
