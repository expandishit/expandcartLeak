<?php

include DIR_SYSTEM . 'library/PHPExcel.php';

use Exception;
use League\Flysystem\Filesystem;
use League\Flysystem\FileNotFoundException;
use ExpandCart\Foundation\Providers\Extension;
use League\Flysystem\Adapter\Ftp as FtpAdapter;
use League\Flysystem\ConnectionRuntimeException;

class ModelModuleFtpProductsImport extends Model
{
    public const EXTENSION_NAME = "ftp_products_import";

    private const EXTENSION_DEFAULT_SETTINGS =          [
        'status' => 0,
        'products_credentials' => [
            'server' => null,
            'user' => null,
            'password' => null,
            'port' => 21,
            'ssl' => 1,
            'document_root' => '/',
            'sync_path' => '',
            'filename' => null, // required
        ],
        'images_credentials' => [
            'server' => null,
            'user' => null,
            'password' => null,
            'port' => 21,
            'ssl' => 1,
            'document_root' => '/',
            'sync_path' => '',
            'save_image_path' => '', // optional
        ],
        'file_schema' => [
            'identifier_name' => 'id',
            'file_columns_map_required' => [
                'name_ar' => 'arabic_name',
                'name_en' => 'english_name',
                'description_ar' => 'description',
                'description_en' => 'description',
                'sku' => 'sku',
                'barcode' => 'barcode',
                'model' => 'model',
                'price' => 'price',
                'quantity' => 'quantity',
                'product_category' => 'product_category',
                'image' => 'image',
                'status' => 'status',
            ],
            'file_columns_map_optional' => [
                'product_image' => 'product_image',
            ]
        ],

    ];

    public const ALLOWED_EXCEL_EXTENSIONS = ['xlsx', 'xls', 'csv'];

    private const ALLOWED_IMAGES_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    private const EMPTY_PRODUCT_COLUMNS_MAP = [
        'model' => '',
        'sku' => '',
        'upc' => '',
        'ean' => '',
        'jan' => '',
        'isbn' => '',
        'mpn' => '',
        'location' => '',
        'quantity' => 0,
        'minimum' =>  0,
        'preparation_days' => 0,
        'maximum' => 1,
        'subtract' => 0,
        'notes' => '',
        'barcode' => '',
        'stock_status_id' => '',
        'date_available' => '',
        'manufacturer_id' => '',
        'shipping' => 0,
        'transaction_type' => 0,
        'external_video_url' => '',
        'price' => 0,
        'printable' => 0,
        'sls_bstr' => [
            'video' => '',
            'status' => 0,
            'free_html' => []
        ],
        'main_status' => 0,
        'main_unit' => null,
        'main_meter_price' => 0,
        'main_package_size' => 0,
        'main_price_percentage' => 0,
        'skirtings_status' => 0,
        'skirtings_meter_price' => 0,
        'skirtings_package_size' => 0,
        'skirtings_price_percentage' => 0,
        'metalprofile_status' => 0,
        'metalprofile_meter_price' => 0,
        'metalprofile_package_size' => 0,
        'metalprofile_price_percentage' => 0,
        'cost_price' => 0,
        'points' => 0,
        'weight' => 0,
        'weight_class_id' => 0,
        'length' => 0,
        'width' => 0,
        'height' => 0,
        'length_class_id' => 0,
        'status' => 0,
        'tax_class_id' => 0,
        'sort_order' => 0,
        'affiliate_link' => null,
        'pd_is_customize' => 0,
        'pd_custom_min_qty' => 1,
        'pd_custom_price' => 0,
        'pd_back_image' => null,
        'start_time' => null,
        'end_time' => null,
        'max_price' => 0,
        'min_offer_step' => 0,
        'start_price' => 0,
        'product_description' => [],
    ];

    private $excelTempDir;

    private $imageTempDir;

    private $productServer;

