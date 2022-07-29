<?php
use ExpandCart\Foundation\String\Slugify;

final class MsSeller extends Model {
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;
	const STATUS_DISABLED = 3;
	const STATUS_DELETED = 4;
	const STATUS_UNPAID = 5;
	const STATUS_NOPAYMENT = 6;

	const MS_SELLER_VALIDATION_NONE = 1;
	const MS_SELLER_VALIDATION_ACTIVATION = 2;
	const MS_SELLER_VALIDATION_APPROVAL = 3;

	private $isSeller = FALSE; 
	private $nickname;
	private $description;
	private $company;
	private $country_id;
	private $avatar;
	private $seller_status;
	private $paypal;
	private $bank_transfer;
	private $subscription_plan;
	private $is_recurring;

  	public function __construct($registry) {
  		parent::__construct($registry);
  		
  		//$this->log->write('creating seller object: ' . $this->session->data['customer_id']);
		if (isset($this->session->data['customer_id'])) {
			//TODO 
			//$seller_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ms_seller WHERE seller_id = '" . (int)$this->session->data['customer_id'] . "' AND seller_status = '1'");
			$seller_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ms_seller WHERE seller_id = '" . (int)$this->session->data['customer_id'] . "'");

			if ($seller_query->num_rows) {
				$this->isSeller = TRUE;
				$this->nickname = $seller_query->row['nickname'];
				$this->description = $seller_query->row['description'];
				$this->company = $seller_query->row['company'];
				$this->website = $seller_query->row['website'];
				$this->country_id = $seller_query->row['country_id'];
				$this->avatar = $seller_query->row['avatar'];
				$this->seller_status = $seller_query->row['seller_status'];
				if (isset($seller_query->row['subscription_plan'])) {
                    $this->subscription_plan = $seller_query->row['subscription_plan'];
                } else {
                    $this->subscription_plan = null;
				}
                if (isset($seller_query->row['is_recurring'])) {
                    $this->is_recurring = $seller_query->row['is_recurring'];
                } else {
                    $this->is_recurring = null;
                }
				$this->date_created = $seller_query->row['date_created'];
				$this->paypal = $seller_query->row['paypal'];
				$this->bank_transfer = $seller_query->row['bank_transfer'];
			}
  		}
	}
		
  	public function isCustomerSeller($customer_id) {
		$sql = "SELECT COUNT(*) as 'total'
				FROM `" . DB_PREFIX . "ms_seller`
				WHERE seller_id = " . (int)$customer_id;
		
		$res = $this->db->query($sql);
		
		if ($res->row['total'] == 0)
			return FALSE;
		else
			return TRUE;	  		
  	}

	public function getSellerName($seller_id) {
		$sql = "SELECT firstname as 'firstname'
				FROM `" . DB_PREFIX . "customer`
				WHERE customer_id = " . (int)$seller_id;
		
		$res = $this->db->query($sql);
		
		return $res->row['firstname'];
	}	
	
	public function getSellerEmail($seller_id) {
		$sql = "SELECT email as 'email' 
				FROM `" . DB_PREFIX . "customer`
				WHERE customer_id = " . (int)$seller_id;
		
		$res = $this->db->query($sql);
		
		return $res->row['email'];
	}
		
	public function createSeller($data) {
        $avatar = $data['avatar_name'] ?? '';

        $commercial_image = $data['commercial_image_name'] ?? '';

        $license_image = $data['license_image_name'] ?? ''; // is not used in any where

        $tax_image = $data['tax_image_name'] ?? '';

        $image_id = $data['image_id_name'] ?? '';
		
		if (isset($data['commission']))
			$commission_id = $this->MsLoader->MsCommission->createCommission($data['commission']);
		
		$sql = "INSERT INTO " . DB_PREFIX . "ms_seller
				SET seller_id = " . (int)$data['seller_id'] . ",
					seller_status = " . (int)$data['status'] . ",
					seller_approved = " . (int)$data['approved'] . ",
					seller_group = " . (int)(isset($data['seller_group']) ? $data['seller_group'] : $this->config->get('msconf_default_seller_group_id')) . ",
					nickname = '" . $this->db->escape($data['nickname']) . "',
					slug = '" . $this->db->escape((new Slugify)->slug($data['nickname'])) . "',
					mobile = '" . $this->db->escape($data['mobile']) . "',
					website = '" . $this->db->escape($data['website']) . "',
					description = '" . $this->db->escape($data['description']) . "',
					company = '" . $this->db->escape($data['company']) . "',
					tax_card = '" . $this->db->escape($data['tax_card']) . "',
					commercial_reg = '" . $this->db->escape($data['commercial_reg']) . "',
					rec_exp_date = '" . $this->db->escape($data['rec_exp_date']) . "',
					license_num = '" . $this->db->escape($data['license_num']) . "',
					lcn_exp_date = '" . $this->db->escape($data['lcn_exp_date']) . "',
					personal_id = '" . $this->db->escape($data['personal_id']) . "',
					country_id = " . (int)$data['country'] . ",
					zone_id = " . (int)$data['zone'] . ",
					commission_id = " . (isset($commission_id) ? $commission_id : 'NULL') . ",
					product_validation = " . (int)$data['product_validation'] . ",
					paypal = '" . $this->db->escape($data['paypal']) . "',
					bank_name = '" . $this->db->escape($data['bank_name']) . "',
					bank_iban = '" . $this->db->escape($data['bank_iban']) . "',
					bank_transfer = '" . $this->db->escape($data['bank_transfer']) . "',
					avatar = '" . $this->db->escape($avatar) . "',
					image_id = '" . $this->db->escape($image_id) . "',
					commercial_image = '" . $this->db->escape($commercial_image) . "',
					license_image = '" . $this->db->escape($license_image) . "',
					tax_image = '" . $this->db->escape($tax_image) . "',
					subscription_plan = '" . $this->db->escape($data['subscription_plan']) . "',
					seller_location = '" . $this->db->escape($data['seller_location']) . "',
					custom_fields = '" . $this->db->escape($data['custom_fields']) . "',
					date_created = NOW(),
					sellerAffiliateReferral = '".$this->db->escape($this->request->cookie['sellerTracking'])."' ";

		$this->db->query($sql);
		$seller_id = $this->db->getLastId();

		if (isset($data['keyword'])) {
			$similarity_query = $this->db->query("SELECT * FROM ". DB_PREFIX . "url_alias WHERE keyword LIKE '" . $this->db->escape($data['keyword']) . "%'");
			$number = $similarity_query->num_rows;
			
			if ($number > 0) {
				$data['keyword'] = $data['keyword'] . "-" . $number;
			}
			$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'seller_id=" . (int)$seller_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
		}
		$this->cache->delete('catalog_seller');
		$this->cache->delete('catalog_seller_total');
		return $seller_id;
	}

