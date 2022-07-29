<?php  
class ControllerReportReports extends Controller {

    private $plan_id = PRODUCTID;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('plan/trial');
        $trial = $this->model_plan_trial->getLastTrial();
        if ($trial){
            $this->plan_id = $trial['plan_id'];
        }

        if($this->plan_id == 3){
            $this->redirect(
                $this->url->link('error/not_found', '', 'SSL')
            );
            exit();
        }
    }

  	public function index() {
		$this->language->load('report/reports');

    	$this->document->setTitle($this->language->get('heading_title'));

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'href'      => $this->url->link('common/home', '', 'SSL'),
       		'text'      => $this->language->get('text_home'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'href'      => $this->url->link('report/reports', '', 'SSL'),
       		'text'      => $this->language->get('heading_title'),
      		'separator' => ' :: '
   		);
		
		$this->load->model('report/reports');
		
 		$this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['report_sale_order'] = $this->url->link('report/sale_order', '', 'SSL');
        $this->data['report_sale_tax'] = $this->url->link('report/sale_tax', '', 'SSL');
        $this->data['report_sale_shipping'] = $this->url->link('report/sale_shipping', '', 'SSL');
        $this->data['report_sale_return'] = $this->url->link('report/sale_return', '', 'SSL');
        $this->data['report_sale_coupon'] = $this->url->link('report/sale_coupon', '', 'SSL');
        $this->data['report_product_viewed'] = $this->url->link('report/product_viewed', '', 'SSL');
        $this->data['report_product_purchased'] = $this->url->link('report/product_purchased', '', 'SSL');
        $this->data['report_product_top_ten_purchased'] = $this->url->link('report/product_top_ten_purchased', '', 'SSL');
        $this->data['report_customer_online'] = $this->url->link('report/customer_online', '', 'SSL');
        $this->data['report_customer_order'] = $this->url->link('report/customer_order', '', 'SSL');
        $this->data['report_customer_reward'] = $this->url->link('report/customer_reward', '', 'SSL');
        $this->data['report_customer_credit'] = $this->url->link('report/customer_credit', '', 'SSL');
        $this->data['report_affiliate_commission'] = $this->url->link('report/affiliate_commission', '', 'SSL');
        $this->data['report_analytics_stats'] = $this->url->link('analytics/analytics', '' , 'SSL');
        $this->data['report_analytics_visitors'] = $this->url->link('analytics/analytics/visitorprofile', '', 'SSL');

        $this->data['text_report_sale_order'] = $this->language->get('text_report_sale_order');
        $this->data['text_report_sale_tax'] = $this->language->get('text_report_sale_tax');
        $this->data['text_report_sale_shipping'] = $this->language->get('text_report_sale_shipping');
        $this->data['text_report_sale_return'] = $this->language->get('text_report_sale_return');
        $this->data['text_report_sale_coupon'] = $this->language->get('text_report_sale_coupon');
        $this->data['text_report_product_viewed'] = $this->language->get('text_report_product_viewed');
        $this->data['text_report_product_purchased'] = $this->language->get('text_report_product_purchased');
        $this->data['text_report_product_top_ten_purchased'] = $this->language->get('text_report_product_top_ten_purchased');
        $this->data['text_report_customer_online'] = $this->language->get('text_report_customer_online');
        $this->data['text_report_customer_order'] = $this->language->get('text_report_customer_order');
        $this->data['text_report_customer_reward'] = $this->language->get('text_report_customer_reward');
        $this->data['text_report_customer_credit'] = $this->language->get('text_report_customer_credit');
        $this->data['text_report_affiliate_commission'] = $this->language->get('text_report_affiliate_commission');
        $this->data['text_report_analytics_stats'] = $this->language->get('text_report_analytics_stats');
        $this->data['text_report_analytics_visitors'] = $this->language->get('text_report_analytics_visitors');

        $this->data['text_products'] = $this->language->get('text_products');
        $this->data['text_sales'] = $this->language->get('text_sales');
        $this->data['text_customers'] = $this->language->get('text_customers');
        $this->data['text_affiliates'] = $this->language->get('text_affiliates');
        $this->data['text_analytics'] = $this->language->get('text_analytics');

        if (\Extension::isInstalled('delivery_slot') && $this->config->get('delivery_slot')['status'] == 1) {
            $this->data['delivery_slot'] = 1;
        }

        $this->initializer([
            'abandonedCart' => 'module/abandoned_cart/settings',
            'affiliates' => 'module/affiliates'
        ]);

        $this->data['abandonedCart'] = false;
        if ($this->abandonedCart->isActive() == true) {
            $this->data['abandonedCart'] = true;
            $this->language->load('report/abandoned_cart');
        }

        $this->data['affiliates'] = false;
        if ($this->affiliates->isActive() == true) {
            $this->data['affiliates'] = true;
        }

		$this->template = 'report/reports.expand';
		$this->children = array(
			'common/header',	
			'common/footer'	
		);
		
		$this->response->setOutput($this->render_ecwig());
  	}
}
?>