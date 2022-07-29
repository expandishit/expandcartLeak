<?php
class ModelMeditorMeditor extends Model {
    public function executeDefaultSettings($templateName = "") {

        ///Check for custom theme
        $jsonPath = "mobilesettings/";
        $mobile_theme = $templateName ?? $this->config->get('config_mobile_template') ?? 'default';

        if($mobile_theme != 'default' && file_exists(DIR_APPLICATION."model/meditor/customtheme/".$mobile_theme."/core.json"))
            $jsonPath = "customtheme/".$mobile_theme."/";
        /////////////////////////

        $jsonFileContent = file_get_contents($jsonPath."core.json", true);
        $template = json_decode($jsonFileContent);
        $templateCodeName = $template->CodeName;
        $this->db->query("DELETE ectemplate
                          FROM ectemplate
                          WHERE ectemplate.CodeName = '" . $templateCodeName . "'");
        $this->db->query("DELETE ecobjectfield
                          FROM ecobjectfield
                          LEFT JOIN eccollection ON (ecobjectfield.ObjectId = eccollection.id AND ecobjectfield.ObjectType='ECCOLLECTION')
                          LEFT JOIN ecsection ON (ecobjectfield.ObjectId = ecsection.id AND ecobjectfield.ObjectType='ECSECTION')
                          WHERE eccollection.id IS NULL AND ecsection.id IS NULL;");

        $this->db->query("INSERT INTO `ectemplate` (CodeName, NextGenTemplate, ExpandishTemplate) VALUES ('$template->CodeName', '1', '1')");
        //$templateId = $this->db->getLastId();
        $query = $this->db->query("SELECT id FROM ectemplate WHERE ectemplate.CodeName = '" . $templateCodeName . "'");
        $templateId=$query->row['id'];

        foreach ($template->Pages as $page) {
            $this->db->query("INSERT INTO `ecpage` (CodeName, Route, TemplateId) VALUES ('$page->CodeName', '$page->Route', '$templateId')");

            $pageId = $this->db->getLastId();

            $this->db->query("INSERT INTO `ecpagedesc` (Name, Description, Lang, PageId) VALUES ('$page->NameEN', '$page->DescEN', 'en', '$pageId')");
            $this->db->query("INSERT INTO `ecpagedesc` (Name, Description, Lang, PageId) VALUES ('$page->NameAR', '$page->DescAR', 'ar', '$pageId')");

            foreach ($page->Regions as $region) {
                $this->db->query("INSERT INTO `ecregion` (CodeName, RowOrder, ColOrder, ColWidth, PageId) VALUES ('$region->CodeName', '$region->RowOrder', '$region->ColOrder', '$region->ColWidth', '$pageId')");

                $regionId = $this->db->getLastId();

                $this->db->query("INSERT INTO `ecregiondesc` (Name, Description, Lang, RegionId) VALUES ('$region->NameEN', '$region->DescEN', 'en', '$regionId')");
                $this->db->query("INSERT INTO `ecregiondesc` (Name, Description, Lang, RegionId) VALUES ('$region->NameAR', '$region->DescAR', 'ar', '$regionId')");

                $sectionOrder = 0;
                $sections = array();
                if($region->Sections) {
                    $sections = $region->Sections;
                }
                elseif ($region->SectionsFiles) {
                    foreach ($region->SectionsFiles as $objSection) {
                        $jsonSectionContent = file_get_contents($jsonPath . $objSection->FileName, true);
                        $sectionDecoded = json_decode($jsonSectionContent);
                        $sectionDecoded->Type = $objSection->Type;
                        if ($sectionDecoded) {
                            $sections[] = $sectionDecoded;
                        }
                    }
                }

                foreach ($sections as $section) {
                    $this->db->query("INSERT INTO `ecsection` (CodeName, Name, Type, State, IsCollection, SortOrder, RegionId) VALUES ('$section->CodeName', '$section->Name', '$section->Type', '$section->State', '$section->IsCollection', '$sectionOrder', '$regionId')");

                    $sectionId = $this->db->getLastId();

                    $this->db->query("INSERT INTO `ecsectiondesc` (Name, Description, Image, Thumbnail, CollectionName, CollectionItemName, CollectionButtonName, Lang, SectionId) VALUES ('$section->NameEN', '$section->DescEN', '$section->ImageEN', '$section->ThumbnailEN', '$section->CollectionNameEN', '$section->CollectionItemNameEN', '$section->CollectionButtonNameEN', 'en', '$sectionId')");
                    $this->db->query("INSERT INTO `ecsectiondesc` (Name, Description, Image, Thumbnail, CollectionName, CollectionItemName, CollectionButtonName, Lang, SectionId) VALUES ('$section->NameAR', '$section->DescAR', '$section->ImageAR', '$section->ThumbnailAR', '$section->CollectionNameAR', '$section->CollectionItemNameAR', '$section->CollectionButtonNameAR', 'ar', '$sectionId')");

                    $fieldOrder = 0;

                    if (isset($section->Fields))
                        foreach($section->Fields as $field) {
                            $this->db->query("INSERT INTO `ecobjectfield` (CodeName, Type, SortOrder, LookUpKey, IsMultiLang, ObjectId, ObjectType) VALUES ('$field->CodeName', '$field->Type', '$fieldOrder', '$field->LookUpKey', '$field->IsMultiLang', '$sectionId', 'ECSECTION')");

                            $fieldId = $this->db->getLastId();

                            $this->db->query("INSERT INTO `ecobjectfielddesc` (Name, Description, Lang, ObjectFieldId) VALUES ('$field->NameEN', '$field->DescEN', 'en', '$fieldId')");
                            $this->db->query("INSERT INTO `ecobjectfielddesc` (Name, Description, Lang, ObjectFieldId) VALUES ('$field->NameAR', '$field->DescAR', 'ar', '$fieldId')");

                            $this->db->query("INSERT INTO `ecobjectfieldval` (Value, Lang, ObjectFieldId) VALUES ('$field->ValueEN', 'en', '$fieldId')");
                            $this->db->query("INSERT INTO `ecobjectfieldval` (Value, Lang, ObjectFieldId) VALUES ('$field->ValueAR', 'ar', '$fieldId')");

                            $fieldOrder++;
                        }

                    $collectionOrder = 0;

                    if (isset($section->Collections))
                        foreach($section->Collections as $collection) {
                            $this->db->query("INSERT INTO `eccollection` (Name, Thumbnail, IsDefault, SortOrder, SectionId) VALUES ('$collection->Name', '$collection->Thumbnail', '$collection->IsDefault', '$collectionOrder', '$sectionId')");

                            $collectionId = $this->db->getLastId();

                            $collectionFieldOrder = 0;

                            foreach($collection->Fields as $collectionField) {
                                $this->db->query("INSERT INTO `ecobjectfield` (CodeName, Type, SortOrder, LookUpKey, IsMultiLang, ObjectId, ObjectType) VALUES ('$collectionField->CodeName', '$collectionField->Type', '$collectionFieldOrder', '$collectionField->LookUpKey', '$collectionField->IsMultiLang', '$collectionId', 'ECCOLLECTION')");

                                $collectionFieldId = $this->db->getLastId();

                                $this->db->query("INSERT INTO `ecobjectfielddesc` (Name, Description, Lang, ObjectFieldId) VALUES ('$collectionField->NameEN', '$collectionField->DescEN', 'en', '$collectionFieldId')");
                                $this->db->query("INSERT INTO `ecobjectfielddesc` (Name, Description, Lang, ObjectFieldId) VALUES ('$collectionField->NameAR', '$collectionField->DescAR', 'ar', '$collectionFieldId')");

                                $this->db->query("INSERT INTO `ecobjectfieldval` (Value, Lang, ObjectFieldId) VALUES ('$collectionField->ValueEN', 'en', '$collectionFieldId')");
                                $this->db->query("INSERT INTO `ecobjectfieldval` (Value, Lang, ObjectFieldId) VALUES ('$collectionField->ValueAR', 'ar', '$collectionFieldId')");

                                $collectionFieldOrder++;
                            }

                            $collectionOrder++;
                        }

                    $sectionOrder++;
                }
            }
        }

//        $sourceDemoImages = "../image/TemplateImages/" . $templateCodeName;
//        $destDemoImages = DIR_IMAGE . "/data/" . $templateCodeName;
//        if (file_exists($sourceDemoImages)) {
//            $this->CreateTemplateFolder($sourceDemoImages, $destDemoImages);
//        }
    }

    public function updateSettings($templateName = "") {

        ///Check for custom theme
        $jsonPath = "mobilesettings/";
        $mobile_theme = $templateName ?? $this->config->get('config_mobile_template') ?? 'default';

        if($mobile_theme != 'default' && file_exists(DIR_APPLICATION."model/meditor/customtheme/".$mobile_theme."/core.json"))
            $jsonPath = "customtheme/".$mobile_theme."/";
        /////////////////////////

        $jsonFileContent = file_get_contents($jsonPath."core.json", true);
        $template = json_decode($jsonFileContent);
        $templateCodeName = $template->CodeName;
        /*$this->db->query("DELETE ectemplate
                          FROM ectemplate
                          WHERE ectemplate.CodeName = '" . $templateCodeName . "'");
        $this->db->query("DELETE ecobjectfield
                          FROM ecobjectfield
                          LEFT JOIN eccollection ON (ecobjectfield.ObjectId = eccollection.id AND ecobjectfield.ObjectType='ECCOLLECTION')
                          LEFT JOIN ecsection ON (ecobjectfield.ObjectId = ecsection.id AND ecobjectfield.ObjectType='ECSECTION')
                          WHERE eccollection.id IS NULL AND ecsection.id IS NULL;");

        $this->db->query("INSERT INTO `ectemplate` (CodeName, NextGenTemplate, ExpandishTemplate) VALUES ('$template->CodeName', '1', '1')");*/
        //$templateId = $this->db->getLastId();
        $query = $this->db->query("SELECT id FROM ectemplate WHERE ectemplate.CodeName = '" . $templateCodeName . "'");
        $templateId=$query->row['id'];

        foreach ($template->Pages as $page) {
            if($page->CodeName != 'contactus')
                continue;

            $query  = $this->db->query("SELECT id FROM ecpage WHERE ecpage.CodeName = '" . $page->CodeName . "' AND ecpage.TemplateId='" . $templateId . "'");
            $pageId = $query->row['id'];

            if(!$pageId)
                continue;
            /*if($pageId){
                $this->db->query("UPDATE `ecpage` SET Route='".$page->Route."' WHERE id='".$pageId."'");

                $this->db->query("UPDATE `ecpagedesc` SET Name = '".$page->NameEN."',  Description='".$page->DescEN."' WHERE Lang='en' AND PageId='".$pageId."'");
                $this->db->query("UPDATE `ecpagedesc` SET Name = '".$page->NameAR."',  Description='".$page->DescAR."' WHERE Lang='ar' AND PageId='".$pageId."'");
            }else{
                $this->db->query("INSERT INTO `ecpage` (CodeName, Route, TemplateId) VALUES ('$page->CodeName', '$page->Route', '$templateId')");
                $pageId = $this->db->getLastId();

                $this->db->query("INSERT INTO `ecpagedesc` (Name, Description, Lang, PageId) VALUES ('$page->NameEN', '$page->DescEN', 'en', '$pageId')");
                $this->db->query("INSERT INTO `ecpagedesc` (Name, Description, Lang, PageId) VALUES ('$page->NameAR', '$page->DescAR', 'ar', '$pageId')");
            }*/

            foreach ($page->Regions as $region) {

                $query    = $this->db->query("SELECT id FROM ecregion WHERE ecregion.PageId = '" . $pageId . "' AND ecregion.CodeName= '" . $region->CodeName . "'");
                $regionId = $query->row['id'];

                if(!$regionId)
                    continue;
                /*if($regionId){
                    $this->db->query("UPDATE `ecregion` SET RowOrder='".$region->RowOrder."',  ColOrder='".$region->ColOrder."',  ColWidth='".$region->ColWidth."' WHERE id='".$pageId."'");

                    $this->db->query("UPDATE `ecregiondesc` SET Name = '".$region->NameEN."',  Description='".$page->DescEN."' WHERE Lang='en' AND RegionId='".$regionId."'");
                    $this->db->query("UPDATE `ecregiondesc` SET Name = '".$region->NameAR."',  Description='".$page->DescEN."' WHERE Lang='ar' AND RegionId='".$regionId."'");
                }else{
                    $this->db->query("INSERT INTO `ecregion` (CodeName, RowOrder, ColOrder, ColWidth, PageId) VALUES ('$region->CodeName', '$region->RowOrder', '$region->ColOrder', '$region->ColWidth', '$pageId')");
                    $regionId = $this->db->getLastId();

                    $this->db->query("INSERT INTO `ecregiondesc` (Name, Description, Lang, RegionId) VALUES ('$region->NameEN', '$region->DescEN', 'en', '$regionId')");
                    $this->db->query("INSERT INTO `ecregiondesc` (Name, Description, Lang, RegionId) VALUES ('$region->NameAR', '$region->DescAR', 'ar', '$regionId')");
                }*/

                $sectionOrder = 0;
                $sections = array();
                if($region->Sections) {
                    $sections = $region->Sections;
                }
                elseif ($region->SectionsFiles) {
                    foreach ($region->SectionsFiles as $objSection) {
                        $jsonSectionContent = file_get_contents($jsonPath . $objSection->FileName, true);
                        $sectionDecoded = json_decode($jsonSectionContent);
                        $sectionDecoded->Type = $objSection->Type;
                        if ($sectionDecoded) {
                            $sections[] = $sectionDecoded;
                        }
                    }
                }

                foreach ($sections as $section) {
                    $query     = $this->db->query("SELECT id FROM ecsection WHERE ecsection.RegionId = '" . $regionId . "' AND ecsection.CodeName = '" . $section->CodeName . "'");
                    $sectionId = $query->row['id'];

                    if(!$sectionId)
                        continue;

                    /*if($sectionId){
                        $this->db->query("UPDATE `ecsection` SET Name='".$section->Name."',  Type='".$section->Type."',  State='".$section->State."',  IsCollection='".$section->IsCollection."',  SortOrder='".$sectionOrder."' WHERE id='".$sectionId."'");

                        $this->db->query("UPDATE `ecsectiondesc` SET Name = '".$section->NameEN."',  Description='".$section->DescEN."',  Image='".$section->ImageEN."',  Thumbnail='".$section->ThumbnailEN."',  CollectionName='".$section->CollectionNameEN."',  CollectionItemName='".$section->CollectionItemNameEN."',  CollectionButtonName='".$section->CollectionButtonNameEN."' WHERE Lang='en' AND SectionId='".$sectionId."'");
                        $this->db->query("UPDATE `ecsectiondesc` SET Name = '".$section->NameAR."',  Description='".$section->DescAR."',  Image='".$section->ImageAR."',  Thumbnail='".$section->ThumbnailAR."',  CollectionName='".$section->CollectionNameAR."',  CollectionItemName='".$section->CollectionItemNameAR."',  CollectionButtonName='".$section->CollectionButtonNameAR."' WHERE Lang='ar' AND SectionId='".$sectionId."'");
                    }else {
                        $this->db->query("INSERT INTO `ecsection` (CodeName, Name, Type, State, IsCollection, SortOrder, RegionId) VALUES ('$section->CodeName', '$section->Name', '$section->Type', '$section->State', '$section->IsCollection', '$sectionOrder', '$regionId')");

                        $sectionId = $this->db->getLastId();

                        $this->db->query("INSERT INTO `ecsectiondesc` (Name, Description, Image, Thumbnail, CollectionName, CollectionItemName, CollectionButtonName, Lang, SectionId) VALUES ('$section->NameEN', '$section->DescEN', '$section->ImageEN', '$section->ThumbnailEN', '$section->CollectionNameEN', '$section->CollectionItemNameEN', '$section->CollectionButtonNameEN', 'en', '$sectionId')");
                        $this->db->query("INSERT INTO `ecsectiondesc` (Name, Description, Image, Thumbnail, CollectionName, CollectionItemName, CollectionButtonName, Lang, SectionId) VALUES ('$section->NameAR', '$section->DescAR', '$section->ImageAR', '$section->ThumbnailAR', '$section->CollectionNameAR', '$section->CollectionItemNameAR', '$section->CollectionButtonNameAR', 'ar', '$sectionId')");
                    }*/

                    $fieldOrder = 0;

                    if (isset($section->Fields))
                        foreach($section->Fields as $field) {
                            $query     = $this->db->query("SELECT id FROM ecobjectfield WHERE ecobjectfield.ObjectId = '" . $sectionId . "' AND ecobjectfield.CodeName = '" . $field->CodeName . "'");
                            $fieldId = $query->row['id'];

                            if(!$fieldId){
                                $this->db->query("INSERT INTO `ecobjectfield` (CodeName, Type, SortOrder, LookUpKey, IsMultiLang, ObjectId, ObjectType) VALUES ('$field->CodeName', '$field->Type', '$fieldOrder', '$field->LookUpKey', '$field->IsMultiLang', '$sectionId', 'ECSECTION')");

                                $fieldId = $this->db->getLastId();

                                $this->db->query("INSERT INTO `ecobjectfielddesc` (Name, Description, Lang, ObjectFieldId) VALUES ('$field->NameEN', '$field->DescEN', 'en', '$fieldId')");
                                $this->db->query("INSERT INTO `ecobjectfielddesc` (Name, Description, Lang, ObjectFieldId) VALUES ('$field->NameAR', '$field->DescAR', 'ar', '$fieldId')");

                                $this->db->query("INSERT INTO `ecobjectfieldval` (Value, Lang, ObjectFieldId) VALUES ('$field->ValueEN', 'en', '$fieldId')");
                                $this->db->query("INSERT INTO `ecobjectfieldval` (Value, Lang, ObjectFieldId) VALUES ('$field->ValueAR', 'ar', '$fieldId')");
                            }
                            $fieldOrder++;
                        }

                    $collectionOrder = 0;

                    if (isset($section->Collections))
                        foreach($section->Collections as $collection) {

                            $query     = $this->db->query("SELECT id FROM eccollection WHERE eccollection.SectionId = '" . $sectionId . "'");
                            $collectionId = $query->row['id'];

                            if(!$collectionId)
                                continue;

                            /*if($collectionId){
                                $this->db->query("UPDATE `eccollection` SET Name='".$collection->Name."',  Thumbnail='".$collection->Thumbnail."',  IsDefault='".$collection->IsDefault."',  SortOrder='".$collectionOrder."' WHERE id='".$collectionId."'");
                            }else{
                                $this->db->query("INSERT INTO `eccollection` (Name, Thumbnail, IsDefault, SortOrder, SectionId) VALUES ('$collection->Name', '$collection->Thumbnail', '$collection->IsDefault', '$collectionOrder', '$sectionId')");
                                $collectionId = $this->db->getLastId();
                            }*/

                            $collectionFieldOrder = 0;

                            foreach($collection->Fields as $collectionField) {
                                $query    = $this->db->query("SELECT id FROM ecobjectfield WHERE ecobjectfield.ObjectId = '" . $collectionId . "' AND ecobjectfield.CodeName = '" . $collectionField->CodeName . "'");
                                $fieldId = $query->row['id'];

                                if(!$fieldId) {
                                    $this->db->query("INSERT INTO `ecobjectfield` (CodeName, Type, SortOrder, LookUpKey, IsMultiLang, ObjectId, ObjectType) VALUES ('$collectionField->CodeName', '$collectionField->Type', '$collectionFieldOrder', '$collectionField->LookUpKey', '$collectionField->IsMultiLang', '$collectionId', 'ECCOLLECTION')");

                                    $collectionFieldId = $this->db->getLastId();

                                    $this->db->query("INSERT INTO `ecobjectfielddesc` (Name, Description, Lang, ObjectFieldId) VALUES ('$collectionField->NameEN', '$collectionField->DescEN', 'en', '$collectionFieldId')");
                                    $this->db->query("INSERT INTO `ecobjectfielddesc` (Name, Description, Lang, ObjectFieldId) VALUES ('$collectionField->NameAR', '$collectionField->DescAR', 'ar', '$collectionFieldId')");

                                    $this->db->query("INSERT INTO `ecobjectfieldval` (Value, Lang, ObjectFieldId) VALUES ('$collectionField->ValueEN', 'en', '$collectionFieldId')");
                                    $this->db->query("INSERT INTO `ecobjectfieldval` (Value, Lang, ObjectFieldId) VALUES ('$collectionField->ValueAR', 'ar', '$collectionFieldId')");
                                }
                                $collectionFieldOrder++;
                            }

                            $collectionOrder++;
                        }

                    $sectionOrder++;
                }
            }
        }

        $this->resetDraftVersion($templateCodeName);
//        $sourceDemoImages = "../image/TemplateImages/" . $templateCodeName;
//        $destDemoImages = DIR_IMAGE . "/data/" . $templateCodeName;
//        if (file_exists($sourceDemoImages)) {
//            $this->CreateTemplateFolder($sourceDemoImages, $destDemoImages);
//        }
    }

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
                                        LEFT JOIN `ectemplatedesc` ON `ectemplate`.`id` = `ectemplatedesc`.`TemplateId`
                                        WHERE `ectemplate`.`CodeName` = '" . $templateCodeName . "'
                                        AND (`ectemplatedesc`.`Lang` = '" . $this->language->get('code') . "' OR `ectemplatedesc`.`id` IS NULL)");
        return $query->row;
    }

    public function getTemplatePages($templateCodeName) {
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
                                        AND Lang = '" . $this->language->get('code') . "'");
        return $query->rows;
    }

    public function getTemplateRegions($templateCodeName) {
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
        }
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
    }

