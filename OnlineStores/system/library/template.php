<?php

use \ExpandCart\Foundation\Exceptions\FileException;

class Template {
	public $data = array();
	
	public function fetch($filename) {
        $dirTemplate = (IS_CUSTOM_TEMPLATE == 1 ? DIR_CUSTOM_TEMPLATE : DIR_TEMPLATE);

		$file = $dirTemplate . $filename;
    
		if (file_exists($file)) {
                return $this->renderTemplate($file);
    	}elseif (file_exists(DIR_TEMPLATE.$filename)) {  
             return $this->renderTemplate(DIR_TEMPLATE.$filename);
        } else {
            throw new FileException('Error: Could not load template ' . $file . '!');
			trigger_error('Error: Could not load template ' . $file . '!');
			exit();				
    	}	
	}
        
        private function renderTemplate($file){
            	extract($this->data);
			
      		ob_start();
      
	  	include($file);
      
	  	$content = ob_get_contents();

      		ob_end_clean();

      		return $content;
        }
        
        
}
?>