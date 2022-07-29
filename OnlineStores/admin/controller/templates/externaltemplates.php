<?php

class ControllerTemplatesExternalTemplates extends Controller
{
    public $baseDir = DIR_CUSTOM_TEMPLATE;
    public $tmpDir = TEMP_DIR_PATH;
    public function import()
    {
        $this->language->load('templates/customize');

        $this->document->setTitle($this->language->get('heading_title'));

        if (isset($this->request->post['template_id']) == false) {
            throw new \Exception('Invalid template');
        }

        $templateId = $this->request->post['template_id'];

        $this->initializer([
            'templates/external',
            'templates/template',
        ]);

        $template = $this->external->getTemplateById($templateId);

        if (!$template) {
            throw new \Exception('Template not found');
        }

        $code = $template['CodeName'];
        $theme = $template['CodeName'] . '.zip';

        $internalTemplate = $this->template->getTemplateByConfigName($code);

        if ($internalTemplate) {
            $this->response->setOutput(json_encode([
                'status' => 'OK',
                'isExists' => 'true',
                'message' => [
                    'file has been uploaded'
                ],
                'template' => ['name' => $internalTemplate['CodeName'], 'id' => $internalTemplate['id']],
                'base' => $code
            ]));
            return;
        }

        if (file_exists($this->baseDir) == false || is_writable($this->baseDir) == false) {
            mkdir($this->baseDir, 0777);
            chmod($this->baseDir, 0777);
        }

        if (file_exists($this->tmpDir) == false) {
            mkdir($this->tmpDir, 0777);
            chmod($this->tmpDir, 0777);
        }

        if (!file_exists(EXTERNAL_THEMES_PATH . $theme)) {
            throw new \Exception('undefined file');
        }

        copy(EXTERNAL_THEMES_PATH . $theme, TEMP_DIR_PATH . $theme);

        $this->session->data['eti'] = $templateId;

        $this->response->setOutput(json_encode([
            'status' => 'OK',
            'message' => [
                'file has been uploaded'
            ],
            'template' => ['name' => $theme],
            'base' => $code
        ]));
        return;
    }

    public function unlinkTheme()
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

        $this->initializer([
            'templates/external',
            'templates/template',
            'templates/applier',
            'importer' => 'templates/import',
        ]);

        $templateId = $this->request->post['template_id'];

        $internalTemplate = $this->template->getTemplateById($templateId);

        if (!$internalTemplate) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNDEFINED_ROW',
                'errors' => [
                    $this->language->get('undefined_row')
                ],
            ]));
            return;
        }

        $this->template->destroyTemplate($internalTemplate['id']);

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

        $baseFile = $internalTemplate['CodeName'];

        $this->applier->construct($baseFile, $this->db, $this->baseDir);

        $this->applier->deletePages($baseFile);
        $this->applier->deleteObjectField($baseFile);

        $this->template->deleteTemplateByCode($baseFile);

        $this->importer->remove($this->template->baseDir . $baseFile);

        $this->response->setOutput(json_encode([
            'status' => 'OK',
            'message' => 'success',
        ]));
        return;
    }
}
