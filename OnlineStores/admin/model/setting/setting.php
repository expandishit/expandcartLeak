<?php
class ModelSettingSetting extends Model {
	
	public function getSetting($group, $store_id = 0) {
		$data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `group` = '" . $this->db->escape($group) . "'");

		foreach ($query->rows as $result) {
			if (!$result['serialized']) {
				$data[$result['key']] = $result['value'];
			} else {
				$data[$result['key']] = unserialize($result['value']);
			}
		}
		
		return $data;
	}
	
	public function getSettingByKey($key, $group = null, $store_id = 0) {
		
		$sql	 = "SELECT * FROM `" . DB_PREFIX . "setting` WHERE store_id = '" . (int)$store_id . "'";
		$sql	.= " AND `key` = '" . $this->db->escape($key) . "'";		
		
		if($group){
			$sql .= " AND `group` = '" . $this->db->escape($group) . "'";	
		}
		
		
		$query 	 = $this->db->query($sql);
		
		if(!$group){
			$data = [];
			foreach ($query->rows as $result) {
				if (!$result['serialized']) {
					$data[$result['key']] = $result['value'];
				} else {
					$data[$result['key']] = unserialize($result['value']);
				}
			}
			return $data;
		}
		
		$result = $query->row ?? [];
		
		if(!empty($result)){
			
			if (!$result['serialized']) {
				return  $result['value'];
			}
				
			return unserialize($result['value']);
		}
		
		return [];
		
		
	}

	public function getSettingByValue($value, $group = null, $store_id = 0) {
		
		$sql = "SELECT * FROM `" . DB_PREFIX . "setting` WHERE store_id = '" . (int)$store_id . "'";	
		$sql .= " AND `value` = '" . $this->db->escape($value) . "' ";		
		
		if($group){
			$sql .= " AND `group` = '" . $this->db->escape($group) . "'";	
		}
		
		$query = $this->db->query($sql);
		
		if(!$group){
			return $query->rows ?? [];
		}
		
		return $query->row ?? [];
	}

