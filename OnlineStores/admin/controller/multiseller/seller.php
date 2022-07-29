<?php

class ControllerMultisellerSeller extends ControllerMultisellerBase
{
    public function __construct($registry)
    {
        parent::__construct($registry);
    }

    public function getTableData()
    {
        $this->initializer([
            'sellers' => 'multiseller/seller'
        ]);

        $colMap = array(
            'seller' => '`c.name`',
            'nickname' => 'ms.nickname',
            'email' => 'c.email',
            'mobile' => 'ms.mobile',
            'balance' => '`current_balance`',
            'date_created' => '`ms.date_created`',
            'status' => '`ms.seller_status`'
        );

        $sorts = array(
            'seller',
            'nickname',
            'email',
            'mobile',
            'total_sales',
            'total_products',
            'total_earnings',
            'date_created',
            'balance',
            'status',
            'date_created'
        );
        $filters = array_diff($sorts, array('status'));

        //var_dump($this->request->get);

//		list($sortCol, $sortDir) = $this->MsLoader->MsHelper->getSortParams($sorts, $colMap);
//		$filterParams = $this->MsLoader->MsHelper->getFilterParams($filters, $colMap);
        list($sortCol, $sortDir) = $this->getSortParams($sorts, $colMap);
        $filterParams = $this->getFilterParams($filters, $colMap);

        $results = $this->sellers->getSellers(
            array(),
            array(
                'order_by' => $sortCol,
                'order_way' => $sortDir,
                'filters' => $filterParams,
                'offset' => $this->request->post['start'],
                'limit' => $this->request->post['length']
            ),
            array(
                'total_products' => 1,
                'total_earnings' => 1,
                'current_balance' => 1
            )
        );

        $total = isset($results[0]) ? $results[0]['total_rows'] : 0;

        $columns = array();

        $currency = $this->config->get('config_currency');

        foreach ($results as $result) {
            // actions
            $actions = "";

            $sellerBalance = $this->MsLoader->MsBalance->getSellerBalance($result['seller_id']);

            $reservedSellerFunds = $this->MsLoader->MsBalance->getReservedSellerFunds($result['seller_id']);

            $available = $sellerBalance - $reservedSellerFunds;

            if ($this->currency->format($available, $currency, '', false) > 0
            ) {
                if (!empty($result['ms.paypal']) && filter_var($result['ms.paypal'], FILTER_VALIDATE_EMAIL)) {
                    $actions .= "<a class='ms-button ms-button-paypal' title='" . $this->language->get('ms_catalog_sellers_balance_paypal') . "'></a>";
                } else {
                    $actions .= "<a class='ms-button ms-button-paypal-bw' title='" . $this->language->get('ms_payment_payout_paypal_invalid') . "'></a>";
                }
            }
            $actions .= "<a class='ms-button ms-button-edit' href='" . $this->url->link(
                    'multiseller/seller/update',
                    'seller_id=' . $result['seller_id'],
                    'SSL'
                ) . "' title='" . $this->language->get('text_edit') . "'></a>";
            $actions .= "<a class='ms-button ms-button-delete' href='" . $this->url->link(
                    'multiseller/seller/delete',
                    'seller_id=' . $result['seller_id'],
                    'SSL'
                ) . "' title='" . $this->language->get('text_delete') . "'></a>";

            $totalEarnings = $this->currency->format(
                $this->MsLoader->MsSeller->getTotalEarnings($result['seller_id']),
                $currency
            );

            $formattedAvailable = $this->currency->format(
                $available > 0 ? $available : 0,
                $currency
            );

            $balance = $this->currency->format($sellerBalance, $currency) . '/' . $formattedAvailable;
            if(\Extension::isInstalled('trips') &&$this->config->get('trips')['status']==1)
            $sellerLink = $this->url->link('multiseller/seller/update', 'seller_id=' . $result['seller_id'], 'SSL');
            else $sellerLink = $this->url->link('sale/customer/update', 'customer_id=' . $result['seller_id'], 'SSL');
            $productLink = $this->url->link('multiseller/product', 'seller_id=' . $result['seller_id'], 'SSL');
            // build table data
            $columns[] = array_merge(
                $result,
                array(
                    'checkbox' => "<input type='checkbox' name='selected[]' value='{$result['seller_id']}' />",
                    'seller' => "<a href='" . $sellerLink . "'>{$result['c.name']}</a>",
                    'nickname' => $result['ms.nickname'],
                    'email' => $result['c.email'],
                    'phone' => $result['ms.mobile'],
                    'total_earnings' => $totalEarnings,
                    'balance' => $balance,
                    'status' => $result['ms.seller_status'],
                    'date_created' => date(
                        $this->language->get('date_format_short'),
                        strtotime($result['ms.date_created'])
                    ),
                    'total_products' => "<a href='" . $productLink . "'>{$result['total_products']}</a>",
                    'actions' => $actions
                )
            );
        }

        $this->response->setOutput(json_encode(array(
            "draw" => intval($this->request->post['draw']),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $columns
        )));
    }

