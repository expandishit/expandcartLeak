<?php

class ControllerShippingNaqel extends Controller {

  /**
   * @var array the validation errors array.
   */
  private $error = [];
  public $version = "9.0";



  public function index(){

    $this->load->language('shipping/naqel');

    //Form Buttons
    //save button - Ajax post request
    $this->data['action'] = $this->url->link('shipping/naqel/save', '' , 'SSL');
    $this->data['cancel'] = $this->url->link('extension/shipping', 'type=shipping', true);

    /*Get form fields data*/
    $this->data['naqel_password']      = $this->config->get('naqel_password');
    $this->data['naqel_client_id']    = $this->config->get('naqel_client_id');
    $this->data['naqel_after_creation_status'] = $this->config->get('naqel_after_creation_status');
    $this->data['naqel_status']       = $this->config->get('naqel_status');
    $this->data['naqel_cost'] = $this->config->get('naqel_cost');

    $this->data['naqel_sender_email']  = $this->config->get('naqel_sender_email');
    $this->data['naqel_sender_phone']  = $this->config->get('naqel_sender_phone');
    $this->data['naqel_sender_country']  = $this->config->get('naqel_sender_country');
    $this->data['naqel_sender_city']  = $this->config->get('naqel_sender_city');
    $this->data['naqel_geo_zone_id']  = $this->config->get('naqel_geo_zone_id');

    $this->load->model('localisation/geo_zone');
    $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

    $this->load->model('shipping/naqel');
    $this->data['countries'] = $this->model_shipping_naqel->getCountries();

    $this->load->model('localisation/order_status');
    $this->data['order_statuses']    = $this->model_localisation_order_status->getOrderStatuses();
    //Breadcrumbs
    $this->data['breadcrumbs']       = $this->_createBreadcrumbs();
    /*prepare quick.expand view data*/
    $this->document->setTitle($this->language->get('heading_title'));
    $this->template = 'shipping/naqel.expand';
    $this->children = ['common/header', 'common/footer'];
    $this->response->setOutput($this->render());

    }

  /**
  * Save form data and Enable Extension after data validation.
  *
  * @return void
  */
  public function save(){
    if( $this->_isAjax() && $this->request->server['REQUEST_METHOD'] == 'POST' ) {

      //Validate form fields
      if ( ! $this->_validateForm() ){
        $result_json['success'] = '0';
        $result_json['errors'] = $this->error;
      }
      else{
        $this->load->model('setting/setting');
        $this->load->language('shipping/naqel');
        //Save naqel settings in settings table
        $this->model_setting_setting->insertUpdateSetting('naqel', $this->request->post );
          $this->tracking->updateGuideValue('SHIPPING');
        $result_json['success_msg'] = $this->language->get('text_success');

        $result_json['success']  = '1';
      }

      $this->response->setOutput(json_encode($result_json));
    }
    else{
      $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
    }
  }


  public function create(){
		$order_id = $this->request->get['order_id'];

    $this->load->language('shipping/naqel');
		$this->load->model('sale/order');
    $this->load->model('shipping/naqel');

		$order = $this->model_sale_order->getOrder($order_id);
    $this->data['countries'] = $this->model_shipping_naqel->getCountries();
		//Check if store order has shipping order already
		if( !empty($order['tracking']) ){
            $this->redirect($this->url->link('shipping/naqel/shipmentDetails', 'order_id=' . $order_id, 'SSL'));

        }

		$this->data['order'] = $order;
    $this->data['weight'] = $this->model_sale_order->getOrderTotalWeight($order_id);

    //getLoadTypeList from naqel
    $load_types = $this->getLoadTypeList();
    // check if we have more load type and send them to the view
    if(isset($load_types[0]))
      $this->data['load_types'] = $load_types ;
		/*prepare naqel.expand view data*/

	  $this->document->setTitle($this->language->get('create_heading_title'));
 		//Breadcrumbs
	  $this->data['breadcrumbs'] = $this->_createBreadcrumbs();
		$this->template = 'shipping/naqel/shipment/create.expand';
	  $this->children = ['common/header', 'common/footer'];
    $this->response->setOutput($this->render_ecwig());
  }


