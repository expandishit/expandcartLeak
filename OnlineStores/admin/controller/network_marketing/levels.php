<?php

class ControllerNetworkMarketingLevels extends Controller
{

    private $settings, $levels;

    protected $data = [];

    private function init()
    {
        $this->load->model('module/network_marketing/settings');
        $this->load->model('module/network_marketing/levels');

        $this->settings = $this->model_module_network_marketing_settings;
        $this->levels = $this->model_module_network_marketing_levels;

        if (isset($this->session->data['errors'])) {
            $this->data['error_warning'] = implode('</br >', $this->session->data['errors']);

            unset($this->session->data['errors']);
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        }

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->language->load('module/network_marketing');
    }

    public function index()
    {
        $this->init();

        $this->document->setTitle($this->language->get('network_marketing_heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link(
                'marketplace/home', 'token=' . $this->session->data['token'] . '&type=apps', 'SSL'
            ),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('network_marketing_heading_title'),
            'href'      => $this->url->link('module/network_marketing', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['links'] = [
            'submit' => $this->url->link(
                'network_marketing/levels/updateLevels',
                'token=' . $this->session->data['token'],
                'SSL'
            ),
            'cancel' => $this->url->link('module/network_marketing', 'token=' . $this->session->data['token'], 'SSL'),
        ];

        $levels = $this->levels->getLevels();

        $this->data['currentCount'] = $this->levels->numRows;

        $this->data['levels'] = $levels;
        $this->data['settings'] = $this->settings->getSettings();

        $this->template = 'module/network_marketing/levels.tpl';

        $this->response->setOutput($this->render());
    }

    public function updateLevels()
    {
        if (
            $this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)
        ) {
            $this->base = "common/base";
            $this->data = $this->language->load('error/permission');
            $this->document->setTitle($this->language->get('heading_title'));
            $this->template = 'error/permission.expand';
            $this->response->setOutput($this->render_ecwig());
            return;
        }

        $this->init();

        $levels = $this->request->post['levels'];

        if (!$this->levels->validateLevels($levels['levels'])) {

            $this->session->data['errors'] = $this->levels->getErrors();

            $this->redirect(
                $this->url->link(
                    'network_marketing/levels',
                    'token=' . $this->session->data['token'], 'SSL'
                )
            );
        }

        $this->settings->updateSettings([
            'network_marketing' => array_merge($this->settings->getSettings(), $levels['settings'])
        ]);

//        $this->levels->truncateLevels();

        $this->levels->insertOrUpdate($levels['levels']);

        $this->redirect(
            $this->url->link(
                'network_marketing/levels',
                'token=' . $this->session->data['token'], 'SSL'
            )
        );
    }
}
