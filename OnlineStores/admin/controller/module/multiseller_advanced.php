<?php

class ControllerModuleMultisellerAdvanced extends Controller
{
    public function install()
    {
        $this->load->model("module/multiseller_advanced");
        $this->model_module_multiseller_advanced->install();
    }

    public function uninstall()
    {
        $this->load->model("module/multiseller_advanced");
        $this->model_module_multiseller_advanced->uninstall();
    }

    public function index()
    {
        $this->language->load('module/multiseller_advanced');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link(
                'marketplace/home',
                '',
                'SSL'
            ),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/multiseller_advanced', '', 'SSL'),
            'separator' => ' :: '
        );
        
        $this->load->model('localisation/order_status');
        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $data['isMultiseller'] = true;
        $data['multiseller_advanced'] = $this->config->get('multiseller_advanced');
        $data['seller_based_status'] = $this->config->get('seller_based_status');
        $this->load->model('module/multiseller_advanced');

        if(!$this->config->get('msconf_enable_subscriptions_plans_system')){
            $data["subscription_plan_system"] = false;
        }
        //Check if multiseller App installed else disable advanced multiseller
        if(!$this->model_module_multiseller_advanced->isMultiseller())
        {
            
            if($data['multiseller_advanced']['status'] == 1)
            {
                $data['multiseller_advanced']['status'] = 0;
                $this->load->model('module/multiseller_advanced');
                $this->model_module_multiseller_advanced->updateSettings(['multiseller_advanced' => $data['multiseller_advanced']]);
            }
            $data['isMultiseller'] = false;
        }

        $this->data['isMessagingSellerEnabled'] = !$this->model_module_multiseller_advanced->isMessagingSellerEnabled() ? false:true;

        $this->template = 'module/advanced_multiseller/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['action'] = $this->url->link('module/multiseller_advanced/updateSettings', '', 'SSL');
        $data['cancel'] = $this->url.'marketplace/home';

        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {
        $this->language->load('module/multiseller_advanced');
        
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
             $result_json['success'] = '0';
             $result_json['error'] = $this->error;
        }else{

            $this->load->model('module/multiseller_advanced');
            $this->load->model('setting/setting');
            $data = $this->request->post['multiseller_advanced'];
            
            $this->model_module_multiseller_advanced->updateSettings(['multiseller_advanced' => $data]);

            $seller_based_status=$this->request->post['seller_based_status'];       
            if( $seller_based_status==1){
             $this->model_module_multiseller_advanced->sellerBasedInstall();
             }
            $this->model_setting_setting->editSetting('seller_based', ['seller_based_status' => $seller_based_status]);      

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
            $result_json['redirect'] = '1';
            $result_json['to'] = 'module/multiseller_advanced';

        }
        $this->response->setOutput(json_encode($result_json));
        return;
    }

    public function audit_chat(){

        $this->language->load('module/multiseller_advanced');
        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        ];
        $data['breadcrumbs'][] = [
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link(
                'marketplace/home',
                '',
                'SSL'
            ),
            'separator' => ' :: '
        ];
        $data['breadcrumbs'][] = [
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/multiseller_advanced', '', 'SSL'),
            'separator' => ' :: '
        ];
        //messaging_seller

        $this->load->model('module/multiseller_advanced');
        $this->data['isMessagingSellerEnabled'] = !$this->model_module_multiseller_advanced->isMessagingSellerEnabled() ? false : true;
        $this->data['isMultiseller'] = !$this->model_module_multiseller_advanced->isMultiseller() ? false : true;
        

        //Get all sellers who have a messages.
        $sellers = $this->model_module_multiseller_advanced->getSellersHaveMessages();

        //If sellers found, get First seller conversations list.
        if($sellers){
            $this->data['sellers']        = $sellers;
            $this->data['conversations']  = $this->model_module_multiseller_advanced->getConversations($sellers[0]['id']);
            //Then get first conversation chat log details.
            $this->data['messages']       = $this->model_module_multiseller_advanced->getConversationMsgs($this->data['conversations'][0]['id']);
        }

        $this->template = 'module/advanced_multiseller/audit_chat.expand';
        $this->children = [ 'common/header' , 'common/footer'];
        $this->response->setOutput($this->render());
    }

    public function getSellerConversations(){
        $this->load->model('module/multiseller_advanced');
        $conversations  = $this->model_module_multiseller_advanced->getConversations($this->request->get['seller_id']);
        $result_json['success'] = '1';
        $result_json['conversations'] = $conversations;

        $this->response->setOutput(json_encode($result_json));
    }

    public function getConversationMsgs(){
        $this->load->model('module/multiseller_advanced');
        $messages       = $this->model_module_multiseller_advanced->getConversationMsgs($this->request->get['conversation_id']);
        $result_json['success'] = '1';
        $result_json['messages'] = $messages;

        $this->response->setOutput(json_encode($result_json));
    }
