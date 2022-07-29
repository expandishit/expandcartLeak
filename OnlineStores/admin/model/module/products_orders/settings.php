<?php

use ExpandCart\Foundation\String\Slugify;

class ModelModuleProductsOrdersSettings extends Model
{
    /**
     * The settings key string
     *
     * @var string
     */
    protected $settingsKey = 'products_orders';

    /**
     * @var array
     */
    protected $errors = null;

    /**
     * Update the module settings.
     *
     * @param array $inputs
     *
     * @return bool|\Exception
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->insertUpdateSetting(
            'module', [$this->settingsKey => $inputs]
        );

        return true;
    }

    /**
     * Get settings.
     *
     * @return array|bool
     */
    public function getSettings()
    {
        return ($this->config->get($this->settingsKey) ?: []);
    }

    /**
     * validate inputs
     *
     * @param array $data
     *
     * return bool
     */
    public function validate($data, $files)
    {
        $this->errors = [];

        if (mb_strlen($data['filename']) < 1) {
            $this->errors[] = 'filename field is required';
        }

        if (isset($files['file']['name']) == false || $files['file']['error'] > 1) {
            $this->errors[] = 'Error in upload file';
        }

        if (
            isset($files['file']['error']) &&
            $files['file']['error'] == 0 &&
            $files['file']['size'] > 15000000
        ) {
            $this->errors[] = 'File is too large';
        }

        if ($this->errors) {
            return false;
        }

        return true;
    }

    /**
     * Push an error to the errors array.
     *
     * @param mixed $error
     *
     * @return void
     */
    public function setError($error)
    {
        $this->errors[] = $error;
    }

    /**
     * Get all errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return is_array($this->errors) ? $this->errors : [];
    }

    /**
     * Install and apply the required DB changes.
     *
     * @return void
     */
    public function install()
    {
        $columns = [];
        $columns[] = '`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY';
        $columns[] = '`filename` varchar(200) DEFAULT NULL';
        $columns[] = '`products` longtext';
        $columns[] = '`status` tinyint(1) DEFAULT "1"';
        $columns[] = '`created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP';
        $columns[] = '`updated_at` DATETIME NULL DEFAULT NULL';
        $columns[] = '`user_id` INT(11) DEFAULT 0';
        $this->db->query(sprintf(
            'CREATE TABLE IF NOT EXISTS `%s` (%s) ENGINE = InnoDB CHARSET=utf8 COLLATE=utf8_general_ci',
            'products_orders',
            implode(', ', $columns)
        ));
    }

    /**
     * To drop the application related changes.
     *
     * @return void
     */
    public function uninstall()
    {
        $this->db->query('DROP TABLE IF EXISTS `products_orders`');
    }

    /**
     * Upload given file.
     *
     * @return mixed
     */
    public function upload($file, $fileName)
    {
        $baseDir = rtrim(TEMP_DIR_PATH, '/');
        $fileName = basename($fileName);
        $fileInfo = pathinfo($fileName);
        $filePath = vsprintf('%s/%s_%s.%s', [
            $baseDir, time(), (new Slugify)->slug($fileInfo['filename']), $fileInfo['extension']
        ]);

        if (move_uploaded_file($file, $filePath)) {
            return $filePath;
        }

        $this->errors[] = 'File does not uploaded' . ' (' . $filePath . ')';

        return false;
    }

    /**
     * Factory method to load and init sheet data
     *
     * @param string $file
     *
     * @return mixed
     */
    public function getInitData($file)
    {
        $supportedFileTypes = [
            'xls' => 'Xls', 'xlsx' => 'Xlsx', 'ods' => 'Ods', 'csv' => 'Csv'
        ];

        $fileInfo = pathinfo($file);

        $extension = strtolower($fileInfo['extension']);

        if (!isset($supportedFileTypes[$extension])) {
            return false;
        }

        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($supportedFileTypes[$extension]);
            $reader->setReadDataOnly(TRUE);
            $reader->setReadEmptyCells(false);
            $spreadsheet = $reader->load($file);

            $worksheet = $spreadsheet->getActiveSheet();
        } catch (\Exception $e) {
            return false;
        }

        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        $initproducts = $options = [];
        $headers = [];
        for ($headerCol = 1; $headerCol <= $highestColumnIndex; ++$headerCol) {
            $value = $worksheet->getCellByColumnAndRow($headerCol, 1)->getValue();
            $headers[] = str_replace(' ', '', strtolower($value));
        }

        $flippedHeaders = array_flip($headers);

        if (!isset($flippedHeaders['barcode']) || !isset($flippedHeaders['quantity'])) {
            $this->setError('File must only containt barcode, quantity columns in this order');
            return false;
        }

        for ($row = 2; $row <= $highestRow; ++$row) {
            $product = [];
            for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                $value = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                $product[] = $value;
            }
            $initproducts[] = array_combine($headers, $product);
        }

        return $initproducts;
    }
}
