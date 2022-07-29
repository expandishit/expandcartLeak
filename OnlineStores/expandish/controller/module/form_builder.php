<?php 
class ControllerModuleFormBuilder extends Controller { 
	private $error = array();
	
	public function __construct($registry) {
		parent::__construct($registry);
        if( !\Extension::isInstalled('form_builder') || !$this->config->get('form_builder')['status'] ){
	        $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
		}
	}

	public function index() {
		$this->language->load_json('module/form_builder', true);
		$this->data['lang_id'] = $this->config->get('config_language_id');
		$settings = $this->config->get('form_builder');
		$title = $settings[$this->data['lang_id']]['form_title'];
		$this->document->setTitle($title);
		$this->data['fields'] = $settings['fields'];
		$this->data['form_title'] = $title;
		$this->data['form_desc'] = html_entity_decode($settings[$this->data['lang_id']]['form_desc']);
  	if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
      $requestData = $this->request->post;
      $requestFiles = '';
      if (isset($this->request->files['fattachments'])) {
        $requestFiles = $this->request->files['fattachments'];
      }

  		$this->sendMail($requestData,$requestFiles);
      $this->redirect($this->url->link('module/form_builder/success', '', 'SSL'));

  	} 		

    	$this->data['breadcrumbs'] = array();

    	$this->data['breadcrumbs'][] = array(
      	'text'      => $this->language->get('text_home'),
		    'href'      => $this->url->link('common/home'),
      	'separator' => false
    	); 
	
    	$this->data['breadcrumbs'][] = array(       	
      	'text'      => $title,
		    'href'      => $this->url->link('module/form_builder', '', 'SSL'),
      	'separator' => $this->language->get('text_separator')
    	);

    	// checks
	
      $this->template = 'default/template/module/form_builder/form_builder.expand';
      
  		$this->children = array(
  			'common/footer',
  			'common/header'
  		);
		$this->response->setOutput($this->render_ecwig());
  }
	
	private function sendMail($params,$files = null){
    $message  = $this->language->get('text_greetings')."\n\n";
    foreach ($params as $question => $answer) {
      $message.= " ".str_replace('_', ' ', $question)."\n";
      if(is_array($answer)){
        $counter =0;
        foreach ($answer as $value) {
          $counter++;
          if($counter<count($answer))
            $message.= $value.", ";
          else
            $message.= $value." .\n";
        }
        $counter =0;
      }
      else{
        $message.= " ".str_replace('_', ' ', $answer)."\n";
      }
    }
    $mail = new Mail(); 
    $mail->protocol = $this->config->get('config_mail_protocol');
    $mail->parameter = $this->config->get('config_mail_parameter');
    $mail->hostname = $this->config->get('config_smtp_host');
    $mail->username = $this->config->get('config_smtp_username');
    $mail->password = $this->config->get('config_smtp_password');
    $mail->port = $this->config->get('config_smtp_port');
    $mail->timeout = $this->config->get('config_smtp_timeout');     
    $mail->setTo($this->config->get('form_builder')['email']);
    $mail->setFrom($this->config->get('config_email'));
    $mail->setSender($this->config->get('config_name'));
    $mail->setSubject(html_entity_decode($this->config->get('form_builder')[$this->config->get('config_language_id')]['form_title'], ENT_QUOTES, 'UTF-8'));
    $mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
    // attachments 
    $allowed_filetypes = "png,pdf,jpeg,jpg,docx,gif,xlsx,csv,doc";
    $total = count($files['name']);
    // Loop through each file
    for( $i=0 ; $i < $total ; $i++ ) {
      $file_data['name'] = $_FILES['fattachments']['name'][$i];
      $file_data['type'] = $_FILES['fattachments']['type'][$i];
      $file_data['tmp_name'] = $_FILES['fattachments']['tmp_name'][$i];
      $file_data['size'] = $_FILES['fattachments']['size'][$i];
      //Make sure we have a file path
      if ($file_data != ""){
        $errors = $this->MsLoader->MsFile->checkFile($file_data, $allowed_filetypes);
        if(empty($errors)){
          $fileName = $this->MsLoader->MsFile->uploadImage($file_data);
          $path = \Filesystem::resolvePath("image/".$fileName);
          $path = $_SERVER['DOCUMENT_ROOT']."/".$path;
          $mail->AddAttachment($path);
        }
      }
    }

    $mail->send();
  }

	public function success() {
		$settings = $this->config->get('form_builder');
		$title = $settings[$this->config->get('config_language_id')]['form_title'];

		$this->language->load_json('module/form_builder');

		$this->document->setTitle($title); 

      	$this->data['breadcrumbs'] = array();

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	);

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $title,
			'href'      => $this->url->link('module/form_builder'),
        	'separator' => $this->language->get('text_separator')
      	);



    	$this->data['heading_title'] = $title;
    	$this->data['text_message']  = $this->language->get('text_success');

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/common/success.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/common/success.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/common/success.expand';
        }
		
		$this->children = array(
			'common/footer',
			'common/header'
		);
				
 		$this->response->setOutput($this->render_ecwig());
	}
}
?>