    public function jxUploadSellerAvatar()
    {
        $json = array();
        $file = array();

        $json['errors'] = $this->MsLoader->MsFile->checkPostMax($_POST, $_FILES);

        if ($json['errors']) {
            return $this->response->setOutput(json_encode($json));
        }

        foreach ($_FILES as $file) {
            $errors = $this->MsLoader->MsFile->checkImage($file);

            if ($errors) {
                $json['errors'] = array_merge($json['errors'], $errors);
            } else {
                $fileName = $this->MsLoader->MsFile->uploadImage($file);
                $thumbUrl = $this->MsLoader->MsFile->resizeImage(
                    $this->config->get('msconf_temp_image_path') . $fileName,
                    $this->config->get('msconf_preview_seller_avatar_image_width'),
                    $this->config->get('msconf_preview_seller_avatar_image_height')
                );
                $json['files'][] = array(
                    'name' => $fileName,
                    'thumb' => $thumbUrl
                );
            }
        }

        return $this->response->setOutput(json_encode($json));
    }

    public function jxSaveSellerInfo()
    {
        $this->validate(__FUNCTION__);
        $data = $this->request->post;
        $seller = $this->MsLoader->MsSeller->getSeller($data['seller']['seller_id']);
        $json = array();
        $this->load->model('sale/customer');
        if (empty($data['seller']['seller_id'])) {
            // creating new seller
            if (empty($data['seller']['nickname'])) {
                $json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_empty');
            } else if (mb_strlen($data['seller']['nickname']) < 4 || mb_strlen($data['seller']['nickname']) > 128) {
                $json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_length');
            } else if ($this->MsLoader->MsSeller->nicknameTaken($data['seller']['nickname'])) {
                $json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_taken');
            } else {
                switch ($this->config->get('msconf_nickname_rules')) {
                    case 1:
                        // extended latin
                        if (!preg_match("/^[a-zA-Z0-9_\-\s\x{00C0}-\x{017F}]+$/u", $data['seller']['nickname'])) {
                            $json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_latin');
                        }
                        break;

                    case 2:
                        // utf8
                        $pattern = "/((?:[\x01-\x7F]|[\xC0-\xDF][\x80-\xBF]|[\xE0-\xEF][\x80-\xBF]{2}|[\xF0-\xF7][\x80-\xBF]{3}){1,100})./x";
                        if (!preg_match($pattern, $data['seller']['nickname'])) {
                            $json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_utf8');
                        }
                        break;

                    case 0:
                    default:
                        // alnum
                        if (!preg_match("/^[a-zA-Z0-9_\-\s]+$/", $data['seller']['nickname'])) {
                            $json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_alphanumeric');
                        }
                        break;
                }
            }

            if (empty($data['customer']['customer_id'])) {
                // creating new customer
                $this->language->load('sale/customer');

                if (
                    (mb_strlen($data['customer']['firstname']) < 1) || (mb_strlen($data['customer']['firstname']) > 32)
                ) {
                    $json['errors']['customer[firstname]'] = $this->language->get('error_firstname');
                }


                if (
                    (mb_strlen($data['customer']['lastname']) < 1) || (mb_strlen($data['customer']['lastname']) > 32)
                ) {
                    $json['errors']['customer[lastname]'] = $this->language->get('error_lastname');
                }

                if (
                    (mb_strlen($data['customer']['email']) > 96) ||
                    !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $data['customer']['email'])
                ) {
                    $json['errors']['customer[email]'] = $this->language->get('error_email');
                }

                $customer_info = $this->model_sale_customer->getCustomerByEmail($data['customer']['email']);

                if (!isset($this->request->get['customer_id'])) {
                    if ($customer_info) {
                        $json['errors']['customer[email]'] = $this->language->get('error_exists');
                    }
                } else {
                    if ($customer_info && ($this->request->get['customer_id'] != $customer_info['customer_id'])) {
                        $json['errors']['customer[email]'] = $this->language->get('error_exists');
                    }
                }

                if ($data['customer']['password'] || (!isset($this->request->get['customer_id']))) {
                    if (
                        (mb_strlen($data['customer']['password']) < 4) ||
                        (mb_strlen($data['customer']['password']) > 20)
                    ) {
                        $json['errors']['customer[password]'] = $this->language->get('error_password');
                    }

                    if ($data['customer']['password'] != $data['customer']['password_confirm']) {
                        $json['errors']['customer[password_confirm]'] = $this->language->get('error_confirm');
                    }
                }
            }
        }