	public function editSetting($group, $data, $store_id = 0) {
        
        $this->insertUpdateSetting($group, $data, $store_id);

        return;

        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `group` = '" . $this->db->escape($group) . "' AND NOT (`group` = 'config' AND `key` IN ('config_template', 'config_file_extension_allowed', 'config_file_mime_allowed'))");
        foreach ($data as $key => $value) {
            
            if (!is_array($value)) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `group` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
            } else {
                $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `group` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(serialize($value)) . "', serialized = '1'");
            }
        }
	}
    
    /**
     * Delete setting by keys
     *
     * @param array $keys
     * @return void
     */
    public function deleteByKeys(array $keys=[]): void {
        $this->db->query("DELETE FROM " . DB_PREFIX . "`setting` WHERE `key` in ('" . implode("','",$keys) . "')");

        foreach ($keys as $key) {

            $this->config->delete($key);
        }
    }

    public function insertUpdateSetting($group, $data, $store_id = 0) {
        $loop_counter = 0;
        foreach ($data as $key => $value) {
            $this->config->set($key, $value);
            $query = $this->db->query("SELECT 1 FROM " . DB_PREFIX . "setting WHERE `group` = '" . $this->db->escape($group) . "' AND `key` = '" . $this->db->escape($key) . "' AND store_id = '" . (int)$store_id . "'");
            if($query->num_rows > 0) {
                if (!is_array($value)) { 
                    $this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape($value) . "' WHERE `group` = '" . $this->db->escape($group) . "' AND `key` = '" . $this->db->escape($key) . "' AND store_id = '" . (int)$store_id . "'");
                } else {
                    $this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape(serialize($value)) . "', serialized = '1' WHERE `group` = '" . $this->db->escape($group) . "' AND `key` = '" . $this->db->escape($key) . "' AND store_id = '" . (int)$store_id . "'");
                }
            } else { 
                if (!is_array($value)) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `group` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
                } else {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `group` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(serialize($value)) . "', serialized = '1'");
                }
                //track payment or shipping if exist
                if(++$loop_counter == count($data)) {
                    $this->trackPaymentIfExist($group);
                    $this->trackShippingIfExist($group);
                }
            }
        }
    }

    public function insertUpdateSettingSecured($group, $data, $store_id = 0) {
        $loop_counter = 0;
        foreach ($data as $key => $value) {
            $this->config->set($key, $value);
            $query = $this->db->execute("SELECT 1 FROM " . DB_PREFIX . "setting WHERE `group`=? AND `key`=? AND store_id=?", [
                $this->db->escape($group), $this->db->escape($key), (int)$store_id
            ]);
            if($query->num_rows > 0) {
                if (!is_array($value)) {
                    $this->db->execute("UPDATE " . DB_PREFIX . "setting SET `value`=? WHERE `group`=? AND `key`=? AND store_id=?", [
                        $value,
                        $this->db->escape($group),
                        $this->db->escape($key),
                        (int)$store_id
                    ]);
                } else {
                    $this->db->execute("UPDATE " . DB_PREFIX . "setting SET `value`=?, serialized = '1' WHERE `group`=? AND `key`=? AND store_id=?", [
                        serialize($value),
                        $this->db->escape($group),
                        $this->db->escape($key),
                        (int)$store_id
                    ]);
                }
            } else {
                if (!is_array($value)) {
                    $this->db->execute("INSERT INTO " . DB_PREFIX . "setting SET store_id=?, `group`=?, `key`=?, `value`=?", [
                        (int)$store_id,
                        $this->db->escape($group),
                        $this->db->escape($key),
                        $value
                    ]);
                } else {
                    $this->db->execute("INSERT INTO " . DB_PREFIX . "setting SET store_id=?, `group`=?, `key`=?, `value`=?, serialized = '1'", [
                        (int)$store_id,
                        $this->db->escape($group),
                        $this->db->escape($key),
                        serialize($value)
                    ]);
                }
                //track payment or shipping if exist
                if(++$loop_counter == count($data)) {
                    $this->trackPaymentIfExist($group);
                    $this->trackShippingIfExist($group);
                }
            }
        }
    }

	public function deleteSetting($group, $store_id = 0) {

        $keysQuery = $this->db->query("SELECT * from " . DB_PREFIX . "setting where store_id = '" . (int)$store_id . "' AND `group` = '" . $this->db->escape($group) . "'");

        $resultKeys = $keysQuery->rows;

        foreach ($resultKeys as $keyName) {

           $this->config->delete($keyName["key"]);
        }

		$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `group` = '" . $this->db->escape($group) . "'");

        //update user payment  or shipping gateways if deleted is payment or shipping
        $this->load->model('extension/payment');
        $this->load->model('extension/shipping');

        if($this->model_extension_payment->selectByCode($group))
            $this->updateTrackPayment();

        elseif($this->model_extension_shipping->getShippingMethodData($group))
            $this->updateTrackShipping();

    }
    
	public function editPaymentSetting($key, $data, $storeId = 0)
    {
        $this->deletePaymentMethod($key, $storeId);

        $queryString = $fields = [];
        $queryString[] = 'INSERT INTO ' . DB_PREFIX . 'setting SET';
        $fields[] = '`store_id`=' . $storeId . '';
        $fields[] = '`group`="payment"';
        $fields[] = '`key`="' . $this->db->escape($key) . '"';
        if (is_array($data)) {
            $data = serialize($data);
            $fields[] = '`serialized`=1';
        }
        $fields[] = '`value`="' . $this->db->escape($data) . '"';
        $queryString[] = implode(',', $fields);

        $this->db->query(implode(' ', $queryString));
    }

    public function deletePaymentMethod($key, $storeId = 0)
    {
        $queryString = [];
        $queryString[] = 'DELETE FROM ' . DB_PREFIX . 'setting';
        $queryString[] = 'WHERE `store_id`=' . $storeId . '';
        $queryString[] = 'AND `group`="payment"';
        $queryString[] = 'AND `key`="' . $this->db->escape($key) . '"';
        $this->db->query(implode(' ', $queryString));

        //update user payment gateways
        $this->updateTrackPayment();
    }

	public function editSettingValue($group = '', $key = '', $value = '', $store_id = 0)
    {        
		if (!is_array($value)) {
			$this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape($value) . "' WHERE `group` = '" . $this->db->escape($group) . "' AND `key` = '" . $this->db->escape($key) . "' AND store_id = '" . (int)$store_id . "'");
		} else {
			$this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape(serialize($value)) . "' WHERE `group` = '" . $this->db->escape($group) . "' AND `key` = '" . $this->db->escape($key) . "' AND store_id = '" . (int)$store_id . "' AND serialized = '1'");
		}
	}

    public function changeTemplate($templateName, $store_id = 0) {
        $query = $this->db->query("SELECT * FROM ectemplate WHERE CodeName = '" . $templateName . "' AND NextGenTemplate=1");
        $currentStore = CURRENT_TEMPLATE;
        if($query->num_rows > 0) {
            $this->executeSQLFile('../expandish/view/theme/' . $templateName . '/eclookup.sql');

            $query = $this->db->query("SELECT * FROM ectemplate WHERE CodeName = '" . $currentStore . "' AND NextGenTemplate=0");

            if($query->num_rows > 0) {
                //capture current state of the template
                $this->captureTables($currentStore);
            }

            if($currentStore != $templateName)
                $this->applyNextGenTemplate($templateName);
            else
                $this->resetNextGenTemplate();
            return;
        }

        $templateFile = '../TemplateScripts/'.$templateName.'.sql';

        //capture current state of the template
        $this->captureTables($currentStore);

        //here the template name has changed in the DB
        $this->editSettingValue('config', 'config_template', $templateName);

        if(file_exists($templateFile))
        {
            //1. if clean.sql exists run it.
            $cleanFile = '../TemplateScripts/clean.sql';
            $this->executeSQLFile($cleanFile);
            //var_dump($data["config_template"]);
            //die;
            //2. if template file exists run it(if there is saved data for the template, it will restore the saved data instead of applying it from the beginning).
            $query = $this->db->query("SELECT COUNT(*) AS total FROM temp_setting where template_name='$templateName'");
            if($query->row["total"] > 0 && $templateName != $currentStore) {
                $this->applySavedTemplate($templateName);
            }
            else {
                $this->resetTemplateState($templateName);

                $this->executeSQLFile($templateFile);

                //3. If blog tables does not exist and template is one of the following, then run blog.sql
                $blogFile = '../TemplateScripts/blog.sql';
                $query = $this->db->query("SHOW TABLES LIKE 'pavblog_blog'");
                //if($data["config_template"] == "pav_clothes" && $query->num_rows <= 0) {
                if ($query->num_rows <= 0)
                    $this->executeSQLFile($blogFile, str_replace("pav_", "", $templateName));
                //}

                //4. Create DemoImages folder inside clientImagePath\Data\ if not exists.
                $clientDemoImagesFolder = DIR_IMAGE . "/data/DemoImages";
                if (!file_exists($clientDemoImagesFolder)) {
                    @mkdir($clientDemoImagesFolder);
                }

                //5. Copy image folder from TemplateIamges to user images DemoImages folder.
                $sourceDemoImages = "../image/TemplateImages/" . str_replace("pav_", "", $templateName);
                $destDemoImages = DIR_IMAGE . "/data/DemoImages/" . str_replace("pav_", "", $templateName);
                if (file_exists($sourceDemoImages)) {
                    $this->CreateTemplateFolder($sourceDemoImages, $destDemoImages);
                }

                //6 Replace known image paths with client image paths (replace in blog tables only if you inserted them).
                $this->FixSettingTableData(STORECODE, str_replace("pav_", "", $templateName));
                $this->Fixmegamenu(STORECODE, str_replace("pav_", "", $templateName));
                $this->Fixpavosliderlayers(STORECODE, str_replace("pav_", "", $templateName));
            }
        }

        return true;
    }

    //Template changing functions
    public function executeSQLFile($file, $TemplateName="") {
        if ($sql = file($file)) {
            $query = '';

            foreach($sql as $line) {
                $tsl = trim($line);

                if (($sql != '') && (substr($tsl, 0, 2) != "--") && (substr($tsl, 0, 1) != '#')) {
                    $query .= $line;

                    if (preg_match('/;\s*$/', $line)) {
                        //var_dump($query);
                        $query = str_replace("<{TEMPLATENAME}>", $TemplateName, $query);
                        $this->db->query($query);
                        $query = '';
                    }


                }

            }
        }
    }

    public function CreateTemplateFolder($src,$dst)
    {
        $dir = opendir($src);
        if(!file_exists($dst)){
            @mkdir($dst);
        }

        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    $this->CreateTemplateFolder($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    public function FixSettingTableData($StoreCode, $TemplateName){
        $query = $this->db->query("SELECT * FROM setting WHERE  ( `group` NOT IN ( 'autocomplete_address', 'google_talk', 'localization', 'product_option_image_pro', 'socialslides', 'zopim_live_chat', 'custom_email_templates', 'textplode', 'masspdiscoupd', 'reward_points_pro', 'popupwindow', 'signup',
                                                                                   'filter', 'special_count_down', 'multiseller', 'quickcheckout', 'd_social_login', 'mega_filter_module', 'mfilter_plus_version', 'mega_filter_settings', 'mega_filter_attribs', 'mfilter_version', 'store_locations', 'smshare', 'mobile_app', 'advanced_deals', 'product_designer', 'ecdeals', 'ecflashsale'

                                                                                   'coupon', 'credit', 'handling', 'klarna_fee', 'low_order_fee', 'reward', 'shipping', 'sub_total', 'tax', 'total', 'voucher',

                                                                                   'google_base', 'google_sitemap', 'articles_google_base', 'articles_google_sitemap',

                                                                                   'pp_standard', 'twocheckout', 'dixipay', 'faturah', 'gate2play', 'innovatepayments', 'paytabs', 'skrill', 'okpay', 'knet', 'my_fatoorah',
                                                                                   'cashu', 'onecard', 'bank_transfer', 'cheque', 'cod', 'ccavenuepay',

                                                                                   'aramex', 'ups', 'fedex', 'weight', 'flat', 'item', 'free', 'pickup', 'category_product_based', 

                                                                                   'expand_seo', 'related_products', 'rental_products', 'dedicated_domains', 'auto_meta_tags', 'network_marketing', 'custom_fees_for_payment_method', 

                                                                                   'pp_plus', 'sendstrap', 'sofort', 'fastpaycash', 'smartlook', 'trustrol'
                                                                                   ) )
        AND NOT ( `group` = 'config'
                  AND `key` IN ( 'config_error_log', 'config_error_display', 'config_compression', 'config_encryption', 'config_password', 'config_maintenance',
                                 'config_seo_url', 'config_file_extension_allowed', 'config_file_mime_allowed', 'config_secure', 'config_shared',
                                 'config_robots', 'config_fraud_status_id', 'config_fraud_score', 'config_fraud_key', 'config_fraud_detection',
                                 'config_alert_emails', 'config_account_mail', 'config_alert_mail', 'config_smtp_timeout', 'config_smtp_port',
                                 'config_smtp_password', 'config_smtp_username', 'config_smtp_host', 'config_mail_parameter', 'config_mail_protocol',
                                 'config_ftp_status', 'config_ftp_root', 'config_ftp_username', 'config_ftp_password', 'config_error_filename',
                                 'config_google_analytics', 'config_ftp_port', 'config_ftp_host', 'config_icon', 'config_logo', 'config_return_status_id',
                                 'config_return_id', 'config_commission', 'config_affiliate_id', 'config_stock_status_id', 'config_stock_checkout',
                                 'config_stock_warning', 'config_stock_display', 'config_complete_status_id', 'config_order_status_id',
                                 'config_order_shipped_status_id', 'config_order_cod_status_id', 'config_invoice_prefix', 'config_order_popup',
                                 'config_checkout_id', 'config_order_edit', 'config_guest_checkout', 'config_cart_weight', 'config_account_id',
                                 'config_customer_price', 'config_customer_group_display', 'config_customer_group_id', 'config_customer_online',
                                 'config_tax_customer', 'config_tax_default', 'config_vat', 'config_tax', 'config_voucher_max', 'config_voucher_min',
                                 'config_download', 'config_review_status', 'config_product_count', 'config_admin_limit', 'config_catalog_limit',
                                 'config_weight_class_id', 'config_length_class_id', 'config_currency_auto', 'config_currency', 'config_admin_language',
                                 'config_language', 'config_zone_id', 'config_country_id', 'config_layout_id', 'config_template', 'config_meta_description',
                                 'config_fax', 'config_title', 'config_telephone', 'config_email', 'config_address', 'config_owner', 'config_name', 'config_url', 'config_webhook_url' )
                )");

        foreach ($query->rows as $result) {
            if ($result['serialized'] && $TemplateName != 'gazal') {
                $objectun = unserialize($result["value"]);
                $this->multi_array_search("image/data/", $objectun, 'image/' . $StoreCode . '/data/');
                $this->multi_array_search("data/", $objectun, 'data/DemoImages/' . $TemplateName . '/');
                $objectser = serialize($objectun);
                if($objectser != $result["value"])
                    $this->editSettingValue($result['group'], $result['key'], $objectun);
            }
        }
    }

    public function Fixmegamenu($StoreCode, $TemplateName){
        $query = $this->db->query("SHOW TABLES LIKE 'megamenu_widgets'");
        if($query->num_rows <= 0)
            return;
        $query = $this->db->query("SELECT * FROM megamenu_widgets");
        foreach ($query->rows as $result) {
            $objectun = unserialize($result["params"]);
            $this->multi_array_search("image/data/", $objectun, 'image/' . $StoreCode . '/data/');
            $this->multi_array_search("data/", $objectun, 'data/DemoImages/' . $TemplateName . '/');
            $objectser = serialize($objectun);
            if ($objectser != $result["params"])
                $this->db->query("UPDATE megamenu_widgets SET `params`='" . $this->db->escape($objectser) . "' WHERE `id`='" . $result['id'] . "'");
        }
    }

    public function Fixpavosliderlayers($StoreCode, $TemplateName){
        $query = $this->db->query("SHOW TABLES LIKE 'pavosliderlayers'");
        if($query->num_rows <= 0)
            return;
        $query = $this->db->query("SELECT * FROM pavosliderlayers");
        foreach ($query->rows as $result) {
            $objectun = unserialize($result["params"]);
            $this->multi_array_search("image/data/", $objectun, 'image/' . $StoreCode . '/data/');
            $this->multi_array_search("data/", $objectun, 'data/DemoImages/' . $TemplateName . '/');
            $objectserparams = serialize($objectun);

            $objectun = unserialize($result["layersparams"]);
            $this->multi_array_search("image/data/", $objectun, 'image/' . $StoreCode . '/data/');
            $this->multi_array_search("data/", $objectun, 'data/DemoImages/' . $TemplateName . '/');
            $objectserlayersparams = serialize($objectun);

            $newImagePath = str_replace("image/data/" ,'image/' . $StoreCode . '/data/', $result["image"]);
            $newImagePath = str_replace("data/" ,'data/DemoImages/' . $TemplateName . '/' ,$newImagePath);
            if ($objectser != $result["params"])
                $this->db->query("UPDATE pavosliderlayers SET `layersparams`='" . $this->db->escape($objectserlayersparams) . "', `params`='" . $this->db->escape($objectserparams) . "', `image`='" . $newImagePath . "' WHERE `id`='" . $result['id'] . "'");
        }
    }

    public function multi_array_search($search_for, &$search_in, $repalce_with) {
        foreach ($search_in as &$element) {
            if(is_array($element)){
                $result = $this->multi_array_search($search_for, $element, $repalce_with);
            }
            else if ( ($element === $search_for || strpos($element, $search_for) !== false) ){
                $element = str_replace($search_for, $repalce_with, $element); ;
            }
        }
    }

    public function getGuideValueCount($guidename) {

        $query = $this->db->query("SELECT count(*) as count FROM " . DB_PREFIX . "`guide` WHERE `GUIDENAME` = '" .
            $this->db->escape($guidename) . "'" . " AND VALUE = 1 AND `KEY` IN ('ADD_PRODUCTS','CUST_DESIGN','ADD_DOMAIN') "

        );

        return $query->row['count'];
    }

    public function getGuideValue($guidename) {
        $data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "`guide` WHERE `GUIDENAME` = '" . $this->db->escape($guidename) . "'");

        foreach ($query->rows as $result) {
                $data[$result['KEY']] = $result['VALUE'];
        }

        return $data;
    }

    public function getBLogs() {

        $query = $this->ecusersdb->query("SELECT * FROM "
            . DB_PREFIX . "`st_admin_blog` b left join "
            . DB_PREFIX . "`st_admin_blog_description` bd on (b.id = bd.blog_id) WHERE `language_code` = '" .
            $this->config->get('config_admin_language') . "'");

        return $query->rows;
    }

    public function getPhoneCode($country_id) {

        $query = $this->db->query("SELECT phonecode FROM "
            . DB_PREFIX . "`country`  WHERE `country_id` = '" .
            $country_id . "'");
        return $query->row['phonecode'];
    }
    public function getOffers() {

        $query = $this->ecusersdb->query("SELECT * FROM "
            . DB_PREFIX . "`st_offers` order by created_at desc limit 1 "
        );

        return $query->row;
    }

    public function editGuideByKey($key,$value){
        $sql = "UPDATE " . DB_PREFIX . "`guide` SET `VALUE` = '" . $this->db->escape($value) .
            "' WHERE  `KEY` = '" . $this->db->escape($key) . "'";
        $this->db->query($sql);
    }

    public function editGuideValue($guidename, $key, $value) {
        $query = $this->db->query("SELECT 1 FROM " . DB_PREFIX . "`guide` WHERE `GUIDENAME` = '" . $this->db->escape($guidename) . "' AND `KEY` = '" . $this->db->escape($key) . "'");

        if($query->num_rows > 0) {
            $this->db->query("UPDATE " . DB_PREFIX . "`guide` SET `VALUE` = '" . $this->db->escape($value) . "' WHERE `GUIDENAME` = '" . $this->db->escape($guidename) . "' AND `KEY` = '" . $this->db->escape($key) . "'");
        } else {
            $this->db->query("INSERT INTO " . DB_PREFIX . "`guide` SET `VALUE` = '" . $this->db->escape($value) . "', `GUIDENAME` = '" . $this->db->escape($guidename) . "', `KEY` = '" . $this->db->escape($key) . "'");
        }

        return true;
    }

    public function captureTables($templatename) {


        $tablename = 'setting';
        $query = $this->db->query("SHOW TABLES LIKE '$tablename'");
        if($query->num_rows > 0) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `temp_setting` (
                              `uniqueid` int(11) NOT NULL AUTO_INCREMENT,
                              `template_name` varchar(64) NOT NULL,
                              `store_id` int(11) NOT NULL DEFAULT '0',
                              `group` varchar(32) NOT NULL,
                              `key` varchar(64) NOT NULL,
                              `value` text NOT NULL,
                              `serialized` tinyint(1) NOT NULL,
                              PRIMARY KEY (`uniqueid`)
                              ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=32697 ;");
            $this->db->query("DELETE FROM temp_$tablename WHERE `template_name`='$templatename'");
            $this->db->query("INSERT INTO `temp_setting` (`template_name`, `store_id`, `group`, `key`, `value`, `serialized`)
                              SELECT '$templatename', `store_id`, `group`, `key`, `value`, `serialized`
                              FROM `setting`
                              WHERE   ( `group` NOT IN ( 'autocomplete_address', 'google_talk', 'localization', 'product_option_image_pro', 'socialslides', 'zopim_live_chat', 'quickcheckout', 'd_social_login', 'custom_email_templates', 'textplode', 'masspdiscoupd', 'reward_points_pro', 'popupwindow', 'signup',
                                                       'filter', 'special_count_down', 'multiseller', 'mega_filter_module', 'mfilter_plus_version', 'mega_filter_settings', 'mega_filter_attribs', 'mfilter_version', 'store_locations', 'smshare', 'mobile_app', 'advanced_deals', 'product_designer', 'ecdeals', 'ecflashsale',

                                                       'coupon', 'credit', 'handling', 'klarna_fee', 'low_order_fee', 'reward', 'shipping', 'sub_total', 'tax', 'total', 'voucher',

                                                       'google_base', 'google_sitemap', 'articles_google_base', 'articles_google_sitemap',

                                                       'pp_standard', 'twocheckout', 'dixipay', 'faturah', 'gate2play', 'innovatepayments', 'paytabs', 'skrill', 'okpay', 'knet', 'my_fatoorah',
                                                       'cashu', 'onecard', 'bank_transfer', 'cheque', 'cod', 'ccavenuepay',

                                                       'aramex', 'ups', 'fedex', 'weight', 'flat', 'item', 'free', 'pickup', 'category_product_based',

                                                       'expand_seo', 'related_products', 'rental_products', 'dedicated_domains', 'auto_meta_tags', 'network_marketing', 'custom_fees_for_payment_method',

                                                       'pp_plus', 'sendstrap', 'sofort', 'fastpaycash', 'smartlook', 'trustrol'
                                                       ) )
                                    AND NOT ( `group` = 'config'
                                              AND `key` IN ( 'config_error_log', 'config_error_display', 'config_compression', 'config_encryption', 'config_password', 'config_maintenance',
                                                             'config_seo_url', 'config_file_extension_allowed', 'config_file_mime_allowed', 'config_secure', 'config_shared',
                                                             'config_robots', 'config_fraud_status_id', 'config_fraud_score', 'config_fraud_key', 'config_fraud_detection',
                                                             'config_alert_emails', 'config_account_mail', 'config_alert_mail', 'config_smtp_timeout', 'config_smtp_port',
                                                             'config_smtp_password', 'config_smtp_username', 'config_smtp_host', 'config_mail_parameter', 'config_mail_protocol',
                                                             'config_ftp_status', 'config_ftp_root', 'config_ftp_username', 'config_ftp_password', 'config_error_filename',
                                                             'config_google_analytics', 'config_ftp_port', 'config_ftp_host', 'config_icon', 'config_logo', 'config_return_status_id',
                                                             'config_return_id', 'config_commission', 'config_affiliate_id', 'config_stock_status_id', 'config_stock_checkout',
                                                             'config_stock_warning', 'config_stock_display', 'config_complete_status_id', 'config_order_status_id',
                                                             'config_order_shipped_status_id', 'config_order_cod_status_id', 'config_invoice_prefix', 'config_order_popup',
                                                             'config_checkout_id', 'config_order_edit', 'config_guest_checkout', 'config_cart_weight', 'config_account_id',
                                                             'config_customer_price', 'config_customer_group_display', 'config_customer_group_id', 'config_customer_online',
                                                             'config_tax_customer', 'config_tax_default', 'config_vat', 'config_tax', 'config_voucher_max', 'config_voucher_min',
                                                             'config_download', 'config_review_status', 'config_product_count', 'config_admin_limit', 'config_catalog_limit',
                                                             'config_weight_class_id', 'config_length_class_id', 'config_currency_auto', 'config_currency', 'config_admin_language',
                                                             'config_language', 'config_zone_id', 'config_country_id', 'config_layout_id', 'config_template', 'config_meta_description',
                                                             'config_fax', 'config_title', 'config_telephone', 'config_email', 'config_address', 'config_owner', 'config_name', 'config_url', 'config_webhook_url' )
                                            );");
        }

        $tablename = 'extension';
        $query = $this->db->query("SHOW TABLES LIKE '$tablename'");
        if($query->num_rows > 0) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `temp_extension` (
                              `uniqueid` int(11) NOT NULL AUTO_INCREMENT,
                              `template_name` varchar(64) NOT NULL,
                              `type` varchar(32) NOT NULL,
                              `code` varchar(32) NOT NULL,
                              PRIMARY KEY (`uniqueid`)
                              ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=700 ;");
            $this->db->query("DELETE FROM temp_$tablename WHERE template_name='$templatename'");
            $this->db->query("INSERT INTO `temp_extension` (`template_name`, `type`, `code`)
                              SELECT '$templatename', `type`, `code`
                              FROM `extension`
                              WHERE   `type` = 'module'
                                      AND `code` NOT IN ( 'autocomplete_address', 'google_talk', 'localization', 'product_option_image_pro', 'socialslides', 'zopim_live_chat', 'mega_filter', 'quickcheckout', 'd_social_login', 'custom_email_templates', 'textplode', 'masspdiscoupd', 'reward_points_pro', 'popupwindow', 'signup',
                                                          'filter', 'special_count_down', 'multiseller', 'social', 'total', 'feed', 'payment', 'shipping', 'store_locations', 'smshare', 'mobile_app', 'advanced_deals', 'product_designer', 'ecdeals', 'ecflashsale',

                                                          'expand_seo', 'related_products', 'rental_products', 'dedicated_domains', 'auto_meta_tags', 'network_marketing', 'custom_fees_for_payment_method', 'sendstrap', 'smartlook', 'trustrol'
                                                          );");
        }

        $tablename = 'pavoslidergroups';
        $query = $this->db->query("SHOW TABLES LIKE '$tablename'");
        if($query->num_rows > 0) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `temp_pavoslidergroups` (
                              `uniqueid` int(11) NOT NULL AUTO_INCREMENT,
                              `template_name` varchar(64) NOT NULL,
                              `id` int(11) NOT NULL,
                              `title` varchar(255) NOT NULL,
                              `params` text NOT NULL,
                              PRIMARY KEY (`uniqueid`)
                              ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;");
            $this->db->query("DELETE FROM temp_$tablename WHERE template_name='$templatename'");
            $this->db->query("INSERT INTO `temp_pavoslidergroups` (`template_name`, `id`, `title`, `params`)
                              SELECT '$templatename', `id`, `title`, `params`
                              FROM `pavoslidergroups`;");
        }

        $tablename = 'pavosliderlayers';
        $query = $this->db->query("SHOW TABLES LIKE '$tablename'");
        if($query->num_rows > 0) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `temp_pavosliderlayers` (
                              `uniqueid` int(11) NOT NULL AUTO_INCREMENT,
                              `template_name` varchar(64) NOT NULL,
                              `id` int(11) NOT NULL,
                              `title` varchar(255) NOT NULL,
                              `parent_id` int(11) NOT NULL,
                              `group_id` int(11) NOT NULL,
                              `params` text NOT NULL,
                              `layersparams` text NOT NULL,
                              `image` varchar(255) NOT NULL,
                              `status` tinyint(1) NOT NULL,
                              `position` int(11) NOT NULL,
                              PRIMARY KEY (`uniqueid`),
                              KEY `uniqueid` (`uniqueid`)
                              ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;");
            $this->db->query("DELETE FROM temp_$tablename WHERE template_name='$templatename'");
            $this->db->query("INSERT INTO `temp_pavosliderlayers` (`template_name`, `id`, `title`, `parent_id`, `group_id`, `params`, `layersparams`, `image`, `status`, `position`)
                              SELECT '$templatename', `id`, `title`, `parent_id`, `group_id`, `params`, `layersparams`, `image`, `status`, `position`
                              FROM `pavosliderlayers`;");
        }

    }

    public function applySavedTemplate($templatename) {
        $tablename = 'setting';
        $query = $this->db->query("SHOW TABLES LIKE '$tablename'");
        if($query->num_rows > 0) {
            $this->db->query("INSERT INTO `setting` (`store_id`, `group`, `key`, `value`, `serialized`)
                              SELECT `store_id`, `group`, `key`, `value`, `serialized`
                              FROM `temp_setting`
                              WHERE template_name='$templatename';");
        }

        $tablename = 'extension';
        $query = $this->db->query("SHOW TABLES LIKE '$tablename'");
        if($query->num_rows > 0) {
            $this->db->query("INSERT INTO `extension` (`type`, `code`)
                              SELECT `type`, `code`
                              FROM `temp_extension`
                              WHERE template_name='$templatename';");

        }

        $this->db->query("CREATE TABLE IF NOT EXISTS `pavoslidergroups` (
                                  `id` int(11) NOT NULL AUTO_INCREMENT,
                                  `title` varchar(255) NOT NULL,
                                  `params` text NOT NULL,
                                  PRIMARY KEY (`id`)
                                  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;");

        $tablename = 'pavoslidergroups';
        $query1 = $this->db->query("SHOW TABLES LIKE 'temp_$tablename'");
        if($query1->num_rows > 0) {
            $savedRowsCount = $this->db->query("SELECT COUNT(*) AS total  FROM temp_$tablename WHERE template_name = '$templatename'");
            if($savedRowsCount->row['total'] > 0) {
                $this->db->query("INSERT INTO `pavoslidergroups` (`id`, `title`, `params`)
                                  SELECT `id`, `title`, `params`
                                  FROM `temp_pavoslidergroups`
                                  WHERE template_name='$templatename';");
            }
        }

        $this->db->query("CREATE TABLE IF NOT EXISTS `pavosliderlayers` (
                                  `id` int(11) NOT NULL AUTO_INCREMENT,
                                  `title` varchar(255) NOT NULL,
                                  `parent_id` int(11) NOT NULL,
                                  `group_id` int(11) NOT NULL,
                                  `params` text NOT NULL,
                                  `layersparams` text NOT NULL,
                                  `image` varchar(255) NOT NULL,
                                  `status` tinyint(1) NOT NULL,
                                  `position` int(11) NOT NULL,
                                  PRIMARY KEY (`id`),
                                  KEY `id` (`id`)
                                  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;");

        $tablename = 'pavosliderlayers';
        $query1 = $this->db->query("SHOW TABLES LIKE 'temp_$tablename'");
        if($query1->num_rows > 0) {
            $savedRowsCount = $this->db->query("SELECT COUNT(*) AS total  FROM temp_$tablename WHERE template_name = '$templatename'");
            if($savedRowsCount->row['total'] > 0) {
                $this->db->query("INSERT INTO `pavosliderlayers` (`id`, `title`, `parent_id`, `group_id`, `params`, `layersparams`, `image`, `status`, `position`)
                                  SELECT `id`, `title`, `parent_id`, `group_id`, `params`, `layersparams`, `image`, `status`, `position`
                                  FROM `temp_pavosliderlayers`
                                  WHERE template_name='$templatename';");
            }
        }
    }

    public function resetTemplateState($templatename) {
        $tablename = 'setting';
        $query = $this->db->query("SHOW TABLES LIKE '$tablename'");
        if($query->num_rows > 0) {
            $this->db->query("DELETE FROM temp_$tablename WHERE `template_name`='$templatename'");
        }

        $tablename = 'extension';
        $query = $this->db->query("SHOW TABLES LIKE '$tablename'");
        if($query->num_rows > 0) {
            $this->db->query("DELETE FROM temp_$tablename WHERE `template_name`='$templatename'");
        }

        $tablename = 'pavoslidergroups';
        $query = $this->db->query("SHOW TABLES LIKE '$tablename'");
        if($query->num_rows > 0) {
            $this->db->query("DELETE FROM temp_$tablename WHERE `template_name`='$templatename'");
        }

        $tablename = 'pavosliderlayers';
        $query = $this->db->query("SHOW TABLES LIKE '$tablename'");
        if($query->num_rows > 0) {
            $this->db->query("DELETE FROM temp_$tablename WHERE `template_name`='$templatename'");
        }
    }


    /*next gen templates changes*/
    public function getAllTemplates(){
        $templates = array();
        $templates['nextgen'] = array();
        $templates['legacy'] = array();

        $query = $this->db->query("SELECT ectemplate.*,
                                    ectemplatedesc.`Name`,
                                    ectemplatedesc.Description,
                                    ectemplatedesc.Image,
                                    ectemplatedesc.Demourl
                                    FROM ectemplate
                                    JOIN ectemplatedesc ON ectemplate.id=ectemplatedesc.TemplateId
                                    WHERE ectemplatedesc.Lang = '" . $this->language->get('code') . "'");

        foreach ($query->rows as $result) {
            if ($result['NextGenTemplate'] == 1) {
                $templates['nextgen'][] = $result;
            } else if ($result == CURRENT_TEMPLATE) {
                $templates['legacy'][] = $result;
            }
        }

        return $templates;
    }

    public function applyNextGenTemplate($templateCodeName) {
        $this->load->model('teditor/teditor');

        $query = $this->db->query("SELECT COUNT(*) AS SectionCount FROM ecsection
                                   INNER JOIN ecregion ON ecsection.RegionId = ecregion.id
                                   INNER JOIN ecpage ON ecregion.PageId = ecpage.id
                                   INNER JOIN ectemplate ON ectemplate.id = ecpage.TemplateId
                                   WHERE ecsection.Type = 'live'
                                   AND ectemplate.CodeName = '" . $templateCodeName . "'");
        //no draft version exist
        if($query->row['SectionCount'] == 0) {
            $this->applyTemplateJsonFile($templateCodeName);
        }

        $this->editSettingValue('config', 'config_template', $templateCodeName);
    }

    public function resetNextGenTemplate() {
        $this->applyTemplateJsonFile(CURRENT_TEMPLATE);

        $this->editSettingValue('config', 'config_template', CURRENT_TEMPLATE);
    }

    private function applyTemplateJsonFile($templateCodeName) {
        $jsonFileContent = file_get_contents("../expandish/view/theme/$templateCodeName/$templateCodeName.json", true);
        $template = json_decode($jsonFileContent);

        $this->db->query("DELETE ecpage
                          FROM ecpage
                          INNER JOIN ectemplate ON ectemplate.id = ecpage.TemplateId
                          WHERE ectemplate.CodeName = '" . $templateCodeName . "'");
        $this->db->query("DELETE ecobjectfield
                          FROM ecobjectfield
                          LEFT JOIN eccollection ON (ecobjectfield.ObjectId = eccollection.id AND ecobjectfield.ObjectType='ECCOLLECTION')
                          LEFT JOIN ecsection ON (ecobjectfield.ObjectId = ecsection.id AND ecobjectfield.ObjectType='ECSECTION')
                          WHERE eccollection.id IS NULL AND ecsection.id IS NULL;");

        $templateId = 0;
        $query = $this->db->query("SELECT id FROM ectemplate WHERE ectemplate.CodeName = '" . $templateCodeName . "'");
        $templateId=$query->row['id'];

        foreach ($template->Pages as $page) {
            $this->db->query("INSERT INTO `ecpage` (CodeName, Route, TemplateId) VALUES ('$page->CodeName', '$page->Route', '$templateId')");

            $pageId = $this->db->getLastId();

            $this->db->query("INSERT INTO `ecpagedesc` (Name, Description, Lang, PageId) VALUES ('$page->NameEN', '$page->DescEN', 'en', '$pageId')");
            $this->db->query("INSERT INTO `ecpagedesc` (Name, Description, Lang, PageId) VALUES ('$page->NameAR', '$page->DescAR', 'ar', '$pageId')");

            foreach ($page->Regions as $region) {
                $this->db->query("INSERT INTO `ecregion` (CodeName, RowOrder, ColOrder, ColWidth, PageId) VALUES ('$region->CodeName', '$region->RowOrder', '$region->ColOrder', '$region->ColWidth', '$pageId')");

                $regionId = $this->db->getLastId();

                $this->db->query("INSERT INTO `ecregiondesc` (Name, Description, Lang, RegionId) VALUES ('$region->NameEN', '$region->DescEN', 'en', '$regionId')");
                $this->db->query("INSERT INTO `ecregiondesc` (Name, Description, Lang, RegionId) VALUES ('$region->NameAR', '$region->DescAR', 'ar', '$regionId')");

                $sectionOrder = 0;
                $sections = array();
                if($region->Sections) {
                    $sections = $region->Sections;
                }
                foreach (glob("../expandish/view/theme/$templateCodeName/template/section/" . $region->CodeName . "/*.json") as $filename) {
                    $jsonSectionContent = file_get_contents($filename, true);
                    $sectionDecoded = json_decode($jsonSectionContent);
                    if($sectionDecoded) {
                        $sections[] = $sectionDecoded;
                    }
                }

                foreach ($sections as $section) {
                    $this->db->query("INSERT INTO `ecsection` (CodeName, Name, Type, State, IsCollection, SortOrder, RegionId) VALUES ('$section->CodeName', '$section->Name', '$section->Type', '$section->State', '$section->IsCollection', '$sectionOrder', '$regionId')");

                    $sectionId = $this->db->getLastId();

                    $this->db->query("INSERT INTO `ecsectiondesc` (Name, Description, Image, Thumbnail, CollectionName, CollectionItemName, CollectionButtonName, Lang, SectionId) VALUES ('$section->NameEN', '$section->DescEN', '$section->ImageEN', '$section->ThumbnailEN', '$section->CollectionNameEN', '$section->CollectionItemNameEN', '$section->CollectionButtonNameEN', 'en', '$sectionId')");
                    $this->db->query("INSERT INTO `ecsectiondesc` (Name, Description, Image, Thumbnail, CollectionName, CollectionItemName, CollectionButtonName, Lang, SectionId) VALUES ('$section->NameAR', '$section->DescAR', '$section->ImageAR', '$section->ThumbnailAR', '$section->CollectionNameAR', '$section->CollectionItemNameAR', '$section->CollectionButtonNameAR', 'ar', '$sectionId')");

                    $fieldOrder = 0;

                    if (isset($section->Fields))
                        foreach($section->Fields as $field) {
                            $this->db->query("INSERT INTO `ecobjectfield` (CodeName, Type, SortOrder, LookUpKey, IsMultiLang, ObjectId, ObjectType) VALUES ('$field->CodeName', '$field->Type', '$fieldOrder', '$field->LookUpKey', '$field->IsMultiLang', '$sectionId', 'ECSECTION')");

                            $fieldId = $this->db->getLastId();

                            $this->db->query("INSERT INTO `ecobjectfielddesc` (Name, Description, Lang, ObjectFieldId) VALUES ('$field->NameEN', '$field->DescEN', 'en', '$fieldId')");
                            $this->db->query("INSERT INTO `ecobjectfielddesc` (Name, Description, Lang, ObjectFieldId) VALUES ('$field->NameAR', '$field->DescAR', 'ar', '$fieldId')");

                            $this->db->query("INSERT INTO `ecobjectfieldval` (Value, Lang, ObjectFieldId) VALUES ('$field->ValueEN', 'en', '$fieldId')");
                            $this->db->query("INSERT INTO `ecobjectfieldval` (Value, Lang, ObjectFieldId) VALUES ('$field->ValueAR', 'ar', '$fieldId')");

                            $fieldOrder++;
                        }

                    $collectionOrder = 0;

                    if (isset($section->Collections))
                        foreach($section->Collections as $collection) {
                            $this->db->query("INSERT INTO `eccollection` (Name, Thumbnail, IsDefault, SortOrder, SectionId) VALUES ('$collection->Name', '$collection->Thumbnail', '$collection->IsDefault', '$collectionOrder', '$sectionId')");

                            $collectionId = $this->db->getLastId();

                            $collectionFieldOrder = 0;

                            foreach($collection->Fields as $collectionField) {
                                $this->db->query("INSERT INTO `ecobjectfield` (CodeName, Type, SortOrder, LookUpKey, IsMultiLang, ObjectId, ObjectType) VALUES ('$collectionField->CodeName', '$collectionField->Type', '$collectionFieldOrder', '$collectionField->LookUpKey', '$collectionField->IsMultiLang', '$collectionId', 'ECCOLLECTION')");

                                $collectionFieldId = $this->db->getLastId();

                                $this->db->query("INSERT INTO `ecobjectfielddesc` (Name, Description, Lang, ObjectFieldId) VALUES ('$collectionField->NameEN', '$collectionField->DescEN', 'en', '$collectionFieldId')");
                                $this->db->query("INSERT INTO `ecobjectfielddesc` (Name, Description, Lang, ObjectFieldId) VALUES ('$collectionField->NameAR', '$collectionField->DescAR', 'ar', '$collectionFieldId')");

                                $this->db->query("INSERT INTO `ecobjectfieldval` (Value, Lang, ObjectFieldId) VALUES ('$collectionField->ValueEN', 'en', '$collectionFieldId')");
                                $this->db->query("INSERT INTO `ecobjectfieldval` (Value, Lang, ObjectFieldId) VALUES ('$collectionField->ValueAR', 'ar', '$collectionFieldId')");

                                $collectionFieldOrder++;
                            }

                            $collectionOrder++;
                        }

                    $sectionOrder++;
                }
            }
        }

        if($this->isCustomTemplate($template->CodeName)){
            $sourceDemoImages = BASE_STORE_DIR . 'customtemplates/' . $template->CodeName . "/demo-images";
            $destDemoImages = "image/data/custom-" .  $template['CodeName'];
        }else{
            $sourceDemoImages = ONLINE_STORES_PATH . "OnlineStores/image/TemplateImages/" . $templateCodeName;
            $destDemoImages = "image/data/" . $templateCodeName;
        }
        

        $this->uploadFolder($sourceDemoImages, $destDemoImages);

        //update new template fields values to support the new language
        $query = $this->db->query("SELECT code FROM `language`");
        $langs = $query->rows;
        foreach ($langs as $lang) {
            if ($lang['code'] != "en") {
                $this->db->query("INSERT INTO ecobjectfieldval (`Value`, Lang, ObjectFieldId)
                                  SELECT DISTINCT ecobjectfieldval.`Value`, '" . $lang['code'] . "', ecobjectfieldval.ObjectFieldId
                                  FROM ecobjectfieldval
                                  LEFT JOIN ecobjectfieldval destval ON ecobjectfieldval.ObjectFieldId = destval.ObjectFieldId AND destval.Lang = '" . $lang['code'] . "'
                                  WHERE destval.id IS NULL AND ecobjectfieldval.Lang = 'en'");
            }
        }
    }

    /**
     * Upload folder contents to a destination path
     * this is used to upload a local files to filesystem adapter
     *
     * @param string $source
     * @param string $destination
     *
     * @return void
     */
    protected function uploadFolder($source, $destination)
    {
        $directory = new \RecursiveDirectoryIterator(
            $source, RecursiveDirectoryIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_SELF
        );
        $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $path => $value) {
            $newPath = str_replace($source, $destination, $path);
            if ($value->isDir()) {
                \Filesystem::createDir($newPath);
            } else {
                \Filesystem::setPath($newPath)->upload($path);
            }
        }
    }

    /**
     * Apply template from json file to preview without setting its value as the template.
     *
     * @param string $templateCodeName
     *
     * @return void
     */
    public function applyNextGenTemplateForPreview($templateCodeName)
    {
        $this->load->model('teditor/teditor');

        $query = $this->db->query("SELECT COUNT(*) AS SectionCount FROM ecsection
                                   INNER JOIN ecregion ON ecsection.RegionId = ecregion.id
                                   INNER JOIN ecpage ON ecregion.PageId = ecpage.id
                                   INNER JOIN ectemplate ON ectemplate.id = ecpage.TemplateId
                                   WHERE ecsection.Type = 'live'
                                   AND ectemplate.CodeName = '" . $templateCodeName . "'");

        if($query->row['SectionCount'] == 0) {
            $this->applyTemplateJsonFile($templateCodeName);
        }
    }

    /**
     * Check if the extension is exists , if not insert it
     * 
     * @param string $type
     * @param string $extension
     * @param bool $insert
     *
     * @return bool
     */
    public function checkIfExtensionIsExists(string $type, string $extension, $insert = false) : bool
    {
        $data = $this->db->query(vsprintf(
            'SELECT 1 FROM `extension` WHERE `type` = "%s" AND `code` = "%s"',
            [$type, $extension]
        ));

        if (isset($data->num_rows) && $data->num_rows > 0) {
            return true;
        }

        if ($insert === true) {
            $this->db->query(vsprintf(
                'INSERT INTO `extension` (`type`, `code`) VALUES ("%s", "%s")',
                [$type, $extension]
            ));
        }

        return false;
    }

    /**
     * Update stock status colors
     * @param array $data stock ids and colors array
     * @author MohamedHassanWD
     */
    public function UpdateStockStatusColor($data)
    {
        //Get stock status ids from the request
        $ids = [];
        foreach($data as $key=>$val){
            $ids[str_replace('stock_status_color_','',$key)] = $val;
        };

        //Loop on the statuses and update each one
        foreach($ids as $id=>$color){
            if(!$id){
                continue;
            }

            if(!$color){
                $sql = "UPDATE `stock_status` SET current_color=default_color WHERE `stock_status_id`=$id;";
            }else{
                $sql = "UPDATE `stock_status` SET current_color='".$color."' WHERE `stock_status_id`=$id;";
            }
            //Update the database
            $this->db->query($sql);
        }

        // update the cache
        $this->cache->delete('stock_status');
    }

    /**
     * Check if the Template is Custom for the given CodeName
     * @param $CodeName
     *
     */
    protected function isCustomTemplate($CodeName){
        $sql = "SELECT * FROM `ectemplate` where `CodeName` = '$CodeName' AND custom_template != 0";
        $query = $this->db->query($sql);
        if($query->num_rows > 0){
            return true;
        }
        return false;
    }

    /********* Track activate payment if installed app is payment gateway ***********/
    private function trackPaymentIfExist($code){

        //check if app is payment gateway
        if($this->checkIfExtensionIsExists('payment', $code)){

            $this->load->model('extension/payment');
            $this->load->model('setting/mixpanel');
            $this->load->model('setting/amplitude');

            //get payment english name language code
            $paymentMethod = $this->model_extension_payment->getPaymentTitleInLang($code);

            //fire active payment gateway event ExpandCartTracking #347707
            $this->model_setting_mixpanel->trackEvent('Activate Payment Gateway',['Gateway Name'=>$paymentMethod['title']]);
            $this->model_setting_amplitude->trackEvent('Activate Payment Gateway',['Gateway Name'=>$paymentMethod['title']]);

            //update user payment gateways
            $this->updateTrackPayment();
        }

    }

    /********* Track activate shipping if installed app is shipping gateway ***********/
    private function trackShippingIfExist($code){

        //check if app is shipping gateway
        if($this->checkIfExtensionIsExists('shipping', $code)){

            $this->load->model('extension/shipping');
            $this->load->model('setting/mixpanel');
            $this->load->model('setting/amplitude');

            //get shipping english name language code
            $shippingMethod = $this->model_extension_shipping->getShippingTitleInLang($code);

            //fire active shipping gateway event ExpandCartTracking #347705
            $this->model_setting_mixpanel->trackEvent('Activate Shipping Gateway',['Gateway Name'=>$shippingMethod['title']]);
            $this->model_setting_amplitude->trackEvent('Activate Shipping Gateway',['Gateway Name'=>$shippingMethod['title']]);

            //update user shipping gateways
            $this->updateTrackShipping();
        }

    }

    /********* Update user tracking payments gateway  ***********/
    public function updateTrackPayment(){

        $this->load->model('extension/payment');
        $this->load->model('setting/mixpanel');
        $this->load->model('setting/amplitude');

        //update user payment gateways
        $installed_payment = $this->model_extension_payment->getInstalledPaymentTitleInLang();
        $this->model_setting_mixpanel->updateUser(['$payment gateways' => $installed_payment]);
        $this->model_setting_amplitude->updateUser(['payment gateways' => $installed_payment]);
    }


    /********* Update user tracking shipping gateway  ***********/
    public function updateTrackShipping(){

        $this->load->model('extension/shipping');
        $this->load->model('setting/mixpanel');
        $this->load->model('setting/amplitude');

        //update user payment gateways
        $installed_shipping = $this->model_extension_shipping->getInstalledShippingTitleInLang();
        $this->model_setting_mixpanel->updateUser(['$shipping gateways' => $installed_shipping]);
        $this->model_setting_amplitude->updateUser(['shipping gateways' => $installed_shipping]);
    }
}
