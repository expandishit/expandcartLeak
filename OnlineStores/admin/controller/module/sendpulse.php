<?php

class ControllerModuleSendPulse extends Controller
{
	/**
     * @var array the validation errors array.
     */
    private $error = [];

	public function index()
	{
    	$this->load->language('module/sendpulse');

    	//Get config settings
        $this->data['sendpulse'] = $this->config->get('sendpulse');

        $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

    	/*prepare settings.expand view data*/
	    $this->document->setTitle($this->language->get('heading_title'));

	    $this->template = 'module/sendpulse/settings.expand';
	    $this->children = ['common/header', 'common/footer'];
	    $this->response->setOutput($this->render());
	}

	/**
     * Saving the changes in the app settings page
     */
    public function save()
    {
        if( $this->_isAjax() && $this->request->server['REQUEST_METHOD'] == 'POST' ) {

            //Validate form fields
            if ( ! $this->_validateForm() ) {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
            }
            else {
                $this->load->model('setting/setting');
                $this->load->language('module/sendpulse');

                //Save App settings in settings table
                $this->model_setting_setting->insertUpdateSetting('sendpulse', $this->request->post );

                $result_json['success_msg'] = $this->language->get('text_success');
                $result_json['success']     = '1';
            }

            $this->response->setOutput(json_encode($result_json));
        }
        else{
            $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
        }
    }


    /**
     * Form the breadcrumbs array.
     *
     * @return Array $breadcrumbs
     */
    private function _createBreadcrumbs(){
        return [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', '', 'SSL')
            ],
            [
                'text' => $this->language->get('text_module'),
                'href' => $this->url->link('marketplace/home', '', 'SSL')
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('module/sendpulse', '', 'SSL')
            ]
        ];
    }

        /**
     * Checking if the request coming via ajax request or not.
     */
    private function _isAjax() {

        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    	/**
	* Validate form fields.
	*
	* @return bool TRUE|FALSE
	*/
	private function _validateForm(){
		$this->load->language('module/sendpulse');

		if (!$this->user->hasPermission('modify', 'module/sendpulse')) {
		    $this->error['warning'] = $this->language->get('error_permission');
		}

		if( !\Extension::isInstalled('sendpulse') ){
		  $this->error['not_installed'] = $this->language->get('error_not_installed');
		}

	    if ((utf8_strlen($this->request->post['sendpulse']['script']) < 100) ) {
	      $this->error['sendpulse_script'] = $this->language->get('error_sendpulse_script');
	    }

		if($this->error && !isset($this->error['error']) ){
		  $this->error['warning'] = $this->language->get('error_warning');
		}
		return !$this->error;
	}

}

