<?php

class ControllerCatalogOption extends Controller
{
	private $error = array();

	public function index()
    {
        // Product Option Image PRO module <<
        $this->language->load('module/product_option_image_pro');
        // >> Product Option Image PRO module

		$this->language->load('catalog/option');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/option');

		$this->data['limit_reached'] = (($this->model_catalog_option->getTotalOptions()+ 1) > OPTIONSLIMIT ) && PRODUCTID == '3' ;

        $this->getList();
	}


	public function insert()
    {
        // Product Option Image PRO module <<
        $this->language->load('module/product_option_image_pro');
        // >> Product Option Image PRO module

		$this->language->load('catalog/option');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/option');

		if ( $this->request->server['REQUEST_METHOD'] == 'POST')
        {

            if ( (($this->model_catalog_option->getTotalOptions()+ 1) > OPTIONSLIMIT ) && PRODUCTID == '3' ) {

                $result_json['success'] = '0';
                $result_json['errors']['error'] = $this->language->get('error_maximum_options_number');

                $this->response->setOutput(json_encode($result_json));
                return;
            }

            if ( !$this->validateForm() )
            {
                $response['success'] = '0';
                $response['errors'] = $this->error;

                $this->response->setOutput(json_encode($response));

                return;
            }

			$this->parse_options_inputs();
			
			$optionId = $this->model_catalog_option->addOption($this->request->post);

            $response['success'] = '1';
            $response['success_msg'] = $this->language->get('text_success');

            $response['data'] = $this->model_catalog_option->getOption($optionId);
            $response['data']['values'] = $this->model_catalog_option->getOptionValues($optionId);
            $response['data']['product_values'] = [];
            $response['redirect'] = '1';
            $response['to'] = $this->url->link(
                'catalog/option', '', 'SSL'
            )->format();
            $response['limit_reached'] = (($this->model_catalog_option->getTotalOptions()+ 1) > OPTIONSLIMIT ) && PRODUCTID == '3' ;

            $this->response->setOutput(json_encode($response));
            
            return;
		}

        if(PRODUCTID == 3 && ($this->model_catalog_option->getTotalOptions()+ 1) > OPTIONSLIMIT){
            $this->base = "common/base";
            $this->data = $this->language->load('error/permission');
            $this->document->setTitle($this->language->get('heading_title'));
            $this->template = 'error/permission.expand';
            $this->response->setOutput($this->render_ecwig());
            return;
        }

        $this->data['links'] = [
            'submit' => $this->url->link('catalog/option/insert', '', 'SSL'),
            'cancel' => $this->url->link(
                'catalog/option', '', 'SSL'
            ),
        ];

		$this->getForm();
	}


    public function insertValues()
    {
        $this->language->load('catalog/option');

        $this->load->model('catalog/option');

        if ( $this->request->server['REQUEST_METHOD'] == 'POST')
        {
            if ( !$this->validateValuesForm() )
            {
                $response['success'] = '0';
                $response['error'] = $this->error;

                $this->response->setOutput(json_encode($response));

                return;
            }

            $lastIds = $this->model_catalog_option->addOptionValues($this->request->post);

            $response['success'] = '1';
            $response['success_msg'] = $this->language->get('text_success');
            $response['ids'] = $lastIds;
            foreach ($lastIds as $lastId) {
                $response['data'][] = $this->model_catalog_option->getOptionValue($lastId);
            }

            $this->response->setOutput(json_encode($response));

            return;
        }
    }


