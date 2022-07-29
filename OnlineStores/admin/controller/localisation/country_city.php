<?php 
class ControllerLocalisationCountryCity extends Controller {
	private $error = array();
 
    public function getCountryDetails()
    {
        if ( $this->request->server['REQUEST_METHOD'] != 'POST' || !isset( $this->request->post['country_id'] ) )
        {
            return false;
        }
        
        $country_id = (int) $this->request->post['country_id'];
        $this->load->model('localisation/country');

        $country = $this->model_localisation_country->getCountry($country_id);
        $country_locale = $this->model_localisation_country->getAllCountryLocale($country_id);
        $locales = array();

        foreach ($country_locale as $locale)
        {
            $locales[$locale['lang_id']] = $locale['name'];
        }

        $result = array('country' => $country, 'locale' => $locales);
        $this->response->setOutput(json_encode($result));
    }

	public function index() {
		$this->language->load('localisation/country');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('localisation/country');
		
		$this->getList();
	}

	public function insert() {
		$this->language->load('localisation/country');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('localisation/country');
		
		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
            if ( !$this->validateForm() )
            {
                $result_json['success'] = '0';

                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

            // This bit of logic is a bit complicated but it had to be this way to add countries locale names.
            // It checks if the post request has keys that contain the word countryLang
            // if yes then it creates a key called names in the data array and add an array to it
            // with the key of the language id and the value of the country name in that language id.
            // Exmaple of the end result:
            // $data['names']['1'] = 'Egypt';
            // $data['names']['2'] = 'مصر';

            $data = array();
            foreach ($this->request->post as $key => $value)
            {
                if ( preg_match('/countryLang.*/', $key) )
                {
                    $lang_id = explode('countryLang', $key)[1];
                    $data['names'][$lang_id] = $value;
                }
                else
                {
                    $data[$key] = $value;
                }
            }

			$country_id = $this->model_localisation_country->addCountry($data);

			// add data to log_history
			$this->load->model('setting/audit_trail');
			$pageStatus = $this->model_setting_audit_trail->getPageStatus("lang_settings");
			if($pageStatus){
				$log_history['action'] = 'add';
				$log_history['reference_id'] = $country_id;
				$log_history['old_value'] = NULL;
				$log_history['new_value'] = json_encode($data,JSON_UNESCAPED_UNICODE);
				$log_history['type'] = 'country';
				$this->load->model('loghistory/histories');
				$this->model_loghistory_histories->addHistory($log_history);
			}

			$result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_success');

            $this->response->setOutput(json_encode($result_json));
            return;
        }

		$this->getForm();
	}
        //ahmed
    public function handler()
    {
        $this->load->model('localisation/country');
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;
        $lang_id = $this->config->get('config_language_id');

        $start = (int) $request['start'];
        $length = (int) $request['length'];

        $columns = array
        (
            0 => 'country_id',
            1 => 'name',
            2 => 'iso_code_2',
            3 => 'iso_code_3',
            4 => 'status',
        );

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->model_localisation_country->handler($start, $length, $lang_id, $search, $orderColumn, $orderType);

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

	public function update()
    {

        if ( !isset( $this->request->get['country_id'] ) )
        {
            $this->redirect($this->url->link('localisation/country/insert'), 'SSL');
        }

		$this->language->load('localisation/country');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('localisation/country');
		
		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
            if ( !$this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
            }
            else
            {
				$oldValue = $this->model_localisation_country->getCountryData($this->request->get['country_id']);

        		$this->model_localisation_country->editCountry($this->request->get['country_id'], $this->request->post);
                //ahmed
                $data = $this->request->post;
                $keys = array();
                foreach ( array_keys($data) as $key )
                {
                    if ( preg_match('/countryLang.*/', $key) )
                    {
                        $keys[] = array('key' => $key, 'value' => $data[$key], 'lang_id' => explode('Lang', $key)[1]);
                    }
                }

                $country_id = $this->request->get['country_id'];



                foreach ($keys as $key)
                {
                    $existBefore = $this->model_localisation_country->getCountryLocale($country_id, $key['lang_id']);

                    if (count($existBefore) > 0)
                    {
						$oldValue['names'][$key['lang_id']] = $existBefore['name'];
                        $this->model_localisation_country->updateCountryLocale($country_id, $key['lang_id'], $key['value']);
						$data['names'][$key['lang_id']] = $key['value'];
                    }
                    else
                    {
                        $this->model_localisation_country->insertCountryLocale($country_id, $key['lang_id'], $key['value']);
						$data['names'][$key['lang_id']] = $key['value'];
                    }
                }

				// add data to log_history
				$this->load->model('setting/audit_trail');
				$pageStatus = $this->model_setting_audit_trail->getPageStatus("lang_settings");
				if($pageStatus){
					$log_history['action'] = 'update';
					$log_history['reference_id'] = $country_id;
					$log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
					$log_history['new_value'] = json_encode($data,JSON_UNESCAPED_UNICODE);
					$log_history['type'] = 'country';
					$this->load->model('loghistory/histories');
					$this->model_loghistory_histories->addHistory($log_history);
				}

                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_success');
            }

            $this->response->setOutput(json_encode($result_json));
            return;
		}

        $this->getForm();
	}

