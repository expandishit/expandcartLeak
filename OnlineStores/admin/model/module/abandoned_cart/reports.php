<?php

class ModelModuleAbandonedCartReports extends ModelModuleAbandonedCartSettings
{
    /**
     * Get all abandoned orders by for report.
     *
     * @param array $data
     * @param array $filterData
     *
     * @return array|bool
     */
    public function getOrdersList($data, $filterData)
    {
        $language_id = $this->config->get('config_language_id') ?: 1;
        $implode_data = '';

        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE) {
            foreach ($data['stores_codes'] as $key=>$store_code){

                $query = $fields = $statusQuery = [];

                $statusQuery[] = 'SELECT os.name FROM '.$store_code."."  . DB_PREFIX . 'order_status os';
                $statusQuery[] = 'WHERE os.order_status_id = o.order_status_id AND os.language_id = "' . $language_id . '"';

                $fields = [
                    'o.order_id',
                    'o.payment_telephone',
                    'o.email',
                    'o.order_status_id',
                    'CONCAT(o.firstname, " ", o.lastname) AS customer',
                    'o.total',
                    'o.currency_code',
                    'o.currency_value',
                    'o.date_added',
                    'o.date_modified',
                    'o.customer_id',
                    'o.gift_product',
                    'eao.emailed',
                    'os.name as statusName',
                    '(SELECT `approved` FROM customer as cu where cu.customer_id=o.customer_id) as customer_status',
                    '(%s) AS status',
                ];

                $fields = sprintf(
                    implode(',', $fields),
                    implode(' ', $statusQuery)
                );

                $query[] = 'SELECT ' . $fields . ' FROM '.$store_code."."  . DB_PREFIX . 'order o';
                $query[] = 'LEFT JOIN '.$store_code."."  . DB_PREFIX . 'emailed_abandoned_orders eao';
                $query[] = 'ON o.order_id=eao.order_id';
                $query[] = 'LEFT JOIN '.$store_code."." . DB_PREFIX . 'order_status os';
                $query[] = 'ON o.order_status_id=os.order_status_id AND os.language_id="' . (int)$language_id . '"';

                $query[] = 'WHERE (';
                $query[] = '(o.order_status_id = "0" AND eao.emailed IS NULL) OR';
                $query[] = '(o.order_status_id = "0" AND eao.emailed = "1") OR';
                $query[] = '(o.order_status_id > "0" AND eao.emailed = "1")';
                $query[] = ')';

                $total = $this->db->query(
                    'SELECT COUNT(*) AS totalData FROM (' .
                    str_replace($fields, '0 as fakeColumn', implode(' ', $query))
                    . ') AS t'
                )->row['totalData'];
               // var_dump($total);

                if (isset($filterData['search'])) {

                    $filterData['search'] = $this->db->escape($filterData['search']);

                    $query[] = 'AND (';

                    if (trim($filterData['search'][0]) == '#') {
                        $order_id = preg_replace('/\#/', '', $filterData['search']);
                        $query[] = "o.order_id LIKE '%{$order_id}%'";
                    } else {
                        $query[] = "o.order_id LIKE '%{$filterData['search']}%'";
                        $query[] = 'OR CONCAT(o.firstname, " ", o.lastname) LIKE "%' . $filterData['search'] . '%"';
                        $query[] = 'OR email LIKE "%' . $filterData['search'] . '%"';
                        $query[] = "OR o.payment_telephone LIKE '%{$filterData['search']}%'";
                    }
                    $query[] = ')';
                }

                if (isset($filterData['order_status_id']) && $filterData['order_status_id'] >= 0) {
                    $query[] = 'AND o.order_status_id = "' . (int)$filterData['order_status_id'] . '"';
                }

                if (isset($filterData['date'])) {

                    $dates = explode('-', $filterData['date']);
                    $startDate = strtotime(trim($dates[0]));
                    $endDate = strtotime(trim($dates[1]));

                    if (($startDate && $endDate) && $endDate > $startDate) {

                        $formattedStartDate = date('Y-m-d', $startDate);
                        $formattedEndDate = date('Y-m-d', $endDate);

                        $query[] = 'AND (date_added BETWEEN "' . $formattedStartDate . '" AND "' . $formattedEndDate . '")';
                    } else if (($startDate && $endDate) && $endDate == $startDate) {
                        $formattedStartDate = date('Y-m-d', $startDate);

                        $query[] = 'AND (date_added="' . $formattedStartDate . '")';
                    }
                }

                $totalFiltered = $this->db->query(
                    'SELECT COUNT(*) AS totalData FROM (' .
                    str_replace($fields, '0 as fakeColumn', implode(' ', $query))
                    . ') AS t'
                )->row['totalData'];

                $sort_data = array(
                    'o.order_id',
                    'customer',
                    'status',
                    'date_added',
                    'date_modified',
                    'total'
                );

                if (!empty($filterData['filter_group'])) {
                    $group = $filterData['filter_group'];
                } else {
                    $group = 'week';
                }

                /*switch ($group) {
                    case 'day';
                        $query[] = " GROUP BY DAY(date_added)";
                        break;
                    default:
                    case 'week':
                        $query[] = " GROUP BY WEEK(date_added)";
                        break;
                    case 'month':
                        $query[] = " GROUP BY MONTH(date_added)";
                        break;
                    case 'year':
                        $query[] = " GROUP BY YEAR(date_added)";
                        break;
                }*/



                $query[] = " UNION ";

                $implode_data .= implode(' ', $query);
            }

            $implode_data = substr($implode_data, 0, -6);
            $sql = [];
            if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
                $sql[] = " ORDER BY " . $data['sort'];
            } else {
                $sql[] = " ORDER BY order_id";
            }
            if (isset($data['order']) && ($data['order'] == 'desc')) {
                $sql[] = " DESC";
            } else {
                $sql[] = " ASC";
            }

