<?php

use Facebook\Facebook;
use Facebook\Exceptions\FacebookSDKException;

/**
 * Class ControllerModuleFacebookBusiness
 * @author AhmedRashwan Ahmed.Rashwan2014@yahoo.com
 * @docs https://developers.facebook.com/docs/marketing-api/fbe/fbe2/
 */
class ControllerModuleFacebookBusiness extends Controller
{
	
	private $model;
    private $error 						= [];
    private $result_json 				= ['success' => '0', 'message' => '', 'code' => ''];
    private $name 						= 'facebook_business';
    private $facebook_app_id 			= '329928231042768';
    private $facebook_secret_key 		= '89b8ba250426527ac48bafeead4bb19c';
    private $isPixelsInstalled 			= '';
    private $isFbInstalled	 			= '';
    private $accessToken 				= '';
    private $pixel_id 					= '';
    private $business_manager_id 		= '';
    private $external_business_id 		= '';
    private $page_id 					= '';
    private $catalog_id 				= '';
    private $app_user_id 				= '';
    private $system_user_token 			= '';
    private $system_user_id 			= '';
    private $user_info		 			= '';
	private $ad_account_id 				= '';
	private $redirect_page 				= 'https://auth.expandcart.com/facebook_business.php';
	//private $redirect_page 				='https://auth.facebook.me/facebook_business.php';
	
	 /**
     * @var string[]
     */
  
    private $facebook_pixel_events = array(
        'Purchase', 'Generate Lead', 'Complete Registration',
        'Add Payment Info', 'Add to Basket', 'Add to Wishlist',
        'Initiate Checkout', 'Search', 'View Content'
    );
		
	/**
     * FacebookAPI Object
     *
     * @var Facebook $fb
     */
    private $fb;
	
	public function __construct ($registry){

			parent::__construct($registry);
			
			$this->initializer([
								'facebook_business'	=> 'module/facebook_business',
								'queue_model' 		=> 'module/facebook_business/jobs'
							]);	
							
			$this->load->model('setting/setting');
			$fbe_settings = $this->model_setting_setting->getSetting($this->name);
			
			$this->accessToken 				 = $fbe_settings['fbe_access_token']		  ?? false;
			$this->system_user_token 		 = $fbe_settings['system_user_token']		  ?? false;
			$this->user_info		 		 = $fbe_settings['user_info']				  ?? false;
			$this->system_user_id			 = $fbe_settings['system_user_id']			  ?? false;
			$this->business_manager_id 		 = $fbe_settings['business_manager_id']		  ?? false;
			//we are using a dynamic id to avoid any re-register with failure case of deleting request at app uninstall
			$this->external_business_id 	 = $fbe_settings['external_business_id']	  ?? STORECODE . "_fbe_".time();  
			$this->ad_account_id 			 = $fbe_settings['ad_account_id']			  ?? false;
			$this->app_user_id 				 = $fbe_settings['app_user_id']				  ?? false;
			$this->page_id 					 = $fbe_settings["page_id"]					  ?? false;
			$this->catalog_id 				 = $fbe_settings["catalog_id"]				  ?? false;
			$this->pixel_id 			 	 = $fbe_settings["pixel_id"]			  	  ?? false;
			$this->isPixelsInstalled 		 = isset($fbe_settings["isPixelsInstalled"])  ? 1 : 0;
			$this->isFbInstalled 		 	 = isset($fbe_settings["isFacebookInstalled"])? 1 : 0;
			$this->account_connected		 = $fbe_settings["account_connected"]		  ?? 0;
			$this->fb 						 = new Facebook([
													'app_id' 	 => $this->facebook_app_id,
													'app_secret' => $this->facebook_secret_key,
												]);

		}
		
	//------------------------ Routes  -----------------//

	//
    public function index(){
        $this->language->load('module/facebook_business');	   
        $this->document->setTitle($this->language->get('heading_title_facebook'));
	    
		$this->load->model("marketplace/common"); 
        $appId 			= $this->model_marketplace_common->hasApp("facebook_business");

        if($appId["hasapp"]) {
            $this->data["appLink"] = $this->url->link("marketplace/app?id=" . $appId["appserviceid"] . "&isRedirectedFromApp=1");
        }

        $this->data["isFbInstalled"] 	 = $this->isFbInstalled;
        $this->data["isPixelsInstalled"] = $this->isPixelsInstalled;
        $this->data["hasError"] 		 = $this->request->get['error'] ? true : false ;

        $this->children = array(
            'common/header',
            'common/footer',
			);
		
		$this->template = 'module/facebook_business/facebookinterface.expand';
        $this->response->setOutput($this->render());
    }

	//main setup 
	public function facebookConnect(){
		
		$allowed_features = ["fb_shop", "fb_pixel"];
		
		if(!isset($this->request->get["feature"])){
			$this->redirect($this->url->link('module/facebook_business'));
		}
		
		$request_feature =  $this->request->get["feature"] ;
		
		$feature = in_array($request_feature , $allowed_features ) ? $request_feature : "fb_shop"; 
		//if the account already connected , redirect to setting or setup page 
		if($this->account_connected){
		//if(true){
			return $this->_checkFeatureSetup($feature);
		}
		
        $this->language->load('setting/setting');
        $this->language->load('module/facebook_business');

        $this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
        
		$fbe_settings = $this->model_setting_setting->getSetting($this->name);

        $this->data['domain'] 						 = DOMAINNAME;
        $this->data['feature'] 						 = $feature;
        $this->data['redirect_page'] 				 = $this->redirect_page;
        $this->data['external_business_id'] 		 = $this->external_business_id;
        $this->data['facebook_app_id'] 				 = $this->facebook_app_id;
        $this->data['currency'] 					 = $this->config->get('config_currency');
        $this->data['timezone'] 					 = $this->config->get('config_timezone');
        $this->data["cancel_url"]  					 = $this->url->link("module/facebook_business/facebookConnect?feature=".$feature);

        $this->data['breadcrumbs'] 	 = array();

        $this->data['breadcrumbs'][] = array(
											'text' 		=> $this->language->get('text_home'),
											'href' 		=> $this->url->link('common/home', '', 'SSL'),
											'separator' => false
										);

        $this->data['breadcrumbs'][] = array(
											'text' 		=> $this->language->get('text_modules'),
											'href' 		=> $this->url->link(
																'marketplace/home',
																'',
																'SSL'
															),
											'separator' => ' :: '
										);

        $this->data['breadcrumbs'][] = array(
											'text' => $this->language->get('heading_title'),
											'href' => $this->url->link('module/facebook_business', '', 'SSL'),
											'separator' => ' :: '
										);

        $this->data['breadcrumbs'][] = array(
												'text' => $this->language->get('text_main_account_setting'),
												'href' => $this->url->link('module/facebook_business', '', 'SSL'),
												'separator' => ' :: '
											);
											
        $this->children = array(
            'common/header',
            'common/footer',
        );
		
		$this->template = 'module/facebook_business/facebookconnect.expand';

        $this->response->setOutput($this->render());		
	}
	
