<?php

class ModelTemplatesApplier extends Model
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
     * @var array
     */
    protected $template;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $defaultLocales = [
        'en' => [
            'template_settings_name' => 'Settings',
            'styling_name' => 'Styling',
            'header_name' => 'Header',
            'footer_name' => 'Footer',
        ],
        'ar' => [
            'template_settings_name' => 'الإعدادات',
            'styling_name' => 'التصميم',
            'header_name' => 'رأس الموقع',
            'footer_name' => 'تذييل الموقع',
        ],
    ];

    /**
     * A shadow constructor, used to initialize the object manually.
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
     * Set locales array.
     *
     * @param array $locales
     *
     * @return void
     */
    public function setLocals($locales)
    {
        $this->template['locales'] = array_merge_recursive($locales, $this->defaultLocales);
    }

    /**
     * Parse locale variables.
     *
     * @param array $description
     * @param array $locales
     * @param string $language
     *
     * @return array
     */
    public function localize($description, $locales, $language = 'en')
    {
        $localized = [];

        foreach ($description as $key => $value) {

            if (strpos($value, '@locales.') === 0) {

                $pureString = str_replace('@locales.', '', $value);

                $localized[$key] = (
                isset($locales[$pureString]) ?
                    $locales[$pureString] :
                    $this->template['locales'][$language][$pureString]
                );
            } else {
                $localized[$key] = $value;
            }
        }

        return $localized;
    }

    /**
     * Apply and merge settings into pages.
     *
     * @return void
     */
    public function applySettings()
    {
        array_unshift($this->template['pages'], [
            'code' => 'templatesettings',
            'description' => [
                'name' => '@locales.template_settings_name',
                'description' => ''
            ],
            'regions' => [
                [
                    'code' => 'styling',
                    "col-order" => -1,
                    "row-order" => -1,
                    "col-width" => -1,
                    'description' => [
                        'name' => '@locales.styling_name',
                        'description' => ''
                    ],
                    'sections' => $this->template['settings']['style']
                ],
            ]
        ]);

        array_unshift($this->template['pages'][1]['regions'], [
            'code' => 'header',
            "col-order" => -1,
            "row-order" => -1,
            "col-width" => -1,
            'description' => [
                'name' => '@locales.header_name',
                'description' => ''
            ],
            'sections' => $this->template['settings']['header']
        ]);

        array_push($this->template['pages'][1]['regions'], [
            'code' => 'footer',
            "col-order" => -1,
            "row-order" => -1,
            "col-width" => -1,
            'description' => [
                'name' => '@locales.footer_name',
                'description' => ''
            ],
            'sections' => $this->template['settings']['footer']
        ]);
    }

    public function setOptions( $options )
    {
        $this->options = $options;
    }

    /**
     * Factory method to apply the template depending on the internal class API.
     *
     * @return void
     */
    public function apply()
    {
        $this->deletePages($this->codeName);
        $this->deleteObjectField($this->codeName);

        if ( ! empty($this->options) ) {

            $templateModel = $this->load->model("templates/template", ['return' => true]);

            $templateModel->beginTransaction();
            $templateModel->EmptyECLookupTable();

            foreach ($this->options as $option) {
                $option = (object) $option;

                $templateModel->newOption($option);
            }

            $templateModel->commitTransaction();
        }

        foreach ($this->template['pages'] as $page) {

            $pageId = $this->insertPage($page['code'], $page['route']);

            foreach ($this->languages as $language) {
                $pageDescription = $this->localize($page['description'], $this->template['locales'][$language], $language);
                $this->insertPageDescription($pageDescription, $language, $pageId);
            }

            foreach ($page['regions'] as $region) {

                $regionId = $this->insertRegion($region, $pageId);

                foreach ($this->languages as $language) {
                    $regionDescription = $this->localize($region['description'], $this->template['locales'][$language], $language);
                    $this->insertRegionDescription($regionDescription, $language, $regionId);
                }

                $sections = $this->getSections($region['sections']);

                $sectionKey = 0;

                foreach ($sections as $section) {

                    if (isset($section['type']) == false) {
                        $section['type'] = ['live'];
                    }

                    if (is_array($section['type']) == false) {
                        $section['type'] = [$section['type']];
                    }

                    foreach ($section['type'] as $type) {
                        $sectionId = $this->insertSection($section, $sectionKey, $type, $regionId);

                        foreach ($this->languages as $language) {
                            $sectionDescription = $this->localize($section['description'], $section['locales'][$language], $language);
                            $this->insertSectionDescription($sectionDescription, $language, $sectionId);
                        }

                        if (isset($section['fields']) && is_array($section['fields'])) {

                            $fieldOrder = 0;

                            foreach ($section['fields'] as $field) {

                                $fieldId = $this->insertObjectField($field, $fieldOrder, $sectionId, "ECSECTION");

                                foreach ($this->languages as $language) {
                                    $fieldDescription = $this->localize(
                                        $field['description'],
                                        $section['locales'][$language],
                                        $language
                                    );

                                    $this->insertObjectFieldDescription(
                                        $fieldDescription, $language, $fieldId
                                    );

                                    $this->insertObjectFieldValue($fieldDescription, $language, $fieldId);
                                }

                                $fieldOrder++;
                            }

                        }

                        if (isset($section['collections']) && is_array($section['collections'])) {

                            $collectionOrder = 0;

                            foreach ($section['collections'] as $collection) {

                                $collectionId = $this->insertEcCollection($collection, $collectionOrder, $sectionId);

                                $collectionFieldOrder = 0;

                                foreach ($collection['fields'] as $collectionField) {

                                    $collectionFieldId = $this->insertObjectField(
                                        $collectionField,
                                        $collectionFieldOrder,
                                        $collectionId,
                                        "ECCOLLECTION"
                                    );

                                    foreach ($this->languages as $language) {
                                        $collectionFieldDescription = $this->localize(
                                            $collectionField['description'],
                                            $section['locales'][$language],
                                            $language
                                        );
                                        $this->insertObjectFieldDescription(
                                            $collectionFieldDescription, $language, $collectionFieldId
                                        );

                                        $this->insertObjectFieldValue(
                                            $collectionFieldDescription,
                                            $language,
                                            $collectionFieldId
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
        $sections = [];

        foreach ($schemas as $schema) {

            if (is_array($schema)) {
                $sections[] = $schema;
            } else {
                $file = $this->baseDir . $this->codeName . '/metainfo/' . $schema . '.json';
                $jsonSectionContent = file_get_contents($file, true);
                $sectionDecoded = json_decode($jsonSectionContent, true);
                if ($sectionDecoded) {
                    $sections[] = $sectionDecoded;
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
    public function deletePages($codeName)
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
    public function deleteObjectField()
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
        $query[] = 'VALUES ("' . $codeName . '", "' . $route . '", "' . $this->templateId . '")';

        $this->db->query(implode(' ', $query));

        return $this->db->getLastId();
    }

    /**
     * Insert a new page description.
     *
     * @param array $pageDescription
     * @param string $language
     * @param int $pageId
     *
     * @return void
     */
    private function insertPageDescription($pageDescription, $language, $pageId)
    {
        $query = [];

        $query[] = 'INSERT INTO `ecpagedesc` SET';
        $fields[] = 'Name="' . $pageDescription['name'] . '"';
        $fields[] = 'Description="' . $pageDescription['description'] . '"';
        $fields[] = 'Lang="' . $language . '"';
        $fields[] = 'PageId="' . $pageId . '"';
        $query[] = implode(', ', $fields);

        $this->db->query(implode(' ', $query));
    }

    /**
     * Insert a new region.
     *
     * @param array $region
     * @param int $pageId
     *
     * @return int
     */
    private function insertRegion($region, $pageId)
    {
        $query = [];

        $query[] = 'INSERT INTO `ecregion` SET';
        $fields[] = 'CodeName="' . $region['code'] . '"';
        $fields[] = 'RowOrder="' . $region["row-order"] . '"';
        $fields[] = 'ColOrder="' . $region["col-order"] . '"';
        $fields[] = 'ColWidth="' . $region["col-width"] . '"';
        $fields[] = 'PageId="' . $pageId . '"';
        $query[] = implode(', ', $fields);

        $this->db->query(implode(' ', $query));

        return $this->db->getLastId();
    }

    /**
     * Insert a new region description.
     *
     * @param array $regionDescription
     * @param string $language
     * @param int $regionId
     *
     * @return void
     */
    private function insertRegionDescription($regionDescription, $language, $regionId)
    {
        $query = [];

        $query[] = 'INSERT INTO `ecregiondesc` SET';
        $fields[] = 'Name="' . $regionDescription['name'] . '"';
        $fields[] = 'Description="' . $regionDescription['description'] . '"';
        $fields[] = 'Lang="' . $language . '"';
        $fields[] = 'RegionId="' . $regionId . '"';
        $query[] = implode(', ', $fields);

        $this->db->query(implode(' ', $query));
    }

    /**
     * Insert a new section.
     *
     * @param array $section
     * @param int $sortOrder
     * @param string $type
     * @param int $regionId
     *
     * @return int
     */
    private function insertSection($section, $sortOrder, $type, $regionId)
    {
        $query = [];

        $query[] = 'INSERT INTO `ecsection` SET';
        $fields[] = 'CodeName="' . $section['code'] . '"';
        $fields[] = 'Name="' . $section['name'] . '"';
        $fields[] = 'Type="' . $type . '"';
        $fields[] = 'State="' . (isset($section['state']) ? $section['state'] : "enabled") . '"';
        $fields[] = 'IsCollection="' . (isset($section['is-collection']) ? $section['is-collection'] : 1) . '"';
        $fields[] = 'SortOrder="' . $sortOrder . '"';
        $fields[] = 'RegionId="' . $regionId . '"';
        $query[] = implode(', ', $fields);

        $this->db->query(implode(' ', $query));

        return $this->db->getLastId();
    }

    /**
     * Insert a new section description.
     *
     * @param array $regionDescription
     * @param string $language
     * @param int $sectionId
     *
     * @return void
     */
    private function insertSectionDescription($regionDescription, $language, $sectionId)
    {
        $query = [];

        $query[] = 'INSERT INTO `ecsectiondesc` SET';
        $fields[] = 'Name="' . $regionDescription['name'] . '"';
        $fields[] = 'Description="' . $regionDescription['description'] . '"';
        $fields[] = 'Image="' . $regionDescription['image'] . '"';
        $fields[] = 'Thumbnail="' . $regionDescription['thumbnail'] . '"';
        $fields[] = 'CollectionName="' . $regionDescription['collection-name'] . '"';
        $fields[] = 'CollectionItemName="' . $regionDescription['item-name'] . '"';
        $fields[] = 'CollectionButtonName="' . $regionDescription['button-name'] . '"';
        $fields[] = 'Lang="' . $language . '"';
        $fields[] = 'SectionId="' . $sectionId . '"';
        $query[] = implode(', ', $fields);

        $this->db->query(implode(' ', $query));
    }

    /**
     * Insert a new object field.
     *
     * @param array $object
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
        $fields[] = 'CodeName="' . $object['code'] . '"';
        $fields[] = 'Type="' . $object['type'] . '"';
        $fields[] = 'SortOrder="' . $sortOrder . '"';
        $fields[] = 'LookUpKey="' . $object['option-id'] . '"';
        $fields[] = 'IsMultiLang="' . $object['multi-lang'] . '"';
        $fields[] = 'ObjectId="' . $parentId . '"';
        $fields[] = 'ObjectType="' . $objectType . '"';
        $query[] = implode(', ', $fields);

        $this->db->query(implode(' ', $query));

        return $this->db->getLastId();
    }

    /**
     * Insert a new object field description.
     *
     * @param array $description
     * @param string $language
     * @param int $parentId
     *
     * @return void
     */
    private function insertObjectFieldDescription($description, $language, $parentId)
    {
        $query = [];

        $query[] = 'INSERT INTO `ecobjectfielddesc` SET';
        $fields[] = 'Name="' . $description['name'] . '"';
        $fields[] = 'Description="' . $description['description'] . '"';
        $fields[] = 'Lang="' . $language . '"';
        $fields[] = 'ObjectFieldId="' . $parentId . '"';
        $query[] = implode(', ', $fields);

        $this->db->query(implode(' ', $query));
    }

    /**
     * Insert a new object field value.
     *
     * @param array $description
     * @param string $language
     * @param int $parentId
     *
     * @return void
     */
    private function insertObjectFieldValue($description, $language, $parentId)
    {
        $query = [];

        $query[] = 'INSERT INTO `ecobjectfieldval` SET';
        $fields[] = 'Value="' . $description['value'] . '"';
        $fields[] = 'Lang="' . $language . '"';
        $fields[] = 'ObjectFieldId="' . $parentId . '"';
        $query[] = implode(', ', $fields);

        $this->db->query(implode(' ', $query));
    }

    /**
     * Insert a new collection.
     *
     * @param array $collection
     * @param int $sortOrder
     * @param int $sectionId
     *
     * @return int
     */
    private function insertEcCollection($collection, $sortOrder, $sectionId)
    {
        $query = [];

        $query[] = 'INSERT INTO `eccollection` SET';
        $fields[] = 'Name="' . $collection['name'] . '"';
        $fields[] = 'Thumbnail="' . $collection['thumbnail'] . '"';
        $fields[] = 'IsDefault="' . $collection['is-default'] . '"';
        $fields[] = 'SortOrder="' . $sortOrder . '"';
        $fields[] = 'SectionId="' . $sectionId . '"';
        $query[] = implode(', ', $fields);

        $this->db->query(implode(' ', $query));

        return $this->db->getLastId();
    }
}
