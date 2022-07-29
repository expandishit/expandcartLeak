<?php

class ModelMultisellerSeller extends Model
{
    public function index($data)
    {
        $this->load->model('tool/image');

        $query = "SELECT DISTINCT seller_id, nickname as name, avatar, custom_fields FROM "
            . DB_PREFIX . "ms_seller WHERE nickname LIKE '%{$data['search_key']}%' AND seller_status = 1 
            ORDER BY name {$data['sort_by']} LIMIT {$data['limit']} offset {$data['offset']}";
        $query = $this->db->query($query);
        $data = [];

        foreach ($query->rows as $row) {
            if ($row['avatar']) {
                $image = $this->model_tool_image->resize($row['avatar'], 50, 50); //width,height
            } else {
                $image = $this->model_tool_image->resize('no_image.jpg', 50, 50);
            }
            $row['avatar'] = $image;

            if (isset($data['working_outside'])) {
                if ($row['custom_fields'] != $data['working_outside']) {
                    unset($row['custom_fields']);
                    continue;
                }
            }
            unset($row['custom_fields']);

            $data[] = $row;
        }

        return $data;
    }

    /**
     * @param int $seller_id
     * @return bool
     * @todo  Gallery and Product attr
     */
    public function get(int $seller_id)
    {
        try {
            $this->load->model('tool/image');

            $query = "SELECT seller_id, nickname as name, avatar, custom_fields,
                    (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "ms_seller_review r1
				            WHERE r1.seller_id = seller_id AND r1.status = '1' GROUP BY r1.seller_id limit 1)
				    AS rating FROM " . DB_PREFIX . "ms_seller  WHERE seller_id =$seller_id AND seller_status = 1";

            $query = $this->db->query($query);
            $data = $query->row;

            if (empty($data)) {
                return false;
            }
            $fields = unserialize($data['custom_fields']);
            unset($data['custom_fields']);
            $data['location'] = $fields[0];
            $data['category'] = $fields[1];
            $data['experience'] = $fields[3];
            $data['work_outside'] = $fields[4];
            $data['rating'] = (float)$data['rating'];

            if ($data['avatar']) {
                $image = $this->model_tool_image->resize($data['avatar'], 50, 50); //width,height
            } else {
                $image = $this->model_tool_image->resize('no_image.jpg', 50, 50);
            }
            $data['avatar_thumb'] = $image;
            $data['avatar'] = HTTP_IMAGE  . $data['avatar'];

            $products = $this->products($data['seller_id']);

            if (is_string($products)) {
                return 'error';
            }

            $data['products'] = $products;

            $gallery = $this->gallery($data['seller_id']);
            $videos = $this->videos($data['seller_id']);
            if (is_string($gallery)) {
                return 'error';
            }

            $data['gallery'] = $gallery;

            $data['videos'] = $videos;

            return $data;
        } catch (Exception $exception) {

        }

        return 'error';
    }
    public function getSeller($seller_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ms_seller WHERE seller_id = '" . (int)$seller_id . "'");
		
		return $query->row;
	}

    private function products(int $seller_id)
    {
        try {
            $this->load->model('localisation/language');
            $language_id = $this->config->get('config_language_id');

            $query = "SELECT pro.product_id, pro.image, pro.price, pro_d.name, pro_d.description FROM " . DB_PREFIX . "ms_product msp JOIN "
                . DB_PREFIX . "product pro ON (msp.product_id = pro.product_id)  JOIN " . DB_PREFIX .
                "product_description pro_d ON (pro_d.product_id = pro.product_id)  
            WHERE msp.seller_id =$seller_id AND pro.status=1 AND pro_d.language_id = $language_id";

            $query = $this->db->query($query);
            $rows = $query->rows;
            $data = [];

            foreach ($rows as $row) {
                if ($row['image']) {
                    $image = HTTP_IMAGE  . $row['image'];
                } else {
                    $image = $this->model_tool_image->resize('no_image.jpg', 50, 50);
                }
                $row['image'] = $image;
                $row['currency'] = $this->currency->getCode();
                $data[] = $row;
            }

            return $data;
        } catch (Exception $exception) {
        }
        return 'error';
    }

