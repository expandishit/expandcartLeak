<?php

class ControllerModuleTrips extends Controller
{
    public function install()
    {
        $this->load->model("module/trips");
        $this->model_module_trips->install();
    }
    public function uninstall()
    {
        $this->load->model("module/trips");
        $this->model_module_trips->uninstall();
    }

    public function index()
    {
        $this->language->load('module/trips');

        $this->document->setTitle($this->language->get('heading_Trips_title'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link(
                'marketplace/home',
                '',
                'SSL'
            ),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_Trips_title'),
            'href'      => $this->url->link('module/trips', '', 'SSL'),
            'separator' => ' :: '
        );
        

        $data['isMultiseller'] = $this->isMultisellerInstalled();
        $data['trips'] = $this->config->get('trips');
       
        $this->template = 'module/trips/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['action'] = $this->url->link('module/trips/updateSettings', '', 'SSL');
        $data['cancel'] = $this->url.'marketplace/home';

        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {
        $this->language->load('module/trips');
        
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
             $result_json['success'] = '0';
             $result_json['error'] = $this->error;
        }else{

            $this->load->model('module/trips');
            $this->load->model('setting/setting');
            $data = $this->request->post['trips'];
            
            $this->model_module_trips->updateSettings(['trips' => $data]);

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
            $result_json['redirect'] = '1';
            $result_json['to'] = 'module/trips';

        }
        $this->response->setOutput(json_encode($result_json));
        return;
    }
    
    public function trips_categories()
    {
        $this->language->load('module/trips'); 
        $this->load->model('module/trips');
        $this->document->setTitle($this->language->get('trips_categories'));
        
        
        $this->load->model('catalog/category');
        $data['categories'] = $this->model_catalog_category->getCategories();
        $data['selectedCategories'] = $this->model_module_trips->getTripsCategoriesIDs();
        $data['tripsCategories']=$this->model_module_trips->getTripsCategories($data['selectedCategories']);
        
        $data['isMultiseller'] = $this->isMultisellerInstalled();
        $this->template = 'module/trips/trips_categories.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $data['action'] = $this->url->link('module/trips/update_trips_categories', '', 'SSL');
        $data['cancel'] = $this->url.'marketplace/home';

        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }
    public function update_trips_categories()
    {
        $this->language->load('module/trips');
        $this->load->model('module/trips');
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
            $result_json['success'] = '0';
            $result_json['error'] = $this->error;
       }else{

        $data = $this->request->post['trips_categories'];
        $this->model_module_trips->addTripsCategories($data);
        $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
            $result_json['redirect'] = '1';
            $result_json['to'] = 'module/trips/trips_categories';
        
       }
        $this->response->setOutput(json_encode($result_json));
        return;
    }
    public function trips_questionnaire()
    {
        $this->language->load('module/trips'); 
        $this->load->model('module/trips');
        $this->document->setTitle($this->language->get('trips_questionnaire'));
        
        
        $this->load->model('catalog/option');
        $data['options'] = $this->model_catalog_option->getOptions();
        $data['selectedOptions'] = $this->model_module_trips->getTripsquestionnaireIDs();
        $data['tripsQuestionnaire']=$this->model_module_trips->getTripsQuestionnaire($data['selectedOptions']);
        
        $data['isMultiseller'] = $this->isMultisellerInstalled();
        $this->template = 'module/trips/trips_questionnaire.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $data['action'] = $this->url->link('module/trips/update_trips_questionnaire', '', 'SSL');
        $data['cancel'] = $this->url.'marketplace/home';

        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }
    public function update_trips_questionnaire()
    {
        $this->language->load('module/trips');
        $this->load->model('module/trips');
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
            $result_json['success'] = '0';
            $result_json['error'] = $this->error;
       }else{

        $data = $this->request->post['trips_questionnaire'];
        $this->model_module_trips->addTripsQuestionnaire($data);
        $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
            $result_json['redirect'] = '1';
            $result_json['to'] = 'module/trips/trips_questionnaire';
        
       }
        $this->response->setOutput(json_encode($result_json));
        return;
    }
    public function reserved_trips($status=1){

        $this->language->load('module/trips');
	    $this->document->setTitle($this->language->get('reserved_trips'));
        $status = $this->request->get['status'];
       
	    $this->load->model('sale/order');
        $this->data['reserved_active'] = false;
        $this->data['canceled_active'] = false;
        if($status==1)
        {
        $data = array(
            'trips' => 1,
        );
        $this->data['reserved_active'] = true;
        }
        if($status==2)
        {
        $data = array(
            'trips' => 1,
            'canceled_trips' => 1,
        );
        $this->data['canceled_active'] = true;
        }
        $reserved_trips = $this->model_sale_order->getOrdersToFilter($data)['data'];
        foreach ($reserved_trips  as &$trip){
            $trip['seller_id']=$this->MsLoader->MsProduct->getSellerId($trip['product_id']);
            $trip['driver']=$this->MsLoader->MsProduct->getSellerName($trip['seller_id']);
            if($trip['riderCancelTrip']==1){$trip['canceld_by']=$this->language->get('text_rider');}
            else if($trip['driverCancelTrip']==1){$trip['canceld_by']=$this->language->get('text_driver');}
        }

        $this->data['reserved_trips'] = $reserved_trips;
        $this->data['isMultiseller'] = $this->isMultisellerInstalled();
		//render view template
		$this->template = 'module/trips/reserved_trips.expand';
        $this->children = [ 'common/header', 'common/footer' ];
        $this->response->setOutput($this->render());

	}
    public function isMultisellerInstalled()
    {
        $isMultiseller = true;
        $data['trips'] = $this->config->get('trips');
        $this->load->model('module/trips');   
        //Check if multiseller App installed else disable trips App 
        if(!\Extension::isInstalled('multiseller'))
        {       
            if($data['trips']['status'] == 1)
            {
                $data['trips']['status'] = 0;
                $this->model_module_trips->updateSettings(['trips' => $data['trips']]);
            }
            $isMultiseller = false; 
        }
        return $isMultiseller;
    }

   

}
