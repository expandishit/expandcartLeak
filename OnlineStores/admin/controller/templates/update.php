<?php

class ControllerTemplatesUpdate extends Controller
{
    public function index()
    {
        $this->initializer([
            'templates/template',
            'importer' => 'templates/import',
            'updater' => 'templates/updater',
            'templates/external',
        ]);

        // Set to maintenance mode

        $templateId = $this->request->post['template_id'];
        $forceUpdateTemplate = (int)($this->request->post['force_update'] ? : 0);

        $template = $this->template->getTemplateById($templateId);

        if (!$template) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNDEFINED_ROWS',
                'errors' => ['Template is not defined']
            ]));
            return;
        }

        $externalTemplate = $this->external->getTemplateByCode($template['CodeName']);

        if (!$externalTemplate) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNDEFINED_ROWS',
                'errors' => ['Template is not defined']
            ]));
            return;
        }

        $themeVersion = '1.0';
        if (isset($template['theme_version']) && (float)$template['theme_version'] > 0) {
            $themeVersion = $template['theme_version'];
        }

        if ($themeVersion >= $externalTemplate['theme_version'] && !$forceUpdateTemplate) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNAUTHORIZED_ACTION',
                'errors' => ['Template is already updated']
            ]));
            return;
        }

        $currentTheme = rtrim($this->updater->baseDir, '/') . '/' . $template['CodeName'];
        $currentSchema = $currentTheme . '/' . $template['CodeName'] . '.json';
        $externalThemePath = EXTERNAL_THEMES_PATH . $template['CodeName'] . '.zip';
        $zip = new \ZipArchive();
        $zip->open($externalThemePath);
        $zip->extractTo($this->updater->tmpDir);
        $zip->close();

        $tmpThemePath = rtrim($this->updater->tmpDir, '/') . '/' . $template['CodeName'];
        $tmpSchema = $tmpThemePath . '/' . 'schema.json';

        if (!file_exists($tmpSchema)) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNAUTHORIZED_ACTION',
                'errors' => ['Schema file is missed']
            ]));
            return;
        }

        exec(sprintf('diff -w %s %s', $currentSchema, $tmpSchema), $diff);

        $templateContents = json_decode(file_get_contents($tmpSchema), true);

        if (count($diff) > 0 || 1==1) {

            if (file_exists('/var/www/ectools/artisan')) {
                // this script run on server only
                exec('php /var/www/ectools/artisan db:snapshot ' . STORECODE);
            }

            $this->updater->construct($template, $this->db, $this->updater->baseDir);

            $this->updater->setTemplateObject($templateContents);

            $this->updater->setLocals($templateContents['locales']);

            //$this->updater->setLanguages($this->dashboardLanguages);

            $this->updater->setOptions($templateContents['options']);

            $this->updater->applySettings();

            $this->updater->compileSchema();

            $this->updater->update();
        }

        $this->template->updateTemplateVersion($template['id'], $externalTemplate['theme_version']);

        // TODO compress moved current theme instead of removing it
        // TODO move current theme files from the current path to temp path with unique name

        $this->importer->remove($currentTheme);

        // move $tmpThemePath to base themes dir
        $this->importer->mv($tmpThemePath, $currentTheme);

        $this->importer->mv($currentTheme . '/' . 'schema.json', $currentTheme . '/' . $template['CodeName'] . '.json');

        $this->importer->remove($tmpThemePath);

        $cacheDir = rtrim($this->updater->baseDir, '/') . '/cache';

        if (is_dir($cacheDir)) {
            exec(sprintf('rm %s -R', $cacheDir));
        }

        $this->response->setOutput(json_encode([
            'status' => 'OK',
        ]));
        return;

        // End maintenance mode
    }

    public function upload()
    {
        $this->initializer([
            'templates/template',
            'importer' => 'templates/import',
            'updater' => 'templates/updater',
            'templates/external',
        ]);

        $templateCode = $this->request->post['code'];

        $template = $this->template->getTemplateByConfigName($templateCode);

        if (!$template) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNDEFINED_ROWS',
                'errors' => ['Template is not defined']
            ]));
            return;
        }

        $themeVersion = '1.0';
        if (isset($template['theme_version']) && (float)$template['theme_version'] > 0) {
            $themeVersion = $template['theme_version'];
        }

        $this->importer->upload($_FILES['template'], $this->updater->tmpDir);

        $archiveFile = $this->updater->tmpDir . $template['CodeName'] . '.zip';

        $currentTheme = rtrim($this->updater->baseDir, '/') . '/' . $template['CodeName'];
        $currentSchema = $currentTheme . '/' . $template['CodeName'] . '.json';
        $zip = new \ZipArchive();
        $zip->open($archiveFile);
        $zip->extractTo($this->updater->tmpDir);
        $zip->close();

        $tmpThemePath = rtrim($this->updater->tmpDir, '/') . '/' . $template['CodeName'];
        $tmpSchema = $tmpThemePath . '/' . 'schema.json';

        if (!file_exists($tmpSchema)) {
            $this->importer->remove($tmpThemePath);
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNAUTHORIZED_ACTION',
                'errors' => ['Schema file is missed']
            ]));
            return;
        }

        if (file_exists($archiveFile)) {
            $this->importer->remove($archiveFile);
        }

        $templateContents = json_decode(file_get_contents($tmpSchema), true);

        if ($themeVersion >= $templateContents['theme_version']) {
            $this->importer->remove($tmpThemePath);
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNAUTHORIZED_ACTION',
                'errors' => ['Template is already updated']
            ]));
            return;
        }

        exec(sprintf('diff -w %s %s', $currentSchema, $tmpSchema), $diff);

        $ecpages = $this->template->getTemplateEcPagesByTemplateId($template['id']);

        if (count($diff) > 0 && is_array($ecpages)) {

            if (file_exists('/var/www/ectools/artisan')) {
                // this script run on server only
                exec('php /var/www/ectools/artisan db:snapshot ' . STORECODE);
            }

            $this->updater->construct($template, $this->db, $this->updater->baseDir);

            $this->updater->setTemplateObject($templateContents);

            $this->updater->setLocals($templateContents['locales']);

            $this->updater->applySettings();

            $this->updater->compileSchema();

            $this->updater->update();
        }

        $this->template->updateTemplateVersion($template['id'], $templateContents['theme_version']);

        // TODO compress moved current theme instead of removing it
        // TODO move current theme files from the current path to temp path with unique name

        $this->importer->remove($currentTheme);

        // move $tmpThemePath to base themes dir
        $this->importer->mv($tmpThemePath, $currentTheme);

        $this->importer->mv($currentTheme . '/' . 'schema.json', $currentTheme . '/' . $template['CodeName'] . '.json');

        $this->importer->remove($tmpThemePath);

        $cacheDir = rtrim($this->updater->baseDir, '/') . '/cache';

        if (is_dir($cacheDir)) {
            exec(sprintf('rm %s -R', $cacheDir));
        }

        $this->response->setOutput(json_encode([
            'status' => 'OK',
            'message' => 'Theme updated successfully'
        ]));
        return;
    }
}