  public function store(){
		if( $this->_isAjax() && $this->request->server['REQUEST_METHOD'] == 'POST' ) {

			//Validate form fields
			if ( !empty($this->_validateShippingOrder()) ){
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;
			}
			else{
        $this->load->language('shipping/naqel');
        $this->load->model('shipping/naqel');
        $this->load->model('sale/order');

				$order_id = $this->request->post['order_id'];
				$order    = $this->model_sale_order->getOrder($order_id);
        $billing_type = ($order['payment_code'] == "cod") ? "COD" : "A";
        $billing_type_id = ($order['payment_code'] == "cod") ? 5 : 1;
        if(isset($this->request->post['load_types'])){
          $load_type_id = $this->request->post['load_types'];
        }
        else{
          $load_type_id = ($this->getLoadTypeList())['ID'];
        }

        $order_total_in_usd = $order['total'];
        if( $order['currency_code'] != "USD" ){
          $order_total_in_usd = round($this->currency->gatUSDRate($order['currency_code']) * $order['total'],4);
        }
        $cod = ($billing_type_id == 5) ? ($order_total_in_usd ) : 0;
        // CurrenyID in xml will sent = 4 as this is the USD currency id from naqel docs
        $reference_id = time()."_".$order_id;
        $lang = $this->config->get('config_admin_language');
        if($this->request->post['shipping_type'] == 0){
          // domestic shipping
            $xml_create_waybill = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                       <soapenv:Header/>
                       <soapenv:Body>
                          <tem:CreateWaybill>
                             <!--Optional:-->
                             <tem:_ManifestShipmentDetails>
                                <!--Optional:-->
                                <tem:ClientInfo>
                                   <!--Optional:-->
                                   <tem:ClientAddress>
                                      <!--Optional:-->
                                      <tem:PhoneNumber>'.$this->config->get('naqel_sender_phone').'</tem:PhoneNumber>
                                      <!--Optional:-->
                                      <tem:POBox></tem:POBox>
                                      <!--Optional:-->
                                      <tem:ZipCode></tem:ZipCode>
                                      <!--Optional:-->
                                      <tem:Fax></tem:Fax>
                                      <!--Optional:-->
                                      <tem:FirstAddress>'.$this->config->get('config_address')[$lang].'</tem:FirstAddress>
                                      <!--Optional:-->
                                      <tem:Location></tem:Location>
                                      <!--Optional:-->
                                      <tem:CountryCode>'.$this->config->get('naqel_sender_country').'</tem:CountryCode>
                                      <!--Optional:-->
                                      <tem:CityCode>'.$this->config->get('naqel_sender_city').'</tem:CityCode>
                                   </tem:ClientAddress>
                                   <!--Optional:-->
                                   <tem:ClientContact>
                                      <!--Optional:-->
                                      <tem:Name>'.$this->config->get('config_name')[$lang].'</tem:Name>
                                      <!--Optional:-->
                                      <tem:Email>'.$this->config->get('naqel_sender_email').'</tem:Email>
                                      <!--Optional:-->
                                      <tem:PhoneNumber>'.$this->config->get('naqel_sender_phone').'</tem:PhoneNumber>
                                      <!--Optional:-->
                                      <tem:MobileNo>'.$this->config->get('naqel_sender_phone').'</tem:MobileNo>
                                   </tem:ClientContact>
                                   <tem:ClientID>'.$this->config->get('naqel_client_id').'</tem:ClientID>
                                   <!--Optional:-->
                                   <tem:Password>'.$this->config->get('naqel_password').'</tem:Password>
                                   <!--Optional:-->
                                   <tem:Version>'.$this->version.'</tem:Version>
                                </tem:ClientInfo>
                                <!--Optional:-->
                                <tem:ConsigneeInfo>
                                   <tem:ConsigneeNationalID>0</tem:ConsigneeNationalID>
                                   <!--Optional:-->
                                   <tem:ConsigneeName>'.$this->request->post['receiver_name'].'</tem:ConsigneeName>
                                   <!--Optional:-->
                                   <tem:Email>'.$this->request->post['receiver_email'].'</tem:Email>
                                   <!--Optional:-->
                                   <tem:Mobile>'.$this->request->post['receiver_phone'].'</tem:Mobile>
                                   <!--Optional:-->
                                   <tem:PhoneNumber>'.$this->request->post['receiver_phone'].'</tem:PhoneNumber>
                                   <!--Optional:-->
                                   <tem:Fax></tem:Fax>
                                   <!--Optional:-->
                                   <tem:District></tem:District>
                                   <!--Optional:-->
                                   <tem:Address>test</tem:Address>
                                   <!--Optional:-->
                                   <tem:NationalAddress></tem:NationalAddress>
                                   <!--Optional:-->
                                   <tem:Near></tem:Near>
                                   <!--Optional:-->
                                   <tem:CountryCode>'.$this->request->post['receiver_country'].'</tem:CountryCode>
                                   <!--Optional:-->
                                   <tem:CityCode>'.$this->request->post['receiver_city'].'</tem:CityCode>
                                </tem:ConsigneeInfo>
                                  
                                <!--Optional:-->
                                <tem:CurrenyID>1</tem:CurrenyID>
                                <tem:BillingType>'.$billing_type_id.'</tem:BillingType>
                                <tem:PicesCount>1</tem:PicesCount>
                                <tem:Weight>'.$this->request->post['weight'].'</tem:Weight>
                                <!--Optional:-->
                                <tem:DeliveryInstruction></tem:DeliveryInstruction>
                                <tem:CODCharge>'.$cod.'</tem:CODCharge>
                                <!--Optional:-->
                                <tem:CreateBooking>' . ($this->request->post['create_booking'] ? 'true' : 'false') . '</tem:CreateBooking>
                                <!--Optional:-->
                                <tem:isRTO>false</tem:isRTO>
                                <!--Optional:-->
                                <tem:GeneratePiecesBarCodes>true</tem:GeneratePiecesBarCodes>
                             
                                <tem:LoadTypeID>36</tem:LoadTypeID>
                                <!--Optional:-->
                                <tem:DeclareValue>0</tem:DeclareValue>
                                <!--Optional:-->
                                <tem:GoodDesc>Bag</tem:GoodDesc>
                                <!--Optional:-->
                                <tem:Latitude></tem:Latitude>
                                <!--Optional:-->
                                <tem:Longitude></tem:Longitude>
                                <!--Optional:-->
                                <tem:RefNo>'.$reference_id.'</tem:RefNo>
                                <!--Optional:-->
                                <tem:Width>1</tem:Width>
                                <!--Optional:-->
                                <tem:Length>1</tem:Length>
                                <!--Optional:-->
                                <tem:Height>1</tem:Height>
                                <!--Optional:-->
                                <tem:VolumetricWeight>0.1</tem:VolumetricWeight>
                                <!--Optional:-->
                                <tem:InsuredValue>0</tem:InsuredValue>
                                <!--Optional:-->
                                <tem:Reference1></tem:Reference1>
                                <!--Optional:-->
                                <tem:Reference2></tem:Reference2>
                                <!--Optional:-->
                                <tem:GoodsVATAmount>0</tem:GoodsVATAmount>
                                <!--Optional:-->
                                <tem:IsCustomDutyPayByConsignee>false</tem:IsCustomDutyPayByConsignee>
                                <!--Optional:-->
                                <tem:PickUpPoint></tem:PickUpPoint>
                             </tem:_ManifestShipmentDetails>
                          </tem:CreateWaybill>
                       </soapenv:Body>
                    </soapenv:Envelope>        
                ';
        }
        else{
          // international shipping 
          $xml_create_waybill = '<?xml version="1.0" encoding="utf-8"?>
          <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
            <soap:Body>
              <CreateWaybill xmlns="http://tempuri.org/">
                <_ManifestShipmentDetails>
                  <ClientInfo>
                    <ClientAddress>
                      <PhoneNumber>'.$this->config->get('naqel_sender_phone').'</PhoneNumber>
                      <POBox></POBox>
                      <ZipCode></ZipCode>
                      <Fax></Fax>
                      <FirstAddress>'.$this->config->get('config_address')[$lang].'</FirstAddress>
                      <Location>'.$this->config->get('config_address')[$lang].'</Location>
                      <CountryCode>'.$this->config->get('naqel_sender_country').'</CountryCode>
                      <CityCode>'.$this->config->get('naqel_sender_city').'</CityCode>
                    </ClientAddress>
                    <ClientContact>
                      <Name>'.$this->config->get('config_name')[$lang].'</Name>
                      <Email>'.$this->config->get('naqel_sender_email').'</Email>
                      <PhoneNumber>'.$this->config->get('naqel_sender_phone').'</PhoneNumber>
                      <MobileNo>'.$this->config->get('naqel_sender_phone').'</MobileNo>
                    </ClientContact>
                      <ClientID>'.$this->config->get('naqel_client_id').'</ClientID>
                      <Password>'.$this->config->get('naqel_password').'</Password>
                      <Version>'.$this->version.'</Version>
                  </ClientInfo>
                  <ConsigneeInfo>
                    <ConsigneeNationalID></ConsigneeNationalID>
                     <ConsigneeName>'.$this->request->post['receiver_name'].'</ConsigneeName>
                     <Email>'.$this->request->post['receiver_email'].'</Email>
                     <Mobile>'.$this->request->post['receiver_phone'].'</Mobile>
                     <PhoneNumber>'.$this->request->post['receiver_phone'].'</PhoneNumber>
                     <Address>'.$this->request->post['receiver_address'].'</Address>
                     <CountryCode>'.$this->request->post['receiver_country'].'</CountryCode>
                     <CityCode>'.$this->request->post['receiver_city'].'</CityCode>
                  </ConsigneeInfo>
                  <_CommercialInvoice>
                    <RefNo>'.$reference_id.'</RefNo>
                    <InvoiceNo>'.$reference_id.'</InvoiceNo>
                    <InvoiceDate>'.$order['date_added'].'</InvoiceDate>
                    <Consignee>'.$this->request->post['receiver_name'].' </Consignee>
                    <ConsigneeAddress>'.$this->request->post['receiver_address'].'</ConsigneeAddress>
                    <ConsigneeEmail>'.$this->request->post['receiver_email'].'</ConsigneeEmail>
                    <MobileNo>'.$this->request->post['receiver_phone'].'</MobileNo>
                    <Phone>'.$this->request->post['receiver_phone'].'</Phone>
                    <TotalCost>'.$cod.'</TotalCost>
                    <CurrencyCode>USD</CurrencyCode>
                    <CommercialInvoiceDetailList>
                      <CommercialInvoiceDetail>
                        <Quantity>1</Quantity>
                        <UnitType>piece</UnitType>
                        <CountryofManufacture></CountryofManufacture>
                        <Description></Description>
                        <UnitCost>'.$cod.'</UnitCost>
                        <CustomsCommodityCode></CustomsCommodityCode>
                        <Currency>USD</Currency>
                      </CommercialInvoiceDetail>
                    </CommercialInvoiceDetailList>
                  </_CommercialInvoice>
                  <CurrenyID>4</CurrenyID>
                  <DeclareValue>0</DeclareValue>
                  <BillingType>'.$billing_type_id.'</BillingType>
                  <PicesCount>1</PicesCount>
                  <Weight>'.$this->request->post['weight'].'</Weight>
                  <DeliveryInstruction>'.$this->request->post['delivery_instruction'].'</DeliveryInstruction>
                  <CODCharge>'.$cod.'</CODCharge>
                  <CreateBooking>' . ($this->request->post['create_booking'] ? 'true' : 'false') . '</CreateBooking>
                  <isRTO>false</isRTO>
                  <GeneratePiecesBarCodes>false</GeneratePiecesBarCodes>
                  <LoadTypeID>'.$load_type_id.'</LoadTypeID>
                  <GoodDesc>'.$this->request->post['delivery_instruction'].'</GoodDesc>
                  <Latitude></Latitude>
                  <Longitude></Longitude>
                  <RefNo>'.$reference_id.'</RefNo>
                  <Reference1></Reference1>
                  <Reference2></Reference2>
                  <GoodsVATAmount>0</GoodsVATAmount>
                  <IsCustomDutyPayByConsignee>false</IsCustomDutyPayByConsignee>
                </_ManifestShipmentDetails>
              </CreateWaybill>
            </soap:Body>
          </soap:Envelope>';
        }
        $waybillResponse = $this->model_shipping_naqel->CreateWaybill($xml_create_waybill);
	    	if($waybillResponse['HasError'] == "false"){
          //succeeded
    			//update status & add history record
    			if( !empty($this->config->get('naqel_after_creation_status')) ){
			        $this->model_sale_order->addOrderHistory($order['order_id'], [
			          'comment'          => 'naqel.Key: ' . $waybillResponse['Key'] . ' & BookingRefNo: ' . $waybillResponse['BookingRefNo'] . '  &  WaybillNo: '.$waybillResponse['WaybillNo'],
			          'order_status_id'  => $this->config->get('naqel_after_creation_status'),
			        ]);
				   }

                $resultData['orderData'] = $this->request->post;
                $resultData['response'] = [
                    "naqel_key" =>$waybillResponse['Key'],
                    "BookingRefNo" => $waybillResponse['BookingRefNo'],
                    "WaybillNo"=>$waybillResponse['WaybillNo']
                ];

                $this->model_shipping_naqel->addShipmentDetails($order_id, $resultData, "pre-pair");

	        //Update Tracking Number & Tracking URL
	        //$this->model_sale_order->updateShippingTrackingURL($order_id , 'https://tracking.naqel.om/track/'.$response['result']['data']['orders'][0]['tracking_number']);
	        $this->model_sale_order->updateShippingTrackingNumber($order_id , $waybillResponse['WaybillNo']);

    			//Returning to Order page
    			$result_json['success_msg'] = $waybillResponse['Message'];
    			$result_json['success']  = '1';
  					//redirect
					$result_json['redirect'] = '1';
			    $result_json['to'] = $this->url->link('shipping/naqel/shipmentDetails', 'order_id=' . $order_id, 'SSL')->format();

        }
    		else{
    			$result_json['success'] = '0';
				  $result_json['errors']  = $waybillResponse['Message'];
    		}
  		}
			$this->response->setOutput(json_encode($result_json));
		}
		else{
			$this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
		}
  }

