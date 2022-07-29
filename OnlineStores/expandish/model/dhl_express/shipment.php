<?php
class ModelDhlExpressShipment extends Model {
	
	public function create($order_id)
    {
		$label_check = '';
		$hit_sucess = '';

		$this->load->model('shipping/dhl_express');
		$this->load->model('checkout/order');
		
		$order_info = $this->model_checkout_order->getOrder($order_id);
		$order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

    	$products = $order_product_query->rows;
		
		$selected_service = explode('.',$order_info['shipping_code'])[1];

		$mailingDate = date('Y-m-d');
		$mailing_datetime = date('c');
		$dhl_configured_curr = $this->model_shipping_dhl_express->hit_get_currency();
		$package_type= $this->config->get('dhl_express_packing_type');
		$package = array();
		
		$opencart_currency_cde = $order_info['currency_code'];
		if(empty($package_type))
		{
			echo "DHL module configuration Error!";
			die();
		}
		else{
		
			if ($package_type == 'weight_based')
			{
				$maximum_weight = ($this->config->get('dhl_express_wight_b') !='') ? $this->config->get('dhl_express_wight_b') : '10';
				$weight_unit = ($this->config->get('dhl_express_weight') == true) ? 'LBS' : 'KG';
				$diam_unit = ($this->config->get('dhl_express_weight') == true) ? 'IN' : 'CM';
			
				$packages = $this->model_shipping_dhl_express->weight_based_shipping($products,$opencart_currency_cde,$weight_unit,$diam_unit,$maximum_weight);
			}
			else
			{
				$weight_unit = ($this->config->get('dhl_express_weight') == true) ? 'LBS' : 'KG';
				$diam_unit = ($this->config->get('dhl_express_weight') == true) ? 'IN' : 'CM';
				$pack_type = ($this->config->get('dhl_express_per_item') !='') ? $this->config->get('dhl_express_per_item') : 'BOX';

				$packages = $this->model_shipping_dhl_express->per_item_shipping($products,$opencart_currency_cde,$weight_unit,$diam_unit,$pack_type);
			}
		}
		
		$pieces = "";
		$total_packages = 0;
		$total_weight = 0;
		$total_value = 0;
		$i=0;
		$key =0;
		if ($packages) {
			foreach ($packages as $group_kay => $parcel) {
				//print_r($parcel);
				$index = $key + 1;
				$key++;
				$total_packages += $parcel['GroupPackageCount']; 
				
				$total_weight += $parcel['Weight']['Value'] * $parcel['GroupPackageCount'];
				$total_value += $parcel['InsuredValue']['Amount'] * $parcel['GroupPackageCount'];
				$pack_type = $this->model_shipping_dhl_express->hit_two_get_pack_type($parcel['packtype']);
				$pieces .= '<Piece><PieceID>' . $index . '</PieceID>';
				$pieces .= '<PackageType>'.$pack_type.'</PackageType>';
				$pieces .= '<Weight>' . $parcel['Weight']['Value'] . '</Weight>';
				if( !empty($parcel['width']) && $parcel['height'] && $parcel['depth'] ){
					$pieces .= '<Width>' . round($parcel['width']) . '</Width>';
					$pieces .= '<Height>' . round($parcel['height']) . '</Height>';
					$pieces .= '<Depth>' . round($parcel['depth']) . '</Depth>';
				}
				$pieces .= '</Piece>';
			}	
		}
	
		if(!isset($dhl_configured_curr[$this->config->get('dhl_express_country_code')]))
		{
			echo "Please Use 2 digit ISO string Country code in module configuration page.";
			die();
		}
		
		$dhl_selected_curr = $dhl_configured_curr[$this->config->get('dhl_express_country_code')]['currency'];
		$regioncode = $dhl_configured_curr[$this->config->get('dhl_express_country_code')]['region'];
		
		//Get EN language ID
		$lang_id = null;
		if($this->config->get('config_language') == 'ar'){
			$this->load->model('localisation/language');
			$languages = $this->model_localisation_language->getLanguages();
			if($languages['en']['language_id'])
				$lang_id = $languages['en']['language_id'];
		}
		///////////////////
		///////////////////
		$this->load->model('localisation/zone');
		$zone_info = $this->model_localisation_zone->getZone($order_info['shipping_zone_id'], $lang_id);
		if($zone_info)
			$destination_city = $zone_info['name'];
		else
			$destination_city = ($order_info['shipping_zone']) ? $order_info['shipping_zone'] : '';

		$this->load->model('localisation/country');
		$country_info = $this->model_localisation_country->getCountryByLanguageId($order_info['shipping_country_id'], $lang_id);
		if($country_info)
			$destination_country = $country_info['locale_name'];
		else
			$destination_country = ($order_info['shipping_country']) ? $order_info['shipping_country'] : '';


		$toaddress = array(
			'first_name'	=> $order_info['shipping_firstname'],
			'last_name'	=> $order_info['shipping_lastname'],
			'company'	=> str_replace("&","&amp;",$order_info['shipping_company']),
			'address_1'	=> substr($order_info['shipping_address_1'],0,30),
			'address_2'	=> $order_info['shipping_address_2'],
			'city'	=> strtoupper($destination_city),
			'postcode' => $order_info['shipping_postcode'],
			'country'	=> $destination_country,
			'countrycode'	=> $order_info['shipping_iso_code_2'],
			'email'	=> $order_info['email'],
			'phone'	=> (!empty($order_info['telephone']) ? $order_info['telephone'] : '1234567890'),
		);
			
		$consignee_companyname = substr(htmlspecialchars(!empty( $toaddress['company'] ) ? $toaddress['company'] : $toaddress['first_name']), 0, 35) ;

		$destination_address = '<AddressLine>'.htmlspecialchars($toaddress['address_1']).'</AddressLine>';
		if( !empty( $toaddress['address_2'] ) )
			$destination_address .= '<AddressLine>'.htmlspecialchars($toaddress['address_2']).'</AddressLine>';
		
		$export_declaration = '';
		$export_line_item = '';
		$line = 1;
		$total_price_of_orders = 0;
		$total_weight_of_product = 0;
		$weight_unit = ($this->config->get('dhl_express_weight') == true) ? 'L' : 'K';
		$diam_unit = ($this->config->get('dhl_express_weight') == true) ? 'I' : 'C';
		
		$local_product_code = $this->model_shipping_dhl_express->hit_get_local_product_code($selected_service, $this->config->get('dhl_express_country_code'));
		$local_product_code_node = $local_product_code ? "<LocalProductCode>{$local_product_code}</LocalProductCode>" : '';
		
		
			
		$this->load->model('catalog/product');
		foreach($products as $product)
		{
			$item = $this->model_catalog_product->getProduct($product['product_id']);
			
			$vilue = (float) round($item['price'],2);
			
			$export_line_item .= '<ExportLineItem>';
			$export_line_item .= ' <LineNumber>'.$line.'</LineNumber>';
			$export_line_item .= ' <Quantity>'.$product['quantity'].'</Quantity>';
			$export_line_item .= ' <QuantityUnit>PCS</QuantityUnit>'; //not sure about this value
			$export_line_item .= ' <Description>'.substr( utf8_encode( $product['name'] ), 0, 75 ).'</Description>';
			$export_line_item .= ' <Value>'.number_format((float) round(($vilue),2) , 2, '.', '').'</Value>';
			$total_price_of_orders += (float) round((($vilue * $product['quantity'])),2);
			
			$xa_send_dhl_weight =(float) round($item['weight'] * $product['quantity'],3);
			if($xa_send_dhl_weight < 0.01){
			$xa_send_dhl_weight = 0.01;
			}else{
			$xa_send_dhl_weight = round((float)$xa_send_dhl_weight,3);
			}
			
			$total_weight_of_product += $xa_send_dhl_weight;
			
			
			$xa_send_dhl_weight = (string)$xa_send_dhl_weight;
			$export_line_item .= ' <Weight><Weight>'.$xa_send_dhl_weight.'</Weight><WeightUnit>'.$weight_unit.'</WeightUnit></Weight>';
			$export_line_item .= ' <GrossWeight><Weight>'.$xa_send_dhl_weight.'</Weight><WeightUnit>'.$weight_unit.'</WeightUnit></GrossWeight>';
			$export_line_item .= '</ExportLineItem>';
			$line++;	
		}
		$export_declaration = '<ExportDeclaration><InvoiceNumber>'.$order_id.'</InvoiceNumber><InvoiceDate>'.$mailingDate.'</InvoiceDate>' .$export_line_item. '</ExportDeclaration>';
	
		$is_dutiable = ($order_info['shipping_iso_code_2'] == $this->config->get('dhl_express_country_code') || $this->model_shipping_dhl_express->hit_dhl_is_eu_country($this->config->get('dhl_express_country_code'), $order_info['shipping_iso_code_2'])) ? "N" : "Y";
		$dutiable_content = "<Dutiable><DeclaredValue>{$total_price_of_orders}</DeclaredValue><DeclaredCurrency>{$opencart_currency_cde}</DeclaredCurrency>";
		if ($is_dutiable == "Y") {
			if ($this->config->get('dhl_express_duty_type') == "S") {
				$dutiable_content .= "<TermsOfTrade>DDP</TermsOfTrade>";
			} elseif ($this->config->get('dhl_express_duty_type') == "R") {
				$dutiable_content .= "<TermsOfTrade>DAP</TermsOfTrade>";
			}
		}
		//$dutiable_content .= $is_dutiable == "Y" ? "</Dutiable>" : "";
		$dutiable_content .= "</Dutiable>";
		$special_service = "";
		
		if ($is_dutiable == "Y") {
			//$special_service .= "<SpecialService><SpecialServiceType>DD</SpecialServiceType></SpecialService>";
			// } elseif ($is_dutiable == "Y" && $this->config->get('shipping_hitdhlexpress_duty_type') == "R") {
			// $special_service .= "<SpecialService><SpecialServiceType>DS</SpecialServiceType></SpecialService>";
			
		}
		if( $this->config->get('dhl_express_plt') && $this->config->get('dhl_express_plt') == true && $is_dutiable == 'Y'){
	
			$special_service	.= "<SpecialService>
			<SpecialServiceType>WY</SpecialServiceType>
			</SpecialService>";
			
		}
	
		$dhl_notification = '';
		if($this->config->get('dhl_express_email_trach') == true)
		{
			$dhl_notification = '<Notification><EmailAddress>'.$toaddress['email'].'</EmailAddress><Message>Track the shipment by given URL.</Message></Notification>';
		}
		$request_archive_airway_bill = 'N';
		$number_of_bills_xml = '';
		if($this->config->get('dhl_express_airway') == true){
			$request_archive_airway_bill = 'Y';
			$number_of_bills_xml = '<NumberOfArchiveDoc>1</NumberOfArchiveDoc>';
		}
		$customer_logo_xml ='';
		$customer_logo_url = $this->config->get('dhl_express_logo');
		if(!empty($customer_logo_url) && file_get_contents($customer_logo_url))
		{
			$type = pathinfo($customer_logo_url, PATHINFO_EXTENSION);
			$data = file_get_contents($customer_logo_url);
			$base64 = base64_encode($data);
			$customer_logo_xml = '<CustomerLogo><LogoImage>'.$base64.'</LogoImage><LogoImageFormat>'.strtoupper($type).'</LogoImageFormat></CustomerLogo>';
		}
	
		$ship_county_name = $this->model_shipping_dhl_express->hit_dhl_get_currency_name();	
		
		if($this->config->get('dhl_express_shipment_content') == "")
			$content_shipment_desc = "Export shipment";
		else
			$content_shipment_desc = $this->config->get('dhl_express_shipment_content');
		
		$xmlrequest ='<?xml version="1.0" encoding="UTF-8"?>
		<req:ShipmentRequest xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com ship-val-global-req.xsd" schemaVersion="6.2">
		<Request>
		<ServiceHeader>
		<MessageTime>'.$mailing_datetime.'</MessageTime>
		<MessageReference>Package created from OpenCart</MessageReference>
		<SiteID>'.$this->config->get('dhl_express_key').'</SiteID>
		<Password>'.$this->config->get('dhl_express_password').'</Password>
		</ServiceHeader>
		<MetaData>
		<SoftwareName>3PV</SoftwareName>
		<SoftwareVersion>6.2</SoftwareVersion>
		</MetaData>
		</Request>
		<RegionCode>'.$regioncode.'</RegionCode>
		<RequestedPickupTime>Y</RequestedPickupTime>
		<NewShipper>Y</NewShipper>
		<LanguageCode>en</LanguageCode>
		<PiecesEnabled>Y</PiecesEnabled>
		<Billing>
		<ShipperAccountNumber>'. $this->config->get('dhl_express_account') .'</ShipperAccountNumber>
		<ShippingPaymentType>S</ShippingPaymentType>
		<BillingAccountNumber>'. $this->config->get('dhl_express_account') .'</BillingAccountNumber>
		</Billing>
		<Consignee>
		<CompanyName>'.$consignee_companyname.'</CompanyName>
		'.$destination_address.'
		<City>'.$toaddress['city'].'</City>
		<PostalCode>'.$toaddress['postcode'].'</PostalCode>
		<CountryCode>'.$toaddress['countrycode'].'</CountryCode>
		<CountryName>'.$toaddress['country'].'</CountryName>
		<Contact>
		<PersonName>'.$toaddress['first_name'].' '.$toaddress['last_name']. '</PersonName>
		<PhoneNumber>'.$toaddress['phone'].'</PhoneNumber>
		<Email>'.$toaddress['email'].'</Email>
		</Contact>
		</Consignee>
		<Commodity>
		<CommodityCode>cc</CommodityCode>
		<CommodityName>cn</CommodityName>
		</Commodity>
		'.$dutiable_content.'
		<UseDHLInvoice>Y</UseDHLInvoice>
		<DHLInvoiceLanguageCode>en</DHLInvoiceLanguageCode>
		<DHLInvoiceType>CMI</DHLInvoiceType>
		'.$export_declaration.'
		<Reference>
		<ReferenceID>'. $order_id .'</ReferenceID>	
		</Reference>	
		<ShipmentDetails>
		<NumberOfPieces>'.$total_packages.'</NumberOfPieces>';
		if($pieces){
			$xmlrequest .='<Pieces>
			'.$pieces.'
			</Pieces>';
		}
		$xmlrequest.=
		'<Weight>'.$total_weight_of_product.'</Weight>
		<WeightUnit>'.$weight_unit.'</WeightUnit>
		<GlobalProductCode>'.$selected_service.'</GlobalProductCode>
		'.$local_product_code_node.'
		<Date>'.$mailingDate.'</Date>
		<Contents>'.$content_shipment_desc.'</Contents>
		<DoorTo>DD</DoorTo>
		<DimensionUnit>'.$diam_unit.'</DimensionUnit>
		<IsDutiable>'.$is_dutiable.'</IsDutiable>
		<CurrencyCode>'.$opencart_currency_cde.'</CurrencyCode>
		</ShipmentDetails>
		<Shipper>
		<ShipperID>'.$this->config->get('dhl_express_account').'</ShipperID>
		<CompanyName>'.$this->config->get('dhl_express_company_name').'</CompanyName>
		<RegisteredAccount>'.$this->config->get('dhl_express_account').'</RegisteredAccount>
		<AddressLine>'.$this->config->get('dhl_express_address1').'</AddressLine>
		<City>'.$this->config->get('dhl_express_city').'</City>
		<Division>'.$this->config->get('dhl_express_state').'</Division>
		<PostalCode>'.$this->config->get('dhl_express_postcode').'</PostalCode>
		<CountryCode>'.$this->config->get('dhl_express_country_code').'</CountryCode>
		<CountryName>'.$ship_county_name[$this->config->get('dhl_express_country_code')].'</CountryName>
		<Contact>
		<PersonName>'.$this->config->get('dhl_express_shipper_name').'</PersonName>
		<PhoneNumber>'.$this->config->get('dhl_express_phone_num').'</PhoneNumber>
		<Email>'.$this->config->get('dhl_express_email_addr').'</Email>
		</Contact>
		</Shipper>
		'.$special_service.'
		'.$dhl_notification.'
		<LabelImageFormat>'.$this->config->get('dhl_express_output_type').'</LabelImageFormat> 
		<RequestArchiveDoc>'.$request_archive_airway_bill.'</RequestArchiveDoc>
		'.$number_of_bills_xml.'
		<Label><HideAccount>Y</HideAccount><LabelTemplate>'.$this->config->get('dhl_express_dropoff_type').'</LabelTemplate>'.$customer_logo_xml.'</Label>
		<ODDLinkReq>Y</ODDLinkReq>
		</req:ShipmentRequest>';

		$url = 'https://xmlpitest-ea.dhl.com/XMLShippingServlet?isUTF8Support=true';
		if (!$this->config->get('dhl_express_test')) {
		$url = 'https://xmlpi-ea.dhl.com/XMLShippingServlet?isUTF8Support=true';
		} 
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_POSTFIELDS, $xmlrequest);
		curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_HEADER => false,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		));
		$result = utf8_encode(curl_exec($curl));
		if($this->config->get('dhl_express_display_time') == true)
		{
			/*if (!file_exists(DIR_APPLICATION .'dhllog')) {
				mkdir(DIR_APPLICATION .'dhllog', 0777, true);
			}*/
			// Whoever introduced xml to shipping companies should be flogged
			$log_xml = 'Request '.PHP_EOL;
			$log_xml .= $xmlrequest .PHP_EOL . PHP_EOL . PHP_EOL;
			$log_xml .= PHP_EOL . 'Response'.PHP_EOL;
			$log_xml .= $result .PHP_EOL .PHP_EOL .PHP_EOL .PHP_EOL;
			
			$arLogData['event_datetime']='['.date('D Y-m-d h:i:s A').'] [client '.$_SERVER['REMOTE_ADDR'].']'; 
			$log =$arLogData['event_datetime']. $log_xml;
			//create file with current date name 
			$stCurLogFileName='dhllog/hit_dhl_label_log_'.date('Ymd').'.txt'; 
			//open the file append mode,dats the log file will create day wise 
			// $fHandler=fopen(DIR_APPLICATION.$stCurLogFileName,'a+'); 
			
			
			//write the info into the file 
			// fwrite($fHandler,$log); 
			//close handler 
			// fclose($fHandler);
		}

		/*
		echo "<pre>";
		print_r(htmlspecialchars($xmlrequest));
		//print_r(htmlspecialchars($result));
		
		die();
		*/

		$xml = '';
		libxml_use_internal_errors(true);
		if(!empty($result))
		{
			$xml = simplexml_load_string(utf8_encode($result));
		}

		if(empty($xml))
		{
			$textmessage = 'DHL Connection Problem With API.';
			$this->createShipmentFail($order_id, $textmessage);
		}
		else if(isset($xml->Response->Status->ActionStatus))
		{
			$textmessage = $xml->Response->Status->Condition->ConditionData . "<br/>";
			$this->createShipmentFail($order_id, $textmessage);
		}
		else
		{
			$tracking_number = (string) $xml->AirwayBillNumber;
			$shipping_charge = (string) $xml->ShippingCharge;
			$service = (string) $xml->ProductShortName;
			$LabelImage = (string) $xml->LabelImage->OutputImage;
			$CommercialInvoice	= (string)$xml->LabelImage->MultiLabels->MultiLabel->DocImageVal;
			$this->db->query("INSERT INTO " . DB_PREFIX . "hittech_dhl_details_new SET order_id = '". $order_id ."', tracking_num = '" . $tracking_number . "', shipping_label = '". $LabelImage ."', invoice = '". $CommercialInvoice ."', one ='". $shipping_charge ."', two = '". $service ."'");
			
			$shipmenthistory = "Tracking_number No. ".$tracking_number." - Order No. ".$order_id;
			$is_email = 1;
			$message = array(
				'notify' => $is_email,
				'comment' => $shipmenthistory
			);
			$this->addOrderHistory($order_id, $message);
			
		}

	}
		
	/// add to order history
	
	public function getOrderStatusId($order_id)
	{
		$query = $this->db->query("SELECT order_status_id FROM `" . DB_PREFIX . "order` WHERE order_id =".(int)$order_id);
		return $query->row['order_status_id'];
	
	}
	
	public function addOrderHistory($order_id, $data) {
		
		$this->load->model('checkout/order');
		$order_status_id = $this->getOrderStatusId($order_id);
		
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$order_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");

		$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '" . (isset($data['notify']) ? (int)$data['notify'] : 0) . "', comment = '" . $this->db->escape(strip_tags($data['comment'])) . "', date_added = NOW()");

		$order_info = $this->model_checkout_order->getOrder($order_id);

		

		if ($data['notify']) {
			
			$subject = sprintf('%s - Order Update %s', $order_info['store_name'], $order_id);

			$message  = 'Order ID:' . $order_id . "\n";
			$message .= 'Date Ordered:'. date("Y-m-d", strtotime($order_info['date_added'])) . "\n\n";

			$order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "' AND language_id = '" . (int)$order_info['language_id'] . "'");

			if ($order_status_query->num_rows) {
				$message .= 'Your order has been updated to the following status:' . "\n";
				$message .= $order_status_query->row['name'] . "\n\n";
			}

			if ($order_info['customer_id']) {
				$message .= 'To view your order click on the link below:' . "\n";
				$message .= html_entity_decode($order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_id, ENT_QUOTES, 'UTF-8') . "\n\n";
			}

			if ($data['comment']) {
				$message .= 'The comments for your order are:' . "\n\n";
				$message .= strip_tags(html_entity_decode($data['comment'], ENT_QUOTES, 'UTF-8')) . "\n\n";
			}
			$adminemail = (!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email'));
			$message .= 'Please reply to this email if you have any questions.';

			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->hostname = $this->config->get('config_smtp_host');
			$mail->username = $this->config->get('config_smtp_username');
			$mail->password = $this->config->get('config_smtp_password');
			$mail->port = $this->config->get('config_smtp_port');
			$mail->timeout = $this->config->get('config_smtp_timeout');
			$mail->setReplyTo(
	            $this->config->get('config_mail_reply_to'),
	            $this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
	            $this->config->get('config_email')
	        );
			$mail->setTo($order_info['email'].','.$adminemail);
			$mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
			$mail->setSender($order_info['store_name']);
			$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
            if ($this->config->get('custom_email_templates_status') && !isset($cet)) {
                include_once(DIR_SYSTEM . 'library/custom_email_templates.php');

                $cet = new CustomEmailTemplates($this->registry);

                $cet_result = $cet->getEmailTemplate(array('type' => 'admin', 'class' => __CLASS__, 'function' => __FUNCTION__, 'vars' => get_defined_vars()));

                if ($cet_result) {
                    if ($cet_result['subject']) {
                        $mail->setSubject(html_entity_decode($cet_result['subject'], ENT_QUOTES, 'UTF-8'));
                    }

                    if ($cet_result['html']) {
                        $mail->setNewHtml($cet_result['html']);
                    }

                    if ($cet_result['text']) {
                        $mail->setNewText($cet_result['text']);
                    }

                    if ($cet_result['bcc_html']) {
                        $mail->setBccHtml($cet_result['bcc_html']);
                    }

                    if ((isset($this->request->post['attach_invoicepdf']) && $this->request->post['attach_invoicepdf'] == 1) || $cet_result['invoice'] == 1) {
                        $path_to_invoice_pdf = $cet->invoice($order_info, $cet_result['history_id']);

                        $mail->addAttachment($path_to_invoice_pdf);
                    }

                    if (isset($this->request->post['fattachments'])) {
                        if ($this->request->post['fattachments']) {
                            foreach ($this->request->post['fattachments'] as $attachment) {
                                foreach ($attachment as $file) {
                                    $mail->addAttachment($file);
                                }
                            }
                        }
                    }

                    $mail->setBccEmails($cet_result['bcc_emails']);
                }
            }
			$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();
            if ($this->config->get('custom_email_templates_status')) {
                $mail->sendBccEmails();
            }
		}
		
	}
	
	///////////////////////// order history end ////////////////////////////
	
	public function createShipmentFail($order_id, $textmessage) {
			$this->load->model('checkout/order');
			
			$subject  = sprintf('%s - auto create shipment %s', $order_info['store_name'], $order_id);
			$message  = 'Order ID:' . $order_id . "\n";
			$message .= 'Date shipment created:'. date("Y-m-d", strtotime($order_info['date_added'])) . "\n\n";
			$message  = 'While creating the shipment we have found below issue:' . "\n\n";
			$message .= $textmessage . "\n\n";
			
			$order_info = $this->model_checkout_order->getOrder($order_id);
			$adminemail = (!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email'));
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->hostname = $this->config->get('config_smtp_host');
			$mail->username = $this->config->get('config_smtp_username');
			$mail->password = $this->config->get('config_smtp_password');
			$mail->port = $this->config->get('config_smtp_port');
			$mail->timeout = $this->config->get('config_smtp_timeout');
			$mail->setReplyTo(
	            $this->config->get('config_mail_reply_to'),
	            $this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
	            $this->config->get('config_email')
	        );
			$mail->setTo($adminemail);
			$mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
			$mail->setSender($order_info['store_name']);
			$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
        if ($this->config->get('custom_email_templates_status') && !isset($cet)) {
            include_once(DIR_SYSTEM . 'library/custom_email_templates.php');

            $cet = new CustomEmailTemplates($this->registry);

            $cet_result = $cet->getEmailTemplate(array('type' => 'admin', 'class' => __CLASS__, 'function' => __FUNCTION__, 'vars' => get_defined_vars()));

            if ($cet_result) {
                if ($cet_result['subject']) {
                    $mail->setSubject(html_entity_decode($cet_result['subject'], ENT_QUOTES, 'UTF-8'));
                }

                if ($cet_result['html']) {
                    $mail->setNewHtml($cet_result['html']);
                }

                if ($cet_result['text']) {
                    $mail->setNewText($cet_result['text']);
                }

                if ($cet_result['bcc_html']) {
                    $mail->setBccHtml($cet_result['bcc_html']);
                }

                if ((isset($this->request->post['attach_invoicepdf']) && $this->request->post['attach_invoicepdf'] == 1) || $cet_result['invoice'] == 1) {
                    $path_to_invoice_pdf = $cet->invoice($order_info, $cet_result['history_id']);

                    $mail->addAttachment($path_to_invoice_pdf);
                }

                if (isset($this->request->post['fattachments'])) {
                    if ($this->request->post['fattachments']) {
                        foreach ($this->request->post['fattachments'] as $attachment) {
                            foreach ($attachment as $file) {
                                $mail->addAttachment($file);
                            }
                        }
                    }
                }

                $mail->setBccEmails($cet_result['bcc_emails']);
            }
        }
			$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();
        if ($this->config->get('custom_email_templates_status')) {
            $mail->sendBccEmails();
        }
	}
	
}
?>