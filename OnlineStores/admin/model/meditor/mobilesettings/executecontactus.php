<?php
$jsonFileContent = file_get_contents("core.json", true);
$template = json_decode($jsonFileContent);

$this->db->query("DELETE ecobjectfield 
                    FROM ecobjectfield 
                    JOIN ecsection ON ecobjectfield.ObjectId = ecsection.id AND ecobjectfield.ObjectType = 'ECSECTION'
                    JOIN ecregion ON ecsection.RegionId = ecregion.id
                    JOIN ecpage ON ecregion.PageId = ecpage.id
                    JOIN ectemplate ON ecpage.TemplateId = ectemplate.id
                    WHERE ectemplate.CodeName = 'mobile-app'
                    AND ecpage.CodeName = 'contactus'
                    AND ecsection.CodeName = 'details';");

$this->db->query("DELETE ecsection 
                    FROM ecsection 
                    JOIN ecregion ON ecsection.RegionId = ecregion.id
                    JOIN ecpage ON ecregion.PageId = ecpage.id
                    JOIN ectemplate ON ecpage.TemplateId = ectemplate.id
                    WHERE ectemplate.CodeName = 'mobile-app'
                    AND (ecsection.Type='draft' OR ecpage.CodeName='contactus');");

$templateId = 0;
$query = $this->db->query("SELECT ectemplate.id `TemplateId`, ecregion.id `RegionId` 
                            FROM ectemplate
                            JOIN ecpage ON ectemplate.id=ecpage.TemplateId
                            JOIN ecregion ON ecpage.id=ecregion.PageId
                            WHERE ectemplate.CodeName = 'mobile-app'
                            AND ecpage.CodeName='contactus'");
$templateId=$query->row['TemplateId'];
$regionId = $query->row['RegionId'];

foreach ($template->Pages as $page) {

    if($page->CodeName == 'contactus')
    foreach ($page->Regions as $region) {
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