    public function dtCountryUpdateStatus()
    {
        $this->load->model("localisation/country");
        if (isset($this->request->post["id"]) && isset($this->request->post["status"])) {
            $id = $this->request->post["id"];
            $status = $this->request->post["status"];

//            $countryData = $this->model_localisation_country->getCountry($id);
            $countryData = $this->model_localisation_country->getCountryData($id);
			$oldValue = $countryData;
            $countryData["status"] = $status;

			$allCountryLocale = $this->model_localisation_country->getAllCountryLocale($id);
			if(count($allCountryLocale) > 0){
				foreach ($allCountryLocale as $key=>$locale){
					$oldValue['names'][$locale['lang_id']] = $locale['name'];
				}
			}

            $this->model_localisation_country->editCountry($id, $countryData);

			// add data to log_history
			$this->load->model('setting/audit_trail');
			$pageStatus = $this->model_setting_audit_trail->getPageStatus("lang_settings");
			if($pageStatus){
				$log_history['action'] = 'update';
				$log_history['reference_id'] = $id;
				$log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
				$log_history['new_value'] = json_encode($countryData,JSON_UNESCAPED_UNICODE);
				$log_history['type'] = 'country';
				$this->load->model('loghistory/histories');
				$this->model_loghistory_histories->addHistory($log_history);
			}

            $response['success'] = '1';
            $response['success_msg'] = $this->language->get('message_updated_successfully');
        } else {
            $response['success'] = '0';
            $response['errors'] = $this->language->get('notification_unknoen_error');
        }
        $this->response->setOutput(json_encode($response));
        return;
    }

