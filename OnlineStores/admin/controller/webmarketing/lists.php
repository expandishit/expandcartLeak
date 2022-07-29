<?php
class ControllerWebmarketinglists extends Controller {
   
    public function index() {
        $this->language->load('webmarketing/list');

        $this->document->setTitle($this->language->get('heading_title'));
        $this->data['breadcrumbs'] = array();
        $this->load->model('setting/setting');
        $checked_marketing_list = explode(',',$this->config->get('checked_marketing_list'));

    $this->data['checklisted'][] = array();
    foreach($checked_marketing_list as $key => $value){
        array_push($this->data['checklisted'],$value);
    }


        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('webmarketing/lists'),
            'separator' => ' :: '
        );

        $task_id = $this->request->get['task'] ? $this->request->get['task'] : 0;
        if (file_exists('view/template/webmarketing/webmarketing.json')) {
			$this->language->load('webmarketing/list');
            $list = json_decode(file_get_contents('view/template/webmarketing/webmarketing.json'), true);
            $li = 0;
            foreach($list as $item){
                
                $list_data[$li] = $item;
                $list_data[$li]['title'] = $this->language->get($item['title']);
                $list_data[$li]['type'] = $this->language->get($item['type']);
                $list_data[$li]['level'] = $this->language->get($item['level']);
                $list_data[$li]['subtitle'] = $this->language->get($item['subtitle']);
                $list_data[$li]['desc'] = $this->language->get($item['desc']);
                if($task_id && $item['id'] == $task_id){
                    $this->data['task_data'] = $list_data[$li];
                    break;
                }

                $li++;
            }
         }
                 $task_id = $this->request->get['task'] ? $this->request->get['task'] : 0;

        if($task_id)
            $this->template = 'webmarketing/task.expand';
        else
            $this->template = 'webmarketing/checklist.expand';

        if (file_exists('view/template/webmarketing/webmarketing.json')) {
			$this->language->load('webmarketing/list');
            $list = json_decode(file_get_contents('view/template/webmarketing/webmarketing.json'), true);
            $li = 0;
            foreach($list as $item){
                $list_data[$li] = $item;
                $list_data[$li]['title'] = $this->language->get($item['title']);
                $list_data[$li]['type'] = $this->language->get($item['type']);
                $list_data[$li]['level'] = $this->language->get($item['level']);
                $list_data[$li]['subtitle'] = $this->language->get($item['subtitle']);
                $list_data[$li]['desc'] = $this->language->get($item['desc']);
                $list_data[$li]['image'] = $this->language->get($item['image']);
                $li++;
            }
		 }
        
         $this->data['list'] = $list_data;

        $this->base = "common/base";

        $this->response->setOutput($this->render_ecwig());
    }

    
    public function NewUpdate(){
        $dataArray = $this->request->post['items'];
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSettingValue("","checked_marketing_list",implode(',',array_unique($dataArray)));
    }

}