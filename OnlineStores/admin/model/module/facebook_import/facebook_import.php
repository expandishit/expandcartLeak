<?php

use Facebook\Facebook;
use Facebook\Exceptions\FacebookSDKException;

/**
 * Facebook catalog import model
 * 
 * @author MohamedHassanWD <hassan.mohamed.sf@gmail.com>
 * @copyright 2020 ExpandCart
 */
class Modelmodulefacebookimportfacebookimport extends model
{

	/**
	 * Get Facebook products from temp table by Facebook product id
	 *
	 * @param string|array $product_ids Comma separated values of products
	 * @param boolean $already_imported Include the previously imported products
	 * @return array|void Product List
	 */
	public function getProductsByIds($product_ids, $exclude_imported = true, $isFacebookBusiness = false)
	{
	    $table_name = "product_facebook";

	    if($isFacebookBusiness) {
	        $table_name = "product_facebook_business";
        }
		//validate ids
		if (is_array($product_ids)) {
			$product_ids = implode(',', $product_ids);
		}

		//validate second param
		$exclude_imported = is_bool($exclude_imported) ? $exclude_imported : false;

		//Get all non existed products in the temp table
		$sql = '
			SELECT * FROM ' . $table_name . ' where facebook_product_id in (' . $product_ids . ')
		';

		if ($exclude_imported) {
			$sql .= ' AND expand_product_id is NULL';
		}

		$products_in_db = $this->db->query($sql);

		return $products_in_db->rows;
	}