    private function gallery(int $seller_id)
    {
        try {
            $query = "SELECT image FROM ms_seller_gallery WHERE seller_id = $seller_id";

            $query = $this->db->query($query);
            $rows = $query->rows;
            $data = [];

            foreach ($rows as $row) {
                if ($row['image']) {
                    $image = HTTP_IMAGE  . $row['image'];
                } else {
                    $image = $this->model_tool_image->resize('no_image.jpg', 50, 50);
                }
                $data[] = ["url" => $image];
            }

            return array_filter($data);
        } catch (Exception $exception) {

        }
        return 'error';
    }

    private function videos(int $seller_id)
    {
        try {
            $query = "SELECT video_id FROM ms_seller_videos WHERE seller_id = $seller_id";

            $query = $this->db->query($query);
            $rows = $query->rows;
            $data = [];
            foreach ($rows as $row) {
                $data[] = ['videoId' => $row['video_id'],
                            'thumb' => "https://img.youtube.com/vi/" . $row['video_id'] . "/0.jpg"];

            }
            return array_filter($data);
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
        return 'error';
    }

    /** Get All Messages related to the customer and the seller of the product
     * @param $user1_id
     * @param $user2_id
     * @param null $latest_id
     * @return array
     */
    public function getsellerMessages($user1_id, $user2_id, $latest_id = null) {
        $query = "SELECT * FROM " . DB_PREFIX . "ms_messaging WHERE ";
        $query .= "(user1_id = '{$user1_id}' AND user2_id = '{$user2_id}') OR ";
        $query .= "(user1_id = '{$user2_id}' AND user2_id = '{$user1_id}')";
        $conversation = $this->db->query($query);

        $product_id = 0;
        $subject = '';
        $messages = false;

        if ($conversation->num_rows) {

            $product_id   = $conversation->row['product_id'];
            $subject      = $conversation->row['subject'];
            $messaging_id = (int)$conversation->row['id'];

            $conv_msg_query = "SELECT *, IF(owner_id=$user1_id,'sender','reciever') AS type FROM " . DB_PREFIX . "ms_messaging_msgs";
            if($latest_id)
                $conv_msg_query .= " WHERE messaging_id = '{$messaging_id}' AND id > '{$latest_id}'";
            else
                $conv_msg_query .= " WHERE messaging_id = '{$messaging_id}'";

            $conv_msgs = $this->db->query($conv_msg_query);

            if ($conv_msgs->num_rows) {
                $messages = $conv_msgs->rows;
                $query = "UPDATE " . DB_PREFIX . "ms_messaging_msgs SET `read` = 1 WHERE owner_id='{$user2_id}'";
                $this->db->query($query);
            }
        }

        return ['subject' => $subject, 'messages' => $messages, 'product_id' => $product_id];
    }

    /**
     * Create new Message Between the Customer and the seller
     * @param $data
     * @return int
     */
    public function PostMessageToSeller($data){
        
        $current_user_id = (int)$data['user1_id'];
        $chated_user_id = (int)$data['user2_id'];

        $query = "SELECT * FROM " . DB_PREFIX . "ms_messaging WHERE ";
        $query .= "(user1_id = '{$chated_user_id}'  AND user2_id = '{$current_user_id}') OR ";
        $query .= "(user2_id = '{$chated_user_id}'  AND user1_id = '{$current_user_id}')";

        $conversation = $this->db->query($query);
        
        if ($conversation->num_rows) {
            $messaging_id = (int)$conversation->row['id'];
        } else {

            $user1_id = $current_user_id;
            $user2_id = $chated_user_id;
            $user1_type = 'c';
            $user2_type = 's';
            $subject = $data['subject'];
            $product_id = (int)$data['product_id'];

            $query = "INSERT INTO " . DB_PREFIX . "ms_messaging SET ";
            $query .= "user1_id = '{$user1_id}', user2_id = '{$user2_id}', ";
            $query .= "user1_type = '{$user1_type}', user2_type = '{$user2_type}', ";
            $query .= "subject = '{$subject}', product_id = '{$product_id}', status = 1";
            $this->db->query($query);

            $messaging_id = (int)$this->db->getLastId();
        }

        $success = false;

        if($messaging_id) {

            $owner = 's';
            $owner_id = (int)$data['user1_id'];
            $message = $this->db->escape($data['msg']);

            $query = "INSERT INTO " . DB_PREFIX . "ms_messaging_msgs SET ";
            $query .= "messaging_id = {$messaging_id}, owner_id = '{$owner_id}', owner = '{$owner}', message = '{$message}'";
            $this->db->query($query);

            $success =  true;
        }

        return $success;
    }

    /**
     * Get All conversation related to the customer
     * @param $user_id
     * @return mixed
     */
    public function getConversations($user_id)
    {
        $query = "SELECT * ,IF(owner_id=$user_id,'sender','reciever') AS type ";
        $query .= "FROM " . DB_PREFIX . "ms_messaging mm ";
        $query .= "LEFT JOIN ms_messaging_msgs mmm ON (mm.id = mmm.messaging_id) ";
        $query .= "WHERE mm.user1_id={$user_id} OR mm.user2_id={$user_id}";
        return $this->db->query($query)->rows;
    }

 
    public function getOptionValues($option_id) {
		$option_value_data = array();

		$lang_id = $this->config->get('config_language_id') ? (int)$this->config->get('config_language_id') : 1;
		$option_value_query = $this->db->query("SELECT *, ov.option_value_id FROM " . DB_PREFIX . "option_value ov LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id AND ovd.language_id = '" . (int)$lang_id . "') WHERE ov.option_id = '" . (int)$option_id . "' ORDER BY ov.sort_order ASC");
				
		foreach ($option_value_query->rows as $option_value) {
			if (isset($option_value['valuable_type']) && $option_value['valuable_type'] == 'product' && \Extension::isInstalled('product_builder') && $this->config->get('product_builder')['status']) {
				$query = "SELECT p.product_id, pd.name FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd  ON (p.product_id = pd.product_id) WHERE p.product_id='{$option_value['valuable_id']}'";
				$query .= " AND pd.language_id = '{$lang_id}'";
				$opv_product = $this->db->query($query)->row;
			}
			$option_value_data[] = array(
				'option_value_id' => $option_value['option_value_id'],
				'name'            => isset($opv_product) && $opv_product ? $opv_product['name'] : $option_value['name'],
				'image'           => $option_value['image'],
				'sort_order'      => $option_value['sort_order']
			);
		}
		
		return $option_value_data;
	}
    public function updateSellerStatus($id, $status)
    {
      $this->db->query("UPDATE " . DB_PREFIX . "ms_seller SET seller_status='" . $status . "' WHERE seller_id = '" . (int)$id . "'");
    }
    
    function uploadImagebase64($image,$seller_id)
    {
        if (!\Filesystem::isDirExists('image/sellers/'. $seller_id . '/')) {
        \Filesystem::createDir('image/sellers/'. $seller_id . '/');
        }
        $filename =   time() . '_' . md5(rand());
        list($type, $image) = explode(';', $image);
        list(, $image)      = explode(',', $image);
        $ext = end(explode('/', $type));
        $image = base64_decode($image);
        $imageName= 'sellers/'.$seller_id.'/'.$filename.'.'.$ext;
        file_put_contents(DIR_IMAGE .  $imageName, $image);
     
        return $imageName;
    }
    

}