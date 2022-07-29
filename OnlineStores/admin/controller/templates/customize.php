<?php
//exit();die();
class ControllerTemplatesCustomize extends Controller
{

    // public $baseDir = DIR_CATALOG . 'view/custom/' . STORECODE . '/';
    public $baseDir = DIR_CUSTOM_TEMPLATE;

    private function getBaseDir()
    {
        return str_replace(['/', '\\'], "/", $this->baseDir);
    }

    public function index()
    {
        $this->language->load('templates/customize');

        $this->document->setTitle($this->language->get('heading_title'));

        if (isset($this->request->get['template']) == false) {
            throw new \Exception('Invalid template');
        }

        $templateId = $this->request->get['template'];

        $this->initializer([
            'templates/template',
        ]);

        $template = $this->template->getTemplateById($templateId);

        if (!$template) {
            throw new \Exception('Template not found');
        }

        $this->data['codeName'] = $template['CodeName'];
        $this->data['template_id'] = $templateId;
        $this->data['baseDir'] = $this->getBaseDir();

        $this->template = 'templates/customize/editor.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function getFilesTree()
    {
        if (isset($this->request->post['template_id']) == false) {
            throw new \Exception('Invalid template');
        }

        $templateId = $this->request->post['template_id'];

        $this->initializer([
            'templates/template',
            'files' => 'templates/filemanager',
        ]);

        $template = $this->template->getTemplateById($templateId);

        $filesTree = $this->files->getFilesTree($this->template->baseDir . $template['CodeName'], 0);

        $this->response->setOutput(json_encode([
            [
                'title' => $template['CodeName'],
                'folder' => true,
                'children' => $filesTree,
                'expanded' => true
            ]
        ]));
    }

    public function getFileContents()
    {
        if (isset($this->request->post['file_path']) == false) {
            throw new \Exception('Missing file path');
        }

        if (isset($this->request->post['file_extension']) == false) {
            throw new \Exception('Missing file path');
        }

        $filePath = $this->request->post['file_path'];
        $fileExtension = $this->request->post['file_extension'];

        $this->initializer([
            'templates/template',
            'files' => 'templates/filemanager',
        ]);

        if ($this->files->validateFilePath($filePath) == false) {
            throw new \Exception('Invalid Path');
        }

        if ($this->files->validateFileExtension($fileExtension) == false) {
            throw new \Exception('Invalid Extension');
        }

        $output = $this->files->getFileContents($filePath);

        $this->response->setOutput($fileExtension == 'expand' ? htmlentities($output) : $output);
    }

    public function putFileContents()
    {
        if (isset($this->request->post['file_contents']) == false) {
            throw new \Exception('Invalid contents');
        }

        if (isset($this->request->post['file_path']) == false) {
            throw new \Exception('Invalid file path');
        }

        $fileContents = $this->request->post['file_contents'];

        if (preg_match('#\<\?(\=|\s.*?\?\>|php)#', html_entity_decode($fileContents))) {
            throw new \Exception('Invalid tokens');
        }

        $filePath = $this->request->post['file_path'];

        $this->initializer([
            'templates/template',
            'files' => 'templates/filemanager',
        ]);

        $this->files->putFileContents($filePath, $fileContents);

        $this->files->clearCache();

        $this->response->setOutput(json_encode([
            'status' => 'success'
        ]));
    }

    public function newChild()
    {
        if (isset($this->request->post['child']) == false) {
            throw new \Exception('Invalid contents');
        }

        if (isset($this->request->post['path']) == false) {
            throw new \Exception('Invalid file path');
        }

        $this->initializer([
            'templates/template',
            'files' => 'templates/filemanager',
        ]);

        $child = $this->request->post['child'];

        $path = $this->request->post['path'];

        $file = $this->files->getFileType($child);

        if (isset($file['extension'])) {

            if ($this->files->validateFileExtension($file['extension']) == false) {
                $this->response->setOutput(json_encode([
                    'status' => 'error',
                    'errors' => 'Invalid file extension'
                ]));
                return;
            }

            $this->files->newFile(rtrim($path, '/'), $child);

        } else {
            $this->files->newDirectory(rtrim($path, '/'), $child);
        }

        $this->response->setOutput(json_encode([
            'status' => 'success'
        ]));
    }

}