  public function printLabel(){
      $this->load->model('shipping/naqel');

      $orderId = $this->request->get['order_id'];

      $orderData = $this->model_shipping_naqel->getShipmentDetails($orderId);
      $shipment_details = json_decode($orderData['details'],true, 512, JSON_UNESCAPED_UNICODE);

      $lang = $this->config->get('config_admin_language');

      $xml_print_label = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                           <soapenv:Header/>
                           <soapenv:Body>
                              <tem:GetWaybillSticker>
                                 <tem:clientInfo>
                                    <tem:ClientAddress>
                                       <tem:PhoneNumber></tem:PhoneNumber>
                                       <tem:POBox></tem:POBox>
                                       <tem:ZipCode></tem:ZipCode>
                                       <tem:Fax></tem:Fax>
                                       <tem:FirstAddress></tem:FirstAddress>
                                       <tem:Location></tem:Location>
                                       <tem:CountryCode></tem:CountryCode>
                                       <tem:CityCode></tem:CityCode>
                                    </tem:ClientAddress>
                                    <tem:ClientContact>
                                       <tem:Name></tem:Name>
                                       <tem:Email></tem:Email>
                                       <tem:PhoneNumber></tem:PhoneNumber>
                                       <tem:MobileNo></tem:MobileNo>
                                    </tem:ClientContact>
                                    <tem:ClientID>'.$this->config->get('naqel_client_id').'</tem:ClientID>
                                    <tem:Password>'.$this->config->get('naqel_password').'</tem:Password>
                                    <tem:Version>'.$this->version.'</tem:Version>
                                 </tem:clientInfo>
                                 <tem:WaybillNo>'.$shipment_details['response']['WaybillNo'].'</tem:WaybillNo>
                                 <tem:StickerSize>FourMSixthInches</tem:StickerSize>
                              </tem:GetWaybillSticker>
                           </soapenv:Body>
                        </soapenv:Envelope>';

      $printLabelResponse = $this->model_shipping_naqel->printLabel($xml_print_label);

      $response_decode = base64_decode($printLabelResponse);

      header("Content-disposition: attachment; filename=sticker.pdf");
      header("Content-type: application/pdf");
      echo $response_decode;
      exit;
  }