    public function createSellerFromAdmin($data)
	{
        if (isset($data['tmp_avatar_name'])) {
        	$image = $data['tmp_avatar_name'];
        	$filename =   time() . '_' . md5(rand());
			list($type, $image) = explode(';', $image);
			list(, $image)      = explode(',', $image);
			$ext = end(explode('/', $type));
			$image = base64_decode($image);
			$avatar = 'sellers/'.$data['seller_id'].'/'.$filename.'.'.$ext;
			file_put_contents(DIR_IMAGE . $avatar, $image);
		} else {
			$avatar = '';
		}

        if (isset($data['commission']))
            $commission_id = $this->MsLoader->MsCommission->createCommission($data['commission']);

        $sql = "INSERT INTO " . DB_PREFIX . "ms_seller
				SET seller_id = " . (int)$data['seller_id'] . ",
					seller_status = " . (int)$data['status'] . ",
					seller_approved = " . (int)$data['approved'] . ",
					seller_group = " .  (isset($data['seller_group']) ? (int)$data['seller_group'] : $this->config->get('msconf_default_seller_group_id'))  .  ",
					nickname = '" . $this->db->escape($data['nickname']) . "',
					slug = '" . $this->db->escape((new Slugify)->slug($data['nickname'])) . "',
					description = '" . $this->db->escape($data['description']) . "',
					company = '" . $this->db->escape($data['company']) . "',
					country_id = " . (int)$data['country'] . ",
					zone_id = " . (int)$data['zone'] . ",
					commission_id = " . (isset($commission_id) ? $commission_id : 'NULL') . ",
					product_validation = " . (int)$data['product_validation'] . ",
					paypal = '" . $this->db->escape($data['paypal']) . "',
					bank_transfer = '" . $this->db->escape($data['bank_transfer']) . "',
					avatar = '" . $this->db->escape($avatar) . "',
					subscription_plan = '" . $this->db->escape($data['subscription_plan']) . "',
					minimum_order = '" .  (float)$data['minimum_order'] .  "',
					view_minimum_alert = '" .  (int)$data['view_minimum_alert'] .  "',
					date_created = NOW()";

        $this->db->query($sql);
        $seller_id = $this->db->getLastId();

        if (isset($data['avatar_name'])) {
            $avatar = $this->MsLoader->MsFile->moveImage($data['avatar_name'], $seller_id);

            $queryString = [];
            $queryString[] = 'UPDATE ' . DB_PREFIX . 'ms_seller';
            $queryString[] = 'SET avatar="' . $this->db->escape($avatar) . '"';
            $queryString[] = 'WHERE seller_id=' . $seller_id;

            $this->db->query(implode(' ', $queryString));
        }


        if (isset($data['keyword'])) {
            $similarity_query = $this->db->query("SELECT * FROM ". DB_PREFIX . "url_alias WHERE keyword LIKE '" . $this->db->escape($data['keyword']) . "%'");
            $number = $similarity_query->num_rows;

            if ($number > 0) {
                $data['keyword'] = $data['keyword'] . "-" . $number;
            }
            $this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'seller_id=" . (int)$seller_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
		}

		$this->cache->delete('catalog_seller');
		$this->cache->delete('catalog_seller_total');
    }
	
	public function nicknameTaken($nickname) {
		$sql = "SELECT nickname
				FROM `" . DB_PREFIX . "ms_seller` p
				WHERE p.nickname = '" . $this->db->escape($nickname) . "'";
		
		$res = $this->db->query($sql);

		return $res->num_rows;
	}
	
