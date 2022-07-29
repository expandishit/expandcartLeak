<?php

class ControllerExtensionTotal extends Controller
{
    public $route = "module/order_total";
    public $module = "order_total";

    public function __construct($registry)
    {
        parent::__construct($registry);

        if (!\Extension::isInstalled($this->module) || !$this->user->hasPermission("modify", "module/{$this->module}")) {
            $this->redirect(
                $this->url->link('error/permission')
            );
        }
    }

	public function index()
    {
		$this->language->load('extension/total');
		 
		$this->document->setTitle($this->language->get('heading_title')); 

   		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/total', '', 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['heading_title'] = $this->language->get('heading_title');
			
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_confirm'] = $this->language->get('text_confirm');

		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_sort_order'] = $this->language->get('column_sort_order');
		$this->data['column_action'] = $this->language->get('column_action');

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		if (isset($this->session->data['error'])) {
			$this->data['error'] = $this->session->data['error'];
		
			unset($this->session->data['error']);
		} else {
			$this->data['error'] = '';
		}

		$this->data['extensions'] = array();
        $this->data['pinned_extensions'] = array(); // Can't resorted

		$this->initializer(['extension/total']);

        $totalExtensions = $this->total->getInstalledExtensions();

        $totalExtensions = array_column($totalExtensions, 'extension_id', 'code');
		$this->load->model('setting/setting');
		foreach ($totalExtensions as $extension => $id) {
            
            // check cffpm app is installed
            if($extension == 'cffpm') {
                $this->load->model('module/custom_fees_for_payment_method');
                if (!$this->model_module_custom_fees_for_payment_method->is_module_installed()) {
                    break;
                }
            }

			$this->language->load('total/' . $extension);
			$status = null;
			if($extension == 'earn_point' ||$extension == 'redeem_point'){
				$status = $this->model_setting_setting->getSetting($extension)[$extension.'_status'];
			}
			$extension_data = array(
				'name'       => $this->language->get('heading_title'),
				'status'     => $status ?? $this->config->get($extension . '_status'),
				'sort_order' => $this->config->get($extension . '_sort_order'),
				'action'     => $action,
				'edit'       => [
					'text' => $this->language->get('text_edit'),
					'href' => $this->url->link('total/' . $extension . '', '', 'SSL')
				],
				'extension_id' => $id ?: null,
				'unique_name' => $extension ?: null,
			);

            if($extension == 'total'){
                $this->data['pinned_extensions'][] = $extension_data;
            }else{
                $this->data['extensions'][] =  $extension_data;
            }
		}

		usort($this->data['extensions'], function ($a, $b) {
		    return $a['sort_order'] - $b['sort_order'];
        });

		$this->data['locales'] = [
		    'search' => $this->language->get('search'),
		    'show' => $this->language->get('show'),
		    'search_placeholder' => $this->language->get('search_placeholder'),
		    'paginate_first' => $this->language->get('paginate_first'),
		    'paginate_last' => $this->language->get('paginate_last'),
		    'paginate_next' => $this->language->get('paginate_next'),
		    'paginate_prev' => $this->language->get('paginate_prev'),
        ];

        $this->document->addScript('view/assets/js/plugins/tables/datatables/extensions/row_reorder.min.js');
        $this->document->addScript('view/javascript/extension/total.js');

		$this->template = 'extension/total.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render_ecwig());
	}

	public function updateSortOrder()
    {
		$this->language->load('extension/total');
        
        $this->initializer([
            'extension/total',
        ]);

        foreach ($this->request->post['data'] as $postData) {

            $key = $postData['name'] . '_sort_order';

            $postData['now'] = $postData['now'] ?: 0;

            $this->total->updateSortOrder($postData, $key);
        }

        $result_json['success'] = '1';
        
        $result_json['success_msg'] = $this->language->get('text_success');

        $this->response->setOutput(json_encode($result_json));
        
        return;

    }

    public function updateStatus()
    {
		$this->language->load('extension/total');
        
        $this->initializer([
            'extension/total',
        ]);

        $postData = $this->request->post;

        $status = $postData['status'];

        $key = $postData['name'] . '_status';

        $this->total->updateStatus($postData['name'], $key, $status);

        $result_json['success'] = '1';
        
        $result_json['success_msg'] = $this->language->get('text_success');

        $this->response->setOutput(json_encode($result_json));
        
        return;
    }

	public function install()
    {
		$this->language->load('extension/total');
			
		if (!$this->user->hasPermission('modify', 'extension/total')) {
			$this->session->data['error'] = $this->language->get('error_permission'); 
			
			$this->redirect($this->url->link('extension/total', '', 'SSL'));
		} else {				
			$this->load->model('setting/extension');
		
			$this->model_setting_extension->install('total', $this->request->get['extension']);

			$this->load->model('user/user_group');
		
			$this->model_user_user_group->addPermission($this->user->getId(), 'access', 'total/' . $this->request->get['extension']);
			$this->model_user_user_group->addPermission($this->user->getId(), 'modify', 'total/' . $this->request->get['extension']);

			require_once(DIR_APPLICATION . 'controller/total/' . $this->request->get['extension'] . '.php');
			
			$class = 'ControllerTotal' . str_replace('_', '', $this->request->get['extension']);
			$class = new $class($this->registry);
			
			if (method_exists($class, 'install')) {
				$class->install();
			}
			
			$this->redirect($this->url->link('extension/total', '', 'SSL'));
		}
	}
	
	public function uninstall()
    {
		$this->language->load('extension/total');
		
		if (!$this->user->hasPermission('modify', 'extension/total')) {
			$this->session->data['error'] = $this->language->get('error_permission'); 
			
			$this->redirect($this->url->link('extension/total', '', 'SSL'));
		} else {			
			$this->load->model('setting/extension');
			$this->load->model('setting/setting');
		
			$this->model_setting_extension->uninstall('total', $this->request->get['extension']);
		
			$this->model_setting_setting->deleteSetting($this->request->get['extension']);
		
			require_once(DIR_APPLICATION . 'controller/total/' . $this->request->get['extension'] . '.php');
			
			$class = 'ControllerTotal' . str_replace('_', '', $this->request->get['extension']);
			$class = new $class($this->registry);
			
			if (method_exists($class, 'uninstall')) {
				$class->uninstall();
			}
		
			$this->redirect($this->url->link('extension/total', '', 'SSL'));
		}
	}	
}
