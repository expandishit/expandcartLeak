<?php
//exit();die();
class ControllerTemplatesTemplate extends Controller
{
    public function apply()
    {
        if (
            isset(getallheaders()['X-Source-Ajax']) == false ||
            getallheaders()['X-Source-Ajax'] != 'true'
        ) {
            $this->response->setOutput(json_encode([
                'status' => 'error',
                'errors' => [
                    $this->language->get('corrupted_request')
                ],
            ]));
            return;

        }

        if (
            isset($this->request->post['template_id']) == false ||
            ((int)$this->request->post['template_id'] <= 0)
        ) {
            $this->response->setOutput(json_encode([
                'status' => 'error',
                'errors' => [
                    $this->language->get('invalid_request')
                ],
            ]));
            return;
        }

        $templateId = $this->request->post['template_id'];
        $templateType = 'uploaded';
        if (
            isset($this->request->post['template_type']) &&
            in_array($this->request->post['template_type'], ['uploaded', 'external'])
        ) {
            $templateType = $this->request->post['template_type'];
        }

        $this->initializer([
            'templates/template',
            'importer' => 'templates/import',
        ]);


        if ($templateType === 'external') {
            $template = $this->template->getTemplateByExternalTemplateId($templateId);
        } else {
            $template = $this->template->getTemplateById($templateId);
        }

        if (!$template || !$template['id'] || !$template['CodeName']) {
            $this->response->setOutput(json_encode([
                'status' => 'error',
                'errors' => [
                    $this->language->get('invalid_template')
                ],
            ]));
            return;
        }

        if ( $template['CodeName'] != 'wonder' &&
            (PRODUCTID =="3" || PRODUCTID =="2" ) &&
            ( $this->config->get('template_version') > 1.2 )
        ){
            $this->response->setOutput(json_encode([
                'status' => 'error',
                'errors' => [
                    $this->language->get('not_allowed')
                ],
            ]));
            return;
        }

        $ecpages = $this->template->getTemplateEcPagesByTemplateId($template['id']);

        if (is_array($ecpages) && $this->config->get('config_template') != $template['CodeName']) {

            $this->markGuideStepAsCompleted();
            $setting = $this->load->model('setting/setting', ['return' => true]);
            $setting->editSettingValue('config', 'config_template', $template['CodeName']);
            $this->response->setOutput(json_encode([
                'status' => 'success',
                'refresh'   => '1',
                'success' => true
            ]));
            return;
        }

        if ($this->template->applyTemplate($template)) {

            $this->markGuideStepAsCompleted();
            $this->response->setOutput(json_encode([
                'status' => 'success',
                'refresh'   => '1',
                'success' => true
            ]));
            return;
        }

        $this->response->setOutput(json_encode([
            'status' => 'error',
            'errors' => [
                $this->language->get('unexcpected_error')
            ],
        ]));
        return;
    }

    public function remove()
    {
        if (
            isset(getallheaders()['X-Source-Ajax']) == false ||
            getallheaders()['X-Source-Ajax'] != 'true'
        ) {
            $this->response->setOutput(json_encode([
                'status' => 'error',
                'errors' => [
                    $this->language->get('corrupted_request')
                ],
            ]));
            return;
        }

        if (
            isset($this->request->get['template_id']) &&
            ((int)$this->request->get['template_id']) <= 0
        ) {
            $this->response->setOutput(json_encode([
                'status' => 'error',
                'errors' => [
                    $this->language->get('invalid_request')
                ],
            ]));
            return;
        }

        $templateId = $this->request->get['template_id'];

        $this->initializer([
            'templates/template',
            'importer' => 'templates/import',
            'templates/applier',
        ]);

        $template = $this->template->getTemplateById($templateId);

        if (!$template || !$template['id'] || !$template['CodeName']) {
            $this->response->setOutput(json_encode([
                'status' => 'error',
                'errors' => [
                    $this->language->get('invalid_template')
                ],
            ]));
            return;
        }

        if ($template['CodeName'] == $this->config->get('config_template')) {
            $this->response->setOutput(json_encode([
                'status' => 'error',
                'errors' => [
                    $this->language->get('can_not_delete_applied_template')
                ],
            ]));
            return;
        }

        if ($this->importer->remove($this->template->baseDir . $template['CodeName'])) {

            $this->template->destroyTemplate($template['id']);

            $this->applier->construct($template['CodeName'], $this->db, $this->template->baseDir);

            $this->applier->deletePages($template['CodeName']);
            $this->applier->deleteObjectField($template['CodeName']);

            $dirTemplate = DIR_TEMPLATE;
            if (IS_CUSTOM_TEMPLATE && !IS_ADMIN) {
                $dirTemplate = DIR_CUSTOM_TEMPLATE;
            }

            $cacheFilesDir = rtrim($dirTemplate, '/') . '/cache/*';
            $cacheFiles = glob($cacheFilesDir);
            foreach ($cacheFiles as $cacheFile) {
                if (is_file($cacheFile)) {
                    unlink($cacheFile);
                }
            }

            $this->response->setOutput(json_encode([
                'status' => 'success',
            ]));
            return;
        }

        $this->response->setOutput(json_encode([
            'status' => 'error',
            'errors' => [
                $this->language->get('unexcpected_error')
            ],
        ]));
        return;
    }

    /**
     * Get current applied template
     */
    public function getCurrentTemplate(){
        $this->load->model('templates/template');
        $this->response->setOutput(json_encode($this->model_templates_template->getCurrentTemplate()));
    }
    
    private function markGuideStepAsCompleted()
    {
        $this->tracking->updateGuideValue("CUST_DESIGN");
    }
    
    
}
