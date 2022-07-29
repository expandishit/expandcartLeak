<?php

class ModelTemplatesTemplate extends Model
{
    /**
     * the templates name, related to database.
     *
     * @var string
     */
    protected $templatesTable = '`' . DB_PREFIX . 'ectemplate`';

    /**
     * @var
     */
    protected $pagesTable = '`' . DB_PREFIX . 'ecpage`';

    /**
     * the templates name, related to database.
     *
     * @var string
     */
    protected $ecLookupTable = '`' . DB_PREFIX . 'eclookup`';

    /**
     * the templates description, related to database.
     *
     * @var string
     */
    protected $templatesDescriptionTable = '`' . DB_PREFIX . 'ectemplatedesc`';

    /**
     * the base directory path.
     *
     * @var string
     */
    // public $baseDir = DIR_CATALOG . 'view/custom/' . STORECODE . '/';
    public $baseDir = DIR_CUSTOM_TEMPLATE;

    /**
     * the temporary directory path.
     *
     * @var string
     */
    // public $tmpDir = DIR_CATALOG . 'view/custom/tmp/';
    public $tmpDir = TEMP_DIR_PATH;

    /**
     * Errors array.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Main template name.
     *
     * @var string
     */
    protected $templateName;

    /**
     * Schema file path.
     *
     * @var string
     */
    protected $templateJsonPath;

    /**
     * The supported backend languages.
     * this may need to be replaced by a function to fetch all supported dashboard languages,
     * but untill this date Oct-2018 there is only [en, en] languages.
     *
     * @var array
     */
    protected $dashboardLanguages = [
        'en', 'ar'
    ];

    /**
     * Maximum files count per each template.
     *
     * @var int
     */
    protected $maximumTemplateFiles = 2500;

    /**
     * Maximum archived file size in bytes ( default : 50 MB ).
     *
     * @var int
     */
    protected $maximumTemplatesSize = 52428800;

    /**
     * Populate a new error in the errors array.
     *
     * @param mixed @error
     *
     * @return ModelTemplatesTemplate
     */
    public function setError($error)
    {
        $this->errors[] = $error;

        return $this;
    }

    /**
     * Get all the errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Set the main template name
     *
     * @param string @error
     *
     * @return ModelTemplatesTemplate
     */
    public function setTemplateName($template)
    {
        $this->templateName = $template;

        return $this;
    }

    /**
     * Get the main template name.
     *
     * @return string
     */
    public function getTemplateName()
    {
        // TODO should be out of this getter scope
        if (!$this->templateName || $this->validateTemplateName($this->templateName) == false) {
            throw new \Exception("Invalid template name");
        }

        return $this->templateName;
    }

    /**
     * Set the schema file contents.
     *
     * @param string $schema
     *
     * @return ModelTemplatesTemplate
     */
    public function setTemplateJson($templateJson)
    {
        $this->templateJson = $templateJson;

        return $this;
    }

    /**
     * Get the template schema file content.
     *
     * @return string
     */
    public function getTemplateJson()
    {
        return $this->templateJson;
    }

    /**
     * Get the maximum template size.
     *
     * @return string
     */
    public function getMaximumTemplatesSize()
    {
        return $this->maximumTemplatesSize;
    }

    /**
     * Set the schema file path.
     *
     * @param string $schema
     *
     * @return ModelTemplatesTemplate
     */
    public function setTemplateJsonPath($templateJsonPath)
    {
        $this->templateJsonPath = $templateJsonPath;

        return $this;
    }

    /**
     * Get the template schema file path.
     *
     * @return string
     */
    public function getTemplateJsonPath()
    {
        return $this->templateJsonPath;
    }

    /**
     * Validate the template name to check if it is valid name.
     *
     * @param string $templateName
     *
     * @return bool
     */
    public function validateTemplateName($templateName)
    {
        return \preg_match('#^[a-z][a-z0-9\_]+$#i', $templateName);
    }

    /**
     * Checks if the template name is alreaddy installed in the user instance.
     *
     * @param string $templateName
     *
     * @return bool
     */
    public function checkTemplate($templateName)
    {
        $query = [];

        $query[] = 'SELECT id FROM ' . $this->templatesTable;
        $query[] = 'WHERE CodeName="' . $templateName . '"';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows == 0) {
            return true;
        }

