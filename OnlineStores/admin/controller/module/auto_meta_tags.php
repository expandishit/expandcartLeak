<?php
/**
*   controller class for auto_meta_tags application
*   @author Michael
*/
class ControllerModuleAutoMetaTags extends Controller
{
    private $error = array();
    /**
    *   Index function to return the template or run $this->submit() if the form was submitted.
    *   @return string $template the template html view or boolean if the operations were successful.
    */
    public function index() {
        // load language file
        $this->language->load('module/auto_meta_tags');

        // load app module
        $this->load->model('module/auto_meta_tags');

        // check if the form was submitted
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            if ( $this->validate() )
            {
                $this->model_module_auto_meta_tags->add_setting('auto_meta_tags_enable_module', $this->request->post['module_status']);
                $this->model_module_auto_meta_tags->add_setting('auto_meta_tags_description_words_number', (int) $this->request->post['description_words_number']);
                $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
                $result_json['success'] = '1';
            }
            else
            {
                $result_json['success'] = '0';
                $result_json['error'] = $this->error;
            }

            $this->response->setOutput(json_encode($result_json));
            return;
        }

        // set document title
        $this->document->setTitle($this->language->get('heading_title'));

        // -==-==-==-==- breadcrumbs -==-==-==-==-
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('marketplace/home', 'type=apps', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/auto_meta_tags', '', 'SSL'),
            'separator' => ' :: '
        );

        // -==-==-==-==- end of breadcrumbs -==-==-==-==-

        // set tempplate
        $this->template = 'module/auto_meta_tags.expand';

        // links
        $this->data['action'] = $this->url->link('module/auto_meta_tags', '', 'SSL');
        $this->data['cancel'] = $this->url->link('marketplace/home', '', 'SSL');

        // check if the module is enabled or not now to select the right option in the select field in the view.
        $this->data['module_enabled'] = $this->is_module_enabled();
        $this->data['description_words_number'] = $this->model_module_auto_meta_tags->get_setting('auto_meta_tags_description_words_number');


        // set required variables and render the template.
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

    /**
     *  Adds values to template data.   
     *
     *   @param array $values an array of the values to add to the template data
     *   @return null
    */
    private function add_to_template_data($values = array())
    {
        foreach ($values as $value) {
            $this->data[$value] = $this->language->get($value);
        }
    }

    /**
    *   Validate Data
    *   @return boolean data valid or not.
    */
    private function validate()
    {
        // check whether module status is EITHER 1 or 0
        if ( ! in_array($this->request->post['module_status'], ['1', 1, 'on', '0', 0, 'off']) )
        {
            $this->error[] = $this->language->get('error_in_module_status');
        }

        // check if words_number is set
        if (!isset($this->request->post['description_words_number']))
        {
            $this->error[] = $this->language->get('error_in_description_words_number');
        }

        // check if words_number is a valid integer
        if (!ctype_digit($this->request->post['description_words_number']))
        {
            $this->error[] = $this->language->get('error_description_words_number_not_int');
        }

        $description_words_number = (int) $this->request->post['description_words_number'];

        // check if words_number > 99 or < 0
        if ($description_words_number > 99 || $description_words_number < 0)
        {
            $this->error[] = $this->language->get('error_description_words_number_not_in_range');
        }

        if ( empty($this->error) )
        {
            return true;    
        }
        else
        {
            return false;
        }
        
    }

    /**
    *   Check whether the module is enabled or not.
    *   @return boolean true if module is enabled and false if not.
    */
    private function is_module_enabled()
    {
        return $this->model_module_auto_meta_tags->get_setting('auto_meta_tags_enable_module');
    }

    public function install() 
    {

    }

    public function uninstall() 
    {
    }
}
