<?php
class ControllerCommonStyle extends Controller
{
    public function index()
    {
        $this->load->model('extension/section');

        $page_codename = 'templatesettings';
        if (isset($this->request->get['__p'])) {
            $template_codename = $this->request->get['__p'];
        }
        else{
            $template_codename = CURRENT_TEMPLATE;
        }
        //$template_codename = CURRENT_TEMPLATE;
        $page_route = '';

        $settingspage = $this->model_extension_section->getPage($template_codename, $page_codename, $page_route);
        
        $settingssections = $this->model_extension_section->getRegionSection($settingspage['page_id']);

        foreach ($settingssections as $section) {
            $region_codename = $section['region_codename'];
            $section_codename = $section['section_codename'];
            $section_id = $section['section_id'];

            if ($region_codename == 'styling' && ($section_codename == 'colors' || $section_codename == 'fonts')) {
                $fields = $this->model_extension_section->getCollections($section_id, $this->config->get('config_language'));
                foreach($fields as $field) {
                    $this->data[$field['field_codename']] = $field['field_value'];
                }
            }
        }
//        global $expandishglobals;
//        //$fields = array();
//        foreach($expandishglobals->getGlobals()['templatesettings']['colors'] as $field_codename => $field_value) {
//            $this->data[$field_codename] = $field_value;
//        }
//        foreach($expandishglobals->getGlobals()['templatesettings']['fonts'] as $field_codename => $field_value) {
//            $this->data[$field_codename] = $field_value;
//        }

        $template = $this->config->get('config_template');
        if (isset($this->request->get['__p'])) {
            $template = $this->request->get['__p'];
        }
        
        //$this->data['buttoncolor'] = "#9a4747";
        $dirTemplate = (IS_CUSTOM_TEMPLATE == 1 ? DIR_CUSTOM_TEMPLATE : DIR_TEMPLATE);
        $cssfile = 'style' . ($this->language->get('direction') == 'rtl' ? '-RTL.css' : '.css');
        if (file_exists($dirTemplate . $template . '/css/'.$cssfile)) {
            $this->template = $template . '/css/'.$cssfile;
        } else {
            $this->template = 'clearion/css/'.$cssfile;
        }
        ob_start("ob_gzhandler");
        $this->response->addHeader('Content-type: text/css; charset: UTF-8');
        $this->response->addHeader('Cache-Control: must-revalidate');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');

        $buffer = $this->render_ecwig();
        // Remove comments
        $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
        // Remove space after colons
        $buffer = str_replace(': ', ':', $buffer);
        // Remove whitespace
        $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);

        $this->response->setOutput($buffer);//($this->render_ecwig());
    }
}
