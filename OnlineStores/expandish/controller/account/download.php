<?php

use ExpandCart\Foundation\Providers\Extension;

class ControllerAccountDownload extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/download', '', 'SSL');

			$this->redirect($this->url->link('account/login', '', 'SSL'));
        }
        
        if (!Extension::isInstalled('product_attachments') || !((int) $this->config->get('product_attachments')['status'] === 1)) {
			$this->redirect($this->url->link('account/account', '', 'SSL'));
        }
        
        $this->data['taps'] = $this->getChild('account/taps/renderTaps', 'account-download');
         		
		$this->language->load_json('account/download', 1);

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
        	'text'      => $this->language->get('text_downloads'),
			'href'      => $this->url->link('account/download', '', 'SSL'),       	
        	'separator' => $this->language->get('text_separator')
      	);
				
		$this->load->model('account/download');

		$download_total = $this->model_account_download->getTotalDownloads();
		
		if ($download_total) {
			if (isset($this->request->get['page'])) {
				$page = $this->request->get['page'];
			} else {
				$page = 1;
			}			
	
			$this->data['downloads'] = array();
			
			$results = $this->model_account_download->getDownloads(($page - 1) * $this->config->get('config_catalog_limit'), $this->config->get('config_catalog_limit'));
			


			foreach ($results as $result) {

				// $path = new File($result['filename'], ['adapter' => 'gcs', 'base' => STORECODE . '/downloads']);
				\Filesystem::setPath('downloads/' . $result['filename']);

				// if (file_exists(DIR_DOWNLOAD . $result['filename'])) {
				if (\Filesystem::isExists()) {
					// $size = filesize(DIR_DOWNLOAD . $result['filename']);
					$size = \Filesystem::getSize();

					$i = 0;

					$suffix = array(
						'B',
						'KB',
						'MB',
						'GB',
						'TB',
						'PB',
						'EB',
						'ZB',
						'YB'
					);

					while (($size / 1024) > 1) {
						$size = $size / 1024;
						$i++;
					}

					$this->data['downloads'][] = array(
						'order_id'   => $result['order_id'],
						'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
						'name'       => $result['name'],
						'remaining'  => $result['remaining'],
						'size'       => round(substr($size, 0, strpos($size, '.') + 4), 2) . $suffix[$i],
						'href'       => $this->url->link('account/download/download', 'order_download_id=' . $result['order_download_id'], 'SSL')
					);
				}
			}
		
			$pagination = new Pagination();
			$pagination->total = $download_total;
			$pagination->page = $page;
			$pagination->limit = $this->config->get('config_catalog_limit');
			$pagination->text = $this->language->get('text_pagination');
			$pagination->url = $this->url->link('account/download', 'page={page}', 'SSL');
			
			$this->data['pagination'] = $pagination->render();
			
			//$this->data['continue'] = $this->url->link('account/account', '', 'SSL');

            // if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/download.expand')) {
            //     $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/download.expand';
            // }
            // else {
            //     $this->template = $this->config->get('config_template') . '/template/account/download.expand';
            // }

			$this->template = $this->checkTemplate('account/download.expand');
		} else {
			//$this->data['continue'] = $this->url->link('account/account', '', 'SSL');

            // if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/error/not_found.expand')) {
            //     $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/error/not_found.expand';
            // }
            // else {
            //     $this->template = $this->config->get('config_template') . '/template/error/not_found.expand';
            // }
        
            // $this->template = 'default/template/account/error/not_found.expand';

			$this->template = $this->checkTemplate('account/error/not_found.expand');
        }
    
    	$this->children = array(
    	    'common/footer',
    	    'common/header'
        );
        
        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) {
            // This is to handle new template structure using extend
            $this->data['include_page'] = 'download.expand';
            if(USES_TWIG_EXTENDS == 1)
                $this->template = 'default/template/account/layout_extend.expand';
            else
                $this->template = 'default/template/account/layout_default.expand';
        }
        
        $this->response->setOutput($this->render_ecwig());
	}

	public function download() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/download', '', 'SSL');

			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$this->load->model('account/download');
		
		if (isset($this->request->get['order_download_id'])) {
			$order_download_id = $this->request->get['order_download_id'];
		} else {
			$order_download_id = 0;
		}
		
		$download_info = $this->model_account_download->getDownload($order_download_id);
		
		if ($download_info) {
			$fileName = $download_info['filename'];
			// $file = new File($fileName, ['adapter' => 'gcs', 'base' => STORECODE . '/downloads']);
			\Filesystem::setPath('downloads/' . $fileName);
			// $file = DIR_DOWNLOAD . $download_info['filename'];
			if (!preg_match(sprintf('#\.%s#i', $download_info['extension']), $download_info['mask'])) {
				$mask = $download_info['mask'] . '.' . $download_info['extension'];
			} else {
				$mask = basename($download_info['mask']);
			}

			if (!headers_sent()) {
				// if (file_exists($file)) {
				if (\Filesystem::isExists()) {
					header('Content-Type: application/octet-stream');
					header('Content-Disposition: attachment; filename="' . ($mask ? $mask : basename($fileName)) . '"');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Pragma: public');
					// header('Content-Length: ' . filesize($file));
					header('Content-Length: ' . \Filesystem::getSize());
					
					if (ob_get_level()) ob_end_clean();
					
					/*readfile(\Filesystem::read(), 'rb');*/
					echo \Filesystem::read();

					$this->model_account_download->updateRemaining($this->request->get['order_download_id']);
					
					exit;
				} else {
					exit('Error: Could not find file ' . $file . '!');
				}
			} else {
				exit('Error: Headers already sent out!');
			}
		} else {
			$this->redirect($this->url->link('account/download', '', 'SSL'));
		}
	}
}
?>