	public function delete()
    {
        if ( ( !isset($this->request->post['id']) && !isset($this->request->post['selected']) ) || !$this->validateDelete() )
        {
            $json_result['success'] = '0';
            $json_result['error'] = ':(';
        }
        else
        {
            $this->language->load('localisation/country');
            $this->document->setTitle($this->language->get('heading_title'));
            $this->load->model('localisation/country');
            $country_id = $this->request->post['id'];

			$this->load->model('setting/audit_trail');
			$pageStatus = $this->model_setting_audit_trail->getPageStatus("lang_settings");

            if (is_array($country_id))
            {
                foreach ($country_id as $id)
                {
					$oldValue = $this->model_localisation_country->getCountryData($id);
					$allCountryLocale = $this->model_localisation_country->getAllCountryLocale($id);
					if(count($allCountryLocale) > 0){
						foreach ($allCountryLocale as $key=>$locale){
							$oldValue['names'][$locale['lang_id']] = $locale['name'];
						}
					}
					if($pageStatus){
						$log_history['action'] = 'delete';
						$log_history['reference_id'] = $id;
						$log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
						$log_history['new_value'] = NULL;
						$log_history['type'] = 'country';
						$this->load->model('loghistory/histories');
						$this->model_loghistory_histories->addHistory($log_history);
					}

                    $this->model_localisation_country->deleteCountry($id);
                }
            }
            else
            {
				if($pageStatus){
					$oldValue = $this->model_localisation_country->getCountryData($country_id);
					$allCountryLocale = $this->model_localisation_country->getAllCountryLocale($country_id);
					if(count($allCountryLocale) > 0){
						foreach ($allCountryLocale as $key=>$locale){
							$oldValue['names'][$locale['lang_id']] = $locale['name'];
						}
					}
					$log_history['action'] = 'delete';
					$log_history['reference_id'] = $country_id;
					$log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
					$log_history['new_value'] = NULL;
					$log_history['type'] = 'country';
					$this->load->model('loghistory/histories');
					$this->model_loghistory_histories->addHistory($log_history);
				}

                $this->model_localisation_country->deleteCountry($country_id);
            }

            $json_result['success'] = '1';
            $json_result['success_msg'] = $this->language->get('text_success');
        }

        $this->response->setOutput(json_encode($json_result));
        return;
	}

