<?php
include DIR_APPLICATION . '/view/javascript/CustomEmailTemplates/compatibility.php';

class ControllerModuleCustomEmailTemplates extends CompatibilityAdmin {
	const MODULE_VERSION = "v4.4";

	private $error = array();
	private $templates = array(
		'order' => array(),
		'return' => array(),
		'mail' => array(),
		'customer' => array('register', 'register.approval', 'password.reset', 'approve', 'voucher', 'balance'),
		'affiliate' => array('register', 'register.approval', 'password.reset', 'approve', 'add.transaction'),
		'contact' => array('confirmation'),
		'reviews' => array('added'),
		'seller'  => array('order','enable','disable','contact'),
		'product' => array('approve', 'reject')
 		//'cron' => array('invoice')
	);
	private $fields = array(
		'status' => array('default' => '0', 'decode' => false, 'required' => true),
		'custom_invoice_status' => array('default' => '0', 'decode' => false, 'required' => false),
		'cron_invoice_status' => array('default' => '0', 'decode' => false, 'required' => false),
		'generate_invoice_number' => array('default' => array(), 'decode' => false, 'required' => false),
		'attach_invoice' => array('default' => array(), 'decode' => false, 'required' => false),
		'invoice_template' => array('default' => 'default.tpl', 'decode' => false, 'required' => false),
		'notify_admin_status' => array('default' => '0', 'decode' => false, 'required' => false),
		'bcc' => array('default' => '', 'decode' => false, 'required' => false),
		'track_status' => array('default' => '0', 'decode' => false, 'required' => false),
		'date_format' => array('default' => 'Y/m/D h:i:s', 'decode' => false, 'required' => false),
		'plain_text_status' => array('default' => '0', 'decode' => false, 'required' => false),
		'layout_width' => array('default' => '680px', 'decode' => false, 'required' => false),
		'text_color' => array('default' => '#222222', 'decode' => false, 'required' => false),
		'link_color' => array('default' => '#1978ab', 'decode' => false, 'required' => false),
		'background_image' => array('default' => '', 'image' => true, 'decode' => false, 'required' => false),
		'background_color' => array('default' => '#ffffff', 'decode' => false, 'required' => false),
		'background_repeat' => array('default' => 'repeat', 'decode' => false, 'required' => false),
		'mlanguage' => array('default' => array(
			'tax_id' => 'Tax ID:',
			'company_id' => 'Company ID:',
		), 'decode' => false, 'required' => false),
		'table' => array('default' => array(
			'font_size' => '12px',
			'font_color' => '#222222',
			'font_weight' => 'bold',
			'background' => '#efefef',
			'padding_top' => '7px',
			'padding_right' => '7px',
			'padding_bottom' => '7px',
			'padding_left' => '7px',
			'border_style' => 'Solid',
			'border_color' => '#dddddd',
			'border_size' => '1px'
		), 'decode' => false, 'required' => false),
		'showcase' => array('default' => array(
			'product_image_width' => '120',
			'product_image_height' => '120',
			'product_name_size' => '14px',
			'product_name_color' => '#1e91cf',
			'product_name_weight' => 'bold',
			'product_description_size' => '12px',
			'product_description_color' => '#000000',
			'product_description_weight' => 'normal',
			'button_size' => '12px',
			'button_color' => '#ffffff',
			'button_background' => 'red',
			'text' => 'View product'
		), 'decode' => false, 'required' => false),
		'taxes_section' => array('default' => array(
			'title' => array(
				'align' => 'left'
			),
			'rate' => array(
				'text'  => 'Tax rate',
				'align' => 'left'
			),
			'amount' => array(
				'text'  => 'Amount',
				'align' => 'right'
			)
		), 'decode' => false, 'required' => false),
		'totals_section' => array('default' => array(
			'title' => array(
				'align' => 'right'
			),
			'amount' => array(
				'align' => 'right'
			)
		), 'decode' => false, 'required' => false),
		'comment_section' => array('default' => array(
			'text'  => 'Comment',
			'align' => 'left',
			'table' => '1'
		), 'decode' => false, 'required' => false),
		'products_section' => array('default' => array(
			'option_status' => '1',
			'totals_status' => '1',
			'column' => array(
				'image' => array(
					'status'       => '1',
					'align'        => 'center',
					'image_width'  => '80',
					'image_height' => '80',
					'text'         => 'Image'
				),
				'product' => array(
					'status'      => '1',
					'align'       => 'left',
					'text'        => 'Product name',
					'link_status' => '0'
				),
				'quantity' => array(
					'status' => '1',
					'align'  => 'center',
					'text'   => 'Quantity'
				),
				'model' => array(
					'status' => '1',
					'align'  => 'left',
					'text'   => 'Model'
				),
				'sku' => array(
					'status' => '0',
					'align'  => 'center',
					'text'   => 'SKU'
				),
				'upc' => array(
					'status' => '0',
					'align'  => 'center',
					'text'   => 'UPC'
				),
				'attribute' => array(
					'status' => '0',
					'align'  => 'left',
					'text'   => 'Attribute'
				),
				'price' => array(
					'status' => '1',
					'align'  => 'right',
					'text'   => 'Price'
				),
				'price_gross' => array(
					'status' => '0',
					'align'  => 'right',
					'text'   => 'Price gross'
				),
				'tax' => array(
					'status' => '0',
					'align'  => 'right',
					'text'   => 'Tax'
				),
				'total' => array(
					'status' => '1',
					'align'  => 'right',
					'text'   => 'Total'
				),
				'total_gross' => array(
					'status' => '0',
					'align'  => 'right',
					'text'   => 'Total gross'
				)
			)
		), 'decode' => false, 'required' => false),
		'returns_section' => array('default' => array(
			'column' => array(
				'product' => array(
					'status'      => '1',
					'align'       => 'left',
					'text'        => 'Product name'
				),
				'quantity' => array(
					'status' => '1',
					'align'  => 'center',
					'text'   => 'Quantity'
				),
				'model' => array(
					'status' => '0',
					'align'  => 'left',
					'text'   => 'Model'
				),
				'reason' => array(
					'status' => '1',
					'align'  => 'center',
					'text'   => 'Reason for Return'
				),
				'opened' => array(
					'status' => '1',
					'align'  => 'center',
					'text'   => 'Opened'
				),
				'action' => array(
					'status' => '0',
					'align'  => 'left',
					'text'   => 'Return Action'
				),
				'return_status' => array(
					'status' => '1',
					'align'  => 'right',
					'text'   => 'Return Status'
				),
				'comment' => array(
					'status' => '0',
					'align'  => 'left',
					'text'   => 'Comment'
				)
			)
		), 'decode' => false, 'required' => false)
	);

