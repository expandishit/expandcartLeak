<?php
class ControllerTeditorTeditorV2 extends Controller {

    public function index() {
        //$data['token'] = $this->session->data['token'];
        $data['langcode'] = $this->language->get('code');
        $data['imageManagerUrl'] = $this->url->link('common/filemanager');

        $this->data = $data;
        $this->data['title'] = "Template Editor";
        $this->template = 'teditorV2/index.html';

        $this->response->setOutput($this->render_ecwig());
    }

    public function getTemplateStructure() {
        if(IS_NEXTGEN_FRONT) {
            $this->load->model('teditor/teditor');
            $this->load->model('catalog/information');
            $json = array();
            $json['Template'] = $this->model_teditor_teditor->getTemplate(CURRENT_TEMPLATE);

            $this->model_teditor_teditor->prepareDraftVersion(CURRENT_TEMPLATE);
            $pages = $this->model_teditor_teditor->getTemplatePages(CURRENT_TEMPLATE, false);
            foreach($pages as &$page){
                $regions = $this->model_teditor_teditor->getTemplateRegions(CURRENT_TEMPLATE, $page['id']);
                foreach($regions as &$region){
                    $userSections = $this->model_teditor_teditor->getSections(CURRENT_TEMPLATE, 'draft', $region['id']);
                    $region['UserSections'] = $userSections;
                }
                $page['Regions'] = $regions;
            }
            $json['Pages'] = $pages;

            $web_pages = $this->model_catalog_information->getInformations();
            foreach($web_pages as &$page){
                $url = $this->registry->get('fronturl');
                $page['prview'] = $url->frontUrl('information/information', 'information_id='.$page['information_id'], true);
            }
            $json['WebPages'] = $web_pages;

            $json['Layouts'] = $this->model_teditor_teditor->getLayouts();

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

    public function getTemplateSettings() {
        if(IS_NEXTGEN_FRONT) {
            $this->load->model('teditor/teditor');
            $json = array();
            $this->model_teditor_teditor->prepareDraftVersion(CURRENT_TEMPLATE);
            $settings_page = $this->model_teditor_teditor->getTemplateSettingsPage(CURRENT_TEMPLATE);

            if(count($settings_page)){
                $regions = $this->model_teditor_teditor->getTemplateRegions(CURRENT_TEMPLATE, $settings_page['id']);
                foreach($regions as &$region){
                    $userSections = $this->model_teditor_teditor->getSections(CURRENT_TEMPLATE, 'draft', $region['id']);
                    foreach($userSections as &$section){
                        $section['Fields'] = $this->model_teditor_teditor->getSectionObjectFields($section['id']);
                        $section['Collections'] = $this->model_teditor_teditor->getSectionCollections($section['id']);

                        foreach ($section['Collections'] as &$collection) {
                            $collection['Fields'] = $this->model_teditor_teditor->getCollectionObjectFields($collection['id']);
                            foreach ($collection['Fields'] as &$field) {
                                $field['FieldVals'] = $this->getFieldVals($field['id']);
                                if($field['LookUpKey'] != "") {
                                    $field['LookUpVals'] = $this->model_teditor_teditor->getLookupByLookupKey($field['LookUpKey']);
                                }
                            }
                        }
                        foreach ($section['Fields'] as &$field) {
                            $field['FieldVals'] = $this->getFieldVals($field['id']);
                            if($field['LookUpKey'] != "") {
                                $field['LookUpVals'] = $this->model_teditor_teditor->getLookupByLookupKey($field['LookUpKey']);
                            }
                        }
                    }
                    $region['UserSections'] = $userSections;
                }
                $settings_page['Regions'] = $regions;
            }

            $json['Settings'] = $settings_page;

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
        if(IS_NEXTGEN_FRONT && isset($_POST)) {
            $this->load->model('teditor/teditor');
            $json = array();
            $this->model_teditor_teditor->publishTemplate(CURRENT_TEMPLATE);
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
        if(IS_NEXTGEN_FRONT && isset($_POST)) {
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
            $this->load->model('localisation/language');

            $json = array();

            $langCode = $_POST['LangCode'];
            $langObject = $this->model_localisation_language->getLanguageByCode($langCode);
            if($langObject){
                $this->session->data['language'] = $langCode;
                $json['status'] = "OK";
                $data['json'] = json_encode($json);

                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
            }
            else {
                $json["error"] = "Language does not exist!";
                $this->response->addHeader('HTTP/1.1 500 Internal Server');
                $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
                $this->response->setOutput(json_encode($json));
            }

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
            && isset($_POST['destType'])) {
            $this->load->model('teditor/teditor');
            $json = array();
            $sourceSectionId = (int)$_POST['sourceSectionId'];
            $sourceType = $_POST['sourceType'];
            $destType = $_POST['destType'];
            
            $destSortOrder = null;
            if(isset($_POST['destSortOrder'])){
                $destSortOrder = (int)$_POST['destSortOrder'];
            }

            $insertedSectionId =  $this->model_teditor_teditor->insertSection($sourceSectionId, $sourceType, $destType, $destSortOrder);
            $json['insertedSectionId'] = $insertedSectionId;
            $json['prevSectionId'] = $this->model_teditor_teditor->getPrevSectionId($insertedSectionId);
            $json['nextSectionId'] = $this->model_teditor_teditor->getNextSectionId($insertedSectionId);

            $section_data = $this->model_teditor_teditor->getSectionForPreview($insertedSectionId);
            $json['sectionHTML'] = $this->render_section($section_data);

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
            $fieldValId = null;
            foreach($_POST as $key => $value) {
                if(!$fieldValId) $fieldValId = $key;
                //$json[$key] = $value;
                $this->model_teditor_teditor->saveFieldValById($key, htmlspecialchars_decode($value));

            }
            $sectionId = $this->model_teditor_teditor->getFieldValSection($fieldValId);
            $section_data = $this->model_teditor_teditor->getSectionForPreview($sectionId);
            $json['sectionId'] = $sectionId;
            $json['sectionHTML'] = $this->render_section($section_data);
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

    public function getPageAvailableSections() {
        if(IS_NEXTGEN_FRONT && isset($_POST['PageId'])) {
            $this->load->model('teditor/teditor');
            $json = array();
            $pageId = (int)$_POST['PageId'];

            $regions = $this->model_teditor_teditor->getTemplateRegions(CURRENT_TEMPLATE, $pageId);
            foreach($regions as &$region){
                $availableSections = $this->model_teditor_teditor->getSections(CURRENT_TEMPLATE, 'available', $region['id']);
                $region['AvailableSections'] = $availableSections;
            }
            $json['Regions'] = $regions;
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
            $this->load->model('tool/image');
            $this->load->model('teditor/link_manager');

            $noImageThumb = $this->model_tool_image->resize('no_image.jpg', 235, 235);
            $json = array();
            $sectionId = (int)$_GET['SectionId'];
            $section = $this->model_teditor_teditor->getSectionById($sectionId);
            $section['Fields'] = $this->model_teditor_teditor->getSectionObjectFields($sectionId);
            $section['Collections'] = $this->model_teditor_teditor->getSectionCollections($sectionId);

            foreach ($section['Collections'] as &$collection) {
                $collection['Fields'] = $this->model_teditor_teditor->getCollectionObjectFields($collection['id']);
                foreach ($collection['Fields'] as &$field) {
                    $field['FieldVals'] = $this->getFieldVals($field['id']);
                    if($field['LookUpKey'] != "") {
                        $field['LookUpVals'] = $this->model_teditor_teditor->getLookupByLookupKey($field['LookUpKey']);
                    }
                }
            }

            foreach ($section['Fields'] as &$field) {
                $field['FieldVals'] = $this->getFieldVals($field['id']);
                if($field['LookUpKey'] != "") {
                    $field['LookUpVals'] = $this->model_teditor_teditor->getLookupByLookupKey($field['LookUpKey']);
                }
            }

            // $json['languages'] = $this->model_localisation_language->getLanguages();
            $json['section'] = $section;
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
            $this->load->model('teditor/link_manager');
            $this->load->model('tool/image');
            $noImageThumb = $this->model_tool_image->resize('no_image.jpg', 235, 235);
            $json = array();
            $sectionId = (int)$_POST['SectionId'];
            $jCollectionId = $this->model_teditor_teditor->insertCollection(0, $sectionId);
            $json['Collection'] = $this->model_teditor_teditor->getCollectionById($jCollectionId);

            $json['Collection']['Fields'] = $this->model_teditor_teditor->getCollectionObjectFields($json['Collection']['id']);
            foreach ($json['Collection']['Fields'] as &$field) {
                $field['FieldVals'] = $this->getFieldVals($field['id']);
                if($field['LookUpKey'] != "") {
                    $field['LookUpVals'] = $this->model_teditor_teditor->getLookupByLookupKey($field['LookUpKey']);
                }
            }

            $section_data = $this->model_teditor_teditor->getSectionForPreview($json['Collection']['SectionId']);
            $json['sectionId'] = $json['Collection']['SectionId'];
            $json['sectionHTML'] = $this->render_section($section_data);
            
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
            $this->load->model('teditor/teditor');
            $json = array();
            $sectionId = (int)$_POST['SectionId'];
            $this->model_teditor_teditor->deleteSection($sectionId);
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
            $this->load->model('teditor/teditor');
            $json = array();
            $cId = null;
            foreach($_POST as $collectionId => $sortOrder) {
                if(!$cId) $cId = $collectionId;
                //$json[$key] = $value;
                $this->model_teditor_teditor->updateCollectionOrder($collectionId, $sortOrder);
            }
            $sectionId = $this->model_teditor_teditor->getCollectionSection($cId);
            $section_data = $this->model_teditor_teditor->getSectionForPreview($sectionId);
            $json['sectionId'] = $sectionId;
            $json['sectionHTML'] = $this->render_section($section_data);
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
            $sectionId = $this->model_teditor_teditor->getCollectionSection($collectionId);
            $this->model_teditor_teditor->deleteCollection($collectionId);

            $section_data = $this->model_teditor_teditor->getSectionForPreview($sectionId);
            $json['sectionId'] = $sectionId;
            $json['sectionHTML'] = $this->render_section($section_data);

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
            $NoImageThumb = $this->model_tool_image->resize('no_image.jpg', 235, 235);
            if ($image) {
                $json['ImageThumb'] = $this->model_tool_image->resize($image, 235, 235);
            } else {
                $json['ImageThumb'] = $NoImageThumb;
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
            $json['ActiveLanguage'] = $this->model_localisation_language->getLanguageByCode($this->language->get('code'));
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
            $this->load->model('teditor/teditor');
            $json = array();
            $name = $_POST['Name'];
            $route = $_POST['Route'];
            $pageId = $this->model_teditor_teditor->addLayout(CURRENT_TEMPLATE, $route, $name);

            if($pageId){
                $page = $this->model_teditor_teditor->getTemplatePage(CURRENT_TEMPLATE, $pageId);
                $regions = $this->model_teditor_teditor->getTemplateRegions(CURRENT_TEMPLATE, $pageId);
                foreach($regions as &$region){
                    $userSections = $this->model_teditor_teditor->getSections(CURRENT_TEMPLATE, 'draft', $region['id']);
                    $region['UserSections'] = $userSections;
                }
                $page['Regions'] = $regions;

                $json['message'] = "Created Successfully";
                $json['Page'] = $page;
            }
            else{
                $json['message'] = "Page already exists!";
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

    private function getFieldVals($fieldId){
        $fieldVals = $this->model_teditor_teditor->getObjectFieldVals($fieldId);
        foreach($fieldVals as &$fieldVal){

            if($fieldVal['Type'] == "tags-product"){
                $fieldVal['tags-product'] = $this->model_teditor_teditor->getProducts($fieldVal['ObjectFieldId']);
            }
            else if($fieldVal['Type'] == "tags-category"){
                $fieldVal['tags-category'] = $this->model_teditor_teditor->getCategories($fieldVal['ObjectFieldId']);
            }
            else if($fieldVal['Type'] == "tags-brand"){
                $fieldVal['tags-brand'] = $this->model_teditor_teditor->getBrands($fieldVal['ObjectFieldId']);
            }
            else if($fieldVal['Type'] == "link"){
                $fieldVal['Links'] = $this->model_teditor_link_manager->getLinks($fieldVal['Value']);
            }
            else if($fieldVal['Type'] == "image"){
                $image="";
                if (isset($fieldVal['Value'])) {
                    $image = $this->model_tool_image->resize($fieldVal['Value'], 235, 235);
                } else {
                    $image = $noImageThumb;
                }
                $fieldVal['ImageThumb'] = $image;
            }

        }

        return $fieldVals;
    }

    protected function render_section($section_data){
        $section_fields = array();
        $collections = array();

        $section_fields_info = $this->model_teditor_teditor->getSectionFieldsForPreview($section_data['section_id']);
        foreach ($section_fields_info as $section_field) {
            $field_value = $section_field['field_value'];

            if ($section_field['field_type'] == 'image' && $field_value != '') {
                $field_value = \Filesystem::getUrl('image/' . $field_value);
            }

            $section_fields[$section_field['field_codename']] = array('field_id' => $section_field['field_id'], 'field_value' => $field_value);
        }

        $collections_info = $this->model_teditor_teditor->getSectionCollectionsForPreview($section_data['section_id']);

        foreach ($collections_info as $collection_row) {
            $field_value = $collection_row['field_value'];

            if ($collection_row['field_type'] == 'image' && $field_value != '') {
                $field_value = \Filesystem::getUrl('image/' . $field_value);
            }

            $collections[$collection_row['collection_id']][$collection_row['field_codename']] =
                array('field_id' => $collection_row['field_id'], 'field_value' => $field_value);
        }

        $this->data['section_id'] = $section_data['section_id'];
        $this->data['fields'] = $section_fields;
        $this->data['collections'] = $collections;
        $this->data['hide_layouts'] = true;

        //Login Display Prices
         $config_customer_price = $this->config->get('config_customer_price');

         $this->data['show_prices_login'] = true;
         if((!$config_customer_price)){
            $this->data['show_prices_login'] = false;
         }

         $pageCodeName = $this->model_teditor_teditor->getSectionPageCodeName($section_data['section_id']);
         if($pageCodeName == 'home'){
            ////////////// Category Droplist Filter
            $this->load->model('module/category_droplist');
            if($this->model_module_category_droplist->isActive()){
                $langCode = $this->config->get('config_language');

                $droplist_data = $this->config->get('category_droplist');
                $this->data['category_droplist']['status'] = true;
                $this->data['category_droplist']['levels'] = $droplist_data['levels'];
                $this->data['category_droplist']['lables'] = $droplist_data['lables'];
                $this->data['category_droplist']['title']  = $droplist_data['title'][$langCode];
                $this->data['category_droplist']['button']  = $droplist_data['button'][$langCode];
                $this->data['category_droplist']['form_url'] = $this->url->link('product/category');
                $this->data['category_droplist']['lang_code'] = $langCode;
                
                $this->data['category_droplist']['cols'] = round(12 / $droplist_data['levels']);
            }
            ///////////////////
            $this->load->model('catalog/category', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
            $categories = $this->model_catalog_category->getCategories(0);
            $this->data['categories'] = $categories;

             $this->load->model('module/product_classification/settings');
             if($this->model_module_product_classification_settings->isActive()) {
                 $this->load->model('module/product_classification/brand', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
                 $this->data['pc_brands'] = $this->model_module_product_classification_brand->getBrands();

                 $langDir = DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/language/';
                 $this->language->load_json('product/category', false, $langDir);

                 $this->data['pc_enabled'] = true;
                 $this->data['pc_form_url'] = $this->url->link('product/product_classification');


                 // get app settings
                 $pcAppsettingsData = $this->model_module_product_classification_settings->getSettings();

                 $lang_id = $this->config->get('config_language_id');
                 $this->data['pc_form_title'] = (isset($pcAppsettingsData[$lang_id]['title']) && !empty($pcAppsettingsData[$lang_id]['title'])) ? $pcAppsettingsData[$lang_id]['title'] : $this->language->get('text_pc_form_title');
                 $this->data['pc_brand_text'] = (isset($pcAppsettingsData[$lang_id]['brand']) && !empty($pcAppsettingsData[$lang_id]['brand'])) ? $pcAppsettingsData[$lang_id]['brand'] : $this->language->get('text_pc_brand');
                 $this->data['pc_model_text'] = (isset($pcAppsettingsData[$lang_id]['model']) && !empty($pcAppsettingsData[$lang_id]['model'])) ? $pcAppsettingsData[$lang_id]['model'] : $this->language->get('text_pc_model');
                 $this->data['pc_year_text'] = (isset($pcAppsettingsData[$lang_id]['year']) && !empty($pcAppsettingsData[$lang_id]['year'])) ? $pcAppsettingsData[$lang_id]['year'] : $this->language->get('text_pc_year');

             }
        }
        if (isset($this->request->get['landing_page'])) {
            $this->data['landing_page'] = $this->request->get['landing_page'];
        }

        $template_found = true;
        if(file_exists(DIR_CUSTOM_TEMPLATE . $this->config->get('config_template') . '/template/section/' . $section_data['region_codename'] . '/' . $section_data['section_codename'] . '.expand')) {
            $this->template = $this->config->get('config_template') . '/template/section/' . $section_data['region_codename'] . '/' . $section_data['section_codename'] . '.expand';
        }
        else if(file_exists(DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/view/theme/' . $this->config->get('config_template') . '/template/section/' . $section_data['region_codename'] . '/' . $section_data['section_codename'] . '.expand')) {
            $this->template = $this->config->get('config_template') . '/template/section/' . $section_data['region_codename'] . '/' . $section_data['section_codename'] . '.expand';
        }
        else {
            return;
        }

        return $this->render_section_ecwig();
    }

    protected function render_section_ecwig()
    {
        $loader = new Twig_Loader_Filesystem([DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/view/theme/', DIR_CUSTOM_TEMPLATE]);

        extract($this->data);

        if(DEV_MODE) {
            $twig = new Twig_Environment($loader, array(
                'autoescape' => false,
                'debug' => true
            ));
            $twig->addExtension(new Twig_Extension_Debug());
        } else {
            $twig = new Twig_Environment($loader, array(
                'autoescape' => false,
                'cache' => $dirTemplate . 'cache',
                'debug' => false
            ));
        }

        $twig->addExtension(new Twig_Extension_ExpandcartGlobals($this->registry)); //$expandishglobals);
        $twig->addExtension(new Twig_Extension_Expandcart($this->registry, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/view/theme/'));
        $twig->addExtension(new Twig_Extension_ExpandcartCategory($this->registry));
        $twig->addExtension(new Twig_Extension_ExpandcartProduct($this->registry));
        $twig->addExtension(new Twig_Extension_ExpandcartHtmlEntityDecode);
        $twig->addFilter(new \Twig_SimpleFilter('htmlspecialchars_decode', 'htmlspecialchars_decode'));
        $twig->addFilter(new \Twig_SimpleFilter('strip_tags', 'strip_tags'));

        $template = $twig->load($this->template);

        $this->output = $template->render($this->data);
      
        return $this->output;
    }
}