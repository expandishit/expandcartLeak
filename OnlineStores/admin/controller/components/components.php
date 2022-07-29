<?php
    class ControllerComponentsComponents extends Controller {
        public function index() {
            $this->data['breadcrumbs'] = array();
            $this->data['breadcrumbs'][] = array(
                'text'      => $this->language->get('text_home'),
             'href'      => $this->url->link('common/home', '', 'SSL'),
               'separator' => false
            );
            $this->data['breadcrumbs'][] = array(
                'text'      => $this->language->get('heading_title'),
                'href'      => false,
                'separator' => false
            );
            $this->template = 'components/components.expand';
            $this->base = 'common/base';
            $this->response->setOutput($this->render_ecwig());
        }
    }
?>