<?php

class ControllerMultisellerSubscriptions extends ControllerMultisellerBase
{
    private $model;

    private function init()
    {
        $this->load->model('multiseller/subscriptions');

        $this->model = $this->model_multiseller_subscriptions;

        if (isset($this->session->data['errors'])) {
            $this->data['error_warning'] = implode('</br >', $this->session->data['errors']);

            unset($this->session->data['errors']);
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        }

        if ($this->config->get('msconf_enable_subscriptions_plans_system') === '0') {
            $this->session->data['error'] = $this->language->get('ms_config_disabled_subscription_system');
            $this->redirect(
                $this->url->link(
                    'module/multiseller',
                    'token=' . $this->session->data['token'],
                    'SSL'
                )
            );
        }
    }

    public function index()
    {
        $this->init();

        $this->document->setTitle($this->language->get('ms_config_subscriptions_plans'));

        $data = [];

        $data['plans'] = $this->model->getList();

        $data['createPlan'] = $this->url->link(
            'multiseller/subscriptions/create',
            'token=' . $this->session->data['token'],
            'SSL'
        );

        $data['refreshLink'] = $this->url->link(
            'multiseller/subscriptions',
            'token=' . $this->session->data['token'],
            'SSL'
        );

        $data['editLink'] = $this->url->link(
            'multiseller/subscriptions/edit',
            'token=' . $this->session->data['token'],
            'SSL'
        );

        $data['deleteLink'] = $this->url->link(
            'multiseller/subscriptions/delete',
            'token=' . $this->session->data['token'],
            'SSL'
        );


        $this->template = 'multiseller/subscriptions/browse.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }

    public function create()
    {
        $this->init();

        $this->load->model('localisation/language');

        $this->document->setTitle(
            $this->language->get('ms_config_subscriptions_plans') . ' - ' .
            $this->language->get('ms_config_subscriptions_new_plan')
        );

        $data = [];

        $data['languages'] = $this->model_localisation_language->getLanguages();

        $data['links']['submit'] = $this->url->link(
            'multiseller/subscriptions/submitForm',
            '',
            'SSL'
        );

        $data['actionType'] = 'create';


        $this->template = 'multiseller/subscriptions/form.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );


        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }

    public function edit()
    {
        if (
            !isset($this->request->get['plan_id'])
            || $this->request->get['plan_id']  == ''
        ) {
            // TODO handle errors messages
            $this->redirect(
                $this->url->link(
                    'multiseller/subscriptions',
                    'token=' . $this->session->data['token'],
                    'SSL'
                )
            );
        }

        $id = $this->request->get['plan_id'];

        $this->init();

        $this->load->model('localisation/language');

        $this->document->setTitle(
            $this->language->get('ms_config_subscriptions_plans') . ' - ' .
            $this->language->get('ms_config_subscriptions_edit_plan')
        );

        $data = [];

        if (!$plan = $this->model->getPlan($id)) {
            // TODO handle errors messages
            $this->redirect(
                $this->url->link(
                    'multiseller/subscriptions',
                    'token=' . $this->session->data['token'],
                    'SSL'
                )
            );
        }

        $data['plan'] = $plan;

        $data['languages'] = $this->model_localisation_language->getLanguages();

        $data['links']['submit'] = $this->url->link(
            'multiseller/subscriptions/submitForm',
            'plan_id=' . $id,
            'SSL'
        );

        $data['actionType'] = 'update';


        $this->template = 'multiseller/subscriptions/form.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }

    public function submitForm()
    {
        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

            $this->init();

            $data = $this->request->post['ms_plan'];

            if (!isset($data['plans']['status'])) {
                $data['plans']['status'] = '0';
            }

            if (!$this->model->validate($data)) {

                if (count($this->model->errors) > 0) {
                    $result_json['success'] = '0';
                    $result_json['errors'] = $this->model->errors;
                    $this->response->setOutput(json_encode($result_json));
                    return;
                }

            }

            if (isset($data['actionType'])) {
                if ($data['actionType'] == 'create') {
                    $id = $this->model->newPlan($data['plans']);
                    $this->model->newPlanDescription($data['details'], $id);

                    $result_json['success'] = '1';
                    $result_json['success_msg'] =  $this->language->get('ms_messages_inserted_success');
                    $result_json['redirect'] = '1';
                    $result_json['to'] = $this->url->link(
                        'multiseller/subscriptions/edit',
                        'plan_id=' . $id,
                        'SSL'
                    )->format();
                    $this->response->setOutput(json_encode($result_json));
                    return;
                    /*
                    // TODO handle success messages
                    $this->redirect(
                        $this->url->link(
                            'multiseller/subscriptions',
                            'token=' . $this->session->data['token'],
                            'SSL'
                        )
                    );
                    */
                } else if (
                    $data['actionType'] == 'update'
                    && isset($this->request->get['plan_id'])
                ) {
                    $id = $this->request->get['plan_id'];
                    $this->model->updatePlan($id, $data['plans']);
                    $this->model->updatePlanDescription($id, $data['details']);

                    $result_json['success'] = '1';
                    $result_json['success_msg'] =  $this->language->get('ms_messages_updated_success');
                    $this->response->setOutput(json_encode($result_json));
                    return;

                }
            } else {
                $this->redirect(
                    $this->url->link(
                        'multiseller/subscriptions',
                        'token=' . $this->session->data['token'],
                        'SSL'
                    )
                );

            }
        } else {
            $this->redirect(
                $this->url->link(
                    'multiseller/subscriptions',
                    'token=' . $this->session->data['token'],
                    'SSL'
                )
            );
        }

    }

    public function delete()
    {
        if (
            !isset($this->request->get['plan_id'])
            || $this->request->get['plan_id']  == ''
        ) {
            // TODO handle errors messages
            $this->redirect(
                $this->url->link(
                    'multiseller/subscriptions',
                    'token=' . $this->session->data['token'],
                    'SSL'
                )
            );
        }

        $id = $this->request->get['plan_id'];

        $this->init();

        $this->model->deletePlan($id);

        // TODO handle success messages
        $this->redirect(
            $this->url->link(
                'multiseller/subscriptions',
                'token=' . $this->session->data['token'],
                'SSL'
            )
        );
    }

    public function dtDelete()
    {
        if (
            !isset($this->request->post['selected'])
            || count($this->request->post['selected']) < 1
        ) {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = 'Invalid Ids';

            $this->response->setOutput(json_encode($response));
            return;
        }

        if (isset($this->request->post['selected'])) {

            $this->init();

            foreach ($this->request->post['selected'] as $id) {
                $this->model->deletePlan($id);
            }

            $response['status'] = 'success';
            $response['title'] = $this->language->get('notification_success_title');
            $response['message'] = $this->language->get('message_deleted_successfully');
        } else {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = '';
        }

        $this->response->setOutput(json_encode($response));
        return;
    }
}