        if (strlen($data['seller']['company']) > 50) {
            $json['errors']['seller[company]'] = 'Company name cannot be longer than 50 characters';
        }
        if (empty($json['errors'])) {
            $mails = array();
            if (empty($data['seller']['seller_id'])) {
                // creating new seller
                if (empty($data['customer']['customer_id'])) {
                    // creating new customer
                    $this->model_sale_customer->addCustomer(
                        array_merge(
                            $data['customer'],
                            array(
                                'telephone' => '',
                                'fax' => '',
                                'customer_group_id' => $this->config->get('config_customer_group_id'),
                                'newsletter' => 1,
                                'status' => 1,
                            )
                        )
                    );

                    $customer_info = $this->model_sale_customer->getCustomerByEmail($data['customer']['email']);
                    $this->db->query(
                        "UPDATE " . DB_PREFIX . "customer SET approved = '1'
                        WHERE customer_id = '" . (int)$customer_info['customer_id'] . "'"
                    );

                    $data['seller']['seller_id'] = $customer_info['customer_id'];
                } else {
                    $data['seller']['seller_id'] = $data['customer']['customer_id'];
                }

                $this->MsLoader->MsSeller->createSellerFromAdmin(
                    array_merge(
                        $data['seller'],
                        array(
                            'approved' => 1,
                        )
                    )
                );

                $json['redirect'] = '1';
                $json['to'] = $this->url->link('multiseller/seller', '', 'SSL')->format();
            } else {
                // edit seller
                $mails[] = array(
                    'type' => MsMail::SMT_SELLER_ACCOUNT_MODIFIED,
                    'data' => array(
                        'recipients' => $seller['c.email'],
                        'addressee' => $seller['ms.nickname'],
                        'message' => (isset($data['seller']['message']) ? $data['seller']['message'] : ''),
                        'seller_id' => $seller['seller_id']
                    )
                );
                // echo '<pre>'; print_r($seller); echo '</pre>'; die();
                switch ($data['seller']['status']) {
                    case MsSeller::STATUS_INACTIVE:
                    case MsSeller::STATUS_DISABLED:
                    case MsSeller::STATUS_DELETED:
                        $products = $this->MsLoader->MsProduct->getProducts(array(
                            'seller_id' => $seller['seller_id']
                        ));

                        foreach ($products as $p) {
                            $this->MsLoader->MsProduct->changeStatus($p['product_id'], $data['seller']['status']);
                        }

                        $data['seller']['approved'] = 0;
                        break;
                    case MsSeller::STATUS_ACTIVE:
                        if (
                            $seller['ms.seller_status'] == MsSeller::STATUS_INACTIVE &&
                            $this->config->get('msconf_allow_inactive_seller_products')
                        ) {
                            $products = $this->MsLoader->MsProduct->getProducts(array(
                                'seller_id' => $seller['seller_id']
                            ));

                            foreach ($products as $p) {
                                $this->MsLoader->MsProduct->changeStatus($p['product_id'], $data['seller']['status']);
                                if ($this->config->get('msconf_product_validation') == MsProduct::MS_PRODUCT_VALIDATION_NONE) {
                                    $this->MsLoader->MsProduct->approve($p['product_id']);
                                }
                            }
                        }

                        $data['seller']['approved'] = 1;
                        break;
                }

                $this->MsLoader->MsSeller->adminEditSeller(
                    array_merge(
                        $data['seller'],
                        array(
                            'approved' => 1,
                        )
                    )
                );
            }

            // send mail
            if ($data['seller']['notify']) {
                $this->MsLoader->MsMail->sendMails($mails);
            }
            
            // send sms notification if app smshare installed and configured for seller status
            $seller_info = $this->MsLoader->MsSeller->getSellerBasic($data['seller']['seller_id']);
            if($seller_info) {
                $_vars = [
                    'store_url'        => HTTP_CATALOG,
                    'seller_email'     => $seller_info['email'],
                    'seller_firstname' => $seller_info['firstname'],
                    'seller_lastname'  => $seller_info['lastname'],
                    'seller_nickname'  => $seller_info['nickname'],
                    'seller_mobile'    => $seller_info['mobile'],
                ];
                
                $sms[] = array(
                    'type' => (int) $data['seller']['status'],
                    'data' => $_vars
                );
                
                $this->MsLoader->MsSms->send($sms);
				//whatsapp
				if (\Extension::isInstalled('whatsapp')) {
					//$json['logs']=$data['seller']['seller_id'];
					$json['logs']=$this->MsLoader->MsWhats->send($sms);
				}
				
				//whatsapp_cloud
				if (\Extension::isInstalled('whatsapp_cloud')) {
					$json['logs']=$this->MsLoader->MsWhatsappCloud->send($sms);
				}
            }
            
            $json['success'] = 1;
            $json['success_msg'] = 'Seller account data saved.';

        } else {
            $json['errors']['warning'] = $this->language->get('error_warning');
        }

