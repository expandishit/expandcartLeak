<?php

class ControllerSaleAbandonedCart extends Controller
{
    private $plan_id = PRODUCTID;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('plan/trial');
        $trial = $this->model_plan_trial->getLastTrial();
        if ($trial){
            $this->plan_id = $trial['plan_id'];
        }
    }

    public function showInstall(){
        $this->getAppData(44);

        $this->template = 'marketplace/home/abandoned_cart.expand';

        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    /** /Old setup **/

    public function list()
    {
        $this->check_app_installed();
        // if($this->request->get['content_url'] != "sale/abandoned_cart/list"){
        //     $this->redirect($this->url->link('sale/component/orders', 'content_url=sale/abandoned_cart/list', 'SSL'));
        // }
        $this->language->load('module/abandoned_cart');
        $this->language->load('sale/order');
        $this->language->load('catalog/product_filter');

        $this->document->setTitle($this->language->get('abandoned_cart_heading_title'));

        $this->load->model('sale/customer');
        $this->load->model('sale/order');

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('marketplace/home', 'type=apps', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('abandoned_cart_heading_title'),
            'href'      => $this->url->link('module/abandoned_cart', '', 'SSL'),
            'separator' => ' :: '
        );

        //$customers = $this->model_sale_customer->getCustomers();

        // array_unshift($customers, [
        //     'customer_id' => 0,
        //     'name' => $this->language->get('text_guest')
        // ]);

        $total_abandoned_carts = $this->model_sale_order->getTotalAbandonedCartOrders();

        $this->initializer([
            'module/abandoned_cart/settings',
            'emailedOrders' => 'module/abandoned_cart/emailed_orders',
        ]);

        $this->data['filterElements'] = [
            //'customers' => $customers,
            'totalRange' => $this->emailedOrders->getOrderMinMaxTotal(),
            'products' => $this->emailedOrders->getAllOrdersProducts()
        ];
        if($total_abandoned_carts == 0) {
            $this->template = 'module/abandoned_cart/empty.expand';
        } else {
            $this->template = 'module/abandoned_cart/list.expand';
        }
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render_ecwig());
    }

    public function datatable()
    {
        $this->load->model('sale/order');
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
            1 => 'order_id',
            2 => 'customer',
            4 => 'total',
            5 => 'date_added',
            6 => 'date_modified',
            7 => '',
        );

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $data = array(
            'filter_model' => $filter_model,
            'filter_price' => $filter_price,
            'filter_quantity' => $filter_quantity,
            'filter_status' => $filter_status,
            'sort' => $orderColumn,
            'order' => $orderType,
            'start' => $request['start'],
            'limit' => $request['length']
        );
        
        $return = $this->model_sale_order->getAbandonedCartOrders($data, $filterData);

        $this->initializer([
            'module/abandoned_cart/settings',
            'emailedOrders' => 'module/abandoned_cart/emailed_orders',
        ]);

        $results = [];

        foreach ($return['data'] as $key => $set) {
            $results[$key] = $set;
            $results[$key]['emailed'] = $this->emailedOrders->getEmailedOrdersByOrderId($set['order_id']);
        }

        $records = $results;
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $json_data = [
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data" => $records,
        ];
        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function deleteAbandonedOrder()
    {
        $this->check_app_installed();
        $this->language->load('sale/order');

        $this->initializer([
            'sale/order',
            'module/abandoned_cart/settings',
            'emailedOrders' => 'module/abandoned_cart/emailed_orders',
        ]);

        if (isset($this->request->post['selected'])) {
            foreach ($this->request->post['selected'] as $order_id) {
                $this->order->deleteOrder($order_id, null);
                $this->emailedOrders->deleteEmailedOrder($order_id);
            }

            $response['status'] = 'success';
            $response['title'] = $this->language->get('notification_success_title');
            $response['message'] = $this->language->get('message_deleted_successfully');
        } else {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = $this->error;
        }

        $this->response->setOutput(json_encode($response));
        return;
    }

    protected function getAppData($id)
    {

        $this->initializer([
            'marketplace/appservice',
            'setting/extension',
            'marketplace/common'
        ]);

        $isApp=0;
        $isService=0;
        $isInstalled=0;
        $isPurchased=0;
        $isFree=0;

        $this->language->load('marketplace/app');

        //$this->document->setTitle($this->language->get('heading_title'));

        //$this->data['heading_title'] = $this->language->get('heading_title');

        // $this->load->model('marketplace/common');

        $result =$this->appservice->getAppServiceById($id);

        $isApp = ($result['type']==1);
        $isService = ($result['type']==2);
        $isPurchased=($result['storeappserviceid'] != -1);
        $isFree=($result['packageappserviceid'] != -1);
        $promoCode = !empty($result['link']) && PRODUCTID >= $result['freeplan'] ? $result['link'] : '';
        // if($isApp && ($isPurchased || $isFree)) {
            //$this->load->model('setting/extension');
            $installedextensions = $this->model_setting_extension->getInstalled('module');
            $isInstalled = in_array($result['CodeName'], $installedextensions);
        // }

        $this->document->setTitle($result['Name']);
        $this->data['heading_title'] = $result['Name'];

        $this->data['text_install'] = $this->language->get('text_install');
        $this->data['text_uninstall'] = $this->language->get('text_uninstall');
        $this->data['text_edit'] = $this->language->get('text_edit');
        $this->data['text_new'] = $this->language->get('text_new');
        $this->data['text_installed'] = $this->language->get('text_installed');
        $this->data['text_purchased'] = $this->language->get('text_purchased');
        $this->data['text_item'] = $this->language->get('text_item');
        $this->data['text_buy'] = $this->language->get('text_buy');
        $this->data['text_rec_once'] = $this->language->get('text_rec_once');
        $this->data['text_free_all'] = $this->language->get('text_free_all');
        $this->data['text_free_business'] = $this->language->get('text_free_business');
        $this->data['text_free_ultimate'] = $this->language->get('text_free_ultimate');
        $this->data['text_free_enterprise'] = $this->language->get('text_free_enterprise');
        $this->data['text_yearly_only'] = $this->language->get('text_yearly_only');
        $this->data['text_choose_plans'] = $this->language->get('text_choose_plans');
        $this->data['text_choose_plans_text'] = $this->language->get('text_choose_plans_text');
        $this->data['text_explore_plans'] = $this->language->get('text_explore_plans');
        $this->data['direction'] = $this->language->get('direction');

        $this->data['id'] = $this->session->data['appid'] = $id;
        $this->data['moduleType'] = $result['type'];
        $this->data['name'] = $result['Name'];
        $this->data['minidesc'] = $result['MiniDescription'];
        $this->data['category'] = $result['category'];
        $this->data['desc'] = $result['Description'];
        $this->data['image'] = $result['AppImage'];
        $this->data['coverimage'] = $result['CoverImage'];
        $this->data['price'] = '$'.((floor($result['price']) == round($result['price'], 2)) ? number_format($result['price']) : number_format($result['price'], 2));
        $this->data['isnew'] = $result['IsNew'];
        $this->data['isquantity'] = $result['IsQuantity'];
        $this->data['recurring'] = $result['recurring'];
        $this->data['isapp'] = $isApp;
        $this->data['isservice'] = $isService;
        $this->data['isinstalled'] = $isInstalled;
        $this->data['ispurchased'] = $isPurchased;
        $this->data['installed'] = $isInstalled;
        $this->data['purchased'] = $isPurchased;
        $this->data['isfree'] = $isFree;
        $this->data['isbundle'] = $isFree;
        $this->data['freeplan'] = $result['freeplan'];
        $this->data['freepaymentterm'] = $result['freepaymentterm'];
        $this->data['PRODUCTID'] = PRODUCTID;
        $this->data['extension']  = $result['CodeName'];
        
        $this->data['isTrial'] = PRODUCTID == 3 ? '1' : '0';

        $activeTrials = array_column($this->extension->getActiveTrials(), null, 'extension_code');

        if ($this->extension->isTrial($result['CodeName']) && !$isPurchased && !$isFree) {
            if (isset($activeTrials[$result['CodeName']]['deleted_at'])) {
                $isTrial = 2;
            } else {
                try {
                    $today = new DateTime('now');
                    $created_at = new DateTime($activeTrials[$result['CodeName']]['created_at']);
                    $diff = $today->diff($created_at)->days;
                    $remaining_time = 7 - (int) $diff;
                    $this->data['remaining_trial_time'] =$remaining_time;
                }
                catch (Exception $e) {}

                $isTrial = 1;
            }
        } else {
            $isTrial = 0;
        }

        // dd([$isTrial, $isinstalled]);

        $this->data['app_isTrial'] = $isTrial;

        $this->data['packageslink'] = $this->url->link('billingaccount/plans', 'token=' . $this->session->data['token'], 'SSL');

        #required for paid apps/services
        $this->load->model('billingaccount/common');
        $timestamp = time(); # Get current timestamp
        $email = BILLING_DETAILS_EMAIL; # Clients Email Address to Login

        # Define WHMCS URL & AutoAuth Key
        $whmcsurl = MEMBERS_LINK;
        $autoauthkey = MEMBERS_AUTHKEY;
        $hash = sha1($email.$timestamp.$autoauthkey); # Generate Hash

        if ($this->user->hasPermission('access', 'billingaccount/plans') && ENABLE_BILLING == '1') {
            $billingAccess = '1';
        }

        $this->data['billingAccess'] = $billingAccess;

        $action = array();
        if($isApp) {
            #action: <install/uninstall, edit(if installed)> if free or purchased, <buy> if not purchased or not free
            $extension = $result['CodeName'];
            if(!$isPurchased && !$isFree) {
                #action: buy
                $tmpbuylink='cart.php?a=add&pid=' . $result['whmcsappserviceid'];
                $tmpbuylink = ($this->language->get('code') == 'ar') ? $tmpbuylink . '&language=Arabic' : $tmpbuylink = $tmpbuylink . '&language=English';
                if(!empty($promoCode)) {
                    $tmpbuylink .= '&promocode=' . $promoCode;
                }
                $buylink = ($billingAccess == "1") ? $whmcsurl."?email=$email&timestamp=$timestamp&hash=$hash&goto=".urlencode($tmpbuylink) : '#';

                $this->data['buylink'] = $buylink;
            }
            else{
                $action[] = array(
                    'text' => $isInstalled ? $this->language->get('text_uninstall') : $this->language->get('text_install'),
                    'icon' => $isInstalled ? "icon-cancel-circle2" : "icon-download",
                    'href' => $this->url->link('marketplace/app/'. ($isInstalled ? 'uninstall' : 'install'), 'token=' . $this->session->data['token'] . '&extension=' . $extension . '&id=' . $id, 'SSL')
                );
                $this->data['extensionlink'] = $this->url->link('module/' . $extension . '', 'token=' . $this->session->data['token'], 'SSL');
                $this->data['actions'] = $action;
            }
        }
        elseif($isService) {
            #action: request service
            $tmpbuylink='cart.php?a=add&pid=' . $result['whmcsappserviceid'];
            $tmpbuylink = ($this->language->get('code') == 'ar') ? $tmpbuylink . '&language=Arabic' : $tmpbuylink = $tmpbuylink . '&language=English';
            if(!empty($promoCode)) {
                $tmpbuylink .= '&promocode=' . $promoCode;
            }
            $buylink = ($billingAccess == "1") ? $whmcsurl."?email=$email&timestamp=$timestamp&hash=$hash&goto=".urlencode($tmpbuylink) : '#';

            $this->data['buylink'] = $buylink;
        }
    }

    public function check_app_installed(){
        $this->initializer([
            'AbandonedCart' => 'module/abandoned_cart/settings'
        ]);

        $abandoned_cart_app_status = $this->AbandonedCart->isActive();

        if(! $abandoned_cart_app_status ){
            $this->redirect(
                $this->url->link('marketplace/app?id=44', '', 'SSL')
            );
            exit();
        }
    }
}
