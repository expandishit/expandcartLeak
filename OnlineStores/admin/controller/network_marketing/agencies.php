<?php

class ControllerNetworkMarketingAgencies extends Controller
{

    private $settings, $agencies;

    protected $data = [];

    private function init($models)
    {
        // TODO modularize this.
        foreach ($models as $model) {

            $this->load->model($model);

            $object = explode('/', $model);
            $object = end($object);

            $model = str_replace('/', '_', $model);

            $this->$object = $this->{"model_" . $model};
        }

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
        $this->init([
            'module/network_marketing/agencies'
        ]);

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
            'href'      => $this->url->link(
                'module/network_marketing',
                'token=' . $this->session->data['token'],
                'SSL'
            ),
            'separator' => ' :: '
        );

        $this->data['links'] = [
            'submit' => $this->url->link(
                'network_marketing/levels/updateLevels',
                'token=' . $this->session->data['token'],
                'SSL'
            ),
            'cancel' => $this->url->link('module/network_marketing', 'token=' . $this->session->data['token'], 'SSL'),
            'viewAgency' => $this->url->link(
                'network_marketing/agencies/view', 'token=' . $this->session->data['token'], 'SSL'
            ),
            'downline' => $this->url->link(
                'network_marketing/agencies/downline', 'token=' . $this->session->data['token'], 'SSL'
            ),
        ];

        $this->data['agencies'] = $this->agencies->listAgencies();

        $this->template = 'module/network_marketing/agencies.tpl';

        $this->response->setOutput($this->render());
    }

    public function view()
    {

        $agencyId = (isset($this->request->get['agency_id']) ? $this->request->get['agency_id'] : null);

        if (!$agencyId) {
            $this->session->data['errors'] = $this->language->get('invalid_agency_id');

            $this->redirect(
                $this->url->link(
                    'network_marketing/levels',
                    'token=' . $this->session->data['token'], 'SSL'
                )
            );
        }

        $this->init([
            'module/network_marketing/agencies'
        ]);

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
            'href'      => $this->url->link(
                'module/network_marketing',
                'token=' . $this->session->data['token'],
                'SSL'
            ),
            'separator' => ' :: '
        );

        $this->data['links'] = [
            'submit' => $this->url->link(
                'network_marketing/levels/updateLevels',
                'token=' . $this->session->data['token'],
                'SSL'
            ),
            'cancel' => $this->url->link('network_marketing/agencies', 'token=' . $this->session->data['token'], 'SSL'),
            'viewAgency' => $this->url->link(
                'network_marketing/agencies/view', 'token=' . $this->session->data['token'], 'SSL'
            ),
            'downline' => $this->url->link(
                'network_marketing/agencies/downline', 'token=' . $this->session->data['token'], 'SSL'
            ),
        ];

        $this->data['agency'] = $this->agencies->viewAgency($agencyId);

        $this->data['subAgencies'] = $this->agencies->listAgencies($agencyId);

        if ($this->data['agency']['parent'] > 0) {
            $this->data['links']['cancel'] = $this->url->link(
                'network_marketing/agencies/view',
                'token=' . $this->session->data['token'] . '&agency_id=' . $this->data['agency']['parent'],
                'SSL'
            );
        }

        $this->data['agencyId'] = $agencyId;

        $this->template = 'module/network_marketing/view_agency.tpl';

        $this->response->setOutput($this->render());
    }

    public function downline()
    {
        $agencyId = (isset($this->request->get['agency_id']) ? $this->request->get['agency_id'] : null);

        if (!$agencyId) {
            $this->session->data['errors'] = $this->language->get('invalid_agency_id');

            $this->redirect(
                $this->url->link(
                    'network_marketing/levels',
                    'token=' . $this->session->data['token'], 'SSL'
                )
            );
        }

        $this->init([
            'module/network_marketing/agencies',
            'module/network_marketing/downlines'
        ]);

        $agency = $this->agencies->viewAgency($agencyId);

        $downLine = $this->downlines->generateDownline(
            $this->downlines->getReferrals($agencyId, $agency['customer_id'])
        );
        echo '<pre>';print_r($downLine);exit;

        $upLine = $this->downlines->generateUpline($downLine['data']);
    }
}