    /*
    *   Look man, this function is a bit complicated
    *   it simply tries to confirm that the option values you are deleting ARE NOT linked to any products.
    *
    *   @author Michael :v:
    */
    private function validetDeleteOptions( $option_id )
    {
        $this->load->model('catalog/option');
        $this->load->model('catalog/product');

        $option_values = $this->model_catalog_option->getOptionValues( $option_id );

        $request = $this->request->post['option_value'];

        if ( count( $option_values ) <= 0 || count($request) >= count($option_values)) // check if adding or updating
        {
            return true;
        }

        $request_option_value_ids = array();

        foreach ( $request as $request_option_value )
        {
            $request_option_value_ids[] = $request_option_value['id'];
        }

        foreach ($option_values as $option_value)
        {
            $product_ids = array();
            
            $result = $this->model_catalog_product->getProductsByOptionValueId( $option_value['option_value_id'] );

            if ( count($result) <= 0 )
            {
                continue;
            }

            foreach ( $result as $res )
            {
                $product_ids[] = $res['product_id'];
            }

            if ( count($product_ids) <= 0 )
            {
                continue;
            }

            foreach ( $product_ids as $product_id )
            {
                $product_options = $this->model_catalog_product->getProductOptions( $product_id );

                if ( count($product_options) <= 0 )
                {
                    continue;
                }

                $product_option_value_ids = array();

                foreach ( $product_options as $prod_option )
                {
                    if ( $prod_option['option_id'] == $option_id )
                    {
                        foreach ( $prod_option['product_option_value'] as $product_option_value )
                        {
                            $product_option_value_ids[] = $product_option_value['option_value_id'];
                        }
                    }
                }

                foreach ( $product_option_value_ids as $product_option_value_id )
                { 
					// $product_option_value_id != "0" May be there is inserted product options with id equal zero in old databases
					// and that is occurred a problem in saving options "0 id isn't available in current options".
                    if ( $product_option_value_id != "0" && ! in_array($product_option_value_id, $request_option_value_ids) )
                    {
                        return $this->language->get('error_option_value_linked_to_product');
                    }
                }

            }
        }

        return true;

	}
	
	private function flat_recurisve_array($key,$value){
        
        if(is_array($value)){
            foreach($value as $k => $va){
                return [$key=>$this->flat_recurisve_array($k,$va)];
            }
        }
        else{
            return [$key=>$value];
        }
	}
	
	private function parse_options_inputs(){
        $inputs_data = json_decode(html_entity_decode($this->request->post['inputs']));          
        unset($this->request->post['inputs']);
        $data=[];
		foreach ($inputs_data as $input) {
            $data = array_merge($data,array($input->name => $input->value));
        }
        $post_data=http_build_query($data);
        $exploded=explode('&',$post_data);
        
        foreach ($exploded as $input) {
            $temp = array();
            parse_str($input, $temp);
            list($key, $value) = each($temp);            
            $arr=$this->flat_recurisve_array($key,$value);
            $this->request->post=array_replace_recursive($this->request->post,$arr);
        }
	}
	
	public function update()
    {
        // Product Option Image PRO module <<
        $this->language->load('module/product_option_image_pro');
        // >> Product Option Image PRO module

		$this->language->load('catalog/option');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/option');

		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

			$this->parse_options_inputs();
			
            $this->load->model('catalog/product');

            if ( $this->model_catalog_product->getTotalProductsByOptionId( $this->request->get['option_id'] ) )
            {
                // The product option being edited is linked to a product or more.
                // Let's confirm that there are no option values linked to products.

                $validate = $this->validetDeleteOptions( $this->request->get['option_id'] );

                if ( $validate !== true )
                {
                    $result_json['success'] = '0';
                    $result_json['errors']['warning'] = $validate;
                    return $this->response->setOutput(json_encode( $result_json ));
                }
			}

			$this->model_catalog_option->editOption($this->request->get['option_id'], $this->request->post);

            $result_json['success'] = '1';

			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $this->response->setOutput(json_encode($result_json));
            return;
		}

        $this->data['links'] = [
            'submit' => $this->url->link('catalog/option/update', 'option_id=' . $this->request->get['option_id'], 'SSL'),
            'cancel' => $this->url->link(
                'catalog/option', '', 'SSL'
            ),
        ];

