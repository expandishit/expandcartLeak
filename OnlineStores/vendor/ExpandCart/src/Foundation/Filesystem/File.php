<?php

namespace ExpandCart\Foundation\Filesystem;

use SplFileInfo;
use ExpandCart\Foundation\Exceptions\FileException;

class File
{
    /**
     * @var mixed
     */
    protected $content;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var FileInterface
     */
    protected $file;

    /**
     * @var array
     */
    protected $fileInfo;

    /**
     * @var array
     */
    protected $mimeTypesMap = [
        'image/png' => 'Image',
        'image/jpeg' => 'Image',
        'image/gif' => 'Image',
        'image/webp' => 'Image',

    ];

    /**
     * @param Filesystem $filesystem
     *
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;

        $this->fileInfo = $this->filesystem->getMetadata();
    }

    public function __call($name, $args)
    {
        try {
            $class = $this->resolveFile($args);
            return $class->$name(...$args);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * resolve the given file based on it's mimetype.
     *
     * @param array $args
     *
     * @return FileInterface
     */
    protected function resolveFile($args)
    {
        $className = $this->mimeTypesMap[$this->fileInfo['mimetype']];
        $className = 'ExpandCart\Foundation\Filesystem\Files\\' . $className;

        if (class_exists($className) == false) {
            throw new \Exception('Invalid or unsupported file type');
        }

        return $this->file = (new $className)->setFile($this)->setInfo($this->fileInfo);
    }

    /**
     * Set the file contents.
     *
     * @param mixed $content
     *
     * @return self
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Return file contents.
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Save file contents to a given path.
     * Note that this will use the value of self::$content
     *
     * @param string $path
     *
     * @return self
     */
    public function saveTo($path)
    {
        $this->filesystem->uploadTo($path, $this->getContent());

        return $this;
    }
}
