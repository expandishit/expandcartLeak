<?php
class ControllerMeditorMeditor extends Controller {

    public function index() {

        if ( ! $this->user->hasPermission('modify', 'meditor/meditor') && ! $this->user->hasPermission('access', 'meditor/meditor'))
        {
            $this->response->redirect($this->url->link('user/user_permission', '', 'SSL'));
        }

        $this->language->load('setting/setting');

        $this->load->model('meditor/meditor');
        $this->load->model('marketplace/appservice');
        $mobile_apps = $this->model_marketplace_appservice->getInstalledMobileApps();
        $data['product_id'] = PRODUCTID;

        #required for paid apps/services
        $this->load->model('billingaccount/common');
        $timestamp = time(); # Get current timestamp
        $email = BILLING_DETAILS_EMAIL; # Clients Email Address to Login

        # Define WHMCS URL & AutoAuth Key
        $whmcsurl = MEMBERS_LINK;
        $autoauthkey = MEMBERS_AUTHKEY;
        $hash = sha1($email.$timestamp.$autoauthkey); # Generate Hash

        $billingAccess = '0';

        if ($this->user->hasPermission('access', 'billingaccount/plans') && ENABLE_BILLING == '1') {
            $billingAccess = '1';
        }

        $data['hasBillingAccess'] = $billingAccess == 1 ? '1' : '0';

        $tmpbuylink = 'cart.php?a=add';
        $tmpbuylink = ($this->language->get('code') == 'ar') ? $tmpbuylink . '&language=Arabic' : $tmpbuylink . '&language=English';

        if (PRODUCTID == 6 || PRODUCTID == 53) {
            $tmpbuylink = $tmpbuylink . '&promocode=MOBULTIMATE';
            $price = 399;
        } elseif (PRODUCTID == 8 || PRODUCTID == 50) {
            $tmpbuylink = $tmpbuylink . '&promocode=MOBENTERPRISE';
            $price = 0;
        } else {
            $price = 499;
        }
        //elseif (PRODUCTID == 4)
        //    $tmpbuylink = $tmpbuylink . '&promocode=MOBBUSINESS';

        $buyAndroidLink = ($billingAccess == "1") ? $whmcsurl . "?email=$email&timestamp=$timestamp&hash=$hash&goto=" . urlencode($tmpbuylink . '&pid=15') : '#';
        $buyIOSLink = ($billingAccess == "1") ? $whmcsurl . "?email=$email&timestamp=$timestamp&hash=$hash&goto=" . urlencode($tmpbuylink . '&pid=43') : '#';
        $buyAllLink = ($billingAccess == "1") ? $whmcsurl . "?email=$email&timestamp=$timestamp&hash=$hash&goto=" . urlencode($tmpbuylink . '&bid=2') : '#';

        $data['buylink'] = array(
            'android' => $buyAndroidLink,
            'ios' => $buyIOSLink,
            'all' => $buyAllLink
        );
        $data['currentMobileApps'] = $mobile_apps;
        $data['token'] = $this->session->data['token'];
        $data['langcode'] = $this->language->get('code');

        $this->data = $data;
        $this->data['title'] = "Mobile Editor";
        $this->template = 'meditor/meditor.expand';
        //$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        //$this->document->setBasehref($actual_link);
        $this->base = 'common/base';

        $this->response->setOutput($this->render_ecwig());
    }

    public function index1() {
        $data['token'] = $this->session->data['token'];
        $data['langcode'] = $this->language->get('code');

        $this->data = $data;
        $this->data['title'] = "Mobile Editor";
        $this->load->model('meditor/meditor');
        $this->model_meditor_meditor->executeDefaultSettings();
        //$this->template = 'teditor/index.html';

        //$this->response->setOutput($this->render());
    }

