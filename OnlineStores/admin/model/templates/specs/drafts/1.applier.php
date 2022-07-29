<?php

class ModelTemplatesApplier
{
    /**
     * @var int
     */
    protected $templateId;

    /**
     * @var string
     */
    protected $codeName;

    /**
     * @var string
     */
    protected $baseDir;

    /**
     * @var \Db
     */
    protected $db;

    /**
     * @var array
     */
    protected $languages;

    /**
     * A shadow constructor, used to manual initialize the object.
     *
     * @param array $template
     * @param \Db $db
     * @param string $baseDir
     *
     * @return void
     */
    public function construct($template, $db, $baseDir)
    {
        $this->templateId = $template['id'];

        $this->codeName = $template['CodeName'];

        $this->db = $db;

        $this->baseDir = $baseDir;
    }

    /**
     * Set the basic template Object.
     *
     * @param stdClass $object
     *
     * @return void
     */
    public function setTemplateObject($object)
    {
        $this->template = $object;
    }

    /**
     * Set the available languages.
     *
     * @param array $languages
     *
     * @return void
     */
    public function setLanguages($languages)
    {
        $this->languages = $languages;
    }

    /**
     * Parse locale variables.
     *
     * @param object $description
     * @param object $locales
     *
     * @return object
     */
    public function localize($description, $locales)
    {
        $localized = new \stdClass;

        foreach ($description as $key => $value) {

            if (strpos($value, '@locales.') === 0) {

                $pureString = str_replace('@locales.', '', $value);

                $localized->$key = $locales->$pureString;
            } else {
                $localized->$key = $value;
            }

        }

        return $localized;
    }

    /**
     * Factory method to apply the template depending on the internal class API.
     *
     * @return bool
     */
    public function apply()
    {
        $this->deletePages($this->codeName);
        $this->deleteObjectField($this->codeName);

        foreach ($this->template->pages as $page) {

            $pageId = $this->insertPage($page->code , $page->route);

            foreach ($page->description as $language => $pageDescription) {
                $this->insertPageDescription($pageDescription, $language, $pageId);
            }

            foreach ($page->regions as $region) {

                $regionId = $this->insertRegion($region, $pageId);

                foreach ($region->description as $language => $regionDescription) {
                    $this->insertRegionDescription($regionDescription, $language, $regionId);
                }

                $sections = $this->getSections(
                    array_filter(
                        array_merge(
                            $region->sections->main, $region->sections->bottom, $region->sections->sidebar
                        )
                    )
                );

                $sectionKey = 0;

                foreach ($sections as $section) {

                    $sectionId = $this->insertSection($section, $sectionKey, $regionId);

                    foreach ($section->description as $language => $sectionDescription) {
                        $this->insertSectionDescription($sectionDescription, $language, $sectionId);
                    }

                    if (isset($section->fields) && is_array($section->fields)) {

                        $fieldOrder = 0;

                        foreach ($section->fields as $field) {

                            $fieldId = $this->insertObjectField($field, $fieldOrder, $sectionId, "ECSECTION");

                            foreach ($field->description as $language => $fieldDescription) {
                                $this->insertObjectFieldDescription(
                                    $fieldDescription, $language, $fieldId
                                );
                            }

                            $fieldOrder++;
                        }

                    }

                    if (isset($section->collections) && is_array($section->collections)) {

                        $collectionOrder = 0;

                        foreach ($section->collections as $collection) {

                            $collectionId = $this->insertEcCollection($collection, $collectionOrder, $sectionId);

                            $collectionFieldOrder = 0;

                            foreach ($collection->fields as $collectionField) {

                                $collectionFieldId = $this->insertObjectField(
                                    $collectionField,
                                    $collectionFieldOrder,
                                    $collectionId,
                                    "ECCOLLECTION"
                                );

                                foreach ($collectionField->description as $language => $collectionFieldDescription) {
                                    $this->insertObjectFieldDescription(
                                        $collectionFieldDescription, $language, $collectionFieldId
                                    );
                                }

                                $collectionFieldOrder++;
                            }

                            $collectionOrder++;
                        }

                    }

                    $sectionKey++;
                }

            }

        }
    }

    /**
     * Get sections schemas.
     *
     * @param array $schemas
     *
     * @return array
     */
    private function getSections($schemas)
    {
        $directory = new \RecursiveDirectoryIterator(
            $this->baseDir . $this->codeName . '/template/section', RecursiveDirectoryIterator::SKIP_DOTS
        );

        $iterator = new \RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);

        $files = [];

