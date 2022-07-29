<?php

class ControllerSellerAccountDeliverySlot extends ControllerSellerAccount {

	public function index() {

        $this->document->setTitle($this->language->get('ms_account_delivery_slot'));
        $this->data['ms_account_delivery_slot'] = $this->language->get('ms_account_delivery_slot');
        $this->data['settings'] = $this->language->get('settings');
        $this->data['entry_checkout_required'] = $this->language->get('entry_checkout_required');
        $this->data['entry_delivery_cutOff'] = $this->language->get('entry_delivery_cutOff');
        $this->data['entry_max_day'] = $this->language->get('entry_max_day');
        $this->data['entry_calendar_style'] = $this->language->get('entry_calendar_style');
        $this->data['entry_calendar_style_help'] = $this->language->get('entry_calendar_style_help');
        $this->data['entry_time_start'] = $this->language->get('entry_time_start');
        $this->data['entry_time_end'] = $this->language->get('entry_time_end');
        $this->data['entry_day_index'] = $this->language->get('entry_day_index');
        $this->data['entry_day_index_note'] = $this->language->get('entry_day_index_note');
        $this->data['entry_other_time'] = $this->language->get('entry_other_time');
        $this->data['text_default'] = $this->language->get('text_default');
        $this->data['text_advanced'] = $this->language->get('text_advanced');
        $this->data['setting_submit_link'] = $this->url->link('seller/account-delivery-slot/storeSettings', '', 'SSL');
        $this->data['button_save'] = $this->language->get('button_save');

        $seller_id = $this->customer->getId();
        $this->data['deliverySlotSettings']= $this->config->get('delivery_slot_'.$seller_id);

        $this->breadcrumbs();
        
      
        list($this->template, $this->children) = $this->MsLoader->MsHelper->loadTemplate('multiseller/delivery_slot/settings');
        
		$this->response->setOutput($this->render());
	}
    public function delivery_slots() {

        $this->document->setTitle($this->language->get('ms_account_delivery_slot'));
        $this->data['ms_account_delivery_slot'] = $this->language->get('ms_account_delivery_slot');
        $this->data['text_new_delivery_slot'] = $this->language->get('text_new_delivery_slot');
        $this->load->model('module/delivery_slots');
        
        $this->data['settings'] = $this->language->get('settings');
        $days = [
            1 => $this->language->get('entry_saturday'),
            2 => $this->language->get('entry_sunday'),
            3 => $this->language->get('entry_monday'),
            4 => $this->language->get('entry_tuesday'),
            5 => $this->language->get('entry_wednesday'),
            6 => $this->language->get('entry_thursday'),
            7 => $this->language->get('entry_friday')
        ];
        $this->data['days']  = $days;

        $this->breadcrumbs();
        $this->data['currentDay'] = isset($this->request->get["day"]) ? $this->request->get["day"] : "Saturday";
        $this->data['day_id'] = isset($this->request->get["day_id"]) ? $this->request->get["day_id"] : 1;

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
            $url .= '&sort=' . $this->request->get['sort'];
        } else {
            $sort = 'ds_date';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
            $url .= '&order=' . $this->request->get['order'];
        } else {
            $order = 'ASC';
        }
        // get today date
        $todayDate = date("m-d-Y");
        $todayName = date('l', strtotime(date("d-m-Y")));


        if(strtolower($todayName) == strtolower($this->data['currentDay'])){
            $balanceDate = $todayDate;
        }else{
            $next = "next ".$this->data['currentDay'];
            $balanceDate = date("m-d-Y", strtotime($next));
        }

        $data = array(
            'sort'  => $sort,
            'order' => $order,
            'day_id' => $this->data['day_id']
        );

        // get total slots
        $slots_total = $this->model_module_delivery_slots->getTotalSlots($data);
        // get all slotss
        $results = $this->model_module_delivery_slots->getSlots($data);
        foreach ($results as  $result) {
            $action = array();

            $action[] = array(
                'text' => $this->language->get('text_edit'),
                'href' => $this->url->link('delivery_slot/slots/update', '&slot_id=' . $result['ds_delivery_slot_id'] . $url, 'SSL')
            );
            $this->data['editSlot'] = "<a href='" . $this->url->link('seller/account-delivery-slot/editSlot', 'slot_id=' . $result['ds_delivery_slot_id'] , 'SSL') ."' class='ms-button ms-button-edit' title='" . $this->language->get('ms_edit') . "'></a>";
          
            $resevedOrders = $this->model_module_delivery_slots->getBalance(['balanceDate'=>$balanceDate,'slot_id'=>$result['ds_delivery_slot_id']]);

            $this->data['slots'][] = array(
                'slot_id'           => $result['ds_delivery_slot_id'],
                'day'              => $days[$result['ds_day_id']],
                'slot_desc'         => $result['delivery_slot'],
                'total_orders'      => $result['total_orders'],
                'reserved'      => $resevedOrders,
                'balance'  => ($result['total_orders'] - $resevedOrders),
                'action'            => $action
            );
        }
        
        list($this->template, $this->children) = $this->MsLoader->MsHelper->loadTemplate('multiseller/delivery_slot/delivery_slots');
        
