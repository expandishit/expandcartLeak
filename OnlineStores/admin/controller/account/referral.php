<?php

class ControllerAccountReferral extends Controller
{
    protected $user_id;

    private $error = array();

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->user_id = $this->user->getId();
    }

    public function index()
    {

        $this->initializer(['account/referral', 'account/invoice']);
        $this->language->load('account/referral');
        $this->document->setTitle($this->language->get('heading_title'));

        $code = null;
        if(!$code = $this->referral->get_my_code()){
            $currency = 'USD';
            $whmcs= new whmcs();
            $clientDetails = $whmcs->getClientDetails(WHMCS_USER_ID);
            $currenciesObject = $this->invoice->getCurrencies();

            $clientCurrencyId = $clientDetails['currency'];
            foreach($currenciesObject->currencies->currency as $c){
                if($c->id == $clientCurrencyId){
                    $currency = $c->code;
                    break;
                }
            }
            $code = $this->referral->insert_code($this->generate_code(), $currency);
        }

        $this->data['currency']=($code['currency']=="USD")?"$":$code['currency'];

        $pricingJSONFileName = "https://ectools.expandcart.com/storage/json/pricing.json";
        $pricingJSONAll = json_decode(file_get_contents($pricingJSONFileName), true);

        $reward_amount_pro_monthly=$pricingJSONAll['referral_rewards']['professional']['monthly']['reward_amount'][$code['currency']];
        $reward_amount_pro_annually=$pricingJSONAll['referral_rewards']['professional']['annually']['reward_amount'][$code['currency']];

        $reward_amount_ult_monthly=$pricingJSONAll['referral_rewards']['ultimate']['monthly']['reward_amount'][$code['currency']];
        $reward_amount_ult_annually=$pricingJSONAll['referral_rewards']['ultimate']['annually']['reward_amount'][$code['currency']];

        $this->data['text_intro_desc3'] = sprintf(
            $this->language->get('text_intro_desc3'), $reward_amount_pro_monthly,$this->data['currency'],$reward_amount_pro_annually,$this->data['currency']
        );

        $this->data['text_intro_desc4'] = sprintf(
            $this->language->get('text_intro_desc4'), $reward_amount_ult_monthly,$this->data['currency'],$reward_amount_ult_annually,$this->data['currency']
        );

        $min_redeem_amount = isset($pricingJSONAll['min_redeem_amount']) ? $pricingJSONAll['min_redeem_amount'] : 20;

        $this->data['min_redeem_amount'] = $min_redeem_amount;
        $this->data['code'] = $code['code'];
        $this->data['share_code_content'] = sprintf($this->language->get('text_share_content'), $code['code']);
        $this->data['error_no_enough_balance'] = sprintf($this->language->get('error_no_enough_balance'), $min_redeem_amount);
        $this->data['text_redeem_once'] = sprintf($this->language->get('text_redeem_once'), $min_redeem_amount,$this->data['currency']);
        $this->data['link'] = 'https://expandcart.com/en/register?referral=' . $code['code'];
        $this->data['count_registered'] = $this->referral->get_code_registerss_count($code['id']);
        $this->data['count_subscribed'] = $this->referral->get_code_subscribers_count($code['id']);
        $this->data['redeemed_balance'] = $this->referral->get_code_balance($code['id'], 'redeemed');
        $this->data['current_balance'] = $this->referral->get_code_balance($code['id'], 'current');
        $this->data['text_redeem_confirmation'] = sprintf($this->language->get('text_redeem_confirmation'), $this->data['current_balance'], $code['currency']);
        $this->data['langcode'] = $this->language->get('code');


        $this->data['main_message'] = json_decode($this->session->data['main_message']);
        unset($this->session->data['main_message']);

        $this->document->setTitle($this->language->get('heading_title'));
        $this->data['heading_title'] = $this->language->get('heading_title');
        
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('account/charge', '', 'SSL'),
            'separator' => false
        );

        $this->data['disableBreadcrumb'] = true;

        $this->data['flash_message'] = null;
        if (isset($this->session->data['referral'])) {
            $this->data['flash_message'] = $this->session->data['referral'];
            unset($this->session->data['referral']);
        }

        $this->template = 'account/referral/index.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

    private function generate_code()
    {
        $this->load->model('account/referral');
        $characters = str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789');
        $code = '';
        do{
            $code = substr($characters, 0, 10);
        }
        while($this->model_account_referral->get_referral_code($code, false) || $this->model_account_referral->get_reward_code($code, false));
        return $code;
    }

    public function history()
    {
        $this->load->model('account/referral');
        $this->language->load('account/referral');

        $request = $this->request->request;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            1 => 'store_name'
        );

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->model_account_referral->historyDtHandler([
            'sort' => $orderColumn,
            'order' => $orderType,
            'start' => $start,
            'length' => $length
        ]);

        $plans = [
            '53' => 'Professional',
            '6' => 'Ultimate',
            '8' => 'Enterprise'
        ];
        foreach ($return['data'] as $key => &$value) {
            $attributes = unserialize($value['attributes']);
            $value['product_name'] = $plans[$attributes['product_id']];
            $value['store_name'] = $attributes['store_name'];
        }

        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $json_data = array(
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data" => $records
        );
        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function reward_codes()
    {
        $this->load->model('account/referral');
        $this->language->load('account/referral');

        $request = $this->request->request;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            1 => 'created_at'
        );

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->model_account_referral->rewardCodesDtHandler([
            'sort' => $orderColumn,
            'order' => $orderType,
            'start' => $start,
            'length' => $length
        ]);

        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $json_data = array(
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data" => $records
        );
        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function redeem(){
        $this->load->model('account/referral');
        $this->language->load('account/referral');
        $json_data;

        $code = $this->model_account_referral->get_my_code();
        $balance = $this->model_account_referral->get_code_balance($code['id'], 0);

        $pricingJSONFileName = "https://ectools.expandcart.com/storage/json/pricing.json";
        $pricingJSONAll = json_decode(file_get_contents($pricingJSONFileName), true);
        $min_redeem_amount = isset($pricingJSONAll['min_redeem_amount']) ? $pricingJSONAll['min_redeem_amount'] : 20;

        if($balance < $min_redeem_amount){
            $json_data['success'] = false;
            $json_data['errors'] = [sprintf($this->language->get('error_no_enough_balance'), $min_redeem_amount)];
        }
        else{
            $this->model_account_referral->redeem($this->generate_code());
            $json_data['success'] = true;
            $json_data['message'] = $this->language->get('success_reward_code_generated');
        }

        $this->session->data['main_message'] = json_encode($json_data);
        $this->response->redirect($this->url->link('account/referral'));
    }
}