    private $imageServer;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->excelTempDir = TEMP_DIR_PATH . self::EXTENSION_NAME;
        $this->imageTempDir = BASE_STORE_DIR . 'image/data/products';
    }

    /**
     * update the module settings.
     *
     * @param array $inputs
     *
     * @return bool
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->insertUpdateSetting(self::EXTENSION_NAME, $inputs);
        return true;
    }

    public function getSettings()
    {
        return $this->mergeRecursive(self::EXTENSION_DEFAULT_SETTINGS, $this->config->get(self::EXTENSION_NAME) ?? []);
    }

    /**
     * Check if app installed
     *
     * @return boolean
     */
    public function isInstalled()
    {
        return Extension::isInstalled(self::EXTENSION_NAME);
    }

    /**
     * Check app status
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->isInstalled() && (int) $this->getSettings()['status'] === 1;
    }

    /**
     *   Install the required values for the application.
     *
     *   @return boolean whether successful or not.
     */
    public function install($store_id = 0)
    {
        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ftp_stored_files` (
                `id` int(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `size` INT NOT NULL,
                `dirname` VARCHAR(255) NOT NULL,
                `filename` VARCHAR(255) NOT NULL,
                `extension` VARCHAR(11) NOT NULL,
                `status` TINYINT(1) DEFAULT 0,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
            )");

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     *   Remove the values from the database.
     *
     *   @return boolean whether successful or not.
     */
    public function uninstall($store_id = 0, $group = self::EXTENSION_NAME)
    {
        try {
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ftp_stored_files`");
            return true;
        } catch (Exception $th) {
            return false;
        }
    }

    public function startImportProducts()
    {
        // Run background process.
        $file_location = DIR_SYSTEM . 'library/ftp_products_import.php';
        $url = (string)$this->url->link('module/ftp_products_import/importProducts', '', 'SSL');
        $cookie = http_build_query($_COOKIE, '', ',');
        $this->execCommand("php $file_location $url $cookie");
    }

    /**
     * connect to ftp servers based on settings and download all not imported excel files 
     * and install product images then store all products
     *
     * @return bool task success or fails
     */
    public function importProducts()
    {

        if (!$this->isActive()) return false;

        $settings = $this->getSettings();

        // connect to product server
        if (!$this->productServer = $this->initialFileSystemConnection($settings['products_credentials'])) return false;

        $files = $this->productServer->listContents($settings['products_credentials']['sync_path'], true);

        // filter files before download them 
        $files = $this->filterFiles(
            $files,
            function ($file_meta) use ($settings) {
                return in_array($file_meta['extension'], self::ALLOWED_EXCEL_EXTENSIONS)
                    && $file_meta['basename'] == $settings['products_credentials']['filename']
                    && !$this->isStoredFileBefore($file_meta);
            }
        );

        // save excel
        foreach ($files as $file) $this->storeProductFileMeta($file);

        // connect to image server
        if (!$this->imageServer = $this->initialFileSystemConnection($settings['images_credentials'])) return false;

        $files = $this->imageServer->listContents($settings['images_credentials']['sync_path'], true);

        $files = $this->filterFiles(
            $files,
            function ($file_meta) {
                return in_array($file_meta['extension'], self::ALLOWED_IMAGES_EXTENSIONS);
            }
        );

        // save images
        foreach ($files as $file) $this->storeImageFileMeta($file);

        // start save products in excel sheets
        $this->log("start save products in excel sheets");
        $this->startStoreProducts();


        return true;
    }

    public function getUnNotifiedTasksCount()
    {
        $query = $this->db->query("SELECT COUNT(id) as files_count FROM " . DB_PREFIX . "ftp_stored_files WHERE status = 1");
        return (int) $query->row['files_count'];
    }

    public function markAsNotified()
    {
        $this->db->query("UPDATE " . DB_PREFIX . "ftp_stored_files SET status = 2 WHERE status = 1");
    }

    private function filterFiles(array $files_collection, Closure $custom_condition)
    {
        return array_filter($files_collection, function ($file_meta) use ($custom_condition) {
            return $file_meta['visibility'] == 'public' &&
                $file_meta['type'] == 'file' &&
                $custom_condition($file_meta);
        });
    }

    private function storeProductFileMeta($file_meta)
    {
        if (!is_dir($this->excelTempDir))
            mkdir($this->excelTempDir, 0777, true);

        try {
            if (!$fileContent = $this->productServer->read($file_meta['path'])) return false;

            file_put_contents($this->excelTempDir . '/' . $file_meta['filename'] . '.' . $file_meta['extension'], $fileContent);

            $this->db->query("INSERT INTO " . DB_PREFIX . "ftp_stored_files SET size = '" . (int)$file_meta['size'] . "', filename='" . $file_meta['filename'] . "',  extension='" . $file_meta['extension'] . "', dirname='" . $file_meta['dirname'] . "'");

            return true;
        } catch (FileNotFoundException $th) {
            return false;
        }
    }

    private function isStoredFileBefore($file_meta)
    {
        $query = $this->db->query("SELECT id FROM " . DB_PREFIX . "ftp_stored_files WHERE size = '" . (int)$file_meta['size'] . "' 
        AND filename='" . $file_meta['filename'] . "' AND extension='" . $file_meta['extension'] . "'");
        return !!$query->num_rows;
    }

    private function storeImageFileMeta($file_meta)
    {
        $settings = $this->getSettings();

        $tempDir = $this->imageTempDir . '/' . $settings['images_credentials']['save_image_path'];

        $tempDir = str_replace('//', '/', $tempDir);

        if (substr($tempDir, -1) == '/')  $tempDir = substr($tempDir, 0, -1);

        if (!is_dir($tempDir))
            mkdir($tempDir, 0777, true);

        $tempName = $tempDir . '/' . $file_meta['filename'] . '.' . $file_meta['extension'];

        if (file_exists($tempName)) return true;

        try {

            if (!$fileContent = $this->imageServer->read($file_meta['path'])) return false;

            file_put_contents($tempName, $fileContent);

            return true;
        } catch (FileNotFoundException $th) {
            return false;
        }
    }

    private function initialFileSystemConnection($cred)
    {
        $adapter = new FtpAdapter([
            'host' => $cred['server'],
            'username' => $cred['user'],
            'password' => $cred['password'],
            /** optional config settings */
            'port' => $cred['port'],
            'root' => $cred['document_root'],
            'ssl' => (bool)$cred['ssl'],
            'timeout' => 30000,
            'ignorePassiveAddress' => true,
        ]);

        try {
            $adapter->connect();
        } catch (ConnectionRuntimeException $th) {
            return false;
        }

        if ($adapter->isConnected()) return new Filesystem($adapter);
        return false;
    }

    private function mergeRecursive(array $array1, array $array2)
    {
        $merged = $array1;
        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->mergeRecursive($merged[$key], $value);
            } else if (is_numeric($key)) {
                if (!in_array($value, $merged)) {
                    $merged[] = $value;
                }
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    private function startStoreProducts()
    {
        foreach ($this->getUnProcessedFiles() as $file) {
            $this->log("start process file: " . $file['filename']);
            if ($this->isProcessedFile($file)) {
                $this->markFileAsProcessed($file);
                $this->deleteTempFile($file);
                $this->log("processed file: " . $file['filename'] . ' DONE');
            }
        }
    }

    private function getUnProcessedFiles()
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ftp_stored_files WHERE status = '0'");
        return !!$query->num_rows ? $query->rows : [];
    }

    private function isProcessedFile(array $file_meta)
    {
        // start ...
        $filename = $this->excelTempDir . '/' . $file_meta['filename'] . '.' . $file_meta['extension'];
        $extension = $file_meta['extension'];

        try {
            if ($extension == 'csv')
                $reader = PHPExcel_IOFactory::createReader('CSV')->load($filename);
            else
                $reader = PHPExcel_IOFactory::load($filename);
        } catch (Exception $e) {
            die('Error loading file "' . pathinfo($filename, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }

        $productsInSheet = $reader->getActiveSheet()->toArray(null, true, true, true);


        $settings = $this->getSettings();

        $productColumns = array_flip(array_merge($settings['file_schema']['file_columns_map_required'], $settings['file_schema']['file_columns_map_optional']));

        $fileHeaderMap = array_shift($productsInSheet); // remove heading sheet

        $validProductColumns = [];

        foreach ($fileHeaderMap as $key => $value)
            $validProductColumns[$key] = array_key_exists($value, $productColumns) ? $productColumns[$value] : 'unknown';


        foreach ($productsInSheet as &$product) {
            $this->removeUnknownProductKeys($validProductColumns, $product);
            $this->formatProductRow($product);
            $this->createOrUpdateProduct($product);
        }

        return true;
    }

    private function removeUnknownProductKeys($columns, &$product)
    {
        $product = array_combine(array_merge($product, $columns), $product);
        foreach ($product as $key => $_) if ($key == "unknown") unset($product[$key]);
    }

    private function formatProductRow(&$product)
    {
        static $arEnLanguages;

        // description
        if ($arEnLanguages === null) {
            $query = $this->db->query("SELECT language_id, code FROM " . DB_PREFIX . "language WHERE code = 'ar' OR code = 'en'");
            $arEnLanguages = $query->rows;
        }

        $product['product_description'] = [];

        foreach ($arEnLanguages as $value) {
            $desc = isset($product['description_' . $value['code']]) ? $product['description_' . $value['code']] : (
                ($value['code'] == 'ar' && isset($product['description_en'])) ? $product['description_en'] : (
                    ($value['code'] == 'en' && isset($product['description_ar'])) ? $product['description_ar'] : ''));

            $product['product_description'][$value['language_id']] = [
                'meta_keyword' => '',
                'meta_description' => '',
                'tag' => '',
                'description' => $desc,
            ];

            if ('ar' === $value['code']) $product['product_description'][$value['language_id']]['name'] = $product['name_ar'];
            if ('en' === $value['code']) $product['product_description'][$value['language_id']]['name'] = $product['name_en'];
        }

        unset($product['description'], $product['name_ar'], $product['name_en']);

        $settings = $this->getSettings();

        // images
        $product['image'] = 'data/products/' . $settings['images_credentials']['save_image_path'] . '/' .  $product['image'];
        $product['image'] = str_replace('//', '/', $product['image']);

        if (!empty($product['product_image'])) {
            $product['product_image'] = explode(',', trim($product['product_image']));
            foreach ($product['product_image'] as $key => &$value) {
                $value = 'data/products/'  . $settings['images_credentials']['save_image_path'] . '/' .  $value;
                $value = str_replace('//', '/', $value);
                $value = [
                    'image' => $value,
                    'sort_order' => $key
                ];
            }
        } else
            unset($product['product_image']);

        // categories
        if (!empty(trim($product['product_category']))) {
            $product['product_category'] = explode(',', trim($product['product_category']));
            foreach ($product['product_category'] as $key => &$value) {
                if ($this->categoryExist($value))
                    $value = (int)$value;
                else
                    unset($product['product_category'][$key]);
            }
        } else
            unset($product['product_category']);

        // product id
        if ($productId = $this->getProductId($product))
            $product['product_id'] = $productId;
    }

    private function createOrUpdateProduct($product)
    {
        static $productModel;

        if (!$productModel) $productModel = $this->load->model('catalog/product', ['return' => true]);

        $productFormatted = array_merge(
            self::EMPTY_PRODUCT_COLUMNS_MAP,
            ['product_store' => [$this->config->get('config_store_id') ?: 0]],
            $product
        );

        if (!isset($product['product_id']))
            $productModel->addProduct($productFormatted);
        else
            $productModel->editProduct($product['product_id'], $productFormatted);
    }

    private function categoryExist($catId)
    {
        $query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "category WHERE category_id = '" . (int)$catId . "'");
        return !!$query->num_rows;
    }

    private function getProductId($product)
    {
        $settings = $this->getSettings();

        $identifierName = $settings['file_schema']['identifier_name'];

        if (isset($product[$identifierName])) {
            $value = $identifierName == 'product_id' ? (int)$product[$identifierName] : (string)$product[$identifierName];

            $query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product WHERE " . $identifierName . " = '" . $value . "'");
            return !!$query->num_rows ? (int)$query->row['product_id'] : 0;
        }

        return 0;
    }

    private function markFileAsProcessed(array $file_meta)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "ftp_stored_files SET status = '1' WHERE id = '" . $file_meta['id'] . "'");
    }

    private function deleteTempFile(array $file_meta)
    {
        try {
            unlink($this->excelTempDir . '/' . $file_meta['filename'] . '.' . $file_meta['extension']);
        } catch (Exception $th) {
        }
    }

    private function execCommand($command)
    {
        if (substr(php_uname(), 0, 7) == "Windows") {
            //windows
            pclose(popen("start /B cmd /C " . $command . " >NUL 2>&1", "r"));
        } else {
            //linux
            shell_exec($command . " > /dev/null 2>/dev/null &");
        }

        return false;
    }

    private function log($resource)
    {
        $this->log->write(is_array($resource) ? json_encode($resource) : $resource);
    }
}
