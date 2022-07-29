<?php
class Expandish
{
    private $_templateCodeName;
    private $_route;
    private $_pageCodeName;
    private $_pageId;
    private $_type;
    private $_langCode;
    private $db;
    private $config;
    private $_regions;
    private $_headerRegion;
    private $_footerRegion;
    private $_sections;
    private $_fields;
    private $_collections;
    private $_pageModules;


    public function __construct($registry, $route) {
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
        $load = $registry->get('load');
        $request = $registry->get('request');
 
       	
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '0' AND `group` = 'config' and `key` ='config_language'");
		$result=$query->row;
		
        $this->_langCode = isset($_GET['draftlangcode']) ? $_GET['draftlangcode'] : ($this->config->get('config_language') ?: $result['value']);
       
        if (isset($request->get['__p'])) {
            $template_codename = $request->get['__p'];
        }
        else{
            $template_codename = CURRENT_TEMPLATE;
        }
        
        $this->_templateCodeName = $template_codename;
        $this->_route = $route;
        $this->LoadPage();


        $section_type = 'live';
        $load->library('user');
        $user = new User($registry);
        if ($user->isLogged() && isset($request->get['isdraft']))
            if ($request->get['isdraft'] == "1")
                $section_type = 'draft';

        $this->_type = $section_type;

        $this->_regions = $this->loadRegions();
        $this->_sections = $this->loadSections();
        $this->_fields = $this->loadFields();
        $this->_collections = $this->loadCollections();
        $this->loadHeaderFooterRegions();
        return;
    }

