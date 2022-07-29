<?php
// -----------------------------
// Social Slides for OpenCart 
// By Best-Byte
// www.best-byte.com
// -----------------------------

class ControllerModuleSocialSlides extends Controller {

	private $_name = 'socialslides';

	protected function index() {
		static $module = 0;
		
		$this->load->model('setting/store');
		
		$this->data['store'] = $this->config->get('config_name');

    $this->load->language('module/' . $this->_name);
		
		$this->data['text_linkedin'] = $this->language->get('text_linkedin');
        $this->data['text_instagram'] = $this->language->get('text_instagram');

    $this->data['facebook_show']  = $this->config->get($this->_name . '_facebook_show');
    $this->data['facebook_code']  = $this->config->get($this->_name . '_facebook_code');
    
    $this->data['twitter_show']  = $this->config->get($this->_name . '_twitter_show');
    $this->data['twitter_code']  = $this->config->get($this->_name . '_twitter_code');  
    
    $this->data['google_show']  = $this->config->get($this->_name . '_google_show');
    $this->data['google_code']  = $this->config->get($this->_name . '_google_code');  

    $this->data['pinterest_show']  = $this->config->get($this->_name . '_pinterest_show');
    $this->data['pinterest_code']  = $this->config->get($this->_name . '_pinterest_code');
    
    $this->data['youtube_show']  = $this->config->get($this->_name . '_youtube_show');
    $this->data['youtube_code']  = $this->config->get($this->_name . '_youtube_code'); 
    
    $this->data['linkedin_show']  = $this->config->get($this->_name . '_linkedin_show');
    $this->data['linkedin_code']  = $this->config->get($this->_name . '_linkedin_code');

        $this->data['instagram_show']  = $this->config->get($this->_name . '_instagram_show');
        $this->data['instagram_code']  = $this->config->get($this->_name . '_instagram_code');

        $this->data['top_position']  = $this->config->get($this->_name . '_top_position');
        $this->data['display'] = $this->config->get($this->_name . '_display');
    
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/socialslides.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/socialslides.tpl';
		} else {
			$this->template = 'default/template/module/socialslides.tpl';
		}
		
		$this->render();
	}
}
?>