		$this->getForm();
	}

	public function delete() {
        // Product Option Image PRO module <<
        $this->language->load('module/product_option_image_pro');
        // >> Product Option Image PRO module

		$this->language->load('catalog/option');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/option');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $option_id) {
				$this->model_catalog_option->deleteOption($option_id);
			}

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

			$this->redirect($this->url->link(
			    'catalog/option', '', 'SSL')
            );
		}

		$this->getList();
	}

	protected function getList()
    {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'o.sort_order';
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
			'href'      => $this->url->link('catalog/option', '', 'SSL'),
      		'separator' => ' :: '
   		);

   		$this->data['links'] = [
   		    'insert' => $this->url->link('catalog/option/insert', '', 'SSL'),
        ];

		$this->data['options'] = array();

		$data = array(
			'sort'  => $sort,
			'order' => $order,
			// 'start' => ($page - 1) * $this->config->get('config_admin_limit'),
			// 'limit' => $this->config->get('config_admin_limit')
		);

		$option_total = $this->model_catalog_option->getTotalOptions();

		$results = $this->model_catalog_option->getOptions($data);

		foreach ($results as $result) {
			$action = array();

			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('catalog/option/update', 'token=' . $this->session->data['token'] . '&option_id=' . $result['option_id'] . $url, 'SSL')
			);

			$this->data['options'][] = array(
				'option_id'  => $result['option_id'],
				'name'       => $result['name'],
				'sort_order' => $result['sort_order'],
				'selected'   => isset($this->request->post['selected']) && in_array($result['option_id'], $this->request->post['selected']),
				'action'     => $action
			);
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_no_results'] = $this->language->get('text_no_results');

		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_sort_order'] = $this->language->get('column_sort_order');
		$this->data['column_action'] = $this->language->get('column_action');

		$this->data['button_insert'] = $this->language->get('button_insert');
		$this->data['button_delete'] = $this->language->get('button_delete');

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

		$this->data['sort_name'] = $this->url->link('catalog/option&token=' . $this->session->data['token'] . '&sort=od.name' . $url, 'SSL');
		$this->data['sort_sort_order'] = $this->url->link('catalog/option&token=' . $this->session->data['token'] . '&sort=o.sort_order' . $url, 'SSL');

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		/*$pagination = new Pagination();
		$pagination->total = $option_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('catalog/option', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();*/

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

        if($option_total == 0){
            $this->template = 'catalog/option/empty.expand';
        }else{
            $this->template = 'catalog/option_list.expand';
        }

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	protected function getForm() {
        // Product Option Image PRO module <<
        $this->load->model('module/product_option_image_pro');
        $this->data['poip_installed'] = $this->model_module_product_option_image_pro->installed();
        $this->data['poip_settings_names'] = $this->model_module_product_option_image_pro->getSettingsNames();
        $this->data['poip_settings_values'] = $this->model_module_product_option_image_pro->getSettingsValues();
        $this->data['poip_module_name'] = $this->language->get('poip_module_name');
        $this->data['poip_saved_settings'] = $this->model_module_product_option_image_pro->getRealOptionSettings( isset($this->request->get['option_id']) ? $this->request->get['option_id'] : 0 );

        $this->data['entry_sort_order_short'] = $this->language->get('entry_sort_order_short');
        $this->data['poip_settings_select_options'] = array('0'=>$this->language->get('entry_settings_default'), '2'=>$this->language->get('entry_settings_yes'), '1'=>$this->language->get('entry_settings_no'));
        $this->load->model('module/product_option_image_pro');
        $settings_names = $this->model_module_product_option_image_pro->getSettingsNames(false);
        foreach ($settings_names as $setting_name) {
            $this->data['entry_'.$setting_name] = $this->language->get('entry_'.$setting_name);
            $this->data['entry_'.$setting_name.'_help'] = $this->language->get('entry_'.$setting_name.'_help');
        }
        $settings_values = $this->model_module_product_option_image_pro->getSettingsValues();
        $this->data['settings_values'] = $settings_values;


 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

 		if (isset($this->error['name'])) {
			$this->data['error_name'] = $this->error['name'];
		} else {
			$this->data['error_name'] = array();
		}

 		if (isset($this->error['option_value'])) {
			$this->data['error_option_value'] = $this->error['option_value'];
		} else {
			$this->data['error_option_value'] = array();
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
			'href'      => $this->url->link('catalog/option', '', 'SSL'),
      		'separator' => ' :: '
   		);

        $this->data['breadcrumbs'][] = array(
            'text'      => !isset($this->request->get['option_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href'      => $this->url->link('catalog/option', '', 'SSL'),
            'separator' => ' :: '
        );

		if (!isset($this->request->get['option_id'])) {
			$this->data['action'] = $this->url->link('catalog/option/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('catalog/option/update', 'token=' . $this->session->data['token'] . '&option_id=' . $this->request->get['option_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link(
		    //                'catalog/option', '', 'SSL'
		    'catalog/option', '', 'SSL'
        );

		if (isset($this->request->get['option_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
      		$option_info = $this->model_catalog_option->getOption($this->request->get['option_id']);
    	}


        $this->data['default_image'] = $this->config->get('no_image');

		$this->data['token'] = $this->session->data['token'];

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['option_description'])) {
			$this->data['option_description'] = $this->request->post['option_description'];
		} elseif (isset($this->request->get['option_id'])) {
			$this->data['option_description'] = $this->model_catalog_option->getOptionDescriptions($this->request->get['option_id']);
		} else {
			$this->data['option_description'] = array();
		}

		if (isset($this->request->post['type'])) {
			$this->data['type'] = $this->request->post['type'];
		} elseif (!empty($option_info)) {
			$this->data['type'] = $option_info['type'];
		} else {
			$this->data['type'] = '';
		}
		
		if (isset($this->request->post['sort_order'])) {
			$this->data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($option_info)) {
			$this->data['sort_order'] = $option_info['sort_order'];
		} else {
			$this->data['sort_order'] = '';
		}

		if (isset($this->request->post['option_value'])) {
			$option_values = $this->request->post['option_value'];
		} elseif (isset($this->request->get['option_id'])) {
			$option_values = $this->model_catalog_option->getOptionValueDescriptions($this->request->get['option_id']);
		} else {
			$option_values = array();
		}

		$this->load->model('tool/image');

		$this->data['option_values'] = array();

		foreach ($option_values as $option_value) {
			if ($option_value['image']) {
				$image = $option_value['image'];
                $thumb = $this->model_tool_image->resize($image, 150, 150);
			} else {
				$image = 'no_image.jpg';
                $thumb = $this->model_tool_image->resize($image, 150, 150);
			}

			$this->data['option_values'][] = array(
				'option_value_id'          => $option_value['option_value_id'],
				'option_value_description' => $option_value['option_value_description'],
				'image'                    => $image,
                'thumb'                    => $thumb,
				'sort_order'               => $option_value['sort_order'],
				'valuable_type'            => $option_value['valuable_type'],
				'valuable_id'              => $option_value['valuable_id'],
			);
		}

		$this->data['optionTypes'] = [
		    'text_choose' => [
                'select',
                'radio',
                'checkbox',
                'image',
            ],
            'text_input' => [
                'text',
                'textarea',
            ],
            'text_file' => [
                'file'
            ],
            'text_date' => [
                'date',
                'time',
                'datetime',
            ]
		];
		
		if (\Extension::isInstalled('product_builder') && $this->config->get('product_builder')['status']) {
            $this->data['product_builder_installed'] = true;
            $this->data['optionTypes']['text_choose'][] = 'product';
            if (isset($this->request->post['custom_option'])) {
                $this->data['custom_option'] = $this->request->post['custom_option'];
            } elseif (!empty($option_info)) {
                $this->data['custom_option'] = $option_info['custom_option'];
            } else {
                $this->data['custom_option'] = '';
            }
		}
		
		$this->data['languagesById'] = array_column($this->data['languages'], null, 'language_id');

        //$this->document->addScript('view/javascript/cube/dropzone.min.js');

        $this->document->addScript('view/javascript/cube/scripts.js');

		$this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);

		// append products for option values 
		$this->load->model('catalog/product');
		$this->data['products'] = $this->model_catalog_product->getProductsFields(['name']);

		$this->template = 'catalog/option_form.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}

	
    private function validateForm()
    {
		if ( ! $this->user->hasPermission('modify', 'catalog/option') )
        {
			$this->error['error'] = $this->language->get('error_permission');
		}

		foreach ( $this->request->post['option_description'] as $language_id => $value )
        {

            if ((utf8_strlen(trim($value['name'] ," "))< 2) || (utf8_strlen(trim($value['name'] ," ")) > 128))
            {
				$this->error['option_name_' . $language_id] = $this->language->get('error_name');
			}
		}

		if ( ($this->request->post['type'] == 'select' || $this->request->post['type'] == 'radio' || $this->request->post['type'] == 'checkbox') && !isset($this->request->post['option_value']) )
        {
			$this->error['warning'] = $this->language->get('error_type');
		}

		if ( isset($this->request->post['option_value']) )
        {
			foreach ($this->request->post['option_value'] as $option_value_id => $option_value) {
                foreach ($option_value['option_value_description'] as $language_id => $option_value_description) {
                    if ((utf8_strlen($option_value_description['name']) < 1) || (utf8_strlen($option_value_description['name']) > 128)) {
					    $this->error['option_value_'.$option_value_id.'_'.$language_id] = $this->language->get('error_option_value');
					}
				}
			}
        }

		if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
	}


    protected function validateValuesForm()
    {
        if (!$this->user->hasPermission('modify', 'catalog/option')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (isset($this->request->post['option_value'])) {
            foreach ($this->request->post['option_value'] as $option_value_id => $option_value) {
                foreach ($option_value['option_value_description'] as $language_id => $option_value_description) {
                    if ((utf8_strlen($option_value_description['name']) < 1) || (utf8_strlen($option_value_description['name']) > 128)) {
                        $this->error['option_value'][$option_value_id][$language_id] = $this->language->get('error_option_value');
                    }

                    if (utf8_strlen(trim($option_value_description['name'])) === 0) {
                        $this->error['option_value'][$option_value_id][$language_id] = $this->language->get('error_warning');
                    }
                }
            }
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }


	protected function validateDelete($ids)
    {
		if (!$this->user->hasPermission('modify', 'catalog/option')) {
			$this->error['error'] = $this->language->get('error_permission');
            return array('error' => $this->error['error']);
		}

		$this->load->model('catalog/product');
        $this->language->load('catalog/option');

		foreach ($ids as $option_id) {
			$product_total = $this->model_catalog_product->getTotalProductsByOptionId($option_id);

			if ( $product_total )
            {
                return array('error' => sprintf($this->language->get('error_product'), $product_total));
			}
		}

		return true;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
            // Product Option Image PRO module <<
            $this->language->load('module/product_option_image_pro');
            // >> Product Option Image PRO module

			$this->language->load('catalog/option');

			$this->load->model('catalog/option');

			$this->load->model('tool/image');

			$data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 20
			);

			$options = $this->model_catalog_option->getOptions($data);

			foreach ($options as $option) {
				$option_value_data = array();

				if ($option['type'] == 'select' || $option['type'] == 'radio' || $option['type'] == 'checkbox' || $option['type'] == 'image' || $option['type'] == 'product') {
					$option_values = $this->model_catalog_option->getOptionValues($option['option_id']);
					// print_r($option_values);  exit();

					foreach ($option_values as $option_value) {
						if ($option_value['image']) {
							$image = $option_value['image'];
						} else {
							$image = '';
						}

						$option_value_data[] = array(
							'option_value_id' => $option_value['option_value_id'],
							'name'            => html_entity_decode($option_value['name'], ENT_QUOTES, 'UTF-8'),
							'image'           => $this->model_tool_image->resize($image, 50, 50),
						);
					}

					$sort_order = array();

					foreach ($option_value_data as $key => $value) {
						$sort_order[$key] = $value['name'];
					}

					array_multisort($sort_order, SORT_ASC, $option_value_data);
				}

				$type = '';

				if ($option['type'] == 'select' || $option['type'] == 'radio' || $option['type'] == 'checkbox' || $option['type'] == 'image' || $option['type'] == 'product' ) {
					$type = $this->language->get('text_choose');
				}

				if ($option['type'] == 'text' || $option['type'] == 'textarea') {
					$type = $this->language->get('text_input');
				}

				if ($option['type'] == 'file') {
					$type = $this->language->get('text_file');
				}

				if ($option['type'] == 'date' || $option['type'] == 'datetime' || $option['type'] == 'time') {
					$type = $this->language->get('text_date');
				}

				$json[] = array(
					'option_id'    => $option['option_id'],
					'name'         => strip_tags(html_entity_decode($option['name'], ENT_QUOTES, 'UTF-8')),
					'category'     => $type,
					'type'         => $option['type'],
					'option_value' => $option_value_data
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

    public function dtDelete()
    {
        $this->load->model("catalog/option");
        if(isset($this->request->post["ids"])) {
            $ids = $this->request->post["ids"];
            $validate = $this->validateDelete($ids);

            if ( $validate !== true )
            {
                $validate['success']  = '0';

                $this->response->setOutput( json_encode($validate) );
                return;
            }

            foreach ($ids as $option_id)
            {
                $this->model_catalog_option->deleteOption($option_id);
            }

            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_delete_success');
        } else {
            $result_json['success'] = '0';
            $result_json['error'] = $this->language->get('text_delete_error');
        }

        $this->response->setOutput(json_encode($result_json));
        return;
	}
	
	public function dtUnlink()
    {
		$this->language->load('catalog/option');
		$this->load->model("catalog/product");
        if(isset($this->request->post["ids"])) {
            $ids = $this->request->post["ids"];

            foreach ($ids as $option_id)
            {
                $this->model_catalog_product->deleteProductOptions($option_id);
            }

            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_unlink_success');
        } else {
            $result_json['success'] = '0';
            $result_json['error'] = $this->language->get('text_unlink_error');
        }

        $this->response->setOutput(json_encode($result_json));
        return;
    }


    public function updateSortOrder()
    {
        $this->initializer([
            'catalog/option',
        ]);

        foreach ($this->request->post['data'] as $id => $sort) {
            $this->option->updateSortOrder($id, $sort);
        }

        $result_json['success'] = '1';

        $result_json['success_msg'] = $this->language->get('text_success');

        $this->response->setOutput(json_encode($result_json));

        return;

    }
}
?>