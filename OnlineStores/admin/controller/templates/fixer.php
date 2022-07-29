<?php

class ControllerTemplatesFixer extends Controller
{
    public function index()
    {
        $this->initializer([
            'templates/template',
            'fixer' => 'templates/fixer',
            'templates/external',
        ]);

        $templateId = 0;

        if (isset($this->request->get['template_id']) && preg_match('#^[0-9]+$#', $this->request->get['template_id']) == true) {
            $templateId = (int)$this->request->get['template_id'];
        }

        if (!$templateId) {
            $templateByCode = $this->template->getTemplateByConfigName($this->config->get('config_template'));

            if (isset($templateByCode['id']) != false) {
                $templateId = $templateByCode['id'];
            }
        }

        $template = $this->template->getTemplateById($templateId);

        if (!$template) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNDEFINED_ROWS',
                'errors' => ['Template is not defined']
            ]));
            return;
        }

        if ($template['custom_template'] != 1) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNDEFINED_ROWS',
                'errors' => ['Template is not custom template']
            ]));
            return;
        }

        $currentTheme = rtrim($this->fixer->baseDir, '/') . '/' . $template['CodeName'];
        $currentSchema = $currentTheme . '/' . $template['CodeName'] . '.json';

        if (!file_exists($currentSchema)) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNAUTHORIZED_ACTION',
                'errors' => ['Schema file is missed']
            ]));
            return;
        }

        $templateContents = json_decode(file_get_contents($currentSchema), true);

        $this->fixer->construct($template, $this->db, $this->fixer->baseDir);

        $this->fixer->setTemplateObject($templateContents);

        $this->fixer->setLocals($templateContents['locales']);

        $this->fixer->applySettings();

        $this->fixer->compileSchema();

        $this->fixer->fix();

        $this->response->setOutput(json_encode([
            'status' => 'OK',
        ]));
        return;
    }
}