    public function getTemplateStructure() {
        if(1 == 1) {
            $this->load->model('meditor/meditor');
            $json = array();
            $template = $this->model_meditor_meditor->getTemplate('mobile-app');
            if(count($template) <= 0) {
                $this->model_meditor_meditor->executeDefaultSettings();
                $template = $this->model_meditor_meditor->getTemplate('mobile-app');
            }
            $json['Template'] = $template;
            $json['Pages'] = $this->model_meditor_meditor->getTemplatePages('mobile-app');
            $json['Regions'] = $this->model_meditor_meditor->getTemplateRegions('mobile-app');
            $this->model_meditor_meditor->prepareDraftVersion('mobile-app');
            $json['UserSections'] = $this->model_meditor_meditor->getSections('mobile-app', "draft");
            $json['Layouts'] = $this->model_meditor_meditor->getLayouts();
            //$json['Collections'] = $this->model_meditor_meditor->getTemplateCollections('mobile-app', "live");
            //$json['Fields'] = $this->model_meditor_meditor->getTemplateFields('mobile-app', "live");
            //$json['FieldsVal'] = $this->model_meditor_meditor->getTemplateFieldsVal('mobile-app', "live");
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
        if(1 == 1) {
            $this->load->model('meditor/meditor');
            $json = array();
            $this->model_meditor_meditor->publishTemplate('mobile-app');
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
        if(1 == 1) {
            $this->load->model('meditor/meditor');
            $json = array();
            $this->model_meditor_meditor->resetDraftVersion('mobile-app');
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
        if(1 == 1 && isset($_POST['LangCode'])) {
            $this->load->model('meditor/meditor');
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
        if(1 == 1
            && isset($_POST['sourceSectionId'])
            && isset($_POST['sourceType'])
            && isset($_POST['destType'])
            && isset($_POST['destSortOrder'])) {
            $this->load->model('meditor/meditor');
            $json = array();
            $sourceSectionId = (int)$_POST['sourceSectionId'];
            $sourceType = $_POST['sourceType'];
            $destType = $_POST['destType'];
            $destSortOrder = (int)$_POST['destSortOrder'];

            $insertedSectionId =  $this->model_meditor_meditor->insertSection($sourceSectionId, $sourceType, $destType, $destSortOrder);
            $json['insertedSectionId'] = $insertedSectionId;

            //$json['UserSections'] = $this->model_meditor_meditor->getSections('mobile-app', "live");
            //$json['Collections'] = $this->model_meditor_meditor->getTemplateCollections('mobile-app', "live");
            //$json['Fields'] = $this->model_meditor_meditor->getTemplateFields('mobile-app', "live");
            //$json['FieldsVal'] = $this->model_meditor_meditor->getTemplateFieldsVal('mobile-app', "live");
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
        if(1 == 1 && isset($_POST)) {
            $this->load->model('meditor/meditor');
            $json = array();
            foreach($_POST as $key => $value) {
                //$json[$key] = $value;
                $this->model_meditor_meditor->saveFieldValById($key, $value);

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

    public function getRegionAvailableSections() {
        if(1 == 1 && isset($_POST['RegionId'])) {
            $this->load->model('meditor/meditor');
            $json = array();
            $regionId = (int)$_POST['RegionId'];
            $json['AvailableSections'] = $this->model_meditor_meditor->getSections('mobile-app', "available", $regionId);
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
        if(1 == 1 && isset($_GET['SectionId'])) {
            $this->load->model('meditor/meditor');
            $this->load->model('localisation/language');
            $this->load->model('catalog/information');
            $json = array();
            $sectionId = (int)$_GET['SectionId'];
            $json['Section'] = $this->model_meditor_meditor->getSectionById($sectionId);
            $json['Collections'] = $this->model_meditor_meditor->getSectionCollections($sectionId);
            $json['Fields'] = $this->model_meditor_meditor->getAllSectionObjectFields($sectionId);
            $json['FieldsVal'] = $this->model_meditor_meditor->getAllSectionObjectFieldsVal($sectionId);
            $json['ReqMapData'] = array();
            $produsts = array();
            $categories = array();
            $brands = array();
            $images = array();
            $links = array();

            foreach ($json['Fields'] as $field) {
                if($field['Type'] == "tags-product"){
                    $produsts = array_merge($produsts, $this->model_meditor_meditor->getProducts($field['id']));
                }
                else if($field['Type'] == "tags-category"){
                    $categories = array_merge($categories, $this->model_meditor_meditor->getCategories($field['id']));
                }
                else if($field['Type'] == "tags-brand"){
                    $brands = array_merge($brands, $this->model_meditor_meditor->getBrands($field['id']));
                }
                else if($field['LookUpKey'] != "" && !isset($json['ReqMapData'][$field['LookUpKey']])) {
                    $json['ReqMapData'][$field['LookUpKey']] = $this->model_meditor_meditor->getLookupByLookupKey($field['LookUpKey']);
                }
            }

            foreach ($json['FieldsVal'] as $fieldval) {
                if(strpos($fieldval['Value'], "product:") === 0) {
                    $produsts = array_merge($produsts, $this->model_meditor_meditor->getProducts(str_replace("product:", "", $fieldval['Value']), true));
                } else if(strpos($fieldval['Value'], "category:") === 0) {
                    $categories = array_merge($categories, $this->model_meditor_meditor->getCategories(str_replace("category:", "", $fieldval['Value']), true));
                }
                else if(strpos($fieldval['Value'], "link:") === 0) {
                    $links = array_merge($links, $this->model_meditor_meditor->getLinks(str_replace("link:", "", $fieldval['Value']), true));
                }
            }

            $this->load->model('tool/image');
            $imageFieldsVal = $this->model_meditor_meditor->getSectionImages($sectionId);
            // $noImageThumb = $this->model_tool_image->resize('no_image.jpg', 235, 235);
            foreach ($imageFieldsVal as $fieldVal) {
                $image="";
                $image = $this->model_tool_image->resize($fieldVal['Value'], 235, 235);
                $fieldVal['ImageThumb'] = $image;
                $images[] = $fieldVal;
            }

            $json['ReqMapData']['tags-product'] = $produsts;
            $json['ReqMapData']['tags-category'] = $categories;
            $json['ReqMapData']['tags-link'] = $links;
            $json['ReqMapData']['images'] = $images;
            $json['ReqMapData']['noimagethumb'] = $noImageThumb;
            $json['ReqMapData']['languages'] = $this->model_localisation_language->getLanguages();
            $json['ReqMapData']['InfoPages'] = array();
            $results = $this->model_catalog_information->getInformations();
            foreach ($results as $result) {
                $json['ReqMapData']['InfoPages'][] = array(
                    'page_id' => $result['information_id'],
                    'page_name'          => $result['title']
                );
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

    public function AddCollection() {
        if(1 == 1 && isset($_POST['SectionId'])) {
            $this->load->model('meditor/meditor');
            $json = array();
            $sectionId = (int)$_POST['SectionId'];
            $json['CollectionId'] = $this->model_meditor_meditor->insertCollection(0, $sectionId);
            $json['Collection'] = $this->model_meditor_meditor->getCollectionById($json['CollectionId']);
            $json['Fields'] = $this->model_meditor_meditor->getCollectionObjectFields($json['CollectionId']);
            $json['FieldsVal'] = $this->model_meditor_meditor->getCollectionObjectFieldsVal($json['CollectionId']);
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
        if(1 == 1 && isset($_POST)) {
            $this->load->model('meditor/meditor');
            $json = array();
            foreach($_POST as $sectionId => $sortOrder) {
                //$json[$key] = $value;
                $this->model_meditor_meditor->updateSectionOrder($sectionId, $sortOrder);
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
        if(1 == 1 && isset($_POST['SectionId'])) {
            $this->load->model('meditor/meditor');
            $json = array();
            $sectionId = (int)$_POST['SectionId'];
            $this->model_meditor_meditor->deleteSection($sectionId);
            $json['UserSections'] = $this->model_meditor_meditor->getSections('mobile-app', "draft");
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
        if(1 == 1 && isset($_POST)) {
            $this->load->model('meditor/meditor');
            $json = array();
            foreach($_POST as $collectionId => $sortOrder) {
                //$json[$key] = $value;
                $this->model_meditor_meditor->updateCollectionOrder($collectionId, $sortOrder);
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
        if(1 == 1 && isset($_POST['CollectionId'])) {
            $this->load->model('meditor/meditor');
            $json = array();
            $collectionId = (int)$_POST['CollectionId'];
            $this->model_meditor_meditor->deleteCollection($collectionId);

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
        if(1 == 1 && isset($_GET['LookupKey'])) {
            $this->load->model('meditor/meditor');
            $json = array();
            $lookupKey = $_GET['LookupKey'];
            $json['Lookup'] = $this->model_meditor_meditor->getLookupByLookupKey($lookupKey);
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
        if(1 == 1 && isset($_GET['Image'])) {
            $this->load->model('tool/image');
            $json = array();
            $image = $_GET['Image'];
            $json['ImageThumb'] = '';
            $json['ImageThumb'] = $this->model_tool_image->resize($image, 235, 235);
            $json['NoImageThumb'] = $this->model_tool_image->resize('no_image.jpg', 235, 235);
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
        if(1 == 1 && isset($_GET['ProductName'])) {
            $this->load->model('meditor/meditor');
            $json = array();
            $str = $_GET['ProductName'];
            $json['Products'] = $this->model_meditor_meditor->searchProducts($str);
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
        if(1 == 1 && isset($_GET['CategoryName'])) {
            $this->load->model('meditor/meditor');
            $json = array();
            $str = $_GET['CategoryName'];
            $json['Categories'] = $this->model_meditor_meditor->searchCategories($str);
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

    public function searchLinks() {
        if(isset($_GET['linkName'])) {
            $this->load->model('meditor/meditor');
            $json = array();
            $str = $_GET['linkName'];
            $json['Links'] = $this->model_meditor_meditor->searchLinks($str);
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
        if(1 == 1 && isset($_POST['ObjectType']) && isset($_POST['ObjectId']) && isset($_POST['NewObjectName'])) {
            $this->load->model('meditor/meditor');
            $json = array();

            if($_POST['ObjectType'] == "Section")
                $this->model_meditor_meditor->updateSectionName((int)$_POST['ObjectId'], $_POST['NewObjectName']);
            else if($_POST['ObjectType'] == "Collection")
                $this->model_meditor_meditor->updateCollectionName((int)$_POST['ObjectId'], $_POST['NewObjectName']);
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
        if(1 == 1 && isset($_POST['SectionId']) && isset($_POST['State'])) {
            $this->load->model('meditor/meditor');
            $json = array();

            $this->model_meditor_meditor->updateSectionState((int)$_POST['SectionId'], $_POST['State']);

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
        if(1 == 1) {
            $this->load->model('localisation/language');
            $json['Languages'] = $this->model_localisation_language->getLanguages();
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
        if(1 == 1 && isset($_POST['Name']) && isset($_POST['Route'])) {
            $this->load->model('meditor/meditor');
            $json = array();
            $name = $_POST['Name'];
            $route = $_POST['Route'];
            $this->model_meditor_meditor->addLayout('mobile-app', $route, $name);

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

    //Themes
    public function template()
    {
        $this->language->load('setting/setting');

        $this->initializer([
            'setting/template_mobile'
        ]);

        $this->document->setTitle($this->language->get('template_heading_title'));

        $this->data['direction'] = $this->language->get('direction');

        $this->load->model('setting/setting');
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_meditor'),
            'href' => $this->url->link('meditor/meditor', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('template_heading_title'),
            'href' => $this->url->link('setting/template', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['config_mobile_template'] = $this->config->get('config_mobile_template') ?? 'default';

        $categories = null;
        if (isset($this->request->get['categories']) && count($this->request->get['categories']) > 0) {
            $categories = $this->data['selectedCategories'] = $this->request->get['categories'];
        }

        $page = 1;
        $perPage = 9;
        $offset = 0;
        if (isset($this->request->get['page']) && $this->request->get['page'] > 1) {
            $page = $this->request->get['page'];

            $offset = ($page - 1) * $perPage;
        }

        $filter = [
            'categories' => $categories,
            'limit' => $perPage,
            'offset' => $offset
        ];

        $this->data['categories'] = [
            'business',
            'electronics',
        ];

        $templates = $this->template_mobile->getTemplates($filter, $this->language->get('code'));

        $this->data['currentTemplate'] = $templates['data'][$this->config->get('config_mobile_template')];
        $this->data['templates']['nextgen'] = $templates['data'];
        $this->data['imageBase'] = HTTP_CATALOG . 'image/templates/mobile/';

        $get = $this->request->get;
        unset($get['page']);

        $pagination = new Pagination();
        $pagination->total = $templates['total'];
        $pagination->page = $page;
        $pagination->limit = $perPage;
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link('setting/template', http_build_query($get) . '&page={page}', 'SSL');

        $this->data['pagination'] = $pagination->render();

        $this->template = 'meditor/template.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function getFilteredTemplates()
    {
        if ( $this->request->server['REQUEST_METHOD'] != 'POST' )
        {
            $this->index();
            return;
        }

        $this->language->load('setting/setting');
        $categories = $this->request->post['categories'] ?: null;

        $page = 1;
        $perPage = 9;
        $offset = 0;

        if ( isset( $this->request->get['page'] ) && $this->request->get['page'] > 1 )
        {
            $page = $this->request->get['page'];
            $offset = $filter['offset'] = ($page - 1) * $perPage;
            $filter['limit'] = $perPage;
        }

        $this->load->model('setting/template_mobile');

        $filter['categories'] = $categories;

        $templates = $this->model_setting_template_mobile->getTemplates( $filter, $this->language->get('code') );
        $currentTemplate = $this->config->get('config_mobile_template');

        $this->data['imageBase'] = HTTP_CATALOG . 'image/templates/';
        $this->data['templates'] = $templates['data'];
        $this->data['config_mobile_template'] = $currentTemplate;

        $get = $this->request->get;
        unset($get['page']);

        if ( $templates['total'] > $perPage )
        {
            $pagination = new Pagination();
            $pagination->total = $templates['total'];
            $pagination->page = $page;
            $pagination->limit = $perPage;
            $pagination->text = $this->language->get('text_pagination');
            $pagination->url = $this->url->link('setting/template', http_build_query($get) . '&page={page}', 'SSL');

            $this->data['pagination'] = $pagination->render();
        }

        $this->template = 'meditor/template_list_snippet.expand';

        $output = $this->render_ecwig();

        $this->response->setOutput($output);

        return;

    }

    //Reload theme if josn changes or custom theme added
    public function reloadTheme() {
        if(isset($this->request->get['basename']) && ($this->request->get['basename'] == 'default' || ($this->request->get['basename'] != 'default' && is_dir(DIR_APPLICATION."model/meditor/customtheme/".$this->request->get['basename'])))){
            $templateName = $this->request->get['basename'];
            $this->load->model('setting/setting');
            $this->model_setting_setting->insertUpdateSetting('config', ['config_mobile_template' => $templateName]);
        }else{
            $data['error'] = "true";
            $this->response->setOutput(json_encode($data));
            return;
        }

        $this->load->model('meditor/meditor');
        $this->model_meditor_meditor->executeDefaultSettings($templateName);

        if(isset($this->request->get['src']) && $this->request->get['src'] == 'ajax'){
            $data['success'] = "true";
            $data['refresh'] = '1';
            $this->response->setOutput(json_encode($data));
            return;
        }

        $template = $this->model_meditor_meditor->getTemplate('mobile-app');
        $this->response->redirect($this->url->link('meditor/meditor', '', true));
    }

    //Reload theme if josn changes or custom theme added
    public function updateTheme() {
        $templateName = $this->request->get['basename'];

        $this->load->model('meditor/meditor');
        $this->model_meditor_meditor->updateSettings($templateName);

        if(isset($this->request->get['src']) && $this->request->get['src'] == 'ajax'){
            $data['success'] = "true";
            $data['refresh'] = '1';
            $this->response->setOutput(json_encode($data));
            return;
        }

        $template = $this->model_meditor_meditor->getTemplate('mobile-app');
        $this->response->redirect($this->url->link('meditor/meditor', '', true));
    }
}