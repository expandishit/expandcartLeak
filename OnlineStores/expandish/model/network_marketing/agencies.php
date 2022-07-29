<?php

class ModelNetworkMarketingAgencies extends Model
{
    private $agenciesTable = DB_PREFIX . 'nm_agencies';
    private $referralsTable = DB_PREFIX . 'nm_referrals';
    private $customerTable = DB_PREFIX . 'customer';

    private $slug = 'REF-';

    private $settings = null;

    public function init()
    {
        if (!$this->settings) {
            $this->settings = $this->config->get('network_marketing');
        }

        return $this;
    }

    public function getCustomerAgencies($customerId)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM ' . $this->agenciesTable . ' AS n';
        $queryString[] = 'INNER JOIN ' . $this->customerTable . ' AS c';
        $queryString[] = 'ON n.customer_id=c.customer_id';
        $queryString[] = 'WHERE n.customer_id=' . $this->db->escape($customerId);
        $queryString[] = 'AND n.parent=0';

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return [
                'data' => $data->rows,
                'count' => $data->num_rows,
            ];
        } else {
            return null;
        }
    }

    public function getCustomerSubAgencies($customerId)
    {
        $queryString = $selectedFields = [];
        $queryString[] = 'SELECT';

//        $selectedFields[] = 's.agency_id as sub_agency';
//        $selectedFields[] = 's.ref_id as sub_ref_id';
        $selectedFields[] = 'c.*';
        $selectedFields[] = 'r.*';
        $selectedFields[] = 'n.*';
        $selectedFields[] = 'c.customer_id as parent_id';
        $selectedFields[] = 'n.agency_id as parent_agency_id';
        $selectedFields[] = 'n.parent as x_parent_agency_id';
        $queryString[] = implode(',', $selectedFields);

        $queryString[] = 'FROM ' . $this->referralsTable . ' AS r';
        $queryString[] = 'INNER JOIN ' . $this->agenciesTable . ' AS n';
        $queryString[] = 'ON n.agency_id=r.agency_id';
//        $queryString[] = 'LEFT JOIN ' . $this->agenciesTable . ' AS s';
//        $queryString[] = 'ON s.customer_id=r.customer_id';
        $queryString[] = 'INNER JOIN ' . $this->customerTable . ' AS c';
        $queryString[] = 'ON r.referrer=c.customer_id';
        $queryString[] = 'WHERE r.customer_id=' . $this->db->escape($customerId);
//        $queryString[] = 'AND s.parent!=0';

//        echo implode(' ', $queryString);exit;

        $data = $this->db->query(implode(' ', $queryString));


        if ($data->num_rows) {

            $agencies = [];

            foreach ($data->rows as $key => $row) {
                $agencies[$key] = $row;
                if ($subAgency = $this->getAgencyByParent($row['parent_agency_id'], $customerId)) {
                    $agencies[$key]['sub_agency'] = $subAgency['agency_id'];
                    $agencies[$key]['sub_ref_id'] = $subAgency['ref_id'];
                }
            }

            return [
                'data' => $agencies,
                'count' => $data->num_rows,
            ];
        } else {
            return null;
        }
    }

    public function getCustomerSubAgency($parentAgency, $customerId)
    {
        $queryString = [];
        $queryString[] = 'SELECT *, n.agency_id as parent_agency_id';
        $queryString[] = 'FROM ' . $this->agenciesTable . ' AS n';
        $queryString[] = 'INNER JOIN ' . $this->referralsTable . ' AS r';
        $queryString[] = 'ON n.agency_id=r.agency_id';
        $queryString[] = 'WHERE r.customer_id=' . $this->db->escape($customerId);
        $queryString[] = 'AND n.agency_id=' . $parentAgency;

//        echo implode(' ', $queryString);exit;

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->row;
        } else {
            return null;
        }
    }

    public function newAgency($customerId, $refid)
    {
        $queryString = $fields = [];
        $queryString[] = 'INSERT INTO ' . $this->agenciesTable . ' SET';
        $fields[] = 'customer_id=' . $customerId;
        $fields[] = 'ref_id="' . $this->db->escape($refid) . '"';
        $queryString[] = implode(', ', $fields);

        $this->db->query(implode(' ', $queryString));
    }

    public function newSubAgency($customerId, $refid, $parent, $level)
    {
        $queryString = $fields = [];
        $queryString[] = 'INSERT INTO ' . $this->agenciesTable . ' SET';
        $fields[] = 'customer_id=' . $customerId;
        $fields[] = 'ref_id="' . $this->db->escape($refid) . '"';
        $fields[] = 'parent="' . $this->db->escape($parent) . '"';
        $fields[] = 'level="' . $this->db->escape($level) . '"';

        $queryString[] = implode(', ', $fields);

        $this->db->query(implode(' ', $queryString));
    }

    public function genereateRefId($customerId)
    {
        $refid = [];

        $refid[] = $this->slug;
        $refid[] = $customerId;
        $refid[] = substr(time(), -4);

        return implode('', $refid);
    }

    public function validateRefId($ref)
    {
        $this->init();

        if (!isset($this->settings['nm_status']) || $this->settings['nm_status'] == 0) {
            return true;
        }

        if (strcmp(substr($ref, 0, 4), $this->slug) !== 0) {
            return false;
        }

        return true;
    }

    public function getAgencyByRefId($refId)
    {
        $queryString = [];

        $queryString[] = 'SELECT * FROM ' . $this->agenciesTable;
        $queryString[] = 'WHERE ref_id="' . $refId . '"';

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows > 0) {
            return $data->row;
        }

        return false;
    }

    public function getAgencyByParent($parent, $customerId)
    {
        $queryString = [];

        $queryString[] = 'SELECT * FROM ' . $this->agenciesTable;
        $queryString[] = 'WHERE parent="' . $parent . '"';
        $queryString[] = 'AND customer_id=' . $this->db->escape($customerId);

//        echo implode(' ', $queryString);exit;

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows > 0) {
            return $data->row;
        }

        return false;
    }

    public function getAgencyById($agencyId)
    {
        $queryString = [];

        $queryString[] = 'SELECT * FROM ' . $this->agenciesTable;
        $queryString[] = 'JOIN customer on customer.customer_id = ' . $this->agenciesTable . '.customer_id';
        $queryString[] = 'WHERE agency_id="' . $agencyId . '"';

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows > 0) {
            return $data->row;
        }

        return false;
    }
}
