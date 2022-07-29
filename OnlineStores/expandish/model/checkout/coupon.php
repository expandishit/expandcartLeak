<?php
class ModelCheckoutCoupon extends Model {
    /**
     * Get all brands that should apply to the coupon
     *
     * @param string $coupon_code
     * @param string $type
     * @return array|void
     */
    public function getCouponBrands($coupon_code)
    {
        $sql = "
            SELECT 
                m.manufacturer_id
            FROM `" . DB_PREFIX . "coupon` c 
            LEFT JOIN `" . DB_PREFIX . "coupon_manufacturer` cm 
                ON (cm.coupon_id=c.coupon_id) 
            LEFT JOIN `" . DB_PREFIX . "manufacturer` m
                ON (m.manufacturer_id=cm.manufacturer_id) 
            WHERE c.code = '".$this->db->escape($coupon_code)."' AND STATUS = '1'
        ";

        $coupon_query = $this->db->query($sql);

        if ($coupon_query->num_rows) {
            return $coupon_query->rows;
        }

        return;
    }

    /**
     * Get coupon and check if can be applied according to coupon conditions on the current cart
     * @param  $code to get the coupon by code
     * @return array
     */
    public function getCoupon($code) {
		$status = true;
        $error_message = '';
        $product_data_get = array();
        $total_buy_quantity=0;
        $total_get_quantity=0;
        $coupon_query =$this->getCouponByCode($code);
        $details = $coupon_query->row['details'] ? json_decode($coupon_query->row['details'],true) : null;
        $this->language->load_json('checkout/cart');

		if ($coupon_query->num_rows) {
			if ($coupon_query->row['total'] >= $this->cart->getSubTotal()) {
                if(STORECODE == 'UPMXV084'){
                    $arr= [
                        'param1'=>$coupon_query->row['total'] ,
                        'param2'=>$this->cart->getSubTotal() ,
                    ];
                    $this->log("IF NO 1" , json_encode($arr));
                }
				$status = false;
			}

			//history
			$coupon_history_query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "coupon_history` ch WHERE ch.coupon_id = '" . (int)$coupon_query->row['coupon_id'] . "'");

			if ($coupon_query->row['uses_total'] > 0 && ($coupon_history_query->row['total'] >= $coupon_query->row['uses_total'])) {
                if(STORECODE == 'UPMXV084'){
                    $arr= [
                        'param1'=>$coupon_query->row['uses_total'] ,
                        'param2'=>$coupon_history_query->row['total'] ,
                        'param3'=>$coupon_query->row['uses_total']
                    ];
                    $this->log("IF NO 2:" , json_encode($arr));
                }
				$status = false;
			}

			if ( ($coupon_query->row['logged'] || $details["customer_option"] == 'logged' ) && !$this->customer->getId() ) {
                if(STORECODE == 'UPMXV084'){
                    $arr= [
                        'param1'=>$coupon_query->row['logged'] ,
                        'param2'=>$details["customer_option"] ,
                        'param3'=>$this->customer->getId()
                    ];
                    $this->log("IF NO 3:" , json_encode($arr));
                }
				$status = false;
                $error_message = $this->language->get('error_coupon_not_logged');
			} else if ($details["customer_option"] != 'all' && $details["customer_option"] != 'logged'){

			    if ($details["customer_option"] == 'group'){
                    $whereQuery=" coupon_id = " . $coupon_query->row['coupon_id'];
                    $sql = "SELECT cg.customer_group_id FROM coupon_group as cg WHERE $whereQuery ";
                    $query = $this->db->query($sql);
                    $customer_group_ids=array();
                    foreach ($query->rows as $item){
                        $customer_group_ids[]=$item['customer_group_id'];
                    }
                    if (!in_array($this->customer->getCustomerGroupId(),$customer_group_ids)){
                        if(STORECODE == 'UPMXV084'){
                            $arr= [
                                'param1'=>$this->customer->getCustomerGroupId(),
                                'param2'=>$customer_group_ids ,
                            ];
                            $this->log("IF NO 4:" , json_encode($arr));
                        }
                        $status = false;
                        $error_message = $this->language->get('error_coupon_not_logged');                       
                    }
                }else if ($details["customer_option"] == 'customer'){
                    $whereQuery=" coupon_id = " . $coupon_query->row['coupon_id'];
                    $sql = "SELECT cc.customer_id FROM coupon_customer as cc WHERE $whereQuery ";
                    $query = $this->db->query($sql);
                    $customer_ids=array();
                    foreach ($query->rows as $item){
                        $customer_ids[]=$item['customer_id'];
                    }
                    if (!in_array($this->customer->getId(),$customer_ids)) {
                        if(STORECODE == 'UPMXV084'){
                            $arr= [
                                'param1'=>$this->customer->getId(),
                                'param2'=>$customer_ids ,
                            ];
                            $this->log("IF NO 5:" , json_encode($arr));
                        }
                        $status = false;
                        $error_message = $this->language->get('error_coupon_not_logged');
                    }
                }
            }

			if ($this->customer->getId()) {
				$coupon_history_query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "coupon_history` ch WHERE ch.coupon_id = '" . (int)$coupon_query->row['coupon_id'] . "' AND ch.customer_id = '" . (int)$this->customer->getId() . "'");

				if ($coupon_query->row['uses_customer'] > 0 && ($coupon_history_query->row['total'] >= $coupon_query->row['uses_customer'])) {
                    if(STORECODE == 'UPMXV084'){
                        $arr= [
                            'param1'=>$coupon_query->row['uses_customer'] ,
                            'param2'=>$coupon_history_query->row['total'],
                            'param3'=>$coupon_query->row['uses_customer'],
                        ];
                        $this->log("IF NO 6:" , json_encode($arr));
                    }
					$status = false;
                    $error_message = $this->language->get('error_coupon_not_logged');
				}
			}
			// end of history

