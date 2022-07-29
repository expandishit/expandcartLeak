<?php

class ModelNetworkMarketingDownlines extends Model
{
    private $agenciesTable = DB_PREFIX . 'nm_agencies';
    private $referralsTable = DB_PREFIX . 'nm_referrals';
    private $levelsTable = DB_PREFIX . 'nm_levels';
    private $customerTable = DB_PREFIX . 'customer';

    public function getReferrals($agencyId, $customerId)
    {
//        $queryString = [];
//        $queryString[] = 'SELECT * FROM ' . $this->referralsTable;
//        $queryString[] = 'WHERE agency_id=' . $agencyId;
//        $queryString[] = 'AND referrer=' . $customerId;

        $queryString = [];
        $queryString[] = 'SELECT *, refs.customer_id as refs_customer, refs.ref_id as refs_id FROM ' . $this->referralsTable . ' AS refs';
        // TODO left join or inner join ??
        $queryString[] = 'LEFT JOIN ' . $this->agenciesTable . ' AS agns';
        $queryString[] = 'ON refs.agency_id=agns.agency_id';
        $queryString[] = 'JOIN customer on customer.customer_id = refs.customer_id';
        $queryString[] = 'WHERE refs.agency_id=' . $agencyId;
        $queryString[] = 'AND refs.referrer=' . $customerId;

//        echo implode(' ', $queryString);exit;

        $data = $this->db->query(implode(' ', $queryString));

        return $data->rows;
    }

    public function getReferralByCustomerId($customerId)
    {
        $queryString = [];
        $queryString[] = 'SELECT *, refs.customer_id as refs_customer, refs.ref_id as refs_id FROM ' . $this->referralsTable . ' AS refs';
        $queryString[] = 'LEFT JOIN ' . $this->agenciesTable . ' AS agns';
        $queryString[] = 'ON refs.agency_id=agns.agency_id';
        $queryString[] = 'WHERE refs.customer_id=' . $customerId;

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->row;
        } else {
            return false;
        }
    }

    public function getChildReferrals($parent, $referrer)
    {
        $queryString = [];
        $queryString[] = 'SELECT *, refs.customer_id as refs_customer, refs.ref_id as refs_id FROM ' . $this->referralsTable . ' AS refs';
        $queryString[] = 'INNER JOIN ' . $this->agenciesTable . ' AS agns';
        $queryString[] = 'ON refs.agency_id=agns.agency_id';
        $queryString[] = 'JOIN customer on customer.customer_id = refs.customer_id';
        $queryString[] = 'WHERE agns.parent=' . $parent;
        $queryString[] = 'AND refs.referrer=' . $referrer;

//        echo implode(' ', $queryString);exit;

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

        $generator = function ($referrals) use (&$generator) {
            $tmpData = [];

            foreach ($referrals as $key => $referral) {

                $arrayelem = &$tmpData[];

                $arrayelem = array('name' => $referral['firstname'] . ' ' . $referral['lastname'], 'title' => $referral['ref_id']);

                if ($childs = $this->getChildReferrals($referral['agency_id'], $referral['refs_customer'])) {
                    $arrayelem['children'] = $generator($childs);
                }

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
//        $queryString[] = 'AND customer_id=' . $customerId;

//        echo implode(' ', $queryString);exit;
        $data = $this->db->query(implode(' ', $queryString));
//        print_r($data->rows);exit;

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
        $generator = function ($current, &$key) use (&$generator, &$data, $levelsCount) {

            if ($key < $levelsCount) {
                $key++;
                if ($current['parent'] > 0) {
                    $data[] = $current;
                    return $generator($this->getParent($current['parent']), $key);
                } else {
                    $data[] = $current;
                }

                return $data;
            }

        };

        $generator($current, $key);

        if (count($data) > 0) {
            return ['fullUpline' => $data, 'leveledUpline' => array_slice($data, 0, $levelsCount)];
        } else {
            return false;
        }
    }

    public function getLevelsCount()
    {
        $data = $this->db->query('select count(level_id) as count from ' . $this->levelsTable);

        $levels = $data->row['count'];

        return $levels;
    }

    public function explainEarnPoints(&$reward_point)
    {
        $queryNetworMarketing = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'network_marketing'"
        );

        if (!$queryNetworMarketing->num_rows || $queryNetworMarketing->num_rows == 0) {
            return null;
        }

        $this->load->model('network_marketing/settings');

        $settings = $this->model_network_marketing_settings->getSettings();

        if ($settings['nm_status'] != 1) {
            return null;
        }

        $upLines = $this->customer->getUpline();

        if (!$upLines) {
            return null;
        }

        usort($upLines['leveledUpline'], function ($a, $b) {
            return $a['level_id'] < $b['level_id'];
        });

        $points = null;

        $this->load->model('network_marketing/levels');

        foreach ($upLines['leveledUpline'] as $k => $upLine) {

            $level = $this->model_network_marketing_levels->getLevelById($upLine['level']);

            $points[$k]['before'] = $reward_point;
            $points[$k]['points'] = round($reward_point * ($level['percentage'] / 100));
            $points[$k]['customer_id'] = $upLine['customer_id'];

            $reward_point -= $points[$k]['points'];
        }

        array_push($points, [
            'before' => $reward_point,
            'points' => $reward_point,
            'customer_id' => $this->customer->getId()
        ]);

        return $points;
    }

}
