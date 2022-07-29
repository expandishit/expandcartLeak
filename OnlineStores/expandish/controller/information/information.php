<?php
class ControllerInformationInformation extends Controller
{
    public function index()
    {
        $this->language->load_json('information/information');

        $this->load->model('catalog/information');
        $this->load->model('module/store_locations');

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home'),
            'separator' => false,
        );

        if ($this->preAction->isActive()) {
            if (isset($this->request->get['slug']) && !isset($this->request->get['information_id'])) {
                $name = $this->request->get['slug'];
                $data = $this->model_catalog_information->getInformationByName($name);

                if ($data) {
                    $this->request->get['information_id'] = $data->row['information_id'];
                }
            }
        }

        if (isset($this->request->get['information_id'])) {
            $information_id = (int) $this->request->get['information_id'];
        } else {
            $information_id = 0;
        }

        $information_info = $this->model_catalog_information->getInformation($information_id);

        if (!empty($information_info) && $information_info['status'] != 0) {
            
            $this->document->setTitle($information_info['title']);

            $this->data['breadcrumbs'][] = array(
                'text' => $information_info['title'],
                'href' => $this->url->link('information/information', 'information_id=' . $information_id),
                'separator' => $this->language->get('text_separator'),
            );

            $this->data['heading_title'] = $information_info['title'];

            $this->data['description'] = html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8');
            $this->data['store_locations'] = [];
            if (\Extension::isInstalled('store_locations')) {
            $this->data['store_locations'] = $this->model_module_store_locations->getList(0,0);
            }
            //$this->data['continue'] = $this->url->link('common/home');

            if (file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/information/information.expand')) {
                $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/information/information.expand';
            } else {
                $this->template = $this->config->get('config_template') . '/template/information/information.expand';
            }

            $this->children = array(
                'common/footer',
                'common/header',
            );

            $this->response->setOutput($this->render_ecwig());
        } else {
            $this->data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_error'),
                'href' => $this->url->link('information/information', 'information_id=' . $information_id),
                'separator' => $this->language->get('text_separator'),
            );

            $this->document->setTitle($this->language->get('text_error'));

            //prepare the error message in the body "Page Title + page not found"
            $this->data['text_error'] = $this->model_catalog_information->getTitle($information_id) . ' ' . $this->language->get('text_error');

            $this->data['continue'] = $this->url->link('common/home');

            if (file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/error/not_found.expand')) {
                $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/error/not_found.expand';
            } else {
                $this->template = $this->config->get('config_template') . '/template/error/not_found.expand';
            }

            $this->children = array(
                'common/footer',
                'common/header',
            );

            $this->response->setOutput($this->render_ecwig());
        }
    }

    public function info()
    {
        $this->load->model('catalog/information');

        if (isset($this->request->get['information_id'])) {
            $information_id = (int) $this->request->get['information_id'];
        } else {
            $information_id = 0;
        }

        $information_info = $this->model_catalog_information->getInformation($information_id);

        if ($information_info) {
            $output = '<html dir="ltr" lang="en">' . "\n";
            $output .= '<head>' . "\n";
            $output .= '  <title>' . $information_info['title'] . '</title>' . "\n";
            $output .= '  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
            $output .= '  <meta name="robots" content="noindex">' . "\n";
            $output .= '</head>' . "\n";
            $output .= '<body>' . "\n";
            $output .= '  <h1>' . $information_info['title'] . '</h1>' . "\n";
            $output .= html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8') . "\n";
            $output .= '  </body>' . "\n";
            $output .= '</html>' . "\n";

            $this->response->setOutput($output);
        }
    }
}
