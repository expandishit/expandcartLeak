<?php  
class ControllerModuleAutocompleteAddress extends Controller {
	public function address() {
        $this->load->model('module/autocomplete_address');
        
		$json = array();
		
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if (isset($this->request->post['address']['4']) && $this->request->post['address']['4']['types']['0'] == 'country') {
                $country_name = $this->request->post['address']['4']['long_name'];
            } else if (isset($this->request->post['address']['3']) && $this->request->post['address']['3']['types']['0'] == 'country') {
                $country_name = $this->request->post['address']['3']['long_name'];
            } else if (isset($this->request->post['address']['6']) && $this->request->post['address']['6']['types']['0'] == 'country') {
                $country_name = $this->request->post['address']['6']['long_name'];
            } else if (isset($this->request->post['address']['5']) && $this->request->post['address']['5']['types']['0'] == 'country') {
                $country_name = $this->request->post['address']['5']['long_name'];
            }
            
            switch ($country_name) {
                case 'France':
                    $country_name = 'France, Metropolitan';
                    break;
                case 'The Netherlands':
                    $country_name = 'Netherlands';
                    break;
                default:
                    $country_name = $country_name;
            }
            
            $json['country'] = $this->model_module_autocomplete_address->getCountry($country_name);
            
            $zone = $this->model_module_autocomplete_address->getZone($json['country']['country_id'], $this->request->post['address']['3']['long_name']);
            
            if (!$zone) {
                $zone = $this->model_module_autocomplete_address->getZone($json['country']['country_id'], $this->request->post['address']['2']['long_name']);
            }
            
            if (!$zone) {
                $zone = $this->model_module_autocomplete_address->getZone($json['country']['country_id'], $this->request->post['address']['5']['long_name']);
            }
            
            if (!$zone) {
                $zone = $this->model_module_autocomplete_address->getZone($json['country']['country_id'], $this->request->post['address']['4']['long_name']);
            }
            
            $json['zone'] = $zone;
        }
        
        $this->response->setOutput(json_encode($json));
	}
}
?>