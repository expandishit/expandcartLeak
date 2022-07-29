<?php
class ModelMultisellerSellerReviews extends Model {
	public function addReview($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "ms_seller_review SET author = '" . $this->db->escape($data['author']) . "', seller_id = '" . $this->db->escape($data['seller_id']) . "', text = '" . $this->db->escape(strip_tags($data['text'])) . "', rating = '" . (int)$data['rating'] . "', status = '" . (int)$data['status'] . "', date_added = NOW()");
	}
	
	public function editReview($review_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "ms_seller_review SET author = '" . $this->db->escape($data['author']) . "', seller_id = '" . $this->db->escape($data['seller_id']) . "', text = '" . $this->db->escape(strip_tags($data['text'])) . "', rating = '" . (int)$data['rating'] . "', status = '" . (int)$data['status'] . "', date_added = NOW() WHERE review_id = '" . (int)$review_id . "'");
	}
	
	public function deleteReview($review_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "ms_seller_review WHERE review_id = '" . (int)$review_id . "'");
	}
	
	public function getReview($review_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT s.nickname as name FROM " . DB_PREFIX . "ms_seller s WHERE s.seller_id = r.seller_id) AS seller FROM " . DB_PREFIX . "ms_seller_review r WHERE r.review_id = '" . (int)$review_id . "'");
		
		return $query->row;
	}

	public function getReviews($data = array()) {
		$sql = "SELECT r.review_id, s.nickname as name, r.author, r.rating, r.status, r.date_added FROM " . DB_PREFIX . "ms_seller_review r LEFT JOIN " . DB_PREFIX . "ms_seller s ON (r.seller_id = s.seller_id)";																																					  
		
		$sort_data = array(
			's.nickname',
			'r.author',
			'r.rating',
			'r.status',
			'r.date_added'
		);	
			
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY r.date_added";	
		}
			
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}
		
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}			

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
			
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}																																				  
		$query = $this->db->query($sql);																								
		return $query->rows;	
	}
	
	public function getTotalReviews() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "ms_seller_review");
		
		return $query->row['total'];
	}
	
	public function getTotalReviewsAwaitingApproval() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "ms_seller_review WHERE status = '0'");
		
		return $query->row['total'];
	}	

    public function dtHandler($data)
    {

        $fields = 'r.review_id, s.nickname as name, r.author, r.rating, r.status, r.date_added';

        $queryString = [];

        $queryString[] = "SELECT {$fields} FROM " . DB_PREFIX . "ms_seller_review r";
        $queryString[] = "LEFT JOIN " . DB_PREFIX . "ms_seller s";
        $queryString[] = "ON (r.seller_id = s.seller_id)";
        //$queryString[] = "WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        $total = $this->db->query(str_replace($fields, 'COUNT(*) as dc', implode(' ', $queryString)))->row['dc'];

        $sort_data = array(
            's.nickname',
            'r.author',
            'r.rating',
            'r.status',
            'r.date_added'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $queryString[] = " ORDER BY " . $data['sort'];
        } else {
            $queryString[] = " ORDER BY r.date_added";
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

        $data = array (
            'data' => $queryData->rows,
            'total' => $total,
            'totalFiltered' => $queryData->num_rows
        );

        return $data;
    }
}
?>