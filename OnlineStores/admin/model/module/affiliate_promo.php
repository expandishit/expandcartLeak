<?php

class ModelModuleAffiliatePromo extends Model {
    public function getPromoCodes($data)
    {
        $queryString = [];

        $fields = 'c.coupon_id, c.code, c.type, c.discount, CONCAT(a.firstname, " ", a.lastname) AS affiliate_name, a.affiliate_id';

        $queryString[] = "SELECT {$fields} FROM " . DB_PREFIX . "coupon c";
        $queryString[] = "LEFT JOIN coupon_to_affiliate cta ON c.coupon_id = cta.coupon_id";
        $queryString[] = "LEFT JOIN affiliate a ON cta.affiliate_id = a.affiliate_id";
        $queryString[] = "WHERE c.from_affiliate = 1";

        if (!empty($data['search'])) {
            $queryString[] = "AND (c.code LIKE '%" . $this->db->escape($data['search']) . "%'";
            $queryString[] = "OR a.firstname LIKE '%" . $this->db->escape($data['search']) . "%'";
            $queryString[] = "OR a.lastname LIKE '%" . $this->db->escape($data['search']) . "%')";
        }

        $total = $this->db->query(str_replace($fields, 'COUNT(*) as dc', implode(' ', $queryString)))->row['dc'];

        $sort_data = [
            'c.code',
            'c.type',
            'c.discount'
        ];

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $queryString[] = " ORDER BY " . $data['sort'];
        } else {
            $queryString[] = " ORDER BY c.code";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $queryString[] = " DESC";
        } else {
            $queryString[] = " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $queryString[] = " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $queryData = $this->db->query(implode(' ', $queryString));

        $data = [
            'data' => $queryData->rows,
            'total' => $total,
            'totalFiltered' => $queryData->num_rows
        ];

        return $data;
    }

    public function delete($coupon_id) {
        $this->db->query("
            DELETE c, cta FROM coupon c
            LEFT JOIN coupon_to_affiliate cta ON c.coupon_id = cta.coupon_id
            WHERE cta.coupon_id = ".(int) $this->db->escape($coupon_id)."
            AND c.coupon_id = ".(int) $this->db->escape($coupon_id)
        );
    }

    public function install()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `".DB_PREFIX."coupon_to_affiliate` (
                `coupon_id` INT UNSIGNED NOT NULL,
                `affiliate_id` INT UNSIGNED NOT NULL,
                PRIMARY KEY(`coupon_id`)
            )
        ");
        $this->db->query("ALTER TABLE `".DB_PREFIX."coupon` ADD COLUMN `from_affiliate` TINYINT(1) DEFAULT 0");
    }

    public function uninstall()
    {
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."coupon_to_affiliate`");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "coupon` WHERE `from_affiliate` = 1");
        $this->db->query("ALTER TABLE `" . DB_PREFIX . "coupon` DROP COLUMN `from_affiliate`");
    }
}