            // applied on coupon products / buy x products
            $appliedOnProducts = $this->appliedOnProductsWhereQuery($coupon_query);
            $whereQuery = $appliedOnProducts['whereQuery'];

			if($whereQuery == "all") {
                $allProductData = true;
            }else{
                $sql = "SELECT DISTINCT p.product_id,ptc.category_id,p.manufacturer_id FROM product as p 
                    LEFT JOIN product_to_category ptc on p.product_id = ptc.product_id WHERE $whereQuery ";
                $query = $this->db->query($sql);
                $allProductData = false;
            }

            $coupon_product_data = array();
            $coupon_category_data = array();
            $coupon_manufacturer_data = array();

            $notAllProductsMissed = true;
            $notAllCategoriesMissed = true;
            $notAllBrandsMissed = true;
            $cartProductsIds = [];
            $cartBrandsIds = [];
            $cartCategoriesds = [];


            if (count($appliedOnProducts['product_excluded']) > 0 || count($appliedOnProducts['product_included']) > 0 ) {
                $product_exist = false;
                foreach (array_column($this->cart->getProducts(), 'product_id') as $product_id) {

                    // ** Hint **
                    // old logic before Dec 2021 - to refuse adding the discount on the valid product
                    // new logic after Jan 2022 - to accept adding the discount on the valid product
//                   if ((in_array($product_id, $appliedOnProducts['product_excluded']) && count($appliedOnProducts['product_excluded']) > 0)){
//                       $status = false;
//                       $error_message = $this->language->get('error_coupon_not_product');
//                       break;
//                   }

                    if (in_array($product_id, $appliedOnProducts['product_included'])) {
                        $product_exist = true;
                    }
                }

                if (!$product_exist && count($appliedOnProducts['product_included']) > 0){
                    $error_message = $this->language->get('error_coupon_not_product');
                    $status = false;
                }
            }

            if (count($appliedOnProducts['manufacturer_excluded']) > 0 || count($appliedOnProducts['manufacturer_included']) > 0 ) {
                $product_exist = false;
                foreach (array_column($this->cart->getProducts(), 'manufacturer_id') as $manufacturer_id) {
                    if (in_array($manufacturer_id, $appliedOnProducts['manufacturer_excluded']) && count($appliedOnProducts['manufacturer_excluded']) > 0) {
                        $notAllBrandsMissed = false;
                        $status = false;
                        $error_message = $this->language->get('error_coupon_not_brand');
                        break;
                    }

                    if (in_array($manufacturer_id, $appliedOnProducts['manufacturer_included']))
                    {
                        $product_exist = true;
                    }
                }

                if (!$product_exist && count($appliedOnProducts['manufacturer_included']) > 0){
                    $notAllBrandsMissed = false;
                    $status = false;
                    $error_message = $this->language->get('error_coupon_not_brand');
                }
            }
            