	public function editSeller($data) {
		$seller_id = (int)$data['seller_id'];

		$old_avatar = $this->getSellerAvatar($seller_id);
		
		if (!isset($data['avatar_name']) || ($old_avatar['avatar'] != $data['avatar_name'])) {
			$this->MsLoader->MsFile->deleteImage($old_avatar['avatar']);
		}
		
		if (isset($data['avatar_name'])) {
			if ($old_avatar['avatar'] != $data['avatar_name']) {	
				$filename =   time() . '_' . md5(rand());

				if (\Filesystem::isDirExists('image/sellers/'.$data['seller_id'].'/') == false) {
					\Filesystem::createDir('image/sellers/'.$data['seller_id'].'/');
					\Filesystem::setPath('image/sellers/'.$data['seller_id'].'/index.html')->write('');
				}
				$image=DIR_IMAGE.$data['avatar_name'];
				$ext = pathinfo($image, PATHINFO_EXTENSION);
				$image = file_get_contents($image);
				$image = 'data:image/' . $ext . ';base64,' . base64_encode($image);
				$avatar = 'sellers/'.$data['seller_id'].'/'.$filename.'.'.$ext;
				\Filesystem::setPath('image/' . $avatar)->upload($image);
				$this->MsLoader->MsFile->deleteImage($data['avatar_name']);

			//	$avatar = $this->MsLoader->MsFile->moveImage($data['avatar_name']);
			} else {
				$avatar = $old_avatar['avatar'];
			}
		} else {
			$avatar = '';
		}

		if (!isset($data['commercial_image_name']) || ($old_avatar['commercial_image'] != $data['commercial_image_name'])) {
			$this->MsLoader->MsFile->deleteImage($old_avatar['commercial_image'], DIR_SELLER_FILES);
		}
		if (isset($data['commercial_image_name'])) {
			if ($old_avatar['commercial_image'] != $data['commercial_image_name']) {			
				$commercial_image = $this->MsLoader->MsFile->moveImage($data['commercial_image_name']);
			} else {
				$commercial_image = $old_avatar['commercial_image'];
			}
		} else {
			$commercial_image = '';
		}

		if (!isset($data['license_image_name']) || ($old_avatar['license_image'] != $data['license_image_name'])) {
			$this->MsLoader->MsFile->deleteImage($old_avatar['license_image'], DIR_SELLER_FILES);
		}
		if (isset($data['license_image_name'])) {
			if ($old_avatar['license_image'] != $data['license_image_name']) {			
				$license_image = $this->MsLoader->MsFile->moveImage($data['license_image_name']);
			} else {
				$license_image = $old_avatar['license_image'];
			}
		} else {
			$license_image = '';
		}

		if (!isset($data['tax_image_name']) || ($old_avatar['tax_image'] != $data['tax_image_name'])) {
			$this->MsLoader->MsFile->deleteImage($old_avatar['tax_image'], DIR_SELLER_FILES);
		}
		if (isset($data['tax_image_name'])) {
			if ($old_avatar['tax_image'] != $data['tax_image_name']) {			
				$tax_image = $this->MsLoader->MsFile->moveImage($data['tax_image_name']);
			} else {
				$tax_image = $old_avatar['tax_image'];
			}
		} else {
			$tax_image = '';
		}

		if (!isset($data['image_id_name']) || ($old_avatar['image_id'] != $data['image_id_name'])) {
			$this->MsLoader->MsFile->deleteImage($old_avatar['image_id'], DIR_SELLER_FILES);
		}
		if (isset($data['image_id_name'])) {
			if ($old_avatar['image_id'] != $data['image_id_name']) {			
				$image_id = $this->MsLoader->MsFile->moveImage($data['image_id_name']);
			} else {
				$image_id = $old_avatar['image_id'];
			}
		} else {
			$image_id = '';
		}
// var_dump($data['seller_location']);die();
		$sql = "UPDATE " . DB_PREFIX . "ms_seller
				SET mobile = '" . $this->db->escape($data['mobile']) . "',
					description = '" . $this->db->escape($data['description']) . "',
					company = '" . $this->db->escape($data['company']) . "',
					tax_card = '" . $this->db->escape($data['tax_card']) . "',
					commercial_reg = '" . $this->db->escape($data['commercial_reg']) . "',
					rec_exp_date = '" . $this->db->escape($data['rec_exp_date']) . "',
					license_num = '" . $this->db->escape($data['license_num']) . "',
					lcn_exp_date = '" . $this->db->escape($data['lcn_exp_date']) . "',
					personal_id = '" . $this->db->escape($data['personal_id']) . "',
					website = '" . $this->db->escape($data['website']) . "',
					nickname = '" . $this->db->escape($data['nickname']) . "',
					slug = '" . $this->db->escape((new Slugify)->slug($data['nickname'])) . "',
					country_id = " . (int)$data['country'] . ",
					zone_id = " . (int)$data['zone'] . ","
					. (isset($data['status']) ? "seller_status=  " .  (int)$data['status'] . "," : '')
					. (isset($data['approved']) ? "seller_approved=  " .  (int)$data['approved'] . "," : '')
					. "paypal = '" . $this->db->escape($data['paypal']) . "',
					bank_name = '" . $this->db->escape($data['bank_name']) . "',
					bank_iban = '" . $this->db->escape($data['bank_iban']) . "',
					bank_transfer= '".$this->db->escape($data['bank_transfer']) . "',
					avatar = '" . $this->db->escape($avatar) . "',
					image_id = '" . $this->db->escape($image_id) . "',
					commercial_image = '" . $this->db->escape($commercial_image) . "',
					license_image = '" . $this->db->escape($license_image) . "',
					tax_image = '" . $this->db->escape($tax_image) . "',
					payment_methods = '" . $this->db->escape(json_encode($data['payment_methods'])) . "',
					seller_location = '" . $this->db->escape($data['seller_location']) . "',
					custom_fields = '" . $this->db->escape($data['custom_fields']) . "'
				WHERE seller_id = " . (int)$seller_id;
		
		$this->db->query($sql);
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'seller_id=" . (int)$seller_id. "'");
		if (isset($data['keyword'])) {
			$similarity_query = $this->db->query("SELECT * FROM ". DB_PREFIX . "url_alias WHERE keyword LIKE '" . $this->db->escape($data['keyword']) . "%'");
			$number = $similarity_query->num_rows;
			
			if ($number > 0) {
				$data['keyword'] = $data['keyword'] . "-" . $number;
			}
			
			$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'seller_id=" . (int)$seller_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
		}

		$this->cache->delete('catalog_seller');
		$this->cache->delete('catalog_seller_total');
	}

