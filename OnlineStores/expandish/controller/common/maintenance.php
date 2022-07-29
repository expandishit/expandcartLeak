<?php
class ControllerCommonMaintenance extends Controller {
    public function index() {
        if ($this->config->get('config_maintenance')) {
			$route = '';
			
			if (isset($this->request->get['route'])) {
				$part = explode('/', $this->request->get['route']);
				
				if (isset($part[0])) {
					$route .= $part[0];
				}			
			}
			
			// Show site if logged in as admin
			$this->load->library('user');
			
			$this->user = new User($this->registry);
	
			if (($route != 'api') && ($route != 'payment') && !$this->user->isLogged()) {
				return $this->forward('common/maintenance/info');
			}						
        }
    }
		
	public function info() {
        $this->language->load_json('common/maintenance');
        
        $this->document->setTitle($this->language->get('heading_title'));

        $this->document->breadcrumbs = array();

        $this->document->breadcrumbs[] = array(
            'text'      => $this->language->get('text_maintenance'),
			'href'      => $this->url->link('common/maintenance'),
            'separator' => false
        );

        $lang_id = $this->config->get('config_language_id');
        $config_msg = $this->config->get('config_maintenance_msg');

        if ( ! empty( $config_msg[ $lang_id ] ) )
        {
            $this->data['text_message'] = html_entity_decode($config_msg[$lang_id]);
        }
        else
        {
            $this->data['text_message'] = $this->language->get('text_message');
        }

        if (file_exists(DIR_TEMPLATE . 'CustomMaintenancePage/' . STORECODE . '/index.html')) {
            $this->template = 'CustomMaintenancePage/' . STORECODE . '/index.html';
        }
        else {
            if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/common/maintenance.expand')) {
                $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/common/maintenance.expand';
            }
            else {
                $this->template = $this->config->get('config_template') . '/template/common/maintenance.expand';
            }

            $this->template = $this->config->get('config_template') . '/template/common/maintenance.expand';

            $this->children = array(
                'common/footer',
                'common/header'
            );
        }

        $this->response->setOutput($this->render_ecwig());
    }
}
?>