            if (count($appliedOnProducts['final_category_excluded']) > 0 || count($appliedOnProducts['final_category_included']) > 0 ) {
                $product_exist = false;
                // getCategories
                $this->load->model('catalog/product');
                foreach (array_column($this->cart->getProducts(), 'product_id') as $product_id) {
                    $categories_ids = array_column($this->model_catalog_product->getCategories($product_id), 'category_id');
                    // if there is no intersect then break
                    if(array_intersect($categories_ids, $appliedOnProducts['final_category_included'])){
                        $product_exist = true;
                    }
                    foreach ($categories_ids as $category_id) {
                        if ((in_array($category_id, $appliedOnProducts['final_category_excluded']) && count($appliedOnProducts['final_category_excluded']) > 0)) {
                            $notAllCategoriesMissed = true;
                            $status = false;
                            $error_message = $this->language->get('error_coupon_not_category');
                            break 2;
                        }
                    }
                }

                if (!$product_exist){
                    $status = false;
                    $error_message = $this->language->get('error_coupon_not_category');
                }
            }

            if($allProductData || (!$allProductData && $query->num_rows)){
                if(!$allProductData){
                    foreach ($query->rows as $data){
                        $coupon_product_data[$data['product_id']] = true;
                    }
                    $coupon_product_data = array_keys($coupon_product_data);
                }
            }else{
                if(STORECODE == 'UPMXV084'){
                    $arr= [
                        'param1'=>$sql,
                    ];
                    $this->log("IF NO 7:" , json_encode($arr));
                }
                $status = false;
            }

            if ($allProductData || $coupon_product_data || $coupon_category_data || $coupon_manufacturer_data) {

                // check if the coupon products exists in the cart
                foreach ($this->cart->getProducts() as $product) {
                    // $allProductData is true if the coupon applied on all products
                    if($allProductData){
                        $product_data[] = $product['product_id'];
                        $total_buy_quantity += $product['quantity'];
                        continue;
                    }
					if (in_array($product['product_id'], $coupon_product_data)) {
                        $product_data[] = $product['product_id'];
                        $total_buy_quantity += $product['quantity'];
                        continue;
					}
				}
				if (!$product_data) {
                    if(STORECODE == 'UPMXV084'){
                        $arr= [
                            'param1'=>'productdatanull',
                        ];
                        $this->log("IF NO 8:" , json_encode($arr));
                    }
					$status = false;
				}
			}
            // end of: applied on coupon products

