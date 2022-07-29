<?php
class ControllerLocalisationGeoZone extends Controller { 
	private $error = array();
 
	public function dtDelete()
	{
        $this->load->model('localisation/geo_zone');
        $this->load->model('setting/setting');
		$this->language->load('localisation/geo_zone');

        if ( !isset($this->request->post['id']) )
        {
            return false;
        }

        $id_s = $this->request->post['id'];

        if ( is_array($id_s) )
        {
            foreach ($id_s as $geo_zone_id)
            {
                if ( $this->model_localisation_geo_zone->deleteGeoZone( (int) $geo_zone_id ) )
                {
                    $result_json['success'] = '1';
                    $result_json['success_msg'] = $this->language->get('text_success');

					$this->tracking->updateGuideValue('GEO_ZONES');
                }
                else
                {
                    $result_json['success'] = '0';
                    $result_json['error'] = $this->language->get('error_delete_last_geozone');
                    break;
                }
            }
        }
        else
        {
            $geo_zone_id = (int) $id_s;

            if ( $this->model_localisation_geo_zone->deleteGeoZone($geo_zone_id) )
            {
                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_success');
                
				$this->tracking->updateGuideValue('GEO_ZONES');
            }
            else
            {
                $result_json['success'] = '0';
                $result_json['error'] = $this->language->get('error_delete_last_geozone');
            }
        }

        $this->response->setOutput(json_encode($result_json));
        return;
	}

