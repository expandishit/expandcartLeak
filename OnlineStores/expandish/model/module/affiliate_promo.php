<?php
class ModelModuleAffiliatePromo extends Model {
    public function addPromoCode($data, $affiliate_id)
    {
        $code = $this->db->escape($data['code']);
        $fields = 'name, code, type, discount, status, from_affiliate, date_start
        , date_end, minimum_to_apply, maximum_limit, uses_total, uses_customer, shipping, tax_excluded ';
        
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "coupon (" . $fields .") VALUES 
            ('".$code."',
            '".$code."',
            '".$this->db->escape($data['type'])."',
            ".(float) $this->db->escape($data['discount']).",
            ".(int) $this->db->escape($data['status']).",
            1,
            '".$data['date_start']."',
            '".$data['date_end']."',
            ".(int) $this->db->escape($data['minimum_to_apply']).",
            ".(int) $this->db->escape($data['maximum_limit']).",
            ".(int) $this->db->escape($data['uses_per_coupon']).",
            ".(int) $this->db->escape($data['uses_per_customer']).",
            ".(int) $this->db->escape($data['shipping']).",
            ".(int) $this->db->escape($data['tax_excluded'])."
            )
        ");

        $lastCouponId = $this->db->getLastId();

        $this->db->query("INSERT INTO " . DB_PREFIX . "coupon_to_affiliate (coupon_id, affiliate_id) VALUES (".$lastCouponId.", ".(int) $this->db->escape($affiliate_id) .")");
    }

    public function getPromoCodes($data)
    {
        $queryString = [];

        $fields = 'c.coupon_id, c.status , c.code, c.type, c.discount, (SELECT COUNT(ch.coupon_id) FROM coupon_history ch WHERE ch.coupon_id = c.coupon_id) AS total_use, a.code as tracking_code';

        $queryString[] = "SELECT {$fields} FROM " . DB_PREFIX . "coupon c";
        $queryString[] = "LEFT JOIN coupon_to_affiliate cta ON c.coupon_id = cta.coupon_id";
        $queryString[] = "LEFT JOIN affiliate a ON a.affiliate_id = cta.affiliate_id";
        $queryString[] = "WHERE cta.affiliate_id = " . (int) $this->db->escape($data['affiliate_id']);

        if (!empty($data['search'])) {
            $queryString[] = "AND c.code LIKE '%" . $this->db->escape($data['search']) . "%'";
        }

        $total = $this->db->query(str_replace($fields, 'COUNT(*) as dc', implode(' ', $queryString)))->row['dc'];

        $sort_data = [
            'code',
            'type',
            'discount'
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

    public function getPromoCode($coupon_id, $affiliate_id)
    {
        return $this->db->query("
            SELECT c.coupon_id, c.code, c.type, c.discount,
             c.minimum_to_apply,c.maximum_limit,c.uses_total,
             c.uses_customer FROM " . DB_PREFIX . "coupon c
            LEFT JOIN coupon_to_affiliate cta ON c.coupon_id = cta.coupon_id 
            WHERE cta.coupon_id = " . (int) $this->db->escape($coupon_id) . "
            AND cta.affiliate_id = " . (int) $this->db->escape($affiliate_id) . "
        ")->row;
    }

    public function editPromoCode($data, $affiliate_id)
    {
        $this->db->query("
            UPDATE " . DB_PREFIX . "coupon c
            LEFT JOIN coupon_to_affiliate cta ON c.coupon_id = cta.coupon_id
            SET c.code = '".$this->db->escape($data['code'])."',
            c.name = '".$this->db->escape($data['code'])."',
            c.type = '".$this->db->escape($data['type'])."',
            c.discount = ".(float) $this->db->escape($data['discount']).",
            c.minimum_to_apply = ".(int) $this->db->escape($data['minimum_to_apply']).",
            c.maximum_limit = ".(int) $this->db->escape($data['maximum_limit']).",
            c.uses_total = ".(int) $this->db->escape($data['uses_per_coupon']).",
            c.uses_customer = ".(int) $this->db->escape($data['uses_per_customer'])."
            WHERE cta.affiliate_id = ".(int) $this->db->escape($affiliate_id)."
            AND cta.coupon_id = " .(int) $this->db->escape($data['coupon_id'])
        );
    }

    public function deletePromoCode($coupon_id, $affiliate_id)
    {
        $this->db->query("
            DELETE c, cta
            FROM coupon c LEFT JOIN coupon_to_affiliate cta
            ON c.coupon_id = cta.coupon_id
            WHERE cta.coupon_id = ".(int) $this->db->escape($coupon_id)."
            AND cta.affiliate_id = ".(int) $this->db->escape($affiliate_id)
        );
    }

    public function promoCodeExists($code)
    {
        $result = $this->db->query("SELECT COUNT(code) AS code_count FROM " . DB_PREFIX . "coupon WHERE code = '".$this->db->escape($code)."'")->row['code_count'];
        return $result > 0;
    }

    public function isUsedWithAffiliateCode($coupon_id, $tracking_code)
    {
        $result = $this->db->query("
            SELECT a.code as tracking_code FROM ".DB_PREFIX."affiliate a
            LEFT JOIN coupon co ON
            a.affiliate_id = co.from_affiliate
            WHERE co.coupon_id = " . (int) $this->db->escape($coupon_id)
        )->row;

        return $result['tracking_code'] === $tracking_code;
    }

    public function fromAffiliateCoupon($coupon_id)
    {
        $result = $this->db->query("
            SELECT from_affiliate FROM " . DB_PREFIX . "coupon WHERE coupon_id = " . (int) $this->db->escape($coupon_id)
        )->row['from_affiliate'] ?? 0;
        
        return $result != 0;
    }

    public function isEnabled()
    {
        $affiliatePromoStatus = $this->config->get('affiliate_promo')['status'] ?? 0;
        return $affiliatePromoStatus == 1;
    }
}