	// 
	private function _checkFeatureSetup($feature){
		if($feature == 'fb_shop'){
			if($this->isFbInstalled ){
				$this->redirect($this->url->link('module/facebook_business/facebookshop?isFacebook=1'));
			} else {
				return $this->_facebookSetupPage();
			}
		}else if($feature == 'fb_pixel') {
			if($this->isPixelsInstalled ){
				$this->redirect($this->url->link('module/facebook_business/pixelsettings'));
			}else{
				return $this->_pixelSetupPage();
			}
		}else {
			$this->redirect($this->url->link('module/facebook_business'));
		}
	}
	
	//partial setup if FB Account already connected 
	private function _facebookSetupPage(){
		$this->language->load('setting/setting');
        $this->language->load('module/facebook_business');
        $this->document->setTitle($this->language->get('heading_title_facebook'));

		$this->data['hasToken'] 	= $this->accessToken != '' && $this->accessToken != false;
		$this->data['hasBusiness'] 	= $this->business_manager_id != '' && $this->business_manager_id != false;
		$this->data['hasPage'] 	 	 = $this->page_id != '' && $this->page_id != false;
		$this->data['hasCatalog'] 	 = $this->catalog_id != '' && $this->catalog_id != false;
		$business_data 				 = $this->facebook_business->getBusiness($this->accessToken);
		$this->data['user_info'] 	 = $this->facebook_business->getUserInfo();
		$this->data['business_data'] = $business_data;
		//$this->data["page_categs"]   = $this->facebook_business->getPageCategories();
		$this->data['owned_pages']   = $business_data['owned_pages']['data']??[];
		$this->data['catalogs']   	 = $business_data['owned_product_catalogs']['data']??[];
		$this->data['fbe_settings']  = $this->model_setting_setting->getSetting($this->name);
		$this->data['redirect_url']  = 'module/facebook_business/facebookshop';

	   $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->template = 'module/facebook_business/facebooksetup.expand';
        $this->response->setOutput($this->render());
	}
	
	//partial setup if FB Account already connected 
	private function _pixelSetupPage(){
		$this->language->load('setting/setting');
        $this->language->load('module/facebook_business');
        $this->document->setTitle($this->language->get('heading_title_facebook'));

		$business_data 				 = $this->facebook_business->getBusiness($this->accessToken);
		
		$this->data['hasToken'] 	 = $this->accessToken != '' && $this->accessToken != false;
		$this->data['hasBusiness'] 	 = $this->business_manager_id != '' && $this->business_manager_id != false;
		$this->data['hasPixel'] 	 = $this->pixel_id != '' && $this->pixel_id != false;
		$this->data['user_info'] 	 = $this->facebook_business->getUserInfo();
		$this->data['business_data'] = $business_data;
		$this->data['pixels']   	 = $business_data['adspixels']['data']??[];
		$this->data['fbe_settings']  = $this->model_setting_setting->getSetting($this->name);
		$this->data['redirect_url']  = 'module/facebook_business/pixelsettings';
	   $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->template = 'module/facebook_business/pixelsetup.expand';
        $this->response->setOutput($this->render());
	}

