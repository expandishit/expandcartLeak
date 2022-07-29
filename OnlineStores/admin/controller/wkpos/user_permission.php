<?php
class ControllerWkposUserPermission extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('wkpos/user_group');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('wkpos/user_group');

		$this->getList();
	}

	public function add() {
		$this->load->language('wkpos/user_group');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('wkpos/user_group');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_wkpos_user_group->addUserGroup($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('wkpos/user_permission', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('wkpos/user_group');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('wkpos/user_group');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_wkpos_user_group->editUserGroup($this->request->get['user_group_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('wkpos/user_permission', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('wkpos/user_group');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('wkpos/user_group');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $user_group_id) {
				$this->model_wkpos_user_group->deleteUserGroup($user_group_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('wkpos/user_permission', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('wkpos/user_permission', 'token=' . $this->session->data['token'] . $url, 'SSL')
		);

		$this->data['add'] = $this->url->link('wkpos/user_permission/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('wkpos/user_permission/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['user_groups'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$user_group_total = $this->model_wkpos_user_group->getTotalUserGroups();

		$results = $this->model_wkpos_user_group->getUserGroups($filter_data);

		foreach ($results as $result) {
			$this->data['user_groups'][] = array(
				'user_group_id' => $result['user_group_id'],
				'name'          => $result['name'],
				'edit'          => $this->url->link('wkpos/user_permission/edit', 'token=' . $this->session->data['token'] . '&user_group_id=' . $result['user_group_id'] . $url, 'SSL')
			);
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_list'] = $this->language->get('text_list');
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_confirm'] = $this->language->get('text_confirm');

		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_action'] = $this->language->get('column_action');

		$this->data['button_add'] = $this->language->get('button_add');
		$this->data['button_edit'] = $this->language->get('button_edit');
		$this->data['button_delete'] = $this->language->get('button_delete');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$this->data['selected'] = (array)$this->request->post['selected'];
		} else {
			$this->data['selected'] = array();
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$this->data['sort_name'] = $this->url->link('wkpos/user_permission', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $user_group_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('wkpos/user_permission', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->language->get('text_pagination'), ($user_group_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($user_group_total - $this->config->get('config_limit_admin'))) ? $user_group_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $user_group_total, ceil($user_group_total / $this->config->get('config_limit_admin')));

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->children = array(
           'common/header',
           'common/footer',
        );
		$this->template = 'wkpos/user_group_list.tpl';
		$this->response->setOutput($this->render(TRUE));

		/*$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('wkpos/user_group_list.tpl', $data));*/
	}

	protected function getForm() {
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_form'] = !isset($this->request->get['user_group_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$this->data['text_select_all'] = $this->language->get('text_select_all');
		$this->data['text_unselect_all'] = $this->language->get('text_unselect_all');

		$this->data['entry_name'] = $this->language->get('entry_name');
		$this->data['entry_access'] = $this->language->get('entry_access');
		$this->data['entry_modify'] = $this->language->get('entry_modify');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$this->data['error_name'] = $this->error['name'];
		} else {
			$this->data['error_name'] = '';
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('wkpos/user_permission', 'token=' . $this->session->data['token'] . $url, 'SSL')
		);

		if (!isset($this->request->get['user_group_id'])) {
			$this->data['action'] = $this->url->link('wkpos/user_permission/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('wkpos/user_permission/edit', 'token=' . $this->session->data['token'] . '&user_group_id=' . $this->request->get['user_group_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('wkpos/user_permission', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['user_group_id']) && $this->request->server['REQUEST_METHOD'] != 'POST') {
			$user_group_info = $this->model_wkpos_user_group->getUserGroup($this->request->get['user_group_id']);
		}

		if (isset($this->request->post['name'])) {
			$this->data['name'] = $this->request->post['name'];
		} elseif (!empty($user_group_info)) {
			$this->data['name'] = $user_group_info['name'];
		} else {
			$this->data['name'] = '';
		}

		$this->children = array(
           'common/header',
           'common/footer',
        );
		$this->template = 'wkpos/user_group_form.tpl';
		$this->response->setOutput($this->render(TRUE));
		
		/*$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('wkpos/user_group_form.tpl', $data));*/
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'wkpos/user_permission')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'wkpos/user_permission')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->load->model('wkpos/user');

		foreach ($this->request->post['selected'] as $user_group_id) {
			$user_total = $this->model_wkpos_user->getTotalUsersByGroupId($user_group_id);

			if ($user_total) {
				$this->error['warning'] = sprintf($this->language->get('error_user'), $user_total);
			}
		}

		return !$this->error;
	}
}
