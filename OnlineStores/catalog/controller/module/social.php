<?php
class ControllerModuleSocial extends Controller {
	protected function index($setting) {
		// Social Details
        $this->socfacebook = $this->config->get ( 'facebook' );
		$this->soctwitter = $this->config->get ( 'twitter' );
		$this->soclinkedin = $this->config->get ( 'linkedin' );
        $this->socskype = $this->config->get ( 'skype' );
        $this->socgplus = $this->config->get ( 'gplus' );
        $this->socmyspace = $this->config->get ( 'myspace' );
        $this->socflickr = $this->config->get ( 'flickr' );
        $this->socrss = $this->config->get ( 'rss' );
        $this->socdescription = $this->config->get ( 'description' );

		//$this->data ['custom_html'] = $this->config->get ( 'custom_html' );
		$this->id = 'Social';
		
		//Choose which view to display this module with - the left and right column use the same file, the home page center
		//column uses its own view file.
		

		if (file_exists ( DIR_TEMPLATE . $this->config->get ( 'config_template' ) . '/template/common/footer.tpl' )) {
			$this->template = $this->config->get ( 'config_template' ) . '/template/common/footer.tpl';
		} else {
			$this->template = 'default/template/common/footer.tpl';
		}

		$this->render ();
	}
}
?>