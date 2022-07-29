<?php
//exit();die();
class ControllerTemplatesImport extends Controller
{
    // public $baseDir = DIR_CATALOG . 'view/custom/' . STORECODE . '/';
    public $baseDir = DIR_CUSTOM_TEMPLATE;
    public $tmpDir = TEMP_DIR_PATH;

    public function index()
    {
        $data = [];

        $this->language->load('templates/import');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['progress_id'] = ini_get("session.upload_progress.name");

        if (file_exists($this->baseDir) == false || is_writable($this->baseDir) == false) {
            mkdir($this->baseDir, 0777);
            chmod($this->baseDir, 0777);
        }

        if (file_exists($this->tmpDir) == false) {
            mkdir($this->tmpDir, 0777);
            chmod($this->tmpDir, 0777);
        }

        $this->template = 'templates/import/form.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function uploadFile()
    {
        $this->initializer([
            'templates/template',
            'templates/applier',
            'importer' => 'templates/import',
        ]);

        if (file_exists($this->tmpDir) == false) {
            mkdir($this->tmpDir, 0777);
        }

        if (is_writable($this->tmpDir) == false) {
            chmod($this->tmpDir, 0777);
        }

        $filename = $_FILES["template"]["name"];
        $ext = explode(".", $filename);
        $ext = end($ext);
        if(strtoupper($ext) != 'ZIP') {
            $this->response->setOutput(json_encode([
                'status' => 'error',
                'errors' => 'illegal file',
            ]));
            return;
        }

        $baseFile = basename($_FILES['template']['name'], '.zip');

        if ($_FILES['template']['size'] > $this->template->getMaximumTemplatesSize()) {
            $this->response->setOutput(json_encode([
                'status' => 'warning',
                'errors' => ['Maximum file size is exceeded'],
            ]));
            return;
        }

        $apply = false;
        if ($baseFile == $this->config->get('config_template')) {
            $apply = true;
        }

        if (isset($this->request->post['override']) && $this->request->post['override'] == 1) {

            $this->applier->construct($baseFile, $this->db, $this->baseDir);

            $this->applier->deletePages($baseFile);
            $this->applier->deleteObjectField($baseFile);

            $this->template->deleteTemplateByCode($baseFile);

            $this->importer->remove($this->baseDir . $baseFile);
        }

        try {
            $this->template->checkTemplate($baseFile);
        } catch(\Exception $e) {
            // TODO error handler
            $archiveFile = $this->tmpDir . $baseFile;

            if (file_exists($archiveFile)) {
                $this->importer->remove($archiveFile);
            }

            $this->response->setOutput(json_encode([
                'status' => 'warning',
                'errors' => [$e->getMessage()],
                'code' => $baseFile
            ]));
            return;
        }

        // TODO generate the name instead of creating it with the same name
        $this->importer->upload($_FILES['template'], $this->tmpDir);

        $this->response->setOutput(json_encode([
            'status' => 'success',
            'message' => [
                'file has been uploaded'
            ],
            'template' => $_FILES['template'],
            'base' => $baseFile,
            'apply_template' => $apply,
        ]));
        return;
    }

    public function uploadProgress()
    {
        $progressKey = ini_get("session.upload_progress.prefix") . "import_template";

        if (!isset($this->request->session[$progressKey]) || empty($this->request->session[$progressKey])) {
            return $this->response->setOutput(json_encode([
                'status' => 'OK',
                'data' => ['progress' => 100],
            ]));
        }

        $progressObject = $this->request->session[$progressKey];

        $uploaded = $progressObject["bytes_processed"];
        $totalSize = $progressObject["content_length"];
        $progress = $uploaded < $totalSize ? ceil($uploaded / $totalSize * 100) : 100;

        return $this->response->setOutput(json_encode([
            'status' => 'OK',
            'data' => [
                'progress' => $progress,
                'uploaded' => $uploaded,
                'total_size' => $totalSize
            ]
        ]));
    }

    public function schemaGaurd()
    {
        $this->initializer([
            'templates/template',
            'importer' => 'templates/import',
            'schema' => 'templates/schema',
            'archive' => 'templates/archive',
        ]);

        // $zip = new ZipArchive();

        $archiveFile = $this->tmpDir . $this->request->post['template']['name'];

        $baseFile = $this->request->post['basefile'];

        try {
            $this->template->checkTemplate($baseFile);
        } catch(\Exception $e) {
            // TODO error handler
            $this->importer->remove($archiveFile);

            $this->response->setOutput(json_encode([
                'status' => 'error',
                'errors' => [$e->getMessage()],
            ]));
            return;
        }

        // $archive = $zip->open($archiveFile);
        $files = $this->archive->setTemplateName($baseFile)->files($archiveFile);

        if (!$files) {
            $this->importer->remove($archiveFile);

            $this->response->setOutput(json_encode([
                'status' => 'error',
                'errors' => $this->archive->getErrors(),
            ]));
            return;
        }

        /*if ($archive == false) {
            $this->response->setOutput(json_encode([
                'status' => 'error',
                'errors' => [
                    'invalid archive'
                ]
            ]));
            return;
        }*/

        /*$missingSchema = true;
        $schema = null;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);

            if ($name == $baseFile . '/schema.json') {
                $missingSchema = false;
                $schema = $zip->getFromName($name);
            }

            $files[] = $name;
        }*/

        // TODO if schema json is verified

        $this->schema->setSchemaPath('base');

        if (!$this->schema->gaurd($schema = $this->archive->getTemplateJson())) {

            $this->importer->remove($archiveFile);

            $this->response->setOutput(json_encode([
                'status' => 'error',
                'errors' => $this->schema->getErrors(),
            ]));
            return;
        }

        $this->archive->extract($this->baseDir)->close();

        $this->importer->remove($archiveFile);

        $apply = false;
        if (isset($this->request->post['apply_template']) && $this->request->post['apply_template'] == 'true') {
            $apply = true;
        }

        $this->response->setOutput(json_encode([
            'status' => 'success',
            'schemaPath' => $this->archive->getTemplateJsonPath(),
            'baseFile' => $baseFile,
            'apply_template' => $apply,
        ]));
        return;
    }

    public function hierarchyGaurd()
    {
        $this->initializer([
            'templates/template',
            'importer' => 'templates/import',
            'schema' => 'templates/schema',
            'archive' => 'templates/archive',
            'hierarchy' => 'templates/hierarchy',
        ]);

        $baseFile = $this->request->post['basefile'];

        $directory = $this->baseDir . $baseFile;

        if (
            rtrim($directory, '/') == rtrim($this->baseDir, '/') ||
            basename($directory) == basename($this->baseDir) ||
            dirname($directory) == dirname($this->baseDir)
        ) {
            $this->response->setOutput(json_encode([
                'status' => 'error',
                'errors' => [
                    $this->language->get('invalid_operation')
                ],
            ]));
            return;
        }

        if ($this->hierarchy->requiredDirectoriesGaurd($directory) == false) {

            $this->importer->remove($directory);

            $this->response->setOutput(json_encode([
                'status' => 'error',
                'errors' => $this->hierarchy->getErrors(),
            ]));
            return;
        }

        if ($this->hierarchy->filesContentGaurd($directory) == false) {

            $this->importer->remove($directory);

            $this->response->setOutput(json_encode([
                'status' => 'error',
                'errors' => $this->hierarchy->getErrors(),
            ]));
            return;
        }

        $schemaPath = $this->request->post['schemaPath'];

        $this->importer->mv(
            $this->baseDir . $baseFile . '/schema.json',
            $this->baseDir . $baseFile . '/' . $baseFile . '.json'
        );

        $apply = false;
        if (isset($this->request->post['apply_template']) && $this->request->post['apply_template'] == 'true') {
            $apply = true;
        }

        $this->response->setOutput(json_encode([
            'status' => 'success',
            'baseFile' => $baseFile,
            'apply_template' => $apply,
        ]));
        return;
    }

    public function sectionGaurd()
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
            isset($this->request->post['basefile']) == false &&
            empty($this->request->post['basefile'])
        ) {
            $this->response->setOutput(json_encode([
                'status' => 'error',
                'errors' => [
                    $this->language->get('invalid_request')
                ],
            ]));
            return;
        }

