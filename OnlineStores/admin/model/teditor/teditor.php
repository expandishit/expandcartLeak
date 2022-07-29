<?php
use ExpandCart\Foundation\String\Slugify as Slugify;
class ModelTeditorTeditor extends Model {
    public function getTemplate($templateCodeName) {
        $query = $this->db->query("SELECT code FROM `language`");
        $langs = $query->rows;
        foreach ($langs as $lang) {
            if($lang['code'] != "en") {
                $this->db->query("INSERT INTO ecobjectfieldval (`Value`, Lang, ObjectFieldId)
                                  SELECT DISTINCT ecobjectfieldval.`Value`, '" . $lang['code'] . "', ecobjectfieldval.ObjectFieldId 
                                  FROM ecobjectfieldval
                                  LEFT JOIN ecobjectfieldval destval ON ecobjectfieldval.ObjectFieldId = destval.ObjectFieldId AND destval.Lang = '" . $lang['code'] . "'
                                  WHERE destval.id IS NULL AND ecobjectfieldval.Lang = 'en'");
            }
        }

        $query = $this->db->query("SELECT
                                        ectemplate.id,
                                        ectemplate.CodeName,
                                        ectemplatedesc.`Name`,
                                        ectemplatedesc.Description,
                                        ectemplatedesc.Image,
                                        ectemplatedesc.Demourl
                                        FROM `ectemplate`
                                        INNER JOIN `ectemplatedesc` ON `ectemplate`.`id` = `ectemplatedesc`.`TemplateId`
                                        WHERE `ectemplate`.`CodeName` = '" . $templateCodeName . "'
                                        AND `ectemplatedesc`.`Lang` = '" . $this->language->get('code') . "'");
        return $query->row;
    }

    public function getTemplatePages($templateCodeName, $getSettings = true) {
        $settingsPageCondition = $getSettings ? "" : "AND ecpage.CodeName <> 'templatesettings'";
        $query = $this->db->query("SELECT
                                        ecpage.id,
                                        ecpage.CodeName,
                                        ecpage.Route,
                                        ecpagedesc.`Name`,
                                        ecpagedesc.Description
                                        FROM ecpage
                                        INNER JOIN ecpagedesc ON ecpage.id = ecpagedesc.PageId
                                        INNER JOIN ectemplate ON ectemplate.id = ecpage.TemplateId
                                        WHERE ectemplate.CodeName = '" . $templateCodeName . "'" .
                                        $settingsPageCondition . "
                                        AND Lang = '" . $this->language->get('code') . "'");
        
        foreach ($query->rows as $key=>$row) {
            # code...
            if($row['Name'] == 'الصفحة العامة' || $row['Name'] == "General Page"){
                $query->rows[$key]['Route'] = 'common/maintenance'; 
            }
            if($row['Route'] == "product/category"){
                $query->rows[$key]['Route'].="&path=".$this->getFirstCategory();
                continue;
            }
            if($row['Route'] == "product/product"){
                $query->rows[$key]['Route'].="&product_id=".$this->getFirstProduct();
                continue;
            }
            if(strtolower($row['CodeName']) == 'home'){
                unset($query->rows[$key]);
                array_unshift($query->rows, $row);
            }
        }
        return $query->rows;
    }

    public function getTemplateSettingsPage($templateCodeName) {
        $query = $this->db->query("SELECT
                                        ecpage.id,
                                        ecpage.CodeName,
                                        ecpage.Route,
                                        ecpagedesc.`Name`,
                                        ecpagedesc.Description
                                        FROM ecpage
                                        INNER JOIN ecpagedesc ON ecpage.id = ecpagedesc.PageId
                                        INNER JOIN ectemplate ON ectemplate.id = ecpage.TemplateId
                                        WHERE ectemplate.CodeName = '" . $templateCodeName . "'
                                        AND ecpage.CodeName = 'templatesettings'
                                        AND Lang = '" . $this->language->get('code') . "'");

        return $query->row;
    }

    public function getTemplatePage($templateCodeName, $pageId) {
        $query = $this->db->query("SELECT
                                        ecpage.id,
                                        ecpage.CodeName,
                                        ecpage.Route,
                                        ecpagedesc.`Name`,
                                        ecpagedesc.Description
                                        FROM ecpage
                                        INNER JOIN ecpagedesc ON ecpage.id = ecpagedesc.PageId
                                        INNER JOIN ectemplate ON ectemplate.id = ecpage.TemplateId
                                        WHERE ectemplate.CodeName = '" . $templateCodeName . "'
                                        AND ecpage.id = '" . $pageId . "'
                                        AND Lang = '" . $this->language->get('code') . "'");

        return $query->row;
    }

    public function getFirstCategory()
    {
        # code...
        $str_sql = "SELECT category_id from category limit 1";
        $results = $this->db->query($str_sql);
        return $results->row['category_id'];
    }
    public function getFirstProduct()
    {
        # code...
        $str_sql = "SELECT product_id from product limit 1";
        $results = $this->db->query($str_sql);
        return $results->row['product_id'];
    }
    public function getTemplateRegions($templateCodeName, $pageId=0) {
        $query = $this->db->query("SELECT
                                        ecregion.id,
                                        ecregion.CodeName,
                                        ecregion.RowOrder,
                                        ecregion.ColOrder,
                                        ecregion.ColWidth,
                                        ecregion.PageId,
                                        ecregiondesc.Name,
                                        ecregiondesc.Description
                                        FROM ecregion
                                        INNER JOIN ecregiondesc ON ecregion.id = ecregiondesc.RegionId
                                        INNER JOIN ecpage ON ecregion.PageId = ecpage.id
                                        INNER JOIN ectemplate ON ectemplate.id = ecpage.TemplateId
                                        WHERE ectemplate.CodeName = '" . $templateCodeName . "' 
                                        AND (ecregion.PageId = " . $pageId . " OR 0 = " . $pageId . ")
                                        AND Lang = '" . $this->language->get('code') . "'");
        return $query->rows;
    }

    public function prepareDraftVersion($templateCodeName) {
        $query = $this->db->query("SELECT COUNT(*) AS SectionCount FROM ecsection
                                   INNER JOIN ecregion ON ecsection.RegionId = ecregion.id
                                   INNER JOIN ecpage ON ecregion.PageId = ecpage.id
                                   INNER JOIN ectemplate ON ectemplate.id = ecpage.TemplateId
                                   WHERE ecsection.Type = 'draft'
                                   AND ectemplate.CodeName = '" . $templateCodeName . "'");
        //no draft version exist
        if($query->row['SectionCount'] == 0) {
            $query = $this->db->query("SELECT ecsection.id FROM ecsection
                                              INNER JOIN ecregion ON ecsection.RegionId = ecregion.id
                                              INNER JOIN ecpage ON ecregion.PageId = ecpage.id
                                              INNER JOIN ectemplate ON ectemplate.id = ecpage.TemplateId
                                              WHERE ecsection.Type = 'live'
                                              AND ectemplate.CodeName = '" . $templateCodeName . "'");

            $liveSections = $query->rows;
            foreach ($liveSections as $section) {
                $destSectionId = 0;
                $this->db->query("INSERT INTO ecsection (CodeName, `Name`, `Type`, IsCollection, SortOrder, RegionId, State)
                                  SELECT CodeName, `Name`, 'draft', IsCollection, SortOrder, RegionId, State
                                  FROM ecsection WHERE id= " . $section['id'] . " AND `Type` = 'live'");
                $destSectionId = $this->db->getLastId();

                $this->db->query("INSERT INTO ecsectiondesc ( `Name`, Description, Image, Thumbnail, CollectionName, CollectionItemName, CollectionButtonName, Lang, SectionId)
                                  SELECT `Name`, Description, Image, Thumbnail, CollectionName, CollectionItemName, CollectionButtonName, Lang, " . $destSectionId . "
                                  FROM ecsectiondesc WHERE SectionId= " . $section['id']);

                $queryCollections = $this->db->query("SELECT id FROM eccollection WHERE SectionId = " . $section['id']);
                $collections = $queryCollections->rows;
                foreach ($collections as $collection) {
                    $this->insertCollection($collection['id'], $destSectionId);
                }

                $queryFields = $this->db->query("SELECT id FROM ecobjectfield WHERE ObjectType = 'ECSECTION' AND ObjectId = " . $section['id']);
                $fields = $queryFields->rows;
                foreach ($fields as $field) {
                    $this->insertObjField($field['id'], $destSectionId);
                }
            }
        }

    }

    public function addLayout($templateCodeName, $route, $pageName) {
        $query = $this->db->query("SELECT COUNT(*) AS PageCount FROM ecpage
                                   INNER JOIN ectemplate ON ectemplate.id = ecpage.TemplateId
                                   WHERE ecpage.Route = '" . $route . "'
                                   AND ectemplate.CodeName = '" . $templateCodeName . "'");

        if($query->row['PageCount'] == 0) {
            $this->db->query("INSERT INTO ecpage(CodeName, Route, TemplateId)
                          SELECT ecpage.CodeName, '" . $route . "', ecpage.TemplateId FROM ecpage
                          JOIN ectemplate ON ectemplate.id=ecpage.TemplateId
                          WHERE ecpage.CodeName='general' AND ecpage.Route='' 
                          AND ectemplate.CodeName = '" . $templateCodeName . "'");
            $destPageId = $this->db->getLastId();
            $this->db->query("INSERT INTO ecpagedesc(`Name`, Description, Lang, PageId) Values 
                          ('" . $pageName . "', 'N/A', 'en', " . $destPageId . "),
                          ('" . $pageName . "', 'N/A', 'ar', " . $destPageId . ")");

            $this->db->query("INSERT INTO ecregion(CodeName, RowOrder, ColOrder, ColWidth, PageId) 
                          SELECT ecregion.CodeName, ecregion.RowOrder, ecregion.ColOrder, ecregion.ColWidth, " . $destPageId . " 
                          FROM ecregion
                          JOIN ecpage ON ecregion.PageId = ecpage.id
                          JOIN ectemplate ON ectemplate.id=ecpage.TemplateId
                          WHERE ecpage.CodeName='general' AND ecpage.Route='' 
                          AND ectemplate.CodeName = '" . $templateCodeName . "'");

            $queryRegions = $this->db->query("SELECT * FROM ecregion WHERE PageId = " . $destPageId);
            $regions = $queryRegions->rows;
            foreach ($regions as $region) {
                $this->db->query("INSERT INTO ecregiondesc(`Name`, Description, Lang, RegionId) 
                          SELECT ecregiondesc.`Name`, ecregiondesc.Description, ecregiondesc.Lang, '" . $region["id"] . "'  
                          FROM ecregiondesc
                          JOIN ecregion ON ecregion.id = ecregiondesc.RegionId
                          JOIN ecpage ON ecregion.PageId = ecpage.id
                          JOIN ectemplate ON ectemplate.id=ecpage.TemplateId
                          WHERE ecpage.CodeName='general' AND ecpage.Route='' AND ecregion.CodeName = '" . $region["CodeName"] . "'
                          AND ectemplate.CodeName = '" . $templateCodeName . "'");

                //(ecsection.`Type` = 'draft' OR ecsection.`Type` = 'available') <removed>
                $querySections = $this->db->query("SELECT ecsection.* FROM ecsection 
                                          JOIN ecregion ON ecsection.RegionId = ecregion.Id
                                          JOIN ecpage ON ecregion.PageId = ecpage.id
                                          JOIN ectemplate ON ectemplate.id=ecpage.TemplateId
                                          WHERE ecregion.CodeName = '" . $region["CodeName"] . "'
                                          AND ecpage.CodeName='general' AND ecpage.Route='' 
                                          AND ectemplate.CodeName = '" . $templateCodeName . "'");
                $sections = $querySections->rows;
                foreach ($sections as $section) {
                    $destSectionId = 0;
                    $this->db->query("INSERT INTO ecsection (CodeName, `Name`, `Type`, IsCollection, SortOrder, RegionId, State)
                                  SELECT CodeName, `Name`, `Type`, IsCollection, SortOrder, " . $region["id"] . ", State
                                  FROM ecsection WHERE id= " . $section['id']);
                    $destSectionId = $this->db->getLastId();

                    $this->db->query("INSERT INTO ecsectiondesc ( `Name`, Description, Image, Thumbnail, CollectionName, CollectionItemName, CollectionButtonName, Lang, SectionId)
                                  SELECT `Name`, Description, Image, Thumbnail, CollectionName, CollectionItemName, CollectionButtonName, Lang, " . $destSectionId . "
                                  FROM ecsectiondesc WHERE SectionId= " . $section['id']);

                    $queryCollections = $this->db->query("SELECT id FROM eccollection WHERE SectionId = " . $section['id']);
                    $collections = $queryCollections->rows;
                    foreach ($collections as $collection) {
                        $this->insertCollection($collection['id'], $destSectionId);
                    }

                    $queryFields = $this->db->query("SELECT id FROM ecobjectfield WHERE ObjectType = 'ECSECTION' AND ObjectId = " . $section['id']);
                    $fields = $queryFields->rows;
                    foreach ($fields as $field) {
                        $this->insertObjField($field['id'], $destSectionId);
                    }
                }
            }

            return $destPageId;
        }

        return false;
    }

    public function resetDraftVersion($templateCodeName) {
        $this->db->query("DELETE ecsection
                          FROM ecsection
                          INNER JOIN ecregion ON ecsection.RegionId = ecregion.id
                          INNER JOIN ecpage ON ecregion.PageId = ecpage.id
                          INNER JOIN ectemplate ON ectemplate.id = ecpage.TemplateId
                          WHERE ecsection.Type = 'draft'
                          AND ectemplate.CodeName = '" . $templateCodeName . "'");
        $this->db->query("DELETE ecobjectfield
                          FROM ecobjectfield
                          LEFT JOIN eccollection ON (ecobjectfield.ObjectId = eccollection.id AND ecobjectfield.ObjectType='ECCOLLECTION')
                          LEFT JOIN ecsection ON (ecobjectfield.ObjectId = ecsection.id AND ecobjectfield.ObjectType='ECSECTION')
                          WHERE eccollection.id IS NULL AND ecsection.id IS NULL;");

        $this->prepareDraftVersion($templateCodeName);
    }

    public function publishTemplate($templateCodeName) {
        $this->db->query("DELETE ecsection
                          FROM ecsection
                          INNER JOIN ecregion ON ecsection.RegionId = ecregion.id
                          INNER JOIN ecpage ON ecregion.PageId = ecpage.id
                          INNER JOIN ectemplate ON ectemplate.id = ecpage.TemplateId
                          WHERE ecsection.Type = 'live'
                          AND ectemplate.CodeName = '" . $templateCodeName . "'");
        $this->db->query("DELETE ecobjectfield
                          FROM ecobjectfield
                          LEFT JOIN eccollection ON (ecobjectfield.ObjectId = eccollection.id AND ecobjectfield.ObjectType='ECCOLLECTION')
                          LEFT JOIN ecsection ON (ecobjectfield.ObjectId = ecsection.id AND ecobjectfield.ObjectType='ECSECTION')
                          WHERE eccollection.id IS NULL AND ecsection.id IS NULL;");

        $query = $this->db->query("SELECT ecsection.id FROM ecsection
                                              INNER JOIN ecregion ON ecsection.RegionId = ecregion.id
                                              INNER JOIN ecpage ON ecregion.PageId = ecpage.id
                                              INNER JOIN ectemplate ON ectemplate.id = ecpage.TemplateId
                                              WHERE ecsection.Type = 'draft'
                                              AND ectemplate.CodeName = '" . $templateCodeName . "'");

        $draftSections = $query->rows;
        foreach ($draftSections as $section) {
            $destSectionId = 0;
            $this->db->query("INSERT INTO ecsection (CodeName, `Name`, `Type`, IsCollection, SortOrder, RegionId, State)
                                  SELECT CodeName, `Name`, 'live', IsCollection, SortOrder, RegionId, State
                                  FROM ecsection WHERE id= " . $section['id'] . " AND `Type` = 'draft'");
            $destSectionId = $this->db->getLastId();

            $this->db->query("INSERT INTO ecsectiondesc ( `Name`, Description, Image, Thumbnail, CollectionName, CollectionItemName, CollectionButtonName, Lang, SectionId)
                                  SELECT `Name`, Description, Image, Thumbnail, CollectionName, CollectionItemName, CollectionButtonName, Lang, " . $destSectionId . "
                                  FROM ecsectiondesc WHERE SectionId= " . $section['id']);

            $queryCollections = $this->db->query("SELECT id FROM eccollection WHERE SectionId = " . $section['id']);
            $collections = $queryCollections->rows;
            foreach ($collections as $collection) {
                $this->insertCollection($collection['id'], $destSectionId);
            }

            $queryFields = $this->db->query("SELECT id FROM ecobjectfield WHERE ObjectType = 'ECSECTION' AND ObjectId = " . $section['id']);
            $fields = $queryFields->rows;
            foreach ($fields as $field) {
                $this->insertObjField($field['id'], $destSectionId);
            }
        }

        $this->load->model('setting/setting');
        $this->model_setting_setting->insertUpdateSetting('template', ['cust_design' => 1]);
    }

    public function getSections($templateCodeName, $sectionType, $regionId = 0) {

        $sql_str = "SELECT
        ecsection.*,
        ecsectiondesc.`Name` AS DescName,
        ecsectiondesc.Description,
        ecsectiondesc.Image,
        ecsectiondesc.Thumbnail,
        ecsectiondesc.CollectionName, 
        ecsectiondesc.CollectionItemName, 
        ecsectiondesc.CollectionButtonName
        FROM ecsection
        INNER JOIN ecsectiondesc ON ecsection.id = ecsectiondesc.SectionId
        INNER JOIN ecregion ON ecsection.RegionId = ecregion.id
        INNER JOIN ecpage ON ecregion.PageId = ecpage.id
        INNER JOIN ectemplate ON ectemplate.id = ecpage.TemplateId
        WHERE ecsection.Type = '" . $sectionType . "'
        AND ectemplate.CodeName = '" . $templateCodeName . "'
        AND (ecregion.id = " . $regionId . " OR 0 = " . $regionId . ")
        AND ecsectiondesc.Lang = '" . $this->language->get('code') . "'
        ORDER BY ecsection.SortOrder";

        $query = $this->db->query($sql_str);
        return $query->rows;
    }

    private function checkCopyrightPoweredField()
    {
        $this->load->model('setting/setting');
        $setting = $this->model_setting_setting->getSetting('teditor');
        $editable_copyright = (boolean)$setting['copyrights_editable'];
        $copyrights_powered_field_editable = (PRODUCTID == 4 || PRODUCTID == 6 || PRODUCTID == 8 || PRODUCTID == 50 || PRODUCTID == 53) || $editable_copyright;
        return $copyrights_powered_field_editable;
    }

    public function getTemplateCollections($templateCodeName, $sectionType) {
        $query = $this->db->query("SELECT
                                        eccollection.*
                                        FROM eccollection
                                        INNER JOIN ecsection ON ecsection.id = eccollection.SectionId
                                        INNER JOIN ecregion ON ecsection.RegionId = ecregion.id
                                        INNER JOIN ecpage ON ecpage.id = ecregion.PageId
                                        INNER JOIN ectemplate ON ectemplate.id = ecpage.TemplateId
                                        WHERE ectemplate.CodeName = '" . $templateCodeName . "'
                                        AND ecsection.Type = '" . $sectionType . "'");
        return $query->rows;
    }

    public function getTemplateFields($templateCodeName, $sectionType) {
        $query = $this->db->query("
                                    SELECT
                                    ecobjectfield.*,
                                    ecobjectfielddesc.`Name`,
                                    ecobjectfielddesc.Description
                                    FROM ecobjectfield
                                    INNER JOIN ecobjectfielddesc ON ecobjectfielddesc.ObjectFieldId = ecobjectfield.id
                                    INNER JOIN ecsection ON ecobjectfield.ObjectId = ecsection.id AND ecobjectfield.ObjectType = 'ECSECTION'
                                    INNER JOIN ecregion ON ecsection.RegionId = ecregion.id
                                    INNER JOIN ecpage ON ecpage.id = ecregion.PageId
                                    INNER JOIN ectemplate ON ectemplate.id = ecpage.TemplateId
                                    WHERE ectemplate.CodeName = '" . $templateCodeName . "'
                                    AND ecsection.Type = '" . $sectionType . "'
                                    AND ecobjectfielddesc.Lang = '" . $this->language->get('code') . "'
                                    UNION ALL
                                    SELECT
                                    ecobjectfield.*,
                                    ecobjectfielddesc.`Name`,
                                    ecobjectfielddesc.Description
                                    FROM ecobjectfield
                                    INNER JOIN ecobjectfielddesc ON ecobjectfielddesc.ObjectFieldId = ecobjectfield.id
                                    INNER JOIN eccollection ON eccollection.id = ecobjectfield.ObjectId AND ecobjectfield.ObjectType = 'ECCOLLECTION'
                                    INNER JOIN ecsection ON eccollection.SectionId = ecsection.id
                                    INNER JOIN ecregion ON ecsection.RegionId = ecregion.id
                                    INNER JOIN ecpage ON ecpage.id = ecregion.PageId
                                    INNER JOIN ectemplate ON ectemplate.id = ecpage.TemplateId
                                    WHERE ectemplate.CodeName = '" . $templateCodeName . "'
                                    AND ecsection.Type = '" . $sectionType . "'
                                    AND ecobjectfielddesc.Lang = '" . $this->language->get('code') . "'
                                    ");
        return $query->rows;
    }

    public function getTemplateFieldsVal($templateCodeName, $sectionType) {
        $query = $this->db->query("
                                    SELECT ecobjectfieldval.*
                                    FROM ecobjectfieldval
                                    INNER JOIN ecobjectfield ON ecobjectfield.id = ecobjectfieldval.ObjectFieldId
                                    INNER JOIN ecsection ON ecobjectfield.ObjectId = ecsection.id AND ecobjectfield.ObjectType = 'ECSECTION'
                                    INNER JOIN ecregion ON ecsection.RegionId = ecregion.id
                                    INNER JOIN ecpage ON ecpage.id = ecregion.PageId
                                    INNER JOIN ectemplate ON ectemplate.id = ecpage.TemplateId
                                    WHERE ectemplate.CodeName = '" . $templateCodeName . "'
                                    AND ecsection.Type = '" . $sectionType . "'
                                    UNION ALL
                                    SELECT ecobjectfieldval.*
                                    FROM ecobjectfieldval
                                    INNER JOIN ecobjectfield ON ecobjectfield.id = ecobjectfieldval.ObjectFieldId
                                    INNER JOIN eccollection ON eccollection.id = ecobjectfield.ObjectId AND ecobjectfield.ObjectType = 'ECCOLLECTION'
                                    INNER JOIN ecsection ON eccollection.SectionId = ecsection.id
                                    INNER JOIN ecregion ON ecsection.RegionId = ecregion.id
                                    INNER JOIN ecpage ON ecpage.id = ecregion.PageId
                                    INNER JOIN ectemplate ON ectemplate.id = ecpage.TemplateId
                                    WHERE ectemplate.CodeName = '" . $templateCodeName . "'
                                    AND ecsection.Type = '" . $sectionType . "'");
        return $query->rows;
    }

    public function getSectionById($sectionId) {
        $query = $this->db->query("SELECT
                                        ecsection.*,
                                        ecsectiondesc.`Name` AS DescName,
                                        ecsectiondesc.Description,
                                        ecsectiondesc.Image,
                                        ecsectiondesc.Thumbnail,
                                        ecsectiondesc.CollectionName, 
                                        ecsectiondesc.CollectionItemName, 
                                        ecsectiondesc.CollectionButtonName
                                        FROM ecsection
                                        INNER JOIN ecsectiondesc ON ecsection.id = ecsectiondesc.SectionId
                                        WHERE ecsection.id = '" . (int)$sectionId . "'
                                        AND ecsectiondesc.Lang = '" . $this->language->get('code') . "'");
        return $query->row;
    }

    public function getSectionForPreview($sectionId) {
        $query = $this->db->query("SELECT
                                     ecregion.id region_id,
                                     ecregion.CodeName region_codename,
                                     ecsection.id section_id,
                                     ecsection.CodeName section_codename,
                                     ecsection.State section_state
                                   FROM ecsection
                                   JOIN ecregion ON ecsection.RegionId = ecregion.id
                                   WHERE ecsection.id = " . $sectionId);

        if ($query->row) {
            return $query->row;
        } else {
            return false;
        }
    }

    public function getPrevSectionId($sectionId) {
        $query = $this->db->query("SELECT
                                     ecsection.id section_id
                                   FROM ecsection
                                   JOIN ecregion ON ecsection.RegionId = ecregion.id
                                   WHERE ecregion.id = (
                                      SELECT RegionId FROM ecsection WHERE id = " . $sectionId . "
                                   )
                                   AND ecsection.SortOrder < (
                                      SELECT SortOrder FROM ecsection WHERE id = " . $sectionId . "
                                   )
                                   AND ecsection.Type='draft'
                                   ORDER BY ecsection.SortOrder DESC
                                   LIMIT 1");

        if ($query->row) {
            return $query->row['section_id'];
        } else {
            return null;
        }
    }

    public function getNextSectionId($sectionId) {
        $query = $this->db->query("SELECT
                                     ecsection.id section_id
                                   FROM ecsection
                                   JOIN ecregion ON ecsection.RegionId = ecregion.id
                                   WHERE ecregion.id = (
                                      SELECT RegionId FROM ecsection WHERE id = " . $sectionId . "
                                   )
                                   AND ecsection.SortOrder > (
                                      SELECT SortOrder FROM ecsection WHERE id = " . $sectionId . "
                                   )
                                   AND ecsection.Type='draft'
                                   ORDER BY ecsection.SortOrder
                                   LIMIT 1");

        if ($query->row) {
            return $query->row['section_id'];
        } else {
            return null;
        }
    }

    public function getSectionFieldsForPreview($sectionId) {
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
                                    WHERE ecsection.id = '" . $sectionId . "'
                                    AND (
                                        ecobjectfieldval.Lang = '" . $this->language->get('code') . "'
                                        OR ecobjectfieldval.Lang = ''
                                    )");

        if ($query->rows) {
            return $query->rows;
        } else {
            return array();
        }
    }

    public function getSectionPageCodeName($sectionId){
        $query = $this->db->query("SELECT
                                        ecpage.CodeName
                                    FROM
                                        ecsection
                                    JOIN ecregion ON ecsection.RegionId = ecregion.id
                                    JOIN ecpage ON ecregion.PageId = ecpage.id
                                    WHERE ecsection.id = '" . $sectionId . "'");

        return $query->row['CodeName'];
    }

    public function getSectionCollectionsForPreview($sectionId) {
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
                                    WHERE ecsection.id = '" . $sectionId . "'
                                    AND eccollection.IsDefault = 0
                                    AND (
                                        ecobjectfieldval.Lang = '" . $this->language->get('code') . "'
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

    public function getSectionCollections($sectionId){
        $query = $this->db->query("SELECT
                                        eccollection.id,
                                        eccollection.`Name`,
                                        eccollection.Thumbnail,
                                        eccollection.IsDefault,
                                        eccollection.SortOrder,
                                        eccollection.SectionId
                                        FROM eccollection
                                        INNER JOIN ecsection ON ecsection.id = eccollection.SectionId
                                        WHERE eccollection.IsDefault = 0 AND ecsection.id = '" . $sectionId . "'
                                        ORDER BY eccollection.SortOrder");
        return $query->rows;
    }

    public function getSectionImages($sectionId) {
        $query = $this->db->query("
                                    SELECT ecobjectfieldval.*
                                    FROM ecobjectfieldval
                                    INNER JOIN ecobjectfield ON ecobjectfield.id = ecobjectfieldval.ObjectFieldId
                                    INNER JOIN ecsection ON ecobjectfield.ObjectId = ecsection.id AND ecobjectfield.ObjectType = 'ECSECTION'
                                    INNER JOIN ecregion ON ecsection.RegionId = ecregion.id
                                    WHERE ecobjectfield.`Type`='image' AND ecsection.id = '" . (int)$sectionId . "'
                                    UNION ALL
                                    SELECT ecobjectfieldval.*
                                    FROM ecobjectfieldval
                                    INNER JOIN ecobjectfield ON ecobjectfield.id = ecobjectfieldval.ObjectFieldId
                                    INNER JOIN eccollection ON eccollection.id = ecobjectfield.ObjectId AND ecobjectfield.ObjectType = 'ECCOLLECTION'
                                    INNER JOIN ecsection ON eccollection.SectionId = ecsection.id
                                    WHERE eccollection.IsDefault = 0 AND ecobjectfield.`Type`='image' AND ecsection.id = '" . (int)$sectionId . "'");
        return $query->rows;
    }

    public function getAllSectionObjectFields($sectionId){ // depricated

        $copyrights_powered_field_editable = $this->checkCopyrightPoweredField();

        $sql_str = "
        SELECT
        ecobjectfield.*,
        ecobjectfielddesc.`Name`,
        ecobjectfielddesc.Description
        FROM ecobjectfield
        INNER JOIN ecobjectfielddesc ON ecobjectfielddesc.ObjectFieldId = ecobjectfield.id
        INNER JOIN ecsection ON ecobjectfield.ObjectId = ecsection.id AND ecobjectfield.ObjectType = 'ECSECTION'
        WHERE ecsection.id = '" . (int)$sectionId . "'
        AND ecobjectfielddesc.Lang = '" . $this->language->get('code') . "'";

        if(!$copyrights_powered_field_editable){
            $sql_str.=" AND (ecobjectfield.CodeName not like 'copyrightsPowered' and ecobjectfield.CodeName not like 'Copyright_Powered')";
        }

        $sql_str.="
        UNION ALL
        SELECT
        ecobjectfield.*,
        ecobjectfielddesc.`Name`,
        ecobjectfielddesc.Description
        FROM ecobjectfield
        INNER JOIN ecobjectfielddesc ON ecobjectfielddesc.ObjectFieldId = ecobjectfield.id
        INNER JOIN eccollection ON eccollection.id = ecobjectfield.ObjectId AND ecobjectfield.ObjectType = 'ECCOLLECTION'
        INNER JOIN ecsection ON eccollection.SectionId = ecsection.id
        WHERE eccollection.IsDefault = 0 AND ecsection.id = '" . (int)$sectionId . "'
        AND ecobjectfielddesc.Lang = '" . $this->language->get('code') . "'
        ";
        
        if(!$copyrights_powered_field_editable){
            $sql_str.=" AND (ecobjectfield.CodeName not like 'copyrightsPowered' and ecobjectfield.CodeName not like 'Copyright_Powered')";
        }

        $query = $this->db->query($sql_str);

        return $query->rows;
    }

    public function getSectionObjectFields($sectionId){

        $copyrights_powered_field_editable = $this->checkCopyrightPoweredField();

        $sql_str = "
        SELECT
        ecobjectfield.*,
        ecobjectfielddesc.`Name`,
        ecobjectfielddesc.Description
        FROM ecobjectfield
        INNER JOIN ecobjectfielddesc ON ecobjectfielddesc.ObjectFieldId = ecobjectfield.id
        INNER JOIN ecsection ON ecobjectfield.ObjectId = ecsection.id AND ecobjectfield.ObjectType = 'ECSECTION'
        WHERE ecsection.id = '" . (int)$sectionId . "'
        AND ecobjectfielddesc.Lang = '" . $this->language->get('code') . "'";

        if(!$copyrights_powered_field_editable){
            $sql_str.=" AND (ecobjectfield.CodeName not like 'copyrightsPowered' and ecobjectfield.CodeName not like 'Copyright_Powered')";
        }

        $query = $this->db->query($sql_str);

        return $query->rows;
    }

    public function getAllSectionObjectFieldsVal($sectionId){ // depricated
        
        $query = $this->db->query("
                                    SELECT ecobjectfieldval.*,ecobjectfield.`Type`
                                    FROM ecobjectfieldval
                                    INNER JOIN ecobjectfield ON ecobjectfield.id = ecobjectfieldval.ObjectFieldId
                                    INNER JOIN ecsection ON ecobjectfield.ObjectId = ecsection.id AND ecobjectfield.ObjectType = 'ECSECTION'
                                    INNER JOIN ecregion ON ecsection.RegionId = ecregion.id
                                    WHERE ecsection.id = '" . (int)$sectionId . "'
                                    UNION ALL
                                    SELECT ecobjectfieldval.*,ecobjectfield.`Type`
                                    FROM ecobjectfieldval
                                    INNER JOIN ecobjectfield ON ecobjectfield.id = ecobjectfieldval.ObjectFieldId
                                    INNER JOIN eccollection ON eccollection.id = ecobjectfield.ObjectId AND ecobjectfield.ObjectType = 'ECCOLLECTION'
                                    INNER JOIN ecsection ON eccollection.SectionId = ecsection.id
                                    WHERE eccollection.IsDefault = 0 AND ecsection.id = '" . (int)$sectionId . "'");

        $results=$query->rows;
        foreach ($results as $key => $row) {
            if($row['Type'] == "link"){
                if($row['Value'] == "#"){
                    $results[$key]['Value'] = 'Home';   
                }
                else{
                    $link_name = $this->getRouteOfLink(html_entity_decode($row['Value']),true);
                    $results[$key]['Value'] = ($link_name);
                }
            }
        }                   
     //   var_dump($results);die();         
        return $results;
    }

    public function getObjectFieldVals($fieldId){
        
        $query = $this->db->query("
                                    SELECT ecobjectfieldval.*,ecobjectfield.`Type`
                                    FROM ecobjectfieldval
                                    INNER JOIN ecobjectfield ON ecobjectfield.id = ecobjectfieldval.ObjectFieldId
                                    WHERE ecobjectfieldval.ObjectFieldId = '" . (int)$fieldId . "'");

        $results=$query->rows;
        foreach ($results as $key => $row) {
            if($row['Type'] == "link"){
                if($row['Value'] == "#"){
                    $results[$key]['Value'] = 'Home';   
                }
                else{
                    $link_name = $this->getRouteOfLink(html_entity_decode($row['Value']),true);
                    $results[$key]['Value'] = ($link_name);
                }
            }
        }                   

        return $results;
    }

    public function getCollectionById($collectionId) {
        $query = $this->db->query("SELECT
                                        eccollection.id,
                                        eccollection.`Name`,
                                        eccollection.Thumbnail,
                                        eccollection.IsDefault,
                                        eccollection.SortOrder,
                                        eccollection.SectionId
                                        FROM eccollection
                                        WHERE eccollection.id = '" . (int)$collectionId . "'");
        return $query->row;
    }

    public function getCollectionObjectFields($collectionId){
        $query = $this->db->query(" SELECT
                                    ecobjectfield.*,
                                    ecobjectfielddesc.`Name`,
                                    ecobjectfielddesc.Description
                                    FROM ecobjectfield
                                    INNER JOIN ecobjectfielddesc ON ecobjectfielddesc.ObjectFieldId = ecobjectfield.id
                                    INNER JOIN eccollection ON eccollection.id = ecobjectfield.ObjectId AND ecobjectfield.ObjectType = 'ECCOLLECTION'
                                    WHERE eccollection.IsDefault = 0 AND eccollection.id = '" . (int)$collectionId . "'
                                    AND ecobjectfielddesc.Lang = '" . $this->language->get('code') . "'
                                    ");
        return $query->rows;
    }

    public function getCollectionObjectFieldsVal($collectionId){
        $query = $this->db->query("
                                    SELECT ecobjectfieldval.*
                                    FROM ecobjectfieldval
                                    INNER JOIN ecobjectfield ON ecobjectfield.id = ecobjectfieldval.ObjectFieldId
                                    INNER JOIN eccollection ON eccollection.id = ecobjectfield.ObjectId AND ecobjectfield.ObjectType = 'ECCOLLECTION'
                                    WHERE eccollection.IsDefault = 0 AND eccollection.id = '" . (int)$collectionId . "'");
        return $query->rows;
    }

    public function getFieldValSection($id){
        $query = $this->db->query("
                                    SELECT ecobjectfield.ObjectType
                                    FROM ecobjectfieldval
                                    INNER JOIN ecobjectfield ON ecobjectfield.id = ecobjectfieldval.ObjectFieldId
                                    WHERE ecobjectfieldval.id = '" . (int)$id . "'");
        if($query->row['ObjectType'] == 'ECCOLLECTION'){
            $query = $this->db->query("
                                      SELECT eccollection.SectionId
                                      FROM ecobjectfieldval
                                      INNER JOIN ecobjectfield ON ecobjectfield.id = ecobjectfieldval.ObjectFieldId
                                      INNER JOIN eccollection ON eccollection.id = ecobjectfield.ObjectId
                                      WHERE ecobjectfieldval.id = '" . (int)$id . "'");
            return $query->row['SectionId'];
        }
        elseif($query->row['ObjectType'] == 'ECSECTION'){
            $query = $this->db->query("
                                      SELECT ecsection.id
                                      FROM ecobjectfieldval
                                      INNER JOIN ecobjectfield ON ecobjectfield.id = ecobjectfieldval.ObjectFieldId
                                      INNER JOIN ecsection ON ecsection.id = ecobjectfield.ObjectId
                                      WHERE ecobjectfieldval.id = '" . (int)$id . "'");
            return $query->row['id'];
        }
    }

    public function getCollectionSection($id){
            $query = $this->db->query("
                                      SELECT eccollection.SectionId
                                      FROM eccollection
                                      WHERE id = '" . (int)$id . "'");
            return $query->row['SectionId'];
    }

    public function insertSection($sourceSectionId, $sourceType, $destType, $destSortOrder)
    {
//      $query = $this->db->query("SELECT * FROM ecsection WHERE id= " . $sourceSectionId . " AND Type = '" . $sourceType . "'");
//      $section = $query->row;

        if(!$destSortOrder){
            $query = $this->db->query("SELECT MAX(SortOrder) AS maxSort FROM ecsection
                                   WHERE Type = '" . $destType . "'
                                   AND RegionId = (SELECT RegionId FROM ecsection WHERE id = " . $sourceSectionId . ")");
            $destSortOrder = $query->row['maxSort'] + 1;
        }

        $this->db->query("INSERT INTO ecsection (CodeName, `Name`, `Type`, IsCollection, SortOrder, RegionId, State)
                          SELECT CodeName, `Name`, '" . $destType . "', IsCollection, " . $destSortOrder . ", RegionId, State
                          FROM ecsection WHERE id= " . $sourceSectionId . " AND `Type` = '" . $sourceType . "'");
        $destSectionId = $this->db->getLastId();

        $this->db->query("INSERT INTO ecsectiondesc ( `Name`, Description, Image, Thumbnail, CollectionName, CollectionItemName, CollectionButtonName, Lang, SectionId)
                          SELECT `Name`, Description, Image, Thumbnail, CollectionName, CollectionItemName, CollectionButtonName, Lang, " . $destSectionId . "
                          FROM ecsectiondesc WHERE SectionId= " . $sourceSectionId);

        $query = $this->db->query("SELECT id FROM eccollection WHERE SectionId = " . $sourceSectionId);
        $collections = $query->rows;
        foreach ($collections as $collection) {
            $this->insertCollection($collection['id'], $destSectionId);
        }

        $query = $this->db->query("SELECT id FROM ecobjectfield WHERE ObjectType = 'ECSECTION' AND ObjectId = " . $sourceSectionId);
        $fields = $query->rows;
        foreach ($fields as $field) {
            $this->insertObjField($field['id'], $destSectionId);
        }

        return $destSectionId;
    }

    public function insertCollection($sourceCollectionId, $destSectionId) {
        $isAddCollection = false;
        if((int)$sourceCollectionId == 0) {
            $query = $this->db->query("SELECT eccollection.id
                              FROM eccollection
                              JOIN ecsection ON eccollection.SectionId = ecsection.id
                              WHERE eccollection.IsDefault = 1
                              AND ecsection.id=" . (int)$destSectionId . " LIMIT 1");
            $sourceCollectionId = $query->row['id'];
            $isAddCollection = true;
        }
        $this->db->query("INSERT INTO eccollection (`Name`, Thumbnail, IsDefault, SortOrder, SectionId)
                          SELECT `Name`, Thumbnail, " . ($isAddCollection? "0" : "IsDefault") . ", SortOrder, " . $destSectionId . "
                          FROM eccollection WHERE id= " . $sourceCollectionId);

        $destCollectionId = $this->db->getLastId();

        $query = $this->db->query("SELECT id FROM ecobjectfield WHERE ObjectType = 'ECCOLLECTION' AND ObjectId = " . $sourceCollectionId);
        $fields = $query->rows;
        foreach ($fields as $field) {
            $this->insertObjField($field['id'], $destCollectionId);
        }

        return $destCollectionId;
    }

    public function insertObjField($sourceFieldId ,$destObjectId) {
        $this->db->query("INSERT INTO ecobjectfield (CodeName,`Type`,SortOrder,LookUpKey,IsMultiLang,ObjectType,ObjectId)
                          SELECT CodeName,`Type`,SortOrder,LookUpKey,IsMultiLang,ObjectType, " . $destObjectId . "
                          FROM ecobjectfield WHERE id= " . $sourceFieldId);

        $destFieldId = $this->db->getLastId();

        $this->db->query("INSERT INTO ecobjectfielddesc (`Name`,Description,Lang,ObjectFieldId)
                          SELECT `Name`,Description,Lang, " . $destFieldId . "
                          FROM ecobjectfielddesc WHERE ObjectFieldId= " . $sourceFieldId);

        $this->db->query("INSERT INTO ecobjectfieldval (`Value`,Lang,ObjectFieldId)
                          SELECT `Value`,Lang, " . $destFieldId . "
                          FROM ecobjectfieldval WHERE ObjectFieldId= " . $sourceFieldId);
    }

    public function deleteSection($sectionId) {
        $this->db->query("DELETE FROM ecsection WHERE `Type`!='available' AND `Type`!='intial' AND id = " . $sectionId);
        $this->db->query("DELETE ecobjectfield
                          FROM ecobjectfield
                          LEFT JOIN eccollection ON (ecobjectfield.ObjectId = eccollection.id AND ecobjectfield.ObjectType='ECCOLLECTION')
                          LEFT JOIN ecsection ON (ecobjectfield.ObjectId = ecsection.id AND ecobjectfield.ObjectType='ECSECTION')
                          WHERE eccollection.id IS NULL AND ecsection.id IS NULL");
    }

    public function saveFieldValById($id, $newValue) {
        
        if(!empty($newValue)){
            $expand_seo = $this->config->get('expand_seo');
            if (isset($expand_seo['es_status']) && $expand_seo['es_status'] == 1) {
                $lang_query = $this->db->query("SELECT code FROM `language`");
                $langs = $lang_query->rows;
            }
            $sql = "SELECT * from ecobjectfieldval 
                    join ecobjectfield 
                    on ecobjectfieldval.ObjectFieldId=ecobjectfield.id  
                    WHERE ecobjectfieldval.id=" . (int)$id . " and ecobjectfield.`Type` like 'link'";
            // Check if the element is link to update with route
            $link_query = $this->db->query($sql);
            
            if(isset($langs)){
                foreach($langs as $lang){
                    $extra_sql = $sql . " and ecobjectfieldval.Lang like '".$lang['code']."'";     
                    $config_lang_link_query = $this->db->query($extra_sql);
                    if($config_lang_link_query->num_rows > 0){
                        $config_lang = $lang['code'];
                        break;
                    }
                }
            }
            if($link_query->num_rows > 0){
                if(lcfirst($newValue) == "home"){
                    if (isset($expand_seo['es_status']) && $expand_seo['es_status'] == 1) {
                        $link_route_query = $this->db->query("SELECT * from expand_seo WHERE `seo_group` like 'common/home'");
                        $newValue=$link_route_query->rows[0]['schema_prefix'];
                        
                    }
                    else{
                        $link_route_query = $this->db->query("SELECT * from link WHERE `name` like 'home'");
                        $newValue=$link_route_query->rows[0]['route'];    
                    }
                }
                else{
                    $result = $this->getRouteOfLink($newValue);
                    
                    if($result){
                        if (isset($expand_seo['es_status']) && $expand_seo['es_status'] == 1) {
                            $newValue=HTTPS_CATALOG.$config_lang."/";
                        }
                        else{
                            $newValue=HTTPS_CATALOG."index.php?route=";
                        }

                        $link_route_query = $this->db->query("SELECT `route` from link WHERE `name` like '".$result[0]."'");
                        switch($result[0]){
                            case 'category':
                                if (isset($expand_seo['es_status']) && $expand_seo['es_status'] == 1) {
                                    $link_route_query = $this->db->query("SELECT * from expand_seo WHERE `seo_group` like 'product/category'");
                                    $newValue.="category/";
                                    $newValue.=$result[2]."-".$result[1];
                                }
                                else{
                                    $newValue.=str_replace("{cat_id}",$result[1],$link_route_query->rows[0]['route']);
                                }
                                break;
                            case 'product':
                                if (isset($expand_seo['es_status']) && $expand_seo['es_status'] == 1) {
                                    $link_route_query = $this->db->query("SELECT * from expand_seo WHERE `seo_group` like 'product/product'");
                                    $newValue.="products/";
                                    $newValue.=$result[2]."-".$result[1];
                                }
                                else{
                                    $tmp=str_replace("{p_id}",$result[1],$link_route_query->rows[0]['route']);
                                    $newValue.=str_replace("{cat_id}",$result[3],$tmp);
                                }
                                break;
                            case 'web page':
                                if (isset($expand_seo['es_status']) && $expand_seo['es_status'] == 1) {
                                    $link_route_query = $this->db->query("SELECT * from expand_seo WHERE `seo_group` like 'information/information'");
                                    $newValue.="info/";
                                    $newValue.=$result[2]."-".$result[1];
                                }
                                else{
                                    $newValue.=str_replace("{info_id}",$result[1],$link_route_query->rows[0]['route']);
                                }
                                break;
                            case 'other pages':
                                if (isset($expand_seo['es_status']) && $expand_seo['es_status'] == 1) {
                                    $page_route=$result[1];
                                    $link_route_query = $this->db->query("SELECT * from expand_seo WHERE `seo_group` like '{$page_route}'");
                                    $newValue.=$result[2];
                                }
                                else{
                                    $newValue.=$result[1];
                                }
                                break;
                        }    
                    }
                }
            }
        }
        $this->db->query("UPDATE ecobjectfieldval
                          SET `Value`='" . $this->db->escape($newValue) . "'
                          WHERE id=" . (int)$id);
    }

    public function getRouteOfLink($value,$reverse=false){
       
        $lang_id = $this->config->get('config_language_id') ? (int)$this->config->get('config_language_id') : 1;
        $lang_code = $this->config->get('config_language')  ? $this->config->get('config_language') : 'en';
        $expand_seo = $this->config->get('expand_seo');

        if(!$reverse){
            $sql = "SELECT c.category_id, cd.name,cd.slug FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) ";
            $sql .= " Where name like '".$this->db->escape($value)."'";
            $sql .= " GROUP BY c.category_id";
                  
            $categories_data = $this->db->query($sql);

            if($categories_data->num_rows > 0){
                if (isset($expand_seo['es_status']) && $expand_seo['es_status'] == 1) {
                    $slug = (new Slugify())->slug($value);
                    return array('category',$categories_data->rows[0]['category_id'],$slug);
                }else{
                    $value=strtolower(str_replace(" ","-",$value));
                    return array('category',$categories_data->rows[0]['category_id'],$value);
                }
            }

            $sql = "SELECT p.product_id, pc.category_id as cid FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";
            $sql .= " join product_to_category pc on p.product_id= pc.product_id ";
            $sql .= " WHERE name like '".$this->db->escape($value)."'";
            $sql .= " GROUP BY p.product_id";
            $product_data = $this->db->query($sql);
            if($product_data->num_rows > 0){
                if (isset($expand_seo['es_status']) && $expand_seo['es_status'] == 1) {
                    $slug = (new Slugify())->slug($value);
                    $product_id     = $product_data->rows[0]['product_id'];
                    return array('product',$product_id,$slug, $product_data->rows[0]['cid']);
                }else{
                    $value=strtolower(str_replace(" ","-",$value));
                    $product_id     = $product_data->rows[0]['product_id'];
                    return array('product',$product_id,$value, $product_data->rows[0]['cid']);
                }
            }
            
            $sql = "SELECT * FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description i_d ON (i.information_id = i_d.information_id)";
            $sql .= " WHERE title like '%".$this->db->escape($value)."%'";
            
            $page_data = $this->db->query($sql);
            if($page_data->num_rows > 0){
                if (isset($expand_seo['es_status']) && $expand_seo['es_status'] == 1) {
                    $slug = (new Slugify())->slug($value);
                    return array('web page',$page_data->rows[0]['information_id'],$slug);
                }else{
                    $value=strtolower(str_replace(" ","-",$value));
                    return array('web page',$page_data->rows[0]['information_id'],$value);
                }
            }

            $newValue=ucwords($value);
            $newValue=str_replace(" ","_",$newValue);

            $link_route_query = $this->db->query("SELECT * from link WHERE `name` like '".$this->db->escape($newValue)."'");
            if($link_route_query->num_rows > 0){
                if (isset($expand_seo['es_status']) && $expand_seo['es_status'] == 1) {
                    $slug = (new Slugify())->slug($link_route_query->rows[0]['route']);
                    return array('other pages',$link_route_query->rows[0]['route'],$slug);
                }else{
                    return array('other pages',$link_route_query->rows[0]['route'],$value);
                }
            }
            return false;
        }
        else{
            if (isset($expand_seo['es_status']) && $expand_seo['es_status'] == 1) {
              
                $absloute_link=substr($value,strrpos($value,$lang_code."/"));
            }
            else{
                $absloute_link=substr($value,strrpos($value,'index.php?route='));
            }
            
            if(strpos($absloute_link,'product/category') !== false || strpos($absloute_link,'category') !== false){
                if (isset($expand_seo['es_status']) && $expand_seo['es_status'] == 1) {
                    $cat_level=strrpos($absloute_link,'-');
                }
                else{
                    $cat_level=strrpos($absloute_link,'_');
                }
                if($cat_level !== false){
                    $cat_id=substr($absloute_link, $cat_level+1);      
                }
                else{
                    $cat_id=substr($absloute_link,strrpos($absloute_link,'=')+1);
                }
                $sql = "SELECT c.category_id, cd.name FROM " . DB_PREFIX . "category c JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) ";
                $sql .= " and c.category_id = '".intval($cat_id)."'";
                $sql .= " GROUP BY c.category_id";
                
                $categories_data = $this->db->query($sql);
                if($categories_data->num_rows > 0){
                    return htmlspecialchars_decode($categories_data->rows[0]['name']);
                }
                else{
                    return $value;
                }
            }
            else if(strpos($absloute_link,'product/product') !== false || strpos($absloute_link,'product') !== false){
                if (isset($expand_seo['es_status']) && $expand_seo['es_status'] == 1) {
                    $product_id=substr($absloute_link,strrpos($absloute_link,'-')+1);
                }
                else{
                    $product_id=substr($absloute_link,strrpos($absloute_link,'=')+1);
                }
                $sql = "SELECT p.product_id,pd.name FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";
                $sql .= " WHERE pd.language_id = $lang_id and p.product_id = '".intval($product_id)."'";
                $sql .= " GROUP BY p.product_id";
                $product_data = $this->db->query($sql);
                if($product_data->num_rows > 0){
                    return htmlspecialchars_decode($product_data->rows[0]['name']);
                }
            
            }
            else if(strpos($absloute_link,'information/information') !== false ||  (isset($expand_seo['es_status']) && $expand_seo['es_status'] == 1 && strpos($absloute_link,'info') !== false)){

                if (isset($expand_seo['es_status']) && $expand_seo['es_status'] == 1) {
                    $page_id=substr($absloute_link,strrpos($absloute_link,'-')+1);
                }
                else{
                    $page_id=substr($absloute_link,strrpos($absloute_link,'=')+1);
                }
                
                //var_dump($page_id);die();
                $sql = "SELECT * FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description i_d ON (i.information_id = i_d.information_id)";
                $sql .= " WHERE i_d.language_id = '{$lang_id}' and i.information_id='".$page_id."'";

                $page_data = $this->db->query($sql);
                
                if($page_data->num_rows > 0){
                    return htmlspecialchars_decode($page_data->rows[0]['title']);
                }
            }
            else{
                if (isset($expand_seo['es_status']) && $expand_seo['es_status'] == 1) {
                    $page_route_slash_sign=strpos($absloute_link,'/');
                    $page_route=substr($absloute_link, $page_route_slash_sign+1);     
                    $link_route_query = $this->db->query("SELECT * from link join expand_seo on link.`route`=expand_seo.`seo_group` WHERE `schema_prefix` like '".$this->db->escape($page_route)."'");
                }
                else{
                    $page_route_equal_sign=strrpos($absloute_link,'=');
                    $page_route=substr($absloute_link, $page_route_equal_sign+1);      
                    $link_route_query = $this->db->query("SELECT * from link WHERE `route` like '".$this->db->escape($page_route)."'");
                }
                if($link_route_query->num_rows > 0){
                    return ucfirst(str_replace("_"," ",$link_route_query->rows[0]['name']));
                }
            }
            return $value;
        }
    }
    public function updateSectionOrder($sectionId, $SortOrder) {
        $this->db->query("UPDATE ecsection SET SortOrder=" . (int)$SortOrder . " WHERE id=" . (int)$sectionId);
    }

    public function updateCollectionOrder($collectionId, $SortOrder) {
        $this->db->query("UPDATE eccollection SET SortOrder=" . (int)$SortOrder . " WHERE id=" . (int)$collectionId);
    }

    public function deleteCollection($collectionId) {
        $this->db->query("DELETE FROM eccollection WHERE id = " . $collectionId);
        $this->db->query("DELETE ecobjectfield
                          FROM ecobjectfield
                          LEFT JOIN eccollection ON (ecobjectfield.ObjectId = eccollection.id AND ecobjectfield.ObjectType='ECCOLLECTION')
                          LEFT JOIN ecsection ON (ecobjectfield.ObjectId = ecsection.id AND ecobjectfield.ObjectType='ECSECTION')
                          WHERE eccollection.id IS NULL AND ecsection.id IS NULL");
    }

    public function getLookupByLookupKey($lookupKey) {
        $query = $this->db->query("SELECT * FROM eclookup WHERE LookUpKey='" . $lookupKey . "' AND Lang='" . $this->language->get('code') . "'");
        return $query->rows;
    }

    public function getProducts($ids) {
        if($ids != "") {
            $query = $this->db->query("SELECT p.product_id AS value, pd.name AS display FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id IN(" . $ids . ") AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
            return $query->rows;
        }
        else {
            return array();
        }
    }

    public function getCategories($ids) {
        $language_id = $this->config->get('config_language_id') ? (int)$this->config->get('config_language_id') : 1;
        if($ids != "") {
            $query = $this->db->query("select * from (SELECT cp.category_id AS value,GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR ' > ') AS display,c.parent_id,c.sort_order FROM category_path cp
            LEFT JOIN category c ON (cp.path_id = c.category_id)
            LEFT JOIN category_description cd1 ON (c.category_id = cd1.category_id)
            LEFT JOIN category_description cd2 ON (cp.category_id = cd2.category_id)
            WHERE cd1.language_id = '".$language_id."'  AND cd2.language_id = '".$language_id."' GROUP BY cp.category_id  ORDER BY display) as result where result.value IN(" . $ids . ") ");
            return $query->rows;
        }
        else {
            return array();
        }
    }

    public function getBrands($ids) {
        if($ids != "") {
            $query = $this->db->query("SELECT m.manufacturer_id AS value, m.name AS display 
                                        FROM manufacturer m 
                                        WHERE m.manufacturer_id IN(" . $ids . ")");
            return $query->rows;
        }
        else {
            return array();
        }
    }

    public function searchProducts($str) {
        $query = $this->db->query("SELECT p.product_id AS value, (SELECT pd1.name FROM product_description pd1 WHERE p.product_id = pd1.product_id AND pd1.language_id='" . (int)$this->config->get('config_language_id') . "') AS display FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.name like '%" . $str . "%'");
        return $query->rows;
    }

    public function searchCategories($str) {
        $availableLanguages = $this->db->query("SELECT language_id FROM `language`");
        $sqlStmt = array();
        foreach ($availableLanguages->rows as $language) {
            $sqlStmt[] = "SELECT * from (SELECT 
                                    cp.category_id AS value,GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR ' > ') AS display,c.parent_id,c.sort_order
                                    FROM category_path cp
                                        LEFT JOIN category c ON (cp.path_id = c.category_id)
                                        LEFT JOIN category_description cd1 ON (c.category_id = cd1.category_id)
                                    WHERE cp.category_id IN (SELECT DISTINCT category_id FROM category) AND cd1.language_id = ".$language['language_id']."
                                    GROUP BY cp.category_id ORDER BY display) AS result WHERE result.display LIKE '%" . $str . "%'";

        }
        $sqlStmt = implode(" UNION ",$sqlStmt);
        $query = $this->db->query($sqlStmt);
        return $query->rows;
    }

    public function searchBrands($str = NULL) {
        $queryString = "SELECT m.manufacturer_id AS value, m.name AS display 
                                    FROM manufacturer m WHERE m.manufacturer_id IN ((SELECT DISTINCT manufacturer_id FROM " . DB_PREFIX . "product WHERE status <> 0))";

        if($str != NULL){
            $queryString .= " AND m.name LIKE '%" . $str . "%'";
        }

        $query = $this->db->query($queryString);
        return $query->rows;
    }

    public function updateSectionName($sectionId, $newSectionName) {
        $this->db->query("UPDATE ecsection SET `Name`='" . $newSectionName . "' WHERE id=" . $sectionId);
    }

    public function updateSectionState($sectionId, $newSectionState) {
        $this->db->query("UPDATE ecsection SET State='" . $newSectionState . "' WHERE id=" . $sectionId);
    }

    public function updateCollectionName($collectionId, $newCollectionName) {
        $this->db->query("UPDATE eccollection SET `Name`='" . $newCollectionName . "' WHERE id=" . $collectionId);
    }

    public function getLayouts() {
        $query = $this->db->query("SELECT `name`, `route` FROM `layout_route` JOIN `layout` ON `layout_route`.layout_id = `layout`.layout_id WHERE route <> 'common/home'");
        
        foreach ($query->rows as $key=>$row) {
            # code...
            if($row['Route'] == "product/category"){
                $query->rows[$key]['Route'].="&path=".$this->getFirstCategory();
                continue;
            }
            if($row['Route'] == "product/product"){
                $query->rows[$key]['Route'].="&product_id=".$this->getFirstProduct();
                continue;
            }
        }
        return $query->rows;
    }

}
