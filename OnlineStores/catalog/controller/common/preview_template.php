<?php

class ControllerCommonPreviewTemplate extends Controller
{
    public function index()
    {
        $codeName = $this->request->get['__p'];

        if (!isset($codeName)) {
            return false;
        }

        if (!isset($codeName) || strlen($codeName) < 1) {
            return false;
        }

        $this->load->library('user');

        $this->user = new User($this->registry);

        if (!$this->user->isLogged()) {
            return false;
        }

        $this->session->data['preview_template'] = $codeName;

        $this->initializer([
            'templates/template',
        ]);

        $template = $this->template->getTemplateByConfigName($codeName);

        if (!$template) {
            return false;
        }

        $this->config->set('config_template', $codeName);
        $this->config->set('tmp_prev', $codeName);
    }
}
