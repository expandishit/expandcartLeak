<?php

class ModelTemplatesImport extends ModelTemplatesTemplate
{
    /**
     * a factory method to move the uploaded file.
     *
     * @param array $template
     * @param string $directoryPath
     *
     * @return bool
     */
    public function upload($template, $directoryPath)
    {
        return move_uploaded_file($template['tmp_name'], $directoryPath . $template['name']);
    }

    /**
     * a factory method to move or rename a file.
     *
     * @param string $from
     * @param string $to
     *
     * @return bool
     */
    public function mv($from, $to)
    {
        return rename($from, $to);
    }

    /**
     * a factory method to remove a file or a directory based on type.
     *
     * @param string $path
     *
     * @return bool
     */
    public function remove($path)
    {
        if (is_dir($path)) {
            return $this->removeDirectoryRecursively($path);
        }

        return unlink($path);
    }

    /**
     * Removes all contents of a directory.
     *
     * @param string $path
     *
     * @return bool
     */
    private function removeDirectoryRecursively($path)
    {
        $directory = new \RecursiveDirectoryIterator(
            $path, RecursiveDirectoryIterator::SKIP_DOTS
        );

        $iterator = new \RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);

        $directories = [];

        foreach ($iterator as $name => $file) {
            if ($file->isDir()) {
                $directories[] = $name;
            } else {
                unlink($name);
            }
        }

        for ($i = count($directories), $n = 0; $i >= $n; $i--) {
            rmdir($directories[$i]);
        }

        return rmdir($path);
    }
}
