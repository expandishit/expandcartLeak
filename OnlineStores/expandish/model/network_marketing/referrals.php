<?php

class ModelNetworkMarketingReferrals extends Model
{
    private $settings = null;

    private $customerTable = DB_PREFIX . 'customer';

    private $customerRewardTable = DB_PREFIX . 'customer_reward';

    private $referralsTable = DB_PREFIX . 'nm_referrals';

    public function init()
    {
        if (!$this->settings) {
            $this->settings = $this->config->get('network_marketing');
        }

        return $this;
    }

    public function isActive()
    {
        $this->init();

        if ($this->settings['nm_status'] == 1) {
            return true;
        }

        return false;
    }

    public function referralsList($input)
    {
        if ($this->init()->settings['register_definer'] == 1) {
            return $this->referralsByPoints($input);
        } else if ($this->init()->settings['register_definer'] == 2) {
            return $this->referralsBySpentMoney($input);
        } else if ($this->init()->settings['register_definer'] == 3) {
            return $this->referralsByTotalProducts($input);
        }
    }

    private function referralsByPoints($input)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM ' . $this->customerTable . ' AS c';
        $queryString[] = 'INNER JOIN ' . $this->customerRewardTable . ' AS cr';
        $queryString[] = 'ON c.customer_id=cr.customer_id';
        $queryString[] = 'WHERE c.email like "%' . $input . '%"';

        $rows = $this->db->query(implode(' ', $queryString));

        $data = [];

        foreach ($rows->rows as $row) {
            $points[$row['customer_id']][] = $row['points'];
            $data[$row['customer_id']] = $row;
            $data[$row['customer_id']]['points'] = array_sum($points[$row['customer_id']]);
        }

        return $data;
    }

    public function newReferral($customerId, $referrer, $agencyId)
    {
        $queryString = $fields = [];
        $queryString[] = 'INSERT INTO ' . $this->referralsTable . ' SET';
        $fields[] = 'customer_id=' . $customerId;
        $fields[] = 'referrer=' . $referrer;
        $fields[] = 'agency_id=' . $agencyId;
        $queryString[] = implode(',', $fields);

        $this->db->query(implode(' ', $queryString));
    }

    public function getReferralsByAgency($agencyId)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM ' . $this->referralsTable;
        $queryString[] = 'WHERE agency_id=' . $agencyId;

        $data = $this->db->query(implode(' ', $queryString));

        return $data->rows;
    }


}
