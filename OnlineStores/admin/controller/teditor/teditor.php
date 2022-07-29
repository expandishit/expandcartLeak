<?php
class ControllerTeditorTeditor extends Controller {

    public function index() {
        //$data['token'] = $this->session->data['token'];
        $data['langcode'] = $this->language->get('code');
        $data['imageManagerUrl'] = $this->url->link('common/filemanager');

        /***************** Start ExpandCartTracking #347718  ****************/
        $extra_routes = [
            'Editor'            => "TEditor",
            'Current Route'     => str_replace("/admin/","",$_SERVER['REQUEST_URI']),
            'Previous Route'    => substr($_SERVER['HTTP_REFERER'],(strpos($_SERVER['HTTP_REFERER'], '/admin/') + strlen('/admin/')))
        ];

        // send mixpanel track page event
        $this->load->model('setting/mixpanel');
        $this->model_setting_mixpanel->trackEvent('Visit Route', $extra_routes);

        // send mixpanel track page event
        $this->load->model('setting/amplitude');
        $this->model_setting_amplitude->trackEvent('Visit Route', $extra_routes);

        /***************** End ExpandCartTracking #347718  ****************/

        $this->data = $data;
        $this->data['title'] = "Template Editor";
        $this->template = 'teditor/index.html';

        $this->response->setOutput($this->render_ecwig());
    }