        $this->response->setOutput(json_encode($json));
    }

    // simple paypal balance payout
    public function jxPayBalance()
    {
        $json = array();
        $seller_id = isset($this->request->get['seller_id']) ? $this->request->get['seller_id'] : 0;
        $seller = $this->MsLoader->MsSeller->getSeller($seller_id);

        if (!$seller) return;

        $sellerBalance = $this->MsLoader->MsBalance->getSellerBalance($seller_id);

        $reservedSellerFunds = $this->MsLoader->MsBalance->getReservedSellerFunds($seller_id);

        $amount = $sellerBalance - $reservedSellerFunds;

        if (!$amount) return;

        //create payment
        $payment_id = $this->MsLoader->MsPayment->createPayment(array(
            'seller_id' => $seller_id,
            'payment_type' => MsPayment::TYPE_PAYOUT,
            'payment_status' => MsPayment::STATUS_UNPAID,
            'payment_data' => $seller['ms.paypal'],
            'payment_method' => MsPayment::METHOD_PAYPAL,
            'amount' => $this->currency->format($amount, $this->config->get('config_currency'), '', FALSE),
            'currency_id' => $this->currency->getId($this->config->get('config_currency')),
            'currency_code' => $this->currency->getCode($this->config->get('config_currency')),
            'description' => sprintf(
                $this->language->get('ms_payment_royalty_payout'), $seller['name'],
                $this->config->get('config_name')
            )
        ));

        // render paypal form
        $this->data['payment_data'] = array(
            'sandbox' => $this->config->get('msconf_paypal_sandbox'),
            'action' => $this->config->get('msconf_paypal_sandbox') ? "https://www.sandbox.paypal.com/cgi-bin/webscr" : "https://www.paypal.com/cgi-bin/webscr",
            'business' => $seller['ms.paypal'],
            'item_name' => sprintf(
                $this->language->get('ms_payment_royalty_payout'),
                $seller['name'],
                $this->config->get('config_name')
            ),
            'amount' => $this->currency->format($amount, $this->config->get('config_currency'), '', FALSE),
            'currency_code' => $this->config->get('config_currency'),
            'return' => $this->url->link('multiseller/seller', ''),
            'cancel_return' => $this->url->link('multiseller/seller', ''),
            'notify_url' => HTTP_CATALOG . 'index.php?route=payment/multimerch-paypal/payoutIPN',
            'custom' => $payment_id
        );

        list($this->template) = $this->MsLoader->MsHelper->admLoadTemplate('payment/multimerch-paypal');

        $json['form'] = $this->render();
        $json['success'] = 1;
        $this->response->setOutput(json_encode($json));
    }

    public function delete()
    {
        $seller_id = isset($this->request->get['seller_id']) ? $this->request->get['seller_id'] : 0;
        $this->MsLoader->MsSeller->deleteSeller($seller_id);
        $this->redirect($this->url->link('multiseller/seller', '', 'SSL'));
    }

    public function index()
    {

        $this->validate(__FUNCTION__);

        // paypal listing payment confirmation
        if (
            isset($this->request->post['payment_status']) &&
            strtolower($this->request->post['payment_status']) == 'completed'
        ) {
            $this->data['success'] = $this->language->get('ms_payment_completed');
        }

        $formattedTotalBalance = $this->currency->format(
            $this->MsLoader->MsBalance->getTotalBalanceAmount(),
            $this->config->get('config_currency')
        );

        $formattedActiveTotalBalance = $this->currency->format(
            $this->MsLoader->MsBalance->getTotalBalanceAmount(
                array('seller_status' => array(MsSeller::STATUS_ACTIVE))
            ), $this->config->get('config_currency')
        );

        $this->data['total_balance'] = sprintf(
            $this->language->get('ms_catalog_sellers_total_balance'),
            $formattedTotalBalance,
            $formattedActiveTotalBalance
        );

        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        $this->data['heading'] = $this->language->get('ms_catalog_sellers_heading');
        $this->data['link_create_seller'] = $this->url->link('multiseller/seller/create', '', 'SSL');
        $this->document->setTitle($this->language->get('ms_catalog_sellers_heading'));

        $this->data['breadcrumbs'] = $this->MsLoader->MsHelper->admSetBreadcrumbs(array(
            array(
                'text' => $this->language->get('ms_menu_multiseller'),
                'href' => $this->url->link('module/multiseller', '', 'SSL'),
            ),
            array(
                'text' => $this->language->get('ms_catalog_sellers_breadcrumbs'),
                'href' => $this->url->link('multiseller/seller', '', 'SSL'),
            )
        ));
         $this->data['tripsApp']=false;
        if(\Extension::isInstalled('trips')&&$this->config->get('trips')['status']==1) $this->data['tripsApp']=true;

        $this->template = 'multiseller/seller/list.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    public function create()
    {
        $this->validate(__FUNCTION__);
        $this->load->model('localisation/country');
        $this->load->model('tool/image');
        $this->data['countries'] = $this->model_localisation_country->getCountries();
        $this->data['customers'] = $this->MsLoader->MsSeller->getCustomers();
        $this->data['seller_groups'] = $this->MsLoader->MsSellerGroup->getSellerGroups();
        $this->data['seller'] = FALSE;

        $this->data['currency_code'] = $this->config->get('config_currency');
        $this->data['heading'] = $this->language->get('ms_catalog_sellerinfo_heading');

        $this->document->setTitle($this->language->get('ms_catalog_sellers_newseller'));

        $this->data['breadcrumbs'] = $this->MsLoader->MsHelper->admSetBreadcrumbs(array(
            array(
                'text' => $this->language->get('ms_menu_multiseller'),
                'href' => $this->url->link('module/multiseller', '', 'SSL'),
            ),
            array(
                'text' => $this->language->get('ms_catalog_sellers_breadcrumbs'),
                'href' => $this->url->link('multiseller/seller', '', 'SSL'),
            ),
            array(
                'text' => $this->language->get('ms_catalog_sellers_newseller'),
                'href' => $this->url->link('multiseller/seller/create', 'SSL'),
            )
        ));

        $this->data['rateSale'] = MsCommission::RATE_SALE;
        $this->data['rateListing'] = MsCommission::RATE_LISTING;
        $this->data['leftCurrencySymbol'] = $this->currency->getSymbolLeft();
        $this->data['rightCurrencySymbol'] = $this->currency->getSymbolRight();

        $msSeller = new ReflectionClass('MsSeller');

        $this->data['customerStatuses'] = [];
        foreach ($msSeller->getConstants() as $constant => $value) {
            if (strpos($constant, 'STATUS_') !== FALSE) {
                $this->data['customerStatuses'][$constant] = $value;
            }
        }

        $this->data['method_balance'] = MsPayment::METHOD_BALANCE;
        $this->data['method_paypal'] = MsPayment::METHOD_PAYPAL;

        $this->document->addScript('view/javascript/multimerch/seller-form.js');
        $this->document->addScript('view/javascript/multimerch/plupload/plupload.full.js');
        $this->document->addScript('view/javascript/multimerch/plupload/jquery.plupload.queue/jquery.plupload.queue.js');

        $this->template = 'multiseller/seller/form.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    public function update()
    {
        $this->validate(__FUNCTION__);
        $this->load->model('localisation/country');
        $this->load->model('tool/image');
        $this->data['countries'] = $this->model_localisation_country->getCountries();

        $seller = $this->MsLoader->MsSeller->getSeller($this->request->get['seller_id']);

        $this->data['seller_groups'] = $this->MsLoader->MsSellerGroup->getSellerGroups();

        if(\Extension::isInstalled('trips') &&$this->config->get('trips')['status']==1)
        {
         $this->load->model('module/trips');
         $this->data['driver']= $this->model_module_trips->getTripCustomer($this->request->get['seller_id']);
         $this->data['isTripsInstalled']=true;        
        }

        if (!empty($seller)) {
            $rates = $this->MsLoader->MsCommission->calculateCommission(array('seller_id' => $this->request->get['seller_id']));
            $actual_fees = [];
            foreach ($rates as $rate) {
                if ($rate['rate_type'] == MsCommission::RATE_SIGNUP) continue;
                $actual_fees[] = '<span class="fee-rate-' . $rate['rate_type'] . '">';
                $actual_fees[] = '<b>' . $this->language->get('ms_commission_short_' . $rate['rate_type']) . ':</b>';
                $actual_fees[] = $rate['percent'] . '%+' . $this->currency->getSymbolLeft();
                $actual_fees[] = $this->currency->format(
                    $rate['flat'],
                    $this->config->get('config_currency'),
                    '',
                    false
                );
                $actual_fees[] = $this->currency->getSymbolRight() . '&nbsp;&nbsp;';
            }

            $this->data['seller'] = $seller;

            //Seller Balance
            $this->data['seller']['ms.balance'] = $this->MsLoader->MsBalance->getSellerBalance($seller['seller_id']);

            $this->data['seller']['actual_fees'] = implode('', $actual_fees);

            if (!empty($seller['ms.avatar'])) {
                $this->data['seller']['avatar']['name'] = \Filesystem::getUrl("image/".$seller['ms.avatar']);
                $this->data['seller']['avatar']['thumb'] = $this->MsLoader->MsFile->resizeImage(
                    $seller['ms.avatar'],
                    $this->config->get('msconf_preview_seller_avatar_image_width'),
                    $this->config->get('msconf_preview_seller_avatar_image_height')
                );
                //$this->session->data['multiseller']['files'][] = $seller['avatar'];
            }

            if (!empty($seller['ms.commercial_image'])) {
                $this->data['seller']['commercial_image']['name'] = \Filesystem::getUrl("image/".$seller['ms.commercial_image']);
                $this->data['seller']['commercial_image']['thumb'] = $this->MsLoader->MsFile->resizeImage(
                    $seller['ms.commercial_image'],
                    250,
                    250
                );
            }

            if (!empty($seller['ms.license_image'])) {
                $this->data['seller']['license_image']['name'] = \Filesystem::getUrl("image/".$seller['ms.license_image']);
                $this->data['seller']['license_image']['thumb'] = $this->MsLoader->MsFile->resizeImage(
                    $seller['ms.license_image'],
                    250,
                    250
                );
            }

            if (!empty($seller['ms.tax_image'])) {
                $this->data['seller']['tax_image']['name'] = \Filesystem::getUrl("image/".$seller['ms.tax_image']);
                $this->data['seller']['tax_image']['thumb'] = $this->MsLoader->MsFile->resizeImage(
                    $seller['ms.tax_image'],
                    250,
                    250
                );
            }

            if (!empty($seller['ms.image_id'])) {
                $this->data['seller']['image_id']['name'] = \Filesystem::getUrl("image/".$seller['ms.image_id']);
                $this->data['seller']['image_id']['thumb'] = $this->MsLoader->MsFile->resizeImage(
                    $seller['ms.image_id'],
                    250,
                    250
                );
            }

            if (!empty($seller['ms.commercial_image'])) {
                $this->data['seller']['commercial_image']['name'] = \Filesystem::getUrl("image/".$seller['ms.commercial_image']);
                $this->data['seller']['commercial_image']['thumb'] = $this->MsLoader->MsFile->resizeImage(
                    $seller['ms.commercial_image'],
                    250,
                    250
                );
            }

            if (is_null($seller['ms.commission_id']))
                $rates = NULL;
            else
                $rates = $this->MsLoader->MsCommission->getCommissionRates($seller['ms.commission_id']);

            $this->data['seller']['commission_id'] = $seller['ms.commission_id'];
            $this->data['seller']['commission_rates'] = $commission_rates = $rates;
        }

        $this->data['rateSale'] = MsCommission::RATE_SALE;
        $this->data['rateListing'] = MsCommission::RATE_LISTING;
        $this->data['leftCurrencySymbol'] = $this->currency->getSymbolLeft();
        $this->data['rightCurrencySymbol'] = $this->currency->getSymbolRight();

        if (isset($commission_rates[MsCommission::RATE_SALE]['percent'])) {
            $this->data['commission_sale']['percent'] = $commission_rates[MsCommission::RATE_SALE]['percent'];
        }

        if (isset($commission_rates[MsCommission::RATE_SALE]['flat'])) {
            $this->data['commission_sale']['flat'] = $this->currency->format(
                $commission_rates[MsCommission::RATE_SALE]['flat'],
                $this->config->get('config_currency'),
                '',
                false
            );
        }

        if (isset($commission_rates[MsCommission::RATE_LISTING]['percent'])) {
            $this->data['commission_listing']['percent'] = $commission_rates[MsCommission::RATE_LISTING]['percent'];
        }

        if (isset($commission_rates[MsCommission::RATE_LISTING]['flat'])) {
            $this->data['commission_listing']['flat'] = $this->currency->format(
                $commission_rates[MsCommission::RATE_LISTING]['flat'],
                $this->config->get('config_currency'),
                '',
                false
            );
        }

        $this->data['method_balance'] = MsPayment::METHOD_BALANCE;
        $this->data['method_paypal'] = MsPayment::METHOD_PAYPAL;

        if (isset($commission_rates[MsCommission::RATE_LISTING])) {
            $this->data['payment_method'] = $commission_rates[MsCommission::RATE_LISTING]['payment_method'];
        }

        $msSeller = new ReflectionClass('MsSeller');

        $this->data['customerStatuses'] = [];
        foreach ($msSeller->getConstants() as $constant => $value) {
            if (strpos($constant, 'STATUS_') !== FALSE) {
                $this->data['customerStatuses'][$constant] = $value;
            }
        }

        $this->data['currency_code'] = $this->config->get('config_currency');
        $this->data['heading'] = $this->language->get('ms_catalog_sellerinfo_heading');
        $this->document->setTitle($this->language->get('ms_catalog_sellerinfo_heading'));

        $this->data['breadcrumbs'] = $this->MsLoader->MsHelper->admSetBreadcrumbs(array(
            array(
                'text' => $this->language->get('ms_menu_multiseller'),
                'href' => $this->url->link('module/multiseller', '', 'SSL'),
            ),
            array(
                'text' => $this->language->get('ms_catalog_sellers_breadcrumbs'),
                'href' => $this->url->link('multiseller/seller', '', 'SSL'),
            ),
            array(
                'text' => $seller['ms.nickname'],
                'href' => $this->url->link('multiseller/seller/update', '&seller_id=' . $seller['seller_id'], 'SSL'),
            )
        ));

        if (($subscription_system_status = $this->config->get('msconf_enable_subscriptions_plans_system')) == 1) {
            $this->data['subscription_system_status'] = $this->config->get('msconf_enable_subscriptions_plans_system');

            $this->load->model('multiseller/subscriptions');
            $model = $this->model_multiseller_subscriptions;

            $this->data['subscriptions_plans'] = $model->listDetailedPlansBySeller($this->request->get['seller_id']);

            $this->data['formats'] = [
                '1' => $this->language->get('day'),
                '2' => $this->language->get('month'),
                '3' => $this->language->get('year'),
            ];
        }

        $this->document->addScript('view/javascript/multimerch/seller-form.js');
        $this->document->addScript('view/javascript/multimerch/plupload/plupload.full.js');
        $this->document->addScript('view/javascript/multimerch/plupload/jquery.plupload.queue/jquery.plupload.queue.js');

        $this->template = 'multiseller/seller/form.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->response->setOutput($this->render());
    }

    // Update seller status in ajax
    public function dtUpdateStatus()
    {
        if (isset($this->request->post["id"]) && isset($this->request->post["status"])) {
            $id = $this->request->post["id"];
            $status = $this->request->post["status"];

            $this->MsLoader->MsSeller->updateSellerStatus($id, $status);

            //Notify Seller
            $seller_info = $this->MsLoader->MsSeller->getSellerBasic($id);
            $_vars = [
                        'store_url'        => HTTP_CATALOG,
                        'seller_email'     => $seller_info['email'], 
                        'seller_firstname' => $seller_info['firstname'],
                        'seller_lastname'  => $seller_info['lastname'],
                        'seller_nickname'  => $seller_info['nickname'],
                        'seller_mobile'    => $seller_info['mobile'],
                    ];
                    
            if($status == 1){
                $mails[] = array(
                            'type' => MsMail::SMT_SELLER_ACCOUNT_ENABLED,
                            'data' => array('class' => 'ControllerMultisellerSeller', 
                                            'function' => 'dtUpdateStatus_enable',
                                            'vars'   => $_vars,
                                            'recipients' => $seller_info['email'],
                                            'addressee' => $seller_info['firstname']
                                        )
                        );
            }else if($status == 2){
                $mails[] = array(
                            'type' => MsMail::SMT_SELLER_ACCOUNT_DISABLED,
                            'data' => array('class' => 'ControllerMultisellerSeller', 
                                            'function' => 'dtUpdateStatus_disable',
                                            'vars'   => $_vars,
                                            'recipients' => $seller_info['email'],
                                            'addressee' => $seller_info['firstname']
                                        )
                        );
            }
            
            // send sms notification if smshare installed and configured for seller status
            $sms[] = array(
                'type' => (int)$status,
                'data' => $_vars
            );
            
            $this->MsLoader->MsSms->send($sms);
            //whatsapp
			if (\Extension::isInstalled('whatsapp')) {
				$this->MsLoader->MsWhats->send($sms);
			}
			//whatsapp_cloud
			if (\Extension::isInstalled('whatsapp_cloud')) {
				$this->MsLoader->MsWhatsappCloud->send($sms);
			}
			
            $this->MsLoader->MsMail->sendMails($mails);
            //////

            $response['success'] = '1';
            $response['success_msg'] = $this->language->get('message_updated_successfully');
        } else {
            $response['success'] = '0';
            $response['errors'] = $this->language->get('notification_unknoen_error');
        }

        $this->response->setOutput(json_encode($response));
        return;
    }

    public function dtDelete()
    {
        if (isset($this->request->post['selected'])) {
            foreach ($this->request->post['selected'] as $seller_id) {
                $this->MsLoader->MsSeller->deleteSeller($seller_id);
            }

            $response['status'] = 'success';
            $response['title'] = $this->language->get('notification_success_title');
            $response['message'] = $this->language->get('message_deleted_successfully');
        } else {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = [];
        }

        $this->response->setOutput(json_encode($response));
        return;
    }

    public function download(){
        $download_path = $this->request->get['download'];
        \Filesystem::setPath($download_path);
		if (!headers_sent()) {
			if (\Filesystem::isExists()) {
				header('Content-Type: application/octet-stream');
				header('Content-Description: File Transfer');
				header('Content-Disposition: attachment; filename="' . ( basename($download_path)) . '"');
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: ' .\Filesystem::getSize());
				if (ob_get_level()) ob_end_clean();
				//readfile($download_path, 'rb');
                echo \Filesystem::read();
				exit;
			} 
		}
    }
}
