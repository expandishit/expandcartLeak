<?php
$jsonFileContent = file_get_contents("core.json", true);
$template = json_decode($jsonFileContent);

$this->db->query("DELETE ecpage
                          FROM ecpage
                          INNER JOIN ectemplate ON ectemplate.id = ecpage.TemplateId
                          WHERE ectemplate.CodeName = '" . $templateCodeName . "'");
$this->db->query("DELETE ecobjectfield
                          FROM ecobjectfield
                          LEFT JOIN eccollection ON (ecobjectfield.ObjectId = eccollection.id AND ecobjectfield.ObjectType='ECCOLLECTION')
                          LEFT JOIN ecsection ON (ecobjectfield.ObjectId = ecsection.id AND ecobjectfield.ObjectType='ECSECTION')
                          WHERE eccollection.id IS NULL AND ecsection.id IS NULL;");

$templateId = 0;
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
                $jsonSectionContent = file_get_contents($objSection->FileName, true);
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

$sourceDemoImages = "../image/TemplateImages/" . $templateCodeName;
$destDemoImages = DIR_IMAGE . "/data/" . $templateCodeName;
if (file_exists($sourceDemoImages)) {
    $this->CreateTemplateFolder($sourceDemoImages, $destDemoImages);
}