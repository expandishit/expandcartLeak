<?php

class ControllerModulelableb extends Controller
{
    
    public function autoComplete(){
        $this->load->model('module/lableb');
        $searchText			= trim($this->request->get['search_text']);
        $json			  	= [];
		$json['products'] 	= $this->model_module_lableb->autocomplete($searchText);
        $this->response->setOutput(json_encode($json));
    }
	
}
