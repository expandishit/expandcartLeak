<?php

use ExpandCart\Foundation\Analytics\Live;
use ExpandCart\Foundation\Analytics\VisitTime;

class ControllerAnalyticsAnalytics extends Controller
{
    private $plan_id = PRODUCTID;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('plan/trial');
        $trial = $this->model_plan_trial->getLastTrial();
        if ($trial){
            $this->plan_id = $trial['plan_id'];
        }

        if($this->plan_id == 3){
            $this->redirect(
                $this->url->link('error/not_found', '', 'SSL')
            );
            exit();
        }
    }
    public function index()
    {
        $this->language->load('analytics/browse');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('api/clients/browse', '', 'SSL'),
            'separator' => ' :: '
        );

        /*$VisitTimeGetByDayOfWeek = (new VisitTime())->setMethod('getByDayOfWeek')->send();

        $this->data['VisitTimeGetByDayOfWeek'] = $VisitTimeGetByDayOfWeek;

        $getLastVisitsDetails = (new Live())->setMethod('getLastVisitsDetails')->send();

        $this->data['getLastVisitsDetails'] = $getLastVisitsDetails;*/

        $this->template = 'analytics/browse.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function visitorProfile()
    {
        $this->language->load('analytics/visitorprofile');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );
//        $this->data['breadcrumbs'][] = array(
//            'text' => $this->language->get('analytics_stats'),
//            'href' => $this->url->link('analytics/analytics', '', 'SSL'),
//            'separator' => ' :: '
//        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('api/clients/browse', '', 'SSL'),
            'separator' => ' :: '
        );

        $visitorId = $this->request->get['visitorId'];

        $getVisitorProfile = (new Live())->setMethod('getVisitorProfile')->setVisitorId($visitorId)->fetch();

        $this->data['visit'] = $getVisitorProfile;

        $this->template = 'analytics/visitor_profile.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render_ecwig());
    }
}