    public function dtHandler() {
        $this->load->model('localisation/geo_zone');
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'geo_zone_id',
            1 => 'name',
            2 => 'description',
        );
        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->model_localisation_geo_zone->dtHandler($start, $length, $search, $orderColumn, $orderType);
        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $json_data = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($totalData), // total number of records
            "recordsFiltered" => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $records   // total data array
        );
        $this->response->setOutput(json_encode($json_data));
        return;
    }	

	public function index() {

		if ($this->request->get['card_name']){
			$this->session->data['card_name'] = $this->request->get['card_name'];
		}

		$this->language->load('localisation/geo_zone');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('localisation/geo_zone');
		
		$this->getList();
	}


	public function insert()
	{
		$this->language->load('localisation/geo_zone');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('localisation/geo_zone');
		
		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
		{
			if ( $this->validateForm() )
			{
				$this->load->model('setting/setting');
				$guide = $this->model_setting_setting->getGuideValue('ASSISTANT');

				if ($guide['GEO_ZONES'] !=1){
					$this->model_setting_setting->editGuideValue('ASSISTANT', 'GEO_ZONES', '1');
				}

				$this->model_localisation_geo_zone->addGeoZone($this->request->post);

				$result_json['success'] = '1';
				$result_json['sucess_msg'] = $this->language->get('text_success');
			}
			else
			{
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;
			}
			
			$this->response->setOutput(json_encode($result_json));
			return;

		}

		$this->getForm();
	}

	
	public function update()
	{
		$this->language->load('localisation/geo_zone');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('localisation/geo_zone');
		
		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
		{

			if ( $this->validateForm() )
			{
				$this->model_localisation_geo_zone->editGeoZone($this->request->get['geo_zone_id'], $this->request->post);
				$this->load->model('setting/setting');
				$this->tracking->updateGuideValue('GEO_ZONES');

				$result_json['success'] = '1';
				$result_json['success_msg'] = $this->language->get('text_success');
				
			}
			else
			{
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;
			}

			$this->response->setOutput(json_encode($result_json));
			return;
		}



		$this->getForm();
	}


	public function delete() {
		$this->language->load('localisation/geo_zone');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('localisation/geo_zone');
		
		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $geo_zone_id) {
				$this->model_localisation_geo_zone->deleteGeoZone($geo_zone_id);
			}

			$this->tracking->updateGuideValue('GEO_ZONES');
						
			$this->session->data['success'] = $this->language->get('text_success');
 
			$url = '';
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			
			$this->redirect($this->url->link('localisation/geo_zone', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}
		
		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else { 
			$order = 'ASC';
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		
		$url = '';
			
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('localisation/geo_zone', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['insert'] = $this->url->link('localisation/geo_zone/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('localisation/geo_zone/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['cancel'] = $this->url->link('localisation/geo_zone', '', 'SSL');

		$this->data['geo_zones'] = array();

		$data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);
		
		$geo_zone_total = $this->model_localisation_geo_zone->getTotalGeoZones();
		
		$results = $this->model_localisation_geo_zone->getGeoZones($data);

		foreach ($results as $result) {
			$action = array();
			
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('localisation/geo_zone/update', 'token=' . $this->session->data['token'] . '&geo_zone_id=' . $result['geo_zone_id'] . $url, 'SSL')
			);
					
			$this->data['geo_zones'][] = array(
				'geo_zone_id' => $result['geo_zone_id'],
				'name'        => $result['name'],
				'description' => $result['description'],
				'selected'    => isset($this->request->post['selected']) && in_array($result['geo_zone_id'], $this->request->post['selected']),
				'action'      => $action
			);
		}

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}
		
		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		 
		$this->data['sort_name'] = $this->url->link('localisation/geo_zone', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');
		$this->data['sort_description'] = $this->url->link('localisation/geo_zone', 'token=' . $this->session->data['token'] . '&sort=description' . $url, 'SSL');
		
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
												
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $geo_zone_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('localisation/geo_zone', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->template = 'localisation/geo_zone_list.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}

	
	private function getForm()
	{
 		
  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('localisation/geo_zone', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
		
   		$this->data['breadcrumbs'][] = array(
       		'text'      => !isset($this->request->get['geo_zone_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
			'href'      => $this->url->link('localisation/geo_zone', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);

		if (!isset($this->request->get['geo_zone_id'])) {

			$this->data['action'] = $this->url->link('localisation/geo_zone/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {

			$this->data['action'] = $this->url->link('localisation/geo_zone/update', 'token=' . $this->session->data['token'] . '&geo_zone_id=' . $this->request->get['geo_zone_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('localisation/geo_zone', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['geo_zone_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {

			$geo_zone_info = $this->model_localisation_geo_zone->getGeoZone($this->request->get['geo_zone_id']);
          		}

		$this->data['token'] = $this->session->data['token'];
		
		if (isset($this->request->post['name'])) {
			$this->data['name'] = $this->request->post['name'];
		} elseif (!empty($geo_zone_info)) {
			$this->data['name'] = $geo_zone_info['name'];
		} else {
			$this->data['name'] = '';
		}

		if (isset($this->request->post['description'])) {
			$this->data['description'] = $this->request->post['description'];
		} elseif (!empty($geo_zone_info)) {
			$this->data['description'] = $geo_zone_info['description'];
		} else {
			$this->data['description'] = '';
		}
		
		$this->load->model('localisation/country');
		$this->load->model('localisation/zone');
		$this->load->model('localisation/area');

		//manar $this->data['countries'] = $this->model_localisation_country->getCountries();
		if (isset($this->request->post['zone_to_geo_zone'])) {

			$this->data['zone_to_geo_zones'] = $this->request->post['zone_to_geo_zone'];
		} elseif (isset($this->request->get['geo_zone_id'])) {

			$this->data['zone_to_geo_zones'] = $this->model_localisation_geo_zone->getZoneToGeoZones($this->request->get['geo_zone_id']);
		
            $tempzone=array();
            $new_zone_to_geo_zones=array();

           
            foreach ($this->data['zone_to_geo_zones'] as $zoneToGeoZone)
			{
                $country_id = $zoneToGeoZone['country_id'];
                if(!empty($country_id)) {
                  //get one country connected to one zone
                    $data_country = array(
                        'country_id' => trim($zoneToGeoZone['country_id'])
                    );

                    $onesaved_country = $this->model_localisation_country->getCountries($data_country);
                    $zoneToGeoZone['country_id'] = $onesaved_country[0]['country_id'];
                    $zoneToGeoZone['country_name'] = $onesaved_country[0]['name'];

                    // get one country /get one zone
                    if (!empty($zoneToGeoZone['zone_id'])) {
                        $data_zone = array(
                            'zone_id' => trim($zoneToGeoZone['zone_id']),
                            'country_id' => trim($zoneToGeoZone['country_id'])
                        );

                        $onesaved_zone = $this->model_localisation_zone->getZoneByCountry($data_zone);
                        if (!empty($onesaved_zone)) {
                            $zoneToGeoZone['zone_id'] = $onesaved_zone[0]['zone_id'];
                            $zoneToGeoZone['zone_name'] = $onesaved_zone[0]['name'];
                        } else {
                           // $zoneToGeoZone['zone_id'] = 0;
                            $zoneToGeoZone['zone_name'] = '';
                        }				
						
                    }else if($zoneToGeoZone['zone_id'] == 0){
                            $zoneToGeoZone['zone_id'] = 0;
                            $zoneToGeoZone['zone_name'] = $this->language->get('text_all_zones');
                    }
					if (!empty($zoneToGeoZone['area_id'])) {
						$data_area = array(
							'zone_id' => trim($zoneToGeoZone['zone_id']),
							'area_id' => trim($zoneToGeoZone['area_id'])
						);
						$onesaved_area=$this->model_localisation_area->getAreaByZone($data_area);
						if (!empty($onesaved_area)) {
							$zoneToGeoZone['area_id'] = $onesaved_area[0]['area_id'];
							$zoneToGeoZone['area_name'] = $onesaved_area[0]['name'];
						} else {
						   // $zoneToGeoZone['zone_id'] = 0;
							$zoneToGeoZone['area_name'] = '';
						}
					}else if($zoneToGeoZone['area_id'] == 0){
							$zoneToGeoZone['area_id'] = 0;
							$zoneToGeoZone['area_name'] = $this->language->get('text_all_zones');
					}
					
					$new_zone_to_geo_zones[]=$zoneToGeoZone;				

                }

			}

            $this->data['zone_to_geo_zones']=$new_zone_to_geo_zones;

			} else {
				$this->data['zone_to_geo_zones'] = array();
			}

		$this->template = 'localisation/geo_zone_form.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}



    public function country_autocomplete() {
        $json = array();
        if (isset($this->request->get['filter_name'])) {

            $this->load->model('localisation/country');

            $data = array(
                'filter_name' => trim($this->request->get['filter_name']),
                'start' => 0,
                'limit' => 20
            );

            $results = $this->model_localisation_country->getCountries($data);

            foreach ($results as $result) {
                $json[] = array(
                    'country_id' => $result['country_id'],
                    'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))

                );
            }

        }
        $sort_order = array();

        foreach ($json as $key => $value) {
            $sort_order[$key] = $value['name'];
        }

        array_multisort($sort_order, SORT_ASC, $json);

        $this->response->setOutput(json_encode($json));
    }

public function zone_autocomplete()
{
  /* $output = '<option value="0">' . $this->language->get('text_all_zones') . '</option>';
    if ($this->request->post['zone_id'] == $result['zone_id']) {
        $output .= ' selected="selected"';
    }*/
    $json = array();


    if (isset($this->request->get['filter_name'])) {


        $this->load->model('localisation/zone');
        if(isset($this->request->get['filter_name']) && $this->request->get['filter_name']) {
			$data = array(
				'filter_name' => trim($this->request->get['filter_name']),
				'country_id' => trim($this->request->get['coun_id'])
			);
		}else{
			$data = array(
				'country_id' => trim($this->request->get['coun_id']),
				'start' => 0,
				'limit' => 20
			);
		}

        $results = $this->model_localisation_zone->getZoneByCountry($data);

        foreach ($results as $result) {
            $json[] = array(
                'zone_id' => $result['zone_id'],
                'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))

            );
        }

    }
    $sort_order = array();

    foreach ($json as $key => $value) {
        $sort_order[$key] = $value['name'];
    }

    array_multisort($sort_order, SORT_ASC, $json);

    $this->response->setOutput(json_encode($json));


}
public function area_autocomplete()
{
  
    $json = array();


    if (isset($this->request->get['filter_name'])) {


        $this->load->model('localisation/area');
        if(isset($this->request->get['filter_name']) && $this->request->get['filter_name']) {
			$data = array(
				'filter_name' => trim($this->request->get['filter_name']),
				'zone_id' => trim($this->request->get['zone_id'])
			);
		}else{
			$data = array(
				'zone_id' => trim($this->request->get['zone_id']),
				'start' => 0,
				'limit' => 20
			);
		}

        $results = $this->model_localisation_area->getAreaByZone($data);

        foreach ($results as $result) {
            $json[] = array(
                'area_id' => $result['area_id'],
                'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))

            );
        }

    }
    $sort_order = array();

    foreach ($json as $key => $value) {
        $sort_order[$key] = $value['name'];
    }

    array_multisort($sort_order, SORT_ASC, $json);

    $this->response->setOutput(json_encode($json));


}
    private function validateForm()
	{
		if ( !$this->user->hasPermission('modify', 'localisation/geo_zone') )
		{
			$this->error['error'] = $this->language->get('error_permission');
		}

		if ( (utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 32) )
		{
			$this->error['name'] = $this->language->get('error_name');
		}

		if ( (utf8_strlen($this->request->post['description']) < 3) || (utf8_strlen($this->request->post['description']) > 255) )
		{
			$this->error['description'] = $this->language->get('error_description');
		}

		if ( $this->error && !isset($this->error['error']) )
		{
		    $this->error['warning'] = $this->language->get('error_warning');
		}
		
		return $this->error ? false : true;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'localisation/geo_zone')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		$this->load->model('localisation/tax_rate');

		foreach ($this->request->post['selected'] as $geo_zone_id) {
			$tax_rate_total = $this->model_localisation_tax_rate->getTotalTaxRatesByGeoZoneId($geo_zone_id);

			if ($tax_rate_total) {
				$this->error['warning'] = sprintf($this->language->get('error_tax_rate'), $tax_rate_total);
			}
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
	
	public function zone() {
		$output = '<option value="0">' . $this->language->get('text_all_zones') . '</option>';
		
		$this->load->model('localisation/zone');
		
		$results = $this->model_localisation_zone->getZonesByCountryId($this->request->post['country_id']);

		foreach ($results as $result) {
			$output .= '<option value="' . $result['zone_id'] . '"';

			if ($this->request->post['zone_id'] == $result['zone_id']) {
				$output .= ' selected="selected"';
			}

			$output .= '>' . $result['name'] . '</option>';
		}

		$this->response->setOutput($output);
	} 		
}
?>
