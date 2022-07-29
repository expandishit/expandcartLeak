<?php

class ModelTemplatesHierarchy extends ModelTemplatesTemplate
{
    /**
     * Define the required directories.
     *
     * @return array
     */
    private function requiredDirectories()
    {
        return [
            'template/account',
            'template/error',
            'template/product',
            'template/section',
        ];
    }

    /**
     * check for the required directories.
     *
     * @return bool
     */
    public function requiredDirectoriesGaurd($path)
    {
        $path = rtrim($path, '/') . '/';
        $directory = new \RecursiveDirectoryIterator($path);

        $directory->setFlags(\RecursiveDirectoryIterator::SKIP_DOTS);

        $iterator = new \RecursiveIteratorIterator($directory, \RecursiveIteratorIterator::SELF_FIRST);

        $files = [];

        $requiredDirectories = array_flip($this->requiredDirectories());

        foreach ($iterator as $name => $file) {
            if ($file->isDir()) {
                $directoryName = str_replace($path, '', $file->getPathname());

                if (isset($requiredDirectories[$directoryName])) {
                    $files[] = $directoryName;
                }
            }
        }

        $diff = array_diff($this->requiredDirectories(), $files);

        if (empty($diff)) {
            return true;
        }

        if ($this->reCheckTemplateFiles($path)) {
            return true;
        }

        array_map(function ($v) {
            $this->setError(
                sprintf($this->language->get('missing_directory'), $v)
            );
        }, $diff);

        return false;
    }

    protected function reCheckTemplateFiles(string $path)
    {
        $dirs = array_filter(glob($path), 'is_dir');

        return array_diff($dirs, $this->requiredDirectories());
    }

    /**
     * scan files to check if it contain a php or not.
     *
     * string $path
     *
     * @return bool
     */
    public function filesContentGaurd(string $path) : bool
    {
        if (is_dir($path) == false) {
            $this->setError('Illegal path');
            return false;
        }

        $cmd = 'egrep -IrH "<\?(\s+|php|=)" %s';

        exec(sprintf($cms, $path), $out);

        if (isset($out) && count($out) > 0) {
            $this->setError('Illegal code');
            return false;
        }

        return true;
    }
}
