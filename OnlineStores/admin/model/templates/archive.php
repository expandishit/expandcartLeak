<?php

class ModelTemplatesArchive extends ModelTemplatesTemplate
{
    /**
     * an instance of the ZipArchive object.
     *
     * @var \ZipArchive
     */
    protected static $archive = null;

    /**
     * the content of the schema file.
     *
     * @var string
     */
    protected $templateJson = null;

    /**
     * the allowed extensions to be uploaded.
     *
     * @var array
     */
    protected $allowedExtensions = [
        'json',
        'js',
        'png',
        'gif',
        'svg',
        'jpg',
        'css',
        'expand',
        'html',
        'woff',
        'ttf',
        'eot',
        'otf',
        'woff2',
        'txt',
        'scss',
        'less',
        'rb',
        'db',
        'psd',
        'map',

        // From cloudflare
        'bmp',
        'ejs',
        'jpeg',
        'pdf',
        'ps',
        'class',
        'pict',
        'webp',
        'eps',
        'pls',
        'svgz',
        'csv',
        'mid',
        'swf',
        'doc',
        'ico',
        'midi',
        'ppt',
        'tif',
        'xls',
        'docx',
        'jar',
        'pptx',
        'tiff',
        'xlsx',
    ];

    protected $specialFiles = [
        'README',
        'README.md',
        '.gitignore',
        '.DS_Store',
    ];

    /**
     * a factory method to create a new \ZipArchive instance and open the archived file.
     *
     * @param string $file
     *
     * @return \ZipArchive
     */
    public function open($file)
    {
        if (!static::$archive) {
            static::$archive = new ZipArchive();

            static::$archive->open($file);
        }

        return static::$archive;
    }

    /**
     * Factory method to do all the zip archive stuff.
     *
     * @param string $file
     *
     * @return array
     */
    public function files($file)
    {
        $archive = $this->open($file);

        $specialFiles = array_flip($this->specialFiles);

        $files_num = static::$archive->numFiles;

        if ($files_num > $this->maximumTemplateFiles) {
            $this->setError('maximum files count exceeded ' . $files_num . "/" . $this->maximumTemplateFiles);

            return false;
        }

        $missingSchema = true;

        for ($i = 0; $i < static::$archive->numFiles; $i++) {
            $statIndex = static::$archive->statIndex($i);
            $name = $statIndex['name'];

            if (
                !$this->isArchiveDir($statIndex) &&
                !isset($specialFiles[basename($name)]) &&
                !$this->extensionGaurd($ext = $this->getExtension($name))
            ) {

                $this->setError(['Illegal file extension']);
                return false;
            }

            if ($name == $this->getTemplateName() . '/schema.json') {
                $missingSchema = false;
                $this->setTemplateJson(static::$archive->getFromName($name));
                $this->setTemplateJsonPath($name);
            }

            $files[] = $name;
        }

        if ($missingSchema) {

            $this->setError('missing schema file');

            return false;
        }

        return $files;
    }

    /**
     * check if specific row is a directory.
     *
     * @param $file
     *
     * @return string|bool
     */
    public function isArchiveDir($statIndex)
    {
        $name = $statIndex['name'];
        $size = $statIndex['size'];

        return (substr($name, -1) == '/') && $size < 1;
    }

    /**
     * extract extension from file.
     *
     * @param $file
     *
     * @return string|bool
     */
    public function getExtension(string $file)
    {
        return pathinfo($file, PATHINFO_EXTENSION);
    }

    /**
     * check if the used extension is allowed.
     *
     * @param $extension
     *
     * @return bool
     */
    public function extensionGaurd(string $extension) : bool
    {
        $allowedExtensions = array_flip($this->allowedExtensions);
        return isset($extension) && isset($allowedExtensions[strtolower($extension)]);
    }

    /**
     * factory method to extract the $archive contents into another path.
     *
     * @param $file
     *
     * @return \ModelTemplatesTemplate
     */
    public function extract($path)
    {
        static::$archive->extractTo($path);

        return $this;
    }

    /**
     * factory method to close $archive instance.
     *
     * @return \ModelTemplatesTemplate
     */
    public function close()
    {
        return static::$archive->close();
    }

    /**
     * a destructor to perform a mandatory clean up.
     *
     * @return \ModelTemplatesTemplate
     */
    public function __desctruct()
    {
        $this->close();
    }
}