    public function shipmentDetails()
    {
        // load Language File
        $this->load->language('shipping/naqel');

        // set Page Title
        $this->document->setTitle($this->language->get('create_heading_title'));


        $this->load->model('sale/order');
        $this->load->model('shipping/naqel');


        $this->data['breadcrumbs'] = $this->_createBreadcrumbs();


        // get order id
        $orderId = $this->request->get['order_id'];

        // get order data
        $orderData = $this->model_shipping_naqel->getShipmentDetails($orderId);

        $this->data['shipment_details'] = json_decode($orderData['details'],true, 512, JSON_UNESCAPED_UNICODE);


        $this->data['order_id'] = $orderId;


        $this->template = 'shipping/naqel/shipment/details.expand';
        $this->children = array(
            'common/footer',
            'common/header'
        );
        $this->response->setOutput($this->render_ecwig());
        return;
    }


  /**
  * Form the breadcrumbs array.
  *
  * @return Array $breadcrumbs
  */
  private function _createBreadcrumbs(){

    $breadcrumbs = [
      [
        'text' => $this->language->get('text_home'),
        'href' => $this->url->link('common/dashboard', '', 'SSL')
      ],
      [
        'text' => $this->language->get('text_extension'),
        'href' => $this->url->link('extension/shipping', 'type=shipping', true)
      ],
      [
        'text' => $this->language->get('heading_title'),
        'href' => $this->url->link('shipping/naqel', true)
      ]
    ];
    return $breadcrumbs;
  }

