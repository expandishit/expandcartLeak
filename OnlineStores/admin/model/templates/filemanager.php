<?php

class ModelTemplatesFileManager extends ModelTemplatesTemplate
{
    /**
     * the tree array.
     *
     * @deprecated
     *
     * @var array
     */
    protected $tree = [];

    /**
     * the allowed files extensions array
     *
     * @var array
     */
    protected $allowedExtensions = [
        'js',
        'expand',
        'json',
        'css'
    ];

    /**
     * Generate the file tree array.
     *
     * @param string $path
     * @param int $key
     *
     * @return array
     */
    public function getFilesTree($path, $key = null)
    {
        $iterator = new \RecursiveDirectoryIterator(
            $path, RecursiveDirectoryIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_SELF
        );

        foreach ($iterator as $path => $value) {
            $key++;
            if ($value->isDir()) {
                $files[] = [
                    'key' => $key,
                    'folder' => true,
                    'title' => basename($path),
                    'children' => $this->getFilesTree($path, $key),
                    'extension' => 'directory',
                    'target' => $path,
                    'order' => 1,
                ];
            } else {
                if (in_array($value->getExtension(), $this->allowedExtensions)) {
                    $files[] = [
                        'key' => $key,
                        'title' => $value->getBasename(),
                        'order' => 0,
                        'target' => $path,
                        'extension' => $value->getExtension(),
                        'folder' => false,

                    ];
                }
            }
        }

        usort($files, function ($a, $b) {
            return $a['order'] < $b['order'];
        });

        return $files;
    }

    /**
     * Validates a given path string.
     *
     * @param string $path
     *
     * @return bool
     */
    public function validateFilePath($path)
    {
        if (is_string($path) == false) {
            return false;
        }

        if (file_exists($path) == false) {
            return false;
        }

        return true;
    }

    /**
     * Validates a given path extensoin.
     *
     * @param string $extension
     *
     * @return bool
     */
    public function validateFileExtension($extension)
    {
        if (in_array($extension, $this->allowedExtensions)) {
            return true;
        }

        return false;
    }

    /**
     * get a file contents.
     *
     * @param string $path
     *
     * @return string|bool
     */
    public function getFileContents($path)
    {
        return file_get_contents($path);
    }

    /**
     * puts a file contents.
     *
     * @param string $path
     * @param string $contents
     *
     * @return bool
     */
    public function putFileContents($path, $contents)
    {
        return file_put_contents($path, html_entity_decode($contents));
    }

    /**
     * get a path info.
     *
     * @param string $file
     *
     * @return array
     */
    public function getFileType($file)
    {
        return pathinfo($file);
    }

    /**
     * create a new directory.
     *
     * @param string $path
     * @param string $child
     *
     * @return void
     */
    public function newDirectory($path, $child)
    {
        mkdir(implode('/', [$path, $child]));
    }

    /**
     * create a new file.
     *
     * @param string $path
     * @param string $child
     *
     * @return bool
     */
    public function newFile($path, $child)
    {
        $f = fopen(implode('/', [$path, $child]), 'w');

        return fclose($f);
    }

    /**
     * Get sections schema files
     *
     * @param string $path
     * @param array $schemas
     *
     * @return array
     */
    public function getSectionSchemas($path, $schemas)
    {
        $directory = new \RecursiveDirectoryIterator(
            $path, RecursiveDirectoryIterator::SKIP_DOTS
        );

        $iterator = new \RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);

        $files = [];

        foreach ($iterator as $name => $file) {
            if (
                $file->isDir() == false &&
                strtolower($file->getExtension()) == 'json'
            ) {
                if (in_array(substr($file->getBasename(), 0, -5), $schemas)) {
                    $files[] = [
                        'path' => $file->getPathname(),
                        'file' => $file->getBasename()
                    ];
                } else {
                    unlink($file->getPathname());
                }
            }
        }

        return $files;
    }
}
