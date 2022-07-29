<?php
// Adikon.eu
// version 1.5
// created 22/10/2015
class CompatibilityAdmin extends Controller {
	protected function loadStyles($module) {
		if (version_compare(VERSION, '2.0') < 0) {
			$this->document->addStyle('view/javascript/' . $module . '/font-awesome/css/font-awesome.min.css');
			//$this->document->addScript('view/javascript/' . $module . '/bootstrap/js/bootstrap.min.js');
			//$this->document->addScript('view/javascript/' . $module . '/compatibility.js');
			//$this->document->addStyle('view/javascript/' . $module . '/bootstrap/css/bootstrap.css');
			//$this->document->addStyle('view/javascript/' . $module . '/compatibility.css');
			$this->document->addStyle('view/javascript/' . $module . '/summernote/summernote.css');
			$this->document->addScript('view/javascript/' . $module . '/summernote/summernote.js');
			$this->document->addScript('view/javascript/' . $module . '/datetimepicker/moment.js');
			$this->document->addScript('view/javascript/' . $module . '/datetimepicker/bootstrap-datetimepicker.min.js');
			$this->document->addStyle('view/javascript/' . $module . '/datetimepicker/bootstrap-datetimepicker.min.css');
		}

		$this->document->addStyle('view/javascript/' . $module . '/module.css');
	}

	protected function toOutput($file, $data) {
		if (version_compare(VERSION, '2.0') < 0) {
			$data['column_left'] = '';
			$this->data = $data;

			$this->template = $file;

			$this->children = array(
				'common/header',
				'common/footer',
			);

			$this->response->setOutput($this->compatibilityJquery($this->render()));
		} else {
			$data['header'] = $this->compatibilityJquery($this->load->controller('common/header'));
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');

			$this->response->setOutput($this->load->view($file, $data));
		}
	}

	protected function redirectTo($route, $params = '', $ssl = true) {
		if (!$route) {
			$route = 'common/home';
		}

		if (isset($this->session->data['token']) && $this->session->data['token'] && !preg_match('#token=#i', $params)) {
			$params = 'token=' . $this->session->data['token'] . $params;
		}

		if (version_compare(VERSION, '2.0') < 0) {
			$this->redirect($this->url->link($route, $params, ($ssl) ? 'SSL' : 'NONSSL'));
		} else {
			$this->response->redirect($this->url->link($route, $params, ($ssl) ? 'SSL' : 'NONSSL'));
		}
	}

	protected function getStores($route) {
		$stores = array();

		$stores[0] = array('store_id' => '0', 'href' => $this->url->link($route, 'token=' . $this->session->data['token'] . '&filter_store_id=0', 'SSL'), 'url' => ((defined('HTTP_CATALOG')) ? HTTP_CATALOG : ''), 'name' => 'Store: Default', 'store_name' => 'Default');

		$this->load->model('setting/store');

		$results = $this->model_setting_store->getStores();

		foreach ($results as $store) {
			$stores[$store['store_id']] = array(
				'store_id'   => $store['store_id'],
				'href'       => $this->url->link($route, 'token=' . $this->session->data['token'] . '&filter_store_id=' . (int)$store['store_id'], 'SSL'),
				'url'        => preg_match('#^http#i', $store['url']) ? $store['url'] : 'http://'. $store['url'],
				'name'       => 'Store: ' . $store['name'],
				'store_name' => $store['name']
			);
		}

		return (array)$stores;
	}

	protected function getCoupons($start = 0, $limit = 999999) {
		$coupons = array();

		if (version_compare(VERSION, '2.0') < 0) {
			$this->load->model('sale/coupon');

			$coupons = $this->model_sale_coupon->getCoupons(array('start' => $start, 'limit' => $limit));
		} else {
			$this->load->model('marketing/coupon');

			$coupons = $this->model_marketing_coupon->getCoupons(array('start' => $start, 'limit' => $limit));
		}

		return (array)$coupons;
	}

	protected function paginationText($page, $limit, $total) {
		return (version_compare(VERSION, '2.0') < 0) ? '' : sprintf($this->language->get('text_pagination'), ($total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($total - $limit)) ? $total : ((($page - 1) * $limit) + $limit), $total, ceil($total / $limit));
	}

	private function compatibilityJquery($header) {
		return str_replace(array('view/javascript/jquery/jquery-1.6.1.min.js', 'view/javascript/jquery/jquery-1.7.1.min.js', 'http://code.jquery.com/jquery-1.7.2.min.js', '../catalog/view/javascript/jquery/jquery-1.7.2.min.js', 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js'), '//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js', $header);
	}

	public function isHttps() {
		return ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on'));
	}

	/* Setting */
	protected function getSetting($group, $store_id = 0) {
		$data = array();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE store_id = '" . (int)$store_id . "' AND `" . (version_compare(VERSION, '2.0.1') < 0 ? 'group' : 'code') . "` = '" . $this->db->escape($group) . "'");

		foreach ($query->rows as $result) {
			$result['key'] = str_replace($group . '_', '', $result['key']);

			if (!$result['serialized']) {
				$data[$result['key']] = $result['value'];
			} else {
				if (version_compare(VERSION, '2.1') > 0) {
					$data[$result['key']] = json_decode($result['value'], true);
				} else {
					$data[$result['key']] = unserialize($result['value']);
				}
			}
		}

		return $data;
	}

	protected function editSetting($group, $data, $store_id = 0) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '" . (int)$store_id . "' AND `" . (version_compare(VERSION, '2.0.1') < 0 ? 'group' : 'code') . "` = '" . $this->db->escape($group) . "'");

		foreach ($data as $key => $value) {
			if (!is_array($value)) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET store_id = '" . (int)$store_id . "', `" . (version_compare(VERSION, '2.0.1') < 0 ? 'group' : 'code') . "` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($group . '_' . $key) . "', `value` = '" . $this->db->escape($value) . "'");
			} else {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET store_id = '" . (int)$store_id . "', `" . (version_compare(VERSION, '2.0.1') < 0 ? 'group' : 'code') . "` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($group . '_' . $key) . "', `value` = '" . (version_compare(VERSION, '2.1') > 0 ? $this->db->escape(json_encode($value)) : $this->db->escape(serialize($value))) . "', serialized = '1'");
			}
		}
	}
}
?>