    public function updateSellerPlan($sellerId, $data)
    {
		$queryString = [];
		$queryString[] = 'UPDATE ' . DB_PREFIX . 'ms_seller SET';
        $queryString[] = 'subscription_plan="' . (int)$data['subscription_plan'] . '" ,';
        $queryString[] = 'is_recurring="' . (int)$data['isRecurring'] . '"';
        $queryString[] = 'WHERE seller_id="' . $sellerId . '"';

        $this->db->query(implode(' ', $queryString));
    }

    public function updateSellerStatus($id, $status)
    {

        $query = 'UPDATE ' . DB_PREFIX . 'ms_seller SET seller_status=' . $status . ' WHERE seller_id="' . $id . '"';

        if($this->db->query($query)){

        	$this->db->query('UPDATE ' . DB_PREFIX . 'ms_product SET product_status=' . $status . ' WHERE seller_id="' . $id . '"');

        	//Enable All Seller's Products
        	if($status == 1){
        		$prd_status = 1;
        	}
        	//Disable All Seller's Products
        	else if($status == 2){
        		$prd_status = 0;
        	}

        	$ms_prd_query = "SELECT product_id FROM `" . DB_PREFIX . "ms_product` WHERE seller_id = " . (int)$id;
        	$updt_query   = 'UPDATE ' . DB_PREFIX . 'product SET status=' . $prd_status . ' WHERE product_id  IN (' . $ms_prd_query . ')';
        	$this->db->query($updt_query);

        	$this->cache->delete('catalog_seller');
			$this->cache->delete('catalog_seller_total');
        }
    }

	public function getSellerAvatar($seller_id) {
		$query = $this->db->query("SELECT avatar as avatar FROM " . DB_PREFIX . "ms_seller WHERE seller_id = '" . (int)$seller_id . "'");
		
		return $query->row;
	}		
		
  	public function getNickname() {
  		return $this->nickname;
  	}

  	public function getCompany() {
  		return $this->company;
  	}
  	
  	public function getCountryId() {
  		return $this->country_id;
  	}

  	public function getDescription() {
  		return $this->description;
  	}
  	
  	public function getAvatarPath() {
  		return $this->avatar;
  	}
  	
  	public function getStatus() {
  		return $this->seller_status;
  	}

  	public function getPaypal() {
  		return $this->paypal;
  	}

    public function getBankTransfer() {
        return $this->bank_transfer;
    }
  	
  	public function isSeller() {
  		return $this->isSeller;
  	}

  	public function getSubscriptionPlan()
	{
		return ($this->subscription_plan ? $this->subscription_plan : 0);
	}

    public function isRecurring()
    {
        return (int)$this->is_recurring;
    }

    public function getDateCreated()
    {
        return $this->date_created;
    }
  	
	public function getSalesForSeller($seller_id) {
		$sql = "SELECT IFNULL(SUM(number_sold),0) as total
				FROM `" . DB_PREFIX . "ms_product`
				WHERE seller_id = " . (int)$seller_id;
		
		$res = $this->db->query($sql);
		
		return $res->row['total'];
	}

	public function getSellerProductsCount($seller_id) {
		$sql = "SELECT COUNT(*) as total
				FROM `" . DB_PREFIX . "ms_product`
				WHERE seller_id = " . (int)$seller_id;
		
		$res = $this->db->query($sql);
		
		return $res->row['total'];
	}
	
