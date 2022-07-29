<?php
class ControllerModulePopupWindow extends Controller  {
	// Module Unifier
	private $moduleName = 'PopupWindow';
	private $moduleNameSmall = 'popupwindow';
	private $moduleData_module = 'popupwindow_module';
	private $moduleModel = 'model_module_popupwindow';
	// Module Unifier

	public function index($setting) {
		$this->data['url'] = preg_replace('/https?\:/', '', $this->url->link("module/".$this->moduleNameSmall."/getPopup", "", "SSL"));

		$this->data['updateImpressionsURL'] = htmlspecialchars_decode($this->url->link('module/popupwindow/updateImpressions', '', 'SSL'));

        $this->document->addExpandishStyle('stylesheet/'.$this->moduleNameSmall.'/animate.css');

		$this->document->addStyle('expandish/view/javascript/jquery/fancybox/jquery.fancybox.css');
		$this->document->addScript('expandish/view/javascript/jquery/fancybox/jquery.fancybox.js');

        $this->document->addExpandishStyle('stylesheet/'.$this->moduleNameSmall.'/popupwindow.css');


        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/' . $this->moduleNameSmall . '.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/' . $this->moduleNameSmall . '.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/module/' . $this->moduleNameSmall . '.expand';
        }

		$this->render_ecwig();
	}

	public function updateImpressions () {
		if(isset($this->request->get['popup_id'])) {
			$this->load->model('module/popupwindow');
			$this->model_module_popupwindow->updateImpressions($this->request->get['popup_id']);
		}
	}

	protected function showPopup($popup_id)
	{	
			if (!isset($this->session->data['popups_repeat']) || !in_array($popup_id, $this->session->data['popups_repeat'])) {
				$this->session->data['popups_repeat'][] = $popup_id;
				return true;
			}
			else {
				return false;
			}	
	}

	public function checkCustomerGroup($popup){
		$popup_customer_group = !empty($popup['customerGroups']) ? $popup['customerGroups'] : array() ;
		$customer_group_id = $this->customer->getCustomerGroupId() ? $this->customer->getCustomerGroupId() : 0;
		return array_key_exists($customer_group_id,$popup_customer_group);
	}

	public function cookieCheck($days, $popup_id) {
		if(!isset($_COOKIE["popupwindow".$popup_id])) {
				setcookie("popupwindow".$popup_id,true, time()+3600*24*$days);
				return true;
		}
		else {
			return false;
		}
	}

	public function timeIsBetween($from, $to, $enabled) {
		$date = 'now';
	    $date = is_int($date) ? $date : strtotime($date); // convert non timestamps
	    $from = is_int($from) ? $from : strtotime($from); // ..
	    $to = is_int($to) ? $to : strtotime($to);
	    if($enabled=="0")
	    	return true;
	    else         // ..
	    	return ($date > $from) && ($date < $to); // extra parens for clarity
	}

	private function checkRepeatConditions($popup) {
		return ($popup['repeat']==0) 
				|| ($popup['repeat']==1 && $this->showPopup($popup['id'], $popup['repeat'])) 
				|| ($popup['repeat']==2 && $this->cookieCheck($popup['days'], $popup['id']));
	}

	private function isHome($uri) {
		$parsedURI = parse_url($uri);
		if(		(strcmp(HTTP_SERVER,$uri)===0)
			|| 	(strcmp(HTTPS_SERVER,$uri)===0)
			|| 	(isset($parsedURI['query']) && $parsedURI['query'] == 'route=common/home')
		    || 	(!isset($parsedURI['query']) && $parsedURI['path']=='/')
		   ) {
			return true;
		} else
			return false;
	}

	function checkDateRange($start_date, $end_date)
	{
	  // Convert to timestamp
	  $start_ts = strtotime($start_date);
	  $end_ts = strtotime($end_date);
	  $current_ts = strtotime(date("Y-m-d"));

	  // Check if current date is between start & end
	  return (($current_ts >= $start_ts) && ($current_ts <= $end_ts));
	}
	
	public function getPopup(){
		header('Access-Control-Allow-Origin: *'); 
		$date = date('H:i', time());

		$data = $this->config->get('PopupWindow');
		if (isset($_GET['uri'])) { $uri=$_GET['uri']; } else { $uri=""; }

		$uri = htmlspecialchars_decode((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://').$_SERVER['SERVER_NAME'].$uri);
		
		if(!isset($this->session->data['popups_repeat']))
			$this->session->data['popups_repeat'] = array();

		$json = array();
							
		foreach ($data['PopupWindow'] as $popup) {
				if($popup['Enabled']=="yes" && $this->checkCustomerGroup($popup)) {

					if($popup['method'] == "0") { // On Homepage method
						if(empty($popup['date_interval']) || $this->checkDateRange($popup['start_date'], $popup['end_date'])) {
							if($this->timeIsBetween($popup['start_time'],$popup['end_time'], $popup['time_interval'])) {
								if($this->isHome($uri)) {
									if($this->checkRepeatConditions($popup)) {
											$temp['match'] = true;
											$temp['popup_id'] =  $popup['id'];
											$temp['content'] = html_entity_decode($popup['content'][$this->config->get('config_language_id')]);
											$temp['width'] = $popup['width'];
											$temp['height'] = $popup['height'];
											$temp['event'] = $popup['event'];
											if ($popup['event']== 4) {
												$temp['percentage_value'] = $popup['scroll_percentage'];
											}
											//fancybox options
											$temp['aspect_ratio'] = $popup['aspect_ratio'];
											$temp['auto_resize'] = $popup['auto_resize'];

											$temp['seconds'] = $popup['seconds'];
											$temp['animation'] = $popup['animation'];
											$temp['prevent_closing'] = $popup['prevent_closing'];
											$json[] = $temp;
									}
								}
							}
						}
					}
					
					if($popup['method'] == "1") {  // All pages method
						if(empty($popup['date_interval']) || $this->checkDateRange($popup['start_date'], $popup['end_date'])) {
							if($this->timeIsBetween($popup['start_time'],$popup['end_time'], $popup['time_interval'])) {
								$excludedURLs = array();
								$excludedURLs = array_map("urldecode",preg_split("/\\r\\n|\\r|\\n/", html_entity_decode($popup['excluded_urls'])));
								if(($this->checkRepeatConditions($popup)) && !in_array($uri,$excludedURLs)) {
										$temp['match'] = true;
										$temp['popup_id'] =  $popup['id'];
										$temp['content'] = html_entity_decode($popup['content'][$this->config->get('config_language_id')]);
										$temp['width'] = $popup['width'];
										$temp['height'] = $popup['height'];
										$temp['event'] = $popup['event'];

										if ($popup['event']== 4) {
												$temp['percentage_value'] = $popup['scroll_percentage'];
											}
										//fancybox options
										$temp['aspect_ratio'] = $popup['aspect_ratio'];
										$temp['auto_resize'] = $popup['auto_resize'];

										$temp['seconds'] = $popup['seconds'];
										$temp['animation'] = $popup['animation'];
										$temp['prevent_closing'] = $popup['prevent_closing'];
										$json[] = $temp;
								}
							}
						}
					}
					
					if($popup['method'] == "2") {  // Specific URLs method
						if(empty($popup['date_interval']) || $this->checkDateRange($popup['start_date'], $popup['end_date'])) {
							if($this->timeIsBetween($popup['start_time'],$popup['end_time'], $popup['time_interval'])) {
								$URLs = array();
								$URLs = array_map("urldecode",preg_split("/\\r\\n|\\r|\\n/", html_entity_decode($popup['url'])));
								$popup['url'] =	htmlspecialchars_decode($popup['url']);
								foreach($URLs as $url) {
									if(!empty($url) && strpos($uri, $url) !== false) {
										if($this->checkRepeatConditions($popup)) {
											$temp['match'] = true;
											$temp['popup_id'] =  $popup['id'];
											$temp['content'] = html_entity_decode($popup['content'][$this->config->get('config_language_id')]);
											$temp['width'] = $popup['width'];
											$temp['height'] = $popup['height'];
											$temp['event'] = $popup['event'];

											if ($popup['event']== 4) {
												$temp['percentage_value'] = $popup['scroll_percentage'];
											}
											//fancybox options
											$temp['aspect_ratio'] = $popup['aspect_ratio'];
											$temp['auto_resize'] = $popup['auto_resize'];

											$temp['seconds'] = $popup['seconds'];
											$temp['animation'] = $popup['animation'];
											$temp['prevent_closing'] = $popup['prevent_closing'];
											$json[] = $temp;
										}
									}
								}
							}
						}
					}
					
					if($popup['method'] == "3") { // CSS Selector method
						if(empty($popup['date_interval']) || $this->checkDateRange($popup['start_date'], $popup['end_date'])) {
							if($this->timeIsBetween($popup['start_time'],$popup['end_time'], $popup['time_interval'])) {
								if($this->checkRepeatConditions($popup)) {
									$temp['match'] = true;
									$temp['popup_id'] =  $popup['id'];
									$temp['content'] = html_entity_decode($popup['content'][$this->config->get('config_language_id')]);
									$temp['width'] = $popup['width'];
									$temp['height'] = $popup['height'];
									$temp['event'] = 5;
									//fancybox options
									$temp['aspect_ratio'] = $popup['aspect_ratio'];
									$temp['auto_resize'] = $popup['auto_resize'];
									
									$temp['seconds'] = $popup['seconds'];
									$temp['css_selector'] = $popup['css_selector'];
									$temp['animation'] = $popup['animation'];
									$temp['prevent_closing'] = $popup['prevent_closing'];
									$json[] = $temp;
								}	
							}
						}
					}
				}

			}
		$this->response->setOutput(json_encode($json));		
	}
}
?>