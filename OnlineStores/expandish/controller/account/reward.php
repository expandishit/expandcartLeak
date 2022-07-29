<?php
class ControllerAccountReward extends Controller {
	public function index() {
        $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

        $this->data['rewardpoint_installed'] = false;
        if($queryRewardPointInstalled->num_rows) {
            $this->document->addStyle('expandish/view/javascript/rewardpoints/css/stylesheet.css');
            $this->data['rewardpoint_installed'] = true;
        }

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/reward', '', 'SSL');
			
	  		$this->redirect($this->url->link('account/login', '', 'SSL'));
        }
        
        $this->data['taps'] = $this->getChild('account/taps/renderTaps', 'account-reward');
        
		
		$this->language->load_json('account/reward', 1);

		$this->document->setTitle($this->language->get('heading_title'));

      	$this->data['breadcrumbs'] = array();

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	); 

      	$this->data['breadcrumbs'][] = array(       	
        	'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);
		
      	$this->data['breadcrumbs'][] = array(       	
        	'text'      => $this->language->get('text_reward'),
			'href'      => $this->url->link('account/reward', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);
		
		$this->load->model('account/reward');

				
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}		
		
		$this->data['rewards'] = array();
		
		$data = array(				  
			'sort'  => 'date_added',
			'order' => 'DESC',
			'start' => ($page - 1) * 10,
			'limit' => 10
		);
		
		$reward_total = $this->model_account_reward->getTotalRewards($data);
	
		$results = $this->model_account_reward->getRewards($data);
 		
    	foreach ($results as $result) {
            $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

            if($queryRewardPointInstalled->num_rows) {
                $this->data['rewards'][] = array(
                    'status'      => $result['status'],
                    'order_id'    => $result['order_id'],
                    'points'      => $result['points'],
                    'description' => $result['description'],
                    'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                    'href'        => $this->url->link('account/order/info', 'order_id=' . $result['order_id'], 'SSL')
                );
            } else {
                $this->data['rewards'][] = array(
                    'order_id'    => $result['order_id'],
                    'points'      => $result['points'],
                    'description' => $result['description'],
                    'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                    'href'        => $this->url->link('account/order/info', 'order_id=' . $result['order_id'], 'SSL')
                );
            }
		}	

		$pagination = new Pagination();
		$pagination->total = $reward_total;
		$pagination->page = $page;
		$pagination->limit = 10; 
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('account/reward', 'page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();
		
		$this->data['total'] =$this->model_account_reward->getTotalPoints() ;
            //(int)$this->customer->getRewardPoints();
		
		$this->data['continue'] = $this->url->link('account/account', '', 'SSL');
		
        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/reward.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/reward.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/account/reward.expand';
        }

        $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

        // $this->language->load_json('rewardpoints/index');
        $this->language->load_json('account/reward', 1);
        if($queryRewardPointInstalled->num_rows) {

            $exchange_rate = explode("/", $this->config->get('currency_exchange_rate'));
            $this->data['exchange_rate']          = array(
                'point' => $exchange_rate[0],
                'rate'  => $this->currency->format($exchange_rate[1], $this->config->get('config_currency')),
            );
            $this->load->model('rewardpoints/helper');
            $this->data['total'] .= " = ".$this->currency->format($this->model_rewardpoints_helper->exchangePointToMoney($this->data['total']), $this->config->get('config_currency'));
            $this->data['referral_link'] = (($this->config->get('config_secure')) ? HTTPS_SERVER : HTTP_SERVER) . "?rw_ref=".md5($this->customer->getId());
        }
		
		$this->children = array(
			'common/footer',
			'common/header'
        );

        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) {
            // This is to handle new template structure using extend
            $this->data['include_page'] = 'reward.expand';
            if(USES_TWIG_EXTENDS == 1)
                $this->template = 'default/template/account/layout_extend.expand';
            else
                $this->template = 'default/template/account/layout_default.expand';
        }
        
		$this->response->setOutput($this->render_ecwig());
	} 		
}
?>
