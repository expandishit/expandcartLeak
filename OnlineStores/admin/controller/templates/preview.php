<?php
class ControllerTemplatesPreview extends Controller
{
    public function index()
    {
        $codeName = $this->request->get['t'];

        if (!isset($codeName) || strlen($codeName) < 1) {
            throw new \Exception('invalide template name');
        }

        $this->initializer([
            'templates/template',
            'templates/preview',
        ]);

        $template = $this->template->getTemplateByConfigName($codeName);

        if (!$template) {
            throw new \Exception('invalid template');
        }

        if (!$this->preview->temporaryTemplateExists($template['id'])) {
            $this->preview->applyTemplateForPreview($template);
        }

        $this->response->redirect($this->fronturl->frontUrl('common/home&__p=' . $codeName));
    }
}