        foreach ($iterator as $name => $file) {
            if (
                $file->isDir() == false &&
                strtolower($file->getExtension()) == 'json'
            ) {
                if (in_array(substr($file->getBasename(), 0, -5), $schemas)) {
                    $jsonSectionContent = file_get_contents($file->getPathname(), true);
                    $sectionDecoded = json_decode($jsonSectionContent);
                    if($sectionDecoded) {
                        $sections[] = $sectionDecoded;
                    }
                }
            }
        }

        return $sections;
    }

    /**
     * Clear ecpage entries for the applied template.
     *
     * @param string $codeName
     *
     * @return void
     */
    private function deletePages($codeName)
    {
        $query = [];

        $query[] = 'DELETE ecpage FROM ecpage INNER JOIN ectemplate ON ectemplate.id = ecpage.TemplateId';
        $query[] = 'WHERE ectemplate.CodeName = "' . $codeName . '"';

        $this->db->query(implode(' ', $query));
    }

    /**
     * Clear object fields entries for the applied template.
     *
     * @return void
     */
    private function deleteObjectField()
    {
        $query = [];

        $query[] = 'DELETE ecobjectfield FROM ecobjectfield';
        $query[] = 'LEFT JOIN eccollection ON';
        $query[] = '(ecobjectfield.ObjectId = eccollection.id AND ecobjectfield.ObjectType="ECCOLLECTION")';
        $query[] = 'LEFT JOIN ecsection ON';
        $query[] = '(ecobjectfield.ObjectId = ecsection.id AND ecobjectfield.ObjectType="ECSECTION")';
        $query[] = 'WHERE eccollection.id IS NULL AND ecsection.id IS NULL';

        $this->db->query(implode(' ', $query));
    }

    /**
     * Insert a new page.
     *
     * @param string $codeName
     * @param string $route
     *
     * @return int
     */
    private function insertPage($codeName, $route)
    {
        $query = [];

        $query[] = 'INSERT INTO `ecpage`';
        $query[] = '(CodeName, Route, TemplateId)';
        $query[] = 'VALUES ("' . $codeName . '", "'. $route . '", "' . $this->templateId . '")';

        $this->db->query(implode(' ', $query));

        return $this->db->getLastId();
    }

    /**
     * Insert a new page description.
     *
     * @param object $pageDescription
     * @param string $language
     * @param int $pageId
     *
     * @return void
     */
    private function insertPageDescription($pageDescription, $language, $pageId)
    {
        $query = [];

        $query[] = 'INSERT INTO `ecpagedesc` SET';
        $fields[] = 'Name="' . $pageDescription->name . '"';
        $fields[] = 'Description="' . $pageDescription->description . '"';
        $fields[] = 'Lang="' . $language . '"';
        $fields[] = 'PageId="' . $pageId . '"';
        $query[] = implode(', ', $fields);

        $this->db->query(implode(' ', $query));
    }

    /**
     * Insert a new region.
     *
     * @param object $region
     * @param int $pageId
     *
     * @return int
     */
    private function insertRegion($region, $pageId)
    {
        $query = [];

        $query[] = 'INSERT INTO `ecregion` SET';
        $fields[] = 'CodeName="' . $region->code . '"';
        $fields[] = 'RowOrder="' . $region->{"row-order"} . '"';
        $fields[] = 'ColOrder="' . $region->{"col-order"} . '"';
        $fields[] = 'ColWidth="' . $region->{"col-width"} . '"';
        $fields[] = 'PageId="' . $pageId . '"';
        $query[] = implode(', ', $fields);

        $this->db->query(implode(' ', $query));

        return $this->db->getLastId();
    }

    /**
     * Insert a new region description.
     *
     * @param object $regionDescription
     * @param string $language
     * @param int $regionId
     *
     * @return void
     */
    private function insertRegionDescription($regionDescription, $language, $regionId)
    {
        $query = [];

        $query[] = 'INSERT INTO `ecregiondesc` SET';
        $fields[] = 'Name="' . $regionDescription->name . '"';
        $fields[] = 'Description="' . $regionDescription->description . '"';
        $fields[] = 'Lang="' . $language . '"';
        $fields[] = 'RegionId="' . $regionId . '"';
        $query[] = implode(', ', $fields);

        $this->db->query(implode(' ', $query));
    }

    /**
     * Insert a new section.
     *
     * @param object $section
     * @param int $sortOrder
     * @param int $regionId
     *
     * @return int
     */
    private function insertSection($section, $sortOrder, $regionId)
    {
        $query = [];

        $query[] = 'INSERT INTO `ecsection` SET';
        $fields[] = 'CodeName="' . $section->code . '"';
        $fields[] = 'Name="' . $section->name . '"';
        $fields[] = 'Type="' . $section->type . '"';
        $fields[] = 'State="' . $section->state . '"';
        $fields[] = 'IsCollection="' . $section->{"is-collection"} . '"';
        $fields[] = 'SortOrder="' . $sortOrder . '"';
        $fields[] = 'RegionId="' . $regionId . '"';
        $query[] = implode(', ', $fields);

        $this->db->query(implode(' ', $query));

        return $this->db->getLastId();
    }

    /**
     * Insert a new section description.
     *
     * @param object $regionDescription
     * @param string $language
     * @param int $sectionId
     *
     * @return void
     */
    private function insertSectionDescription($regionDescription, $language, $sectionId)
    {
        $query = [];

        $query[] = 'INSERT INTO `ecsectiondesc` SET';
        $fields[] = 'Name="' . $regionDescription->name . '"';
        $fields[] = 'Description="' . $regionDescription->description . '"';
        $fields[] = 'Image="' . $regionDescription->image . '"';
        $fields[] = 'Thumbnail="' . $regionDescription->thumbnail . '"';
        $fields[] = 'CollectionName="' . $regionDescription->{"collection-name"} . '"';
        $fields[] = 'CollectionItemName="' . $regionDescription->{"item-name"} . '"';
        $fields[] = 'CollectionButtonName="' . $regionDescription->{"button-name"} . '"';
        $fields[] = 'Lang="' . $language . '"';
        $fields[] = 'SectionId="' . $sectionId . '"';
        $query[] = implode(', ', $fields);

        $this->db->query(implode(' ', $query));
    }

    /**
     * Insert a new object field.
     *
     * @param object $object
     * @param int $sortOrder
     * @param int $parentId
     * @param string $objectType
     *
     * @return int
     */
    private function insertObjectField($object, $sortOrder, $parentId, $objectType)
    {
        $query = [];

        $query[] = 'INSERT INTO `ecobjectfield` SET';
        $fields[] = 'CodeName="' . $object->code . '"';
        $fields[] = 'Type="' . $object->type . '"';
        $fields[] = 'SortOrder="' . $sortOrder . '"';
        $fields[] = 'LookUpKey="' . $object->{"option-id"} . '"';
        $fields[] = 'IsMultiLang="' . $object->{"multi-lang"} . '"';
        $fields[] = 'ObjectId="' . $parentId . '"';
        $fields[] = 'ObjectType="' . $objectType . '"';
        $query[] = implode(', ', $fields);

        $this->db->query(implode(' ', $query));

        return $this->db->getLastId();
    }

    /**
     * Insert a new object field description.
     *
     * @param object $description
     * @param string $language
     * @param int $parentId
     *
     * @return void
     */
    private function insertObjectFieldDescription($description, $language, $parentId)
    {
        $query = [];

        $query[] = 'INSERT INTO `ecobjectfielddesc` SET';
        $fields[] = 'Name="' . $description->name . '"';
        $fields[] = 'Description="' . $description->description . '"';
        $fields[] = 'Lang="' . $language . '"';
        $fields[] = 'ObjectFieldId="' . $parentId . '"';
        $query[] = implode(', ', $fields);

        $this->db->query(implode(' ', $query));
    }

    /**
     * Insert a new object field value.
     *
     * @param object $description
     * @param string $language
     * @param int $parentId
     *
     * @return void
     */
    private function insertObjectFieldValue($description, $language, $parentId)
    {
        $query = [];

        $query[] = 'INSERT INTO `ecobjectfieldval` SET';
        $fields[] = 'Name="' . $description->value . '"';
        $fields[] = 'Lang="' . $language . '"';
        $fields[] = 'ObjectFieldId="' . $parentId . '"';
        $query[] = implode(', ', $fields);

        $this->db->query(implode(' ', $query));
    }

    /**
     * Insert a new collection.
     *
     * @param object $collection
     * @param int $sortOrder
     * @param int $sectionId
     *
     * @return int
     */
    private function insertEcCollection($collection, $sortOrder, $sectionId)
    {
        $query = [];

        $query[] = 'INSERT INTO `eccollection` SET';
        $fields[] = 'Name="' . $collection->name . '"';
        $fields[] = 'Thumbnail="' . $collection->thumbnail . '"';
        $fields[] = 'IsDefault="' . $collection->{"is-default"} . '"';
        $fields[] = 'SortOrder="' . $sortOrder . '"';
        $fields[] = 'SectionId="' . $sectionId . '"';
        $query[] = implode(', ', $fields);

        $this->db->query(implode(' ', $query));

        return $this->db->getLastId();
    }
}
