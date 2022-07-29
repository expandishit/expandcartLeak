<?php  
class ControllerLoghistoryHistories extends Controller {
  	public function index() {
        if(PRODUCTID == 3){
            $this->redirect(
                $this->url->link('error/permission', '', 'SSL')
            );
            exit();
        }
		$this->language->load('loghistory/histories');

    	$this->document->setTitle($this->language->get('heading_title'));

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'href'      => $this->url->link('common/home', '', 'SSL'),
       		'text'      => $this->language->get('text_home'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'href'      => $this->url->link('loghistory/histories', '', 'SSL'),
       		'text'      => $this->language->get('heading_title'),
      		'separator' => ' :: '
   		);
		

 		$this->data['heading_title'] = $this->language->get('heading_title');

 		//url
        $this->data['report_sale_voucher'] = $this->url->link('loghistory/sale_voucher', '', 'SSL');
        $this->data['report_sale_coupon'] = $this->url->link('loghistory/sale_coupon', '', 'SSL');
        $this->data['report_customer'] = $this->url->link('loghistory/customer', '', 'SSL');
        $this->data['report_customer_point'] = $this->url->link('loghistory/customer_point', '', 'SSL');
        $this->data['report_customer_balance'] = $this->url->link('loghistory/customer_balance', '', 'SSL');


        $this->data['report_sale_order'] = $this->url->link('loghistory/sale_order', '', 'SSL');
        $this->data['report_sale_tax'] = $this->url->link('loghistory/sale_tax', '', 'SSL');
        $this->data['report_sale_return'] = $this->url->link('loghistory/sale_return', '', 'SSL');
        $this->data['report_product_viewed'] = $this->url->link('loghistory/product_viewed', '', 'SSL');
        $this->data['report_product_purchased'] = $this->url->link('loghistory/product_purchased', '', 'SSL');
        $this->data['report_customer_order'] = $this->url->link('loghistory/customer_order', '', 'SSL');
        $this->data['report_customer_reward'] = $this->url->link('loghistory/customer_reward', '', 'SSL');
        $this->data['report_customer_credit'] = $this->url->link('loghistory/customer_credit', '', 'SSL');
        $this->data['report_affiliate_commission'] = $this->url->link('loghistory/affiliate_commission', '', 'SSL');
        $this->data['report_analytics_stats'] = $this->url->link('analytics/analytics', '' , 'SSL');
        $this->data['report_analytics_visitors'] = $this->url->link('analytics/analytics/visitorprofile', '', 'SSL');




		$this->template = 'loghistory/histories.expand';
		$this->children = array(
			'common/header',	
			'common/footer'	
		);
		
		$this->response->setOutput($this->render_ecwig());
  	}
}
?>