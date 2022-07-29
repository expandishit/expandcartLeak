<?php
class ControllerModuleProductNotifyMe extends Controller {
	private $error      = array();
    private $modulepath = 'module/product_notify_me';
    private $productNotifyMe = '';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model($this->modulepath);
        $this->productNotifyMe = $this->model_module_product_notify_me;
        $this->load->language($this->modulepath);
    }

    public function install()
    {
        $this->productNotifyMe->install();
    }

    public function uninstall()
    {
        $this->productNotifyMe->uninstall();
    }

    public function index() {
    	$this->document->setTitle($this->language->get('heading_title'));

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_heading_main'),
			'href' => $this->url->link('marketplace/home', 'token=' . $this->session->data['token'], true)
		);
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_settings'),
			'href' => $this->url->link($this->modulepath, 'token=' . $this->session->data['token'] . $url, 'SSL')
		);

		$this->data['product_notify_me'] = $this->config->get('product_notify_me');
		
		$this->data['action'] = $this->url->link($this->modulepath.'/updateSettings', '', 'SSL');
        $this->data['cancel'] = $this->url.'marketplace/home';

        $this->load->model('localisation/language');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();

		$this->children = array(
           'common/header',
           'common/footer',
        );
		$this->template = $this->modulepath.'/settings.expand';
		$this->response->setOutput($this->render(TRUE));
	}

	public function updateSettings()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
             $result_json['success'] = '0';
             $result_json['error'] = $this->error;
        }else{

            $data = $this->request->post['product_notify_me'];
            $this->productNotifyMe->updateSettings(['product_notify_me' => $data]);
            $this->session->data['success'] = $this->language->get('text_settings_success');
            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
        }
        $this->response->setOutput(json_encode($result_json));
        return;
    }

	public function products() {
        $this->document->setTitle($this->language->get('heading_title'));
		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_heading_main'),
			'href' => $this->url->link('marketplace/home', 'token=' . $this->session->data['token'], true)
		);
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_products_list'),
			'href' => $this->url->link($this->modulepath, 'token=' . $this->session->data['token'] . $url, 'SSL')
		);

		$this->data['heading_title'] = $this->language->get('heading_title');

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

		$this->children = array(
           'common/header',
           'common/footer',
        );
		$this->template = $this->modulepath.'/products_list.expand';
		$this->response->setOutput($this->render(TRUE));
	}

	public function pr_dtHandler() {

        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            parse_str(html_entity_decode($this->request->post['filter']), $filterData);
            $filterData = $filterData['filter'];
        }

        $filterData['search'] = null;
        if (isset($request['search']['value']) && strlen($request['search']['value']) > 0) {
            $filterData['search'] = $request['search']['value'];
        }

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => '',
            1 => 'id',
			2 => 'product_name',
            3 => 'request_count',
            4 => 'quantity',
        );

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $data = array(
            'sort' => $orderColumn,
            'order' => $orderType,
            'start' => $request['start'],
            'limit' => $request['length']
        );

        $return = $this->productNotifyMe->getProductsDt($data, $filterData);

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
    }

    public function requests() {
        $this->document->setTitle($this->language->get('heading_title'));
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_heading_main'),
            'href' => $this->url->link('marketplace/home', 'token=' . $this->session->data['token'], true)
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_requests_list'),
            'href' => $this->url->link($this->modulepath, 'token=' . $this->session->data['token'] . $url, 'SSL')
        );

        $this->data['heading_title'] = $this->language->get('heading_title');

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

        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->template = $this->modulepath.'/requests_list.expand';
        $this->response->setOutput($this->render(TRUE));
    }

    public function rq_dtHandler() {

        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            parse_str(html_entity_decode($this->request->post['filter']), $filterData);
            $filterData = $filterData['filter'];
        }

        $filterData['search'] = null;
        if (isset($request['search']['value']) && strlen($request['search']['value']) > 0) {
            $filterData['search'] = $request['search']['value'];
        }

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => '',
            1 => 'id',
            2 => 'product_name',
            3 => 'request_count',
            4 => 'quantity',
        );

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $data = array(
            'sort' => $orderColumn,
            'order' => $orderType,
            'start' => $request['start'],
            'limit' => $request['length']
        );

        $return = $this->productNotifyMe->getRequestsDt($data, $filterData);

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
    }

    public function dtCleatNotified(){
        if ( !isset($this->request->post) )
        {
            return false;
        }
        if ( $this->productNotifyMe->clearNotified() )
        {
            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_success');
        }
        else
        {
            $result_json['success'] = '0';
        }
    }

    public function dtDelete()
    {
        if ( !isset($this->request->post['id']) )
        {
            return false;
        }

        $id_s = $this->request->post['id'];


        if ( is_array($id_s) )
        {
            $ids = implode(',',$id_s);
            if ( $this->productNotifyMe->deleteRequests( $ids, true ) )
            {
                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_success');
            }
            else
            {
                $result_json['success'] = '0';
            }
        }
        else
        {
            $id = (int) $id_s;

            if ($this->productNotifyMe->deleteRequests( $id ) )
            {
                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_success');
            }
            else
            {
                $result_json['success'] = '0';
            }
        }

        $this->response->setOutput(json_encode($result_json));
        return;

    }

    public function dtSendNotify() {
        $this->language->load('marketing/mass_mail_sms');

        $result_json = array(
            'success' => '0',
            'errors' => array(),
            'success_msg' => ''
        );

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $settings = $this->config->get('product_notify_me');
            $language_id = $this->config->get('config_language_id') ?: 1;

            if (!$this->user->hasPermission('modify', 'module/product_notify_me')) {
                $result_json['success'] = '0';
                $result_json['errors']['warning'] = $this->language->get('error_permission');
            }

            if (!$settings['subject'][$language_id]) {
                $result_json['success'] = '0';
                $result_json['errors']['mail_message'] = $this->language->get('error_subject');
            }

            if (!$settings['body'][$language_id]) {
                $result_json['success'] = '0';
                $result_json['errors']['mail_message'] = $this->language->get('error_message');
            }

            if (!$this->request->post['pid'] && !$this->request->post['nid']) {
                $result_json['success'] = '0';
                $result_json['errors']['mail_message'] = $this->language->get('no_selections');
            }

            /**
             * PREVENT SENDING MAILS IF EXPAND SMTP ENABLED
             */
            if ($this->config->get('config_mail_protocol') == 'expandcart_relay') {
                $result_json['success'] = '0';
                $result_json['errors']['mail_message'] = $this->language->get('text_change_smtp');
            }

            if (!$result_json['errors']) {

                if ($this->request->post['pid']) {
                   $ids = implode(',', $this->request->post['pid']);
                   $ids_type = 'product_id';
                }else if($this->request->post['nid']){
                    $ids = implode(',', $this->request->post['nid']);
                    $ids_type = 'id';
                }

                $mailMessage = $settings['body'][$language_id];
                $email_total = 0;
                $emails = array();

                $results = $this->productNotifyMe->getEmails($ids, $ids_type);

                $this->load->model('tool/image');

                foreach ($results as $result) {
                    $img = '';

                    if($result['product_image']){
                        $img = $this->model_tool_image->resize($result['product_image'], 100, 100);
                        $img = '<img src="'.$img.'" />';
                    }

                    $product_url = '<a href="'.$this->url->frontUrl('product/product', 'product_id='.$result['product_id'], true).'">'.$result['product_name'].'</a>';
                    $product_url = str_replace('admin/', '', $product_url);

                    if(STORECODE == 'QZWUOK9396'){
                        $customUrl = new Url(HTTP_SERVER, $this->config->get('config_secure') ? HTTPS_SERVER : HTTP_SERVER, $this->registry);
                        $product_url = '<a href="'.$customUrl->frontUrlWithDomain('product/product', ['product_id'=>$result['product_id']]).'">'.$result['product_name'].'</a>';
                        $product_url = str_replace('admin/', '', $product_url);
                    }

                    $product_price = number_format($result['price'], 2) . ' ' . $this->config->get('config_currency');

                    $emails[] = array(
                        'address'   => $result['email'],
                        'id'     => $result['id'],
                        'product_id'     => $result['product_id'],
                        'name'     => $result['name'],
                        'phone' => $result['phone'],
                        'product_name' => $result['product_name'],
                        'product_image' => $img,
                        'product_quantity' => $result['product_quantity'],
                        'product_price'     => $product_price,
                        'product_url'     => $product_url
                        );
                }

                if ($emails) {
                    $message  = '<html dir="ltr" lang="en">' . "\n";
                    $message .= '  <head>' . "\n";
                    $message .= '    <title>' . $this->request->post['mail_subject'] . '</title>' . "\n";
                    $message .= '    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
                    $message .= '  </head>' . "\n";
                    $message .= '  <body>' . html_entity_decode($mailMessage, ENT_QUOTES, 'UTF-8') . '</body>' . "\n";
                    $message .= '</html>' . "\n";

                    $find = array(
                        '{product_name}',
                        '{product_image}',
                        '{product_quantity}',
                        '{product_price}',
                        '{product_name_url}',
                        '{name}',
                        '{phone}',
                        '{email}'
                    );
                    foreach ($emails as $email) {

                        $replace = array(
                            'product_name'      => $email['product_name'] ? $email['product_name'] : '',
                            'product_image'      => $email['product_image'] ? $email['product_image'] : '',
                            'product_quantity'      => $email['product_quantity'] ? $email['product_quantity'] : '',
                            'product_price'      => $email['product_price'] ? $email['product_price'] : '0.00',
                            'product_name_url'      => $email['product_url'] ? $email['product_url'] : '',
                            'name'     => $email['name'] ? $email['name'] : '',
                            'phone'      => $email['phone'] ? $email['phone'] : '',
                            'email'   => $email['email'] ? $email['email'] : ''

                        );
                        $messageTemp = str_replace($find, $replace, $message);

                        $mail = new Mail();
                        $mail->protocol = $this->config->get('config_mail_protocol');
                        $mail->parameter = $this->config->get('config_mail_parameter');
                        $mail->hostname = $this->config->get('config_smtp_host');
                        $mail->username = $this->config->get('config_smtp_username');
                        $mail->password = $this->config->get('config_smtp_password');
                        $mail->port = $this->config->get('config_smtp_port');
                        $mail->timeout = $this->config->get('config_smtp_timeout');
                        $mail->setTo($email['address']);
                        $mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
		    	$mail->setSender($this->config->get('config_name')[$this->config->get('config_language')]);
                        $mail->setSubject(html_entity_decode($settings['subject'][$language_id], ENT_QUOTES, 'UTF-8'));
                        $mail->setHtml($messageTemp);
                        $sent = $mail->send();

                        $this->productNotifyMe->updateNotifyStatus($email);
                    }

                    $result_json['success'] = '1';
                    $result_json['success_msg'] = $this->language->get('notifications_sent');
                }
                else{
                    $result_json['success'] = '0';
                    $result_json['errors'] = array('error'=>$this->language->get('no_emails'));
                }
            }
        }

        $this->response->setOutput(json_encode($result_json));
    }
}