    public function getSections($templateCodeName, $sectionType, $regionId = 0) {
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
                                        INNER JOIN ecregion ON ecsection.RegionId = ecregion.id
                                        INNER JOIN ecpage ON ecregion.PageId = ecpage.id
                                        INNER JOIN ectemplate ON ectemplate.id = ecpage.TemplateId
                                        WHERE ecsection.Type = '" . $sectionType . "'
                                        AND ectemplate.CodeName = '" . $templateCodeName . "'
                                        AND (ecregion.id = " . $regionId . " OR 0 = " . $regionId . ")
                                        AND ecsectiondesc.Lang = '" . $this->language->get('code') . "'");
        return $query->rows;
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
                                        WHERE eccollection.IsDefault = 0 AND ecsection.id = '" . $sectionId . "'");
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

    public function getAllSectionObjectFields($sectionId){
        $query = $this->db->query("
                                    SELECT
                                    ecobjectfield.*,
                                    ecobjectfielddesc.`Name`,
                                    ecobjectfielddesc.Description
                                    FROM ecobjectfield
                                    INNER JOIN ecobjectfielddesc ON ecobjectfielddesc.ObjectFieldId = ecobjectfield.id
                                    INNER JOIN ecsection ON ecobjectfield.ObjectId = ecsection.id AND ecobjectfield.ObjectType = 'ECSECTION'
                                    WHERE ecsection.id = '" . (int)$sectionId . "'
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
                                    WHERE eccollection.IsDefault = 0 AND ecsection.id = '" . (int)$sectionId . "'
                                    AND ecobjectfielddesc.Lang = '" . $this->language->get('code') . "'
                                    ");
        return $query->rows;
    }

    public function getAllSectionObjectFieldsVal($sectionId){
        $query = $this->db->query("
                                    SELECT ecobjectfieldval.*
                                    FROM ecobjectfieldval
                                    INNER JOIN ecobjectfield ON ecobjectfield.id = ecobjectfieldval.ObjectFieldId
                                    INNER JOIN ecsection ON ecobjectfield.ObjectId = ecsection.id AND ecobjectfield.ObjectType = 'ECSECTION'
                                    INNER JOIN ecregion ON ecsection.RegionId = ecregion.id
                                    WHERE ecsection.id = '" . (int)$sectionId . "'
                                    UNION ALL
                                    SELECT ecobjectfieldval.*
                                    FROM ecobjectfieldval
                                    INNER JOIN ecobjectfield ON ecobjectfield.id = ecobjectfieldval.ObjectFieldId
                                    INNER JOIN eccollection ON eccollection.id = ecobjectfield.ObjectId AND ecobjectfield.ObjectType = 'ECCOLLECTION'
                                    INNER JOIN ecsection ON eccollection.SectionId = ecsection.id
                                    WHERE eccollection.IsDefault = 0 AND ecsection.id = '" . (int)$sectionId . "'");
        return $query->rows;
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

    public function insertSection($sourceSectionId, $sourceType, $destType, $destSortOrder)
    {
//      $query = $this->db->query("SELECT * FROM ecsection WHERE id= " . $sourceSectionId . " AND Type = '" . $sourceType . "'");
//      $section = $query->row;

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
        $this->db->query("UPDATE ecobjectfieldval
                          SET `Value`='" . $this->db->escape($newValue) . "'
                          WHERE id=" . (int)$id);
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

    public function getProducts($fieldId, $isProductId = false) {
        if($isProductId) {
            $productIds = $fieldId;
        } else {
            $query = $this->db->query("SELECT `Value` FROM ecobjectfieldval WHERE ecobjectfieldval.ObjectFieldId=" . (int)$fieldId . " limit 1");
            $productIds = $query->row["Value"];
        }
        if($productIds != "") {
            $query = $this->db->query("SELECT p.product_id AS value, pd.name AS display FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id IN(" . $productIds . ") AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
            return $query->rows;
        }
        else {
            return array();
        }
    }

    public function getCategories($fieldId, $iscategoryId = false) {
        if($iscategoryId) {
            $categoryIds = $fieldId;
        } else {
            $query = $this->db->query("SELECT `Value` FROM ecobjectfieldval WHERE ecobjectfieldval.ObjectFieldId=" . (int)$fieldId . " limit 1");
            $categoryIds = $query->row["Value"];
        }
        if($categoryIds != "") {
            $query = $this->db->query("SELECT c.category_id AS value, cd.name AS display 
                                        FROM category c 
                                        LEFT JOIN category_description cd ON (c.category_id = cd.category_id) 
                                        WHERE c.category_id IN(" . $categoryIds . ") 
                                        AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
            return $query->rows;
        }
        else {
            return array();
        }
    }
    
    public function getLinks($fieldId, $isLinkId = false) {
        return array(['value' => '1','display' => 'register'],['value' => '1','display' => 'تسجيل']);
    }
    
    public function getBrands($fieldId, $isBrandId = false) {
        if($isBrandId) {
            $brandIds = $fieldId;
        } else {
            $query = $this->db->query("SELECT `Value` FROM ecobjectfieldval WHERE ecobjectfieldval.ObjectFieldId=" . (int)$fieldId . " limit 1");
            $brandIds = $query->row["Value"];
        }
        if($brandIds != "") {
            $query = $this->db->query("SELECT name AS value 
                                        FROM manufacturer
                                        WHERE manufacturer_id IN(" . $brandIds . ")");
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
        $query = $this->db->query("SELECT c.category_id AS value, (SELECT cd1.name FROM category_description cd1 WHERE c.category_id = cd1.category_id AND cd1.language_id='" . (int)$this->config->get('config_language_id') . "') AS display 
                                    FROM category c
                                    LEFT JOIN category_description cd ON (c.category_id = cd.category_id) 
                                    WHERE cd.name LIKE '%" . $str . "%'");
        return $query->rows;
    }

    public function searchLinks($str)
    {
        return array(['value' => '1','display' => 'register'],['value' => '1','display' => 'تسجيل']);
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
        return $query->rows;
    }


}