    public function getTemplateStructure() {
        if(IS_NEXTGEN_FRONT) {
            $this->load->model('teditor/teditor');
            $json = array();
            $json['Template'] = $this->model_teditor_teditor->getTemplate(CURRENT_TEMPLATE);
            $json['Pages'] = $this->model_teditor_teditor->getTemplatePages(CURRENT_TEMPLATE);
            $json['Regions'] = $this->model_teditor_teditor->getTemplateRegions(CURRENT_TEMPLATE);
            $this->model_teditor_teditor->prepareDraftVersion(CURRENT_TEMPLATE);
            $json['UserSections'] = $this->model_teditor_teditor->getSections(CURRENT_TEMPLATE, "draft");
            $json['Layouts'] = $this->model_teditor_teditor->getLayouts();
            //$json['Collections'] = $this->model_teditor_teditor->getTemplateCollections(CURRENT_TEMPLATE, "live");
            //$json['Fields'] = $this->model_teditor_teditor->getTemplateFields(CURRENT_TEMPLATE, "live");
            //$json['FieldsVal'] = $this->model_teditor_teditor->getTemplateFieldsVal(CURRENT_TEMPLATE, "live");
            $data['json'] = json_encode($json);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
        else {
            $json["error"] = "Not nextgen template";
            $this->response->addHeader('HTTP/1.1 500 Internal Server');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
        }
    }
    public function Publish() {


        if(IS_NEXTGEN_FRONT) {
            if ( !$this->user->hasPermission('modify', 'templates/update') )
            {
                $result_json['success'] = '0';
                $result_json['error'] = $this->language->get('error_permission');
                $this->response->setOutput(json_encode($result_json));
                return;
            }
            $this->load->model('teditor/teditor');
            $json = array();
            $this->model_teditor_teditor->publishTemplate(CURRENT_TEMPLATE);

            /***************** Start ExpandCartTracking #347717  ****************/

            $this->load->model('setting/template');
            $template_info = $this->model_setting_template->getTemplateInfo(CURRENT_TEMPLATE);

            $meta_data = [
                'Editor'            => "TEditor",
                'Template Name'     => isset($template_info['Name']) ? $template_info['Name'] :  CURRENT_TEMPLATE
            ];

            $this->userActivation->processSoftActivation($this->userActivation::PUBLISH_TEMPLATE);

            // send mixpanel track publish template
            $this->load->model('setting/mixpanel');
            $this->model_setting_mixpanel->trackEvent('Publish Template',$meta_data);

            // send mixpanel track publish template
            $this->load->model('setting/amplitude');
            $this->model_setting_amplitude->trackEvent('Publish Template',$meta_data);

            /***************** End ExpandCartTracking #347717  ****************/


            $json['status'] = "OK";

            $data['json'] = json_encode($json);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
        else {
            $json["error"] = "Not nextgen template";
            $this->response->addHeader('HTTP/1.1 500 Internal Server');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function ResetDraftVersion() {
        if(IS_NEXTGEN_FRONT) {
            $this->load->model('teditor/teditor');
            $json = array();
            $this->model_teditor_teditor->resetDraftVersion(CURRENT_TEMPLATE);
            $json['status'] = "OK";

            $data['json'] = json_encode($json);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
        else {
            $json["error"] = "Not nextgen template";
            $this->response->addHeader('HTTP/1.1 500 Internal Server');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function selectLanguage() {
        if(IS_NEXTGEN_FRONT && isset($_POST['LangCode'])) {
            $this->load->model('teditor/teditor');
            $json = array();
            $langCode = $_POST['LangCode'];
            $this->session->data['language'] = $langCode;
            $json['status'] = "OK";
            $data['json'] = json_encode($json);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
        else {
            $json["error"] = "Not nextgen template";
            $this->response->addHeader('HTTP/1.1 500 Internal Server');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function insertSection() {
        if(IS_NEXTGEN_FRONT
            && isset($_POST['sourceSectionId'])
            && isset($_POST['sourceType'])
            && isset($_POST['destType'])
            && isset($_POST['destSortOrder'])) {
            $this->load->model('teditor/teditor');
            $json = array();
            $sourceSectionId = (int)$_POST['sourceSectionId'];
            $sourceType = $_POST['sourceType'];
            $destType = $_POST['destType'];
            $destSortOrder = (int)$_POST['destSortOrder'];

            $insertedSectionId =  $this->model_teditor_teditor->insertSection($sourceSectionId, $sourceType, $destType, $destSortOrder);
            $json['insertedSectionId'] = $insertedSectionId;

            //$json['UserSections'] = $this->model_teditor_teditor->getSections(CURRENT_TEMPLATE, "live");
            //$json['Collections'] = $this->model_teditor_teditor->getTemplateCollections(CURRENT_TEMPLATE, "live");
            //$json['Fields'] = $this->model_teditor_teditor->getTemplateFields(CURRENT_TEMPLATE, "live");
            //$json['FieldsVal'] = $this->model_teditor_teditor->getTemplateFieldsVal(CURRENT_TEMPLATE, "live");
            $data['json'] = json_encode($json);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
        else {
            $json["error"] = "Not nextgen template";
            $this->response->addHeader('HTTP/1.1 500 Internal Server');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function SaveFieldsVal() {
        if(IS_NEXTGEN_FRONT && isset($_POST)) {
            $this->load->model('teditor/teditor');
            $json = array();
            foreach($_POST as $key => $value) {
                //$json[$key] = $value;
                $this->model_teditor_teditor->saveFieldValById($key, htmlspecialchars_decode($value));

            }

            /***************** Start ExpandCartTracking #347696  ****************/

            $this->load->model('setting/template');
            $template_info = $this->model_setting_template->getTemplateInfo(CURRENT_TEMPLATE);
            $meta_data = [
                'Editor'            => "TEditor",
                'Template Name'     => isset($template_info['Name']) ? $template_info['Name'] :  CURRENT_TEMPLATE
            ];

            // send mixpanel event edit theme
            $this->load->model('setting/mixpanel');
            $this->model_setting_mixpanel->trackEvent('Edit Template', $meta_data);

            // send amplitude event edit theme
            $this->load->model('setting/amplitude');
            $this->model_setting_amplitude->trackEvent('Edit Template', $meta_data);

            /***************** End ExpandCartTracking #347696  ****************/

            $json['status'] = "OK";
            $data['json'] = json_encode($json);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
        else {
            $json["error"] = "Not nextgen template";
            $this->response->addHeader('HTTP/1.1 500 Internal Server');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function getRegionAvailableSections() {
        if(IS_NEXTGEN_FRONT && isset($_POST['RegionId'])) {
            $this->load->model('teditor/teditor');
            $json = array();
            $regionId = (int)$_POST['RegionId'];
            $json['AvailableSections'] = $this->model_teditor_teditor->getSections(CURRENT_TEMPLATE, "available", $regionId);
            $data['json'] = json_encode($json);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
        else {
            $json["error"] = "Not nextgen template";
            $this->response->addHeader('HTTP/1.1 500 Internal Server');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function getSectionFields() {
        if(IS_NEXTGEN_FRONT && isset($_GET['SectionId'])) {
            $this->load->model('teditor/teditor');
            $this->load->model('localisation/language');
            $json = array();
            $sectionId = (int)$_GET['SectionId'];
            $json['Section'] = $this->model_teditor_teditor->getSectionById($sectionId);
            $json['Collections'] = $this->model_teditor_teditor->getSectionCollections($sectionId);
            $json['Fields'] = $this->model_teditor_teditor->getAllSectionObjectFields($sectionId);
            $json['FieldsVal'] = $this->model_teditor_teditor->getAllSectionObjectFieldsVal($sectionId);
            $json['ReqMapData'] = array();
            $produsts = array();
            $categories = array();
            $brands = array();
            $images = array();

            foreach ($json['Fields'] as $field) {
                if($field['Type'] == "tags-product"){
                    $produsts = array_merge($produsts, $this->model_teditor_teditor->getProducts($field['id']));
                }
                else if($field['Type'] == "tags-category"){
                    $categories = array_merge($categories, $this->model_teditor_teditor->getCategories($field['id']));
                }
                else if($field['Type'] == "tags-brand"){
                    $brands = array_merge($brands, $this->model_teditor_teditor->getBrands($field['id']));
                }
                else if($field['LookUpKey'] != "" && !isset($json['ReqMapData'][$field['LookUpKey']])) {
                    $json['ReqMapData'][$field['LookUpKey']] = $this->model_teditor_teditor->getLookupByLookupKey($field['LookUpKey']);
                }
            }

            $this->load->model('tool/image');
            $imageFieldsVal = $this->model_teditor_teditor->getSectionImages($sectionId);
            $noImageThumb = $this->model_tool_image->resize('no_image.jpg', 235, 235);
            foreach ($imageFieldsVal as $fieldVal) {
                $image="";
                if (isset($fieldVal['Value'])) {
                    $image = $this->model_tool_image->resize($fieldVal['Value'], 235, 235);
                } else {
                    $image = $noImageThumb;
                }
                $fieldVal['ImageThumb'] = $image;
                $images[] = $fieldVal;
            }

            $json['ReqMapData']['tags-product'] = $produsts;
            $json['ReqMapData']['tags-category'] = $categories;
            $json['ReqMapData']['tags-brand'] = $brands;
            $json['ReqMapData']['images'] = $images;
            $json['ReqMapData']['noimagethumb'] = $noImageThumb;
            $json['ReqMapData']['languages'] = $this->model_localisation_language->getLanguages();
            $data['json'] = json_encode($json);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
        else {
            $json["error"] = "Not nextgen template";
            $this->response->addHeader('HTTP/1.1 500 Internal Server');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function getLinks() {

        $this->load->model('teditor/link_manager');
        $json = array();
        $json['Links'] = $this->model_teditor_link_manager->getLinks($this->request->get['search']);
        
        $data = json_encode($json);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput($data);
    
    }

    public function AddCollection() {
        if(IS_NEXTGEN_FRONT && isset($_POST['SectionId'])) {
            $this->load->model('teditor/teditor');
            $json = array();
            $sectionId = (int)$_POST['SectionId'];
            $json['CollectionId'] = $this->model_teditor_teditor->insertCollection(0, $sectionId);
            $json['Collection'] = $this->model_teditor_teditor->getCollectionById($json['CollectionId']);
            $json['Fields'] = $this->model_teditor_teditor->getCollectionObjectFields($json['CollectionId']);
            $json['FieldsVal'] = $this->model_teditor_teditor->getCollectionObjectFieldsVal($json['CollectionId']);
            $data['json'] = json_encode($json);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
        else {
            $json["error"] = "Not nextgen template";
            $this->response->addHeader('HTTP/1.1 500 Internal Server');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function ReorderSections() {
        if(IS_NEXTGEN_FRONT && isset($_POST)) {
        if ( !$this->user->hasPermission('modify', 'templates/update') )
        {
            $result_json['success'] = '0';
            $result_json['error'] = $this->language->get('error_permission');
            $this->response->setOutput(json_encode($result_json));
            return;
        }
            $this->load->model('teditor/teditor');
            $json = array();
            foreach($_POST as $sectionId => $sortOrder) {
                //$json[$key] = $value;
                $this->model_teditor_teditor->updateSectionOrder($sectionId, $sortOrder);
            }
            $json['status'] = "OK";


            $data['json'] = json_encode($json);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
        else {
            $json["error"] = "Not nextgen template";
            $this->response->addHeader('HTTP/1.1 500 Internal Server');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function RemoveSection() {
        if(IS_NEXTGEN_FRONT && isset($_POST['SectionId'])) {
            if ( !$this->user->hasPermission('modify', 'templates/update') )
            {
                $result_json['success'] = '0';
                $result_json['error'] = $this->language->get('error_permission');
                $this->response->setOutput(json_encode($result_json));
                return;
            }
            $this->load->model('teditor/teditor');
            $json = array();
            $sectionId = (int)$_POST['SectionId'];
            $this->model_teditor_teditor->deleteSection($sectionId);
            $json['UserSections'] = $this->model_teditor_teditor->getSections(CURRENT_TEMPLATE, "draft");
            $json['status'] = "OK";
            $data['json'] = json_encode($json);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
        else {
            $json["error"] = "Not nextgen template";
            $this->response->addHeader('HTTP/1.1 500 Internal Server');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function ReorderCollections() {
        if(IS_NEXTGEN_FRONT && isset($_POST)) {
            if ( !$this->user->hasPermission('modify', 'templates/update') )
            {
                $result_json['success'] = '0';
                $result_json['error'] = $this->language->get('error_permission');
                $this->response->setOutput(json_encode($result_json));
                return;
            }
            $this->load->model('teditor/teditor');
            $json = array();
            foreach($_POST as $collectionId => $sortOrder) {
                //$json[$key] = $value;
                $this->model_teditor_teditor->updateCollectionOrder($collectionId, $sortOrder);
            }
            $json['status'] = "OK";


            $data['json'] = json_encode($json);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
        else {
            $json["error"] = "Not nextgen template";
            $this->response->addHeader('HTTP/1.1 500 Internal Server');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function RemoveCollection() {
        if(IS_NEXTGEN_FRONT && isset($_POST['CollectionId'])) {
            $this->load->model('teditor/teditor');
            $json = array();
            $collectionId = (int)$_POST['CollectionId'];
            $this->model_teditor_teditor->deleteCollection($collectionId);

            $json['status'] = "OK";
            $data['json'] = json_encode($json);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
        else {
            $json["error"] = "Not nextgen template";
            $this->response->addHeader('HTTP/1.1 500 Internal Server');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function getLookup() {
        if(IS_NEXTGEN_FRONT && isset($_GET['LookupKey'])) {
            $this->load->model('teditor/teditor');
            $json = array();
            $lookupKey = $_GET['LookupKey'];
            $json['Lookup'] = $this->model_teditor_teditor->getLookupByLookupKey($lookupKey);
            $data['json'] = json_encode($json);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
        else {
            $json["error"] = "Not nextgen template";
            $this->response->addHeader('HTTP/1.1 500 Internal Server');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function getImageThumb() {
        if(IS_NEXTGEN_FRONT && isset($_GET['Image'])) {
            $this->load->model('tool/image');
            $json = array();
            $image = $_GET['Image'];
            $json['ImageThumb'] = '';
            $json['NoImageThumb'] = $this->model_tool_image->resize('no_image.jpg', 235, 235);
            if ($image) {
                $json['ImageThumb'] = $this->model_tool_image->resize($image, 235, 235);
            } else {
                $json['ImageThumb'] = $json['NoImageThumb'];
            }
            $data['json'] = json_encode($json);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
        else {
            $json["error"] = "Not nextgen template";
            $this->response->addHeader('HTTP/1.1 500 Internal Server');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function searchProducts() {
        if(IS_NEXTGEN_FRONT && isset($_GET['ProductName'])) {
            $this->load->model('teditor/teditor');
            $json = array();
            $str = $_GET['ProductName'];
            $json['Products'] = $this->model_teditor_teditor->searchProducts($str);
            $data['json'] = json_encode($json);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
        else {
            $json["error"] = "Not nextgen template";
            $this->response->addHeader('HTTP/1.1 500 Internal Server');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function searchCategories() {
        if(IS_NEXTGEN_FRONT && isset($_GET['CategoryName'])) {
            $this->load->model('teditor/teditor');
            $json = array();
            $str = $_GET['CategoryName'];
            $json['Categories'] = $this->model_teditor_teditor->searchCategories($str);
            $data['json'] = json_encode($json);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
        else {
            $json["error"] = "Not nextgen template";
            $this->response->addHeader('HTTP/1.1 500 Internal Server');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function searchBrands() {
        if(IS_NEXTGEN_FRONT && isset($_GET['BrandName'])) {
            $this->load->model('teditor/teditor');
            $json = array();
            $str = $_GET['BrandName'];
            $json['Brands'] = $this->model_teditor_teditor->searchBrands($str);
            $data['json'] = json_encode($json);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
        else {
            $json["error"] = "Not nextgen template";
            $this->response->addHeader('HTTP/1.1 500 Internal Server');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function UpdateObjectName() {
        if(IS_NEXTGEN_FRONT && isset($_POST['ObjectType']) && isset($_POST['ObjectId']) && isset($_POST['NewObjectName'])) {
            $this->load->model('teditor/teditor');
            $json = array();

            if($_POST['ObjectType'] == "Section")
                $this->model_teditor_teditor->updateSectionName((int)$_POST['ObjectId'], $_POST['NewObjectName']);
            else if($_POST['ObjectType'] == "Collection")
                $this->model_teditor_teditor->updateCollectionName((int)$_POST['ObjectId'], $_POST['NewObjectName']);
            $json['status'] = "OK";
            $data['json'] = json_encode($json);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
        else {
            $json["error"] = "Not nextgen template";
            $this->response->addHeader('HTTP/1.1 500 Internal Server');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function UpdateSectionState() {
        if(IS_NEXTGEN_FRONT && isset($_POST['SectionId']) && isset($_POST['State'])) {
            if ( !$this->user->hasPermission('modify', 'templates/update') )
            {
                $result_json['success'] = '0';
                $result_json['error'] = $this->language->get('error_permission');
                $this->response->setOutput(json_encode($result_json));
                return;
            }
            $this->load->model('teditor/teditor');
            $json = array();

            $this->model_teditor_teditor->updateSectionState((int)$_POST['SectionId'], $_POST['State']);

            $json['status'] = "OK";
            $data['json'] = json_encode($json);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
        else {
            $json["error"] = "Not nextgen template";
            $this->response->addHeader('HTTP/1.1 500 Internal Server');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function GetLanguages() {
        if(IS_NEXTGEN_FRONT) {
            $this->load->model('localisation/language');
            $json['Languages'] = $this->model_localisation_language->getActiveLanguages();
            $data['json'] = json_encode($json);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
        else {
            $json["error"] = "Not nextgen template";
            $this->response->addHeader('HTTP/1.1 500 Internal Server');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function AddLayout() {
        if(IS_NEXTGEN_FRONT && isset($_POST['Name']) && isset($_POST['Route'])) {
            if ( !$this->user->hasPermission('modify', 'templates/update') )
            {
                $result_json['success'] = '0';
                $result_json['error'] = $this->language->get('error_permission');
                $this->response->setOutput(json_encode($result_json));
                return;
            }
            $this->load->model('teditor/teditor');
            $json = array();
            $name = $_POST['Name'];
            $route = $_POST['Route'];
            $this->model_teditor_teditor->addLayout(CURRENT_TEMPLATE, $route, $name);

            $json['status'] = "OK";
            $data['json'] = json_encode($json);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
        else {
            $json["error"] = "Not nextgen template";
            $this->response->addHeader('HTTP/1.1 500 Internal Server');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
        }
    }
}