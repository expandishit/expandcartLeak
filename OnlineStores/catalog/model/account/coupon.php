<?php

use ExpandCart\Foundation\Support\Factories\Google\ClientFactory;

class ModelAccountCoupon extends Model {
    protected $notificationsTable = 'firebase_notifications';

	public function getNotfiableCoupons() {
		$coupon_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "coupon` WHERE date_start <= CURDATE() AND date_end >= CURDATE() AND status = '1' AND notify_mobile = '1' ");
		return $coupon_query->rows;
	}

    public function pushNotification($devices, $data)
    {
        return ClientFactory::pushNotification($devices, $data);
    }

    public function insertNotification($data)
    {
        $query = $fields = [];
        $query[] = 'INSERT INTO %s SET';
        $fields[] = 'name="%s"';
        $fields[] = 'title="%s"';
        $fields[] = 'body="%s"';
        $fields[] = 'message_type="%s"';
        $fields[] = 'customer_id="%s"';
        $query[] = vsprintf(implode(',', $fields), [
            $data['name'],
            $data['title'],
            $data['body'],
            $data['message_type'],
            $data['customer_id'],
        ]);

        $this->db->query(sprintf(implode(' ', $query), $this->notificationsTable));
    }

}
?>