  /**
  * Validate form fields.
  *
  * @return bool TRUE|FALSE
  */
  private function _validateForm(){
    $this->load->language('shipping/naqel');

    if (!$this->user->hasPermission('modify', 'shipping/naqel')) {
        $this->error['warning'] = $this->language->get('error_permission');
    }

    if( !\Extension::isInstalled('naqel') ){
      $this->error['naqel_not_installed'] = $this->language->get('error_not_installed');
    }

    if ((utf8_strlen($this->request->post['naqel_passowrd']) > 40) ) {
      $this->error['naqel_passowrd'] = $this->language->get('error_password');
    }

    if((utf8_strlen($this->request->post['naqel_client_id']) > 7) ) {
        $this->error['naqel_client_id'] = $this->language->get('error_client_id');
    }

    if( !preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/", $this->request->post['naqel_sender_email'])){
      $this->error['email'] = $this->language->get('error_email');
    }
    if( !isset($this->request->post['naqel_sender_phone']) || !preg_match("/^\+?\d+$/", $this->request->post['naqel_sender_phone']) ){
        $this->error['phone'] = $this->language->get('error_phone');
    }

    if(utf8_strlen($this->request->post['naqel_cost']) < 0){
      $this->error['naqel_cost'] = $this->language->get('error_cost');
    }

    if($this->error && !isset($this->error['error']) ){
      $this->error['warning'] = $this->language->get('error_warning');
    }
    return !$this->error;
  }