	public function getSalt($seller_id) {
		$sql = "SELECT salt
				FROM `" . DB_PREFIX . "customer`
				WHERE customer_id = " . (int)$seller_id;
		
		$res = $this->db->query($sql);
		
		return $res->row['salt'];		
	}
	
	
	public function adminEditSeller($data) {
		$seller_id = (int)$data['seller_id'];


		if (isset($data['tmp_avatar_name']) && empty($data['tmp_avatar_name']) == false) {

			$old_avatar = $this->getSellerAvatar($seller_id);
		
			if (!isset($data['tmp_avatar_name']) || ($old_avatar['avatar'] != $data['tmp_avatar_name'])) {
				$this->MsLoader->MsFile->deleteImage($old_avatar['avatar']);
			}

        	$image = $data['tmp_avatar_name'];
        	$filename =   time() . '_' . md5(rand());

        	if (\Filesystem::isDirExists('image/sellers/'.$data['seller_id'].'/') == false) {
        		// mkdir(DIR_IMAGE . 'sellers/'.$data['seller_id'].'/', 0755);
        		// @touch(DIR_IMAGE . 'sellers/'.$data['seller_id'].'/' . 'index.html');
        		\Filesystem::createDir('image/sellers/'.$data['seller_id'].'/');
        		\Filesystem::setPath('image/sellers/'.$data['seller_id'].'/index.html')->write('');
        	}

			list($type, $image) = explode(';', $image);
			list(, $image)      = explode(',', $image);
			$ext = end(explode('/', $type));
			$image = base64_decode($image);
			$avatar = 'sellers/'.$data['seller_id'].'/'.$filename.'.'.$ext;
			// file_put_contents(DIR_IMAGE . $avatar, $image);
			\Filesystem::setPath('image/' . $avatar)->put($image);
		} else {
			$avatar = '';
		}

        //$avatar = $data['tmp_avatar_name'];

		// commissions
		if (!$data['commission_id']) {
			$commission_id = $this->MsLoader->MsCommission->createCommission($data['commission']);
		} else {
			$commission_id = $this->MsLoader->MsCommission->editCommission($data['commission_id'], $data['commission']);
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'seller_id=" . (int)$seller_id. "'");
		
		if (isset($data['keyword'])) {
			$similarity_query = $this->db->query("SELECT * FROM ". DB_PREFIX . "url_alias WHERE keyword LIKE '" . $this->db->escape($data['keyword']) . "%'");
			$number = $similarity_query->num_rows;
			
			if ($number > 0) {
				$data['keyword'] = $data['keyword'] . "-" . $number;
			}
			$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'seller_id=" . (int)$seller_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
		}

        if (isset($data['avatar_name'])) {
            $avatar = $this->MsLoader->MsFile->moveImage($data['avatar_name'], $seller_id);
        }

        if ($avatar == '') {
        	$old_avatar = $this->getSellerAvatar($seller_id);
        	$avatar = $old_avatar['avatar'];
        }

		$sql = "UPDATE " . DB_PREFIX . "ms_seller
				SET description = '" . $this->db->escape($data['description']) . "',
					company = '" . $this->db->escape($data['company']) . "',
					country_id = " . (int)$data['country'] . ",
					paypal = '" . $this->db->escape($data['paypal']) . "',
					bank_transfer = '" . $this->db->escape($data['bank_transfer']) . "',
					seller_status = '" .  (int)$data['status'] .  "',
					zone_id = " . (int)$data['zone'] . ",
					seller_approved = '" .  (int)$data['approved'] .  "',
					avatar = '" .  $avatar .  "',
					product_validation = '" .  (int)$data['product_validation'] .  "',
					commission_id = " . (!is_null($commission_id) ? (int)$commission_id : 'NULL' ) . ",
					minimum_order = '" .  (float)$data['minimum_order'] .  "',
					view_minimum_alert = '" .  (int)$data['view_minimum_alert'] .  "',
					seller_group = '" .  (int)$data['seller_group'] .  "'
				WHERE seller_id = " . (int)$seller_id;
		
		$this->db->query("UPDATE " . DB_PREFIX . "ms_balance SET balance = '" . $this->db->escape($data['balance']) . "' WHERE seller_id = " . (int)$seller_id);
		
		$this->db->query($sql);	
		$this->cache->delete('catalog_seller');
		$this->cache->delete('catalog_seller_total');
	}
	
	/********************************************************/
	
	
	public function getTotalSellers($data = array()) {
		$sql = "
			SELECT COUNT(*) as total
			FROM " . DB_PREFIX . "ms_seller ms
			WHERE 1 = 1 "
			. (isset($data['seller_status']) ? " AND seller_status IN  (" .  $this->db->escape(implode(',', $data['seller_status'])) . ")" : '');

		$res = $this->db->query($sql);

		return $res->row['total'];
	}
	
	public function getSeller($seller_id, $data = array()) {
		$sql = "SELECT	CONCAT(c.firstname, ' ', c.lastname) as name,
						c.email as 'c.email',
						ms.seller_id as 'seller_id',
						ms.nickname as 'ms.nickname',
						ms.mobile as 'ms.mobile',
						ms.company as 'ms.company',
						ms.website as 'ms.website',
						ms.tax_card as 'ms.tax_card',
						ms.commercial_reg as 'ms.commercial_reg',
						ms.rec_exp_date as 'ms.rec_exp_date',
						ms.license_num as 'ms.license_num',
						ms.lcn_exp_date as 'ms.lcn_exp_date',
						ms.personal_id as 'ms.personal_id',
						ms.paypal as 'ms.paypal',
						ms.bank_name as 'ms.bank_name',
						ms.bank_iban as 'ms.bank_iban',
						ms.bank_transfer as 'ms.bank_transfer',
						ms.seller_status as 'ms.seller_status',
						ms.seller_approved as 'ms.seller_approved',
						ms.date_created as 'ms.date_created',
						ms.product_validation as 'ms.product_validation',
						ms.avatar as 'ms.avatar',
						ms.image_id as 'ms.image_id',
						ms.commercial_image as 'ms.commercial_image',
						ms.license_image as 'ms.license_image',
						ms.tax_image as 'ms.tax_image',
						ms.country_id as 'ms.country_id',
						ms.zone_id as 'ms.zone_id',
						ms.description as 'ms.description',
						ms.commission_id as 'ms.commission_id',
						ms.seller_group as 'ms.seller_group',
						ms.minimum_order as 'ms.minimum_order',
						ms.view_minimum_alert as 'ms.view_minimum_alert',
						ms.seller_location as 'ms.seller_location',
						ms.payment_methods,
						ms.custom_fields as 'ms.custom_fields',
						IFNULL(SUM(mp.number_sold), 0) as 'total_sales',
						(SELECT keyword FROM `" . DB_PREFIX . "url_alias` WHERE `query` = 'seller_id=" . (int)$seller_id . "' LIMIT 1) AS keyword,

						(SELECT AVG(rating) AS total FROM " . DB_PREFIX . "ms_seller_review r1
				            WHERE r1.seller_id = ms.seller_id AND r1.status = '1' GROUP BY r1.seller_id)
				        AS rating,
				        (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "ms_seller_review r2
				            WHERE r2.seller_id = ms.seller_id AND r2.status = '1' GROUP BY r2.seller_id)
            			AS reviews
            			
				FROM `" . DB_PREFIX . "customer` c
				INNER JOIN `" . DB_PREFIX . "ms_seller` ms
					ON (c.customer_id = ms.seller_id)
				LEFT JOIN `" . DB_PREFIX . "ms_product` mp
					ON (c.customer_id = mp.seller_id)
				WHERE ms.seller_id = " .  (int)$seller_id
				. (isset($data['product_id']) ? " AND mp.product_id =  " .  (int)$data['product_id'] : '')
				. (isset($data['seller_status']) ? " AND seller_status IN  (" .  $this->db->escape(implode(',', $data['seller_status'])) . ")" : '')
				. " GROUP BY ms.seller_id
				LIMIT 1";
				
		$res = $this->db->query($sql);

		if (!isset($res->row['seller_id']) || !$res->row['seller_id'])
			return FALSE;
		else
			return $res->row;
	}	
	
	public function getSellerBasic($seller_id) {
		$sql = "SELECT c.firstname,  c.lastname, c.email, ms.nickname, ms.mobile 
				FROM `" . DB_PREFIX . "customer` c
				INNER JOIN `" . DB_PREFIX . "ms_seller` ms
					ON (c.customer_id = ms.seller_id)
				WHERE customer_id = " . (int)$seller_id;
		
		$res = $this->db->query($sql);

		return $res->row;
	}	

	public function getSellers($data = array(), $sort = array(), $cols = array()) {
		$hFilters = $wFilters = '';
		if(isset($sort['filters'])) {
			$cols = array_merge($cols, array("`c.name`" => 1, "total_sales" => 1, "`ms.date_created`" => 1));
			foreach($sort['filters'] as $k => $v) {
				if (!isset($cols[$k])) {
					$wFilters .= " AND {$k} LIKE '%" . $this->db->escape($v) . "%'";
				} else {
					$hFilters .= " AND {$k} LIKE '%" . $this->db->escape($v) . "%'";
				}
			}
		}
		
		$sql = "SELECT
					SQL_CALC_FOUND_ROWS"
					// additional columns
					. (isset($cols['total_products']) ? "
						(SELECT COUNT(*) FROM " . DB_PREFIX . "product p
						LEFT JOIN " . DB_PREFIX . "ms_product mp USING (product_id)
						LEFT JOIN " . DB_PREFIX . "ms_seller USING (seller_id)
						WHERE seller_id = ms.seller_id) as total_products,
					" : "")

					. (isset($cols['total_earnings']) ? "
						(SELECT COALESCE(SUM(amount),0)
							- (SELECT COALESCE(ABS(SUM(amount)),0)
								FROM `" . DB_PREFIX . "ms_balance`
								WHERE seller_id = ms.seller_id
								AND balance_type = ". MsBalance::MS_BALANCE_TYPE_REFUND
						. ") as total
						FROM `" . DB_PREFIX . "ms_balance`
						WHERE seller_id = ms.seller_id
						AND balance_type = ". MsBalance::MS_BALANCE_TYPE_SALE . ") as total_earnings,
					" : "")
					
					. (isset($cols['current_balance']) ? "
						(SELECT COALESCE(
							(SELECT balance FROM " . DB_PREFIX . "ms_balance
								WHERE seller_id = ms.seller_id  
								ORDER BY balance_id DESC
								LIMIT 1
							),
							0
						)) as current_balance,
					" : "")	
					
					// default columns
					." CONCAT(c.firstname, ' ', c.lastname) as 'c.name',
					c.email as 'c.email',
					ms.seller_id as 'seller_id',
					ms.nickname as 'ms.nickname',
					ms.company as 'ms.company',
					ms.website as 'ms.website',
					ms.seller_status as 'ms.seller_status',
					ms.seller_approved as 'ms.seller_approved',
					ms.date_created as 'ms.date_created',
					ms.avatar as 'ms.avatar',
					ms.country_id as 'ms.country_id',
					ms.zone_id as 'ms.zone_id',
					ms.description as 'ms.description',
					ms.paypal as 'ms.paypal',
					ms.bank_transfer as 'ms.bank_transfer',
					IFNULL(SUM(mp.number_sold), 0) as 'total_sales'
				FROM `" . DB_PREFIX . "customer` c
				INNER JOIN `" . DB_PREFIX . "ms_seller` ms
					ON (c.customer_id = ms.seller_id)
				LEFT JOIN `" . DB_PREFIX . "ms_product` mp
					ON (c.customer_id = mp.seller_id)
				WHERE 1 = 1 "
				. (isset($data['seller_id']) ? " AND ms.seller_id =  " .  (int)$data['seller_id'] : '')
				. (isset($data['seller_status']) ? " AND seller_status IN  (" .  $this->db->escape(implode(',', $data['seller_status'])) . ")" : '')
				
				. $wFilters
				
				. " GROUP BY ms.seller_id HAVING 1 = 1 "
				
				. $hFilters
				
				. (isset($sort['order_by']) ? " ORDER BY {$sort['order_by']} {$sort['order_way']}" : '')
				. (isset($sort['limit']) ? " LIMIT ".(int)$sort['offset'].', '.(int)($sort['limit']) : '');

		
		$res = $this->db->query($sql);
		$total = $this->db->query("SELECT FOUND_ROWS() as total");
		if ($res->rows) $res->rows[0]['total_rows'] = $total->row['total'];
		
		return $res->rows;
	}
	
	public function getCustomers($sort = array()) {
		$sql = "SELECT  CONCAT(c.firstname, ' ', c.lastname) as 'c.name',
						c.email as 'c.email',
						c.customer_id as 'c.customer_id',
						ms.seller_id as 'seller_id'
				FROM `" . DB_PREFIX . "customer` c
				LEFT JOIN `" . DB_PREFIX . "ms_seller` ms
					ON (c.customer_id = ms.seller_id)
				WHERE ms.seller_id IS NULL"
				. (isset($sort['order_by']) ? " ORDER BY {$sort['order_by']} {$sort['order_way']}" : '')
    			. (isset($sort['limit']) ? " LIMIT ".(int)$sort['offset'].', '.(int)($sort['limit']) : '');

		$res = $this->db->query($sql);
		
		return $res->rows;
	}
	
	public function getTotalEarnings($seller_id, $data = array()) {
		// note: update getSellers() if updating this
		$sql = "SELECT COALESCE(SUM(amount),0)
					   - (SELECT COALESCE(ABS(SUM(amount)),0)
						  FROM `" . DB_PREFIX . "ms_balance`
                          LEFT JOIN order_product p ON (ms_balance.order_id = p.order_id) AND (ms_balance.product_id = p.product_id)
					 	  WHERE seller_id = " . (int)$seller_id . "
						  AND balance_type = ". MsBalance::MS_BALANCE_TYPE_REFUND
						  . (isset($data['period_start']) ? " AND DATEDIFF(date_created, '{$data['period_start']}') >= 0" : "")
				. ") as total
				FROM `" . DB_PREFIX . "ms_balance`
				LEFT JOIN order_product p ON (ms_balance.order_id = p.order_id) AND (ms_balance.product_id = p.product_id)
				WHERE seller_id = " . (int)$seller_id . "
				AND balance_type = ". MsBalance::MS_BALANCE_TYPE_SALE
				. (isset($data['period_start']) ? " AND DATEDIFF(date_created, '{$data['period_start']}') >= 0" : "");

		$res = $this->db->query($sql);
		return $res->row['total'];
	}
	
	public function changeStatus($seller_id, $seller_status) {
		$sql = "UPDATE " . DB_PREFIX . "ms_seller
				SET	seller_status =  " .  (int)$seller_status . "
				WHERE seller_id = " . (int)$seller_id;
		
		$res = $this->db->query($sql);
		$this->cache->delete('catalog_seller');
		$this->cache->delete('catalog_seller_total');
	}
	
	public function changeApproval($seller_id, $approved) {
		$sql = "UPDATE " . DB_PREFIX . "ms_seller
				SET	approved =  " .  (int)$approved . "
				WHERE seller_id = " . (int)$seller_id;
		
		$res = $this->db->query($sql);
		$this->cache->delete('catalog_seller');
		$this->cache->delete('catalog_seller_total');
	}
	
	public function deleteSeller($seller_id) {
		$products = $this->MsLoader->MsProduct->getProducts(array('seller_id' => $seller_id));

		foreach ($products as $product) {
			$this->MsLoader->MsProduct->changeStatus($product['product_id'], MsProduct::STATUS_DELETED);
		}
	
		$this->db->query("DELETE FROM " . DB_PREFIX . "ms_seller WHERE seller_id = '" . (int)$seller_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "ms_balance WHERE seller_id = '" . (int)$seller_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "ms_payment WHERE seller_id = '" . (int)$seller_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE `query` = 'seller_id=".(int)$seller_id."'");

		$isWarehouses = $this->db->query('show tables like "warehouses"');
        if ($isWarehouses->num_rows) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "warehouses WHERE seller_id = '" . (int)$seller_id . "'");
        }

		$this->cache->delete('catalog_seller');
		$this->cache->delete('catalog_seller_total');
		
	}

	/**
	 * Get seller payment methods.
	 *
	 * @param int $sellerId
	 *
	 * return array|bool
	 */
	public function getPaymentMethods($sellerId)
	{
		$query = [];

		$query[] = 'SELECT payment_methods FROM ms_seller';
		$query[] = 'WHERE seller_id = "'. (int) $sellerId .'"';

		$data = $this->db->query(implode(' ', $query));

		if ($data->num_rows) {

			$data = array_map(function ($v) {
				return ['code' => $v, 'ms_payment_gateway' => true];
			}, json_decode($data->row['payment_methods'], true));

			return $data;
		}

		return false;
	}

    /**
     * Get seller id by his/her nickname.
     *
     * @param string $nickname
     *
     * return array|bool
     */
	public function getSellerIdByNickname($nickname)
	{
		$query = 'SELECT seller_id FROM ms_seller WHERE nickname="' . $nickname . '"';

		$data = $this->db->query($query);

		if ($data->num_rows) {
			return $data->row;
		}

		return false;
	}

	/**
	 * Get all seller by product name or description.
     *
     * @param array $filter
     *
     * @return array|bool
     */



    public function getSellersByProduct($filter = array())
    {
	    $query = $columns = [];

	    $columns[] = 'ms.seller_id as "seller_id"';
        $columns[] = 'ms.nickname as "ms.nickname"';
        $columns[] = 'ms.slug as "ms.slug"';
        $columns[] = 'ms.company as "ms.company"';
        $columns[] = 'ms.website as "ms.website"';
        $columns[] = 'ms.seller_status as "ms.seller_status"';
        $columns[] = 'ms.seller_approved as "ms.seller_approved"';
        $columns[] = 'ms.date_created as "ms.date_created"';
        $columns[] = 'ms.avatar as "ms.avatar"';
        $columns[] = 'ms.country_id as "ms.country_id"';
        $columns[] = 'ms.zone_id as "ms.zone_id"';
        $columns[] = 'ms.description as "ms.description"';
        $columns[] = 'ms.paypal as "ms.paypal"';
        $columns[] = 'ms.bank_transfer as "ms.bank_transfer"';

	    $query[] = 'SELECT ' . implode(', ', $columns) . ' FROM ms_seller as ms';
	    $query[] = 'INNER JOIN ms_product as mp';
	    $query[] = 'ON ms.seller_id=mp.seller_id';
        $query[] = 'INNER JOIN product_description as pd';
        $query[] = 'ON mp.product_id=pd.product_id AND pd.language_id=%d';
        $query[] = 'WHERE pd.name like "%s" OR pd.description like "%s"
        			OR ms.nickname like "%s" OR ms.slug like "%s"';

        if (isset($filter['zone_id']) && !empty($filter['zone_id'])){
            $query[] = 'OR ms.zone_id  = ' .  $filter['zone_id'];
        }

		$query[] = 'GROUP BY seller_id';

        $data = $this->db->query(vsprintf(implode(' ', $query), [
            $this->config->get('config_language_id'),
            '%' . $filter['filter'] . '%',
            '%' . $filter['filter'] . '%',
            '%' . $filter['filter'] . '%',
            '%' . $filter['filter'] . '%'
        ]));
        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }

    /**
	 * Get all seller by product ID.
     *
     * @param array $filter
     *
     * @return array|bool
     */
    public function getSellerByProductId($productId)
    {
	    $query = $columns = [];

	    $customerQuery = 'SELECT concat(firstname, " ", lastname) FROM customer as c WHERE c.customer_id=ms.seller_id';

	    $columns[] = 'ms.*, mp.*, p.price as validPrice';
	    $columns[] = '(%s) as customerName';

	    $query[] = 'SELECT %s FROM ms_seller as ms';
	    $query[] = 'INNER JOIN ms_product as mp';
	    $query[] = 'ON ms.seller_id=mp.seller_id';
        $query[] = 'INNER JOIN product as p';
        $query[] = 'ON mp.product_id=p.product_id';
        $query[] = 'WHERE p.product_id = %d';

        $data = $this->db->query(vsprintf(implode(' ', $query), [
            sprintf(implode(', ', $columns), $customerQuery),
            $productId,
        ]));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }


    /****************** Seller Gallery Methods */
    ///Add image
    public function addGalleryImg($data) {
		$seller_id = (int)$data['seller_id'];

		$this->db->query("INSERT INTO " . DB_PREFIX . "ms_seller_gallery SET seller_id='" . (int)$seller_id . "', image = '" . $this->db->escape($data['image']) . "'");
	}

	///Get Image by id
	public function getGalleryImg($id) {
		$sql = "SELECT * 
				FROM `" . DB_PREFIX . "ms_seller_gallery`
				WHERE id = " . (int)$id;
		
		$res = $this->db->query($sql);

		return $res->row;
	}

	///Get All Images
	public function getGalleryImgs($seller_id) {
		$sql = "SELECT * 
				FROM `" . DB_PREFIX . "ms_seller_gallery`
				WHERE seller_id = " . (int)$seller_id;
		
		$res = $this->db->query($sql);
		return $res->rows;
	}

	///Remove Image by id
	public function removeGalleryImg($id) {

		$qry = $this->db->query("DELETE FROM " . DB_PREFIX . "ms_seller_gallery WHERE id='" . (int)$id. "'");

		return $qry;
	}
	/*************************************************/


	public function updateSellerVideos($data) {
		$seller_id = (int)$data['seller_id'];
		if($data['videoIDs']){
			$qry = $this->db->query("DELETE FROM " . DB_PREFIX . "ms_seller_videos WHERE seller_id='" . (int)$seller_id. "'");
			foreach ($data['videoIDs'] as $videoId){
				$this->db->query("INSERT INTO " . DB_PREFIX . "ms_seller_videos SET seller_id='" . (int)$seller_id . "', video_id  = '" . $this->db->escape($videoId) . "'");
			}
		}
	}


	public function getSellerVideos($seller_id)
	{
		$sql = "SELECT * 
	FROM `" . DB_PREFIX . "ms_seller_videos`
	WHERE seller_id = " . (int)$seller_id;
		$res = $this->db->query($sql);
		return $res->rows;
	}

    public function getPlanBySellerId($seller_id)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM ' . DB_PREFIX .  'ms_subscriptions  as p';
        $queryString[] = 'LEFT JOIN ' . DB_PREFIX .  'ms_seller as s';
        $queryString[] = 'ON p.plan_id=s.subscription_plan';
        $queryString[] = 'WHERE s.seller_id=' . $seller_id;
        $queryString[] = 'AND p.plan_status=1';

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows > 0) {
            return $data->rows;
        }

        return null;
    }

    function applySellerAffiliateCommission($planPrice)
    {
        $amount = 0;
        $customerLink = HTTP_ADMIN . "/sale/customer/update?customer_id=" . $this->customer->getId();
        if (\Extension::isInstalled('multiseller_advanced')) {
            $this->load->model('setting/setting');
            $this->load->model('affiliate/affiliate');
            $this->load->model('affiliate/transaction');

            $multiSellerAdvanced = $this->model_setting_setting->getSetting('multiseller_advanced');
            $affiliate_info = $this->model_affiliate_affiliate->getAffiliateBySellerCode($this->request->cookie['sellerTracking']);
            if ($affiliate_info && $affiliate_info['approved'] == 1 && $multiSellerAdvanced['multiseller_advanced']['seller_affiliate']) {
                if ($this->config->get('msconf_enable_subscriptions_plans_system') && $this->config->get('msconf_enable_subscriptions_plans_system') == 1) {
                    if ($affiliate_info['seller_affiliate_type'] == "F") {
                        $amount = $affiliate_info['seller_affiliate_commission'];
                    } elseif ($affiliate_info['seller_affiliate_type'] == 'P') {
                        $amount = ($planPrice / 100 * $affiliate_info['seller_affiliate_commission']);
                    }
                    $this->model_affiliate_transaction->addTransaction($affiliate_info['affiliate_id'],$customerLink, $amount, 0, 1);
                } else {
                    $amount = $multiSellerAdvanced['multiseller_advanced']['affiliate_seller_commission'];
                    $this->model_affiliate_transaction->addTransaction($affiliate_info['affiliate_id'],$customerLink, $amount, 0, 1);
                }
            }

        }
    }
}
