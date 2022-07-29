<?php
class ControllerMarketingMassMailSMS extends Controller {
    private $error = array();

    private $plan_id = PRODUCTID;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('plan/trial');
        $trial = $this->model_plan_trial->getLastTrial();
        if ($trial){
            $this->plan_id = $trial['plan_id'];
        }

        if( $this->plan_id == 3){
            $this->redirect(
                $this->url->link('error/not_found', '', 'SSL')
            );
            exit();
        }
    }

    public function index() {

        $this->language->load('marketing/mass_mail_sms');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('marketing/mass_mail_sms', '', 'SSL'),
            'separator' => ' :: '
        );

        $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

        if($queryMultiseller->num_rows) {
            $this->language->load('multiseller/multiseller');
            $this->data['text_seller_all'] = $this->language->get('ms_all_sellers');
        }

        $this->load->model('setting/store');

        $this->data['stores'] = $this->model_setting_store->getStores();

        $this->load->model('sale/customer_group');

        $this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups(0);

        $querySMSModule = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'smshare'");
        if($querySMSModule->num_rows) {
            $this->data['enable_sms_tab'] = '1';
        } else {
            $this->data['enable_sms_tab'] = '0';
        }

        if ($this->config->get('config_mail_protocol') == 'expandcart_relay' || $this->config->get('config_mail_protocol') == 'mail') {
            $this->data['expandcart_smtp_enabled'] = '1';
        }else{
            $this->data['expandcart_smtp_enabled'] = '0';
        }

        $this->template = 'marketing/mass_mail_sms.expand';
        $this->base = "common/base";

        $this->response->setOutput($this->render_ecwig());
    }

    public function sendMail($page=1) {
        $this->language->load('marketing/mass_mail_sms');

        $result_json = array(
            'success' => '0',
            'errors' => array(),
            'success_msg' => ''
        );

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if (!$this->user->hasPermission('modify', 'marketing/mass_mail_sms')) {
                $result_json['success'] = '0';
                $result_json['errors']['warning'] = $this->language->get('error_permission');
            }

            if (!$this->request->post['mail_subject']) {
                $result_json['success'] = '0';
                $result_json['errors']['mail_subject'] = $this->language->get('error_subject');
            }

            if (!$this->request->post['mail_message']) {
                $result_json['success'] = '0';
                $result_json['errors']['mail_message'] = $this->language->get('error_message');
            }

            /**
             * PREVENT SENDING MAILS IF EXPAND SMTP ENABLED
             */
            if ($this->request->post['mass_type'] == 'mail' && $this->config->get('config_mail_protocol') == 'expandcart_relay') {
                $result_json['success'] = '0';
                $result_json['errors']['mail_message'] = $this->language->get('text_change_smtp');
            }

            if (!$result_json['errors']) {
                $this->load->model('setting/store');

                $store_info = $this->model_setting_store->getStore($this->request->post['mail_store_id']);

                if ($store_info) {
                    $store_name = $store_info['name'];
                } else {
                    // get store name based on the current language
                    $store_name = $this->config->get('config_name')[$this->config->get('config_admin_language')];
                }

                $this->load->model('sale/customer');

                $this->load->model('sale/customer_group');

                $this->load->model('sale/affiliate');

                $this->load->model('sale/order');

                $this->initializer([
                    'module/abandoned_cart/settings',
                    'emailedOrders' => 'module/abandoned_cart/emailed_orders',
                ]);

                $mailMessage = $this->request->post['mail_message'];


                $email_total = 0;

                $emails = array();

                switch ($this->request->post['mail_to']) {
                    case 'newsletter':
                        $customer_data = array(
                            'filter_newsletter' => 1,
                            'start'             => ($page - 1) * 10,
                            'limit'             => 10
                        );

                        $email_total = $this->model_sale_customer->getTotalCustomers($customer_data);

                        $results = $this->model_sale_customer->getCustomers($customer_data);

                        foreach ($results as $result) {
                            $emails[] = array('address'   => $result['email'], 
                                              'fname'     => $result['firstname'],
                                              'lname'     => $result['lastname'],
                                              'telephone' => $result['telephone']);
                        }

                        //Add unregistered subscribers users
                        $this->load->model('sale/newsletter/subscriber');
                        $subscribers = $this->model_sale_newsletter_subscriber->getUnregisteredSubscribers();
                        $email_total += count($subscribers);
                        foreach ($subscribers as $subscriber) {
                            $name = explode(' ', $subscriber['name']);
                            $emails[] = array('address'   => $subscriber['email'], 
                                              'fname'     => $name[0],
                                              'lname'     => $name[1] ?: $name[0],
                                              'telephone' => '000000'); //No phone number provided
                        }
                        break;
                    case 'customer_all':
                        $customer_data = array(
                            'start'  => ($page - 1) * 10,
                            'limit'  => 10
                        );

                        $email_total = $this->model_sale_customer->getTotalCustomers($customer_data);

                        $results = $this->model_sale_customer->getCustomers($customer_data);

                        foreach ($results as $result) {
                            $emails[] = array('address'   => $result['email'], 
                                              'fname'     => $result['firstname'],
                                              'lname'     => $result['lastname'],
                                              'telephone' => $result['telephone']);
                        }
                        break;
                    case 'customer_group':
                        $customer_data = array(
                            'filter_customer_group_id' => $this->request->post['mail_customer_group_id'],
                            'start'                    => ($page - 1) * 10,
                            'limit'                    => 100
                        );

                        $email_total = $this->model_sale_customer->getTotalCustomers($customer_data);

                        $results = $this->model_sale_customer->getCustomers($customer_data);

                        foreach ($results as $result) {
                            $emails[$result['customer_id']] = array('address'   => $result['email'], 
                                                                    'fname'     => $result['firstname'],
                                                                    'lname'     => $result['lastname'],
                                                                    'telephone' => $result['telephone']);
                        }
                        break;
                    case 'customer':
                        if (!empty($this->request->post['mail_customer'])) {
                            foreach ($this->request->post['mail_customer'] as $customer_id) {
                                $customer_info = $this->model_sale_customer->getCustomer($customer_id);

                                if ($customer_info) {
                                    $emails[] = array('address'   => $customer_info['email'], 
                                                      'fname'     => $customer_info['firstname'],
                                                      'lname'     => $customer_info['lastname'],
                                                      'telephone' => $customer_info['telephone']);
                                }
                            }
                        }
                        break;
                    case 'affiliate_all':
                        $affiliate_data = array(
                            'start'  => ($page - 1) * 10,
                            'limit'  => 10
                        );

                        $email_total = $this->model_sale_affiliate->getTotalAffiliates($affiliate_data);

                        $results = $this->model_sale_affiliate->getAffiliates($affiliate_data);

                        foreach ($results as $result) {
                           $emails[] = array('address'   => $result['email'], 
                                              'fname'     => $result['firstname'],
                                              'lname'     => $result['lastname'],
                                              'telephone' => $result['telephone']);
                        }
                        break;
                    case 'affiliate':
                        if (!empty($this->request->post['mail_affiliate'])) {
                            foreach ($this->request->post['mail_affiliate'] as $affiliate_id) {
                                $affiliate_info = $this->model_sale_affiliate->getAffiliate($affiliate_id);

                                if ($affiliate_info) {
                                    $emails[] = array('address'   => $affiliate_info['email'], 
                                              'fname'     => $affiliate_info['firstname'],
                                              'lname'     => $affiliate_info['lastname'],
                                              'telephone' => $affiliate_info['telephone']);
                                }
                            }
                        }
                        break;
                    case 'seller_all':
                        $email_total = $this->MsLoader->MsSeller->getTotalSellers(array(
                            'seller_status' => array(MsSeller::STATUS_ACTIVE)
                        ));

                        $results = $this->MsLoader->MsSeller->getSellers(
                            array(
                                'seller_status' => array(MsSeller::STATUS_ACTIVE)
                            ),
                            array(
                                'order_by'	=> 'ms.nickname',
                                'order_way'	=> 'ASC',
                                'offset'		=> ($page - 1) * 10,
                                'limit'		=> 10,
                            )
                        );

                        foreach ($results as $result) {
                            $emails[] = $result['c.email'];
                        }
                        break;
                    case 'product':
                        if (isset($this->request->post['mail_product'])) {
                            $email_total = $this->model_sale_order->getTotalEmailsByProductsOrdered($this->request->post['mail_product']);

                            $results = $this->model_sale_order->getEmailsByProductsOrdered($this->request->post['mail_product'], ($page - 1) * 10, 10);

                            foreach ($results as $result) {
                                $emails[] = array('address'   => $result['email'], 
                                                  'fname'     => $result['firstname'],
                                                  'lname'     => $result['lastname'],
                                                  'telephone' => $result['telephone']);
                            }
                        }
                        break;
                    case 'abandoned_orders':
                        if (!empty($this->request->post['abandoned_orders'])) {
                            foreach ($this->request->post['abandoned_orders'] as $order_id) {
                                $orderInfo = $this->model_sale_order->getAbandonedOrderByOrderId($order_id);

                                $orderEmail = trim($orderInfo['email']);
                                
                                if (
                                    mb_strlen($orderEmail) > 0 &&
                                    isset($orderEmail) == true &&
                                    filter_var($orderEmail, FILTER_VALIDATE_EMAIL) == true
                                ) {
                                    if ($orderInfo && !$this->emailedOrders->getEmailedOrdersByOrderId($order_id)) {
                                        $emails[$orderInfo['order_id']] = array('address'   => $orderEmail, 
                                                        'fname'     => $orderInfo['firstname'],
                                                        'lname'     => $orderInfo['lastname'],
                                                        'telephone' => $orderInfo['telephone'],
                                                        'mailMessage' => $this->emailedOrders->renderMessageTemplate($mailMessage, $orderInfo)
                                                    );
                                        $this->emailedOrders->insertEmailedOrder($order_id);
                                        
                                        if (strstr($this->request->post['mail_message'], '{productsImages}') !== false){
                                            $this->load->model('sale/order');
                                            $this->load->model('tool/image');
                                            $orderProducts = $this->model_sale_order->getOrderProducts($order_id);
                                            foreach($orderProducts as $key => $product ){ 
                                                $image = $this->model_tool_image->resize($product['image'], 50, 50); 
                                                $orderProducts[$key]['image'] = $image;
                                            }
                                            $this->data['orderProducts'] = $orderProducts;
                                            $this->data['more_products'] = count($orderProducts) > 5 ?  count($orderProducts) - 5 : '';
                                            $logo_img = $this->model_tool_image->resize($this->config->get('config_logo'), 70, 70); 
                                            if(!strstr($logo_img, 'no_image')){
                                                $this->data['logo'] = $logo_img; 
                                            }
                                            $this->data['store_name'] = $this->config->get('config_name')[$this->config->get('config_language')];
                                            $this->data['cart_redirect'] =$this->fronturl->link('index.php?route=checkout/cart&src=mailMsg','', 'SSL')->format();
                                            $this->template = '/module/abandoned_cart/product_images.expand';
                                            $productImagesTemplate = $this->render_ecwig();
                                            $emails[$orderInfo['order_id']]['mailMessage'] = preg_replace('#\{productsImages\}#', $productImagesTemplate, $emails[$orderInfo['order_id']]['mailMessage']);
                                        }
                                        
                                    }
                                    else{
                                        $result_json['success'] = '0';
                                        $result_json['errors'] = array('error'=>$this->language->get('already_sent'));
                                        return $this->response->setOutput(json_encode($result_json));
                                    }
                                }
                            }
                            
                        }
                        break;
                }

                if ($emails) {
                    $start = ($page - 1) * 10;
                    $end = $start + 10;

                    if ($end < $email_total) {
                        $result_json['success'] = '1';
                        $result_json['success_msg'] = sprintf($this->language->get('text_sent'), $start, $email_total);
                    } else {
                        $result_json['success'] = '1';
                        $result_json['success_msg'] = $this->language->get('text_success');
                    }

                    $find = array(
                        '{firstname}',
                        '{lastname}',
                        '{phonenumber}'
                    );
                    
                    foreach ($emails as $email) {

                        if (!$email['address']) {
                            continue;
                        }
                        $message  = '<html dir="ltr" lang="en">' . "\n";
                        $message .= '  <head>' . "\n";
                        $message .= '    <title>' . $this->request->post['mail_subject'] . '</title>' . "\n";
                        $message .= '    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
                        $message .= '  </head>' . "\n";
                        if($this->request->post['mail_to'] == 'abandoned_orders'){
                            $message .= '  <body>' . html_entity_decode($email['mailMessage'], ENT_QUOTES, 'UTF-8') . '</body>' . "\n";
                        }else{
                            $message .= '  <body>' . html_entity_decode($mailMessage, ENT_QUOTES, 'UTF-8') . '</body>' . "\n";
                        }
                        
                        $message .= '</html>' . "\n";
                        
                        $replace = array(
                            'firstname'     => $email['fname'] ? $email['fname'] : '',
                            'lastname'      => $email['lname'] ? $email['lname'] : '',
                            'phonenumber'   => $email['telephone'] ? $email['telephone'] : ''
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
                        $mail->setReplyTo(
                            $this->config->get('config_mail_reply_to'),
                            $this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
                            $this->config->get('config_email')
                        );
                        $mail->setTo($email['address']);
                        $mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
                        $mail->setSender($store_name);
                        $mail->setSubject(html_entity_decode($this->request->post['mail_subject'], ENT_QUOTES, 'UTF-8'));
                        if ($this->config->get('custom_email_templates_status') && !isset($cet)) {
                            include_once(DIR_SYSTEM . 'library/custom_email_templates.php');

                            $cet = new CustomEmailTemplates($this->registry);

                            $cet_result = $cet->getEmailTemplate(array('type' => 'admin', 'class' => __CLASS__, 'function' => __FUNCTION__, 'vars' => get_defined_vars()));

                            if ($cet_result) {
                                if ($cet_result['subject']) {
                                    $mail->setSubject(html_entity_decode($cet_result['subject'], ENT_QUOTES, 'UTF-8'));
                                }

                                if ($cet_result['html']) {
                                    $mail->setNewHtml($cet_result['html']);
                                }

                                if ($cet_result['text']) {
                                    $mail->setNewText($cet_result['text']);
                                }

                                if ($cet_result['bcc_html']) {
                                    $mail->setBccHtml($cet_result['bcc_html']);
                                }

                                if ((isset($this->request->post['mail_attach_invoicepdf']) && $this->request->post['mail_attach_invoicepdf'] == 1) || $cet_result['invoice'] == 1) {
                                    $path_to_invoice_pdf = $cet->invoice($order_info, $cet_result['history_id']);

                                    $mail->addAttachment($path_to_invoice_pdf);
                                }

                                if (isset($this->request->post['mail_fattachments'])) {
                                    if ($this->request->post['mail_fattachments']) {
                                        foreach ($this->request->post['mail_fattachments'] as $attachment) {
                                            foreach ($attachment as $file) {
                                                $mail->addAttachment($file);
                                            }
                                        }
                                    }
                                }

                                $mail->setBccEmails($cet_result['bcc_emails']);
                            }
                        }
                        $mail->setHtml($messageTemp);
                        $mail->send();
                        if ($this->config->get('custom_email_templates_status')) {
                            $mail->sendBccEmails();
                        }
                    }

                    if ($end < $email_total) {
                        $this->sendMail($page + 1);
                    }
                }
                else{
                    $result_json['success'] = '0';
                    $result_json['errors'] = array('error'=>$this->language->get('no_emails'));
                }
            }
        }

        $this->response->setOutput(json_encode($result_json));
    }

    public function sendSMS($page=1){

        $this->language->load('marketing/mass_mail_sms');

        $result_json = array(
            'success' => '1',
            'errors' => array(),
            'success_msg' => $this->language->get('text_success')
        );

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

            if (!$this->request->post['sms_message']) {
                $result_json['success'] = '0';
                $result_json['errors']['mail_message'] = $this->language->get('error_message');
            }

            if (!$result_json['errors']) {

                $this->load->model('setting/store');

                $store_info = $this->model_setting_store->getStore($this->request->post['sms_store_id']);

                if ($store_info) {
                    $store_name = $store_info['name'];
                } else {
                    // get store name based on the current language
                    $store_name = $this->config->get('config_name')[$this->config->get('config_admin_language')];
                }

                $this->load->model('sale/customer');

                $this->load->model('sale/customer_group');

                $this->load->model('sale/affiliate');

                $email_total = 0;

                switch ($this->request->post['sms_to']) {
                    case 'newsletter':

                        $customer_data = array(
                            'filter_newsletter' => 1,
                            'start'             => ($page - 1) * 10,
                            'limit'             => 10
                        );

                        $email_total = $this->model_sale_customer->getTotalCustomers($customer_data);

                        $results = $this->model_sale_customer->getCustomers($customer_data);

                        break;

                    case 'customer_all':
                        $customer_data = array(
                            'start'  => ($page - 1) * 10,
                            'limit'  => 10
                        );

                        $email_total = $this->model_sale_customer->getTotalCustomers($customer_data);

                        $results = $this->model_sale_customer->getCustomers($customer_data);

                        break;

                    case 'customer_group':
                        $customer_data = array(
                            'filter_customer_group_id' => $this->request->post['sms_customer_group_id'],
                            'start'                    => ($page - 1) * 10,
                            'limit'                    => 10
                        );

                        $email_total = $this->model_sale_customer->getTotalCustomers($customer_data);

                        $results = $this->model_sale_customer->getCustomers($customer_data);

                        break;

                    case 'customer':
                        if (isset($this->request->post['sms_customer'])) {

                            $results = array();

                            foreach ($this->request->post['sms_customer'] as $customer_id) {
                                $customer_info = $this->model_sale_customer->getCustomer($customer_id);
                                $results[] = $customer_info;
                            }

                        }
                        break;

                    case 'affiliate_all':
                        $affiliate_data = array(
                            'start'  => ($page - 1) * 10,
                            'limit'  => 10
                        );

                        $email_total = $this->model_sale_affiliate->getTotalAffiliates($affiliate_data);

                        $results = $this->model_sale_affiliate->getAffiliates($affiliate_data);

                        break;

                    case 'affiliate':
                        if (isset($this->request->post['sms_affiliate'])) {

                            $results = array();

                            foreach ($this->request->post['sms_affiliate'] as $affiliate_id) {
                                $affiliate_info = $this->model_sale_affiliate->getAffiliate($affiliate_id);
                                $results[] = $affiliate_info;
                            }

                        }
                        break;

                    case 'product':
                        if (isset($this->request->post['sms_product'])) {
                            $this->load->model('sale/order');    //load the order model (it will be accessible by as model_sale_order.)
                            $results = $this->model_sale_order->getTelephonesByProductsOrdered($this->request->post['sms_product'], 0,0);
                        }
                        break;
                }

                if ($results) {
                    $start = ($page - 1) * 10;
                    $end = $start + 10;

                    if ($end < $email_total) {
                        $result_json['success'] = '1';
                        $result_json['success_msg'] = sprintf($this->language->get('text_sent'), $start, $email_total);
                    } else {
                        $result_json['success'] = '1';
                        $result_json['success_msg'] = $this->language->get('text_success');
                    }

                    if ($end < $email_total) {
                        $this->sendSMS($page + 1);
                    }

                    /*
                     * Send
                     */
                    $sms_gateway_reply_text = "";
                    //We don't support yet sending grouped SMS list to gateway.
                    if('profile_api' == 'profile_api') {    //Send using api
                        $sms_gateway_reply 		= $this->oldSendSMSList($results);
                        $sms_gateway_reply_text = " Gateway reply: " . $sms_gateway_reply;
                    }else{
                        $output=$this->sendSMSList($results);

                        if($output != "1" || $output !== true){
                            $result_json['success']='0';
                            $result_json['success_msg']="There is something is wrong with the api";
                            return $this->response->setOutput(json_encode($result_json));
                        }
                    }

                }


            }    //if !json

            $this->response->setOutput(json_encode($result_json));
        }
    }


    private function sendSMSList($results){
        $smsList = array();

        $smshare_patterns   = $this->config->get('smshare_config_number_filtering');
        $number_size_filter = $this->config->get('smshare_cfg_num_filt_by_size');

        foreach ($results as $result) {

            if(! SmsharePhonenumberFilter::isNumberAuthorized($result['telephone'], $smshare_patterns)
                ||
                ! SmsharePhonenumberFilter::passTheNumberSizeFilter($result['telephone'], $number_size_filter)
            ){
                continue;
            }

            //Variable substitution in the SMS body
            $message = html_entity_decode($this->request->post['sms_message'], ENT_QUOTES, 'UTF-8');
            $smshareCommons = new SmshareCommons();
            $message = $smshareCommons->replace_smshare_variables($message, $result, 0);

            //Create the smsbean
            $smshare_smsBean = new stdClass();
            $smshare_smsBean->destination = $result['telephone'];
            $smshare_smsBean->message= $message;

            //push sms to the list
            $smsList[] = $smshare_smsBean;
        }

        $smshareCommons = new SmshareCommons();
        $smshareCommons->sendSMSList($smsList, $this->config);
    }

    /**
     * If all messages are the same, we just join phone numbers and make one request to the gateway.
     * Else, we make as much requests to the gateway as there are phonenumber.
     */
    private function oldSendSMSList($results){

        $smshareCommons = new SmshareCommons();

        $smshare_patterns   = $this->config->get('smshare_config_number_filtering');
        $number_size_filter = $this->config->get('smshare_cfg_num_filt_by_size');

        /*
         *
         */
        $smshare_smsBeans = array();
        foreach ($results as $result) {

            if(! SmsharePhonenumberFilter::isNumberAuthorized($result['telephone'], $smshare_patterns)
                ||
                ! SmsharePhonenumberFilter::passTheNumberSizeFilter($result['telephone'], $number_size_filter)
            ){
                continue;
            }

            //Variable substitution in the SMS body
            $message = html_entity_decode($this->request->post['sms_message'], ENT_QUOTES, 'UTF-8');
            $message = $smshareCommons->replace_smshare_variables($message, $result, 0);

            //Create the smsbean
            $smshare_smsBean = new stdClass();
            $smshare_smsBean->destination = $result['telephone'];
            $smshare_smsBean->message     = $message;

            $smshare_smsBeans[] = $smshare_smsBean;
        }

        /*
         *
         */
        $messages_are_equals = true;

        if(count($smshare_smsBeans) == 0) return "✘ There is no SMS to send";

        $previous_message = $smshare_smsBeans[0]->message;

        foreach ($smshare_smsBeans as $smshare_smsBean){
            $current_message = $smshare_smsBean->message;
            if($current_message != $previous_message){
                $messages_are_equals = false;
                break;
            }else{
                $previous_message = $current_message;
            }
        }

        if($messages_are_equals){

            /*
             * Get phones
             */
            $phones = array();
            foreach ($results as $result) {
                $phones[] = $result['telephone'];
            }

            /*
             * Remove duplicate
             */
            $phones = array_unique($phones);

            if ($phones) {

                /*
                 * Send sms
                 */

                //PHONE NUMBER REWRITING
                $sms_to_arr = array();
                foreach ($phones as $phone) {
                    array_push($sms_to_arr, SmsharePhonenumberFilter::rewritePhoneNumber($phone, $this->config));
                }

                $destinations = join(",", $sms_to_arr);
                return $smshareCommons->sendSMS($destinations,
                    $previous_message,
                    $this->config);
            }
        }else{
            $gateway_replies = array();
            /*
             * Send N requests.
             */
            foreach ($smshare_smsBeans as $smshare_smsBean) {

                //PHONE NUMBER REWRITING
                $phone_rewritten = SmsharePhonenumberFilter::rewritePhoneNumber($smshare_smsBean->destination, $this->config);

                $gateway_reply = $smshareCommons->sendSMS($phone_rewritten,
                    $smshare_smsBean->message,
                    $this->config);
                $gateway_replies[] = $gateway_reply;
            }
            return join(",", $gateway_replies);
        }
    }
}