    /**
     * Detatch products from facebook products by expandcart product id
     *
     * @param array $ids
     *
     * @return bool
     */
    public function detachProductByExpandProductIds($ids)
    {
        $query = 'UPDATE `%s` SET expand_product_id = NULL WHERE expand_product_id IN (%s)';

        try {
            $this->db->query(sprintf($query, 'product_facebook', implode(',', $ids)));

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Insert one product to the database
     *
     * @param null $product The product object
     * @param int $manufacturer_id Brand id
     * @return int Inserted product id
     */
	public function insertProductToDB($product = null, $manufacturer_id = 0, $isFacebookBusiness = false)
	{
		//Save image to file system
		//Insert new image

        $table_name = "product_facebook";

        if($isFacebookBusiness) {
            $table_name = "product_facebook_business";
        }

        $extension = pathinfo(substr($product->image_url, strrpos($product->image_url, '/') + 1), PATHINFO_EXTENSION);
        if (strlen($extension) > 4) {
            $extension = image_type_to_extension(getimagesize($product->image_url)[2]);
        }
        $image_name = uniqid() . '.' .$extension;
        $this->grab_image($product->image_url, 'image/data/facebook_products/' . $image_name);
        if(isset($product->custom_label_2) && !empty($product->custom_label_2)){
            $model = $product->custom_label_2;
        }else{
            $model = $product->retailer_id;
        }
        if(isset($product->custom_label_3) && !empty($product->custom_label_3)){
            $sku = $product->custom_label_3;
        }else{
            $sku =  $product->retailer_id;
        }
        $ignore_facebook_product_quntity= $this->config->get('ignore_facebook_product_quantity') ?? 0;
		//SQL to insert product

        $columns = [
              'price'
            , 'manufacturer_id'
            , 'image'
            , 'model'
            , 'sku'
            , 'stock_status_id'
            , 'date_available'
            , 'weight_class_id'
            , 'length_class_id'
            , 'sort_order'
            , 'status'
            , 'date_modified'
        ];
        $values = [
            $this->convertPriceInEnglish($product->price),
            $manufacturer_id,
            "data/facebook_products/$image_name" ,
            $model,
            $sku,
            1,
            date('Y-m-d H:i:s'),
            0,
            0,
            1,
            1,
            date('Y-m-d H:i:s')
        ];
        if(!$ignore_facebook_product_quntity){
            $columns[]='quantity';
            $values[]=(int)$product->inventory;
        }

        $columnsStr = implode(",", $columns);
        $valuesStr = "'" . implode("','", $values) . "'";
        $sql = ("INSERT INTO product ($columnsStr)  VALUES ($valuesStr)");

		$this->db->query($sql);
		$inserted_product_id = $this->db->getLastId();

		//insert product description
		$langs = $this->db->query('select language_id from language')->rows;

		foreach ($langs as $language) {
			$sql2 = '
				insert into product_description (
					product_id,
					language_id,
					name,
					description
				) values (
					' . $inserted_product_id . ',
					' . $language['language_id'] . ',
					"' . $this->db->escape($product->name) . '",
					"' . $this->db->escape($product->description) . '"
				);
			';

			$this->db->query($sql2);
		}

		//After inserting new product
		//Update it in the facebook products table
		$sql_u = '
			update ' . $table_name . ' 
				set 
				expand_product_id=' . $inserted_product_id . ' 
			where facebook_product_id=' . $product->id . ' 
			LIMIT 1;
		';
		$this->db->query($sql_u);


		//insert product to store
		$sql_x = 'insert into product_to_store values (' . $inserted_product_id . ',0);';
		$this->db->query($sql_x);

		//If there is a sale price insert it
		$sale_price = (float) preg_replace('/[^0-9\.]/', '', $product->sale_price);
		if (!empty($sale_price) && $sale_price != 0.0) {
			$sql_y = '
				insert into product_special (
					product_id,
					customer_group_id,
					price
				) values (
					' . $inserted_product_id . ',
					1,
					' . $sale_price . '
				)
			';
			$this->db->query($sql_y);
		}

		return $inserted_product_id;
	}

    /**
     * Convert numbers from Arabic or Persian numbers to English
     * @param $string
     * @return string|string[]
     */
    public function convertPriceInEnglish($string): string
    {
        $persian = array_reverse(['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹']);
        $arabic = array_reverse(['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١', '٠']);

        $num = range(0, 9);
        $convertedPersianNums = str_replace($persian, $num, $string);
        $convertedArabicNums = str_replace($arabic, $num, $convertedPersianNums);
        $removeArabic = str_replace(['ج.م.','$','ر.س.','د.إ.' , ','], '', $convertedArabicNums);
        // reformat the currency separator
        if (strpos($string, 'ر.س') !== false) {
            // sanitize int
            $removeArabic = filter_var($removeArabic, FILTER_SANITIZE_NUMBER_INT);
            return ($removeArabic/100);
        }
        return $removeArabic;
    }

	/**
	 * Insert one product to the database
	 *
	 * @param int $expand_product_id The product id to update
	 * @param object $product The product object
	 * @param int $manufacturer_id Brand id
	 * @param string $image_name Image name in the filesystem
	 * @return int updated product id
	 */
	public function updateProductInDB($expand_product_id = 0, $product = null, $manufacturer_id = 0, $image_name = null, $isFacebookBusiness = false)
	{
        $table_name = "product_facebook";

        if($isFacebookBusiness) {
            $table_name = "product_facebook_business";
        }
        //Save image to file system
        if ($product->image_url && (!str_contains(STORECODE, $product->image_url) && \Filesystem::isExists($product->image_url))) {
            //Remove Image
            \Filesystem::deleteFile('image/data/facebook_products/' . $image_name);

            //Insert new image
            $extension = pathinfo(substr($product->image_url, strrpos($product->image_url, '/') + 1), PATHINFO_EXTENSION);
            if (strlen($extension) > 4) {
                $extension = image_type_to_extension(getimagesize($product->image_url)[2]);
            }
            $image_name = uniqid() . '.' . $extension;
            $this->grab_image($product->image_url, 'image/data/facebook_products/' . $image_name);
            $image_name = $this->db->escape("data/facebook_products/" . $image_name);
        }
        if(isset($product->custom_label_2) && !empty($product->custom_label_2)){
            $model = $product->custom_label_2;
        }else{
            $model = $product->retailer_id;
        }
        if(isset($product->custom_label_3) && !empty($product->custom_label_3)){
            $sku = $product->custom_label_3;
        }else{
            $sku =  $product->retailer_id;
        }

		$this->load->model('localisation/language');

		//SQL to update product
		$sql = '
			UPDATE product
			SET price="' . $this->db->escape($this->convertPriceInEnglish($product->price)) . '",
			manufacturer_id="' . $this->db->escape($manufacturer_id) . '",';
		if (!empty($image_name)) {
            $sql .='
			image="' . $image_name . '",';
        }
        $ignore_facebook_product_quntity= $this->config->get('ignore_facebook_product_quantity') ?? 0;
		$sql .='	model="' . $this->db->escape($model) . '",
			sku="' . $this->db->escape($sku) . '",
			stock_status_id="1",
			date_available=NOW(),
			weight_class_id="0",
			length_class_id="0",
			sort_order="1",
			status="1",
			date_modified=NOW() ';

		if(!$ignore_facebook_product_quntity)
		    $sql .=',quantity="' . $this->db->escape((int) $product->inventory) . '"';

		$sql .= ' WHERE product_id = ' . (int) $this->db->escape($expand_product_id) . ' LIMIT 1';


		$this->db->query($sql);

		//update product description
		//get language for operations



		$lang_code = $this->config->get('facebook_export_language') ? $this->config->get('facebook_export_language') : $this->config->get('config_admin_language');
		$language_id = $this->model_localisation_language->getLanguageByCode($lang_code)['language_id'];
        // custom_label_1 has the product language .
        if(isset($product->custom_label_1) &&  !empty(isset($product->custom_label_1))){
            $languageInfo = explode(":" , $product->custom_label_1);
            if ($languageInfo[1] && is_numeric($languageInfo[1]))
                $language_id = $languageInfo[1];
        }
		$sql2 = '
			UPDATE product_description
			SET 
			name="' . $this->db->escape($product->name) . '",
			description="' . $this->db->escape($product->description) . '"
			WHERE product_id=' . $this->db->escape($expand_product_id) . '
			AND language_id=' . $this->db->escape($language_id) . '
			LIMIT 1
		';



		$this->db->query($sql2);

		//If there is a sale price update it
		$sale_price = $this->convertPriceInEnglish($product->sale_price);

		if (!empty($sale_price) && $sale_price != 0.0) {
			$sql_y = '
			UPDATE product_special
			SET 
			product_id=' . $expand_product_id . ',
			customer_group_id=' . $this->config->get('config_customer_group_id') . ',
			price="' . $sale_price . '"

			WHERE product_id=' . $expand_product_id . '
			LIMIT 1
			';


			$this->db->query($sql_y);
		}

		return $expand_product_id;
	}

	/**
	 * Create or update a product based on the Facebook product id
	 *
	 * @param object|null $product
	 * @return int ExpandCart product id
	 */
	public function createOrUpdate($product = [], $manufacturer_id = 0, $image_name = null, $isFacebookBusiness = false)
	{
		//Select product from intermediary table

        $table_name = "product_facebook";

        if($isFacebookBusiness) {
            $table_name = "product_facebook_business";
        }

		$facebook_product_id = (int) $this->db->escape($product->id);
		$facebook_product_sql = 'SELECT * FROM ' . DB_PREFIX . $table_name . ' WHERE facebook_product_id=' . $facebook_product_id . ' LIMIT 1';
		$facebook_product = $this->db->query($facebook_product_sql)->row;

        $check_product_sql = 'SELECT * FROM ' . DB_PREFIX . 'product WHERE product_id=' . ($facebook_product['expand_product_id'] ?? 0) . ' LIMIT 1';
        $product_exist = $this->db->query($check_product_sql)->num_rows > 0 ? true : false;

        if ($facebook_product['expand_product_id'] && $product_exist) {
			//Update product
			// dd('This will update '.$facebook_product_id.' with '.$facebook_product['expand_product_id']);
			return $this->updateProductInDB($facebook_product['expand_product_id'], $product, $manufacturer_id, $image_name, $isFacebookBusiness);
        } else {
            //Create product
            return $this->insertProductToDB($product, $manufacturer_id, $isFacebookBusiness);
        }
    }

	/**
	 * Grap an image with CURL and save it to the specified path
	 *
	 * @param string $url
	 * @param string $saveto
	 * @return void
	 */
	public function grab_image($url, $saveto): void
	{
		set_time_limit(0);
		$ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 400);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        if (str_contains('expandcart.com',  parse_url($url)['host'])) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        $raw = curl_exec($ch);
		curl_close($ch);
		if (\Filesystem::isExists($saveto)) {
			\Filesystem::deleteFile($saveto);
		}
		\Filesystem::setPath($saveto)->put($raw);
	}

	public function install()
	{
		$sql1 = "CREATE TABLE IF NOT EXISTS facebook_catalog_queue_jobs (
			`job_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			`user_id` INT(11) DEFAULT NULL,
			`catalog_id` VARCHAR(191) NOT NULL DEFAULT 0,
			`status` VARCHAR(50) DEFAULT NULL,
			`product_count` INT(11) DEFAULT 0,
			`payload` TEXT,
			`created_at` DATETIME DEFAULT NULL,
			`updated_at` DATETIME DEFAULT NULL,
			`finished_at` DATETIME DEFAULT NULL,
			`operation` VARCHAR(50) NOT NULL DEFAULT \"import\",
			PRIMARY KEY (`job_id`)
		) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

		$sql2 = "CREATE TABLE IF NOT EXISTS facebook_catalog_queue_product (
			`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			`job_id` VARCHAR(50) DEFAULT NULL,
			`facebook_product_id` VARCHAR(50) DEFAULT NULL,
			`expandcart_product_id` INT(11) DEFAULT NULL,
			`created_at` DATETIME DEFAULT NULL,
			PRIMARY KEY (`id`)
		) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

		$sql3 = "CREATE TABLE IF NOT EXISTS facebook_catalog_exports (
			`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			`user_id` INT(11) DEFAULT NULL,
			`handle` VARCHAR(500) DEFAULT NULL,
			`created_at` DATETIME DEFAULT NULL,
			PRIMARY KEY (`id`)
		) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

		$sql4 = "CREATE TABLE IF NOT EXISTS product_facebook (
			`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			`expand_product_id` INT(11) DEFAULT NULL,
			`retailer_id` VARCHAR(50) DEFAULT NULL,
			`facebook_business_id` BIGINT(20) DEFAULT NULL,
			`facebook_catalog_id` BIGINT(20) DEFAULT NULL,
			`facebook_product_id` BIGINT(20) DEFAULT NULL,
			`payload` MEDIUMTEXT,
			`created_at` TIMESTAMP NULL DEFAULT NULL,
			`updated_at` TIMESTAMP NULL DEFAULT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `facebook_product_id` (`facebook_product_id`),
			UNIQUE KEY `retailer_id` (`retailer_id`)
		) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";


		$this->db->query($sql1);
		$this->db->query($sql2);
		$this->db->query($sql3);
		$this->db->query($sql4);
	}
	
	public function uninstall()
	{
		$this->db->query("DROP TABLE IF EXISTS `facebook_catalog_queue_jobs`");
		$this->db->query("DROP TABLE IF EXISTS `facebook_catalog_queue_product`");
		$this->db->query("DROP TABLE IF EXISTS `facebook_catalog_exports`");
		$this->db->query("DROP TABLE IF EXISTS `product_facebook`");
	}
}