	public function edit() {

        $store_id = (int)$this->request->get['filter_store_id'];
        if(!preg_match("/^[0-9][0-9]*$/", $store_id)) return false;

		$this->load->language('module/custom_email_templates');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/custom_email_templates');

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {

		    if($this->validateForm()){
                $template_info = $this->model_catalog_custom_email_templates->getEmailTemplate($this->request->get['code'], $this->request->get['filter_store_id']);
				
                if ($template_info) {
                    $this->model_catalog_custom_email_templates->editEmailTemplate($template_info['template_id'], $this->request->post);
                } else {
                    $this->model_catalog_custom_email_templates->addEmailTemplate($this->request->get['code'], $this->request->post);
                }

                if ($this->request->post['status'] && $this->request->get['code'] == 'reviews.added') {
                    $this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `value` = '1' WHERE `key` = 'config_review_mail' AND store_id = '" . (int)$this->request->get['filter_store_id'] . "'");
                }
                $response['success'] = '1';
                $response['success_msg'] = $this->language->get('text_success');
            }
			else{
                $response['success'] = '0';
                $response['errors'] = $this->error;
            }
            $this->response->setOutput(json_encode($response));
            return;
		}

		$this->getForm();
	}

	public function index() {
        $this->load->model('marketplace/common');
        $market_appservice_status = $this->model_marketplace_common->hasApp('custom_email_templates');
        if (!$market_appservice_status['hasapp']) {
            $this->redirect($this->url->link('marketplace/app', 'id=' . $market_appservice_status['appserviceid'], 'SSL'));
            return;
        }

		$data = array_merge(array(), $this->language->load('module/custom_email_templates'));

		$this->loadStyles('CustomEmailTemplates');

		$this->document->setTitle($this->language->get('heading_title'));
		$data['heading_title'] = $this->language->get('heading_title');

		if (isset($this->request->get['filter_store_id'])) {
			$filter_store_id = (int)$this->request->get['filter_store_id'];
		} else {
			$filter_store_id = 0;
		}

		if (isset($this->request->get['filter_tab_show'])) {
			$filter_tab_show = (string)$this->request->get['filter_tab_show'];
		} else {
			$filter_tab_show = '';
		}

		$url = '&filter_store_id=' . (int)$filter_store_id;
				
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if ($this->validateSetting()){
                $this->editSetting('custom_email_templates', $this->request->post['custom_email_templates'], $filter_store_id);
                $result_json['success'] = '1';
                $result_json['success_msg'] =  $this->language->get('text_success');
            }
		    else{
                $result_json['success'] = '0';
                $result_json['errors'] =  $this->error;
            }
            $this->response->setOutput(json_encode($result_json));
            return;
		}

 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['error'])) {
			$data['error'] = $this->error['error'];
		} else {
			$data['error'] = array();
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$this->load->model('catalog/custom_email_templates');
		$this->load->model('tool/image');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('marketplace/home'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link($this->route, 'store_id='.$store_id, 'SSL'),
            'separator' => ' :: '
        );

		$data['add'] = $this->url->link('module/custom_email_templates/edit', 'code=mail_' . $this->model_catalog_custom_email_templates->getMaxIdMailTemplates() . $url, 'SSL');

		$config_store = $this->getSetting('custom_email_templates', $filter_store_id);

		foreach ($this->fields as $key => $value) {
			if (isset($this->request->post['custom_email_templates'][$key])) {
				$data[$key] = $this->request->post['custom_email_templates'][$key];
			} elseif (isset($config_store[$key])) {
				$data[$key] = $config_store[$key];
			} else {
				if ($value['decode']) {
					if (is_array($value['default'])) {
						$_tmp = $value['default'];

						foreach ($_tmp as $key2 => $result) {
							$value['default'][$key2] = base64_decode($result);
						}
					} else {
						$value['default'] = base64_decode($value['default']);
					}
				}

				$data[$key] = $value['default'];
			}

			if (isset($value['image'])) {
				if ($data[$key]) {
					$data[$key. '_thumb'] = $this->model_tool_image->resize($data[$key], 150, 150);
				} else {
					$data[$key. '_thumb'] = $this->model_tool_image->resize('no_image.png', 150, 150);
				}
			}
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 150, 150);


		$data['stores'] = $this->getStores('module/custom_email_templates');

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['lines'] = array('Solid', 'Dashed', 'Dotted', 'Double', 'Groove');
		$data['aligns'] = array('left' => $this->language->get('text_left'), 'center' => $this->language->get('text_center'), 'right' => $this->language->get('text_right'));

		if (isset($data['stores'][0])) {
			$link = (substr($data['stores'][0]['url'], -1) == '/') ? $data['stores'][0]['url'] : $data['stores'][0]['url'] . '/';

			$data['cron'] = $link . 'index.php?route=common/custom_email_templates/cron&format=raw';
		} else {
			$data['cron'] = '';
		}

		$data['filter_store_id'] = $filter_store_id;
		$data['filter_tab_show'] = $filter_tab_show;

        $data['iframe_width']=(int)$this->registry->get('config')->get('custom_email_templates_layout_width');
        $data['iframe_width']= (($data['iframe_width'] ?  $data['iframe_width'] : 510) + 50);

		$data['token'] = null;

		$this->toOutput('module/custom_email_templates.expand', $data);
	}

    public function dtTemplateHandler() {
        $this->load->model('marketplace/common');
        $this->load->model('catalog/custom_email_templates');

        $this->language->load('module/reward_points_pro');

        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'tab_name',
            1 => 'name',
            2 => 'code',
            3 => 'status',
            4 => 'template_id',
        );

        if (isset($this->request->get['filter_store_id'])) {
            $filter_store_id = (int)$this->request->get['filter_store_id'];
        } else {
            $filter_store_id = 0;
        }
        $url = '&filter_store_id=' . (int)$filter_store_id;

        if (isset($this->request->get['filter_tab_show'])) {
            $filter_tab_show = (string)$this->request->get['filter_tab_show'];
        } else {
            $filter_tab_show = '';
        }

        $data['templates']=$this->getTemplatesArray($filter_store_id,$url);

        $templates_array=array();
        $totalFiltered=null;


        foreach ($data['templates'] as $sub_array) {
            foreach ($sub_array['templates'] as $sub_array2) {
                if(!empty($search)){
                    if(preg_grep ("/\b(\w*".$search.'\w*)\b/i', $sub_array2)){
                        $templates_array[]=$sub_array2;
                        $totalFiltered++;
                    }
                }
                else{
                    $templates_array[]=$sub_array2;
                }
            }
        }

        $return = array_slice($templates_array,$start,$length);

        $records = $return;
        $totalData = count($templates_array);

        if($totalFiltered == null)
            $totalFiltered = count($templates_array);

        $json_data = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($totalData), // total number of records
            "recordsFiltered" => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $records,   // total data array
        );

        $this->response->setOutput(json_encode($json_data));
        return;
    }////////////////////

    public function getTemplatesArray($filter_store_id,$url=''){
        $this->load->model('localisation/order_status');
        $this->load->model('catalog/custom_email_templates');


        $this->language->load('module/custom_email_templates');

        $order_statuses = $this->model_localisation_order_status->getOrderStatuses(array('start' => 0, 'limit' => 99999));

        $data['order_statuses'] = $order_statuses;

        $this->load->model('localisation/return_status');

        $return_statuses = $this->model_localisation_return_status->getReturnStatuses(array('start' => 0, 'limit' => 99999));

        $data['return_statuses'] = $return_statuses;

        $order_status_templates = $this->model_catalog_custom_email_templates->getOrderStatusTemplates($filter_store_id);
        $return_status_templates = $this->model_catalog_custom_email_templates->getReturnStatusTemplates($filter_store_id);
        $mail_templates = $this->model_catalog_custom_email_templates->getMailTemplates($filter_store_id);
        $other_templates = $this->model_catalog_custom_email_templates->getOtherTemplates($filter_store_id);

        $data['templates'] = array();

        foreach ($this->templates as $key => $value) {

            if ($key == 'order') {
                foreach ($order_statuses as $order_status) {
                    $code = 'order.status_' . $order_status['order_status_id'];

                    $data['templates']['order']['templates'][] = array(
                        'template_id' => ($order_status_templates && array_key_exists($code, $order_status_templates)) ? $order_status_templates[$code]['template_id'] : '',
                        'tab_name'=> ucfirst($key),
                        'code'   => 'order.status_' . $order_status['order_status_id'],
                        'name'   => $order_status['name'],
                        'status_text' => ($order_status_templates && (array_key_exists($code, $order_status_templates) && $order_status_templates[$code]['status'])) ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
                        'status' => ($order_status_templates && (array_key_exists($code, $order_status_templates) && $order_status_templates[$code]['status'])) ? '1' : '0',
                        'url'   => $url
                    );
                }
            } elseif ($key == 'return') {
                foreach ($return_statuses as $return_status) {
                    $code = 'return.status_' . $return_status['return_status_id'];

                    $data['templates']['return']['templates'][] = array(
                        'template_id' => ($return_status_templates && array_key_exists($code, $return_status_templates)) ? $return_status_templates[$code]['template_id'] : '',
                        'tab_name'=> ucfirst($key),
                        'code'   => 'return.status_' . $return_status['return_status_id'],
                        'name'   => $return_status['name'],
                        'status_text' => ($return_status_templates && (array_key_exists($code, $return_status_templates) && $return_status_templates[$code]['status'])) ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
                        'status' => ($return_status_templates && (array_key_exists($code, $return_status_templates) && $return_status_templates[$code]['status'])) ? '1' : '0',
                        'url'   => $url
                    );
                }
            } elseif ($key == 'mail') {
                if ($mail_templates) {

                    foreach ($mail_templates as $mail) {
                        $data['templates'][$key]['templates'][] = array(
                            'template_id' => $mail['template_id'],
                            'tab_name'=> ucfirst($key),
                            'code'   => $mail['code'],
                            'name'   => $mail['subject'],
                            'status' => $mail['status'],
                            'status_text' => ($mail['status']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
                            'url'   => $url
                        );
                        // $this->url->link('module/custom_email_templates/edit', 'token=' . $this->session->data['token'] . '&code=' . $mail['code'] . $url, 'SSL')
                    }
                } else {
                    $data['templates'][$key]['templates'][] = array(
                        'template_id' => '',
                        'tab_name'=> ucfirst($key),
                        'code'   => '',
                        'name'   => '',
                        'status' => '',
                        'status_text' => '',
                        'url'   => $url

                    );
                }
            } else {
                foreach ($this->templates[$key] as $value2) {
                    $code = $key . '.' . $value2;
                    $name= $this->language->get('text_template_' . str_replace('.', '_', $code));
                    $data['templates'][$key]['templates'][] = array(
                        'template_id' => ($other_templates && array_key_exists($code, $other_templates)) ? $other_templates[$code]['template_id'] : '',
                        'tab_name'=> ucfirst($key),
                        'code'   => $code,
                        'name'   => $name,
                        'status' => ($other_templates && (array_key_exists($code, $other_templates) && $other_templates[$code]['status'])) ? '1': '0',
                        'status_text' => ($other_templates && (array_key_exists($code, $other_templates) && $other_templates[$code]['status'])) ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
                        'url'   => $url
                    );
                }
            }
        }
        return $data['templates'];

    }
	public function history() {

		$data = array_merge(array(), $this->language->load('module/custom_email_templates'));


        $this->document->setTitle($this->language->get('heading_title'));
        $data['heading_title'] = $this->language->get('heading_title');

		if (isset($this->request->get['store_id'])) {
			$filter_store_id = (int)$this->request->get['store_id'];
		} else {
			$filter_store_id = 0;
		}

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('marketplace/home'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link($this->route, 'store_id='.$store_id, 'SSL'),
            'separator' => ' :: '
        );

        $data['filter_store_id']=$filter_store_id;
        $data['templates']= $this->getTemplatesArray($filter_store_id);

        $data['action'] = $this->url->link('module/custom_email_templates/dtHistoryHandler');

		$data['iframe_width']=(int)$this->registry->get('config')->get('custom_email_templates_layout_width');
        $data['iframe_width']= (($data['iframe_width'] ?  $data['iframe_width'] : 510) + 50);


		$url = '';

		$this->toOutput('module/custom_email_templates/history_list.expand', $data);
	}

    public function dtHistoryHandler() {
        $this->load->model('marketplace/common');
        $this->load->model('catalog/custom_email_templates');

        $this->language->load('module/reward_points_pro');

        $request = $_REQUEST;
        $filter_data = $request['data'];

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'subject',
            1 => 'template',
            2 => 'email',
            3 => 'attachment',
            4 => 'date_added',
            5 => 'date_opened',
            6 => 'history_id',
        );

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];


        $this->load->model('catalog/custom_email_templates');

        $return = $this->model_catalog_custom_email_templates->dtHistoryHandler($start, $length, $filter_data, $orderColumn, $orderType);

        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $json_data = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($totalData), // total number of records
            "recordsFiltered" => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $records,   // total data array
        );

        $this->response->setOutput(json_encode($json_data));
        return;
    }////////////////////


    public function setting() {
        $this->loadStyles('CustomEmailTemplates');

        $data = array_merge(array(), $this->language->load('module/custom_email_templates'));


        $this->document->setTitle($this->language->get('heading_title'));
        $data['heading_title'] = $this->language->get('heading_title');

        if (isset($this->request->get['store_id'])) {
            $filter_store_id = (int)$this->request->get['store_id'];
        } else {
            $filter_store_id = 0;
        }

        $this->load->model('localisation/order_status');
        $this->load->model('catalog/custom_email_templates');
        $this->load->model('tool/image');

        $order_statuses = $this->model_localisation_order_status->getOrderStatuses(array('start' => 0, 'limit' => 99999));

        $data['order_statuses'] = $order_statuses;

        $data['invoice_templates'] = array();

        foreach (glob(DIR_CATALOG . 'view/theme/default/template/custom_email_templates/invoice/*.tpl') as $invoice_template) {
            $name = basename($invoice_template);
            if(strpos($name, 'ar_') === false)
            	$data['invoice_templates'][] = $name;
        }
        $data['repeats'] = array('repeat', 'repeat-x', 'repeat-y', 'no-repeat');

        $data['invoice_template_path']= HTTP_CATALOG . 'catalog/view/theme/default/template/custom_email_templates/invoice/';
        $data['invoice_template_img']= $data['invoice_template_path']. str_replace('.tpl', '.jpg',  $data['invoice_templates'][0] );
        $data['custom_invoice_app_installed'] = \Extension::isInstalled('custom_invoice_template');


        $data['date_formats'] = array (
            'Y/m/d H:i:s',
            'Y/m/d',
            'd/m/Y H:i:s',
            'd/m/Y',
            'd-m-Y H:i:s',
            'd-m-Y',
            'F j, Y, g:i a',
            'Y-m-d H:i:s',
            'm.d.y',
            'd.m.y'
        );

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('marketplace/home'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link($this->route, 'store_id='.$store_id, 'SSL'),
            'separator' => ' :: '
        );

        $data['filter_store_id']=$filter_store_id;

        $data['action'] = $this->url->link('module/custom_email_templates');
        $data['cancel'] = $this->url->link('marketplace/home');


        $config_store = $this->getSetting('custom_email_templates', $filter_store_id);

        foreach ($this->fields as $key => $value) {
            if (isset($this->request->post['custom_email_templates'][$key])) {
                $data[$key] = $this->request->post['custom_email_templates'][$key];
            } elseif (isset($config_store[$key])) {
                $data[$key] = $config_store[$key];
            } else {
                if ($value['decode']) {
                    if (is_array($value['default'])) {
                        $_tmp = $value['default'];

                        foreach ($_tmp as $key2 => $result) {
                            $value['default'][$key2] = base64_decode($result);
                        }
                    } else {
                        $value['default'] = base64_decode($value['default']);
                    }
                }

                $data[$key] = $value['default'];
            }

            if (isset($value['image'])) {
                if ($data[$key]) {
                    $data[$key. '_thumb'] = $this->model_tool_image->resize($data[$key], 150, 150);
                } else {
                    $data[$key. '_thumb'] = $this->model_tool_image->resize('no_image.png', 150, 150);
                }
            }
        }

        $data['no_image'] = $data['thumb'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);

        $this->toOutput('module/custom_email_templates/setting_form.expand', $data);
    }


    public function addhistory() {
		$this->load->language('module/custom_email_templates');

		$json = array();
		$order_ids = array();
		$keys = array(
			'order_status_id',
			'notify',
			'append',
			'override',
			'comment'
		);

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->user->hasPermission('modify', 'module/custom_email_templates')) {
			if (version_compare(VERSION, '2.0') < 0) {
				$this->load->model('sale/order');

				foreach ($this->request->post['selected'] as $order_id) {
					foreach ($keys as $key) {
						if (!isset($this->request->post[$key])) {
							$this->request->post[$key] = '';
						}
					}

					$this->model_sale_order->addOrderHistory($order_id, $this->request->post);

					$order_ids[] = $order_id;
				}

				$json['success'] = sprintf($this->language->get('text_history_success'), implode(',', $order_ids));
			} elseif (version_compare(VERSION, '2.1.0.1') < 0) {
				if ($this->request->get['api_key']) {
					$this->request->get['api'] = 'api/order/history';
					$this->request->get['api_key'] =  $this->request->get['api_key'];

					foreach ($this->request->post['selected'] as $order_id) {
						foreach ($keys as $key) {
							if (!isset($this->request->post[$key])) {
								$this->request->post[$key] = '';
							}
						}

						$this->request->get['order_id'] = $order_id;
						//$this->request->get['store_id'] = '';

						$this->load->controller('sale/order/api', $this->request->post);

						$order_ids[] = $order_id;
					}

					$json['success'] = sprintf($this->language->get('text_history_success'), implode(',', $order_ids));
				} else {
					$json['error'] = $this->language->get('error_key');
				}
			} else {
				if ($this->request->get['api_key']) {
					$e = '';
					$post = array();

					foreach ($this->request->post['selected'] as $order_id) {
						$curl = curl_init();

						if (substr(HTTPS_CATALOG, 0, 5) == 'https') {
							curl_setopt($curl, CURLOPT_PORT, 443);
						}

						curl_setopt($curl, CURLOPT_HEADER, false);
						curl_setopt($curl, CURLINFO_HEADER_OUT, true);
						curl_setopt($curl, CURLOPT_USERAGENT, $this->request->server['HTTP_USER_AGENT']);
						curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
						curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
						curl_setopt($curl, CURLOPT_FORBID_REUSE, false);
						curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($curl, CURLOPT_URL, HTTPS_CATALOG . 'index.php?route=api/order/history&api_key=' . $this->request->get['api_key'] . '&order_id=' . $order_id);
						curl_setopt($curl, CURLOPT_POST, true);

						foreach ($keys as $key) {
							if (!isset($this->request->post[$key])) {
								$post[$key] = '';
							} else {
								$post[$key] = $this->request->post[$key];
							}
						}

						curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));

						$response = curl_exec($curl);

						if (!$response) {
							$e = sprintf($this->language->get('error_curl'), curl_error($curl), curl_errno($curl));

							break;
						} else {
							$response = json_decode($response, true);

							if (isset($response['error'])) {
								$e = $response['error'];

								break;
							} else {
								$order_ids[] = $order_id;
							}

							curl_close($curl);
						}
					}

					if ($e) {
						$json['error'] = sprintf($this->language->get('text_history_break'), $e, implode(',', $order_ids));
					} else {
						$json['success'] = sprintf($this->language->get('text_history_success'), implode(',', $order_ids));
					}
				} else {
					$json['error'] = $this->language->get('error_key');
				}
			}
		} else {
			$json['error'] = $this->language->get('error_permission');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function remove() {
		$this->load->language('module/custom_email_templates');

		$json = array();

		if (!$this->user->hasPermission('modify', 'module/custom_email_templates')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			if (isset($this->request->get['store_id'])) {
				$filter_store_id = (int)$this->request->get['store_id'];
			} else {
				$filter_store_id = 0;
			}

			$this->load->model('catalog/custom_email_templates');

			if (isset($this->request->get['history_id'])) {
				$history_id = $this->request->get['history_id'];

				$this->model_catalog_custom_email_templates->deleteHistory($history_id);
			} else {
				$this->model_catalog_custom_email_templates->deleteHistories($filter_store_id);
			}

			$json['success'] = $this->language->get('text_email_history_removed');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function removeHistory() {
		$this->load->language('module/custom_email_templates');

		$json = array();

     	if (!$this->user->hasPermission('modify', 'module/custom_email_templates')) {
      		$json['error'] = $this->language->get('error_permission');
    	} else {
			if (isset($this->request->get['history_id'])) {
				$history_id = $this->request->get['history_id'];
			} else {
				$history_id = 0;
			}

			$this->load->model('catalog/custom_email_templates');

			$this->model_catalog_custom_email_templates->deleteHistoryOrderById($this->request->get['history_id']);

			$json['success'] = $this->language->get('text_order_history_removed');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
  	}

	public function preview() {
		$this->load->language('module/custom_email_templates');

		$html = 'No preview available!';

		if ($this->user->hasPermission('access', 'module/custom_email_templates')) {
			if (isset($this->request->get['id'])) {
				$id = $this->request->get['id'];
			} else {
				$id = 0;
			}

			if (isset($this->request->get['type'])) {
				$type = $this->request->get['type'];
			} else {
				$type = '';
			}

			if ($id && $type) {
				$this->load->model('catalog/custom_email_templates');

				if ($type == 'history') {
					$result = $this->model_catalog_custom_email_templates->getHistory($id);

					if ($result) {
						$html = $result['description'];
					}
				} elseif ($type == 'template') {
					$result = $this->model_catalog_custom_email_templates->getEmailTemplateDescriptions($id);

					if ($result) {
						$description = isset($result[$this->config->get('config_language_id')]['description']) ? html_entity_decode($result[$this->config->get('config_language_id')]['description'], ENT_COMPAT, 'UTF-8') : '';

						$message = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
						$message .= '<style>
							.cet_container, td, th, input, select, textarea, option, optgroup { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px; color: ' . $this->config->get('custom_email_templates_text_color') . '; }
							.cet_container { padding: 0px; margin: 0px; width: ' . $this->config->get('custom_email_templates_layout_width') . '; }
							a,a:link,a:visited,a:hover { color: ' . $this->config->get('custom_email_templates_link_color') . '; }
							</style>';
						$message .= '<body style="background-color: ' . $this->config->get('custom_email_templates_background_color') . '; ' . (($this->config->get('custom_email_templates_background_image')) ? 'background-image: url(\'' . HTTP_SERVER . $this->config->get('custom_email_templates_background_image') . '\'); background-repeat: ' . $this->config->get('custom_email_templates_background_repeat') . ';' : '') . '">';

						$found_container = false;

						if (stripos($description, '"cet_container"') === false) {
							$message .= '<div class="cet_container">';

							$found_container = true;
						}

						$message .= $description;

						if ($found_container) {
							$message .= '</div>';
						}

						$message .= '</body>';

						$html = $message;
					}
				}
			}
		}

		echo html_entity_decode($html, ENT_COMPAT, 'UTF-8');
		exit();
	}

	protected function getForm() {
		$data = array_merge(array(), $this->language->load('module/custom_email_templates'));

		$this->loadStyles('CustomEmailTemplates');

		$data['text_form'] = sprintf($this->language->get('text_edit_template'), $this->request->get['code']);

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['subject'])) {
			$data['error_subject'] = $this->error['subject'];
		} else {
			$data['error_subject'] = array();
		}

		if (isset($this->error['description'])) {
			$data['error_description'] = $this->error['description'];
		} else {
			$data['error_description'] = array();
		}

		if (isset($this->request->get['filter_store_id'])) {
			$filter_store_id = (int)$this->request->get['filter_store_id'];
		} else {
			$filter_store_id = 0;
		}

		$url = 'filter_tab_show=template';

		if (isset($this->request->get['filter_store_id'])) {
			$url .= '&filter_store_id=' . $this->request->get['filter_store_id'];
		}

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('marketplace/home', 'type=apps', 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link($this->route, 'store_id='.$store_id, 'SSL'),
            'separator' => ' :: '
        );

		$data['action'] = $this->url->link('module/custom_email_templates/edit', $url . '&code=' . $this->request->get['code'], 'SSL');
        $data['cancel'] = $this->url->link('module/custom_email_templates', $url, 'SSL');

		$text_shortcode = $this->language->get('text_shortcode_' . str_replace('.', '_', preg_replace('#_[0-9]+#i', '', $this->request->get['code'])));

		//check if product video links app installed, then append its shortcode.
		$data['text_shortcode'] = $this->_getProductVideoLinksAppData($text_shortcode);


		if (isset($this->request->get['code']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$template_info = $this->model_catalog_custom_email_templates->getEmailTemplate($this->request->get['code'], $filter_store_id);
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['template_description'])) {
			$data['template_description'] = $this->request->post['template_description'];
		} elseif (!empty($template_info)) {
			$data['template_description'] = $this->model_catalog_custom_email_templates->getEmailTemplateDescriptions($template_info['template_id']);
		} else {
			$data['template_description'] = array();
		}

		if (isset($this->request->post['bcc'])) {
			$data['bcc'] = $this->request->post['bcc'];
		} elseif (!empty($template_info)) {
			$data['bcc'] = $template_info['bcc'];
		} else {
			$data['bcc'] = '';
		}

		$data['products'] = array(
			'1' => $this->language->get('text_product_latest'),
			'2' => $this->language->get('text_product_bestseller'),
			'3' => $this->language->get('text_product_mostviewed'),
			'4' => $this->language->get('text_product_special')
		);

		if (isset($this->request->post['product'])) {
			$data['product'] = $this->request->post['product'];
		} elseif (!empty($template_info)) {
			$data['product'] = $template_info['product'];
		} else {
			$data['product'] = '';
		}

		if (isset($this->request->post['product_limit'])) {
			$data['product_limit'] = $this->request->post['product_limit'];
		} elseif (!empty($template_info)) {
			$data['product_limit'] = $template_info['product_limit'];
		} else {
			$data['product_limit'] = 3;
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($template_info)) {
			$data['status'] = $template_info['status'];
		} else {
			$data['status'] = true;
		}

		if (isset($this->request->post['store_id'])) {
			$data['store_id'] = $this->request->post['store_id'];
		} elseif (!empty($template_info)) {
			$data['store_id'] = $template_info['store_id'];
		} else {
			$data['store_id'] = (isset($this->request->get['filter_store_id'])) ? (int)$this->request->get['filter_store_id'] : 0;
		}

		$data['templates'] = array();

		foreach (glob(DIR_CATALOG . 'view/theme/default/template/custom_email_templates/mail/*.tpl') as $mail_template) {
			$data['templates'][] = basename($mail_template);
		}

		$data['token'] = null;

		//var_dump($data);
		//die();
		$this->toOutput('module/custom_email_templates/template_form.expand', $data);
	}



    /**
    * Get Product Video Links App data if installed
    *
    * @return void
    */
    private function _getProductVideoLinksAppData($text_shortcodes){

        $this->load->model('module/product_video_links');
        $product_video_links_installed = $this->model_module_product_video_links->isInstalled();

        if($product_video_links_installed){

        	$str = '<br />CSS table ID selectors:';
        	$index = strpos($text_shortcodes, $str);

        	$evu = "{external_products_videos_urls}<br />";
        	$new_text_shortcodes = substr_replace ( $text_shortcodes ,  $evu ,  $index , 0 );

        	return $new_text_shortcodes;
        }
        return $text_shortcodes;
    }



	public function loadtemplate() {
		$this->load->language('module/custom_email_templates');

		$json = array();

		if (!$this->user->hasPermission('modify', 'module/custom_email_templates')) {
			$json['warning'] = $this->language->get('error_permission');
		} elseif (isset($this->request->get['id']) && $this->request->get['id']) {
			$this->load->model('catalog/custom_email_templates');

			$template_info = $this->model_catalog_custom_email_templates->getEmailTemplate($this->request->get['id'], $this->request->get['store_id']);

			if ($template_info) {
				$result = $this->model_catalog_custom_email_templates->getEmailTemplateDescriptions($template_info['template_id']);

				$json['subject'] = html_entity_decode($result[(int)$this->config->get('config_language_id')]['subject'], ENT_COMPAT, 'UTF-8');
				$json['description'] = str_replace(array('<style>', '</style>'), array('&lt;style&gt;', '&lt;/style&gt;'), html_entity_decode($result[(int)$this->config->get('config_language_id')]['description'], ENT_COMPAT, 'UTF-8'));
			}
		} elseif (isset($this->request->get['file']) && $this->request->get['file']) {
			$json['subject'] = '';
			$json['description'] = str_replace(array('<style>', '</style>'), array('&lt;style&gt;', '&lt;/style&gt;'), file_get_contents(DIR_CATALOG . 'view/theme/default/template/custom_email_templates/mail/' . $this->request->get['file']));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function upload() {
		$this->load->language('module/custom_email_templates');

		$json = array();

		if (!$this->user->hasPermission('modify', 'module/custom_email_templates')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$json) {
			if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
				$filename = basename(html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8'));

				if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 128)) {
					$json['error'] = $this->language->get('error_filename');
				}

				$allowed = array();
				$extension_allowed = array();

				if ($this->config->get('config_file_extension_allowed')) {
					$extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_extension_allowed'));
				} elseif ($this->config->get('config_file_ext_allowed')) {
					$extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_ext_allowed'));
				}

				$filetypes = explode("\n", $extension_allowed);

				foreach ($filetypes as $filetype) {
					$allowed[] = trim($filetype);
				}

				if ($allowed) {
					if (!in_array(strtolower(substr(strrchr($filename, '.'), 1)), $allowed)) {
						$json['error'] = $this->language->get('error_filetype');
					}
				}

				$allowed = array();
				$mime_allowed = array();

				if ($this->config->get('config_file_mime_allowed')) {
					$mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_mime_allowed'));
				}

				$filetypes = explode("\n", $mime_allowed);

				foreach ($filetypes as $filetype) {
					$allowed[] = trim($filetype);
				}

				if ($allowed) {
					if (!in_array($this->request->files['file']['type'], $allowed)) {
						$json['error'] = $this->language->get('error_filetype');
					}
				}

				if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
					$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
				}
			} else {
				$json['error'] = $this->language->get('error_upload');
			}
		}

		if (!$json) {
			$file =  time() . '_' . $filename;

			// move_uploaded_file($this->request->files['file']['tmp_name'], DIR_DOWNLOAD . $file);
            \Filesystem::setPath('download/' . $file)->upload($this->request->files['file']['tmp_name']);

			$json['filename'] = 'download/' . $file;
			$json['mask'] = $file;
			$json['id'] = time();

			$json['success'] = $this->language->get('text_upload');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function download() {
		$this->load->model('catalog/custom_email_templates');

		if (isset($this->request->get['attachment_id'])) {
			$attachment_id = $this->request->get['attachment_id'];
		} else {
			$attachment_id = 0;
		}

		$attachment_info = $this->model_catalog_custom_email_templates->getAttachment($attachment_id);

		if ($this->user->hasPermission('modify', 'module/custom_email_templates') && $attachment_info) {
			// $file = DIR_DOWNLOAD . $attachment_info['file'];
            \Filesystem::setPath('downloads/' . $attachment_info['file']);
			$mask = basename($attachment_info['file']);

			if (!headers_sent()) {
				if (\Filesystem::isExists()) {
					header('Content-Type: application/octet-stream');
					header('Content-Disposition: attachment; filename="' . $mask . '"');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Pragma: public');
					header('Content-Length: ' . \Filesystem::getSize());

					if (ob_get_level()) {
						ob_end_clean();
					}

					// readfile($file, 'rb');
                    echo \Filesystem::read();

					exit();
				} else {
					exit('Error: Could not find file ' . $file . '!');
				}
			} else {
				exit('Error: Headers already sent out!');
			}
		} else {
			$this->response->redirect($this->url->link('module/custom_email_templates', '', 'SSL'));
		}
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'module/custom_email_templates')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['template_description'] as $language_id => $value) {
			if ((utf8_strlen($value['subject']) < 2) || (utf8_strlen($value['subject']) > 255)) {
				$this->error['subject'.$language_id] = $this->language->get('error_subject');
			}

			$tmp_description = str_replace('<p><br></p>', '', html_entity_decode($value['description'], ENT_COMPAT, 'UTF-8'));

			if (utf8_strlen($tmp_description) < 3) {
				$this->error['description'.$language_id] = $this->language->get('error_description');
			}
		}
        if ($this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_required');
        }

		return !$this->error;
	}

	private function validateSetting() {
		if (!$this->user->hasPermission('modify', 'module/custom_email_templates')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if ($this->request->post['custom_email_templates']) {
			foreach ($this->fields as $key => $value) {
				if ($value['required']) {
					if (isset($this->request->post['custom_email_templates'][$key]) && is_array($this->request->post['custom_email_templates'][$key])) {
						foreach ($this->request->post['custom_email_templates'][$key] as $key2 => $setting) {
							if (is_array($setting)) {
								foreach ($setting as $key3 => $value2) {
									if (is_array($value2)) {
										foreach ($value2 as $value3) {
											if (!$value3) {
												$this->error['error'][$key] = $this->language->get('error_' . $key);
											}
										}
									} elseif (!isset($value2) || empty($value2)) {
										$this->error['error'][$key] = $this->language->get('error_' . $key);
									}
								}
							} elseif (!isset($setting) || $setting === null || $setting == '') {
								$this->error['error'][$key] = $this->language->get('error_' . $key);
							}
						}
					} elseif (!isset($this->request->post['custom_email_templates'][$key]) || $this->request->post['custom_email_templates'][$key] === null || $this->request->post['custom_email_templates'][$key] == '') {
						$this->error['error'][$key] = $this->language->get('error_' . $key);
					}
				}
			}

			if (isset($this->error['error'])) {
				$this->error['warning'] = $this->language->get('error_required');
			}
		} else {
			$this->error['warning'] = $this->language->get('error_module');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}

	public function install() {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cet_template` (
			`template_id` int(11) NOT NULL AUTO_INCREMENT,
			`code` varchar(80) COLLATE utf8_bin NOT NULL,
			`bcc` text COLLATE utf8_bin NOT NULL,
			`product` tinyint(1) NOT NULL DEFAULT '0',
			`product_limit` tinyint(2) NOT NULL DEFAULT '0',
			`store_id` int(11) NOT NULL,
			`status` tinyint(1) NOT NULL DEFAULT '0',
			PRIMARY KEY (`template_id`),
			KEY `store_id` (`store_id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cet_description` (
			`template_id` int(11) NOT NULL,
			`language_id` int(11) NOT NULL,
			`subject` varchar(180) COLLATE utf8_bin NOT NULL,
			`description` mediumtext COLLATE utf8_bin NOT NULL,
			PRIMARY KEY (`template_id`, `language_id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cet_history` (
			`history_id` int(11) NOT NULL AUTO_INCREMENT,
			`template_id` int(11) NOT NULL,
			`email` varchar(255) COLLATE utf8_bin NOT NULL,
			`subject` varchar(180) COLLATE utf8_bin NOT NULL,
			`description` mediumtext COLLATE utf8_bin NOT NULL,
			`track` tinyint(1) NOT NULL DEFAULT '0',
			`ip` varchar(15) COLLATE utf8_bin NOT NULL,
			`date_added` datetime NOT NULL,
			`date_opened` datetime NULL DEFAULT NULL,
			PRIMARY KEY (`history_id`),
			KEY `template_id` (`template_id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cet_attachment` (
			`attachment_id` int(11) NOT NULL AUTO_INCREMENT,
			`history_id` int(11) NOT NULL,
			`file` varchar(255) COLLATE utf8_bin NOT NULL,
			`date_added` datetime NOT NULL,
			PRIMARY KEY (`attachment_id`),
			KEY `history_id` (`history_id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");

		if (!$this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "order` LIKE 'invoice_sent'")->row) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "order` ADD `invoice_sent` TINYINT(1) NOT NULL DEFAULT '0'");
		}

		if (!$this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "order` LIKE 'invoice_date'")->row) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "order` ADD `invoice_date` datetime NULL DEFAULT NULL");
		}

		if (!$this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "affiliate` LIKE 'store_id'")->row) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "affiliate` ADD `store_id` INT(11) NOT NULL DEFAULT '0'");
		}
	}

	public function uninstall() {
		$this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "cet_template");
		$this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "cet_description");
		$this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "cet_history");
		$this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "cet_attachment");
	}
}
?>