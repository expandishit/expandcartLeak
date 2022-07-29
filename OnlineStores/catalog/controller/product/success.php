<?php 

class ControllerProductSuccess extends Controller {  

	public function index() {

    	$this->language->load('account/success');

  

    	$this->document->setTitle($this->language->get('heading_title'));



		$this->data['breadcrumbs'] = array();



      	$this->data['breadcrumbs'][] = array(

        	'text'      => $this->language->get('text_home'),

			'href'      => $this->url->link('common/home'),       	

        	'separator' => false

      	); 





    	$this->data['heading_title'] = $this->language->get('heading_title');



		
		

    	$this->data['button_continue'] = $this->language->get('button_continue');

		

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/product/success.tpl')) {

			$this->template = $this->config->get('config_template') . '/template/product/success.tpl';

		} else {

			$this->template = 'default/template/product/success.tpl';

		}

		

		$this->children = array(

			'common/column_left',

			'common/column_right',

			'common/content_top',

			'common/content_bottom',

			'common/footer',

			'common/header'	

		);

						

		$this->response->setOutput($this->render());				

  	}

}

?>