            if (isset($data['start']) || isset($data['limit'])) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1) {
                    $data['limit'] = 20;
                }

                $sql[] = " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }
            $implode_data .= implode(' ', $sql);

        }else{
            $query = $fields = $statusQuery = [];

            $statusQuery[] = 'SELECT os.name FROM ' . DB_PREFIX . 'order_status os';
            $statusQuery[] = 'WHERE os.order_status_id = o.order_status_id AND os.language_id = "' . $language_id . '"';

            $fields = [
                'o.order_id',
                'o.payment_telephone',
                'o.email',
                'o.order_status_id',
                'CONCAT(o.firstname, " ", o.lastname) AS customer',
                'o.total',
                'o.currency_code',
                'o.currency_value',
                'o.date_added',
                'o.date_modified',
                'o.customer_id',
                'o.gift_product',
                'eao.emailed',
                'os.name as statusName',
                '(SELECT `approved` FROM customer as cu where cu.customer_id=o.customer_id) as customer_status',
                '(%s) AS status',
            ];

            $fields = sprintf(
                implode(',', $fields),
                implode(' ', $statusQuery)
            );

            $query[] = 'SELECT ' . $fields . ' FROM `' . DB_PREFIX . 'order` o';
            $query[] = 'LEFT JOIN `' . DB_PREFIX . 'emailed_abandoned_orders` eao';
            $query[] = 'ON o.order_id=eao.order_id';
            $query[] = 'LEFT JOIN `' . DB_PREFIX . 'order_status` os';
            $query[] = 'ON o.order_status_id=os.order_status_id AND os.language_id="' . (int)$language_id . '"';

            $query[] = 'WHERE (';
            $query[] = '(o.order_status_id = "0" AND eao.emailed IS NULL) OR';
            $query[] = '(o.order_status_id = "0" AND eao.emailed = "1") OR';
            $query[] = '(o.order_status_id > "0" AND eao.emailed = "1")';
            $query[] = ')';

            $total = $this->db->query(
                'SELECT COUNT(*) AS totalData FROM (' .
                str_replace($fields, '0 as fakeColumn', implode(' ', $query))
                . ') AS t'
            )->row['totalData'];

            if (isset($filterData['search'])) {

                $filterData['search'] = $this->db->escape($filterData['search']);

                $query[] = 'AND (';

                if (trim($filterData['search'][0]) == '#') {
                    $order_id = preg_replace('/\#/', '', $filterData['search']);
                    $query[] = "o.order_id LIKE '%{$order_id}%'";
                } else {
                    $query[] = "o.order_id LIKE '%{$filterData['search']}%'";
                    $query[] = 'OR CONCAT(o.firstname, " ", o.lastname) LIKE "%' . $filterData['search'] . '%"';
                    $query[] = 'OR email LIKE "%' . $filterData['search'] . '%"';
                    $query[] = "OR o.payment_telephone LIKE '%{$filterData['search']}%'";
                }
                $query[] = ')';
            }

            if (isset($filterData['order_status_id']) && $filterData['order_status_id'] >= 0) {
                $query[] = 'AND o.order_status_id = "' . (int)$filterData['order_status_id'] . '"';
            }

            if (isset($filterData['date'])) {

                $dates = explode('-', $filterData['date']);
                $startDate = strtotime(trim($dates[0]));
                $endDate = strtotime(trim($dates[1]));

                if (($startDate && $endDate) && $endDate > $startDate) {

                    $formattedStartDate = date('Y-m-d', $startDate);
                    $formattedEndDate = date('Y-m-d', $endDate);

                    $query[] = 'AND (date_added BETWEEN "' . $formattedStartDate . '" AND "' . $formattedEndDate . '")';
                } else if (($startDate && $endDate) && $endDate == $startDate) {
                    $formattedStartDate = date('Y-m-d', $startDate);

                    $query[] = 'AND (date_added="' . $formattedStartDate . '")';
                }
            }

            $totalFiltered = $this->db->query(
                'SELECT COUNT(*) AS totalData FROM (' .
                str_replace($fields, '0 as fakeColumn', implode(' ', $query))
                . ') AS t'
            )->row['totalData'];

            $sort_data = array(
                'o.order_id',
                'customer',
                'status',
                'date_added',
                'date_modified',
                'total'
            );

            if (!empty($filterData['filter_group'])) {
                $group = $filterData['filter_group'];
            } else {
                $group = 'week';
            }

            /*switch ($group) {
                case 'day';
                    $query[] = " GROUP BY DAY(date_added)";
                    break;
                default:
                case 'week':
                    $query[] = " GROUP BY WEEK(date_added)";
                    break;
                case 'month':
                    $query[] = " GROUP BY MONTH(date_added)";
                    break;
                case 'year':
                    $query[] = " GROUP BY YEAR(date_added)";
                    break;
            }*/

            if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
                $query[] = "ORDER BY " . $data['sort'];
            } else {
                $query[] = "ORDER BY o.order_id";
            }

            if (isset($data['order']) && ($data['order'] == 'desc')) {
                $query[] = "DESC";
            } else {
                $query[] = "ASC";
            }

            if (isset($data['start']) || isset($data['limit'])) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1) {
                    $data['limit'] = 20;
                }

                $query[] = "LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }
            $implode_data .= implode(' ', $query);
        }


        $query = $this->db->query($implode_data);

        return [
            'data' => $query->rows,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        ];
    }
}
