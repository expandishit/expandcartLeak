<?php

class ControllerMarketingNetworkAgencies extends Controller
{
    private $settings, $agencies, $downlines;

    public function init($models)
    {
        // TODO modularize this.
        foreach ($models as $model) {

            $this->load->model($model);

            $object = explode('/', $model);
            $object = end($object);

            $model = str_replace('/', '_', $model);

            $this->$object = $this->{"model_" . $model};
        }

        $this->language->load_json('network_marketing/global');

        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('marketing/network/agencies', '', 'SSL');

            $this->redirect($this->url->link('account/login', '', 'SSL'));
        }

        if (isset($this->session->data['errors'])) {
            $this->data['error_warning'] = implode('<br />', $this->session->data['errors']);

            unset($this->session->data['errors']);
        }
    }

    public function index()
    {
        $this->init([
            'network_marketing/settings',
            'network_marketing/agencies',
        ]);

        $settings = $this->settings->getSettings();

        $this->data['agencies'] = $this->agencies->getCustomerAgencies(
            $this->customer->getId()
        );

        $this->data['subAgencies'] = $this->agencies->getCustomerSubAgencies(
            $this->customer->getId()
        );

        $this->data['settings'] = $settings;

        $template = 'network_marketing/browse.expand';
        $customTemplate = DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' .
            $this->config->get('config_template') . '/template/' . $template;
        if(file_exists($customTemplate)) {
            $this->template = 'customtemplates/' . STORECODE . '/' .
                $this->config->get('config_template') . '/template/' . $template;
        } else {
            $this->template = 'default/template/' . $template;
        }

        $this->response->setOutput($this->render_ecwig());
    }

    public function newAgency()
    {
        $this->init([
            'network_marketing/settings',
            'network_marketing/agencies',
        ]);

        $settings = $this->settings->getSettings();

        if ($settings['minimum_points'] > 0) {
            if ($settings['minimum_points'] >= $this->customer->getRewardPoints()) {
                $this->session->data['errors'][] = $this->language->get('error_not_enough_points');

                $this->redirect($this->url->link('marketing/network/agencies', '', 'SSL'));
            }
        }

        if(strpos($this->request->server['HTTP_REFERER'], $this->request->server['HTTP_HOST']) === false) {
            $this->session->data['errors'][] = $this->language->get('error_unauthorized');

            $this->redirect($this->url->link('marketing/network/agencies', '', 'SSL'));
        }

        $agencies = $this->agencies->getCustomerSubAgencies(
            $this->customer->getId()
        );

        if ($settings['max_agencies'] > 0) {
            if ($agencies['count'] >= $settings['max_agencies']) {
                $this->session->data['errors'][] = $this->language->get('error_maximum_agencies');

                $this->redirect($this->url->link('marketing/network/agencies', '', 'SSL'));
            }
        }

        $this->agencies->newAgency(
            $this->customer->getId(),
            $this->agencies->genereateRefId($this->customer->getId())
        );

        $this->session->data['success'] = $this->language->get('success_new_agency');

        $this->redirect($this->url->link('marketing/network/agencies', '', 'SSL'));
    }

    public function newSubAgency()
    {
        $this->init([
            'network_marketing/settings',
            'network_marketing/agencies',
        ]);

        $settings = $this->settings->getSettings();

        if ($settings['minimum_points'] > 0) {
            if ($settings['minimum_points'] >= $this->customer->getRewardPoints()) {
                $this->session->data['errors'][] = $this->language->get('error_not_enough_points');

                $this->redirect($this->url->link('marketing/network/agencies', '', 'SSL'));
            }
        }

        if(strpos($this->request->server['HTTP_REFERER'], $this->request->server['HTTP_HOST']) === false) {
            $this->session->data['errors'][] = $this->language->get('error_unauthorized');

            $this->redirect($this->url->link('marketing/network/agencies', '', 'SSL'));
        }

        $parentId = (isset($this->request->get['p']) ? $this->request->get['p'] : null);

        $parentAgency = $this->agencies->getCustomerSubAgency(
            $parentId,
            $this->customer->getId()
        );

        $this->agencies->newSubAgency(
            $this->customer->getId(),
            $this->agencies->genereateRefId($this->customer->getId()),
            $parentId,
            ($parentAgency['level'] + 1)
        );

        $this->redirect($this->url->link('marketing/network/agencies', '', 'SSL'));
    }

    public function downline()
    {
        $agencyId = (isset($this->request->get['agency_id']) ? $this->request->get['agency_id'] : null);

        $this->init([
            'network_marketing/downlines',
            'network_marketing/agencies'
        ]);

        $downLine = $this->downlines->generateDownline(
            $this->downlines->getReferrals($agencyId, $this->customer->getId())
        );

        $top_agency = $this->agencies->getAgencyById($agencyId);

        $downLine = [
            'name' => $top_agency['firstname'] . ' ' . $top_agency['lastname'],
            'title' => $top_agency['ref_id'],
            'children' => $downLine
        ];

        $upLine = $this->downlines->generateUpline($downLine['data']);

        $this->init([
            'network_marketing/settings',
            'network_marketing/agencies',
        ]);

        $this->data['upLine'] = $upLine;
        $this->data['downLine'] = json_encode($downLine);

        $template = 'network_marketing/downline.expand';

        $customTemplate = DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' .
            $this->config->get('config_template') . '/template/' . $template;

        if(file_exists($customTemplate)) {
            $this->template = 'customtemplates/' . STORECODE . '/' .
                $this->config->get('config_template') . '/template/' . $template;
        } else {
            $this->template = 'default/template/' . $template;
        }

        $this->response->setOutput($this->render_ecwig());
    }
}