        throw new \Exception("The template name is already exists, kindley choose another name");
    }

    /**
     * Get the customized template using it's id.
     *
     * @param int $templateId
     *
     * @return array|bool
     */
    public function getTemplateById($templateId)
    {
        $query = [];

        $query[] = 'SELECT * FROM ' . $this->templatesTable;
        $query[] = 'WHERE id="' . $templateId . '" AND custom_template=1';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

    /**
     * Get the customized template using it's config name.
     *
     * @param string $configName
     *
     * @return array|bool
     */
    public function getCustomTemplateByConfigName($configName)
    {
        $query = [];

        $query[] = 'SELECT * FROM ' . $this->templatesTable;
        $query[] = 'WHERE CodeName="' . $configName . '" AND custom_template=1';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

    /**
     * Get template using it's config name.
     *
     * @param string $configName
     *
     * @return array|bool
     */
    public function getTemplateByConfigName($configName)
    {
        $query = [];

        $query[] = 'SELECT * FROM ' . $this->templatesTable;
        $query[] = 'WHERE CodeName="' . $configName . '"';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

    /**
     * Get template using external_tempalte_id.
     *
     * @param int $id
     *
     * @return array|bool
     */
    public function getTemplateByExternalTemplateId($id)
    {
        $query = 'SELECT * FROM %s WHERE external_template_id=%d';

        $data = $this->db->query(vsprintf($query, [
            $this->templatesTable,
            $id
        ]));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

    /**
     * Insert a new template record into templates database.
     *
     * @param string $templateName
     * @param int $externalTemplateId
     *
     * @return int
     */
    public function insertTemplate($templateName, $externalTemplateId = 0, $themeVersion = '1.0', $attributes = false)
    {
        $attributes = $attributes ? json_encode($attributes) : [];

        $query = $fields = [];

        $query[] = 'INSERT INTO ' . $this->templatesTable;
        $query[] = 'SET';
        $fields[] = 'CodeName="' . $this->db->escape($templateName) . '"';
        $fields[] = 'NextGenTemplate="1"';
        $fields[] = 'ExpandishTemplate="1"';
        $fields[] = 'custom_template="1"';
        $fields[] = 'theme_version="' . $themeVersion . '"';
        $fields[] = "attributes='" . $attributes . "'";
        if ($externalTemplateId) {
            $fields[] = 'external_template_id=' . $externalTemplateId;
        }
        $query[] = implode(', ', $fields);

        $this->db->query(implode(' ', $query));

        return $this->db->getLastId();
    }

    /**
     * Insert a new description row into template description table.
     *
     * @param int $templateId
     * @param string $language
     * @param array $description
     *
     * @return void
     */
    public function insertTemplateDescription($templateId, $language, $description)
    {
        $query = $fields = [];

        $query[] = 'INSERT INTO ' . $this->templatesDescriptionTable;
        $query[] = 'SET';
        $fields[] = 'Name="' . $description->name . '"';
        $fields[] = 'Description="' . $description->description . '"';
        $fields[] = 'Image="' . $description->image . '"';
        $fields[] = 'Demourl="' . $description->demourl . '"';
        $fields[] = 'Lang="' . $language . '"';
        $fields[] = 'TemplateId="' . $templateId . '"';
        $query[] = implode(', ', $fields);

        $this->db->query(implode(' ', $query));
    }

    /**
     * Remove a template record from the database.
     *
     * @param int $templateId
     *
     * @return void
     */
    public function destroyTemplate($templateId)
    {
        $query = [];

        $query[] = 'DELETE FROM ' . $this->templatesTable;
        $query[] = 'WHERE id="' . $templateId . '" AND custom_template=1';

        $this->db->query(implode(' ', $query));
    }

    /**
     * Remove a template record from the database useing the CodeName.
     *
     * @param string $codeName
     *
     * @return void
     */
    public function deleteTemplateByCode(string $codeName)
    {
        $query = [];

        $query[] = 'DELETE FROM ' . $this->templatesTable;
        $query[] = 'WHERE CodeName="' . $codeName . '" AND custom_template=1';

        $this->db->query(implode(' ', $query));
    }

    /**
     * Check if an option is exists in the database.
     *
     * @param array $option
     *
     * @return bool
     */
    public function ifOptionExists($option)
    {
        $query = 'SELECT 1 FROM %s WHERE LookUpKey="%s" AND Name="%s" AND Lang="%s"';

        $data = $this->db->query(vsprintf($query, [
            $this->ecLookupTable,
            $option->key,
            $option->name,
            $option->language,
        ]));

        if ($data->num_rows) {
            return true;
        }

        return false;
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
            $this->ecLookupTable,
            $option->key,
            $option->name,
            $option->value,
            $option->language,
            $option->sort,
        ]));
    }

    /**
     * Factory method to apply template and set it as a default.
     *
     * @param array $template
     *
     * @return bool
     */
    public function applyTemplate($template)
    {
        $applier = $this->load->model('templates/applier', ['return' => true]);
        $setting = $this->load->model('setting/setting', ['return' => true]);

        $templateContents = json_decode(
            file_get_contents($this->baseDir . $template['CodeName'] . '/' . $template['CodeName'] . '.json')
            , true);

        $applier->construct($template, $this->db, $this->baseDir);

        $applier->setTemplateObject($templateContents);

        $applier->setLocals($templateContents['locales']);

        $applier->setLanguages($this->dashboardLanguages);

        $applier->setOptions( $templateContents['options'] );

        $applier->applySettings();

        $applier->apply();

        $dirTemplate = DIR_TEMPLATE;
        if (IS_CUSTOM_TEMPLATE && !IS_ADMIN) {
            $dirTemplate = DIR_CUSTOM_TEMPLATE;
        }

        $cacheFilesDir = rtrim($dirTemplate, '/') . '/cache/*';
        $cacheFiles = glob($cacheFilesDir);
        foreach ($cacheFiles as $cacheFile) {
            if (is_file($cacheFile)) {
                unlink($cacheFile);
            }
        }
        
        if($this->isCustomTemplate($template['CodeName'])){
            $sourceDemoImages = BASE_STORE_DIR . 'customtemplates/' . $template['CodeName'] . "/demo-images";
            $destinationDemoImages = "image/data/custom-" .  $template['CodeName'];
        }else{
            $sourceDemoImages = ONLINE_STORES_PATH . "OnlineStores/image/TemplateImages/" . $template['CodeName'];
            $destinationDemoImages = "image/data/" . $template['CodeName'];
        }
        
        $this->uploadFolder($sourceDemoImages, $destinationDemoImages);

        $setting->editSettingValue('config', 'config_template', $template['CodeName']);

        return true;
    }

    /**
     * Upload folder contents to a destination path
     * this is used to upload a local files to filesystem adapter
     *
     * @param string $source
     * @param string $destination
     *
     * @return void
     */
    protected function uploadFolder($source, $destination)
    {
        $directory = new \RecursiveDirectoryIterator(
            $source, RecursiveDirectoryIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_SELF
        );
        $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $path => $value) {
            $newPath = str_replace($source, $destination, $path);
            if ($value->isDir()) {
                \Filesystem::createDir($newPath);
            } else {
                \Filesystem::setPath($newPath)->upload($path);
            }
        }
    }

    /**
     * a facade method to parse locale variables.
     *
     * @param object $description
     * @param object $locales
     *
     * @return object
     */
    public function localize($description, $locales)
    {
        $applier = $this->load->model('templates/applier', ['return' => true]);

        return $applier->localize($description, (array)$locales);
    }

    /**
     * Check if the template was applied before
     *
     * @param int $templateId
     *
     * @return bool
     */
    public function templateIsAlreadyApplied($templateId)
    {
        $data = $this->db->query(vsprintf('SELECT 1 FROM %s WHERE TemplateId=%d', [
            $this->pagesTable,
            $templateId
        ]));

        if ($data->num_rows) {
            return true;
        }

        return false;
    }

    /**
     * Upgrade template version to a pecific version
     *
     * @param int $templateId
     * @param float $version
     *
     * @return void
     */
    public function updateTemplateVersion($templateId, $version)
    {
        $query = 'UPDATE %s SET theme_version="%s" WHERE id=%d';
        $query = sprintf($query, $this->templatesTable, $version, $templateId);

        $this->db->query($query);
    }

    public function getTemplateEcPagesByTemplateId($templateId)
    {
        $query = 'SELECT * FROM %s WHERE TemplateId=%d';

        $data = $this->db->query(sprintf($query, 'ecpage', $templateId));

        if ($data->num_rows > 0) {
            return $data->rows;
        }

        return false;
    }

    public function beginTransaction()
    {
        $this->db->query("START TRANSACTION;");
    }


    public function EmptyECLookupTable()
    {
        $query = "DELETE FROM {$this->ecLookupTable};";

        $this->db->query( $query );
    }


    public function commitTransaction()
    {
        $this->db->query("COMMIT;");
    }

    public function copyFolder($src, $dst)
    {
        if(!file_exists($src)){
            return;
        }

        $dir = opendir($src);
        if(!file_exists($dst)){
            @mkdir($dst);
        }

        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    $this->copyFolder($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    /**
     * Get current applied template
     */
    public function getCurrentTemplate()
    {
        $code_name = $this->config->get('config_template');
        $sql = "SELECT * FROM `ectemplate` where `CodeName` = '$code_name'";
        $query = $this->db->query($sql);
        if($query->num_rows > 0){
            return $query->row;
        }
        return false;
    }
    
    
    /**
     * Check if the Template is Custom for the given CodeName
     * @param $CodeName
     * 
     */
    protected function isCustomTemplate($CodeName){
        $sql = "SELECT * FROM `ectemplate` where `CodeName` = '$CodeName'";
        $query = $this->db->query($sql);
        if($query->num_rows > 0){
            return true;
        }
        return false;
    }

     /**
     * Clear Template Cache
     *
     * @return void
     */
     public function clearCache(): void
     {
         // delete cache
         /*if (is_dir(DIR_ONLINESTORES . 'expandish/view/theme/cache'))
            $this->removeDirectory(DIR_ONLINESTORES . 'expandish/view/theme/cache');
         */
         if (is_dir(DIR_ONLINESTORES . 'ecdata/stores/' . STORECODE . '/customtemplates/cache')) {
            $this->removeDirectory( DIR_ONLINESTORES . 'ecdata/stores/' . STORECODE . '/customtemplates/cache');
         }
     }

     /**
     * Remove directory
      *
     * @param $dir string
     * @return void
     */
     private function removeDirectory(string $dir): void
     {
        $directory = new RecursiveDirectoryIterator($dir,  FilesystemIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if (is_dir($file)) {
                rmdir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dir);
    }
}
