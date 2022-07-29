<?php

class ControllerModuleCustomerProfilePermissions extends Controller
{
    /**
    * @var array the validation errors array.
    */
    private $error = [];

    public function index(){
 		$this->language->load('module/customer_profile_permissions');
        $this->document->setTitle($this->language->get('customer_profile_permissions_heading_title'));
        $this->data['cancel'] = $this->url->link('marketplace/home', '', 'SSL');
        
        //Breadcrumbs
        $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

        //Get config settings
        $this->data['settings'] = $this->config->get('customer_profile_permissions');

        $this->load->model('sale/customer_group');
        $this->data['groups'] = $this->model_sale_customer_group->getCustomerGroups();

    	$this->template = 'module/customer_profile_permissions/settings.expand';
        $this->children = [ 'common/header' , 'common/footer' ];
        $this->response->setOutput($this->render_ecwig());
    }


    /**
    * Update Module Settings in DB settings table.
    *
    * @return void
    */
    public function update():void{
     if( $this->_isAjax() && $this->request->server['REQUEST_METHOD'] == 'POST' ) {

          //Validate form fields
          if ( ! $this->_validateForm() ){
            $result_json['success'] = '0';
            $result_json['errors'] = $this->error;
          }
          else{
            $this->load->model('setting/setting');
            $this->load->language('module/customer_profile_permissions');

            //Save module status & order status in settings table
            $this->model_setting_setting->insertUpdateSetting('customer_profile_permissions', $this->request->post );

            $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success']  = '1';
          }

          $this->response->setOutput(json_encode($result_json));
        }
        else{
          $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
        }
    }

    /**
    * Validate form fields.
    *
    * @return bool TRUE|FALSE
    */
    private function _validateForm(){
        $this->load->language('module/customer_profile_permissions');

        if (!$this->user->hasPermission('modify', 'module/customer_profile_permissions')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if( !\Extension::isInstalled('customer_profile_permissions') ){
          $this->error['not_installed'] = $this->language->get('error_not_installed');
        }

        if( $this->request->post['customer_profile_permissions']['limit_cust_edit_profile_data'] && 
          empty($this->request->post['customer_profile_permissions']['limit_cust_edit_profile_data_groups']) ){
          $this->error['limit_cust_edit_profile_data_groups'] = $this->language->get('error_limit_cust_edit_profile_data_groups');          
        }

        if( $this->request->post['customer_profile_permissions']['limit_cust_edit_address_data'] && 
          empty($this->request->post['customer_profile_permissions']['limit_cust_edit_address_data_groups']) ){
          $this->error['limit_cust_edit_address_data_groups'] = $this->language->get('error_limit_cust_edit_address_data_groups');          
        }

        if($this->error && !isset($this->error['error']) ){
          $this->error['warning'] = $this->language->get('error_warning');
        }
        return !$this->error;
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
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('marketplace/home', '', 'SSL')
          ],
          [
            'text' => $this->language->get('customer_profile_permissions_heading_title'),
            'href' => $this->url->link('module/customer_profile_permissions_heading_title', '', 'SSL')
          ]
        ];

        return $breadcrumbs;
    }

    /**
    * Check if comming response in AJAX or not.
    *
    * @return bool TRUE|FALSE
    */
    private function _isAjax() {

        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

}

