<?php
class ModelAccountOrder extends Model {
    public function getOrder($order_id, $type = NULL) {
        $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

        if($queryMultiseller->num_rows) {
            if (empty($type)) {
                $order_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND order_status_id > '0'");
            } elseif($type == 'seller') {
                $order_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` AS o, `" . DB_PREFIX . "ms_order_product_data` AS m WHERE o.order_id = m.order_id AND o.order_id = " . (int)$order_id . " AND m.seller_id = " . (int)$this->customer->getId() . " AND o.order_status_id > '0'");
            }
        } else {
            $order_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND order_status_id > '0'");
        }
	
		if ($order_query->num_rows) {
			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['payment_country_id'] . "'");
			
			if ($country_query->num_rows) {
				$payment_iso_code_2 = $country_query->row['iso_code_2'];
				$payment_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$payment_iso_code_2 = '';
				$payment_iso_code_3 = '';				
			}
			
			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['payment_zone_id'] . "'");
			
			if ($zone_query->num_rows) {
				$payment_zone_code = $zone_query->row['code'];
			} else {
				$payment_zone_code = '';
			}
			
			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['shipping_country_id'] . "'");
			
			if ($country_query->num_rows) {
				$shipping_iso_code_2 = $country_query->row['iso_code_2'];
				$shipping_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$shipping_iso_code_2 = '';
				$shipping_iso_code_3 = '';				
			}
			
			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['shipping_zone_id'] . "'");
			
			if ($zone_query->num_rows) {
				$shipping_zone_code = $zone_query->row['code'];
			} else {
				$shipping_zone_code = '';
			}
			
			return array(
				'order_id'                => $order_query->row['order_id'],
				'invoice_no'              => $order_query->row['invoice_no'],
				'invoice_prefix'          => $order_query->row['invoice_prefix'],
				'store_id'                => $order_query->row['store_id'],
				'store_name'              => $order_query->row['store_name'],
				'store_url'               => $order_query->row['store_url'],				
				'customer_id'             => $order_query->row['customer_id'],
				'firstname'               => $order_query->row['firstname'],
				'lastname'                => $order_query->row['lastname'],
				'telephone'               => $order_query->row['telephone'],
				'fax'                     => $order_query->row['fax'],
				'email'                   => $order_query->row['email'],
				'payment_firstname'       => $order_query->row['payment_firstname'],
				'payment_lastname'        => $order_query->row['payment_lastname'],				
				'payment_company'         => $order_query->row['payment_company'],
				'payment_address_1'       => $order_query->row['payment_address_1'],
				'payment_address_2'       => $order_query->row['payment_address_2'],
				'payment_telephone'       => $order_query->row['payment_telephone'],
				'payment_postcode'        => $order_query->row['payment_postcode'],
				'payment_city'            => $order_query->row['payment_city'],
				'payment_zone_id'         => $order_query->row['payment_zone_id'],
				'payment_zone'            => $order_query->row['payment_zone'],
				'payment_zone_code'       => $payment_zone_code,
				'payment_country_id'      => $order_query->row['payment_country_id'],
				'payment_country'         => $order_query->row['payment_country'],	
				'payment_iso_code_2'      => $payment_iso_code_2,
				'payment_iso_code_3'      => $payment_iso_code_3,
				'payment_address_format'  => $order_query->row['payment_address_format'],
				'payment_method'          => $order_query->row['payment_method'],
				'shipping_firstname'      => $order_query->row['shipping_firstname'],
				'shipping_lastname'       => $order_query->row['shipping_lastname'],				
				'shipping_company'        => $order_query->row['shipping_company'],
				'shipping_address_1'      => $order_query->row['shipping_address_1'],
				'shipping_address_2'      => $order_query->row['shipping_address_2'],
				'shipping_postcode'       => $order_query->row['shipping_postcode'],
				'shipping_city'           => $order_query->row['shipping_city'],
				'shipping_zone_id'        => $order_query->row['shipping_zone_id'],
				'shipping_zone'           => $order_query->row['shipping_zone'],
				'shipping_zone_code'      => $shipping_zone_code,
				'shipping_country_id'     => $order_query->row['shipping_country_id'],
				'shipping_country'        => $order_query->row['shipping_country'],	
				'shipping_iso_code_2'     => $shipping_iso_code_2,
				'shipping_iso_code_3'     => $shipping_iso_code_3,
				'shipping_address_format' => $order_query->row['shipping_address_format'],
				'shipping_method'         => $order_query->row['shipping_method'],
				'shipping_tracking_url'   => $order_query->row['shipping_tracking_url'],
				'comment'                 => $order_query->row['comment'],
				'total'                   => $order_query->row['total'],
				'order_status_id'         => $order_query->row['order_status_id'],
				'language_id'             => $order_query->row['language_id'],
				'currency_id'             => $order_query->row['currency_id'],
				'currency_code'           => $order_query->row['currency_code'],
				'currency_value'          => $order_query->row['currency_value'],
				'date_modified'           => $order_query->row['date_modified'],
				'date_added'              => $order_query->row['date_added'],
				'ip'                      => $order_query->row['ip']
			);
		} else {
			return false;	
		}
	}

    public function getOrderData($order_id) {
        $order_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "' AND customer_id = '" . (int)$this->customer->getId() . "'");

        if ($order_query->num_rows) {
            $country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['payment_country_id'] . "'");

            if ($country_query->num_rows) {
                $payment_iso_code_2 = $country_query->row['iso_code_2'];
                $payment_iso_code_3 = $country_query->row['iso_code_3'];
            } else {
                $payment_iso_code_2 = '';
                $payment_iso_code_3 = '';
            }

            $zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['payment_zone_id'] . "'");

            if ($zone_query->num_rows) {
                $payment_zone_code = $zone_query->row['code'];
            } else {
                $payment_zone_code = '';
            }

            $country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['shipping_country_id'] . "'");

            if ($country_query->num_rows) {
                $shipping_iso_code_2 = $country_query->row['iso_code_2'];
                $shipping_iso_code_3 = $country_query->row['iso_code_3'];
            } else {
                $shipping_iso_code_2 = '';
                $shipping_iso_code_3 = '';
            }

            $zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['shipping_zone_id'] . "'");

            if ($zone_query->num_rows) {
                $shipping_zone_code = $zone_query->row['code'];
            } else {
                $shipping_zone_code = '';
            }

            return array(
                'order_id'                => $order_query->row['order_id'],
                'invoice_no'              => $order_query->row['invoice_no'],
                'invoice_prefix'          => $order_query->row['invoice_prefix'],
                'store_id'                => $order_query->row['store_id'],
                'store_name'              => $order_query->row['store_name'],
                'store_url'               => $order_query->row['store_url'],
                'customer_id'             => $order_query->row['customer_id'],
                'firstname'               => $order_query->row['firstname'],
                'lastname'                => $order_query->row['lastname'],
                'telephone'               => $order_query->row['telephone'],
                'fax'                     => $order_query->row['fax'],
                'email'                   => $order_query->row['email'],
                'payment_firstname'       => $order_query->row['payment_firstname'],
                'payment_lastname'        => $order_query->row['payment_lastname'],
                'payment_company'         => $order_query->row['payment_company'],
                'payment_address_1'       => $order_query->row['payment_address_1'],
                'payment_address_2'       => $order_query->row['payment_address_2'],
                'payment_postcode'        => $order_query->row['payment_postcode'],
                'payment_city'            => $order_query->row['payment_city'],
                'payment_zone_id'         => $order_query->row['payment_zone_id'],
                'payment_zone'            => $order_query->row['payment_zone'],
                'payment_zone_code'       => $payment_zone_code,
                'payment_country_id'      => $order_query->row['payment_country_id'],
                'payment_country'         => $order_query->row['payment_country'],
                'payment_iso_code_2'      => $payment_iso_code_2,
                'payment_iso_code_3'      => $payment_iso_code_3,
                'payment_address_format'  => $order_query->row['payment_address_format'],
                'payment_method'          => $order_query->row['payment_method'],
                'shipping_firstname'      => $order_query->row['shipping_firstname'],
                'shipping_lastname'       => $order_query->row['shipping_lastname'],
                'shipping_company'        => $order_query->row['shipping_company'],
                'shipping_address_1'      => $order_query->row['shipping_address_1'],
                'shipping_address_2'      => $order_query->row['shipping_address_2'],
                'shipping_postcode'       => $order_query->row['shipping_postcode'],
                'shipping_city'           => $order_query->row['shipping_city'],
                'shipping_zone_id'        => $order_query->row['shipping_zone_id'],
                'shipping_zone'           => $order_query->row['shipping_zone'],
                'shipping_zone_code'      => $shipping_zone_code,
                'shipping_country_id'     => $order_query->row['shipping_country_id'],
                'shipping_country'        => $order_query->row['shipping_country'],
                'shipping_iso_code_2'     => $shipping_iso_code_2,
                'shipping_iso_code_3'     => $shipping_iso_code_3,
                'shipping_address_format' => $order_query->row['shipping_address_format'],
                'shipping_method'         => $order_query->row['shipping_method'],
                'comment'                 => $order_query->row['comment'],
                'total'                   => $order_query->row['total'],
                'order_status_id'         => $order_query->row['order_status_id'],
                'language_id'             => $order_query->row['language_id'],
                'currency_id'             => $order_query->row['currency_id'],
                'currency_code'           => $order_query->row['currency_code'],
                'currency_value'          => $order_query->row['currency_value'],
                'date_modified'           => $order_query->row['date_modified'],
                'date_added'              => $order_query->row['date_added'],
                'ip'                      => $order_query->row['ip'],
                'confirm_code'            => $order_query->row['smsverifcode'],
                'phoneverified'           => $order_query->row['phoneverified'],
                'smsveriftrials'          => $order_query->row['smsveriftrials'],
                'whatsveriftrials'        => $order_query->row['whatsveriftrials']
            
			);
        } else {
            return false;
        }
    }
	 
	public function getOrders($start = 0, $limit = 20) {
        $showArchived = $this->config->get('config_show_archived_orders_history');
		if ($start < 0) {
			$start = 0;
		}
		
		if ($limit < 1) {
			$limit = 1;
		}

        $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

        if($queryMultiseller->num_rows || Extension::isInstalled('customer_order_flow') ) {
            $query = $this->db->query("SELECT o.order_id, o.firstname, o.lastname, os.name as status, os.order_status_id, o.date_added, o.total, o.currency_code, o.currency_value FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "order_status os ON (o.order_status_id = os.order_status_id) WHERE o.customer_id = '" . (int)$this->customer->getId() . "' AND o.order_status_id > '0' " . ($showArchived ? "" : "AND o.archived = 0") . " AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.order_id DESC LIMIT " . (int)$start . "," . (int)$limit);
        }
        else {
            $query = $this->db->query("SELECT o.order_id, o.firstname, o.lastname, os.name as status, o.date_added, o.total, o.currency_code, o.currency_value FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "order_status os ON (o.order_status_id = os.order_status_id) WHERE o.customer_id = '" . (int)$this->customer->getId() . "' AND o.order_status_id > '0' " . ($showArchived ? "" : "AND o.archived = 0") . " AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.order_id DESC LIMIT " . (int)$start . "," . (int)$limit);
        }
	
		return $query->rows;
	}
	
	public function getOrderProducts($order_id, $product_tbl_cols = false) {
		//$product_tbl_cols: array of 'product table' column's names need to retrieve with 'order_product' date. i.e: ['weight', 'height']
		if($product_tbl_cols){
			$columns = [];
			foreach ($product_tbl_cols as $col) {
				$columns[] = 'p.'.$col;
			}
			if(count($columns)){
				$product_columns = implode($columns, ', ');

				$query = $this->db->query("SELECT op.*,p.image, $product_columns FROM " . DB_PREFIX . "order_product op LEFT JOIN " . DB_PREFIX . "product p ON op.product_id = p.product_id WHERE order_id = '" . (int)$order_id . "'");
				return $query->rows;
			}
		}///////////////////////////////
		
		$query = $this->db->query("select op.*,p.image from " . DB_PREFIX . "order_product op left join " . DB_PREFIX . "product p on op.product_id=p.product_id WHERE op.order_id = '" . (int)$order_id . "'");
		
		return $query->rows;
	}

    public function getOrderOptions($order_id, $order_product_id=null) {
        $sql = "SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "'";

        if($order_product_id){
            $order_product_id_arr = [];
            if (!is_array($order_product_id))
                $order_product_id_arr[] = (int)$order_product_id ;
            else
                $order_product_id_arr = $order_product_id;


            $order_product_id_arr = implode("','",$order_product_id_arr);


            $sql .= "AND order_product_id in ('$order_product_id_arr') ";
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }
	
	public function getOrderVouchers($order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_voucher` WHERE order_id = '" . (int)$order_id . "'");
		
		return $query->rows;
	}
	
	public function getOrderTotals($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order");
	
		return $query->rows;
	}	

	public function getOrderHistories($order_id) {
		$query = $this->db->query("SELECT date_added, os.order_status_id as order_status_id, os.name AS status, oh.comment, oh.notify FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "order_status os ON oh.order_status_id = os.order_status_id WHERE oh.order_id = '" . (int)$order_id . "' AND oh.notify = '1' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY oh.date_added");
	
		return $query->rows;
	}	

	public function getOrderDownloads($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_download WHERE order_id = '" . (int)$order_id . "' ORDER BY name");
	
		return $query->rows; 
	}	

	public function getTotalOrders() {
        $showArchived = $this->config->get('config_show_archived_orders_history');
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE customer_id = '" . (int)$this->customer->getId() . "' AND order_status_id > '0' " . ($showArchived ? "" : "AND archived = 0") );
		
		return $query->row['total'];
	}
		
	public function getTotalOrderProductsByOrderId($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
		
		return $query->row['total'];
	}
	
	public function getTotalOrderVouchersByOrderId($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order_voucher` WHERE order_id = '" . (int)$order_id . "'");
		
		return $query->row['total'];
	}

	public function getFdsOrderid($track_num) {
      	$query = $this->db->query("SELECT order_id FROM " . DB_PREFIX . "shipments_details WHERE details = '" . $track_num . "' AND shipment_status = '1'");
		
		return $query->row['order_id'];
	}	
	
	public function orderIsReturned($order_id) {
		$query = $this->db->query("SELECT COUNT(order_id) as returned FROM `" . DB_PREFIX . "return` WHERE `order_id` = " . (int) $order_id);
		return $query->row['returned'] > 0;
	}

    public function getCustomerStorableOrderProducts(int $customerId)
    {
        $query = [];
        $fields = [
            'o.order_id',
            'o.customer_id',
            'o.order_attributes',
            'o.date_added',
            'op.name',
            'op.model',
            'op.quantity',
            'op.price',
            'op.total',
            'p.image',
            'p.storable',
        ];
        $query[] = 'SELECT %s FROM `order` o INNER JOIN order_product op ON o.order_id = op.order_id';
        $query[] = 'INNER JOIN product p ON op.product_id = p.product_id';
        $query[] = 'WHERE o.order_attributes->"$.store_in_store" = "1"';
        $query[] = 'AND o.customer_id = %d';
        $query[] = 'AND p.storable = 1';

        $data = $this->db->query(vsprintf(implode(' ', $query), [
            implode(',', $fields),
            $customerId
        ]));

        if ($data->num_rows > 0) {
            return $data->rows;
        }

        return false;
    }

    public function getOrderProductsForInvoice($orderId, $languageId)
    {
        $sortBy = $this->config->get('config_invoice_products_sort_order');
        $sortTy = $this->config->get('config_invoice_products_sort_type') ?? 'ASC';
        $this->load->model('module/rental_products/settings');

        if($sortBy === 'category'){
            $sortLv = $this->config->get('config_invoice_products_sort_ctlevel') ?? 0;
            /*$categories = [];
            $sorted_categories = [];*/
            $products=[];
            //Select order's products ordered by category name
            $query = '
            SELECT
                DISTINCT op.product_id,
                op.model,
                op.quantity,
                op.price,
                op.total,
                op.order_product_id,
                op.tax,
                IFNULL(ptc.category_id, 0) as category_id,
                cp.level,
                cd.name as category_name,
                p.image,
                p.status as product_status,
                p.sku,
                pd.name,
                p.barcode
            FROM order_product op
            left join product_to_category ptc on ptc.product_id=op.product_id
            left join category_path cp on cp.path_id=ptc.category_id
            left join category_description cd on ptc.category_id=cd.category_id
            left join product p on op.product_id=p.product_id
            left join product_description pd on op.product_id=pd.product_id

            WHERE order_id = '.$orderId.'
            AND cd.language_id=' . (int)$languageId . '
            AND pd.language_id=' . (int)$languageId . '
            ORDER BY category_name '.$sortTy;

            $tempIds = [0];
            $allData = $this->db->query($query)->rows;
            //First pick products with category level of selected level $sortLv
            foreach ($allData as $idx => $product) {
                if ($product['level'] == $sortLv && !in_array($product['order_product_id'], $tempIds)) {
                    $products[$product['order_product_id']] = $product;
                    $tempIds[] = $product['order_product_id'];
                    unset($allData[$idx]);
                }
            }
            //Check if there are products not picked in last step

            foreach ($allData as $idx => $product) {
                if (!in_array($product['order_product_id'], $tempIds)) {
                    $products[$product['order_product_id']] = $product;
                    $tempIds[] = $product['order_product_id'];
                }
            }
            //get categories
            /*$query = '
            SELECT
                op.product_id,
                IFNULL(ptc.category_id, 0)
            FROM order_product op
            left join product_to_category ptc on ptc.product_id=op.product_id
            WHERE order_id = '.$orderId;*/

            //Getting products categories ids based on child level
            /*$query = '
            SELECT
               DISTINCT ptc.category_id
            FROM order_product op
            left join product_to_category ptc on ptc.product_id=op.product_id
            left join category_path cp on cp.path_id=ptc.category_id
            WHERE cp.level = 0 AND order_id = '.$orderId;*/


            /*foreach($this->db->query($query)->rows as $product){
					//get product categories
					$sql = '
                    select
                        c.category_id,
                        cd.name
                    from category c
                    left join category_description cd on c.category_id=cd.category_id
                    where c.category_id=' . (int)$product['category_id'] . '
                    and cd.language_id=' . (int)$languageId . '
                ';

					if (!isset($categories[$product['category_id']])) {
						$categories[$product['category_id']] = $this->db->query($sql)->row;
					} else {
						$categories[0] = '';
					}
					$categories[$product['category_id']]['products'][] = $product['product_id'];
				}*/

            /*foreach ($categories as $cat) {
                $sorted_categories[$cat['category_id']] = $cat['name'];
            }*/

            //asort($sorted_categories);

            /*foreach ($sorted_categories as $key => $category) {
                //get category products by ids
                $query = [];

                $fields = 'order_product.*, product.image, product.status as product_status, product.sku, product_description.name, product.barcode';

                $query[] = 'SELECT ' . $fields . ' FROM ' . DB_PREFIX . 'order_product';
                $query[] = 'JOIN product ON order_product.product_id = product.product_id';
                $query[] = 'JOIN product_description ON order_product.product_id = product_description.product_id';
                $query[] = 'WHERE product.product_id in(' . implode(',', $categories[$key]['products']) . ')';
                $query[] = 'AND order_id = "' . (int)$orderId . '"';
                $query[] = 'AND language_id = "' . (int)$languageId . '"';

                $data = $this->db->query(implode(' ', $query));

                foreach ($data->rows as $product) {
                    if (!isset($products[$product['product_id']])) {
                        $products[$product['product_id']] = $product;
                    }
                }
            }*/

            return $products;
        }

        $query = [];

        $fields = 'order_product.*, product.image, product.status as product_status, product.sku,product_description.name,product.barcode';

        // get rental fields if rental app is installed
        if($this->model_module_rental_products_settings->isActive()){
            $fields .= ',order_product_rental.from_date,order_product_rental.to_date,order_product_rental.diff';
        }

        $query[] = 'SELECT ' . $fields . ' FROM ' . DB_PREFIX . 'order_product';
        $query[] = 'JOIN product ON order_product.product_id = product.product_id';
        $query[] = 'JOIN product_description ON order_product.product_id = product_description.product_id';

        // join order_product_rental table is rental app is installed
        if($this->model_module_rental_products_settings->isActive()){
            $query[] = 'LEFT JOIN order_product_rental ON order_product.order_product_id = order_product_rental.order_product_id';
        }

        $query[] = 'WHERE order_product.order_id = "' . (int)$orderId . '"';
        $query[] = 'AND language_id = "' . (int)$languageId . '"';

        if ($sortBy == 'model') {
            $query[] = 'ORDER BY product.model '.$sortTy;
        } else if ($sortBy == 'sku') {
            $query[] = 'ORDER BY product.sku '.$sortTy ;
        }

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }

    public function updateOrderStatus($order_id , $order_status_id){
        return $this->db->query("UPDATE `" . DB_PREFIX . "order` SET `order_status_id` = " . $this->db->escape($order_status_id) . " WHERE `order_id` = " . $this->db->escape($order_id));
    }
}
