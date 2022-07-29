<?php

class ModelTemplatesFixer extends ModelTemplatesTemplate
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
     * @var \Db
     */
    protected $db;

    /**
     * @var array
     */
    protected $template;

    /**
     * the base directory path.
     *
     * @var string
     */
    public $baseDir = DIR_CUSTOM_TEMPLATE;

    /**
     * @var array
     */
    protected $languages = ['en', 'ar'];

    /**
     * @var array
     */
    protected $flipLanguages;

    /**
     * @var array
     */
    protected $locales = [];

    /**
     * @var array
     */
    protected $metainfo = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $sectionsIdsContainer = [];

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

        $this->flipLanguages = array_flip($this->languages);
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
     * Set locales array.
     *
     * @param array $locales
     *
     * @return void
     */
    public function setLocals($locales)
    {
        $this->locales = array_merge_recursive($locales, $this->defaultLocales ?: []);

        unset($this->template['locales']);
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

    /**
     * Parse locale variables.
     *
     * @param array $elem
     *
     * @return array
     */
    public function localize($elem, $fallback = null)
    {
        $localized = [];
        foreach ($elem as $key => $value) {
            foreach ($this->languages as $language) {
                if (strpos($value, '@locales.') === 0) {
                    $pureString = str_replace('@locales.', '', $value);

                    if ($fallback) {
                        $localesSource = $fallback;
                    } else {
                        $localesSource = $this->locales;
                    }

                    if (isset($fallback[$language][$pureString])) {
                        $localized[$language][$key] = $fallback[$language][$pureString];
                    } else {
                        $localized[$language][$key] = (
                            isset($localesSource[$language][$pureString]) ?
                                $localesSource[$language][$pureString] :
                                $localesSource['en'][$pureString]
                        );
                    }
                    
                } else {
                    $localized[$language][$key] = $value;
                }
            }
        }

        return $localized;
    }

    /**
     * Get sections schemas and compile them into the main schema.
     *
     * @param array $schemas
     *
     * @return array
     */
    private function compileSections($schemas)
    {
        $sections = [];

        foreach ($schemas as $schema) {

            if (is_array($schema)) {
                $sections[] = $schema;
            } else {
                $file = rtrim($this->fixer->baseDir, '/') . '/' . $this->codeName . '/metainfo/' . $schema . '.json';
                $jsonSectionContent = file_get_contents($file, true);
                $sectionDecoded = json_decode($jsonSectionContent, true);
                if ($sectionDecoded) {
                    $sections[] = $sectionDecoded;

                    $this->locales = array_merge_recursive($this->locales, $sectionDecoded['locales'] ?: []);
                }
            }
        }

        $this->flattenLocales();

        return $sections;
    }

    /**
     * Mysqlize string.
     *
     * @param string $string
     *
     * @return string
     */
    private function normalize($string)
    {
        return str_replace(['-'], '', $string);
    }

    /**
     * Compile main schema with sections schemas to one array and inject locales to the main array.
     *
     * @return void
     */
    public function compileSchema()
    {
        $this->template['description'] = $this->localize($this->template['description']);

        foreach ($this->template['pages'] as $pageKey => &$page) {

            $this->metainfo['pages_codes'][$page['code']] = $page['code'];

            $page['description'] = $this->localize($page['description']);

            foreach ($page['regions'] as $regionKey => &$region) {
                $region['description'] = $this->localize($region['description']);

                $region['sections'] = $this->compileSections($region['sections']);

                foreach ($region['sections'] as $sectionKey => &$section) {

                    if (isset($section['type']) == false) {
                        $section['type'] = ['live'];
                    }

                    if (is_array($section['type']) == false) {
                        $section['type'] = [$section['type']];
                    }

                    $fallback = false;
                    if (isset($section['locales']) && is_array($section['locales'])) {
                        $fallback = $section['locales'];
                    }

                    $section['description'] = $this->localize($section['description'], $fallback);

                    if (isset($section['fields']) && is_array($section['fields'])) {
                        foreach ($section['fields'] as &$field) {
                            $field['description'] = $this->localize($field['description']);
                        }
                    }

                    if (isset($section['collections']) && is_array($section['collections'])) {
                        foreach ($section['collections'] as &$collection) {
                            
                            if (isset($collection['fields']) && is_array($collection['fields'])) {
                                foreach ($collection['fields'] as &$field) {
                                    $field['description'] = $this->localize($field['description']);
                                }
                            }

                        }
                    }
                }
            }
        }

        unset($this->template['settings']);
    }

    /**
     * Get pages using parent template id.
     *
     * @param int $templateId
     *
     * @return array
     */
    private function getPagesByTemplateId(int $templateId)
    {
        $query = 'SELECT * FROM ecpage WHERE TemplateId = %d';

        $data = $this->db->query(sprintf($query, $templateId));

        return $data->rows;
    }

    /**
     * Get regions by set of pages ids.
     *
     * @param array $pagesIds
     *
     * @return array
     */
    private function getRegionsByPagesIds(array $pagesIds)
    {
        $query = 'SELECT *, concat(PageId, "_", CodeName) as uid FROM ecregion WHERE PageId IN (%s)';
        $query = sprintf($query, implode(',', $pagesIds));

        return $this->db->query($query)->rows;
    }

    /**
     * Get sections by set of regions ids.
     *
     * @param array $regionsIds
     *
     * @return array
     */
    private function getSectionsByRegionsIds(array $regionsIds)
    {
        $query = 'SELECT *, concat(RegionId, "_", CodeName, "_", Type) as uid FROM ecsection WHERE RegionId IN (%s)';
        $query = sprintf($query, implode(',', $regionsIds));

        return $this->db->query($query)->rows;
    }

    /**
     * Get only available sections by set of regions ids.
     *
     * @param array $regionsIds
     *
     * @return array
     */
    private function getAvailableSectionsByRegionsIds(array $regionsIds)
    {
        $query = 'SELECT *, concat(RegionId, "_", CodeName) as uid FROM ecsection WHERE RegionId IN (%s) AND Type="%s"';
        $query = sprintf($query, implode(',', $regionsIds), "available");

        return $this->db->query($query)->rows;
    }

    /**
     * Get only live sections by set of regions ids.
     *
     * @param array $regionsIds
     *
     * @return array
     */
    private function getLiveSectionsByRegionsIds(array $regionsIds)
    {
        $query = 'SELECT *, concat(RegionId, "_", CodeName) as uid FROM ecsection
            WHERE RegionId IN (%s) AND (Type="%s" OR Type="%s")';
        $query = sprintf($query, implode(',', $regionsIds), "live", "draft");

        return $this->db->query($query)->rows;
    }

    /**
     * rearrange sections by the region id and section id in a new multidimensional array.
     *
     * @param array $sections
     *
     * @return array
     */
    private function reArrangeLiveSections(array $sections)
    {
        $re = [];
        foreach ($sections as $section) {
            $re[$section['RegionId']][$section['id']] = $section;
            $this->sectionsIdsContainer[$section['CodeName']][$section['id']] = $section['id'];
        }

        return $re;
    }

    /**
     * Fix multi dimensoinal array locale element
     *
     * @return void
     */
    public function flattenLocales()
    {
        foreach ($this->locales as &$lang) {
            foreach ($lang as &$locale) {
                if (is_array($locale)) {
                    $locale = $locale[0];
                }
            }
        }
    }

    /**
     * Set options object
     *
     * @param array $options
     *
     * @return self
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Insert a new option/lookup record.
     *
     * @param array $option
     *
     * @return void
     */
    public function newOption($option)
    {
        $query = 'INSERT INTO %s SET LookUpKey="%s", Name="%s", Value="%s", Lang="%s", SortOrder="%s";';
        $this->db->query(vsprintf($query, [
            'eclookup',
            $option['key'],
            $option['name'],
            $option['value'],
            $option['language'],
            $option['sort'],
        ]));
    }

    /**
     * Factory method to remove lookup keys and re insert it.
     *
     * @param array $options
     *
     * @return void
     */
    public function syncOptions($options)
    {
        $keys = array_column($options, 'key');

        $this->deleteOptionsByKeys($keys);

        foreach ($options as $option) {
            $this->newOption($option);
        }
    }

    /**
     * Delete multiple options by array of keys.
     *
     * @param array $keys
     *
     * @return void
     */
    public function deleteOptionsByKeys($keys)
    {
        if (count($keys) < 1) {
            return false;
        }

        $query = 'DELETE FROM %s WHERE LookUpKey IN ("%s")';

        $this->db->query(sprintf($query, 'eclookup', implode('","', array_unique($keys))));
    }

    public function insertPagesDescriptions($pageId, $description)
    {
        $clear = $this->db->query(sprintf('DELETE FROM ecpagedesc WHERE PageId = "%d"', $pageId));

        $query = $columns = [];
        $query[] = 'INSERT INTO ecpagedesc (`Name`, `Description`, `Lang`, `PageId`) VALUES';

        foreach ($description as $lang => $cols) {

            $cols[] = $lang;
            $cols[] = $pageId;

            $columns[] = sprintf('("%s")', implode('","', $cols));
        }

        $query[] = implode(', ', $columns);

        $query = vsprintf(implode(' ', $query), []);

        $this->db->query($query);
    }

    public function fix()
    {
        $this->refreshTemplateDescription($this->templateId, $this->template['description']);

        $installedPages = $this->getPagesByTemplateId($this->templateId);
        
        $installedPagesIdWithCode = [];
        foreach ($installedPages as $installedPage) {
            $installedPagesIdWithCode[$installedPage['CodeName']][] = $installedPage;
        }
        $installedPagesId = array_column($installedPages, 'id');
        $installedRegions = $this->getRegionsByPagesIds($installedPagesId);
        $installedRegionsIds = array_column($installedRegions, 'id', 'uid');
        // dd($installedRegionsIds);

        $installedAvailableSections = $this->getAvailableSectionsByRegionsIds($installedRegionsIds);
        $installedAvailableSectionsIds = array_column($installedAvailableSections, 'id', 'uid');

        $installedLiveSections = $this->getLiveSectionsByRegionsIds($installedRegionsIds);
        // dd(count($installedLiveSections));
        $arrangedLiveSections = $this->reArrangeLiveSections($installedLiveSections);

        $this->purgeAvailableSections($installedRegionsIds);
        $this->purgeAvailableObjectFields($installedAvailableSectionsIds);

        foreach ($this->template['pages'] as $key => &$page) {
            if (isset($installedPagesIdWithCode[$page['code']])) {

                foreach ($installedPagesIdWithCode[$page['code']] as $installedPage) {
                    $installedPageId = $installedPage['id'];

                    $this->insertPagesDescriptions($installedPageId, $page['description']);

                    $regions = $page['regions'];

                    foreach ($regions as &$region) {

                        $regionUid = $installedPageId . '_' . $region['code'];

                        if (isset($installedRegionsIds[$regionUid])) {

                            $installedRegionsId = $installedRegionsIds[$regionUid];

                            $this->updateRegion($installedPageId, $installedRegionsId, $region, $key);
                        }

                    }
                }
            }
        }

        foreach ($this->metainfo['available_sections'] as $regionId => &$avialableSections) {
            $this->insertAvailableSections($regionId, $avialableSections);
        }

        foreach ($installedLiveSections as $installedLiveSection) {
            $this->updateLiveSections($regionId, $installedLiveSection, []);
        }
    }

    /**
     * Updates live sections.
     *
     * @param int $regionId
     * @param array $sections
     * @param array $installedCodes
     *
     * @return void
     */
    private function updateLiveSections($regionId, $section, $installedCodes)
    {
        $sortOrder = 0;

        if (isset($this->metainfo['sections'][$section['CodeName']])) {
            $section['jsonsection'] = $this->metainfo['sections'][$section['CodeName']];
            $sectionIds = $section['id'];
            $this->updateSectionDesc($section, $sectionIds, $section['CodeName'], $sortOrder);
        }
    }

    /**
     * Factory method to update live section data.
     * this method :- update the section , then build the update section description query ,
     * invoke child methods to update section collections & fields
     *
     * @param array $section
     * @param int $sectionId
     * @param int $sortOrder
     *
     * @return void
     */
    private function updateSectionDesc($section, $sectionId, $codeName, $sortOrder)
    {
        $reset = $this->db->query(sprintf('DELETE FROM ecsectiondesc WHERE SectionId = "%d"', $sectionId));

        $query = $columns = [];
        $query[] = 'INSERT INTO ecsectiondesc';
        $query[] = '(`Name`, `Description`, `CollectionName`, `CollectionItemName`, `CollectionButtonName`, `Image`, `Thumbnail`, `Lang`, `SectionId`)';
        $query[] = 'VALUES';
        foreach ($section['jsonsection']['description'] as $lang => $values) {
            if (isset($values['image']) == false) {
                $values['image'] = '';
            }

            if (isset($values['thumbnail']) == false) {
                $values['thumbnail'] = '';
            }

            $values['lang'] = $lang;
            $values['sectionid'] = $sectionId;
            $columns[] = sprintf('("%s")', implode('","', $values));
        }

        $query[] = implode(', ', $columns);

        $this->db->query(implode(' ', $query));

        $jsonCollectionFields = $jsonSectionFields = [];

        if (isset($section['jsonsection']['collections']) && count($section['jsonsection']['collections']) > 0) {
            foreach ($section['jsonsection']['collections'] as $collection) {
                foreach ($collection['fields'] as $field) $jsonCollectionFields[$field['code']] = $field['description'];
            }

            $sectionCollections = $this->getSectionCollectionFeildsBySectionId($sectionId);
            foreach ($sectionCollections as $sectionCollection) {
                if (isset($jsonCollectionFields[$sectionCollection['CodeName']])) {
                    $this->db->query(sprintf(
                        'DELETE FROM ecobjectfielddesc WHERE ObjectFieldId = "%d"',
                        $sectionCollection['objid']
                    ));
                    $this->insertFieldDescription($sectionCollection['objid'], $jsonCollectionFields[$sectionCollection['CodeName']]);
                }
            }
        }

        if (isset($section['jsonsection']['fields']) && count($section['jsonsection']['fields']) > 0) {
            foreach ($section['jsonsection']['fields'] as $field) {
                $jsonSectionFields[$field['code']] = $field['description'];
            }

            $sectionFields = $this->getFieldsBySectionId($sectionId);

            foreach ($sectionFields as $sectionField) {
                if (isset($jsonSectionFields[$sectionField['CodeName']])) {
                    $this->db->query(sprintf(
                        'DELETE FROM ecobjectfielddesc WHERE ObjectFieldId = "%d"',
                        $sectionField['id']
                    ));

                    $this->insertFieldDescription($sectionField['id'], $jsonSectionFields[$sectionField['CodeName']]);
                }
            }
        }
    }

    public function getSectionCollectionFeildsBySectionId($sectionId)
    {
        $columns = ['*','ecof.id as objid'];
        $query = [];
        $query[] = 'SELECT %s FROM ecobjectfield ecof INNER JOIN eccollection ecc ON ecof.ObjectId = ecc.id';
        $query[] = 'WHERE ObjectType = "%s" AND ObjectId in (%s)';
        // $query[] = 'AND ecc.IsDefault = 0';
        $query = sprintf(
            implode(' ', $query),
            implode(',', $columns),
            'ECCOLLECTION',
            sprintf('SELECT id FROM `eccollection` where sectionid = %d', $sectionId)
        );

        $data = $this->db->query($query);

        if ($data->num_rows > 0) {
            return $data->rows;
        }

        return false;
    }

    /**
     * Select fields by section id.
     *
     * @param int $sectionId
     *
     * @return array|bool
     */
    private function getFieldsBySectionId($sectionId)
    {
        $query = 'SELECT * FROM ecobjectfield WHERE ObjectId = "%d" AND ObjectType = "%s"';
        $query = sprintf($query, $sectionId, 'ECSECTION');

        $data = $this->db->query($query);

        if ($data->num_rows > 0) {
            return $data->rows;
        }

        return false;
    }

    /**
     * Select section non default collection by section id.
     *
     * @param int $sectionId
     *
     * @return array|bool
     */
    private function getNonDefaultCollectionBySectionId(int $sectionId)
    {
        $query = 'SELECT * FROM eccollection WHERE SectionId = %d AND IsDefault = 0';
        $query = sprintf($query, $sectionId);

        $data = $this->db->query($query);

        if ($data->num_rows > 0) {
            return $data->rows;
        }

        return false;
    }

    /**
     * Delete all section default collection.
     *
     * @param int $sectionId
     *
     * @return array|bool
     */
    private function deleteDefaultCollection(array $sectionIds)
    {
        $query = 'DELETE FROM eccollection WHERE SectionId IN (%s) AND IsDefault = 1';
        $query = sprintf($query, implode(',', $sectionIds));

        $this->db->query($query);
    }

    /**
     * Insert a new collection.
     *
     * @param int $sectionId
     * @param array $collection
     * @param int $sortOrder
     *
     * @return int
     */
    private function insertCollection(int $sectionId, array $collection, int $sortOrder) {
        $query = $fields = [];

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

    /**
     * Update non default collection for a specific sectoin.
     *
     * @param int $sectionId
     * @param array $collection
     *
     * @return void
     */
    private function updateNonDefaultCollection(array $sectionIds, array $collection)
    {
        $query = $fields = [];

        $query[] = 'UPDATE `eccollection` SET';
        $fields[] = 'Name="' . $collection['name'] . '"';
        $fields[] = 'Thumbnail="' . $collection['thumbnail'] . '"';
        $query[] = implode(', ', $fields);
        $query[] = 'WHERE IsDefault="0"';
        $query[] = 'AND SectionId IN ("' . implode(',', $sectionIds) . '")';

        $this->db->query(implode(' ', $query));
    }

    /**
     * Insert a new field.
     *
     * @param int $parentId
     * @param array $object
     * @param string $objectType
     * @param int $sortOrder
     *
     * @return int
     */
    private function insertField($parentId, $object, $objectType, $sortOrder)
    {
        $query = $fields = [];

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
     * Factory method to update a specified section collections ,
     * this method delete all default collections
     * re-insert the default collection
     * update all non default collections
     * insert fields for the default collection
     * update or insert the fields for the non default collections
     *
     * @param int $sectionId
     * @param array $collection
     * @param int $sortOrder
     * @param array $dbCollections
     * @param array $dbCollectionFields
     *
     * @return void
     */
    private function updateSectionCollections(array $sectionIds, array $collection, int $sortOrder, array $dbCollections, array $dbCollectionFields)
    {
        $this->deleteDefaultCollection($sectionIds);
        $this->updateNonDefaultCollection($sectionIds, $collection);

        foreach ($sectionIds as $sectionId) {
            $collectionId = $this->insertCollection($sectionId, $collection, 0);

            foreach ($collection['fields'] as $field) {
                $fieldId = $this->insertField($collectionId, $field, 'ECCOLLECTION', 0);
                $this->insertFieldDescription($fieldId, $field['description']);
                $this->insertFieldValue($fieldId, $field['description']);
            }

            foreach ($dbCollections as $collectionId => $dbCollection) {
                $collectionFieldOrder = 0;
                foreach ($collection['fields'] as $field) {
                    if (isset($dbCollectionFields[$field['code']])) {
                        $this->updateLiveObjectField($collectionId, $field, 'ECCOLLECTION', $collectionFieldOrder);
                        $this->updateLiveObjectFieldDescription(
                            $collectionId,
                            $field['code'],
                            $field['description'],
                            'ECCOLLECTION'
                        );
                    } else {
                        $fieldId = $this->insertField($collectionId, $field, 'ECCOLLECTION', $collectionFieldOrder);
                        $this->insertFieldDescription($fieldId, $field['description']);
                        $this->insertFieldValue($fieldId, $field['description']);
                    }
                    $collectionFieldOrder++;
                }
            }
        }
    }

    /**
     * Update live object field description
     *
     * @param int $parentId
     * @param string $fieldCode
     * @param array $descriptions
     * @param string $objectType
     *
     * @return void
     */
    private function updateLiveObjectFieldDescription(int $parentId, string $fieldCode, array $descriptions, string $objectType)
    {
        unset($descriptions['value']);

        $query = $columns = [];
        $query[] = 'UPDATE `ecobjectfielddesc` ecod INNER JOIN ecobjectfield eco';
        $query[] = 'ON eco.id=ecod.ObjectFieldId AND ObjectType="%s" AND eco.CodeName="%s"';
        $query[] = 'SET';

        foreach ($descriptions as $field => $description) {
            $cases = [];
            $field = $this->normalize($field);
            $cases[] = sprintf('%s = (CASE', $field);
            foreach ($description as $lang => $value) {
                $cases[] = sprintf('WHEN Lang="%s" THEN "%s"', $lang, $value);
            }
            $cases[] = 'END)';
            $columns[] = implode(' ', $cases);
        }

        $query[] = implode(', ', $columns);
        $query[] = sprintf('WHERE eco.ObjectId=%d', $parentId);

        $this->db->query(sprintf(implode(' ', $query), $objectType, $fieldCode));
    }

    /**
     * Update live object field
     *
     * @param int $parentId
     * @param array $field
     * @param string $objectType
     * @param int $collectionFieldOrder
     *
     * @return void
     */
    private function updateLiveObjectField(int $parentId, array $field, string $objectType, int $collectionFieldOrder)
    {
        $query = [];
        $query[] = 'UPDATE ecobjectfield SET Type="%s", SortOrder="%s", LookUpKey="%s", IsMultiLang="%s"';
        $query[] = 'WHERE ObjectId = %d AND CodeName="%s" AND ObjectType="%s"';

        $query = vsprintf(implode(' ', $query), [
            $field['type'],
            $collectionFieldOrder,
            $field['option-id'],
            $field['multi-lang'],
            $parentId,
            $field['code'],
            $objectType,
        ]);

        $this->db->query($query);
    }

    /**
     * Select non default collection fields for a given section by section id
     *
     * @param int $sectionId
     *
     * @return array|bool
     */
    private function getNonDefaultCollectionFieldsBySectionId(array $sectionIds)
    {
        $columns = ['*','ecof.id as objid'];
        $query = [];
        $query[] = 'SELECT %s FROM ecobjectfield ecof INNER JOIN eccollection ecc ON ecof.ObjectId = ecc.id';
        $query[] = 'WHERE ObjectType = "%s" AND ObjectId in (%s)';
        $query[] = 'AND ecc.IsDefault = 0';
        $query = sprintf(
            implode(' ', $query),
            implode(',', $columns),
            'ECCOLLECTION',
            sprintf('SELECT id FROM `eccollection` where sectionid IN (%s)', implode(',', $sectionIds))
        );

        $data = $this->db->query($query);

        if ($data->num_rows > 0) {
            return $data->rows;
        }

        return false;
    }

    /**
     * Select a section by the parent region id and the section code
     *
     * @param int $regionId
     * @param string $code
     *
     * @return array|bool
     */
    private function getSectionByCodeAndRegion(int $regionId, string $code)
    {
        $query = 'SELECT * FROM ecsection WHERE RegionId = %d AND CodeName="%s"
            AND (Type="live" OR Type="draft")';
        $query = sprintf($query, $regionId, $code);
        $data = $this->db->query($query);

        if ($data->num_rows > 0) {
            return $data->row;
        }

        return false;
    }

    /**
     * Factory method to insert new sections
     * this will insert new section
     * insert new section description
     * insert new collection /fields for that section
     *
     * @param int $regionId
     * @param array $sections
     *
     * @return void
     */
    private function insertAvailableSections(int $regionId, array $sections)
    {
        $sortOrder = 0;

        $sections = array_column($sections, null, 'code');

        foreach ($sections as $section) {
            $query = $values = [];

            $query[] = 'INSERT INTO ecsection (%s) VALUES';

            $isCollection = 0;
            if (isset($section['is-collection']) && $section['is-collection'] == 1) {
                $isCollection = 1;
            }

            $value = [];
            $value[] = $section['code'];
            $value[] = $section['name'];
            $value[] = 'available';
            $value[] = $section['state'];
            $value[] = $isCollection;
            $value[] = $sortOrder;
            $value[] = $regionId;
            $values[] = vsprintf('("%s", "%s", "%s", "%s", "%s", %d, %d)', $value);

            $sortOrder++;
            $query[] = implode(', ', $values);

            $query = sprintf(
                implode(' ', $query),
                '`CodeName`, `Name`, `Type`, `State`, `IsCollection`, `SortOrder`, `RegionId`'
            );
            $this->db->query($query);

            $sectionId = $this->db->getLastId();

            $this->insertAvailableSectionDescription($sectionId, $section['description']);

            if (isset($section['collections']) && count($section['collections']) > 0) {
                $this->insertCollections($sectionId, $section['collections']);
            }

            if (isset($section['fields']) && count($section['fields']) > 0) {
                $this->insertFields($sectionId, $section['fields'], 'ECSECTION');
            }
        }
    }

    /**
     * Insert new section description
     *
     * @param int $sectionId
     * @param array $descriptions
     *
     * @return void
     */
    private function insertAvailableSectionDescription(int $sectionId, array $descriptions)
    {
        $query = $columns = [];
        $query[] = 'INSERT INTO ecsectiondesc (`Name`, `Description`, `Image`, `Thumbnail`, `CollectionName`, `CollectionItemName`, `CollectionButtonName`, `Lang`, `SectionId`)';
        $query[] = 'VALUES';

        $columns[] = vsprintf('("%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", %d)', [
            $descriptions['en']['name'],
            $descriptions['en']['description'],
            $descriptions['en']['image'],
            $descriptions['en']['thumbnail'],
            $descriptions['en']['collection-name'],
            $descriptions['en']['item-name'],
            $descriptions['en']['button-name'],
            'en',
            $sectionId
        ]);
        $columns[] = vsprintf('("%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", %d)', [
            $descriptions['ar']['name'],
            $descriptions['ar']['description'],
            $descriptions['ar']['image'],
            $descriptions['ar']['thumbnail'],
            $descriptions['ar']['collection-name'],
            $descriptions['ar']['item-name'],
            $descriptions['ar']['button-name'],
            'ar',
            $sectionId
        ]);
        $query[] = implode(',', $columns);

        $this->db->query(implode(' ', $query));
    }

    /**
     * Insert new section collections
     *
     * @param int $sectionId
     * @param array $collections
     *
     * @return void
     */
    private function insertCollections(int $sectionId, array $collections)
    {
        $sortOrder = 0;
        foreach ($collections as $collection) {
            $id = $this->insertCollection($sectionId, $collection, $sortOrder);

            $this->insertFields($id, $collection['fields'], 'ECCOLLECTION');
            $sortOrder++;
        }
    }

    /**
     * Insert new section fields
     *
     * @param int $parentId
     * @param array $objects
     * @param string $objectType
     *
     * @return void
     */
    private function insertFields(int $parentId, array $objects, string $objectType)
    {
        $sortOrder = 0;
        foreach ($objects as $object) {
            $query = $fields = [];

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

            $id = $this->db->getLastId();

            $this->insertFieldDescription($id, $object['description']);
            $this->insertFieldValue($id, $object['description']);

            $sortOrder++;
        }
    }

    /**
     * Insert new field description
     *
     * @param int $objectFieldId
     * @param array $object
     *
     * @return void
     */
    private function insertFieldDescription(int $objectFieldId, array $object)
    {
        $query = $columns = [];
        $query[] = 'INSERT INTO ecobjectfielddesc (`Name`, `Description`, `Lang`, `ObjectFieldId`)';
        $query[] = 'VALUES';
        $columns[] = vsprintf(
            '("%s", "%s", "%s", %d)', [$object['en']['name'], $object['en']['description'], 'en', $objectFieldId]
        );
        $columns[] = vsprintf(
            '("%s", "%s", "%s", %d)', [$object['ar']['name'], $object['ar']['description'], 'ar', $objectFieldId]
        );

        $query[] = implode(', ', $columns);

        $this->db->query(implode(' ', $query));
        // dd(implode(' ', $query), 1);
    }

    /**
     * Insert new field value
     *
     * @param int $objectFieldId
     * @param array $object
     *
     * @return void
     */
    private function insertFieldValue(int $objectFieldId, array $object)
    {
        $query = $columns = [];
        $query[] = 'INSERT INTO ecobjectfieldval (`Value`, `Lang`, `ObjectFieldId`)';
        $query[] = 'VALUES';
        $columns[] = vsprintf(
            '("%s", "%s", %d)', [$object['en']['value'], 'en', $objectFieldId]
        );
        $columns[] = vsprintf(
            '("%s", "%s", %d)', [$object['ar']['value'], 'ar', $objectFieldId]
        );

        $query[] = implode(', ', $columns);

        $this->db->query(implode(' ', $query));
    }

    /**
     * Update a given region
     *
     * @param int $pageId
     * @param int $regionId
     * @param array $region
     * @param int $key
     *
     * @return void
     */
    private function updateRegion(int $pageId, int $regionId, array $region, int $key)
    {
        $description = $region['description'];

        $delete = $this->db->query(sprintf('DELETE FROM ecregiondesc WHERE RegionId = "%d"', $regionId));

        $query = $columns = [];
        $query[] = 'INSERT INTO ecregiondesc (`Name`, `Description`, `Lang`, `RegionId`) VALUES';

        foreach ($region['description'] as $lang => $cols) {

            $cols[] = $lang;
            $cols[] = $regionId;

            $columns[] = sprintf('("%s")', implode('","', $cols));
        }

        $query[] = implode(', ', $columns);

        $query = vsprintf(implode(' ', $query), []);

        $this->db->query($query);

        foreach ($region['sections'] as $section) {
            if (in_array('available', $section['type'])) {
                $this->metainfo['available_sections'][$regionId][] = $section;
            }

            $this->metainfo['sections'][$section['code']] = $section;
        }
    }

    /**
     * Update template description
     *
     * @param int $templateId
     * @param array $descriptions
     *
     * @return void
     */
    private function refreshTemplateDescription(int $templateId, array $descriptions)
    {
        $this->db->query(sprintf('DELETE FROM ectemplatedesc WHERE TemplateId = "%d"', $templateId));
        $query = $columns = [];
        $query[] = 'INSERT INTO `ectemplatedesc` (`Name`, `Description`, `Image`, `Demourl`, `Lang`, `TemplateId`) VALUES';
        foreach ($descriptions as $lang => $description) {
            $description[] = $lang;
            $description[] = $templateId;
            $columns[] = sprintf('("%s")', implode('","', $description));
        }
        $query[] = implode(', ', $columns);

        $this->db->query(implode(' ', $query));
    }

    /**
     * Remove all available sections for a given regions
     *
     * @param int $regionsIds
     *
     * @return void
     */
    private function purgeAvailableSections($regionsIds)
    {
        if (count($regionsIds) > 0) {
            $this->db->query(sprintf(
                'DELETE FROM ecsection WHERE Type="%s" AND RegionId IN (%s)',
                'available',
                implode(',', $regionsIds)
            ));
        }
    }

    /**
     * Delete all object fields for a given set of sections by array of sections ids
     *
     * @param array $avialableSections
     *
     * @return void
     */
    private function purgeAvailableObjectFields(array $avialableSections)
    {
        $query = [];
        $query[] = 'SELECT * FROM ecobjectfield WHERE (ObjectType="%s" and ObjectId IN (%s))';
        $query[] = 'OR';
        $query[] = '(ObjectType="%s" and ObjectId IN (SELECT id from eccollection WHERE SectionId in (%s)))';
        if (count($avialableSections) > 0) {
            $avialableSections = implode(',', $avialableSections);
            $query = vsprintf(implode(' ', $query), [
                'ECSECTION',
                $avialableSections,
                'ECCOLLECTION',
                $avialableSections
            ]);

            $this->db->query($query);
        }
    }
}
