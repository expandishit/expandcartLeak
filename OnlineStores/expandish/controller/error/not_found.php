<?php   
class ControllerErrorNotFound extends Controller {
	public function index() {		
		$this->language->load_json('error/not_found');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->data['breadcrumbs'] = array();
 
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	);		
		
		if (isset($this->request->get['route'])) {
			$data = $this->request->get;
			
			unset($data['_route_']);
			
			$route = $data['route'];
			
			unset($data['route']);
			
			$url = '';
			
			if ($data) {
				$url = '&' . urldecode(http_build_query($data, '', '&'));
			}	
			
			if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
				$connection = 'SSL';
			} else {
				$connection = 'NONSSL';
			}
											
       		$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link($route, $url, $connection),
        		'separator' => $this->language->get('text_separator')
      		);	   	
		}
		
		
		if(isset($this->request->get['msg']))
		{
			$this->data['text_error']=ucfirst(str_replace("_"," ",$this->request->get['msg']));
		}
		
		$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . '/1.1 404 Not Found');
		
		$this->data['continue'] = $this->url->link('common/home');

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/error/not_found.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/error/not_found.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/error/not_found.expand';
        }
		
		$this->children = array(
			'common/footer',
			'common/header'
		);
		
		$this->response->setOutput($this->render_ecwig());
  	}
}
?>