		$this->response->setOutput($this->render());
	}
  
   
    public function breadcrumbs()
    {
        $this->load->model('seller/seller');
        $seller_title = $this->model_seller_seller->getSellerTitle();
        $this->data['breadcrumbs'] = $this->MsLoader->MsHelper->setBreadcrumbs(array(
			array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account', '', 'SSL'),
			),
			array(
				'text' => sprintf( $this->language->get('ms_account_dashboard_breadcrumbs') , $seller_title ),
				'href' => $this->url->link('seller/account-dashboard', '', 'SSL'),
			),
			array(
				'text' => $this->language->get('ms_account_delivery_slot'),
				'href' => $this->url->link('seller/account-delivery-slot', '', 'SSL'),
			)
		));
        $this->data['settings_link'] = $this->url->link('seller/account-delivery-slot', '', 'SSL');
        $this->data['delivery_slots_link'] = $this->url->link('seller/account-delivery-slot/delivery_slots', '', 'SSL');
        $this->data['slots_order_link'] = $this->url->link('seller/account-delivery-slot/slot_orders', '', 'SSL');

    }
    

    public function addNewSlot() 
    {
        $this->document->setTitle($this->language->get('text_new_delivery_slot'));
        $this->data['ms_account_delivery_slot'] = $this->language->get('ms_account_delivery_slot');
        $this->data['submit_link'] = $this->url->link('seller/account-auctions/store', '', 'SSL');
        $this->getForm();         
	}
  
    public function editSlot() 
    {
        $this->document->setTitle($this->language->get('text_edit_delivery_slot'));
        $this->data['ms_account_delivery_slot'] = $this->language->get('ms_account_delivery_slot');
        $this->load->model('module/delivery_slots');
        $this->data['slot']= $this->model_module_delivery_slots->getSlot($this->request->get['slot_id']);
        $this->getForm();    
        
	}
    public function storeSettings(){
        $json = array();
        $data=$this->request->post;
        $group="delivery_slot";
        $seller_id = $this->customer->getId();
        $valueOfKey='delivery_slot_'.$seller_id;
        
        $this->load->model('setting/setting');
        $this->model_setting_setting->insertUpdateSetting($group, [$valueOfKey =>$data]);
        $json['success']='1';
        $data['ms_success']=$this->language->get('ms_success');
        $this->response->setOutput(json_encode($json));
                
     }
    private function getForm() {
		$this->data['textenabled'] = $this->language->get('text_enabl');
		$this->data['textdisabled'] = $this->language->get('text_disable');

		$this->load->model('localisation/currency');
		$currency = $this->model_localisation_currency->getCurrencyByCode($this->config->get('config_currency'));
        $days = [
            1 => $this->language->get('entry_saturday'),
            2 => $this->language->get('entry_sunday'),
            3 => $this->language->get('entry_monday'),
            4 => $this->language->get('entry_tuesday'),
            5 => $this->language->get('entry_wednesday'),
            6 => $this->language->get('entry_thursday'),
            7 => $this->language->get('entry_friday')
        ];
        $this->data['days']  = $days;

		$this->breadcrumbs();
        $this->document->addScript('expandish/view/javascript/popupwindow/timepicker.js');

        list($this->template, $this->children) = $this->MsLoader->MsHelper->loadTemplate('multiseller/delivery_slot/slot_form');
        
		$this->response->setOutput($this->render());
  	}
      private function validateForm(){

        $data = $this->request->post;

		if( empty($data['delivery_slot']) ){
			$json['errors']['delivery_slot'] = $this->language->get('error_delivery_slot_required');
		}
        if( empty($data['time_start']) ){
			$json['errors']['time_start'] = $this->language->get('error_time_start_required');
		}
        if( empty($data['time_end']) ){
			$json['errors']['time_end'] = $this->language->get('error_time_end_required');
		}
        if( empty($data['day_id']) ){
			$json['errors']['day_id'] = $this->language->get('error_day_required');
		}
        if( empty($data['total_orders']) ){
			$json['errors']['total_orders'] = $this->language->get('error_orders_count_required');
		}
        
        return $json;
	}
    public function storeSlot(){

        $json = array();
         $json= $this->validateForm();
         if (empty($json))
         {
             $data['ms_success']=$this->language->get('ms_success');

             $this->load->model('module/delivery_slots');
             $slot_id = $this->request->post['slot_id'];
             if($slot_id)
             $updateData = $this->model_module_delivery_slots->editSlot($slot_id, $this->request->post);
             else
             $addData = $this->model_module_delivery_slots->addSlots($this->request->post);
 
             if($addData){
                 $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
                 $json['success'] = '1';
                 $data['ms_success']=$this->language->get('ms_success');

             }else{
                 $json['success'] = '0';
                 $data['ms_success']=$this->language->get('unexpected_error');
             }
         }
   
         $this->response->setOutput(json_encode($json));
                
     }
      public function deleteSlot(){

        $this->load->model('module/delivery_slots');
         $is_deleted =  $this->model_module_delivery_slots->deleteSlot($this->request->post['id']);
         if($is_deleted) return true; else return false;
       }
       public function slot_orders() {

        $this->document->setTitle($this->language->get('slot_orders'));
        $this->data['ms_account_delivery_slot'] = $this->language->get('ms_account_delivery_slot');
        $this->breadcrumbs();
        
        $this->load->model('module/delivery_slots');
        $this->data['order_slots']=$this->model_module_delivery_slots->slotsOrders();
    
        list($this->template, $this->children) = $this->MsLoader->MsHelper->loadTemplate('multiseller/delivery_slot/slots_orders');
        
		$this->response->setOutput($this->render());
	}
    public function deleteSlotOrder(){

        $this->load->model('module/delivery_slots');
         $is_deleted =  $this->model_module_delivery_slots->deleteOrderSlot($this->request->post['id']);
         if($is_deleted) return true; else return false;
         
       }
      
  

}