            // get free products in "buy x get y discount"
            if ($coupon_query->row['type'] == "B" && $details){
                $allGetProductData = false;
                if ($details['get_item_from']== "all"){
                    $whereQueryGet = " 1=1 ";
                    $allGetProductData = true;
                }else{
                    $whereQueryGet = $this->appliedOnGetProductsWhereQuery($coupon_query,$details);
                    if($whereQueryGet == "all") {
                        $allGetProductData = true;
                    }
                }

                // get y coupon products
                if(!$allGetProductData){
                    $sql = "SELECT DISTINCT p.product_id,ptc.category_id,p.manufacturer_id FROM product as p 
                    LEFT JOIN product_to_category ptc on p.product_id = ptc.product_id WHERE $whereQueryGet";
                    $query = $this->db->query($sql);
                }

                if (!$whereQueryGet){
                    if(STORECODE == 'UPMXV084'){
                        $arr= [
                            'param1'=>'whereQueryGetisnull',
                        ];
                        $this->log("IF NO 9:" , json_encode($arr));
                    }
                    $status = false;
                }

                //$query = $this->db->query($sql);
                $coupon_product_data_get = array();
                $coupon_category_data_get = array();
                $coupon_manufacturer_data_get = array();

                if($allGetProductData || (!$allGetProductData && $query->num_rows)){
                    if(!$allGetProductData){
                        foreach ($query->rows as $data){
                            $coupon_product_data_get[$data['product_id']] = true;
                        }
                        $coupon_product_data_get = array_keys($coupon_product_data_get);
                    }
                }else{
                    if(STORECODE == 'UPMXV084'){
                        $arr= [
                            'param1'=>$sql,
                        ];
                        $this->log("IF NO 10:" , json_encode($arr));
                    }
                    $status = false;
                }


                if ($allGetProductData || $coupon_product_data_get || $coupon_category_data_get || $coupon_manufacturer_data_get) {

                    // check if the coupon products exists in the cart
                    foreach ($this->cart->getProducts() as $product) {
                        if($allGetProductData){
                            $product_data_get[] = $product['product_id'];
                            if($allProductData){
                                $total_get_duplicated_pro_quantity += $product['quantity'];
                            }else{
                                if (!in_array($product['product_id'], $coupon_product_data) ) {
                                    $total_get_quantity += $product['quantity'];
                                }else{
                                    $total_get_duplicated_pro_quantity += $product['quantity'];
                                }
                            }

                            continue;
                        }
                        if (in_array($product['product_id'], $coupon_product_data_get) ) {
                            // check if the quantity is valid to apply buy x get y

                            $product_data_get[] = $product['product_id'];
                            if (!in_array($product['product_id'], $coupon_product_data) ) {
                                $total_get_quantity += $product['quantity'];
                            }else{
                                $total_get_duplicated_pro_quantity += $product['quantity'];
                            }
                        }
                    }
                    if (
                        !$product_data_get ||
                        ( !(($details['get_quantity']+$details['buy_quantity']) <= $total_get_quantity+ $total_buy_quantity) )
                        ||
                        !($details['get_quantity'] <= $total_get_duplicated_pro_quantity + $total_get_quantity)
                    ) {
                        if(STORECODE == 'UPMXV084'){
                            $arr= [
                                'param1'=>$product_data_get,
                            ];
                            $this->log("IF NO 11:" , json_encode($arr));
                        }
                        $status = false;
                    }
                }

                // check if the total quantity less then the product cart quantity
                if ($details['buy_quantity'] > $total_buy_quantity){
                    if(STORECODE == 'UPMXV084'){
                        $arr= [
                            'param1'=>$details['buy_quantity'] ,
                            'param2'=>$total_buy_quantity ,
                        ];
                        $this->log("IF NO 12:" , json_encode($arr));
                    }
                    $status = false;
                }

            }
            // end of get y product in "buy x get y discount"

