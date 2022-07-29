<?php

class ControllerAliexpressManagerAccount extends ControllerAliexpressBase
{
    public function index()
    {
        $this->load->language('aliexpress/account');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->initializer([
            'userModel' => 'user/user',
            'aliexpress/manager',
        ]);

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', '', 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('aliexpress/manager_account', '', 'SSL')
        );

        if (isset($this->session->data['user_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $user_info = $this->userModel->getUser($this->session->data['user_id']);
            
            /**
             * [$position_info warehouse position information]
             * @var [array]
             */
            $position_info = $this->manager->getPosition($this->session->data['user_id']);
        }

        $data['userid'] = $this->session->data['user_id'];
        $data['username'] = '';
        $data['firstname'] = '';
        $data['lastname'] = '';
        $data['email'] = '';
        $data['longitude'] = '';
        $data['latitude'] = '';
        $data['password'] = '';
        $data['confirm'] = '';

        //Data sending
        if (!empty($user_info)) {
            $data['username'] = $user_info['username'];
            $data['firstname'] = $user_info['firstname'];
            $data['lastname'] = $user_info['lastname'];
            $data['email'] = $user_info['email'];
        }

        if (!empty($position_info)) {
            $data['longitude'] = $position_info['longitude'];
            $data['latitude'] = $position_info['latitude'];
        }

        $this->template = 'aliexpress/manager/account.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->data = $data;

        $this->response->setOutput($this->render_ecwig());
    }

    public function updateAccount()
    {
        if (
            $this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)
        ) {
            $this->response->setOutput(json_encode(['errors' => true]));

            return;
        }

        $userId = $this->session->data['user_id'];

        if ($userId == 999999999) {
            return $this->response->setOutput(json_encode([
                'success' => '0',
                'errors' => ['error' => $this->language->get('user_is_not_editable')]
            ]));
        }

        $this->initializer([
            'aliexpress/manager'
        ]);

        $this->manager->editUser($userId, $this->request->post);
        $this->manager->editPosition($userId, $this->request->post);

        $response['success_msg'] = $this->language->get('text_settings_success');
        $response['success'] = '1';

        $this->response->setOutput(json_encode($response));
    }
}