        $attributes = [];

        $baseFile = $this->request->post['basefile'];

        $externalTemplateId = 0;
        if (isset($this->session->data['eti']) && (int)$this->session->data['eti'] > 0) {
            $externalTemplateId = $this->session->data['eti'];
        }

        $this->initializer([
            'templates/template',
            'importer' => 'templates/import',
            'schema' => 'templates/schema',
            'file' => 'templates/filemanager',
        ]);

        $directory = $this->baseDir . $baseFile;

        $schema = json_decode(file_get_contents($directory . '/' . $baseFile . '.json'));

        $sectionSchemas = $this->schema->getSectionSchemas($schema);

        $sectionJsons = $this->file->getSectionSchemas($directory . '/metainfo', array_filter($sectionSchemas));

        $this->schema->setSchemaPath('sections');

        if (count($sectionJsons) > 0) {
            foreach ($sectionJsons as $sectionJson) {
                if (!$this->schema->gaurd($this->file->getFileContents($sectionJson['path']))) {

                    $this->importer->remove($directory);

                    $this->response->setOutput(json_encode([
                        'status' => 'error',
                        'errors' => $this->schema->getErrors(),
                        'file' => $sectionJson['file']
                    ]));
                    return;
                }
            }
        }

        $themeVersion = '1.0';
        if (isset($schema->theme_version) && (float)$schema->theme_version > $themeVersion) {
            $themeVersion = $schema->theme_version;
        }

        $attributes['uses_twig_extends'] = isset($schema->uses_twig_extends) && $schema->uses_twig_extends ? 1 : 0;

        $templateId = $this->template->insertTemplate($baseFile, $externalTemplateId, $themeVersion, $attributes);

        foreach ($schema->locales as $language => $locales) {

            $description = $this->template->localize($schema->description, $locales);

            $this->template->insertTemplateDescription($templateId, $language, (object)$description);
        }

        if (is_string($schema->options)) {
            $schema->options = $this->template->resolveSchemaOptions($schema->options);
        }

        $this->template->beginTransaction();

        $this->template->EmptyECLookupTable();

        $cacheDir = rtrim($this->baseDir, '/') . '/cache';
        if (is_dir($cacheDir)) {
            exec(sprintf('rm %s -R', $cacheDir));
        }

        foreach ($schema->options as $option) {
            if ($this->template->ifOptionExists($option) == false) {
                $this->template->newOption($option);
            }
        }

        $this->template->commitTransaction();

        unset($this->session->data['eti']);

        $apply = false;
        if (isset($this->request->post['apply_template']) && $this->request->post['apply_template'] == 'true') {
            $apply = true;
        }

        $this->response->setOutput(json_encode([
            'status' => 'success',
            'baseFile' => $baseFile,
            'template' => ['id' => $templateId, 'name' => $baseFile],
            'apply_template' => $apply,
            /*'href' => $this->url->link(
                'templates/customize',
                'template=' . $templateId,
                'SSL'
            )->format(),*/
            'href' => $this->url->link(
                'setting/template',
                '',
                'SSL'
            )->format(),
        ]));
        return;
    }
}