    private function LoadPage() {
        $this->_pageCodeName = $this->_route == 'common/home' ? 'home' : 'general';

        $query = $this->db->query("SELECT
                                     ecpage.id page_id, 
                                     ecpage.Route page_route
                                   FROM ecpage
                                   JOIN ectemplate ON ecpage.TemplateId = ectemplate.id
                                   WHERE ecpage.CodeName = '" . $this->_pageCodeName . "'
                                   AND ectemplate.CodeName = '" . $this->_templateCodeName . "'");
        if($this->_pageCodeName == 'home') {
            $this->_pageId = $query->row['page_id'];
        } else {
            $Pages = $query->rows;
            foreach ($Pages as $page) {
                if($page['page_route'] == '') {
                    $this->_pageId = $page['page_id'];
                } else {
                    if(substr($this->_route, 0, strlen($page['page_route'])) === $page['page_route']) {
                        $this->_pageId = $page['page_id'];
                        break;
                    }
                }
            }
        }
    }

    private function loadRegions() {

        $query = $this->db->query("SELECT
                                     ecregion.id region_id,
                                     ecregion.CodeName region_codename
                                   FROM ecregion
                                   JOIN ecpage ON ecregion.PageId = ecpage.id
                                   JOIN ectemplate ON ecpage.TemplateId = ectemplate.id
                                   WHERE ecpage.id = " . $this->_pageId . "
                                   AND ectemplate.CodeName = '" . $this->_templateCodeName . "'
                                   AND ecregion.CodeName NOT IN ('header', 'footer')
                                   ORDER BY ecregion.id");

        if ($query->rows) {
            return $query->rows;
        } else {
            return array();
        }
    }

    private function loadSections() {
        $query = $this->db->query("SELECT
                                     ecregion.id region_id,
                                     ecregion.CodeName region_codename,
                                     ecsection.id section_id,
                                     ecsection.CodeName section_codename,
                                     ecsection.State section_state
                                   FROM ecsection
                                   JOIN ecregion ON ecsection.RegionId = ecregion.id
                                   JOIN ecpage ON ecregion.PageId = ecpage.id
                                   JOIN ectemplate ON ecpage.TemplateId = ectemplate.id
                                   WHERE ecsection.Type = '" . $this->_type . "'
                                   AND ecpage.id = " . $this->_pageId . "
                                   AND ectemplate.CodeName = '" . $this->_templateCodeName . "'
                                   AND ecregion.CodeName NOT IN ('header', 'footer')
                                   ORDER BY
                                     ecregion.id,
                                     ecsection.SortOrder");

        if ($query->rows) {
            return $query->rows;
        } else {
            return array();
        }
    }

    private function loadFields()
    {
        $query = $this->db->query("SELECT
                                        ecobjectfield.id field_id,
                                        ecobjectfield.CodeName field_codename,
                                        ecobjectfield.Type field_type,
                                        ecobjectfieldval.`Value` field_value,
                                        ecsection.id section_id,
                                        ecsection.CodeName section_codename,
                                        ecregion.id region_id,
                                        ecregion.CodeName region_codename
                                    FROM
                                        ecobjectfield
                                    JOIN ecobjectfieldval ON ecobjectfieldval.ObjectFieldId = ecobjectfield.id
                                    JOIN ecsection ON ecobjectfield.ObjectId = ecsection.id AND ecobjectfield.ObjectType = 'ECSECTION'
                                    JOIN ecregion ON ecsection.RegionId = ecregion.id
                                    JOIN ecpage ON ecregion.PageId = ecpage.id
                                    JOIN ectemplate ON ecpage.TemplateId = ectemplate.id
                                    WHERE ecsection.Type = '" . $this->_type . "'
                                    AND ecpage.id = " . $this->_pageId . "
                                    AND ectemplate.CodeName = '" . $this->_templateCodeName . "'
                                    AND ecregion.CodeName NOT IN ('header', 'footer')
                                    AND (
                                        ecobjectfieldval.Lang = '" . $this->_langCode . "'
                                        OR ecobjectfieldval.Lang = ''
                                    )");

        if ($query->rows) {
            return $query->rows;
        } else {
            return array();
        }
    }

    private function loadCollections() {
        $query = $this->db->query("SELECT
                                        ecobjectfield.id field_id,
                                        ecobjectfieldval.`Value` field_value,
                                        ecobjectfield.CodeName field_codename,
                                        ecobjectfield.Type field_type,
                                        eccollection.id collection_id,
                                        ecsection.id section_id,
                                        ecsection.CodeName section_codename,
                                        ecregion.id region_id,
                                        ecregion.CodeName region_codename
                                    FROM
                                        ecobjectfield
                                    JOIN ecobjectfieldval ON ecobjectfieldval.ObjectFieldId = ecobjectfield.id
                                    JOIN eccollection ON ecobjectfield.ObjectId = eccollection.id AND ecobjectfield.ObjectType = 'ECCOLLECTION'
                                    JOIN ecsection ON eccollection.SectionId = ecsection.id
                                    JOIN ecregion ON ecsection.RegionId = ecregion.id
                                    JOIN ecpage ON ecregion.PageId = ecpage.id
                                    JOIN ectemplate ON ecpage.TemplateId = ectemplate.id
                                    WHERE ecsection.Type = '" . $this->_type . "'
                                    AND ecpage.id = " . $this->_pageId . "
                                    AND ectemplate.CodeName = '" . $this->_templateCodeName . "'
                                    AND eccollection.IsDefault = '0'                                
                                    AND ecregion.CodeName NOT IN ('header', 'footer')
                                    AND (
                                        ecobjectfieldval.Lang = '" . $this->_langCode . "'
                                        OR ecobjectfieldval.Lang = ''
                                    )
                                    ORDER BY
                                        eccollection.SortOrder");

        if ($query->rows) {
            return $query->rows;
        } else {
            return array();
        }
    }

    private function loadHeaderFooterRegions() {
        $fieldsquery = $this->db->query("SELECT
                                        ecobjectfield.id field_id,
                                        ecobjectfield.CodeName field_codename,
                                        ecobjectfield.Type field_type,
                                        ecobjectfieldval.`Value` field_value,
                                        ecsection.id section_id,
                                        ecsection.CodeName section_codename,
                                        ecsection.State section_state,
                                        ecregion.id region_id,
                                        ecregion.CodeName region_codename
                                    FROM
                                        ecobjectfield
                                    JOIN ecobjectfieldval ON ecobjectfieldval.ObjectFieldId = ecobjectfield.id
                                    JOIN ecsection ON ecobjectfield.ObjectId = ecsection.id AND ecobjectfield.ObjectType = 'ECSECTION'
                                    JOIN ecregion ON ecsection.RegionId = ecregion.id
                                    JOIN ecpage ON ecregion.PageId = ecpage.id
                                    JOIN ectemplate ON ecpage.TemplateId = ectemplate.id
                                    WHERE ecsection.Type = '" . $this->_type . "'
                                    AND ecpage.CodeName = 'home'
                                    AND ectemplate.CodeName = '" . $this->_templateCodeName . "'
                                    AND ecregion.CodeName IN ('header', 'footer')
                                    AND (
                                        ecobjectfieldval.Lang = '" . $this->_langCode . "'
                                        OR ecobjectfieldval.Lang = ''
                                    )");
        $fields = $fieldsquery->rows;

        $collectionsquery = $this->db->query("SELECT
                                                    ecobjectfield.id field_id,
                                                    ecobjectfieldval.`Value` field_value,
                                                    ecobjectfield.CodeName field_codename,
                                                    ecobjectfield.Type field_type,
                                                    eccollection.id collection_id,
                                                    ecsection.id section_id,
                                                    ecsection.CodeName section_codename,
                                                    ecsection.State section_state,
                                                    ecregion.id region_id,
                                                    ecregion.CodeName region_codename
                                                FROM
                                                    ecobjectfield
                                                JOIN ecobjectfieldval ON ecobjectfieldval.ObjectFieldId = ecobjectfield.id
                                                JOIN eccollection ON ecobjectfield.ObjectId = eccollection.id AND ecobjectfield.ObjectType = 'ECCOLLECTION'
                                                JOIN ecsection ON eccollection.SectionId = ecsection.id
                                                JOIN ecregion ON ecsection.RegionId = ecregion.id
                                                JOIN ecpage ON ecregion.PageId = ecpage.id
                                                JOIN ectemplate ON ecpage.TemplateId = ectemplate.id
                                                WHERE ecsection.Type = '" . $this->_type . "'
                                                AND ecpage.CodeName = 'home'
                                                AND ectemplate.CodeName = '" . $this->_templateCodeName . "'
                                                AND eccollection.IsDefault = '0'                                
                                                AND ecregion.CodeName IN ('header', 'footer')
                                                AND (
                                                    ecobjectfieldval.Lang = '" . $this->_langCode . "'
                                                    OR ecobjectfieldval.Lang = ''
                                                )
                                                ORDER BY
                                                    eccollection.SortOrder");
        $collections = $collectionsquery->rows;


        $headerRegion = array();
        $footerRegion = array();
        foreach ($fields as $field) {
            $field1 = array();
            $field1['id'] = $field['field_id'];
            $field1['type'] = $field['field_type'];
            $field1['value'] = $field['field_value'];

            if($field['region_codename'] == 'header') {
                if(!isset($headerRegion[$field['section_codename']]['section_state'])) {
                    $headerRegion[$field['section_codename']]['section_state'] = $field['section_state'];
                    $headerRegion[$field['section_codename']]['section_id'] = $field['section_id'];
                }
                if(!isset($headerRegion[$field['section_codename']]['fields'])) {
                    $headerRegion[$field['section_codename']]['fields'] = array();
                }
                $headerRegion[$field['section_codename']]['fields'][$field['field_codename']] = array();
                $headerRegion[$field['section_codename']]['fields'][$field['field_codename']] = $field1;
            }
            elseif($field['region_codename'] == 'footer') {
                if(!isset($footerRegion[$field['section_codename']]['section_state'])) {
                    $footerRegion[$field['section_codename']]['section_state'] = $field['section_state'];
                    $footerRegion[$field['section_codename']]['section_id'] = $field['section_id'];
                }
                if(!isset($footerRegion[$field['section_codename']]['fields'])) {
                    $footerRegion[$field['section_codename']]['fields'] = array();
                }
                $footerRegion[$field['section_codename']]['fields'][$field['field_codename']] = array();
                $footerRegion[$field['section_codename']]['fields'][$field['field_codename']] = $field1;
            }
        }

        foreach ($collections as $collection) {
            $field = array();
            $field['id'] = $collection['field_id'];
            $field['type'] = $collection['field_type'];
            $field['value'] = $collection['field_value'];

            if($collection['region_codename'] == 'header') {
                if(!isset($headerRegion[$collection['section_codename']]['section_state'])) {
                    $headerRegion[$collection['section_codename']]['section_state'] = $collection['section_state'];
                    $headerRegion[$collection['section_codename']]['section_id'] = $collection['section_id'];
                }
                if(!isset($headerRegion[$collection['section_codename']]['collections'])) {
                    $headerRegion[$collection['section_codename']]['collections'] = array();
                }
                if(!isset($headerRegion[$collection['section_codename']]['collections'][$collection['collection_id']]))
                    $headerRegion[$collection['section_codename']]['collections'][$collection['collection_id']] = array();
                $headerRegion[$collection['section_codename']]['collections'][$collection['collection_id']][$collection['field_codename']] = $field;
            }
            elseif($collection['region_codename'] == 'footer') {
                if(!isset($footerRegion[$collection['section_codename']]['section_state'])) {
                    $footerRegion[$collection['section_codename']]['section_state'] = $collection['section_state'];
                    $footerRegion[$collection['section_codename']]['section_id'] = $collection['section_id'];
                }
                if(!isset($footerRegion[$collection['section_codename']]['collections'])) {
                    $footerRegion[$collection['section_codename']]['collections'] = array();
                }
                if(!isset($footerRegion[$collection['section_codename']]['collections'][$collection['collection_id']]))
                    $footerRegion[$collection['section_codename']]['collections'][$collection['collection_id']] = array();
                $footerRegion[$collection['section_codename']]['collections'][$collection['collection_id']][$collection['field_codename']] = $field;
            }
        }

        $this->_headerRegion = $headerRegion;
        $this->_footerRegion = $footerRegion;
    }

    public function setPageModules($module_data) {
        $this->_pageModules = $module_data;
    }

    public function getPageModules() {
        return $this->_pageModules;
    }

    public function getRegions() {
        return $this->_regions;
    }

    public function getRegionSections($regionCodeName) {
        $sections = array();

        foreach($this->_sections as $section) {
            if($section['region_codename'] == $regionCodeName) {
                $sections[] = $section;
            }
        }

        return $sections;
    }

    public function getSectionFields($sectionId) {
        $fields = array();

        foreach($this->_fields as $field) {
            if($field['section_id'] == $sectionId) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * Return just the value of a field
     * @author MohamedHassanWD
     * @version 1.0
     * @param String $field_codename The code name of the field to get it's value
     * @return String The value of the field
     */
    public function getSectionFieldValue($field_codename=''){
        $value = '';
        $sql = "SELECT DISTINCT
                    ecobjectfieldval.`Value` field_value
                FROM
                    ecobjectfield
                JOIN ecobjectfieldval ON ecobjectfieldval.ObjectFieldId = ecobjectfield.id
                JOIN ecsection ON ecobjectfield.ObjectId = ecsection.id AND ecobjectfield.ObjectType = 'ECSECTION'
                WHERE ecsection.CodeName = '" . $field_codename . "'
                AND ecobjectfield.CodeName = 'SideAd1_Check'
            ";
        $query = $this->db->query($sql);

        if ($query->row) {
            return $query->row['field_value'];
        } else {
            return;
        }
    }

    public function getSectionCollections($sectionId) {
        $collections = array();

        foreach($this->_collections as $collection) {
            if($collection['section_id'] == $sectionId) {
                $collections[] = $collection;
            }
        }

        return $collections;
    }

    public function getHeader() {
        return $this->_headerRegion;
    }

    public function getFooter() {
        return $this->_footerRegion;
    }

    public function getRoute(){
        return $this->_route;
    }

    public function getPageCodeName() {
        return $this->_pageCodeName;
    }
}