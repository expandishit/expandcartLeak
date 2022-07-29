<?php
use Facebook\Facebook;
use Facebook\Exceptions\FacebookSDKException;
class ModelCatalogProduct extends Model {
	public function updateViewed($product_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "product SET viewed = (viewed + 1) WHERE product_id = '" . (int)$product_id . "'");
	}

    /**
    *   Check if the product is by a seller from the multi sellers.
    *
    *   @author Michael
    *   @param int $product_id
    *   @return mixed
    */
    private function isProductByAMultiSeller( $product_id )
    {
        // $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

        if(\Extension::isInstalled('multiseller')) {
            $sql = "SELECT * FROM " . DB_PREFIX . "ms_product 
                WHERE `product_id` = '" . $this->db->escape($product_id) . "' LIMIT 1;";

            $query = $this->db->query($sql);
            if (!empty($query->row)) {
                return $query->row;
            }
        }
        
        return false;
    }

    /**
    *   Check the products state in the MultiSeller table
    *
    *   @author Michael
    *   @param int $seller_id
    *   @return mixed
    */
    private function getMSProductsState( $seller_id )
    {
        // $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

        if(\Extension::isInstalled('multiseller')) {
            $sql = "SELECT * FROM " . DB_PREFIX . "ms_seller 
                WHERE `seller_id` = '" . $this->db->escape($seller_id) . "' LIMIT 1;";
            $query = $this->db->query($sql);
            if (!empty($query->row)) {
                return $query->row['products_state'];
            }
        }

        return false;
    }


    public function getProduct($product_id) {
        if ($this->customer->isLogged()) {
            $customer_group_id = $this->customer->getCustomerGroupId();
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }   

        // Multi Seller Product checking.
        /*$seller_information = $this->isProductByAMultiSeller($product_id);
        if ( $seller_information )
        {
            // Check if the selling status exist
            $seller_products_state = $this->getMSProductsState($seller_information['seller_id']);
            // if the state is 0 then return false.
            if ( $seller_products_state == 0 )
            {
                return false;
            }
        }*/

        $dedicatedPriceWhereClause = '';

        if ($this->dedicatedDomains->isActive()) {
            $productDomain = $this->load->model('module/dedicated_domains/product_domain', ['return' => true]);

            $domainId = $this->dedicatedDomains->getDomainId();

            if ($productDomains = $productDomain->getProductDomainsByProductId($product_id)) {

                $productDomainsId = $productDomain->getProductDomainIds($productDomains);

                if (
                    isset(array_flip($productDomainsId)[$domainId]) == false
                ) {
                    return false;
                }
            }

            if ($domainId) {
                $dedicatedPriceWhereClause = 'AND dedicated_domains="' . $domainId . '"';
            } else {
                $dedicatedPriceWhereClause  = 'AND (dedicated_domains IS NULL OR dedicated_domains=""';
                $dedicatedPriceWhereClause .= 'OR dedicated_domains = 0)';
            }

        }
        $app_config_timezone = $this->config->get('config_timezone')?:'UTC';
        $now = (new DateTime('NOW', new DateTimeZone($app_config_timezone)))->format('Y-m-d');
        
		$queryString = [];
        $queryString[] = "SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, pd.description as description,
        m.image AS manufacturerimg,
        (SELECT price FROM " . DB_PREFIX . "product_discount pd2
            WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$customer_group_id . "' AND
            pd2.quantity = '1' AND ((IFNULL(pd2.date_start, '0000-00-00') = '0000-00-00' OR pd2.date_start < NOW()) AND
            (IFNULL(pd2.date_end, '0000-00-00') = '0000-00-00' OR pd2.date_end > NOW()))
            " . $dedicatedPriceWhereClause . "
            ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1)
        AS discount,
        (SELECT price FROM " . DB_PREFIX . "product_special ps
        WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND
            ((IFNULL(ps.date_start, '0000-00-00') = '0000-00-00' OR ps.date_start <= '".$now."') AND
            (IFNULL(ps.date_end, '0000-00-00') = '0000-00-00' OR ps.date_end >= '".$now."'))
            " . $dedicatedPriceWhereClause . "
            ORDER BY ps.priority ASC, ps.price ASC LIMIT 1)
        AS special,
        (SELECT discount_type FROM " . DB_PREFIX . "product_special ps
        WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND
            ((IFNULL(ps.date_start, '0000-00-00') = '0000-00-00' OR ps.date_start <= '".$now."') AND
            (IFNULL(ps.date_end, '0000-00-00') = '0000-00-00' OR ps.date_end >= '".$now."'))
            " . $dedicatedPriceWhereClause . "
            ORDER BY ps.priority ASC, ps.price ASC LIMIT 1)
        AS special_discount_type,
        (SELECT discount_value FROM " . DB_PREFIX . "product_special ps
        WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND
            ((IFNULL(ps.date_start, '0000-00-00') = '0000-00-00' OR ps.date_start <= '".$now."') AND
            (IFNULL(ps.date_end, '0000-00-00') = '0000-00-00' OR ps.date_end >= '".$now."'))
            " . $dedicatedPriceWhereClause . "
            ORDER BY ps.priority ASC, ps.price ASC LIMIT 1)
        AS special_discount_value,
        (SELECT date_end FROM " . DB_PREFIX . "product_special ps
            WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND
            ((IFNULL(ps.date_start, '0000-00-00') = '0000-00-00' OR ps.date_start < '".$now."') AND
            (IFNULL(ps.date_end, '0000-00-00') = '0000-00-00' OR ps.date_end > '".$now."'))
            " . $dedicatedPriceWhereClause . "
            ORDER BY ps.priority ASC, ps.price ASC LIMIT 1)
        AS special_enddate,
        (SELECT SUM(points) FROM " . DB_PREFIX . "product_reward pr
            WHERE pr.product_id = p.product_id AND customer_group_id = '" . (int)$customer_group_id . "')
        AS reward,
        (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss
            WHERE ss.stock_status_id = p.stock_status_id AND
            ss.language_id = '" . (int)$this->config->get('config_language_id') . "')
        AS stock_status,
        (SELECT ss.current_color FROM " . DB_PREFIX . "stock_status ss
            WHERE ss.stock_status_id = p.stock_status_id AND
            ss.language_id = '" . (int)$this->config->get('config_language_id') . "')
        AS stock_status_color,
        (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd
            WHERE p.weight_class_id = wcd.weight_class_id AND
            wcd.language_id = '" . (int)$this->config->get('config_language_id') . "')
        AS weight_class,
        (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd
            WHERE p.length_class_id = lcd.length_class_id AND
            lcd.language_id = '" . (int)$this->config->get('config_language_id') . "')
        AS length_class,
        (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1
            WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id)
        AS rating,";

        // $queryString[] = "(SELECT CONCAT('[', GROUP_CONCAT('{\"sort_order\":\"', sort_order, '\", \"image\":\"',image,'\"}'), ']') as images from product_image where p.product_id = product_image.product_id) as product_images,";
        $queryString[] = "(SELECT CONCAT('[', GROUP_CONCAT(JSON_OBJECT('sort_order', sort_order, 'image', image)), ']') as images from product_image where p.product_id = product_image.product_id) as product_images,";

        $queryString[] = "(SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2
            WHERE r2.product_id = p.product_id AND r2.status = '1' GROUP BY r2.product_id)
        AS reviews, p.sort_order FROM " . DB_PREFIX . "product p
        LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
        LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)
        LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id)";
        if (\Extension::isInstalled('multiseller')) {
            $queryString[] = ' LEFT JOIN ms_product msp ON p.product_id = msp.product_id';
            $queryString[] = ' LEFT JOIN ms_seller mss ON msp.seller_id = mss.seller_id';
        }
        $queryString[] = "WHERE p.product_id = '" . (int)$product_id . "' AND
        pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
        AND p.status = '1' AND p.date_available <= NOW()
        AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
        if (\Extension::isInstalled('multiseller')) {
            $queryString[] = ' AND (msp.product_id IS NULL OR mss.seller_id IS NULL OR mss.products_state = 1)';
        }

        $this->db->query('SET SESSION group_concat_max_len = 100000');
        $query = $this->db->query(implode(' ', $queryString));
        if ($query->num_rows) {

            if (empty($query->row['product_id'])) {
                $query->row['product_id'] = $product_id;
            }

            $productRating = round($query->row['rating']);

            if(!$productRating)
                $productRating = $this->getAvgProductRating($product_id);

            $query->row['rating'] = $productRating;
            $query->row['reviews'] = $query->row['reviews'] ? $query->row['reviews'] : 0;

            $this->load->model('module/dedicated_domains/domain_prices');

            if ($this->model_module_dedicated_domains_domain_prices->isActive()) {

                $dedicatedDomainPricesModel = $this->model_module_dedicated_domains_domain_prices;

                $domainData = $dedicatedDomainPricesModel->getDomain();

                if ($domainData) {

                    $dedicatedDiscount = $dedicatedDomainPricesModel->getProductDedicatedDiscount(
                        $product_id,
                        $customer_group_id,
                        $domainData
                    );

                    if ($dedicatedDiscount) {
                        $query->row['discount'] = $dedicatedDiscount;
                    } else {
                        $query->row['discount'] = null;
                    }

                    $dedicatedPrice = $dedicatedDomainPricesModel->getProductDedicatedPrices(
                        $product_id,
                        $domainData
                    );

                    if ($dedicatedPrice) {

                        if ($dedicatedDiscount) {
                            $query->row['price'] = $dedicatedDiscount;
                        } else {
                            $query->row['price'] = $dedicatedPrice;
                        }
                    }

                    $dedicatedSpecial = $dedicatedDomainPricesModel->getProductDedicatedSpecial(
                        $product_id,
                        $customer_group_id,
                        $domainData
                    );

                    if ($dedicatedSpecial) {
                        $query->row['special'] = $dedicatedSpecial;
                    } else {
                        $query->row['special'] = null;
                    }

                }
            }

            $query->row['price'] = ($query->row['discount'] ? $query->row['discount'] : $query->row['price']);
            $query->row['price_meter_data'] = json_decode($query->row['price_meter_data'], true);
            return $query->row;
        } else {
            return false;
        }
	}

    /**
     *   Get product specific column/s
     *
     *   @param int $product_id
     *   @param array $columns
     *   @return mixed
     */
    public function getProductCols($product_id, $columns) {

        $selections = implode($columns, ',');

        $query = "SELECT $selections FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "' limit 1";
        $query = $this->db->query($query);

        if ($query->num_rows)
            return $query->row;

         return false;
    }

	public function getProducts($data = array()) { 
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}
        $app_config_timezone = $this->config->get('config_timezone')?:'UTC';
        $now = (new DateTime('NOW', new DateTimeZone($app_config_timezone)))->format('Y-m-d');
		$sql = " SELECT 
        p.product_id, p.date_available,p.date_added,p.quantity,";

        if (\Extension::isinstalled('product_sort') && $this->config->get('product_sort_app_status') == '1') {
            $sql .= "p.manual_sort,";
            if ($data['display_page'] == 'category') {
                $sql .= "p2cs.manual_sort as category_manual_sort,p2cs.product_id p2cs_product_id,p2cs.category_id as p2cs_category_id,";
            }
        }

		if($this->config->get('products_notes')['general_use']){
           $sql .= "p.general_use,";
        }

        $sql .= "(SELECT AVG(rating) AS total 
        FROM " . DB_PREFIX . "review r1 
        WHERE r1.product_id = p.product_id 
        AND r1.status = '1' GROUP BY r1.product_id) AS rating, 

        (SELECT price 
        FROM " . DB_PREFIX . "product_discount pd2 
        WHERE pd2.product_id = p.product_id 
        AND pd2.customer_group_id = '" . (int)$customer_group_id . "' 
        AND pd2.quantity = '1' 
        AND ((IFNULL(pd2.date_start, '0000-00-00') = '0000-00-00' OR pd2.date_start < NOW()) 
        AND (IFNULL(pd2.date_end, '0000-00-00') = '0000-00-00' OR pd2.date_end > NOW())) 
        ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, 

        (SELECT ss.name 
        FROM " . DB_PREFIX . "stock_status ss 
        WHERE ss.stock_status_id = p.stock_status_id 
        AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status, 

        (SELECT price 
        FROM " . DB_PREFIX . "product_special ps 
        WHERE ps.product_id = p.product_id 
        AND ps.customer_group_id = '" . (int)$customer_group_id . "' 
        AND ((IFNULL(ps.date_start, '0000-00-00') = '0000-00-00' OR ps.date_start <= '".$now."') 
        AND (IFNULL(ps.date_end, '0000-00-00') = '0000-00-00' OR ps.date_end >= '".$now."')) 
        ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special";
      
		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)";
			} else {
				$sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
			}

			if (!empty($data['filter_filter'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p2c.product_id = pf.product_id) LEFT JOIN " . DB_PREFIX . "product p ON (pf.product_id = p.product_id)";
			} else {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
			}

            if (\Extension::isinstalled('product_sort') && $this->config->get('product_sort_app_status') == '1') {
               $sql .= " LEFT JOIN " . DB_PREFIX . "product_to_categories_sorting p2cs ON (p2cs.product_id = p.product_id) AND p2cs.category_id = '" . (int)$data['filter_category_id'] . "'";
            }
           

		}else if($data['pc_target'] && $data['pc_target'] == 'pc_app'){
            $sql .= " FROM " . DB_PREFIX . "pc_product_brand_mapping pbm  ";
            $sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p.product_id = pbm.product_id)";
        } else {
			$sql .= " FROM " . DB_PREFIX . "product p";
		}

        if (isset($data['city_id']) && $data['city_id'] > 0) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "ms_product as md on md.product_id=p.product_id LEFT JOIN " . DB_PREFIX . "ms_seller as ms on ms.seller_id=md.seller_id";
        }
        if (isset($data['path']) && $data['path'] =='deals') {
            $sql .= ' LEFT JOIN '.
            '((SELECT product_id,price,date_end FROM product_discount WHERE date_end >= NOW()) UNION (SELECT product_id,price,date_end FROM product_special WHERE date_end >= NOW()))'
            .' ps ON ps.product_id = p.product_id ';
        }

        if (\Extension::isInstalled('multiseller')) {
            $sql .= ' LEFT JOIN ms_product msp ON p.product_id = msp.product_id';
            $sql .= ' LEFT JOIN ms_seller mss ON msp.seller_id = mss.seller_id';
        }

        
        if(($data['trips']==1)
		&&(\Extension::isInstalled('multiseller'))&&(\Extension::isInstalled('trips') &&$this->config->get('trips')['status']==1))   {
            $sql .= " LEFT JOIN " . DB_PREFIX . "trips_product trips_pro ON (trips_pro.product_id = p.product_id)";
			if(isset($data['filterbyArea'])){
			$sql .= " LEFT JOIN " . DB_PREFIX . "geo_area_locale geoarea ON (trips_pro.area_id = geoarea.area_id)";
			}
        }
        if (!empty($data['categories_ids']) || isset($data['filterText']) || isset($data['popular'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category pro_cat ON (pro_cat.product_id = p.product_id)";
            if (isset($data['filterText'])) {
                $sql .= " LEFT JOIN " . DB_PREFIX . "category_description cat ON (pro_cat.category_id = cat.category_id)";
            }
        }

        if(isset($data['ignore_lang']) && $data['ignore_lang'] === true){
            $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
        }
        else{
            $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
        }

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
			} else {
                // This is for combined categories with comma in teditor
                if(count(explode(",",$data['filter_category_id'])) > 1){
                    $sql .= " AND p2c.category_id in (". $data['filter_category_id'] .")" ;
                }
                else{
                    $sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
                }
			}
        

			if (!empty($data['filter_filter'])) {
				$implode = array();

				$filters = explode(',', $data['filter_filter']);

				foreach ($filters as $filter_id) {
					$implode[] = (int)$filter_id;
				}

				$sql .= " AND pf.filter_id IN (" . implode(',', $implode) . ")";
			}
		}
            
        if (!empty($data['categories_ids'])) {
            $categories_ids = implode(" , ", $data['categories_ids']);
            $sql .= " AND pro_cat.category_id IN ({$categories_ids})";
        }

		if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";

			if (!empty($data['filter_name'])) {
				$implode = array();

				if (\Extension::isInstalled('fast_finder') && $this->config->get('fast_finder')['separated_word_search']) {
                    $implode[] = "( TRIM(pd.name) LIKE '%" . $this->db->escape($data['filter_name']) . "%' 
                                OR TRIM(pd.name) LIKE '".$this->db->escape($data['filter_name'])."%' 
                                OR TRIM(pd.name) LIKE '%".$this->db->escape($data['filter_name'])."')";
                } else {
                    $words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['filter_name'])));

                    foreach ($words as $word) {
                        $implode[] = "(pd.name LIKE '%" . $this->db->escape($word) . "%' OR REPLACE(pd.name, '''', '') LIKE '%".$this->db->escape($word)."%')";
                    }
                }

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

				if (!empty($data['filter_description'])) {
					$sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}
			}

			if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}

			if (!empty($data['filter_tag'])) {
				$sql .= "pd.tag LIKE '%" . $this->db->escape($data['filter_tag']) . "%'";
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			$sql .= ")";
		}
        if (isset($data['path']) && $data['path'] =='deals') {
 
            $sql .= " AND ps.date_end >= NOW() ";
        }
        if($data['pc_target'] && $data['pc_target'] == 'pc_app'){
            if(isset($data['pc_brand_id']) AND $data['pc_brand_id'] != 0 )
            {

                $sql .= " AND pbm.pc_brand_id = ".(int)$data['pc_brand_id'] ." ";
            }

            if(isset($data['pc_model_id']) AND $data['pc_model_id'] != 0 )
            {

                $sql .= " AND pbm.pc_model_id = ".(int)$data['pc_model_id'] ." ";
            }

            if(isset($data['pc_year_id']) AND $data['pc_year_id'] != 0 )
            {

                $sql .= " AND pbm.pc_year_id = ".(int)$data['pc_year_id'] ." ";
            }
        }


        if (isset($data['city_id']) && $data['city_id'] > 0) {
            $sql .= " AND ms.zone_id=" . $data['city_id'];
        }

		if (!empty($data['filter_manufacturer_id'])) {
			$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
		}

        if (\Extension::isInstalled('multiseller')) {
            $sql .= ' AND (msp.product_id IS NULL OR mss.seller_id IS NULL OR mss.products_state = 1)';
        }
        if( (\Extension::isInstalled('multiseller'))
		&&(\Extension::isInstalled('trips') &&$this->config->get('trips')['status']==1) && $data['trips']==1) 
		{    
            if($data['trips']==1)
            {
                $sql .= " AND trips_pro.from_date >= CURDATE()";
                if(isset($data['filterbyArea']))
                {
                $sql .= " AND ( ";
                $implode = [];
                $filter_name = $data['filterbyArea'];
                $words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $filter_name)));

                foreach ($words as $word) {
                    $word = $this->db->escape($word);
                    $implode[] = " geoarea.name LIKE '%{$word}%'";
                }

                if ($implode) {
                    $sql .= implode(" AND ", $implode);
                }

                $sql .= " ) ";
                }
                if(!empty($data['from_date']) &&!empty($data['to_date']))
                {
                    $sql .= " AND trips_pro.from_date >= '".$data['from_date']."' AND trips_pro.to_date <= '".$data['to_date']."' ";
                }
                elseif(!empty($data['from_date']))
                {
                    $sql .= " AND trips_pro.from_date >= '".$data['from_date']."' ";
                }
                elseif(!empty($data['to_date']))
                {
                    $sql .= " AND  trips_pro.to_date <= '".$data['to_date']."' ";
                }
                if (!empty($data['from_price'])) {
                    $sql .= " AND p.price >= '".$data['from_price']."' ";
                }
                if (!empty($data['to_price'])) {
                    $sql .= " AND p.price <= '".$data['to_price']."' ";
                }
               
             }
            elseif (empty($data['trips'])) {
                $sql .= " AND p.product_id NOT IN ( SELECT product_id FROM trips_product WHERE product_id IS NOT NULL)";
                }
        }
           

	    	$sql .= " GROUP BY p.product_id";

          if (!empty($data['popular'])) {
            $sql .= " HAVING ";

            $havingQuery = [];

            if ($data['popular']) {
                $havingQuery[] = "COUNT(pro_cat.category_id) > 1";
            }

            $sql .= implode(" AND ", $havingQuery);
         }

		$sort_data = array(
			'pd.name',
			'p.model',
			'p.quantity',
			'p.price',
			'rating',
			'p.sort_order',
            'p.date_added',
            'p.date_available',
            'stock_status',
            'product_manual_sort',
            'RAND()'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
				$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
			} elseif ($data['sort'] == 'p.price') {
				$sql .= " ORDER BY (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE p.price END)";
			} elseif ($data['sort'] == 'stock_status') {
                if($this->config->get('config_language_id') == 2)//Arabic
                    $sql .= " ORDER BY FIELD(`stock_status`, 'متوفر', 'غير متوفر', '2 - 3 أيام', 'حجز مسبق'), p.`quantity` ";
                else
                    $sql .= " ORDER BY FIELD(`stock_status`, 'In Stock', 'Out Of Stock', '2 - 3 Days', 'Pre-Order'), p.`quantity` ";
    
                $data['order'] = 'DESC';
             } else if ($data['sort'] == 'product_manual_sort' && \Extension::isinstalled('product_sort') && $this->config->get('product_sort_app_status') == '1') {
                if ($data['display_page'] == 'category') {
                    $sql .= " ORDER BY p2cs.manual_sort";
                    $data['order'] = 'ASC';
                } else {
                    $sql .= " ORDER BY p.manual_sort";
                }
             } else {
                $sql .= " ORDER BY " . $data['sort'];
			}
		} else {
            $sql .= " ORDER BY p.sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC, LCASE(pd.name) DESC";
		} else {
			$sql .= " ASC, LCASE(pd.name) ASC";

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

		$product_data = array();

        if( in_array( __FUNCTION__, array( 'getProducts', 'getTotalProducts', 'getProductSpecials', 'getTotalProductSpecials' ) ) ) {
            if( ! empty( $this->request->get['mfp'] ) || ( NULL != ( $mfSettings = $this->config->get('mega_filter_settings') ) && ! empty( $mfSettings['in_stock_default_selected'] ) ) ) {

                if( ! empty( $this->request->get['mfp'] ) || $this->config->get('mfp_is_activated') || ! empty( $mfSettings['in_stock_default_selected'] ) ) {
                    $this->load->model( 'module/mega_filter' );
                    if(\Extension::isInstalled('advanced_product_attributes')==true && $this->config->get('advanced_product_attribute_status')==1)
                    {
                        $advanced_sql = MegaFilterCore::newInstance( $this, $sql )->getSQL( __FUNCTION__ ,NULL,NULL, array(),1);
   
                    }
                    $sql = MegaFilterCore::newInstance( $this, $sql )->getSQL( __FUNCTION__ ,NULL,NULL, array(),0);
                }
            }
        }

        $query = $this->db->query($sql);
        $rows=$query->rows;
        if(isset($advanced_sql))
        {
            $advanced_query=$this->db->query($advanced_sql);

            //i'm merge using loop beacuse array_merge or + overwrite same key
            foreach($advanced_query->rows as $row)
            {
                $rows[]=$row;
            }
        }
        $returned_product_ids = array_column($rows, 'product_id');
        $products = $this->getProductsByIds($returned_product_ids);
		foreach ($products as $product) {
            // Multi Seller Product checking.
           /*$seller_information = $this->isProductByAMultiSeller($product['product_id']);
           if ( $seller_information )
           {
               // Check if the selling status exist
               $seller_products_state = $this->getMSProductsState($seller_information['seller_id']);
               // if the state is 0 then return false.
               if ( (string) $seller_products_state === "0" )
               {
                   continue;
               }
           }*/
			$product_data[$product['product_id']] = $product;
		}
		return $product_data;
	}

	public function getProductsByIdsForDedicatedDomains($ids)
    {
        if (is_array($ids) === false) {
            return false;
        }

        $products = [];
        foreach ($ids as $id) {
            if ($product = $this->getProduct($id)) {
                $products[$id] = $product;
            }
        }

        return $products;
    }

	public function getProductsByIds($Ids = array()) {
        $app_config_timezone = $this->config->get('config_timezone')?:'UTC';
        $now = (new DateTime('NOW', new DateTimeZone($app_config_timezone)))->format('Y-m-d');
        if(count($Ids) == 0 || !is_array($Ids)) {
            return false;
        }

        $this->load->model('module/dedicated_domains/domain_prices');
        $this->load->model('section/product', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');

        if ($this->model_module_dedicated_domains_domain_prices->isActive()) {
            return $this->getProductsByIdsForDedicatedDomains($Ids);
        }

        if ($this->customer->isLogged()) {
            $customer_group_id = $this->customer->getCustomerGroupId();
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }

        $product_ids = implode(",", $Ids);
        if(empty($product_ids)) return false;

        $query = $columns = [];
        $columns[] = '*';
        $columns[] = 'ss.name AS stock_status';
        $columns[] = 'ss.current_color AS current_color';
        $columns[] = 'TRIM(pd.name) AS name';
        $columns[] = 'p.image';
        $columns[] = 'm.name AS manufacturer';
        $columns[] = 'm.image AS manufacturerimg';
        $columns[] = '(SELECT price FROM product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = "' . (int)$customer_group_id . '" AND pd2.quantity = "1" AND ((IFNULL(pd2.date_start, "0000-00-00") = "0000-00-00" OR pd2.date_start < NOW()) AND (IFNULL(pd2.date_end, "0000-00-00") = "0000-00-00" OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount';
        $columns[] = '(SELECT price FROM product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = "' . (int)$customer_group_id . '" AND ((IFNULL(ps.date_start, "0000-00-00") = "0000-00-00" OR ps.date_start <= "'.$now.'") AND (IFNULL(ps.date_end, "0000-00-00") = "0000-00-00" OR ps.date_end >= "'.$now.'")) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special';
        $columns[] = '(SELECT date_end FROM product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = "' . (int)$customer_group_id . '" AND ((IFNULL(ps.date_start, "0000-00-00") = "0000-00-00" OR ps.date_start <= "'.$now.'") AND (IFNULL(ps.date_end, "0000-00-00") = "0000-00-00" OR ps.date_end >= "'.$now.'")) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special_enddate';
        $columns[] = '(SELECT points FROM product_reward pr WHERE pr.product_id = p.product_id AND customer_group_id = "' . (int)$customer_group_id . '" limit 1) AS reward';
        $columns[] = '(SELECT wcd.unit FROM weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = "' . (int)$this->config->get('config_language_id') . '") AS weight_class';
        $columns[] = '(SELECT lcd.unit FROM length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = "' . (int)$this->config->get('config_language_id') . '") AS length_class';
        $columns[] = '(SELECT AVG(rating) AS total FROM review r1 WHERE r1.product_id = p.product_id AND r1.status = "1" GROUP BY r1.product_id) AS rating';
        $columns[] = '(SELECT COUNT(*) AS total FROM review r2 WHERE r2.product_id = p.product_id AND r2.status = "1" GROUP BY r2.product_id) AS reviews';
        $columns[] = "(SELECT CONCAT('[', GROUP_CONCAT(JSON_OBJECT('sort_order', sort_order, 'image', image)), ']') as images from product_image where p.product_id = product_image.product_id) as product_images";
        $columns[] = 'p.sort_order';
        $query[] = 'SELECT DISTINCT %s FROM product p';
        $query[] = "LEFT JOIN stock_status ss ON (ss.stock_status_id = p.stock_status_id) AND ss.language_id = ". (int)$this->config->get('config_language_id');
        $query[] = 'LEFT JOIN product_description pd ON (p.product_id = pd.product_id)';
        $query[] = 'LEFT JOIN product_to_store p2s ON (p.product_id = p2s.product_id)';
        $query[] = 'LEFT JOIN manufacturer m ON (p.manufacturer_id = m.manufacturer_id)';
        $query[] = 'WHERE 1=1';
        $query[] = 'AND p.product_id IN (' . $product_ids . ')';
        $query[] = 'AND pd.language_id = "' . (int)$this->config->get('config_language_id') . '"';
        $query[] = 'AND p.status = "1" and p.archived = 0';
        $query[] = 'AND p.date_available <= NOW()';
        $query[] = 'AND p2s.store_id = "' . (int)$this->config->get('config_store_id') . '"';
        $this->db->query('SET SESSION group_concat_max_len = 100000');
        $query = $this->db->query(vsprintf(implode(' ', $query), [
            implode(',', $columns)
        ]));
        $show_opt_id   = $this->config->get('config_show_option');

        //Prize Draw
        $prize_draw_status = $this->prize_draw_status();

        if ($query->num_rows) {
            $products = array();
            foreach ($query->rows as $product) {

                $prize_draw = null;
                $consumed_percentage = 0;
                if($prize_draw_status){
                    $prize_draw = $this->product_prize($product['product_id']);
                    $consumed_percentage = $this->product_consumed_percentage($product['product_id'], $product['quantity']);
                }
                
                $option_details = false;
                if($show_opt_id){
                    $option_details = $this->model_section_product->getProductOptionValues($product['product_id'],$show_opt_id);
                }

                if (!isset($this->session->data['wishlist'])) {
                    $this->session->data['wishlist'] = array();
                }

                $display_price = true;
                $product_options = $this->getProductOptions($product['product_id']);
                foreach ($product_options as $product_option) {
                    if ($product_option['required']) {
                        $display_price = false;
                        break;
                    }
                }

                $cart_products = [];
                foreach ($this->session->data['cart'] as $key => $quantity) {
                    $p = explode(':', $key);
                    if(isset($cart_products[$p[0]])){
                        $cart_products[$p[0]] += $quantity;
                        continue;
                    }
                    $cart_products[$p[0]] = $quantity;
                }
                $productRating = round($product['rating']);
                if(!$productRating)
                    $productRating = $this->getAvgProductRating($product['product_id']);

                $products[$product['product_id']] =  array(
                    'product_id' => $product['product_id'],
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'meta_description' => $product['meta_description'],
                    'meta_keyword' => $product['meta_keyword'],
                    'tag' => $product['tag'],
                    'model' => $product['model'],
                    'sku' => $product['sku'],
                    'upc' => $product['upc'],
                    'ean' => $product['ean'],
                    'jan' => $product['jan'],
                    'isbn' => $product['isbn'],
                    'mpn' => $product['mpn'],
                    'location' => $product['location'],
                    'quantity' => $product['quantity'],
                    'stock_status' => $product['stock_status'],
                    'stock_status_id' => $product['stock_status_id'],
                    'stock_status_color' => $product['current_color'],
                    'image' => $product['image'],
                    'manufacturer_id' => $product['manufacturer_id'],
                    'manufacturer' => $product['manufacturer'],
                    'price' => ($product['discount'] ? $product['discount'] : $product['price']),
                    'special' => $product['special'],
                    'special_enddate' => $product['special_enddate'],
                    'reward' => $product['reward'],
                    'points' => $product['points'],
                    'tax_class_id' => $product['tax_class_id'],
                    'date_available' => $product['date_available'],
                    'weight' => $product['weight'],
                    'weight_class_id' => $product['weight_class_id'],
                    'length' => $product['length'],
                    'width' => $product['width'],
                    'height' => $product['height'],
                    'length_class_id' => $product['length_class_id'],
                    'subtract' => $product['subtract'],
                    'rating' => $productRating,
                    'reviews' => $product['reviews'] ? $product['reviews'] : 0,
                    'minimum' => $product['minimum'],
                    'sort_order' => $product['sort_order'],
                    'status' => $product['status'],
                    'date_added' => $product['date_added'],
                    'date_modified' => $product['date_modified'],
                    'viewed' => $product['viewed'],
                    'manufacturerimg' => $product['manufacturerimg'],
                    'product_images' => json_decode($product['product_images'], true),
                    'prize_draw' => $prize_draw,
                    'option'=>$option_details,
                    'consumed_percentage' => $consumed_percentage,
                    'general_use' => $product['general_use'] ?? '',
                    'in_wishlist' => in_array($product['product_id'], $this->session->data['wishlist']) ? 1 : 0 ,
                    'in_cart' => array_key_exists($this->request->get['product_id'], $cart_products) ? $cart_products[$this->request->get['product_id']] : 0,
                    'unlimited' =>$product['unlimited'],
                    'display_price' =>$display_price
                );
            }
            // author : Ahmed abdelfattah
            // Sort Array
            // return only product array and remove empty one (it could be empty because dont have language version)
            $products = array_filter(array_replace(array_flip($Ids), $products), function($product){
                return is_array($product);
            });

            return $products;
        } else {
            return false;
        }
    }

	public function getProductSpecials($data = array()) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$sql = "SELECT DISTINCT ps.product_id, (SELECT AVG(rating) FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = ps.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating FROM " . DB_PREFIX . "product_special ps LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((IFNULL(ps.date_start, '0000-00-00') = '0000-00-00' OR ps.date_start < NOW()) AND (IFNULL(ps.date_end, '0000-00-00') = '0000-00-00' OR ps.date_end > NOW())) GROUP BY ps.product_id";

		$sort_data = array(
			'pd.name',
			'p.model',
			'ps.price',
			'rating',
			'p.sort_order',
            'RAND()'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
				$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
			} else {
				$sql .= " ORDER BY " . $data['sort'];
			}
		} else {
			$sql .= " ORDER BY p.sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC, LCASE(pd.name) DESC";
		} else {
			$sql .= " ASC, LCASE(pd.name) ASC";
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

		$product_data = array();

        if( in_array( __FUNCTION__, array( 'getProducts', 'getTotalProducts', 'getProductSpecials', 'getTotalProductSpecials' ) ) ) {
            if( ! empty( $this->request->get['mfp'] ) || ( NULL != ( $mfSettings = $this->config->get('mega_filter_settings') ) && ! empty( $mfSettings['in_stock_default_selected'] ) ) ) {
                if( ! empty( $this->request->get['mfp'] ) || $this->config->get('mfp_is_activated') || ! empty( $mfSettings['in_stock_default_selected'] ) ) {
                    $this->load->model( 'module/mega_filter' );

                    if(\Extension::isInstalled('advanced_product_attributes')==true && $this->config->get('advanced_product_attribute_status')==1)
                    {
                        $advanced_sql = MegaFilterCore::newInstance( $this, $sql )->getSQL( __FUNCTION__ ,NULL,NULL, array(),1);
   
                    }
                    $sql = MegaFilterCore::newInstance( $this, $sql )->getSQL( __FUNCTION__ ,NULL,NULL, array(),0);
                }
            }
        }
        $query = $this->db->query($sql);
        $rows=$query->rows;
        if(isset($advanced_sql))
        {
            $advanced_query=$this->db->query($advanced_sql);
            //i'm merge using loop beacuse array_merge or + overwrite same key
            foreach($advanced_query->rows as $row)
            {
                $rows[]=$row;
            }
        }
        $returned_product_ids = array_column($rows, 'product_id');
        $products = $this->getProductsByIds($returned_product_ids);
        foreach ($products as $product) {
            // Multi Seller Product checking.
           $seller_information = $this->isProductByAMultiSeller($product['product_id']);
           if ( $seller_information )
           {
               // Check if the selling status exist
               $seller_products_state = $this->getMSProductsState($seller_information['seller_id']);
               // if the state is 0 then return false.
               if ( (string) $seller_products_state === "0" )
               {
                   continue;
               }
           }
            $product_data[$product['product_id']] = $product;
        }

		return $product_data;
	}

    public function getProductSpecialsByCategoryId($category_id, $data = array()) {
        if ($this->customer->isLogged()) {
            $customer_group_id = $this->customer->getCustomerGroupId();
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }

        $sql = "SELECT DISTINCT ps.product_id, (SELECT AVG(rating) FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = ps.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating FROM " . DB_PREFIX . "product_special ps LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id) JOIN `" . DB_PREFIX . "product_to_category` p2c ON (p.product_id = p2c.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((IFNULL(ps.date_start, '0000-00-00') = '0000-00-00' OR ps.date_start < NOW()) AND (IFNULL(ps.date_end, '0000-00-00') = '0000-00-00' OR ps.date_end > NOW())) AND p2c.category_id = " . (int)$category_id . " GROUP BY ps.product_id";

        $sort_data = array(
            'pd.name',
            'p.model',
            'ps.price',
            'rating',
            'p.sort_order'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
                $sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
            } else {
                $sql .= " ORDER BY " . $data['sort'];
            }
        } else {
            $sql .= " ORDER BY p.sort_order";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC, LCASE(pd.name) DESC";
        } else {
            $sql .= " ASC, LCASE(pd.name) ASC";
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

        $product_data = array();

        if( in_array( __FUNCTION__, array( 'getProducts', 'getTotalProducts', 'getProductSpecials', 'getTotalProductSpecials' ) ) ) {
            if( ! empty( $this->request->get['mfp'] ) || ( NULL != ( $mfSettings = $this->config->get('mega_filter_settings') ) && ! empty( $mfSettings['in_stock_default_selected'] ) ) ) {
                if( ! empty( $this->request->get['mfp'] ) || $this->config->get('mfp_is_activated') || ! empty( $mfSettings['in_stock_default_selected'] ) ) {
                    $this->load->model( 'module/mega_filter' );
                    if(\Extension::isInstalled('advanced_product_attributes')==true && $this->config->get('advanced_product_attribute_status')==1)
                    {
                        $advanced_sql = MegaFilterCore::newInstance( $this, $sql )->getSQL( __FUNCTION__ ,NULL,NULL, array(),1);
   
                    }
                    $sql = MegaFilterCore::newInstance( $this, $sql )->getSQL( __FUNCTION__ ,NULL,NULL, array(),0);
                }
            }
        }
        $query = $this->db->query($sql);
        $rows=$query->rows;
        if(isset($advanced_sql))
        {
            $advanced_query=$this->db->query($advanced_sql);
            //i'm merge using loop beacuse array_merge or + overwrite same key
            foreach($advanced_query->rows as $row)
            {
                $rows[]=$row;
            }
        }
                
        $returned_product_ids = array_column($rows, 'product_id');
        $products = $this->getProductsByIds($returned_product_ids);
        foreach ($products as $product) {
            $product_data[$product['product_id']] = $product;
        }

        return $product_data;
    }

	public function getLatestProducts($limit) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$product_data = $this->cache->get('product.latest.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $customer_group_id . '.' . (int)$limit);

		if (!$product_data) {
			$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY p.date_added DESC LIMIT " . (int)$limit);
            $returned_product_ids = array_column($query->rows, 'product_id');
            $products = $this->getProductsByIds($returned_product_ids);
            foreach ($products as $product) {
                $product_data[$product['product_id']] = $product;
            }

			$this->cache->set('product.latest.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'). '.' . $customer_group_id . '.' . (int)$limit, $product_data);
		}

		return $product_data;
	}

    public function getLatestProductsByCategoryId($category_id, $limit, $data = false)
    {
        if ($this->customer->isLogged()) {
            $customer_group_id = $this->customer->getCustomerGroupId();
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }

        if($this->config->get('config_products_default_sorting_column') == 'date_available'){
            $sort = 'date_available';
        }else if (isset($data['sort'])){
            $sort = $data['sort'];
        }else{
            $sort = 'date_added';
        }

        if (isset($data['order'])) {
            $order = $data['order'];
        } else {
            $order = " ASC";
        }

        $query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p JOIN `" . DB_PREFIX . "product_to_category` p2c ON (p.product_id = p2c.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p2c.category_id = " . (int)$category_id . "  ORDER BY p.".$sort." ".$order." LIMIT " . (int)$limit);

        $returned_product_ids = array_column($query->rows, 'product_id');
        $products = $this->getProductsByIds($returned_product_ids);
        foreach ($products as $product) {
            $product_data[$product['product_id']] = $product;
        }

        return $product_data;
    }

	public function getPopularProducts($limit) {
		$product_data = array();

		$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY p.viewed, p.date_added DESC LIMIT " . (int)$limit);
        $returned_product_ids = array_column($query->rows, 'product_id');
        $products = $this->getProductsByIds($returned_product_ids);
        foreach ($products as $product) {
            $product_data[$product['product_id']] = $product;
        }

		return $product_data;
	}

	public function getBestSellerProducts($limit) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

        $domainId = "";
        if ($this->dedicatedDomains->isActive()) {
            $domainId = $this->dedicatedDomains->getDomainId();
        }
        
        if(!empty($domainId))
        {
		$product_data = $this->cache->get('product.bestseller.' . $domainId . '.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'). '.' . $customer_group_id . '.' . (int)$limit);
        }
        else
        {
        $product_data = $this->cache->get('product.bestseller.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'). '.' . $customer_group_id . '.' . (int)$limit);
        }
		if (!$product_data) {
			$product_data = array();

			$query = $this->db->query("SELECT op.product_id, COUNT(*) AS total FROM " . DB_PREFIX . "order_product op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) LEFT JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE o.order_status_id > '0' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' GROUP BY op.product_id ORDER BY total DESC LIMIT " . (int)$limit);
            $returned_product_ids = array_column($query->rows, 'product_id');
            $products = $this->getProductsByIds($returned_product_ids);
            foreach ($products as $product) {
                $product_data[$product['product_id']] = $product;
            }
            if(!empty($domainId))
            {
			$this->cache->set('product.bestseller.' .$domainId.'.'. (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'). '.' . $customer_group_id . '.' . (int)$limit, $product_data);
            }
            else
            {
            $this->cache->set('product.bestseller.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'). '.' . $customer_group_id . '.' . (int)$limit, $product_data);
            }
        }

		return $product_data;
	}

    public function getBestSellerProductsByCategoryId($category_id, $limit)
    {
        if ($this->customer->isLogged()) {
            $customer_group_id = $this->customer->getCustomerGroupId();
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }

        $product_data = $this->cache->get('product.bestseller.bycategory.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'). '.' . $customer_group_id . '.' . $category_id . '.' . (int)$limit);

        if(!$product_data) {
            $product_data = array();
            $query = $this->db->query("SELECT op.product_id, COUNT(*) AS total FROM " . DB_PREFIX . "order_product op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) LEFT JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id) JOIN `" . DB_PREFIX . "product_to_category` p2c ON (p.product_id = p2c.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE o.order_status_id > '0' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p2c.category_id = " . (int)$category_id . " GROUP BY op.product_id ORDER BY total DESC LIMIT " . (int)$limit);
            $returned_product_ids = array_column($query->rows, 'product_id');
            $products = $this->getProductsByIds($returned_product_ids);
            foreach ($products as $product) {
                $product_data[$product['product_id']] = $product;
            }

            //$this->cache->set('product.bestseller.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'). '.' . $customer_group_id . '.' . (int)$limit, $product_data);
            $this->cache->set('product.bestseller.bycategory.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $customer_group_id . '.' . $category_id . '.' . (int)$limit, $product_data);
        }
        return $product_data;
    }

    public function getPrizeProducts($prize_id, $limit, $data)
    {
        if ($this->customer->isLogged()) {
            $customer_group_id = $this->customer->getCustomerGroupId();
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }

        if($this->config->get('config_products_default_sorting_column') == 'date_available'){
            $sort = 'date_available';
        }else if (isset($data['sort'])){
            $sort = $data['sort'];
        }else{
            $sort = 'date_added';
        }

        if (isset($data['order'])) {
            $order = $data['order'];
        } else {
            $order = " ASC";
        }

        $query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p JOIN `" . DB_PREFIX . "prdw_product_to_prize` p2p ON (p.product_id = p2p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2p.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2p.prize_draw_id = " . (int)$prize_id . "  ORDER BY p.".$sort." ".$order." LIMIT " . (int)$limit);

        $returned_product_ids = array_column($query->rows, 'product_id');
        $products = $this->getProductsByIds($returned_product_ids);
        foreach ($products as $product) {
            $product_data[$product['product_id']] = $product;
        }

        return $product_data;
    }

	public function getProductAttributes($product_id) {
		$product_attribute_group_data = array();

		$product_attribute_group_query = $this->db->query("SELECT ag.attribute_group_id, agd.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_group ag ON (a.attribute_group_id = ag.attribute_group_id) LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON (ag.attribute_group_id = agd.attribute_group_id) WHERE pa.product_id = '" . (int)$product_id . "' AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY ag.attribute_group_id ORDER BY ag.sort_order, agd.name");

		foreach ($product_attribute_group_query->rows as $product_attribute_group) {
			$product_attribute_data = array();

			$product_attribute_query = $this->db->query("SELECT a.attribute_id, ad.name, pa.text FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE pa.product_id = '" . (int)$product_id . "' AND a.attribute_group_id = '" . (int)$product_attribute_group['attribute_group_id'] . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND pa.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY a.sort_order, ad.name");

			foreach ($product_attribute_query->rows as $product_attribute) {
				$product_attribute_data[] = array(
					'attribute_id' => $product_attribute['attribute_id'],
					'name'         => $product_attribute['name'],
					'text'         => $product_attribute['text']
				);
			}

			$product_attribute_group_data[] = array(
				'attribute_group_id' => $product_attribute_group['attribute_group_id'],
				'name'               => $product_attribute_group['name'],
				'attribute'          => $product_attribute_data
			);
		}

		return $product_attribute_group_data;
	}

    public function getProductAttributesCustom($product_id)
    {
        $product_attribute_data = array();

        $product_attribute_query = $this->db->query("SELECT attribute_id FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' GROUP BY attribute_id");

        foreach ($product_attribute_query->rows as $product_attribute) {

            if($this->config->get('wk_amazon_connector_status')){
                $getSpecificationEntry = $this->Amazonconnector->checkSpecificationEntry(array('attribute_id' => $product_attribute['attribute_id']));
                if(isset($getSpecificationEntry) && $getSpecificationEntry){
                    continue;
                }
            }
            $product_attribute_description_data = array();

            $product_attribute_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

            foreach ($product_attribute_description_query->rows as $product_attribute_description) {
                $product_attribute_description_data[$product_attribute_description['language_id']] = array('text' => $product_attribute_description['text']);
            }

            $product_attribute_data[] = array(
                'attribute_id' => $product_attribute['attribute_id'],
                'product_attribute_description' => $product_attribute_description_data
            );
        }

        return $product_attribute_data;
    }

	public function getProductOptions($product_id) {
		$product_option_data = array();

        // Product Option Image PRO module <<
        $this->load->model('module/product_option_image_pro');
        $images = $this->model_module_product_option_image_pro->getProductOptionImages($product_id);
        $product_poip_settings = $this->model_module_product_option_image_pro->getRealProductSettings($product_id);
        // >> Product Option Image PRO module
        
        $dedicatedDomain = null;
        if ($this->dedicatedDomains->isActive()) {
            $dedicatedDomain = $this->dedicatedDomains->getDomain();;
        }

		$product_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY po.sort_order,o.sort_order");
        
        foreach ($product_option_query->rows as $product_option) {
			if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image' || $product_option['type'] == 'product') {
				$product_option_value_data = array();

                if ($product_option['type'] == 'product' && \Extension::isInstalled('product_builder') && $this->config->get('product_builder')['status']) {
				    $product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (ov.valuable_id = pd.product_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY pov.sort_order");
                } else {    
                    $kanwatSort = '';
                    if (\Extension::isInstalled('knawat_dropshipping')) {
                        
                         $iskanwatProduct = ($this->db->query("SELECT meta_value FROM ". DB_PREFIX ."knawat_metadata WHERE resource_id ='" . (int)$product_id . "' AND meta_key = 'is_knawat' "))->row['meta_value'];

                         $kanwatSort = ($iskanwatProduct == 1) ? ',ovd.name asc' : '';

                     }    
                            
				    $product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ov.sort_order , pov.sort_order$kanwatSort");
                }
				foreach ($product_option_value_query->rows as $product_option_value) {
                                    
                    // Product Option Image PRO module <<
                    $option_value_images = array();
                    if (isset($images[$product_option['product_option_id']][$product_option_value['product_option_value_id']])) {
                        foreach ($images[$product_option['product_option_id']][$product_option_value['product_option_value_id']] as $image) {
                            $option_value_images[] = $image;
                        }
                    }
                    // >> Product Option Image PRO module
                    
				    if ($dedicatedDomain) {
                        /*$product_option_value['price'] = $this->currency->convert(
                            $product_option_value['price'],
                            $dedicatedDomain['currency'],
                            $this->config->get('config_currency')
                        );*/
                    }

					$product_option_value_data[] = array(
                        // Product Option Image PRO module <<
                        'images' => $option_value_images,
                        // >> Product Option Image PRO module
                        
						'product_option_value_id' => $product_option_value['product_option_value_id'],
						'option_value_id'         => $product_option_value['option_value_id'],
						'name'                    => $product_option_value['name'],
						'image'                   => $product_option_value['image'],
						'quantity'                => $product_option_value['quantity'],
						'subtract'                => $product_option_value['subtract'],
						'price'                   => $product_option_value['price'],
						'price_prefix'            => $product_option_value['price_prefix'],
						'weight'                  => $product_option_value['weight'],
						'weight_prefix'           => $product_option_value['weight_prefix'],
                        'valuable_id'             => $product_option_value['valuable_id']
					);
				}

				if (!empty($product_option_value_data) && count($product_option_value_data) > 0 ) {
                    $product_option_data[] = array(
                        'product_option_id' => $product_option['product_option_id'],
                        'option_id' => $product_option['option_id'],
                        'name' => $product_option['name'],
                        'type' => $product_option['type'],
                        'option_value' => $product_option_value_data,
                        'custom_option' => $product_option['custom_option'],
                        'required' => $product_option['required']
                    );
                }
			} else {
				$product_option_data[] = array(
					'product_option_id' => $product_option['product_option_id'],
					'option_id'         => $product_option['option_id'],
					'name'              => $product_option['name'],
					'type'              => $product_option['type'],
					'option_value'      => $product_option['option_value'],
                    'custom_option'     => $product_option['custom_option'],
                    'required'          => $product_option['required']
				);
			}
      	}

		return $product_option_data;
	}

    /**
     *   Get option(main selected) relational option(related to main)
     *
     *   @By Ali Hemeda
     *   @param array $data [
     *                       'product_id' -- Product Id
     *                       'option_id' -- main selected option id
     *                       'pr_op_id' -- main selected option value id
     *                       'child_opt' -- related option id
     *                       'parents_values' -- main selected, option value ids of it's parent option if exists
     *                      ]
     *   @return array
     */
    public function getProductRelationalOptions($data) {

        //Prepare product_variations like query, getting parents option_value_id by product_option_value_id
        $parentVaules = trim($data['parents_values'], ',');
        $parentVaules = rtrim($parentVaules, ',');

        $like = '';
        $likes = [];
        if($parentVaules){

            $parent_product_option_ids_query  = $this->db->query("SELECT option_value_id FROM " . DB_PREFIX . "product_option_value 
                                                              WHERE product_id = '" . (int)$data['product_id'] . "' 
                                                              AND product_option_value_id IN (" .$parentVaules.")");
            $parent_product_option_ids_reslut = $parent_product_option_ids_query->rows;
            if(count($parent_product_option_ids_reslut)){
                $like .= 'AND (';
                foreach ($parent_product_option_ids_reslut as $parent_product_option_id){
                    //id place conditions : at the firest #, or at the middle ,#, or at the end ,#
                    //This to avoid wrong match: say LIKE '%8%' in this case '88' will match and this is wrong
                    $likes[] = '(pvar.option_value_ids LIKE \'' . (int)$parent_product_option_id['option_value_id'] . ',%\' OR pvar.option_value_ids LIKE \'%,' . (int)$parent_product_option_id['option_value_id'] . ',%\' OR pvar.option_value_ids LIKE \'%,' . (int)$parent_product_option_id['option_value_id'] . '\')';
                }
                $likes = implode($likes, ' AND ');
                $like .= $likes. ')';
            }
        }
        ///////////////////////////////////////////////////////////////////////////////////////////////////////

        $child_product_option_id_query = $this->db->query("SELECT product_option_id FROM " . DB_PREFIX . "product_option 
                                                           WHERE product_id = '" . (int)$data['product_id'] . "' 
                                                           AND option_id = '" . (int)$data['child_opt'] . "'");
        $child_product_option_id = $child_product_option_id_query->row['product_option_id'];

        //main data query
        $product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov 
                                                                 LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) 
                                                                 LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) 
                                                                 LEFT JOIN " . DB_PREFIX . "product_variations pvar ON (pvar.product_id = pov.product_id)
                                                                 WHERE pov.product_id = '" . (int)$data['product_id'] . "' 
                                                                 AND ( (pvar.product_quantity = '-1' OR pvar.product_quantity > 0)
                                                                        AND (   option_value_ids LIKE CONCAT('', pov.option_value_id ,',%') 
                                                                                OR option_value_ids LIKE CONCAT('%,', pov.option_value_id ,',%') 
                                                                                OR option_value_ids LIKE CONCAT('%,', pov.option_value_id ,'')
                                                                            ) ".$like."
                                                                     )
                                                                 AND pov.product_option_id = '" . (int)$child_product_option_id . "' 
                                                                 AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
                                                                 ORDER BY ov.sort_order");

        return $product_option_value_query->rows ?? [];
    }

    public function getOption($option_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "option` o LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE o.option_id = '" . (int)$option_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "'");

        return $query->row;
    }

    public function getOptionValue($option_value_id) {
        $query = $this->db->query("SELECT *, ovd.name as ov_name FROM " . DB_PREFIX . "option_value ov LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) LEFT JOIN " . DB_PREFIX . "option o ON (o.option_id = ov.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE ov.option_value_id = '" . (int)$option_value_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

        return $query->row;
    }

    public function getOptions($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "option` o LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE od.language_id = '" . (int)$this->config->get('config_language_id') . "'";
        
        if (isset($data['filter_name']) && !is_null($data['filter_name'])) {
            $sql .= " AND od.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

        $sort_data = array(
            'od.name',
            'o.type',
            'o.sort_order'
        );  
        
        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];   
        } else {
            $sql .= " ORDER BY od.name";    
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

    // public function getOptionValues($option_id) {
    //     $option_value_data = array();
        
    //     $option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_value ov LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE ov.option_id = '" . (int)$option_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ov.sort_order ASC");
                
    //     foreach ($option_value_query->rows as $option_value) {
    //         $option_value_data[] = array(
    //             'option_value_id' => $option_value['option_value_id'],
    //             'name'            => $option_value['name'],
    //             'image'           => $option_value['image'],
    //             'sort_order'      => $option_value['sort_order']
    //         );
    //     }
        
    //     return $option_value_data;
    // }

        public function getProductOptionsCustom($product_id)
    {
        $product_option_data = array();

        $product_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.sort_order");

        foreach ($product_option_query->rows as $product_option) {
            if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image' || $product_option['type'] == 'product') {
                $product_option_value_data = array();

                if ($product_option['type'] == 'product' && \Extension::isInstalled('product_builder') && $this->config->get('product_builder')['status']) {
                    $product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (ov.valuable_id = pd.product_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ov.sort_order");
                } else {
                    $product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ov.sort_order");
                }

                foreach ($product_option_value_query->rows as $product_option_value) {
                    $product_option_value_data[] = array(
                        'product_option_value_id' => $product_option_value['product_option_value_id'],
                        'option_value_id' => $product_option_value['option_value_id'],
                        'name' => $product_option_value['name'],
                        'image' => $product_option_value['image'],
                        'quantity' => $product_option_value['quantity'],
                        'subtract' => $product_option_value['subtract'],
                        'price' => $product_option_value['price'],
                        'price_prefix' => $product_option_value['price_prefix'],
                        'weight' => $product_option_value['weight'],
                        'weight_prefix' => $product_option_value['weight_prefix']
                    );
                }

                $product_option_data[] = array(
                    'product_option_id' => $product_option['product_option_id'],
                    'option_id' => $product_option['option_id'],
                    'name' => $product_option['name'],
                    'type' => $product_option['type'],
                    'option_value' => $product_option_value_data,
                    'required' => $product_option['required']
                );
            } else {
                $product_option_data[] = array(
                    'product_option_id' => $product_option['product_option_id'],
                    'option_id' => $product_option['option_id'],
                    'name' => $product_option['name'],
                    'type' => $product_option['type'],
                    'option_value' => $product_option['option_value'],
                    'required' => $product_option['required']
                );
            }
        }

        return $product_option_data;
    }

	public function getProductDiscounts($product_id) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

        $this->load->model('module/dedicated_domains/domain_prices');



        if ($this->model_module_dedicated_domains_domain_prices->isActive()) {
            $domainData = $this->model_module_dedicated_domains_domain_prices->getDomain();
            return $this->model_module_dedicated_domains_domain_prices->getProductDedicatedDiscounts(
                $product_id,
                $customer_group_id,
                $domainData['domain_id'],
                $domainData['currency']
            );
        } else {


        }

        $queryString = [];
        $queryString[] = "SELECT * FROM " . DB_PREFIX . "product_discount";
        $queryString[] = "WHERE product_id = '" . (int)$product_id . "' AND";
        $queryString[] = "customer_group_id = '" . (int)$customer_group_id . "' AND";
        $queryString[] = "quantity > 1 AND";
        $queryString[] = "((IFNULL(date_start, '0000-00-00') = '0000-00-00' OR date_start < NOW()) AND";
        $queryString[] = "(IFNULL(date_end, '0000-00-00') = '0000-00-00' OR date_end > NOW()))";

        $queryString[] = "ORDER BY priority ASC, quantity ASC, price ASC";

        $query = $this->db->query(implode(' ', $queryString));

		return $query->rows;
	}

	public function getProductImages($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "' ORDER BY sort_order ASC");

		return $query->rows;
	}

	public function getProductRelated($product_id) {
		$product_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related pr LEFT JOIN " . DB_PREFIX . "product p ON (pr.related_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pr.product_id = '" . (int)$product_id . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		foreach ($query->rows as $result) {
		    if (isset($result['related_id'])) {
                $product_data[$result['related_id']] = $this->getProduct($result['related_id']);
            }
		}

		return $product_data;
	}

	public function getProductLayoutId($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return $query->row['layout_id'];
		} else {
			return  $this->config->get('config_layout_product');
		}
	}

	public function getCategories($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

		return $query->rows;
	}

	public function getTotalProducts($data = array()) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$sql = "SELECT  p.product_id";
        

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)";
			} else {
				$sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
			}

			if (!empty($data['filter_filter'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p2c.product_id = pf.product_id) LEFT JOIN " . DB_PREFIX . "product p ON (pf.product_id = p.product_id)";
			} else {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
			}
		}else if($data['pc_target'] && $data['pc_target'] == 'pc_app'){
            $sql .= " FROM " . DB_PREFIX . "pc_product_brand_mapping pbm  ";
            $sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p.product_id = pbm.product_id)";
        }  else {
			$sql .= " FROM " . DB_PREFIX . "product p";
		}

        if (isset($data['city_id']) && $data['city_id'] > 0) {
            $sql .= " LEFT JOIN ms_product as md on md.product_id=p.product_id left join `ms_seller` as ms on ms.seller_id=md.seller_id";
        }
        if(($data['trips']==1) &&(\Extension::isInstalled('multiseller'))&&(\Extension::isInstalled('trips') &&$this->config->get('trips')['status']==1))   {
            $sql .= " LEFT JOIN " . DB_PREFIX . "trips_product trips_pro ON (trips_pro.product_id = p.product_id)";
            if(isset($data['filterbyArea'])){
                $sql .= " LEFT JOIN " . DB_PREFIX . "geo_area_locale geoarea ON (trips_pro.area_id = geoarea.area_id)";
                }
			
        }
        if (!empty($data['categories_ids'])|| isset($data['filterText']) || isset($data['popular'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category pro_cat ON (pro_cat.product_id = p.product_id)";
            if (isset($data['filterText'])) {
                $sql .= " LEFT JOIN " . DB_PREFIX . "category_description cat ON (pro_cat.category_id = cat.category_id)";
            }
        }
     
        if (isset($data['path']) && $data['path'] =='deals') {
            $sql .= " LEFT JOIN ".
            "((SELECT product_id,price,date_end FROM  product_discount WHERE date_end >= NOW()) UNION (SELECT product_id,price,date_end FROM product_special WHERE date_end >= NOW()))"
            ." ps ON ps.product_id = p.product_id ";
        }

        if(isset($data['ignore_lang']) && $data['ignore_lang'] === true){
            $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
        }
        else{
            $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
        }
    
        
     

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
			} else {
				$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
			}

			if (!empty($data['filter_filter'])) {
				$implode = array();

				$filters = explode(',', $data['filter_filter']);

				foreach ($filters as $filter_id) {
					$implode[] = (int)$filter_id;
				}

				$sql .= " AND pf.filter_id IN (" . implode(',', $implode) . ")";
			}
		}
        if (!empty($data['categories_ids'])) {
            $categories_ids = implode(" , ", $data['categories_ids']);
            $sql .= " AND pro_cat.category_id IN ({$categories_ids})";
        }

		if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";

			if (!empty($data['filter_name'])) {
				$implode = array();

				if (\Extension::isInstalled('fast_finder') && $this->config->get('fast_finder')['separated_word_search']) {
                    $implode[] = "(pd.name LIKE '% " . $this->db->escape($data['filter_name']) . " %' 
                                OR pd.name LIKE '".$this->db->escape($data['filter_name'])." %' 
                                OR pd.name LIKE '% ".$this->db->escape($data['filter_name'])."')";
                } else {
                    $words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['filter_name'])));

                    foreach ($words as $word) {
                        $implode[] = "(pd.name LIKE '%" . $this->db->escape($word) . "%' OR REPLACE(pd.name, '''', '') LIKE '%".$this->db->escape($word)."%')";
                    }
                }

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

				if (!empty($data['filter_description'])) {
					$sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}
			}

			if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}

			if (!empty($data['filter_tag'])) {
				$sql .= "pd.tag LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_tag'])) . "%'";
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			$sql .= ")";
		}
        if (isset($data['path']) && $data['path'] =='deals') {
 
            $sql .= " AND ps.date_end >= NOW() ";
        }
        if($data['pc_target'] && $data['pc_target'] == 'pc_app'){
            if(isset($data['pc_brand_id']) AND $data['pc_brand_id'] != 0 )
            {

                $sql .= " AND pbm.pc_brand_id = ".(int)$data['pc_brand_id'] ." ";
            }

            if(isset($data['pc_model_id']) AND $data['pc_model_id'] != 0 )
            {

                $sql .= " AND pbm.pc_model_id = ".(int)$data['pc_model_id'] ." ";
            }

            if(isset($data['pc_year_id']) AND $data['pc_year_id'] != 0 )
            {

                $sql .= " AND pbm.pc_year_id = ".(int)$data['pc_year_id'] ." ";
            }
        }

        if (isset($data['city_id']) && $data['city_id'] > 0) {
            $sql .= " AND ms.zone_id=" . $data['city_id'];
        }

		if (!empty($data['filter_manufacturer_id'])) {
			$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
		}

        if( in_array( __FUNCTION__, array( 'getProducts', 'getTotalProducts', 'getProductSpecials', 'getTotalProductSpecials' ) ) ) {
            if( ! empty( $this->request->get['mfp'] ) || ( NULL != ( $mfSettings = $this->config->get('mega_filter_settings') ) && ! empty( $mfSettings['in_stock_default_selected'] ) ) ) {

                if( ! empty( $this->request->get['mfp'] ) || $this->config->get('mfp_is_activated') || ! empty( $mfSettings['in_stock_default_selected'] ) ) {
                    $this->load->model( 'module/mega_filter' );

                    if(\Extension::isInstalled('advanced_product_attributes')==true && $this->config->get('advanced_product_attribute_status')==1)
                    {
                        $advanced_sql = MegaFilterCore::newInstance( $this, $sql )->getSQL( __FUNCTION__ ,NULL,NULL, array(),1);
   
                    }
                    $sql = MegaFilterCore::newInstance( $this, $sql )->getSQL( __FUNCTION__ ,NULL,NULL, array(),0);
                }
            }
        }

        // $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

        if(\Extension::isInstalled('multiseller')) {
            $sql_disable = "SELECT p.product_id as 'product_id'";

            /* Filters */
            if (!empty($data['filter_category_id'])) {
                if (!empty($data['filter_sub_category'])) {
                    $sql_disable .= " FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)";
                } else {
                    $sql_disable .= " FROM " . DB_PREFIX . "product_to_category p2c";
                }
                if (!empty($data['filter_filter'])) {
                    $sql_disable .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p2c.product_id = pf.product_id) LEFT JOIN " . DB_PREFIX . "product p ON (pf.product_id = p.product_id)";
                } else {
                    $sql_disable .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
                }
            } else {
                $sql_disable .= " FROM " . DB_PREFIX . "product p";
            }

            $sql_disable .= " LEFT JOIN `" . DB_PREFIX . "ms_product` mp ON (p.product_id = mp.product_id)";

            $sql_disable .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

            if (!empty($data['filter_category_id'])) {
                if (!empty($data['filter_sub_category'])) {
                    $sql_disable .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
                } else {
                    $sql_disable .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
                }

                if (!empty($data['filter_filter'])) {
                    $implode = array();

                    $filters = explode(',', $data['filter_filter']);

                    foreach ($filters as $filter_id) {
                        $implode[] = (int)$filter_id;
                    }

                    $sql_disable .= " AND pf.filter_id IN (" . implode(',', $implode) . ")";
                }
            }

            if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
                $sql_disable .= " AND (";

                if (!empty($data['filter_name'])) {
                    $implode = array();

                    if (\Extension::isInstalled('fast_finder') && $this->config->get('fast_finder')['separated_word_search']) {
                   	 	$implode[] = "(pd.name LIKE '% " . $this->db->escape($data['filter_name']) . " %' 
                                	OR pd.name LIKE '".$this->db->escape($data['filter_name'])." %' 
                                	OR pd.name LIKE '% ".$this->db->escape($data['filter_name'])."')";
	                } else {
	                    $words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['filter_name'])));

	                    foreach ($words as $word) {
	                        $implode[] = "(pd.name LIKE '%" . $this->db->escape($word) . "%' OR REPLACE(pd.name, '''', '') LIKE '%".$this->db->escape($word)."%')";
	                    }
	                }

                    if ($implode) {
                        $sql_disable .= " " . implode(" AND ", $implode) . "";
                    }

                    if (!empty($data['filter_description'])) {
                        $sql_disable .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
                    }
                }

                if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
                    $sql_disable .= " OR ";
                }

                if (!empty($data['filter_tag'])) {
                    $sql_disable .= "pd.tag LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_tag'])) . "%'";
                }

                if (!empty($data['filter_name'])) {
                    $sql_disable .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                }

                if (!empty($data['filter_name'])) {
                    $sql_disable .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                }

                if (!empty($data['filter_name'])) {
                    $sql_disable .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                }

                if (!empty($data['filter_name'])) {
                    $sql_disable .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                }

                if (!empty($data['filter_name'])) {
                    $sql_disable .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                }

                if (!empty($data['filter_name'])) {
                    $sql_disable .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                }

                if (!empty($data['filter_name'])) {
                    $sql_disable .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                }

                $sql_disable .= ")";
            }

            if (!empty($data['filter_manufacturer_id'])) {
                $sql_disable .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
            }
            if( (\Extension::isInstalled('multiseller'))
            &&(\Extension::isInstalled('trips') &&$this->config->get('trips')['status']==1)) 
            {
                if($data['trips']==1){
                $sql .= " AND trips_pro.from_date >= CURDATE()";
                if(isset($data['filterbyArea']))
                {
                $sql .= " AND ( ";
                $implode = [];
                $filter_name = $data['filterbyArea'];
                $words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $filter_name)));
    
                foreach ($words as $word) {
                    $word = $this->db->escape($word);
                    $implode[] = " geoarea.name LIKE '%{$word}%'";
                }
    
                if ($implode) {
                    $sql .= implode(" AND ", $implode);
                }
    
                $sql .= " ) ";
                }
                if(isset($data['from_date']) && isset($data['to_date']))
                {
                    $sql .= " AND trips_pro.from_date >= '".$data['from_date']."' AND trips_pro.to_date <= '".$data['to_date']."' ";
                }
                elseif(isset($data['from_date']))
                {
                    $sql .= " AND trips_pro.from_date >= '".$data['from_date']."' ";
                }
                elseif(isset($data['to_date']))
                {
                    $sql .= " AND  trips_pro.to_date <= '".$data['to_date']."' ";
                }
              }elseif (empty($data['trips'])) {
             $sql .= " AND p.product_id NOT IN ( SELECT product_id FROM trips_product WHERE product_id IS NOT NULL)";
             }
            }

            $this->language->load('multiseller/multiseller');

            $sql_disable .= " AND mp.list_until < NOW() AND mp.list_until != '0000-00-00' AND mp.list_until IS NOT NULL AND p.status = 1";

            $res_disable = $this->db->query($sql_disable);

            if ($res_disable->num_rows) {
                foreach ($res_disable->rows as $product) {
                    $this->MsLoader->MsProduct->changeStatus((int)$product['product_id'], MsProduct::STATUS_DISABLED);
                    $this->MsLoader->MsProduct->disapprove((int)$product['product_id']);

                    $seller_id = $this->MsLoader->MsProduct->getSellerId((int)$product['product_id']);
                    $mail = array(
                        'recipients' => $this->MsLoader->MsSeller->getSellerEmail($seller_id),
                        'addressee' => $this->MsLoader->MsSeller->getSellerName($seller_id),
                        'seller_id' => $seller_id,
                        'product_id' => (int)$product['product_id']
                    );
                    $this->MsLoader->MsMail->sendMail(MsMail::SMT_REMIND_LISTING, $mail);
                }
            }
        }

        $query = $this->db->query($sql);

        $rows=$query->rows;
        if(isset($advanced_sql))
        {
            $advanced_query=$this->db->query($advanced_sql);

            //i'm merge using loop beacuse array_merge or + overwrite same key
            foreach($advanced_query->rows as $row)
            {
                $rows[]=$row;
            }
        }
        $products_ids = array_column($rows, 'product_id');
        $unique_ids=array_unique($products_ids);

        $this->load->model('module/dedicated_domains/domain_prices');
        if ($this->model_module_dedicated_domains_domain_prices->isActive()) {
            return count($this->getProductsByIdsForDedicatedDomains($unique_ids));
        }
        $total=count($unique_ids);
        return $total;
        
	}

	public function getTotalProductSpecials() {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

        $sql = "SELECT ps.product_id FROM " . DB_PREFIX . "product_special ps LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((IFNULL(ps.date_start, '0000-00-00') = '0000-00-00' OR ps.date_start < NOW()) AND (IFNULL(ps.date_end, '0000-00-00') = '0000-00-00' OR ps.date_end > NOW()))";

        if( ! empty( $this->request->get['mfp'] ) || ( NULL != ( $mfSettings = $this->config->get('mega_filter_settings') ) && ! empty( $mfSettings['in_stock_default_selected'] ) ) ) {
            $this->load->model( 'module/mega_filter' );

            if(\Extension::isInstalled('advanced_product_attributes')==true && $this->config->get('advanced_product_attribute_status')==1)
            {
                 $advanced_sql = MegaFilterCore::newInstance( $this, $sql )->getSQL( __FUNCTION__ ,NULL,NULL, array(),1);
   
            }
            $sql = MegaFilterCore::newInstance( $this, $sql )->getSQL( __FUNCTION__ ,NULL,NULL, array(),0);
        }

        $query = $this->db->query( $sql );
        $rows=$query->rows;
        if(isset($advanced_sql))
        {
            $advanced_query=$this->db->query($advanced_sql);

            //i'm merge using loop beacuse array_merge or + overwrite same key
            foreach($advanced_query->rows as $row)
            {
                $rows[]=$row;
            }
        }
        $products_ids = array_column($rows, 'product_id');
        $unique_ids=array_unique($products_ids);
        $total=count($unique_ids);
        return $total;
	}

    public function checkMSModule()
    {
        // $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

        if (\Extension::isInstalled('multiseller')) {
            $search_by_city = $this->config->get('msconf_search_by_city');

            if (isset($search_by_city) && $search_by_city == 1) {
                return true;
            }
        }

        return false;
    }

    public function get_zones()
    {
        $query = $this->db->query("
			SELECT * FROM `ms_seller` inner join `zone` on `ms_seller`.zone_id=`zone`.zone_id group by zone.name
		");

        if ($query->num_rows > 0) {
            return $query->rows;
        }

        return null;
    }

    public function getProductByName($name , $lang_id = null)
    {
        $queryString = '
            SELECT * FROM ' . DB_PREFIX . 'product_description WHERE slug="' . $this->db->escape( str_replace(' ' , '-' , strtolower($name)) ) . '"
        ';
        if($lang_id != null){
            $queryString .= " AND language_id = " . (int)$lang_id;
        }
        $data = $this->db->query($queryString);

        if ($data->num_rows > 0) {
            return $data;
        } else {
            return false;
        }
    }

    public function getProductVariationSku($product_id,$option_value_ids)
    {
        $query = $this->db->query("SELECT * FROM product_variations WHERE product_id = '" . (int)$product_id . "' and option_value_ids = '$option_value_ids'");
        return $query->row;
    }

    public function getProductVariationSkuById($product_id)
    {
        $query = $this->db->query("SELECT * FROM product_variations WHERE product_id = '" . (int)$product_id . "'");
        return $query->rows;
    }

    public function getProductVaritionsByOvIds($p_id, $ov_ids)
    {
        $ov_ids= array_filter(explode(",",$ov_ids), function($item){
            if (!empty($item)){
                return $item;
            }
        });

        if (!empty($ov_ids)) {
            $ov_ids_query = "";
            foreach ($ov_ids as $key => $ov_id) {
                $ov_ids_query .= " and FIND_IN_SET(". $this->db->escape($ov_id) .", option_value_ids)";

            }
            $query = $this->db->query("SELECT * FROM product_variations WHERE product_id = '" . $this->db->escape($p_id) . "'" . $ov_ids_query);
            $row = $query->row;
        }
        return isset($row) ? $row : false;
    }

    public function getProductOptionValue($product_id,$product_option_value_id)
    {
        $query = $this->db->query("SELECT * FROM product_option_value WHERE product_id = '" . (int)$product_id . "' and product_option_value_id = $product_option_value_id");
        return $query->row;
    }

    public function getCustomerGroups($data = array()) {
        $lang_id = $this->config->get('config_language_id') ? (int) $this->config->get('config_language_id') : 1;

        $sql = "SELECT * FROM " . DB_PREFIX . "customer_group cg LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (cg.customer_group_id = cgd.customer_group_id) WHERE cgd.language_id = '{$lang_id}'";
        
        $sort_data = array(
            'cgd.name',
            'cg.sort_order'
        );  
            
        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];   
        } else {
            $sql .= " ORDER BY cgd.name";   
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

    //Check prize draw app
    private function prize_draw_status(){
        $this->load->model('module/prize_draw');
        return $this->model_module_prize_draw->isActive();
    }
    //get product prize
    private function product_prize($product_id){
        $this->load->model('module/prize_draw');
        $product_prize = $this->model_module_prize_draw->getProductPrize($product_id);
        if($product_prize){
            return $product_prize;
        }
        return null;
    }
    //get product consumed in percentage
    private function product_consumed_percentage($product_id, $product_quantity){
        $this->load->model('module/prize_draw');
        $ordered_count = $this->load->model_module_prize_draw->getOrderedCount($product_id);

        $consumed_percentage = ( ((int)$ordered_count * 100) / (int)$product_quantity ) ?? 0;

        return $consumed_percentage;
    }


    /**
    * Get product video links Module information.
    *
    * @return Array of order_status_id_evu & external_video_url values
    */
    public function getVideoURLInfo($product_id){

        $query = $this->db->query("SELECT external_video_url FROM `". DB_PREFIX ."product` WHERE product_id='" . $product_id . "'");
                
        return [
            'external_video_url'  => $query->row['external_video_url'],
        ];
    }

    /**
    * Get products that have videos links.
    *
    * @return Array of products external_video_url field
    */
    public function getProductsWithVideos($customer_id){

        $query = $this->db->query("SELECT p.product_id, pd.name, p.external_video_url, p.image 
            FROM `". DB_PREFIX ."product` as p
            
            JOIN `". DB_PREFIX ."product_description` as pd
            On p.product_id = pd.product_id

            JOIN `". DB_PREFIX ."order_product` as op
            ON op.product_id = p.product_id

            JOIN `". DB_PREFIX ."order` as o
            ON o.order_id = op.order_id

            WHERE pd.language_id = '" . $this->config->get('config_language_id') . "'
            AND o.order_status_id = ". $this->config->get('product_video_links_order_status_id_evu') ." AND external_video_url!='' AND o.customer_id='" . $customer_id . "'");
        
        return $query->rows;
    }

    public function addQty(int $product_id, int $qty)
    {
        $old_qty = $this->getQty($product_id);
        $final_qty = $old_qty + $qty;

        return $this->db->query("UPDATE ".DB_PREFIX."product SET `quantity` = '{$final_qty}' WHERE `product_id` = '{$product_id}'");
    }

    public function getQty(int $product_id)
    {
        $result = $this->db->query("SELECT `quantity` FROM ".DB_PREFIX."`product` WHERE `product_id` = '{$product_id}' LIMIT 1");

        return $result->row['quantity'];
    }
    
    /** Get catalog Id for Facebook_catalog products'
     * @param $customer_id
     * @return mixed
     */
    public function getFacebookCatalogId($product_id=0){
        // $query = $this->db->query("SELECT catalog_id FROM `". DB_PREFIX ."facebook_catalog_queue_jobs` LIMIT 1 ");        
        $result = [];
        $result['catalog_id'] = $this->db->query("SELECT facebook_catalog_id AS catalog_id FROM `". DB_PREFIX ."product_facebook` WHERE expand_product_id = ".(int)$product_id." LIMIT 1")->row['catalog_id'] ?? 0;
        return $result;
    }


    public function isKnawatProduct($product_id) {

        if (!\Extension::isInstalled('knawat_dropshipping')) {
            return false;
        }

        return ($this->db->query("SELECT meta_value FROM ". DB_PREFIX ."knawat_metadata WHERE resource_id ='" . (int)$product_id . "' AND meta_key = 'is_knawat' "))->row['meta_value'] == 1;
    }

    public function getOrderProductAddedByUserType($order_id, $product_id){
        return $this->db->query("
            SELECT added_by_user_type 
            FROM `".DB_PREFIX."order_product` 
            WHERE order_id=".(int)$order_id." 
            AND product_id=".(int)$product_id)->row['added_by_user_type'];
    }

    public function checkProductRentals($product_id,$date){
        $sql = "SELECT SUM(op.quantity) as rented_items
            FROM `". DB_PREFIX ."order_product_rental` as opr
            JOIN `". DB_PREFIX ."order_product` as op
            On opr.order_product_id = op.order_product_id
            WHERE op.product_id = '" . $product_id . "'
            AND opr.from_date <= '" . $date . "' AND opr.to_date>='" . $date . "'";
        $query = $this->db->query($sql);
        return $query->row;
    }

    public function getRentDisabledDates($from,$to,$product_id,$stock_quantity,$cart_quantity = 0)
    {
        
        // $from and $to are timpestamps
        $disabled_days = [];
        $begin = new DateTime(date('Y-m-d',$from));
        $end = new DateTime(date('Y-m-d',$to));
        $end->setTime(0,0,1);
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($begin, $interval, $end);
        foreach ($period as $dt) {
            $data = $this->checkProductRentals($product_id,$dt->format("Y-m-d"));
            //two conditions 1-only display the dates in product page 2-submit dates to cart
            if($cart_quantity == 0 && $data['rented_items'] >= $stock_quantity)
                // displaying dates in product page
                $disabled_days[] = $dt->format("Y-m-d");
            elseif( $cart_quantity != 0 && ($data['rented_items'] + $cart_quantity ) > $stock_quantity )
                // submit dates on add to cart pressed
                $disabled_days[] = $dt->format("Y-m-d");
        }
        return $disabled_days;
    }


    public function checkIfProductHasOptions($product_id){
        $product_option_query = $this->db->query("SELECT COUNT(product_id) AS total FROM " . DB_PREFIX . "product_option  WHERE product_id = '" . (int)$product_id . "' ");
        return ($product_option_query->row['total'] > 0) ? TRUE : FALSE;

    }

    public function getCategoriesWithDesc($product_id) {
        $sql = "SELECT * FROM " . DB_PREFIX . "category WHERE category_id IN (SELECT category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id = '".(int)$product_id ."')";
        $categories = $this->db->query($sql);

        $fullCategories = [];
        $this->load->model('tool/image');

        foreach ($categories->rows as $key => $category) {

            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int)$category['category_id'] . "'");
            foreach ($query->rows as $result) {
                $description[$result['language_id']] = array(
                    'name' => $result['name'],
                    'description' => $result['description'],
                    'meta_keyword' => $result['meta_keyword'],
                    'meta_description' => $result['meta_description']
                );
            }

            if($category['image']){
                $image = $this->model_tool_image->resize($category['image'], 550, 550);
                $category['image'] = $image;
            }

            $category['category_description'] = $description;
            $fullCategories[] = $category;
        }

        return $fullCategories;
    }

    public function getProductBundles($main_product_id) {
        $query = $this->db->query("SELECT pb.bundle_product_id, pb.discount FROM " . DB_PREFIX . "product_bundles pb WHERE pb.main_product_id = " . (int)$main_product_id);
        $rows = $query->rows;
        $returned_product_ids = array_column($rows, 'bundle_product_id');
        $returned_product_discounts = array_column($rows, 'discount','bundle_product_id');
        $products = $this->getProductsByIds($returned_product_ids);
        // getProductsByIds sometimes does not bring the products 
        foreach ($products as $product) {
            $product_data[$product['product_id']] = $product;
            $product_data[$product['product_id']]['bundle_discount'] = $returned_product_discounts[$product['product_id']];
        }
        
        return $product_data;
    }
    public function updateProductsStatusBySellerId($sellerId , $status){
        $query = "UPDATE `" . DB_PREFIX . "product`
                              SET `status` = $status 
                              WHERE  `product_id`
                              IN (SELECT product_id FROM `" . DB_PREFIX . "ms_product`
                              WHERE `seller_id` = '".$this->db->escape($sellerId)."')";

       return $this->db->query($query);
    }

    public function is_aliexpress_product($product_id){
        if(!\Extension::isInstalled('aliexpress_dropshipping'))
            return 0 ;

        if(!$this->config->get('module_wk_dropship_status'))
            return 0;
        $sql = "SELECT product_id FROM ".DB_PREFIX."warehouse_aliexpress_product 
        Where product_id = '{$product_id}'";
        $result = $this->db->query($sql);
        return $result->num_rows;
    }

    /**
     * @param $productId
     * @return false|float|int
     */
    public function getAvgProductRating($productId){
        $query = $this->db->query("SELECT  r.rating  FROM " . DB_PREFIX . "review r LEFT JOIN " . DB_PREFIX . "product p ON (r.product_id = p.product_id) WHERE p.product_id = '" . (int)$productId . "' AND p.status = '1' AND r.status = '1'");
        $average = 0;
        if ($query->num_rows){
            $reviews =[];
            foreach ($query->rows as $key => $result) {
                if(is_array( $ratingArr = unserialize($result['rating']))){
                    if(isset($ratingArr['Quality']))
                        $reviews[]=(int)$ratingArr['Quality'];
                    else
                        $reviews[] = 0;
                }else{
                    $reviews[]=(int)$result['rating'];
                }

            }
            $average = round(array_sum($reviews) / count($reviews));

        }
        return $average;

    }

    public function getLastProductInLimitId($products_limit){
        $query = $this->db->query("select product_id FROM " . DB_PREFIX . "product where archived = 0 limit 1 offset ".($products_limit - 1) );
        return $query->row['product_id'];
    }

    public function disableTrialProducts($products_limit){
        $lastProductInLimitId =  (int) $this->getLastProductInLimitId($products_limit);
        if ($lastProductInLimitId){
            $sql = "UPDATE " . DB_PREFIX . "product SET status = '" . 0 . "'". " WHERE product_id > $lastProductInLimitId";
            $this->db->query($sql);
        }

    }
}
?>
