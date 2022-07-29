<?php

class ControllerModuleWideBot extends Controller {
	
	/**
	* @var array the validation errors array.
	*/
	private $error = [];

	public function index(){
        $this->load->language('module/widebot');
		$this->document->setTitle($this->language->get('heading_title'));

        //Breadcrumbs
	    $this->data['breadcrumbs'] = $this->_createBreadcrumbs();
	    $this->data['cancel']      = $this->url->link('marketplace/home', '', 'SSL');

        //Get config settings
        $this->data['widebot']     = $this->config->get('widebot');
        
        //Display Api credentials...
        $api = $this->_getAPICredentials();
        $this->data['api_username']     =  $api['username'];
        $this->data['api_password']     =  $api['password'];

		//render view template
		$this->template = 'module/widebot/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
	}

	public function save(){
	    if( $this->_isAjax() && $this->request->server['REQUEST_METHOD'] == 'POST' ) {

	      //Validate form fields
	      if ( ! $this->_validateForm() ){
	        $result_json['success'] = '0';
	        $result_json['errors'] = $this->error;
	      }
	      else{
	        $this->load->model('setting/setting');
	        $this->load->language('module/widebot');

	        //Save App settings in settings table
	        $this->model_setting_setting->insertUpdateSetting('widebot', $this->request->post );

	        $result_json['success_msg'] = $this->language->get('text_success');
	        $result_json['success']  = '1';
	      }

	      $this->response->setOutput(json_encode($result_json));
	    }
	    else{
	      $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
	    }
	}

	/** Private Methods ***/

	/**
	* Validate form fields.
	*
	* @return bool TRUE|FALSE
	*/
	private function _validateForm(){
		$this->load->language('module/widebot');

		if (!$this->user->hasPermission('modify', 'module/widebot')) {
		    $this->error['warning'] = $this->language->get('error_permission');
		}

		if( !\Extension::isInstalled('widebot') ){
		  $this->error['not_installed'] = $this->language->get('error_not_installed');
		}

	    if ((utf8_strlen($this->request->post['widebot']['script']) < 200) ) {
	      $this->error['widebot_script'] = $this->language->get('error_widebot_script');
	    }

	    if ((utf8_strlen($this->request->post['widebot']['success_follow_name']) < 3) ) {
	      $this->error['success_follow_name'] = $this->language->get('error_success_follow_name');
	    }

	    if ((utf8_strlen($this->request->post['widebot']['failure_follow_name']) < 3) ) {
	      $this->error['failure_follow_name'] = $this->language->get('error_failure_follow_name');
	    }


		if($this->error && !isset($this->error['error']) ){
		  $this->error['warning'] = $this->language->get('error_warning');
		}
		return !$this->error;
	}

    private function _isAjax() {

    	return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
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
		    'text' => $this->language->get('heading_title'),
		    'href' => $this->url->link('module/widebot', '', 'SSL')
		  ]
		];

		return $breadcrumbs;
	}

	private static function str_random(
    	int $length = 64,
    	string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
	): string {
	    if ($length < 1) {
	        throw new \RangeException("Length must be a positive integer");
	    }
	    $pieces = [];
	    $max = mb_strlen($keyspace, '8bit') - 1;
	    for ($i = 0; $i < $length; ++$i) {
	        $pieces []= $keyspace[random_int(0, $max)];
	    }
	    return implode('', $pieces);
	}


	private function _getAPICredentials(){
		$api = $this->db->query('SELECT * FROM api')->row;

        if(empty($api)) {
            $data['username'] = $username = self::str_random(10);
            $data['password'] = $password = self::str_random(15);
            $this->db->query("INSERT INTO api(`username`, `password`, `status`, `firstname`, `lastname`, `date_added`, `date_modified`) VALUES('$username','$password', '1', '', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP )");
        } else {
            $data['username'] = $api['username'];
            $data['password'] = $api['password'];
        }
        return $data;
	}

}
