<?php

class ModelModuleNetworkMarketingDownlines extends Model
{
    private $agenciesTable = DB_PREFIX . 'nm_agencies';
    private $referralsTable = DB_PREFIX . 'nm_referrals';
    private $levelsTable = DB_PREFIX . 'nm_levels';
    private $customerTable = DB_PREFIX . 'customer';

    public function getReferrals($agencyId, $customerId)
    {
        $queryString = [];
        $queryString[] = 'SELECT *, refs.customer_id as refs_customer, refs.ref_id as refs_id FROM ' . $this->referralsTable . ' AS refs';
        $queryString[] = 'INNER JOIN ' . $this->agenciesTable . ' AS agns';
        $queryString[] = 'ON refs.agency_id=agns.agency_id';
        $queryString[] = 'WHERE refs.agency_id=' . $agencyId;
        $queryString[] = 'AND refs.referrer=' . $customerId;

        $data = $this->db->query(implode(' ', $queryString));

        return $data->rows;
    }

    public function getChildReferrals($parent, $referrer)
    {
        $queryString = [];
        $queryString[] = 'SELECT *, refs.customer_id as refs_customer, refs.ref_id as refs_id FROM ' . $this->referralsTable . ' AS refs';
        $queryString[] = 'INNER JOIN ' . $this->agenciesTable . ' AS agns';
        $queryString[] = 'ON refs.agency_id=agns.agency_id';
        $queryString[] = 'WHERE agns.parent=' . $parent;
        $queryString[] = 'AND refs.referrer=' . $referrer;

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->rows;
        } else {
            return false;
        }
    }

    public function generateDownline($referrals)
    {
        $data = [];

        $generator = function ($referrals) use(&$generator) {
            $tmpData = [];

            foreach ($referrals as $key => $referral) {
                if (($childs = $this->getChildReferrals($referral['agency_id'], $referral['refs_customer']))) {
                    $child = $generator($childs);
                } else {
                    $child['data'] = $referral;
                }

                $tmpData['data'] = $referral;
                $tmpData['childs'][$key] = $child;
            }

            return $tmpData;
        };

        $data = $generator($referrals);

        return $data;
    }

    public function getParent($parentId)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM ' . $this->agenciesTable;
        $queryString[] = 'WHERE agency_id=' . $parentId;

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows && $data->num_rows > 0) {
            return $data->row;
        }

        return false;
    }

    public function generateUpline($current)
    {
        $levelsCount = $this->getLevelsCount();

        $data = [];
        $key = 0;
        $generator = function ($current, &$key) use (&$generator, &$data, $levels) {

            if ($key < $levels) {
                var_dump($key);
                $key++;
                if ($parent = $this->getParent($current['parent'])) {
                    $data[] = $current;
                    return $generator($parent, $key);
                } else {
                    $data[] = $current;
                }


                return $data;
            }

        };

        $generator($current, $key);

        return ['fullUpline' => $data, 'leveledUpline' => array_slice($data, 0, $levelsCount)];
    }

    public function getLevelsCount()
    {
        $data = $this->db->query('select count(level_id) as count from ' . $this->levelsTable);

        $levels = $data->row['count'];

        return $levels;
    }
}
