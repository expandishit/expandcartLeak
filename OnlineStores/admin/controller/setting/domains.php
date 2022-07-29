<?php
class ControllerSettingDomains extends Controller
{
    private $error = array();

    public function index()
    {
        if ($this->request->get['card_name']){
            $this->session->data['card_name'] = $this->request->get['card_name'];
        }

        $this->language->load('setting/domains');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['token'] = $this->session->data['token'];
        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['text_domainname'] = $this->language->get('text_domainname');
        $this->data['text_action'] = $this->language->get('text_action');
        $this->data['text_delete'] = $this->language->get('text_delete');
        $this->data['text_adddomain'] = $this->language->get('text_adddomain');
        $this->data['direction'] = $this->language->get('direction');
        $this->data['text_buynewdomain'] = $this->language->get('text_buynewdomain');

        $this->load->model('setting/domainsetting');

        $this->data['domains_count'] = $this->model_setting_domainsetting->countDomain();

        $domains = $this->model_setting_domainsetting->getDomains();

        $status = 0;
        $status_changed = 0;
        // 34.107.102.192
        foreach ($domains as $key =>$domain){
            $ips = gethostbynamel($domain['DOMAINNAME']);
            if (is_array($ips) && count($ips) == 1 && $ips[0] == "34.107.102.192"){
                $domains[$key]['status']="1";
                if(!$status_changed){
                    $status = 1;
                    $status_changed = 1;
                }
            }else{
                $domains[$key]['status']="0";
                $status = 0;
                $status_changed = 1;
            }
        }

        $this->data['domains_status'] = $status;
        $this->data['domains'] = $domains;

        $this->load->model('billingaccount/common');

        # Define WHMCS URL & AutoAuth Key
        $whmcsurl = MEMBERS_LINK;
        $autoauthkey = MEMBERS_AUTHKEY;

        $langParam = '&language=English';

        if ($this->language->get('code') == 'ar') {
            $langParam = '&language=Arabic';
        }

        $timestamp = time(); # Get current timestamp
        $email = BILLING_DETAILS_EMAIL; # Clients Email Address to Login
        $registerDomainLink = "cart.php?a=add&domain=register" . $langParam;

        $hash = sha1($email.$timestamp.$autoauthkey); # Generate Hash

        $this->data['url_registerDomainLink'] = $whmcsurl."?email=$email&timestamp=$timestamp&hash=$hash&goto=".urlencode($registerDomainLink);

        $store_code= STORECODE;

        $this->data['store_url'] ="https://$store_code.expandcart.com";

        $this->data['breadcrumbs'][] = array(
            'href'      => $this->url->link('common/home'),
            'text'      => $this->language->get('text_home'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'href'      => $this->url->link('setting/domains'),
            'text'      => $this->language->get('heading_title'),
            'separator' => ' :: '
        );

        $this->template = 'setting/domains.expand';

//        if (!$domain_count){
//            $this->template = 'setting/domains/empty.expand';
//        }else{
//            $this->template = 'setting/domains.expand';
//        }

        $this->base = 'common/base';

        $this->response->setOutput($this->render());
    }

    public function insert() {

        $result_json = array(
            'success' => '0',
            'errors' => array(),
            'success_msg' => ''
        );
        $this->language->load('setting/domains');
        $this->language->load('setting/setting');
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if ($this->request->post['DomainName'] && !empty($this->request->post['DomainName'])) {
                $this->load->model('setting/domainsetting');
                $domainname = strtolower($this->request->post['DomainName']);
                $domainname = str_ireplace("http://", "", $domainname);
                $domainname = str_ireplace("https://", "", $domainname);
                $domainname = str_ireplace("www.", "", $domainname);
                $domainname = 'http://' . $domainname;
                $domainname = strtoupper(parse_url($domainname, PHP_URL_HOST));
                $NoOfDomains = $this->model_setting_domainsetting->countDomain();

                $this->load->model('module/dedicated_domains/domains');
                $domains_limit = $this->model_module_dedicated_domains_domains->isActive() ? 10 : 3;
                
                if($NoOfDomains >= $domains_limit) {
                    $result_json['success'] = false;
                    $result_json['errors']['domain_name'] = $this->language->get('error_domainlimit');
                    $result_json['success_msg'] = '';
                }
                else if (!$this->is_valid_domain_name($this->request->post['DomainName'])){
                    $result_json['success'] = false;
                    $result_json['errors']['domain_name'] = $this->language->get('error_wrong_domain_name');
                    $result_json['success_msg'] = '';
                }
                else {
                    $DomainExists = $this->model_setting_domainsetting->countDomain($domainname);
                    if ($DomainExists <= 0) {
                        $result_json['uniqueid'] = $this->model_setting_domainsetting->addDomain($domainname);
                        $result_json['domainname'] = strtolower($domainname);

                        /***************** Start  ExpandCartTracking #347702  ****************/
                        // send mixpanel Link Domain
                        $this->load->model('setting/mixpanel');
                        $this->model_setting_mixpanel->trackEvent('Link Domain',['Domain Name' => $result_json['domainname']]);

                        // send amplitude Link Domain
                        $this->load->model('setting/amplitude');
                        $this->model_setting_amplitude->trackEvent('Link Domain',['Domain Name' => $result_json['domainname']]);

                        /***************** End ExpandCartTracking #347702  ****************/

                        $result_json['success'] = true;
                        $result_json['errors'] = '';
                        $result_json['success_msg'] = $this->language->get('text_addsuccess');

                    } else {
                        $result_json['success'] = false;
                        $result_json['errors']['domain_name'] = $this->language->get('error_domainexists');
                        $result_json['success_msg'] = '';
                    }
                }
            }else{
                $result_json['success'] = false;
                $result_json['errors']['domain_name'] = $this->language->get('error_field_cant_be_empty');
                $result_json['success_msg'] = '';
            }
        }
        $this->response->setOutput(json_encode($result_json));
    }

    public function delete()
    {
        $result_json = array(
            'success' => '0',
            'errors' => array(),
            'success_msg' => ''
        );

        $this->language->load('setting/domains');
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if ($this->request->post['UniqueId']) {
                $this->load->model('setting/domainsetting');
                $uniqueid = $this->request->post['UniqueId'];
                $this->model_setting_domainsetting->deleteDomain($uniqueid);
                $result_json['deleted'] = true;
                $result_json['success'] = true;
                $result_json['success_msg'] = $this->language->get('text_deletesuccess');
                $result_json['errors'] = '';
            }
        }
        $this->response->setOutput(json_encode($result_json));
    }

    private function is_valid_domain_name($domain_name)
    {
        $domain_name = str_ireplace("www.", "", $domain_name);

        if (strpos($domain_name, '.') === false) {
            return false;
        }

        return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name) //valid chars check
            && preg_match("/^.{1,253}$/", $domain_name) //overall length check
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name)   ); //length of each label
    }

}