//////////////////////seller based options////////////////////////

    public function sellerbased_options()
    {
        $this->language->load('module/multiseller_advanced'); 

        $this->document->setTitle($this->language->get('seller_options_heading_title'));

        $this->load->model('module/multiseller_sellerbased_options');

        $this->model = $this->model_module_multiseller_sellerbased_options;

        if (isset($this->session->data['errors'])) {
            $this->data['error_warning'] = implode('</br >', $this->session->data['errors']);

            unset($this->session->data['errors']);
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        }

        $data = [];

        $data['sellerbased_options'] = $this->model->getList();


        $this->template = 'module/advanced_multiseller/sellerbased_options/list.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }
    public function addNewOption(){

        $this->language->load('module/multiseller_advanced'); 

        $this->document->setTitle($this->language->get('seller_options_heading_title'));

        $this->load->model('module/multiseller_sellerbased_options');

        $this->model = $this->model_module_multiseller_sellerbased_options;

        if (isset($this->session->data['errors'])) {
            $this->data['error_warning'] = implode('</br >', $this->session->data['errors']);

            unset($this->session->data['errors']);
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        }
        $this->load->model('localisation/language');

        $this->document->setTitle(
            $this->language->get('seller_options_heading_title') . ' - ' .
            $this->language->get('seller_options_new_option')
        );

        $data = [];

        $data['languages'] = $this->model_localisation_language->getLanguages();

        $data['links']['submit'] = $this->url->link(
            'module/multiseller_advanced/submitSellerbasedOptionsForm',
            '',
            'SSL'
        );

        $data['actionType'] = 'create';

        $this->template = 'module/advanced_multiseller/sellerbased_options/form.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );


        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());


    }
    public function editNewOption()
    {
        if (
            !isset($this->request->get['sellerbased_options_id'])
            || $this->request->get['sellerbased_options_id']  == ''
        ) {
            // TODO handle errors messages
            $this->redirect(
                $this->url->link(
                    'multiseller_advanced/sellerbased_options',
                    'token=' . $this->session->data['token'],
                    'SSL'
                )
            );
        }

        $id = $this->request->get['sellerbased_options_id'];

        $this->language->load('module/multiseller_advanced'); 

        $this->document->setTitle($this->language->get('seller_options_heading_title'));

        $this->load->model('module/multiseller_sellerbased_options');

        $this->model = $this->model_module_multiseller_sellerbased_options;

        $this->data['chargeTypes'] =
		[	['value' => 'percentage', 'text'  => $this->language->get('text_charge_percentage')],	
			['value' => 'flat', 'text'  => $this->language->get('text_charge_flat')]		
		];
        $this->load->model('localisation/geo_zone');
        $this->data['areas'] =  $this->model_localisation_geo_zone->getGeoZones();
        
        $sellerBasedSettingsConfig= $this->config->get('seller_based_charge_admin_'.$id);
		$this->data['saved_charges'] = $sellerBasedSettingsConfig;
        $arrayRows= array();
        $Rows= array();
        foreach ($sellerBasedSettingsConfig as $num => $rules) {
            $sub = substr($num, 7, 5 - 4);
            array_push($arrayRows,$sub);
		}
        $this->data['charges_rows']=array_unique($arrayRows);
    
        if (isset($this->session->data['errors'])) {
            $this->data['error_warning'] = implode('</br >', $this->session->data['errors']);

            unset($this->session->data['errors']);
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        }
        $this->load->model('localisation/language');

        $this->document->setTitle(
            $this->language->get('seller_options_heading_title') . ' - ' .
            $this->language->get('seller_options_edit_option')
        );
        $data = [];

        if (!$option = $this->model->getOption($id)) {
            // TODO handle errors messages
            $this->redirect(
                $this->url->link(
                    'multiseller_advanced/sellerbased_options',
                    'token=' . $this->session->data['token'],
                    'SSL'
                )
            );
        }

        $data['options'] = $option;

        $data['languages'] = $this->model_localisation_language->getLanguages();

        $data['links']['submit'] = $this->url->link(
            'module/multiseller_advanced/submitSellerbasedOptionsForm',
            'sellerbased_options_id=' . $id,
            'SSL'
        );

        $data['actionType'] = 'update';


        $this->template = 'module/advanced_multiseller/sellerbased_options/form.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }
    public function submitSellerbasedOptionsForm()
    {
        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

            $this->language->load('module/multiseller_advanced'); 

            $this->document->setTitle($this->language->get('seller_options_heading_title'));
    
            $this->load->model('module/multiseller_sellerbased_options');
    
            $this->model = $this->model_module_multiseller_sellerbased_options;
    
            if (isset($this->session->data['errors'])) {
                $this->data['error_warning'] = implode('</br >', $this->session->data['errors']);
    
                unset($this->session->data['errors']);
            }
    
            if (isset($this->session->data['success'])) {
                $this->data['success'] = $this->session->data['success'];
    
                unset($this->session->data['success']);
            }

            $data = $this->request->post['ms_options'];
            $chargedata = $this->request->post['charge'];
            

            if (!isset($data['options']['status'])) {
                $data['options']['status'] = '0';
            }

            if (!$this->model->validate($data)) {

                if (count($this->model->errors) > 0) {
                    $result_json['success'] = '0';
                    $result_json['errors'] = $this->model->errors;
                    $this->response->setOutput(json_encode($result_json));
                    return;
                }

            }
            if (isset($data['actionType'])) {
                if ($data['actionType'] == 'create') {
             
                    $id = $this->model->newOption($data['options']);
                    $this->model->newOptionDescription($data['details'], $id);

                    $result_json['success'] = '1';
                    $result_json['success_msg'] =  $this->language->get('ms_messages_inserted_success');
                    $result_json['redirect'] = '1';
                    $result_json['to'] = $this->url->link(
                        'module/multiseller_advanced/editNewOption',
                        'sellerbased_options_id=' . $id,
                        'SSL'
                    )->format();
                    $this->response->setOutput(json_encode($result_json));
                    return;
                  
                } else if (
                    $data['actionType'] == 'update'
                    && isset($this->request->get['sellerbased_options_id'])
                ) {
                    $id = $this->request->get['sellerbased_options_id'];
                    $this->model->updateOption($id, $data['options']);
                    $this->model->updateOptionDescription($id, $data['details']);
                    if($chargedata){
                        $group= 'seller_based';    
                        $valueOfKey='seller_based_charge_admin_'. $id;
                        $this->load->model('setting/setting');
                        $this->model_setting_setting->insertUpdateSetting($group, [$valueOfKey =>$chargedata]);
                        }

                    $result_json['success'] = '1';
                    $result_json['success_msg'] =  $this->language->get('ms_messages_updated_success');
                    $this->response->setOutput(json_encode($result_json));
                    return;

                }
            } else {
                $this->redirect(
                    $this->url->link(
                        'module/multiseller_advanced/sellerbased_options',
                        'token=' . $this->session->data['token'],
                        'SSL'
                    )
                );

            }
        } else {
            $this->redirect(
                $this->url->link(
                    'module/multiseller_advanced/sellerbased_options',
                    'token=' . $this->session->data['token'],
                    'SSL'
                )
            );
        }

    }
    public function dtDeleteOption()
    {
        if (
            !isset($this->request->post['selected'])
            || count($this->request->post['selected']) < 1
        ) {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = 'Invalid Ids';

            $this->response->setOutput(json_encode($response));
            return;
        }

        if (isset($this->request->post['selected'])) {

            $this->language->load('module/multiseller_advanced'); 

            $this->document->setTitle($this->language->get('seller_options_heading_title'));
    
            $this->load->model('module/multiseller_sellerbased_options');
    
            $this->model = $this->model_module_multiseller_sellerbased_options;
    
            if (isset($this->session->data['errors'])) {
                $this->data['error_warning'] = implode('</br >', $this->session->data['errors']);
    
                unset($this->session->data['errors']);
            }
    
            if (isset($this->session->data['success'])) {
                $this->data['success'] = $this->session->data['success'];
    
                unset($this->session->data['success']);
            }

            foreach ($this->request->post['selected'] as $id) {
                $this->model->deleteOption($id);
            }

            $response['status'] = 'success';
            $response['title'] = $this->language->get('notification_success_title');
            $response['message'] = $this->language->get('message_deleted_successfully');
        } else {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = '';
        }

        $this->response->setOutput(json_encode($response));
        return;
    }

}
