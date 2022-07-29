<?php 
use \Firebase\JWT\JWT;  
class ControllerMarketingOverview extends Controller {
    private $current_user_permissions = array();
    private $has_permission_to_view_settings = false;
    private $first_link_in_settings = false;
    private $settings_routes = array();


	public function index() {

        $this->language->load('marketing/overview');
	 
		$this->document->setTitle($this->language->get('heading_title'));

    	$this->data['heading_title'] = $this->language->get('heading_title');

		// Check install directory exists
 		if (is_dir(dirname(DIR_APPLICATION) . '/install')) {
			$this->data['error_install'] = $this->language->get('error_install');
		} else {
			$this->data['error_install'] = '';
		}
										
		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/sales', '', 'SSL'),
      		'separator' => false
   		);

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => false,
            'separator' => false
        );

		$this->template = 'marketing/overview.expand';
		$this->base = 'common/base';
		$this->response->setOutput($this->render_ecwig());
  	}

}
?>