  private function _isAjax() {

    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
  }



  private function _validateShippingOrder(){
    $this->load->language('shipping/naqel');
    if(utf8_strlen($this->request->post['receiver_name']) < 3){
      $this->error['receiver_name'] = $this->language->get('error_name');
    }
    if( !isset($this->request->post['receiver_phone']) || !preg_match("/^\+?\d+$/", $this->request->post['receiver_phone']) ){
      $this->error['receiver_phone'] = $this->language->get('error_phone');
    }

    if( utf8_strlen($this->request->post['receiver_country']) < 2){
      $this->error['receiver_country'] = $this->language->get('error_country');
    }
    if( utf8_strlen($this->request->post['receiver_city']) < 2){
      $this->error['receiver_city'] = $this->language->get('error_city');
    }
    if( utf8_strlen($this->request->post['receiver_address']) < 4){
      $this->error['receiver_address'] = $this->language->get('error_address');
    }
    if($this->request->post['weight'] <= 0){
      $this->error['weight'] = $this->language->get('error_weight');
    }

    if($this->error && !isset($this->error['error']) ){
      $this->error['warning'] = $this->language->get('errors_heading');
    }

    return $this->error;
  }

  private function getLoadTypeList()
  {
    $lang = $this->config->get('config_admin_language');
    $xml_post_string  = '<?xml version="1.0" encoding="utf-8"?>
    <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
      <soap:Body>
        <GetLoadTypeList xmlns="http://tempuri.org/">
          <ClientInfo>
            <ClientAddress>
              <PhoneNumber>'.$this->config->get('naqel_sender_phone').'</PhoneNumber>
              <POBox></POBox>
              <ZipCode></ZipCode>
              <Fax></Fax>
              <FirstAddress>'.$this->config->get('config_address')[$lang].'</FirstAddress>
              <Location>'.$this->config->get('config_address')[$lang].'</Location>
              <CountryCode>'.$this->config->get('naqel_sender_country').'</CountryCode>
              <CityCode>'.$this->config->get('naqel_sender_city').'</CityCode>
            </ClientAddress>
            <ClientContact>
              <Name>'.$this->config->get('config_name')[$lang].'</Name>
              <Email>'.$this->config->get('naqel_sender_email').'</Email>
              <PhoneNumber>'.$this->config->get('config_telephone').'</PhoneNumber>
              <MobileNo>'.$this->config->get('naqel_sender_phone').'</MobileNo>
            </ClientContact>
            <ClientID>'.$this->config->get('naqel_client_id').'</ClientID>
            <Password>'.$this->config->get('naqel_password').'</Password>
            <Version>'.$this->version.'</Version>
          </ClientInfo>
        </GetLoadTypeList>
      </soap:Body>
    </soap:Envelope>';
    return $this->model_shipping_naqel->getLoadTypeList($xml_post_string);
    
  }

  public function install() {
    $this->load->model("shipping/naqel");
    $this->model_shipping_naqel->install();
  }

  public function uninstall() {
    $this->load->model("shipping/naqel");
    $this->model_shipping_naqel->uninstall();
  }

  public function country() {
    $json = array();
    $this->load->model('shipping/naqel');
    $json = array(
      'zone' => $this->model_shipping_naqel->getCitiesByCountryCode($this->request->post['country_code'])
    );
    $this->response->setOutput(json_encode($json));
  }

}