	protected function getList() {
            //ahmed
                $this->data['genericUpdate'] = $this->url->link('localisation/country/update', 'token=' . $this->session->data['token'] . '&country_id='  . $url, 'SSL');
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
			'href'      => $this->url->link('localisation/country', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['insert'] = $this->url->link('localisation/country/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('localisation/country/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');
		 
		$this->data['countries'] = array();

		$data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);
		
		$country_total = $this->model_localisation_country->getTotalCountries();
		
		$results = $this->model_localisation_country->getCountries($data);
		
        $this->load->model('localisation/language');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        $this->data['all_countries_localed'] = $this->model_localisation_country->getAllCountriesLocale( $this->config->get('config_language_id') );

		foreach ($results as $result) {
			$action = array();
			
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('localisation/country/update', 'token=' . $this->session->data['token'] . '&country_id=' . $result['country_id'] . $url, 'SSL')
			);

			$this->data['countries'][] = array(
				'country_id' => $result['country_id'],
				'name'       => $result['name'] . (($result['country_id'] == $this->config->get('config_country_id')) ? $this->language->get('text_default') : null),
				'iso_code_2' => $result['iso_code_2'],
				'iso_code_3' => $result['iso_code_3'],
				'selected'   => isset($this->request->post['selected']) && in_array($result['country_id'], $this->request->post['selected']),				
				'action'     => $action
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
		
		$this->data['sort_name'] = $this->url->link('localisation/country', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');
		$this->data['sort_iso_code_2'] = $this->url->link('localisation/country', 'token=' . $this->session->data['token'] . '&sort=iso_code_2' . $url, 'SSL');
		$this->data['sort_iso_code_3'] = $this->url->link('localisation/country', 'token=' . $this->session->data['token'] . '&sort=iso_code_3' . $url, 'SSL');
		
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
												
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $country_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('localisation/country', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();
		
		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->template = 'localisation/country_city_list.expand';
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
			'href'      => $this->url->link('localisation/country_city', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
		
        $this->data['breadcrumbs'][] = array(
            'text'      => !isset($this->request->get['country_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update') ,
            'href'      => $this->url->link('localisation/country', 'token=' . $this->session->data['token'] . $url, 'SSL'),
            'separator' => ' :: '
        );

		if (!isset($this->request->get['country_id'])) { 
			$this->data['action'] = $this->url->link('localisation/country_city/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('localisation/country_city/update', 'token=' . $this->session->data['token'] . '&country_id=' . $this->request->get['country_id'] . $url, 'SSL');
		}
		
		$this->data['cancel'] = $this->url->link('localisation/country_city', 'token=' . $this->session->data['token'] . $url, 'SSL');

        $this->load->model('localisation/language');

        $languages = $this->data['languages'] = $this->model_localisation_language->getLanguages();

		if ( isset($this->request->get['country_id']) && $this->request->server['REQUEST_METHOD'] != 'POST' )
        {

			$country_info = $this->model_localisation_country->getCountryWithAllDetails($this->request->get['country_id']);            
            //ahmed
            $countryLocale = $this->model_localisation_country->getAllCountryLocale($this->request->get['country_id']);
            $countryLocaleNames = array();
            foreach ($languages as $index => $lang)
            {
                $value = array_values(array_filter($countryLocale, function ($record) use($lang)
                {
                    return $record['lang_id'] == $lang['language_id'];
                }));
                $value = count($value > 0) ? $value[0]['name'] : '';
                $countryLocaleNames[$index] = $value;
            }

            $this->data['countryLocaleNames'] = $countryLocaleNames;
		}

		if (isset($this->request->post['name'])) {
			$this->data['name'] = $this->request->post['name'];
		} elseif (!empty($country_info)) {
			$this->data['name'] = $country_info['name'];
		} else {
			$this->data['name'] = '';
		}

		if (isset($this->request->post['iso_code_2'])) {
			$this->data['iso_code_2'] = $this->request->post['iso_code_2'];
		} elseif (!empty($country_info)) {
			$this->data['iso_code_2'] = $country_info['iso_code_2'];
		} else {
			$this->data['iso_code_2'] = '';
		}

		if (isset($this->request->post['iso_code_3'])) {
			$this->data['iso_code_3'] = $this->request->post['iso_code_3'];
		} elseif (!empty($country_info)) {
			$this->data['iso_code_3'] = $country_info['iso_code_3'];
		} else {
			$this->data['iso_code_3'] = '';
		}

		if (isset($this->request->post['address_format'])) {
			$this->data['address_format'] = $this->request->post['address_format'];
		} elseif (!empty($country_info)) {
			$this->data['address_format'] = $country_info['address_format'];
		} else {
			$this->data['address_format'] = '';
		}

		if (isset($this->request->post['postcode_required'])) {
			$this->data['postcode_required'] = $this->request->post['postcode_required'];
		} elseif (!empty($country_info)) {
			$this->data['postcode_required'] = $country_info['postcode_required'];
		} else {
			$this->data['postcode_required'] = 0;
        }
        
        if (isset($this->request->post['phonecode'])) {
			$this->data['phonecode'] = $this->request->post['phonecode'];
		} elseif (!empty($country_info)) {
			$this->data['phonecode'] = $country_info['phonecode'];
		} else {
			$this->data['phonecode'] = '';
		}
				
		if (isset($this->request->post['status'])) {
			$this->data['status'] = $this->request->post['status'];
		} elseif (!empty($country_info)) {
			$this->data['status'] = $country_info['status'];
		} else {
			$this->data['status'] = '1';
		}

        $this->language->load('localisation/country');

        $this->data['column_address_format'] = $this->language->get('column_address_format');
        $this->data['entry_address_format'] = $this->language->get('entry_address_format');

		$this->template = 'localisation/country_form.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render_ecwig());
	}

	protected function validateForm() {
		if ( ! $this->user->hasPermission('modify', 'localisation/country_city') )
        {
			$this->error['error'] = $this->language->get('error_permission');
		}

        $data = $this->request->post;
        $keys = array();

        foreach ( array_keys($data) as $key )
        {
            if ( strstr($key, 'countryLang') )
            {
                $keys[] = $key;
            }
        }

        if ( count($keys) <= 0 )
        {
            $this->error['warning'] = $this->language->get('error_name');
        }
        else
        {
            foreach ( $keys as $key )
            {
                if ( (utf8_strlen($this->request->post[$key]) < 3)  || ( utf8_strlen($this->request->post[$key] ) > 128) )
                {
                    $this->error[$key] = $this->language->get('error_name');
                }
            }
        }

        if ( ! $this->request->post['iso_code_2'] )
        {
            $this->error['iso_code_2'] = $this->language->get('error_field_cant_be_empty');
        }

        if ( ! $this->request->post['iso_code_3'] )
        {
            $this->error['iso_code_3'] = $this->language->get('error_field_cant_be_empty');
        }

        if ( ! $this->request->post['phonecode'] )
        {
            $this->error['phonecode'] = $this->language->get('error_field_cant_be_empty');
        }
        // if ( ! $this->request->post['address_format'] )
        // {
        //     $this->error['address_format'] = $this->language->get('error_field_cant_be_empty');
        // }

		if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', "localisation/country_city")) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		$this->load->model('setting/store');
		$this->load->model('sale/customer');
		$this->load->model('sale/affiliate');
		$this->load->model('localisation/zone');
		$this->load->model('localisation/geo_zone');
		
		foreach ($this->request->post['selected'] as $country_id) {
			if ($this->config->get('config_country_id') == $country_id) {
				$this->error['warning'] = $this->language->get('error_default');
			}
			
			$store_total = $this->model_setting_store->getTotalStoresByCountryId($country_id);

			if ($store_total) {
				$this->error['warning'] = sprintf($this->language->get('error_store'), $store_total);
			}
			
			$address_total = $this->model_sale_customer->getTotalAddressesByCountryId($country_id);
	
			if ($address_total) {
				$this->error['warning'] = sprintf($this->language->get('error_address'), $address_total);
			}

			$affiliate_total = $this->model_sale_affiliate->getTotalAffiliatesByCountryId($country_id);
	
			if ($affiliate_total) {
				$this->error['warning'] = sprintf($this->language->get('error_affiliate'), $affiliate_total);
			}
							
			$zone_total = $this->model_localisation_zone->getTotalZonesByCountryId($country_id);
		
			if ($zone_total) {
				$this->error['warning'] = sprintf($this->language->get('error_zone'), $zone_total);
			}
		
			$zone_to_geo_zone_total = $this->model_localisation_geo_zone->getTotalZoneToGeoZoneByCountryId($country_id);
		
			if ($zone_to_geo_zone_total) {
				$this->error['warning'] = sprintf($this->language->get('error_zone_to_geo_zone'), $zone_to_geo_zone_total);
			}
		}
	
		if (!$this->error) {
			return true;
		} else {
			return false;
		}
                
	}
        
    //ahmed
    public function removeCountry() {
        $country_id = $_POST['country_id'];
        if ($this->deleteValidationCheck($country_id)) {
            $this->load->model('localisation/country');
			$this->load->model('setting/audit_trail');
			$pageStatus = $this->model_setting_audit_trail->getPageStatus("lang_settings");
			if($pageStatus){
				$oldValue = $this->model_localisation_country->getCountryData($country_id);
				$allCountryLocale = $this->model_localisation_country->getAllCountryLocale($country_id);
				if(count($allCountryLocale) > 0){
					foreach ($allCountryLocale as $key=>$locale){
						$oldValue['names'][$locale['lang_id']] = $locale['name'];
					}
				}
				$log_history['action'] = 'delete';
				$log_history['reference_id'] = $country_id;
				$log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
				$log_history['new_value'] = NULL;
				$log_history['type'] = 'country';
				$this->load->model('loghistory/histories');
				$this->model_loghistory_histories->addHistory($log_history);
			}
            $this->model_localisation_country->deleteCountry($country_id);
        } else {
            $this->language->load('localisation/country');
            $errors = array();
            foreach ($this->error as $err) {
                $errors[] = $this->language->get("$err");
            }
            print_r($errors);
        }
    }

    public function removeCountryBulk() {
        $country_ids = $_POST['country_ids'];
        if ($this->deleteValidationCheck($country_ids)) {
            $this->load->model('localisation/country');
			$this->load->model('setting/audit_trail');
			$pageStatus = $this->model_setting_audit_trail->getPageStatus("lang_settings");

            foreach ($country_ids as $id) {
				if($pageStatus){
					$oldValue = $this->model_localisation_country->getCountryData($id);
					$allCountryLocale = $this->model_localisation_country->getAllCountryLocale($id);
					if(count($allCountryLocale) > 0){
						foreach ($allCountryLocale as $key=>$locale){
							$oldValue['names'][$locale['lang_id']] = $locale['name'];
						}
					}
					$log_history['action'] = 'delete';
					$log_history['reference_id'] = $id;
					$log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
					$log_history['new_value'] = NULL;
					$log_history['type'] = 'country';
					$this->load->model('loghistory/histories');
					$this->model_loghistory_histories->addHistory($log_history);
				}
                $this->model_localisation_country->deleteCountry($id);
            }
        } else {
            $this->language->load('localisation/country');
            $errors = array();
            foreach ($this->error as $err) {
                $errors[] = $this->language->get("$err");
            }
            print_r($errors);
        }
    }

    public function deleteValidationCheck($countries) {
        if (is_array($countries)) {
            foreach ($countries as $country_id) {
                if (!$this->genericDeleteValidation($country_id)) {
                    return false;
                }
            }
            return true;
        } else {
            return $this->genericDeleteValidation($countries);
        }
    }

    public function genericDeleteValidation($country_id) {
        if (!$this->user->hasPermission('modify', 'localisation/country')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $this->load->model('setting/store');
        $this->load->model('sale/customer');
        $this->load->model('sale/affiliate');
        $this->load->model('localisation/zone');
        $this->load->model('localisation/geo_zone');

        if ($this->config->get('config_country_id') == $country_id) {
            $this->error['warning'] = $this->language->get('error_default');
        }

        $store_total = $this->model_setting_store->getTotalStoresByCountryId($country_id);

        if ($store_total) {
            $this->error['warning'] = sprintf($this->language->get('error_store'), $store_total);
        }

        $address_total = $this->model_sale_customer->getTotalAddressesByCountryId($country_id);

        if ($address_total) {
            $this->error['warning'] = sprintf($this->language->get('error_address'), $address_total);
        }

        $affiliate_total = $this->model_sale_affiliate->getTotalAffiliatesByCountryId($country_id);

        if ($affiliate_total) {
            $this->error['warning'] = sprintf($this->language->get('error_affiliate'), $affiliate_total);
        }

        $zone_total = $this->model_localisation_zone->getTotalZonesByCountryId($country_id);

        if ($zone_total) {
            $this->error['warning'] = sprintf($this->language->get('error_zone'), $zone_total);
        }

        $zone_to_geo_zone_total = $this->model_localisation_geo_zone->getTotalZoneToGeoZoneByCountryId($country_id);

        if ($zone_to_geo_zone_total) {
            $this->error['warning'] = sprintf($this->language->get('error_zone_to_geo_zone'), $zone_to_geo_zone_total);
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function resetCountriesAndCities()
	{
		$this->load->model('localisation/country');
		$result = ['success' => $this->model_localisation_country->reset()];
		$this->response->setOutput(json_encode($result));

	}

	public function bulkUpdate()
	{

		$countries = $this->request->get['countries'];
		$status = $this->request->get['status'];


		if (empty($countries) || $countries == null || !is_array($countries)
			|| !in_array($status, ['0', '1', 'on', 'off', 1, 0])) {

			$result = array('data' => 'error');
			return $this->response->setOutput(json_encode($result));
		}


		$this->load->model('localisation/country');
		$this->model_localisation_country->bulkUpdate($countries, $status);

		$result = array('data' => 'success');
		return $this->response->setOutput(json_encode($result));
	}
}
?>