	//
    public function facebookshop(){
        
		$this->language->load('setting/setting');
        $this->language->load('module/facebook_business');
        $this->load->model('setting/setting');
		
        $this->document->setTitle($this->language->get('heading_title_facebook'));
		
		if(!$this->isFbInstalled) {
			$this->redirect($this->url->link('module/facebook_business/facebookConnect?feature=fb_shop'));
        } 

		$business_data 				 = $this->facebook_business->getBusiness($this->system_user_token);
		
		$this->data['business_data'] = $business_data;
		$this->data['catalogs']   	 = $business_data['owned_product_catalogs']['data']??[];
        $this->data['catalog_id']    = $this->catalog_id;
        $this->data['error']    	 = $business_data['error'] ??  '';
		
        $this->data['business_manager_id']    = $this->business_manager_id;

        unset($this->session->data["import_success_message"]);

        if(isset($this->request->get["product_export_count"])) {
            $this->data["product_export_count"] = (int) $this->request->get["product_export_count"];
        }


        if(isset($this->request->get["product_export_failed_count"])) {
            $this->data["product_export_failed_count"] = (int) $this->request->get["product_export_failed_count"];
        }

        if(isset($this->request->get["import_success"])) {
            $this->result_json['success'] = '1';
            $this->result_json["success_msg"] = "catalog imported successfully";
            $this->response->setOutput(json_encode( $this->result_json));
            return;
        }
		
        $this->template = 'module/facebook_business/facebookshop.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

	//
    public function pixelsettings(){
        $this->language->load('module/facebook_business');
        $this->document->setTitle($this->language->get('heading_title_facebook'));
        
		if(!$this->isPixelsInstalled){
			$this->redirect($this->url->link('module/facebook_business/facebookConnect?feature=fb_pixel'));
		}

		$business_data 				 	= $this->facebook_business->getBusiness($this->system_user_token);
		
		$this->data['business_data'] 	= $business_data;
		$this->data['pixel_id']   	 	= $this->pixel_id;
		$this->data['pixels']   	 	= $business_data['adspixels']['data']??[];
		$this->data["pixel_actions"] 	= $this->facebook_pixel_events;
        $this->data["pixelSettings"] 	= $this->model_setting_setting->getSetting("integrations");

		$this->children = array(
            'common/header',
            'common/footer',
        );
		
        $this->template = 'module/facebook_business/pixelsettings.expand';
 
        $this->response->setOutput($this->render());
    }
	
	//
    public function catalogproducts(){
        $this->language->load('setting/setting');
        $this->language->load('module/facebook_business');
        $this->load->model("localisation/language");
		if(!$this->isFbInstalled) {
			$this->redirect($this->url->link('module/facebook_business/facebookConnect?feature=fb_shop'));
        } 

        $this->document->setTitle($this->language->get('heading_title_facebook'));

        $this->data["catalog_id"] 	= $this->catalog_id;
        $this->data["import_link"] 	= "module/facebook_business/getProducts";
        
		
		$this->template 			= 'module/facebook_business/catalogproducts.expand';
       
	   $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

	//
    public function storeproducts(){
        $this->language->load('setting/setting');
        $this->language->load('module/facebook_business');
        $this->load->model("localisation/language");
        $this->document->setTitle($this->language->get('heading_title_facebook'));
        
		if(!$this->isFbInstalled) {
			$this->redirect($this->url->link('module/facebook_business/facebookConnect?feature=fb_shop'));
        } 


        $this->load->model('setting/setting');
        $this->data['settings'] 			= $this->model_setting_setting->getSetting('config');
        $this->data['fbe_settings'] 		= $this->model_setting_setting->getSetting($this->name);
        $this->data["facebook_catalog_id"] 	= $this->data["fbe_settings"]['catalog_id'];
        $this->data["languages"] 			= $this->model_localisation_language->getLanguages();
		
        $this->template = 'module/facebook_business/storeproducts.expand';
        
		$this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

	//
    public function facebooksettings(){
	
        $this->language->load('setting/setting');
        $this->language->load('module/facebook_business');
        $this->document->setTitle($this->language->get('heading_title_facebook'));

        $this->load->model('setting/setting');
		
		if(!$this->account_connected) {
			$this->redirect($this->url->link('module/facebook_business'));
        }
		
		$business_data 				 = $this->facebook_business->getBusiness($this->accessToken);
		$this->data['error']    	 = $business_data['error'] ??  '';
		$this->data['user_info'] 	 = $this->facebook_business->getUserInfo();
		$this->data['business_data'] = $business_data;
		$this->data['user_pages']    = $this->facebook_business->getUserPages()['data']??[];
		$this->data['catalogs']   	 = $business_data['owned_product_catalogs']['data']??[];
		$this->data['pixels']   	 = $business_data['adspixels']['data']??[];
		$this->data['isFbInstalled'] = $this->isFbInstalled;
		$this->data['isPixelsInstalled'] = $this->isPixelsInstalled;
		
		$this->data['fbe_settings']  = $this->model_setting_setting->getSetting($this->name);
	
        $this->load->model("marketplace/common");
		
        $appId = $this->model_marketplace_common->hasApp("facebook_business");

        if($appId["hasapp"]) {
            $this->data["appLink"] = $this->url->link("marketplace/app?id=" . $appId["appserviceid"] . "&isRedirectedFromApp=1");
        }
		
        $this->data["logoutLink"] = $this->url->link("module/facebook_business/logout", "", "SSL");

        $this->data['settings'] = $this->model_setting_setting->getSetting('config');

        $fbeSettings = $this->model_setting_setting->getSetting($this->name);

        $this->data['fbe_settings'] = $fbeSettings;

        $this->template = 'module/facebook_business/facebooksettings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        if(isset($this->request->get["error"])) {
            $this->data["error"] = $this->request->get["error"];
        }

        if(isset($this->request->get["success"])) {
            $this->data["success"] = $this->request->get["success"];
        }

        $this->response->setOutput($this->render());
    }

	// 
	public function synchistory(){
		$this->language->load('module/facebook_business');
        $this->document->setTitle($this->language->get('heading_title_facebook'));
		if(!$this->isFbInstalled) {
			$this->redirect($this->url->link('module/facebook_business/facebookConnect?feature=fb_shop'));
        } 


        $this->template = 'module/facebook_business/synchistory.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
		$this->response->setOutput($this->render());
		
	}
	
	//
	public function queJobsList(){
		$start 			 = $this->request->post['start']		 	?? 0;
        $limit 			 = $this->request->post['length']		 	?? 50;
		$options 		 = ['sort' => 'j.job_id' , 'sort_type' => 'DESC'];
		$jobs_result = $this->queue_model->getJobs($start,$limit,$options);
		$result = [];
		$result['data'] 			= $jobs_result['data'];
		$result['recordsTotal'] 	= $jobs_result['total_jobs'];
        $result['recordsFiltered']  = $jobs_result['total_jobs'];
		
				
		$this->response->setOutput(json_encode($result));
		return;
	}
	
	//
	public function getXHRList(){
		
        $this->language->load('catalog/product');
        $this->language->load('catalog/product_filter');

        $request = $this->request->request;

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['direction'] = $this->language->get('direction');

        $this->load->model('catalog/product');
		$this->load->model('tool/image');
		 
        $this->data['products'] = array();

        $start  = $request['start'];
        $length = $request['length'];

        $orderColumn 	 = $request['columns'][$request['order'][0]['column']]['name'];
        $orderColumnData = $request['columns'][$request['order'][0]['column']]['data'];
        $orderType 		 = $request['order'][0]['dir'];
		$filterData 	 = [];
        $filterData['search'] = null;
		
		
        if (isset($request['search']['value']) && strlen($request['search']['value']) > 0) {
            $filterData['search'] = $request['search']['value'];
        }

        $data = array(
            'sort' => $orderColumn,
            'order' => $orderType,
            'start' => $request['start'],
            'limit' => $request['length'], 
			'show_facebook_statuses' => true , 
			'catalog_id' => $this->catalog_id 
        );

        $results = $this->model_catalog_product->getFBProductsToFilter($data, $filterData);

        foreach ($results['data'] as $result) {
            $image 			 = $this->model_tool_image->resize($result['image'], 550, 550);
            $thumb 			 = $this->model_tool_image->resize($result['image'], 40, 40);  
            $productQuantity = (intval($result['quantity']) >= 0) ? $result['quantity'] : 0;
			
            $this->data['products'][] = array(
                'product_id' 			=> $result['product_id'],
                'name' 					=> $result['localized_name'],
                'category_name' 		=> $result['category_name'],
                'category_id' 			=> $result['category_id'],
                "isPublishedOnCatalog" 	=> ($result["isPublishedOnCatalog"] == 1 && $result["fbCatalogId"] == $this->config->get("catalog_id")) ? "yes" : "no",
                'categories_ids'    	=> $result['categories_ids'],
                'categories_names'  	=> $result['categories_names'],
                'categories_images' 	=> $result['categories_images'],
                'model' 				=> $result['model'],
                'price' 				=> number_format($result['price'], 2) . ' ' . $this->config->get('config_currency'),
                'image'				 	=> $image,
                'thumb' 				=> $thumb,
                'quantity' 				=> $productQuantity,
                'status' 				=> $result['status'],
                'sku' 					=> $result['sku'],
                'barcode' 				=> $result['barcode'],
                'barcode_image' 		=> $barcodeImageString,
                'date_added' 			=> $result['date_added'],
                'push_status' 			=> $result['push_status'],
                'rejection_reason' 		=> $result['rejection_reason'],
                'batch_id' 				=> $result['batch_id'],
                'retailer_id' 			=> $result['retailer_id'],
            );
        }

        $sortBy = array_column($this->data['products'], $orderColumnData);
        $orderBy = ($orderType == 'asc') ? SORT_ASC : SORT_DESC;
        array_multisort($sortBy, $orderBy, $this->data['products']);
        
        $this->response->setOutput(json_encode([
												"draw" 				=> intval($request['draw']),
												"recordsTotal"  	=> intval($results['total']),
												"recordsFiltered" 	=> $results['totalFiltered'],
												'data' 				=> $this->data['products'],
												'heading' 			=> $this->data['locales']['show_x_from']
											]));
        return;
    }
	
	//
    public function createPage(){

		if ($this->request->server['REQUEST_METHOD'] != 'POST') {
			$this->result_json['success'] = '0';
			$this->result_json['message'] = "invalid request method";
			$this->response->setOutput(json_encode($this->result_json));
			return;
		}
		
        if (empty($this->request->post['page_name'])) {
			$this->result_json['success'] = '0';
			$this->result_json['message'] = "page name required";
			$this->response->setOutput(json_encode($this->result_json));
			return;
		}
		
		if (!$this->accessToken) {
			//something went wrong! no token exists!
			$this->result_json['success'] = '0';
			$this->result_json['message'] = $result["error"] ?? "something went wrong! No token exists!";
			$this->response->setOutput(json_encode($this->result_json));
			return;
		}
		
		$page_name 	= $this->request->post['page_name'];
		$page_about = $this->request->post['page_about'] ?? $page_name ; 
		
		$pic = $_FILES["picture"];
        $image_fn = "image/data" . '/' . $pic['name'];
        $pic_uploaded = \Filesystem::setPath($image_fn)->upload($pic['tmp_name']);
        $pic_uri = \Filesystem::getUrl($image_fn);
   

        $cover = $_FILES["cover"];
        $cover_fn = "image/data" . '/' . $cover['name'];
        $cover_uploaded = \Filesystem::setPath($cover_fn)->upload($cover['tmp_name']);
        $cover_uri = \Filesystem::getUrl($cover_fn);
		
		$data = [
				'page_name'  => $page_name, 
				'page_about' => $page_about, 
				'pic_uri'	 => $pic_uri , 
				'cover_uri'	 => $cover_uri
				];
		
        $result = $this->facebook_business->createPage($data);
			   
		if($result["status"] == 'success' ){
			$this->result_json['success'] = '1';
			$this->result_json['message'] = 'page created successfully';
		} else {
			$this->result_json['success'] = '0';
			$this->result_json['message'] = $result["error"] ?? "something went wrong!";
		}
		
		$this->response->setOutput(json_encode($this->result_json));
		return;   
   }

	//
	public function createCatalog(){

        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
			$this->result_json['success'] = '0';
			$this->result_json['message'] = "invalid request method";
			$this->response->setOutput(json_encode($this->result_json));
			return;
		}
		
        if (empty($this->request->post['catalog_name'])) {
			$this->result_json['success'] = '0';
			$this->result_json['message'] = "catalog name required";
			$this->response->setOutput(json_encode($this->result_json));
			return;
		}
        
		if ($this->accessToken) {
				$catalog_name = $this->request->post['catalog_name'];
				
                $result = $this->facebook_business->createCatalog($catalog_name);
			   
			   if($result["status"] == 'success' ){
				    $this->result_json['success'] = '1';
				    $this->result_json['message'] = 'catalog created successfully';
			   }else {
				    $this->result_json['success'] = '0';
				    $this->result_json['message'] = $result["error"] ?? "something went wrong!";
			   }
			   
            } else {
				//something went wrong! no token exists!
				$this->result_json['success'] = '0';
				$this->result_json['message'] = $result["error"] ?? "something went wrong! No token exists!";
			}
	
        $this->response->setOutput(json_encode($this->result_json));
		return;
    }
    
	//
    public function createPixel(){

		if ($this->request->server['REQUEST_METHOD'] != 'POST') {
			$this->result_json['success'] = '0';
			$this->result_json['message'] = "invalid request method";
			$this->response->setOutput(json_encode($this->result_json));
			return;
		}
		
        if (empty($this->request->post['pixel_name'])) {
			$this->result_json['success'] = '0';
			$this->result_json['message'] = "pixel name required";
			$this->response->setOutput(json_encode($this->result_json));
			return;
		}
		
		//need to review this point later 
		$adAccount = $this->getAdAccounts($this->business_manager_id)[0]["account_id"];
        $ad_account_id = $adAccount;
			
        if (empty($ad_account_id)) {
			$this->result_json['success'] = '0';
			$this->result_json['message'] = "no ad account exists!";
			$this->response->setOutput(json_encode($this->result_json));
			return;
		}
		
		if ($this->accessToken) {
			$pixel_name = $this->request->post['pixel_name'];
            $result = $this->facebook_business->createPixel($ad_account_id,$pixel_name);
			   
			if($result["status"] == 'success' ){
				$this->result_json['success'] = '1';
				$this->result_json['message'] = 'pixel created successfully';
			} else {
				$this->result_json['success'] = '0';
				$this->result_json['message'] = $result["error"] ?? "something went wrong!";
			}
			   
        } else {
			//something went wrong! no token exists!
			$this->result_json['success'] = '0';
			$this->result_json['message'] = $result["error"] ?? "something went wrong! No token exists!";
		}
	
        $this->response->setOutput(json_encode($this->result_json));
		return;
    }

	//
    public function logout(){
		
        $this->facebook_business->logout();

        $this->redirect($this->url->link('module/facebook_business'));
        return;
    }

	//------------------------ XHR -----------------//
    /**
     * POST
     * Store facebook business user token
     */
	//
    public function storeToken(){
		
        if ($this->request->server['REQUEST_METHOD'] == 'GET' && isset($this->request->get['accessToken'])) {
            
            
			
            $this->accessToken = $this->request->get['accessToken'];
            $this->app_user_id = $this->request->get['app_user_id'];
			
			
            $data['fbe_access_token'] =  $this->facebook_business->getLongLiveToken($this->accessToken);
			
			// IF REQUEST FAILED
            if (! $data['fbe_access_token']) {
                $this->redirect($this->url->link('module/facebook_business?error=errorInToken'));
            }
			
			$data['external_business_id'] = $this->request->get['external_business_id'] ?? $this->external_business_id;
            
			$this->load->model('setting/setting');
            $ids = $this->facebook_business->getInstallationIds($this->accessToken,$data['external_business_id']);

			// IF REQUEST FAILED
            if (!isset($ids) || count($ids) <= 0) {
                $this->redirect($this->url->link('module/facebook_business?error=errorInToken'));
                return;
            }
			
			$token_result = $this->facebook_business->createSystemUserAccessToken($ids['business_manager_id'],
															$this->accessToken,
															$data['external_business_id']);
			
			if(!$token_result->access_token){
                $this->redirect($this->url->link('module/facebook_business?error=errorInToken'));
			}
			
			$sys_user_result = $this->facebook_business->getSystemUserId($token_result->access_token);
			
			if(!$sys_user_result->id){
                $this->redirect($this->url->link('module/facebook_business?error=errorInToken'));
			}

            $data['pixel_id'] 				= $ids['pixel_id']?? "";
            $data['page_id'] 				= $ids['pages'][0]??"";
            $data['catalog_id'] 			= $ids['catalog_id']??"";
            $data['app_user_id'] 			= $this->app_user_id;
            $data["user_info"] 				= $this->facebook_business->getUserInfo();
            $data["instagram_id"] 			= $ids["instagram_profiles"][0];
			$data['system_user_token'] 		= $token_result->access_token;
			$data['system_user_id'] 		= $sys_user_result->id;
            $data['business_manager_id'] 	= $ids['business_manager_id'];
			$data['account_connected'] 		= 1;
			
			$feature = $this->request->get["feature"];
			
			if($feature == "fb_shop") {
				$data["isFacebookInstalled"]	= "1";
			}else if ($feature == "fb_pixel"){
				$data["isPixelsInstalled"] 		= "1";
			} 
			
            // Add token and Ids to the DB
            $this->load->model('setting/setting');
            $this->model_setting_setting->insertUpdateSetting($this->name, $data);
            $this->result_json['success'] = '1';
            $this->result_json['success_msg'] = $this->language->get('text_success');
            
			if(strtolower($feature) == "fb_shop") {
				$this->redirect($this->url->link('module/facebook_business/facebookshop'));
			}else if (strtolower($feature) == "fb_pixel"){
				 $this->redirect($this->url->link('module/facebook_business/pixelSettings'));
			} 
           
			
            $this->response->setOutput(json_encode($this->result_json));

            return;
        } else {
			 $this->redirect($this->url->link('module/facebook_business?error=errorInToken'));
        }
    }

	//
    public function install(){
		
        $this->facebook_business->install();
        if(!\Filesystem::isExists('image/data/facebook_products')) {

            \Filesystem::createDir('image/data/facebook_products');
            \Filesystem::setPath('image/data/facebook_products')->changeMod('writable');

        }

    }

	//
    public function uninstall(){ 
        $this->facebook_business->uninstall();
    }
	
	//
    public function storePixelEvents(){

        $events = $this->request->post["selected_events"];

        $status = $this->request->post["pixel_status"];

        $pixel_id = $this->pixel_id;

        $this->load->model("setting/setting");



        if(!empty($events) && $status != "" && !empty($pixel_id)) {

            $data["mn_integ_fbp_status"] = $status;
            $data["mn_integ_fbp_code"] = $pixel_id;
            $data["mn_integ_fbp_action"] = $events;
            $this->model_setting_setting->insertUpdateSetting( "integrations" , $data);
            $this->model_setting_setting->insertUpdateSetting( $this->name , ["pixel_update" => "1"]);
            $result_json["success"] = "1";
            $result_json["success_msg"] = "settings saved successfully"; //TO:DO | need localize this message
            $this->response->setOutput(json_encode($result_json));
            return;



        } else {
            $result_json["error"] = "some field is missing";  //TO:DO | need localize this message
            $this->response->setOutput(json_encode($result_json));
            return;
        }

    }

	//
    public function assignAsset(){
	
        if(isset($this->request->post["itemName"])) {

            $this->load->model('setting/setting');

            $itemName = $this->request->post["itemName"];
			
            $itemValue = $this->request->post["itemValue"];

			//$items_types_to_su = ['page_id','catalog_id','pixel_id'];
			$items_types_to_su = ['page_id','catalog_id']; //cant assign pixel to system user till whitelist our App to use Ads Api's 
			if(in_array($itemName,$items_types_to_su)){
				
				$tasks = 'MANAGE';
				if($itemName == 'pixel_id'){
					$tasks = 'EDIT';
				}
				
				$result = $this->facebook_business->assignAssetToSU($this->request->post["itemValue"],$tasks);
				if(!$result->success){
					$this->result_json['success'] = '0';
					$this->result_json['message'] = 'cant assign asset to the SU !';
					$this->response->setOutput(json_encode($this->result_json));
					return;
				}
			}
			
			$this->model_setting_setting->insertUpdateSetting($this->name, [$itemName => $itemValue]);
            $this->result_json['success'] = '1';
			$this->result_json['message'] = 'Asset connected successfuly';
			$this->response->setOutput(json_encode($this->result_json));
			return;

            return;
        }else {
			$this->result_json['success'] = '0';
			$this->result_json['message'] = 'you should choose one pixel account to continue setup!';
			$this->response->setOutput(json_encode($this->result_json));
			return;
			
		}


    }

	//
	//used at partial setup only 
    public function connectItem(){

        if(isset($this->request->post["itemName"])) {

            $this->load->model('setting/setting');

            $itemName = $this->request->post["itemName"];

            $itemValue = $this->request->post["itemValue"];

            $this->model_setting_setting->insertUpdateSetting($this->name, [$itemName => $itemValue]);

            $this->redirect($_SERVER['HTTP_REFERER']);

            return;
        }


    }

	//
    public function disconnectItem(){

        if(isset($this->request->post["itemName"])) {

            $this->load->model('setting/setting');

            $itemName = $this->request->post["itemName"];

            $this->model_setting_setting->deleteByKeys([$itemName]);

            $this->redirect($_SERVER['HTTP_REFERER']);

            return;

        }
    }

	//
	/*
	array(5) { 
	["draw"]=> string(1) "1"
	["columns"]=> array(7) { [0]=> array(5) { ["data"]=> string(2) "id" ["name"]=> string(0) "" ["searchable"]=> string(4) "true" ["orderable"]=> string(5) "false" ["search"]=> array(2) { ["value"]=> string(0) "" ["regex"]=> string(5) "false" } } [1]=> array(5) { ["data"]=> string(9) "image_url" ["name"]=> string(0) "" ["searchable"]=> string(4) "true" ["orderable"]=> string(5) "false" ["search"]=> array(2) { ["value"]=> string(0) "" ["regex"]=> string(5) "false" } } [2]=> array(5) { ["data"]=> string(4) "name" ["name"]=> string(4) "name" ["searchable"]=> string(4) "true" ["orderable"]=> string(5) "false" ["search"]=> array(2) { ["value"]=> string(0) "" ["regex"]=> string(5) "false" } } [3]=> array(5) { ["data"]=> string(5) "price" ["name"]=> string(5) "price" ["searchable"]=> string(4) "true" ["orderable"]=> string(5) "false" ["search"]=> array(2) { ["value"]=> string(0) "" ["regex"]=> string(5) "false" } } [4]=> array(5) { ["data"]=> string(8) "currency" ["name"]=> string(8) "currency" ["searchable"]=> string(4) "true" ["orderable"]=> string(5) "false" ["search"]=> array(2) { ["value"]=> string(0) "" ["regex"]=> string(5) "false" } } [5]=> array(5) { ["data"]=> string(5) "brand" ["name"]=> string(5) "brand" ["searchable"]=> string(4) "true" ["orderable"]=> string(5) "false" ["search"]=> array(2) { ["value"]=> string(0) "" ["regex"]=> string(5) "false" } } [6]=> array(5) { ["data"]=> string(11) "is_imported" ["name"]=> string(0) "" ["searchable"]=> string(4) "true" ["orderable"]=> string(5) "false" ["search"]=> array(2) { ["value"]=> string(0) "" ["regex"]=> string(5) "false" } } }
	["start"]=> string(1) "0"
	["length"]=> string(2) "25"
	["search"]=> array(2) { ["value"]=> string(0) "" ["regex"]=> string(5) "false" } 
	}
	*/
   public function getProducts(){
        //Get request variables
		$catalog_id 	 = $this->request->get['catalog_id'] 		?? null;
        $start 			 = $this->request->post['start']		 	?? null;
        $limit 			 = $this->request->post['length']		 	?? null;
        $next_cursor 	 = $this->request->post['next_cursor'] 		?? null;
        $previous_cursor = $this->request->post['previous_cursor']  ?? null;

        //check for the catalog id in the request
        if (!$catalog_id) {
			$this->response->setOutput(json_encode(['status' => 'ERROR', 'message' => 'You have to provide a catalog id.']));
        }
		

        //Check and validate for the limit in the request
        if ($limit < 10) {
            $limit = 10;
        } else if ($limit > 100) {
            $limit = 100;
        }
		
        //Load the data from Facebook|Database
        $result = $this->facebook_business->fetchFacebookProductsToDB($catalog_id, $limit, $next_cursor, $previous_cursor);
		$result['start']= $start;
		
		 $this->response->setOutput(json_encode($result));
    }
	
	//
    public function handleImport(){
		
        $this->load->language('module/facebook_business');

		 $selected_products=$this->request->post['products'];
		 
		 
            $products_in_db = $this->facebook_business->getProductsByIds($selected_products,false);
			
			
            if (count($products_in_db) > 0) {
                $pr = [];
                $image_name = null;
                $manufacturer_id = null;

                //insert into product table
                foreach ($products_in_db as $product) {
                    $product_decoded = json_decode($product['payload']);

                    //get brand or insert
                    //check if the brand is not empty
                    if(!empty($product_decoded->brand)){
                        $b = $this->db->query('SELECT * FROM manufacturer where name="' . $product_decoded->brand . '"');
                        if ($b->num_rows > 0) {
                            $manufacturer_id = $b->row['manufacturer_id'];
                        } else {
                            $s = 'insert into manufacturer (name) values ("' . $product_decoded->brand . '");';

                            $this->db->query($s);
                            $manufacturer_id = $this->db->getLastId();

                            $s = 'insert into manufacturer_to_store values (' . $manufacturer_id . ',0);';
                            $this->db->query($s);
                        }

                        $inserted_product_id = $this->facebook_business->createOrUpdate($product_decoded, $manufacturer_id, null, true);
                    }else{
                        //Insert product with brand id 0
                        $inserted_product_id = $this->facebook_business->createOrUpdate($product_decoded, 0, null, true);
                    }

                    $pr[] = $inserted_product_id;
                }
				
				if(!empty($pr)){
					$this->facebook_business->markProductImported($pr);
				}
            }
			
			 //Add the job to the DB
        $job_id = $this->queue_model->addJob([
											'catalog_id'	 => $this->catalog_id,
											'operation' 	 => 'import',
											'status' 	 	 => 'completed',
											'operation_type' => 'runtime',
											'product_count'  => count($products_in_db)
											]);
			
			// echo json_encode( ['status' => 'success', 'message' => "catalog imported successfully" , 'pr' => $pr]);
			$this->result_json['success'] = '1';
			$this->result_json['message'] = "catalog imported successfully";
			//$this->result_json['message'] = "catalog imported successfully";
            $this->response->setOutput(json_encode($this->result_json));
            return;
          
    }

	/**
     * handle the import all request
     *
     * @return void
     */
   //
   public function handleImportAll(){
		
        $this->load->language('module/facebook_business');

        if ($this->system_user_token && $_SERVER['REQUEST_METHOD'] === 'POST') {
           
		    $catalog_id = $this->catalog_id;
            $check_job  = $this->queue_model->getLatestJobByCatalogId($catalog_id);
			
            if ($check_job && !in_array($check_job['status'], ['completed', 'failed'])) {
				$this->result_json['success'] = '0';
				$this->result_json['message'] = $this->language->get('res_queue_exists');;
				$this->response->setOutput(json_encode($this->result_json));
				return;
		   }
			
            //Add the job to the DB
            $job_id = $this->queue_model->addJob([
												  'catalog_id' => $catalog_id,
												  'operation'  => 'import'
												]);
			//for test sync
			/*
			$job 	= $this->queue_model->getJob($job_id);
			$token  = $this->system_user_token;
			$result = $this->facebook_business->fetchAllFacebookProductsToDB($job['catalog_id'], 1000, $job_id, $token);
			
			var_dump($result);
			die();
			*/
			//#for test  
			  
			
            //Run the queue process
            $file_location = DIR_SYSTEM . 'library/facebook_business_queue.php';

            $token = $this->system_user_token;

            $storecode = STORECODE;
			
			$operation = "import" ;

            shell_exec("php $file_location $job_id \"$token\" \"$operation\" $storecode >/dev/null 2>&1 &");
            //shell_exec("php $file_location $job_id \"$token\" \"$operation\" $storecode > NUL 2>&1 "); //for local test 

			$this->result_json['success'] = '1';
			$this->result_json['message'] = $this->language->get('res_queue_import_success');
           
        } else {
			$this->result_json['success'] = '0';
			$this->result_json['message'] = $this->language->get('res_error_happened');
		}
		
		$this->response->setOutput(json_encode($this->result_json));
        return;
    }

	/**
     * Innitialize the import queue job
     *
     * @param int $job_id The job ID to initialize
     * @param string $token Facebook access token
     */
    public function importAllProductsQueueJob($job_id = 0, $token = null){
		
		$job = $this->queue_model->getJob($job_id, $token);
		
        try {
            $this->facebook_business->fetchAllFacebookProductsToDB($job['catalog_id'], 1000, $job_id, $token);
        } catch (FacebookSDKException $e) {
            file_put_contents(BASE_STORE_DIR . 'logs/facebook_errors.txt', $e->getMessage() . "\n", FILE_APPEND);
        }
    }

	/**
     * Innitialize the export queue job
     *
     * @param int $job_id The job ID to initialize
     * @param string $token Facebook access token
     */
    public function exportAllProductsQueueJob($data){

        $job = $this->queue_model->getJob($data['job_id']);

		$data['catalog_id'] = $job['catalog_id'];
        
		try {
            $this->facebook_business->exportAllFacebookProductsFromDB($data);
			
        } catch (FacebookSDKException $e) {
            file_put_contents(
                BASE_STORE_DIR . 'logs/facebook_errors.txt',
                'Something wrong happend, please contact support.' . $e->getMessage() . "\n",
                FILE_APPEND
            );
            die;
        }
    }
	
	//
    public function handleExport(){

        //Load the language
        $this->load->language('module/facebook_business');

        //Check for proper request
        if (!$this->system_user_token || $_SERVER['REQUEST_METHOD'] != 'POST') {
			$result = [
						'success' => '0',
						'message' => $this->language->get('res_something_went_wrong')
						];
						
			$this->response->setOutput(json_encode($result));
			return;
        }

        //Set the required vars
        $catalog_id 			  = $this->request->post['catalog_id']?? $this->catalog_id;
		$selected_products 		  = $this->request->post['products_ids'];
		$main_lang_id 			  = $this->request->post['main_lang_id'];
		$localize_languages_codes = $this->request->post['localize_languages'];
		
        //Check for products
        if (count($selected_products) < 1) {
            $result = [
						'success' => '0',
						'message' => $this->language->get('res_export_select_product')
						];
						
			$this->response->setOutput(json_encode($result));
			return;
        }
		
        //Check for products
        if (count($localize_languages_codes) < 1) {
            $result = [
						'success' => '0',
						'message' => "should use at least one localize lang"
						];
						
			$this->response->setOutput(json_encode($result));
			return;
        }

		 //Add the job to the DB
        $job_id = $this->queue_model->addJob([
											'catalog_id'	 => $catalog_id,
											'operation' 	 => 'export',
											'status' 	 	 => 'completed',
											'operation_type' => 'runtime'
											]);
											
		$data 	= [
					'selected_products' 		=> $selected_products,
					'main_lang_id'  			=> $main_lang_id,
					'localize_languages_codes'  => $localize_languages_codes,
					'catalog_id' 				=> $catalog_id,
					'job_id' 					=> $job_id
					];
					
		$result = $this->facebook_business->exportSelectedProducts($data);
		
		$this->response->setOutput(json_encode($result));
        return;
    }

	// 
	/**
     * handle the export all request
     *
     * @return void
     */
    public function handleExportAll(){
		
        $this->load->language('module/facebook_business');

        //Check for proper request
        if (!$this->system_user_token || $_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->result_json['success'] = '0';
			$this->result_json['message'] = $this->language->get('res_export_select_product');
			$this->response->setOutput(json_encode($this->result_json));
			return;
        }

		$catalog_id = $this->request->post['catalog_id']?? $this->catalog_id;
        $check_job  = $this->queue_model->getLatestJobByCatalogId($catalog_id);

        if ($check_job && !in_array($check_job['status'], ['completed', 'failed'])) {           
			$this->result_json['success'] = '0';
			$this->result_json['message'] = $this->language->get('res_queue_exists');
			$this->response->setOutput(json_encode($this->result_json));
			return;        
		}

		//Set the required vars
        $catalog_id 			  = $this->request->post['catalog_id']?? $this->catalog_id;
		$main_lang_id 			  = $this->request->post['main_lang_id'];
		$localize_languages_codes = $this->request->post['localize_languages'];
		
		if(is_array($localize_languages_codes)){
			$localize_languages_codes = implode(",",$localize_languages_codes);
		}
		
        //Add the job to the DB
        $job_id = $this->queue_model->addJob([
											'catalog_id' => $catalog_id,
											'operation'  => 'export'
											]);
		
        //Run the queue process
        $file_location 	= DIR_SYSTEM . 'library/facebook_business_queue.php';
        $token  		= $this->system_user_token;
        $storecode 		= STORECODE;
        $http_catalog 	= HTTPS_CATALOG;
		
        shell_exec("php $file_location $job_id \"$token\" \"export\" $storecode $http_catalog $main_lang_id \"$localize_languages_codes\" >/dev/null 2>&1 &");
       // shell_exec( "php $file_location $job_id \"$token\" \"export\" $storecode $http_catalog $main_lang_id \"$localize_languages_codes\"  > NUL 2>&1 "); //for local test 
		/*
		//this for test only 
		$data 	= [
					'main_lang_id'  			=> $main_lang_id,
					'localize_languages_codes'  => $localize_languages_codes,
					'catalog_id' 				=> $catalog_id,
					'job_id' 					=> $job_id,
					'http_catalog'				=> $http_catalog
					];
					
		$this->exportAllProductsQueueJob($data);
		
		*/
		$this->result_json['success'] = '1';
		$this->result_json['message'] = $this->language->get('res_queue_export_success');
		$this->response->setOutput(json_encode($this->result_json));
		return;
	}

	//
	public function finalizePartialSetup(){
		$feature = $this->request->post['feature'];
		
		$has_pixel 		 = $this->pixel_id != '' && $this->pixel_id != false;
		$has_page 	 	 = $this->page_id != '' && $this->page_id != false;
		$has_catalog 	 = $this->catalog_id != '' && $this->catalog_id != false;
		
		if($feature == 'fb_pixel'){
			
			if(!$has_pixel){
				$this->result_json['success'] = '0';
				$this->result_json['message'] = 'you should choose one pixel account to continue setup!';
				$this->response->setOutput(json_encode($this->result_json));
				return;
			
			}
			
			$fbe_settings = $this->model_setting_setting->getSetting($this->name);
			$this->model_setting_setting->insertUpdateSetting($this->name, ["isPixelsInstalled" =>1]);
			
			//cant assign pixel to system user till whitelist our App to use Ads Api's 
			
		}
		else if ($feature == 'fb_shop'){
			if(!$has_page){
				$this->result_json['success'] = '0';
				$this->result_json['message'] = 'you should choose one page to continue setup!';
				$this->response->setOutput(json_encode($this->result_json));
				return;
				
			}if(!$has_catalog){
				$this->result_json['success'] = '0';
				$this->result_json['message'] = 'you should choose one catalog to continue setup';
				$this->response->setOutput(json_encode($this->result_json));
				return;
			}
			
			
			$result = $this->facebook_business->assignAssetToSU($this->catalog_id,'MANAGE');
			if(!$result->success){
					$this->result_json['success'] = '0';
					$this->result_json['message'] = 'Cant assign catalog to the SU';
					$this->response->setOutput(json_encode($this->result_json));
					return;
			}
			
			$result = $this->facebook_business->assignAssetToSU($this->page_id,'MANAGE');
			if(!$result->success){
					$this->result_json['success'] = '0';
					$this->result_json['message'] = 'Cant assign page to the SU';
					$this->response->setOutput(json_encode($this->result_json));
					return;
			}
			
			
			$fbe_settings = $this->model_setting_setting->getSetting($this->name);
			$this->model_setting_setting->insertUpdateSetting($this->name, ["isFacebookInstalled" =>1]);
			
		}
		
		
		$this->result_json['success'] = '1';
		$this->response->setOutput(json_encode($this->result_json));
		return;		
		
	}
	
	//-------------- Helpers ---------------//
	//Helper
    public function getInstagramAccounts($pages){

        if(count($pages) > 0) {

            $accounts = [];

            foreach ($pages as $page) {

                try {

                    $result = $this->fb->get( '/' . $page["id"] . "?fields=instagram_business_account{id,username}", $page["access_token"])->getDecodedBody();

                    if(count($result) > 0 && isset($result["instagram_business_account"])) {
                        $result["instagram_business_account"]["page_id"] = $page["id"];
                        array_push($accounts, $result["instagram_business_account"]);
                    }

                   // return ["name" => $result["name"], "profile_image_url" => $result["picture"]["data"]["url"]];
                } catch (FacebookSDKException $e) {
                        print_r($e->getMessage());
                        die();
                    //return [];
                }


            }

            return $accounts;

        }

    }

	//Helper
    public function getAdAccounts($businessId) {
			
        try {

            $result = $this->fb->get( '/' . $businessId . '/owned_ad_accounts', $this->accessToken, null, "v11.0")->getDecodedBody();
            return $result["data"];

        } catch(FacebookSDKException $e) {

            $href = strtok($_SERVER["HTTP_REFERER"], '?');
            $href = $href . "?error=" . substr($e->getMessage(), 0, 5);
            $this->redirect($href);
            return;
        }

    }

}