            // check if the quantity can be applied
            $total_buy_quantity = $total_get_quantity+$total_buy_quantity;
            if ( !(($details['buy_quantity']+$details['get_quantity']) <= $total_buy_quantity ) ){
                if(STORECODE == 'UPMXV084'){
                    $arr= [
                        'param1'=>$details['buy_quantity'] ,
                        'param2'=>$details['get_quantity'] ,
                        'param3'=>$total_buy_quantity ,
                    ];
                    $this->log("IF NO 13:" , json_encode($arr));
                }
                $status=false;
            }

        } else {
            if(STORECODE == 'UPMXV084'){
                $arr= [
                    'result'=>'no coupon'
                ];
                $this->log("IF NO 14:" , json_encode($arr));
            }
			$status = false;
		}

		if ($status) {
			return array(
                'status'        => true,
				'coupon_id'     => $coupon_query->row['coupon_id'],
				'code'          => $coupon_query->row['code'],
				'name'          => $coupon_query->row['name'],
				'type'          => $coupon_query->row['type'],
				'discount'      => $coupon_query->row['discount'],
				'shipping'      => $coupon_query->row['shipping'],
                'tax_excluded'  => $coupon_query->row['tax_excluded'],
				'total'         => $coupon_query->row['total'],
				'product'       => $product_data,
				'product_get'   => $product_data_get,
				'date_start'    => $coupon_query->row['date_start'],
				'date_end'      => $coupon_query->row['date_end'],
				'uses_total'    => $coupon_query->row['uses_total'],
				'uses_customer' => $coupon_query->row['uses_customer'],
				'error_message' => $error_message,
				'maximum_limit' => $coupon_query->row['maximum_limit'],
				'minimum_to_apply' => $coupon_query->row['minimum_to_apply'],
				'date_added'    => $coupon_query->row['date_added'],
                'details'       =>$coupon_query->row['details'],
                'automatic_apply' =>$coupon_query->row['automatic_apply']
            );
		} else {
            return [
                'status' => false,
                'error_message' => $error_message ? $error_message : $this->language->get('error_coupon')
            ];
        }
	}

	public function getAutomaticCoupons(){
        $coupon_query = $this->db->query(
            "SELECT code FROM `" . DB_PREFIX . "coupon` WHERE ".
            "((IFNULL(date_start, '0000-00-00') = '0000-00-00' ".
            "OR date_start <= CURDATE()) AND (date_end = NULL OR date_end = '0000-00-00' OR date_end >= CURDATE())) AND status = '1' ".
            "AND automatic_apply=1"
        );

        return $coupon_query->rows;
    }
    private function appliedOnProductsWhereQuery($coupon_query): array
    {
        $includeQuery = array();
        $excludeQuery = array();
        $product_data = array();
        // Products
        $coupon_product_data = array();

        $coupon_product_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "coupon_product` WHERE coupon_id = '" . (int)$coupon_query->row['coupon_id'] . "'");

        // GET ALL COUPON PRODUCTS AND FILTER IT
        // INCLUED :- COUPON WILL APPLY ON.
        // EXCLUDED :- COUPON NOT VALID FOR.
        $product_included = array();
        $product_excluded = array();
        foreach ($coupon_product_query->rows as $product){
            if($product['product_excluded']){
                $product_excluded[] = $product['product_id'];
            }else{
                $product_included[] = $product['product_id'];
            }
        }

        if($product_included){
            $includeQuery[] = " p.product_id IN (".implode(',',$product_included).") ";
        }
        if($product_excluded){
            $excludeQuery[] = "( p.product_id NOT IN (".implode(',',$product_excluded).") OR p.product_id IS NULL )";
        }

        // Categories
        $coupon_category_data = array();

        $coupon_category_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "coupon_category` cc LEFT JOIN `" . DB_PREFIX . "category` c ON (cc.category_id = c.category_id) WHERE coupon_id = '" . (int)$coupon_query->row['coupon_id'] . "'");

        // GET ALL COUPON GATEGORIES AND FILTER IT
        // INCLUED :- COUPON WILL APPLY ON.
        // EXCLUDED :- COUPON NOT VALID FOR.
        $category_included = array();
        $category_excluded = array();
        foreach ($coupon_category_query->rows as $category){
            if($category['category_excluded']){
                $category_excluded[] = $category['category_id'];
            }else{
                $category_included[] = $category['category_id'];
            }
        }

        //validating array for null values
        $final_category_included= array_filter($category_included);
        $final_category_excluded= array_filter($category_excluded);

        if($final_category_included){
            $includeQuery[] = " ptc.category_id IN (".implode(',',$final_category_included).")  ";
        }
        if($final_category_excluded){
            $excludeQuery[] =  " (ptc.category_id NOT IN (".implode(',',$final_category_excluded).") OR ptc.category_id IS NULL )";
        }


        $coupon_manufacturer_data = array();

        $coupon_manufacturer_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "coupon_manufacturer` WHERE coupon_id = '" . (int)$coupon_query->row['coupon_id'] . "'");

        $manufacturer_included = array();
        $manufacturer_excluded = array();
        foreach ($coupon_manufacturer_query->rows as $manufacturer){
            if($manufacturer['manufacturer_excluded']){
                $manufacturer_excluded[] = $manufacturer['manufacturer_id'];
            }else{
                $manufacturer_included[] = $manufacturer['manufacturer_id'];
            }
        }

        // GET ALL COUPON MANUFACTURER AND FILTER IT
        // INCLUED :- COUPON WILL APPLY ON.
        // EXCLUDED :- COUPON NOT VALID FOR.
        if($manufacturer_included){
            $includeQuery[] = " p.manufacturer_id IN (".implode(',',$manufacturer_included).")";
        }
        if($manufacturer_excluded){
            $excludeQuery[] = "( p.manufacturer_id NOT IN (".implode(',',$manufacturer_excluded).") OR p.manufacturer_id IS NULL )";
        }

        $includeQuery = implode(" OR ",$includeQuery);
        $excludeQuery =  implode(" AND ",$excludeQuery);
        if ($product_included || $final_category_included || $manufacturer_included){
            $includeQuery = " (".$includeQuery.")";
        }

        if ($product_excluded || $final_category_excluded || $manufacturer_excluded){
            $excludeQuery = "(".$excludeQuery.")";
        }
        $whereQuery = "";
        if (!empty($excludeQuery) && !empty($includeQuery) ){
            $whereQuery = "$includeQuery AND $excludeQuery";
        }else if(empty($excludeQuery) && empty($includeQuery)){
            $whereQuery = "all";
        }else{
            $whereQuery = "$includeQuery $excludeQuery";
        }

        return compact('whereQuery', 'product_included', 'final_category_included', 'manufacturer_included', 'product_excluded', 'final_category_excluded', 'manufacturer_excluded');
    }

    private function appliedOnGetProductsWhereQuery($coupon_query,$details): ?string
    {
        $includeQuery = array();
        $excludeQuery = array();
        $product_data = array();
        // Products
        $coupon_product_data = array();

        $product_included = $details['coupon_product_get'];
        if(STORECODE == 'UPMXV084'){
            $arr= [
                'product_included'=>$product_included ,
            ];
            $this->log("appliedOnGetProductsWhereQuery:" , json_encode($arr));
        }
        if($product_included){
            $includeQuery[] = " p.product_id IN (".implode(',',$product_included).") ";
        }

        // Categories
        if(STORECODE == 'UPMXV084'){
            $arr= [
                'car_included'=>$details['coupon_category_get'] ,
            ];
            $this->log("appliedOnGetProductsWhereQuery:" , json_encode($arr));
        }
        $final_category_included = $details['coupon_category_get'];

        if($final_category_included){
            $includeQuery[] = " ptc.category_id IN (".implode(',',$final_category_included).")  ";
        }

        // Manufacturers

        $manufacturer_included = $details['coupon_manufacturer_get'];
        if(STORECODE == 'UPMXV084'){
            $arr= [
                'brand_included'=>$manufacturer_included ,
            ];
            $this->log("appliedOnGetProductsWhereQuery:" , json_encode($arr));
        }
        // GET ALL COUPON MANUFACTURER AND FILTER IT
        // INCLUDE :- COUPON WILL APPLY ON.
        if($manufacturer_included){
            $includeQuery[] = " p.manufacturer_id IN (".implode(',',$manufacturer_included).")";
        }

        $includeQuery = implode(" OR ",$includeQuery);
        if ($product_included || $final_category_included || $manufacturer_included){
            $includeQuery = " (".$includeQuery.")";
        }
        if(STORECODE == 'UPMXV084'){
            $arr= [
                'include_var'=>$includeQuery ,
            ];
            $this->log("appliedOnGetProductsWhereQuery:" , json_encode($arr));
        }
        $whereQuery = "";
        if (!empty($includeQuery) ){
            $whereQuery = "$includeQuery";
        }else if(empty($includeQuery)){
            $whereQuery = "all";
        }else{
            $whereQuery = "$includeQuery";
        }

        return $whereQuery;
    }

    function log($type, $contents , $fileName=false)
    {
        if (!$fileName || empty($fileName))
            $fileName='coupon.log';

        $log = new Log($fileName);
        $log->write('[' . strtoupper($type) . '] ' . $contents);
    }


    public function redeem($coupon_id, $order_id, $customer_id, $amount) {
        if(STORECODE == 'UPMXV084'){
            $query="INSERT INTO `" . DB_PREFIX . "coupon_history` SET coupon_id = '" . (int)$coupon_id . "', order_id = '" . (int)$order_id . "', customer_id = '" . (int)$customer_id . "', amount = '" . (float)$amount . "', date_added = NOW()";
            $arr= [
                'coupon'=>$coupon_id ,
                'order'=>$order_id ,
                'amount'=>$amount ,
                'query'=>$query
            ];
            $this->log("CouponRedeem:" , json_encode($arr));
        }
        $this->db->query("INSERT INTO `" . DB_PREFIX . "coupon_history` SET coupon_id = '" . (int)$coupon_id . "', order_id = '" . (int)$order_id . "', customer_id = '" . (int)$customer_id . "', amount = '" . (float)$amount . "', date_added = NOW()");
    }

    public function getCouponsByGroupId($group_id) {
        $queryString[] = 'SELECT * FROM ' . DB_PREFIX . 'coupon_group';
        $queryString[] = 'WHERE customer_group_id = "' . (int)$group_id . '"';

        $coupon_ids = $this->db->query(implode(' ', $queryString));

        foreach ($coupon_ids->rows as $coupon_data) {
            $coupon_group_data[] = $this->getCouponById($coupon_data['coupon_id']);
        }

        return $coupon_group_data;

    }

    public function getCouponById($coupon_id,$return_row=true)
    {
        $queryString = [];

        $queryString[] = "SELECT DISTINCT * FROM " . DB_PREFIX . "coupon WHERE coupon_id = '" . (int)$coupon_id . "' AND status ='1' ";
        $query = $this->db->query(implode(' ', $queryString));
        if ($return_row){
            return $query->row;
        }
        return $query;
    }

    public function getCouponByCode($code)
    {
        return $this->db->query("SELECT * FROM ".
            "`" . DB_PREFIX . "coupon` WHERE ".
            "code = '" . $this->db->escape($code) .
            "' AND ((IFNULL(date_start, '0000-00-00') = '0000-00-00' OR date_start <= CURDATE()) AND (date_end = NULL OR date_end = '0000-00-00' OR date_end >= CURDATE())) AND status = '1'"
        );
    }

    public function getProductsExcluded($coupon_id)
    {
        $query = $this->db->query("SELECT  coupon_product.product_id FROM "
            . DB_PREFIX . "coupon_product WHERE coupon_id = '" . (int)$coupon_id . "' AND  product_excluded ='1'  ");
        $result=  $query->rows;
        $product_ids = [];
        foreach ($result as $product_id){
            $product_ids[] = $product_id['product_id'];
        }
        return $product_ids;
    }

    public function getCoupons(){
        $queryString = [];

        $queryString[] = "SELECT DISTINCT * FROM " . DB_PREFIX . "coupon WHERE status ='1' "
        ."AND notification_status=1 "
        ."AND ((IFNULL(date_start, '0000-00-00') = '0000-00-00' OR date_start <= CURDATE()) AND (date_end = NULL OR date_end = '0000-00-00' OR date_end >= CURDATE()))"
        ;

        $query = $this->db->query(implode(' ', $queryString));
        return $query->rows;
    }

    public function getCouponProducts($coupon_id,$fetchAllData = false)
    {
        $coupon_product_data = $queryString = [];

        $queryString[] = "SELECT * FROM " . DB_PREFIX . "coupon_product WHERE coupon_id = '" . (int)$coupon_id . "'";

        $query = $this->db->query(implode(' ', $queryString));

        foreach ($query->rows as $result) {
            if($fetchAllData){
                $coupon_product_data[] = $result;
            }else{
                $coupon_product_data[] = $result['product_id'];
            }
        }

        return $coupon_product_data;
    }

    public function getCouponCategories($coupon_id,$fetchAllData = false)
    {
        $coupon_category_data = $queryString = [];

        $queryString[] = 'SELECT * FROM ' . DB_PREFIX . 'coupon_category';
        $queryString[] = 'WHERE coupon_id = "' . (int)$coupon_id . '"';

        $query = $this->db->query(implode(' ', $queryString));

        foreach ($query->rows as $result) {
            if($fetchAllData){
                $coupon_category_data[] = $result;
            }else{
                $coupon_category_data[] = $result['category_id'];
            }
        }

        return $coupon_category_data;
    }

    public function getCouponManufacturer($coupon_id,$fetchAllData = false)
    {
        $coupon_manufacturer_data = $queryString = [];

        $queryString[] = 'SELECT * FROM ' . DB_PREFIX . 'coupon_manufacturer';
        $queryString[] = 'WHERE coupon_id = "' . (int)$coupon_id . '"';


        $query = $this->db->query(implode(' ', $queryString));

        foreach ($query->rows as $result) {
            if($fetchAllData){
                $coupon_manufacturer_data[] = $result;
            }else{

                $coupon_manufacturer_data[] = $result['manufacturer_id'];
            }
        }
        return $coupon_manufacturer_data;
    }

    public function getActiveCouponDetails($code){
        $coupon_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "coupon` WHERE code = '" . $this->db->escape($code) . "' AND ((IFNULL(date_start, '0000-00-00') = '0000-00-00' OR date_start <= CURDATE()) AND (date_end = NULL OR date_end = '0000-00-00' OR date_end >= CURDATE())) AND status = '1'");
        if(STORECODE == 'UPMXV084'){
            $sql ="SELECT * FROM `" . DB_PREFIX . "coupon` WHERE code = '" . $this->db->escape($code) . "' AND ((IFNULL(date_start, '0000-00-00') = '0000-00-00' OR date_start <= CURDATE()) AND (date_end = NULL OR date_end = '0000-00-00' OR date_end >= CURDATE())) AND status = '1'";

            $arr= [
                'sql'=>$sql ,
                'result'=>$coupon_query->row ,
            ];
            $this->log("getActiveCouponDetails" , json_encode($arr));
        }
        return $coupon_query->row;
    }
}
?>
