<?php

namespace ExpandCart\Foundation\Filesystem;

use SplFileInfo;
use League\Flysystem\Filesystem as Flyesystem;
use League\Flysystem\Util\MimeType as FlysystemMimeType;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use ExpandCart\Foundation\Exceptions\ExpandCartException;
use ExpandCart\Foundation\Support\Facades\Slugify;

use Google\Cloud\Storage\StorageClient;
use Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter;

class Filesystem
{
    protected $adapter;

    protected $adapterNamespace = null;

    protected $splfile;

    protected $instances = [];

    protected $path;

    protected $base;

    protected $pathPrefix = '';

    protected $defaults = [];

    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->setPathPrefix($config['path_prefix']);
        $this->setPath('');
        $this->setDefaults($config);

        if (isset($config['adapter'])) {
            $this->adapterNamespace = $config['adapter'];
            $this->setAdapter($this->resolveAdapter($config['adapter'], $config));
        } else {
            throw new \Exception('add adapter');
        }
    }

    /**
     * Set detaults array.
     * this is an array of values that should not be changed
     *
     * @param array $defaults
     *
     * @return self
     */
    protected function setDefaults($defaults) : self
    {
        $this->defaults = array_merge($this->defaults, $defaults);

        return $this;
    }

    /**
     * Restore a value from the defaults array.
     *
     * @param string $default
     *
     * @return self
     */
    public function restoreDefaults($default = false) : self
    {
        if ($default && isset($this->defaults[$default])) {
            $this->{Slugify::camelize($default)} = $this->defaults[$default];
        }

        return $this;
    }

    /**
     * Set the path prefix property value.
     *
     * @param string $pathPrefix
     *
     * @return self
     */
    public function setPathPrefix(string $pathPrefix) : self
    {
        $this->pathPrefix = $pathPrefix;

        return $this;
    }

    /**
     * Get path prefix value.
     * this function prepair the path and add the trailing / to it
     *
     * @return string
     */
    public function getPathPrefix() : string
    {
        return ($this->pathPrefix ? rtrim($this->pathPrefix, '/') . '/' : '');
    }

    /**
     * Set the base path for a given file.
     * setting the path will avoid you to pass the path to some other methods
     *
     * @param string $path
     *
     * @return self
     */
    public function setPath(string $path) : Self
    {
        $this->splfile = new SplFileInfo($path ?: '/');

        $this->resolvePath();

        return $this;
    }

    /**
     * Set the adapter object.
     *
     * @param AdapterInterface $adapter
     *
     * @return self
     */
    public function setAdapter(AdapterInterface $adapter) : self
    {
        $this->adapter = new Flyesystem($adapter, ['visibility' => 'public']);

        return $this;
    }

    /**
     * Resolve & translate a given path string into a the most important data.
     *
     * @param string $path
     *
     * @return string
     */
    public function resolvePath($path = '') : string
    {
        if ($path) {
            $this->setPath($path);
        }

        $pathInfo = $this->splfile->getPathInfo();

        $type = null;
        $extension = $this->splfile->getExtension();
        $extensions = FlysystemMimeType::getExtensionToMimeTypeMap();
        if ($extension != '' && in_array(strtolower($extension), array_keys($extensions))) {
            $type = 'file';
        } else {
            $type = 'dir';
        }

        $this->setType($type);

        $this->path = [
            'path' => $this->splfile->getPathname(),
            'file_name' => $this->splfile->getFilename(),
            'base_name' => $this->splfile->getBasename('.' . $this->splfile->getExtension()),
            'extension' => $extension,
            'directory_path' => $pathInfo->getPathname() == '.' ? '' : $pathInfo->getPathname(),
            'type' => $type,
            'directory_base_name' => $pathInfo->getBasename() == '.' ? '' : $pathInfo->getBasename(),
        ];

        return (isset($path[0]) && $path[0] == '/' ? '' : $this->getPathPrefix()) . $this->path['path'];
    }

    /**
     * Get instance of a gievn path.
     * based on the type of the given path [ file, dir ] return instance
     * of File or Directory
     *
     * @param sting $path
     *
     * @return self
     */
    public function get($path = false)
    {
        $this->resolvePath($path);

        if (isset($this->path['type']) == false) {
            return 'throw';
        }

        if ($this->path['type'] == 'file') {
            return new File($this);
        }

        if ($this->path['type'] == 'dir') {
            return new Directory($this);
        }

        return 'throw';
    }

    /**
     * Return the metadata from the adapter then merge path info with it.
     *
     * @param string $path
     *
     * @return array
     */
    public function getMetadata($path = false) : array
    {
        $path = $this->resolvePath($path);

        $data = $this->adapter->getMetadata($path);

        $invalidMimetypes = ['application/octet-stream', 'inode/x-empty', 'application/x-empty'];

        if (!isset($data['mimetype']) || in_array($data['mimetype'], $invalidMimetypes)) {
            $data['mimetype'] = FlysystemMimeType::detectByFilename($path);
        }

        $data['info'] = $this->path;

        return $data;
    }

    /**
     * Return path info , this is the result of resolvePath method.
     *
     * @return array
     */
    public function getPathInfo() : array
    {
        return $this->path;
    }

    /**
     * Return the base name of a given path using the reolvePath result.
     *
     * @return string
     */
    public function getBasename() : string
    {
        return isset($this->path['base_name']) ? $this->path['base_name'] : '';
    }

    /**
     * Resolve the adapter using the adapter name and the adapter config.
     *
     * @param string $adapter
     * @param array $config
     *
     * @return AdapterInterface
     * @throws Exception
     */
    public function resolveAdapter(string $adapter, $config = []) : AdapterInterface
    {
        switch ($adapter) {
            case 'local':
            case 'gcs':
                // Register the adapter in the container as a singleton
                \Container::set(
                    'fs.adapter.' . $adapter,
                    $this->{'resolve' . ucfirst($adapter) . 'Adapter'}($config)
                );
                return \Container::get('fs.adapter.' . $adapter);
                break;
            default:
                throw new \Exception('invalid adapter'); 
        }
    }

    /**
     * Factory method to initialize Local adapter.
     *
     * @param array $config
     *
     * @return AdapterInterface
     */
    protected function resolveLocalAdapter($config = []) : AdapterInterface
    {
        return new Local($this->base = $config['base'], LOCK_EX, Local::DISALLOW_LINKS, [
            'dir' => ['writable' => 0777],
            'file' => ['writable' => 0777],
        ]);
    }

    /**
     * Factory method to initialize Google cloud storage adapter.
     *
     * @param array $config
     *
     * @return AdapterInterface
     */
    protected function resolveGcsAdapter($config = []) : AdapterInterface
    {
        $storageClient = new StorageClient([
            'projectId' => FS['project_id'],
            'keyFilePath' => FS['key'],
        ]);

        $bucket = $storageClient->bucket(FS['bucket_id']);

        return new class ($storageClient, $bucket, $this->base = $config['base']) extends GoogleStorageAdapter {
            protected function getOptionsFromConfig(\League\Flysystem\Config $config)
            {
                $options = [];

                if (empty($this->bucket->info()['iamConfiguration']['uniformBucketLevelAccess']['enabled'])) {
                    if ($visibility = $config->get('visibility')) {
                        $options['predefinedAcl'] = $this->getPredefinedAclForVisibility($visibility);
                    } else {
                        $options['predefinedAcl'] = $this->getPredefinedAclForVisibility(AdapterInterface::VISIBILITY_PRIVATE);
                    }
                }

                if ($metadata = $config->get('metadata')) {
                    $options['metadata'] = $metadata;
                }

                return $options;
            }
        };
    }

    /**
     * Check if the type of a given path is file.
     *
     * @return bool
     */
    public function isFile() : bool
    {
        return $this->path['type'] == 'file';
    }

    /**
     * Check if the type of a given path is directory.
     *
     * @return bool
     */
    public function isDirectory() : bool
    {
        return $this->path['type'] == 'dir';
    }

    /**
     * Set or override the type of path.
     * this is useful for special cases , EG [ .git/ is directory and .htaccess is file ]
     * and this must be changed manually
     *
     * @param string $type
     *
     * @return self
     */
    public function setType(string $type) : self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the url of a given path.
     * if $path is null we will rely on the self::setPath() method or it will be empty path
     *
     * @param string $path
     *
     * @return string
     */
    public function getUrl($path = false)
    {
        if ($this->adapterNamespace == 'gcs') {
            return $this->adapter->getAdapter()->getUrl($this->resolvePath($path));
        }

        if ($this->adapterNamespace == 'local') {
            return implode('/', [rtrim(STORE_DATA_URL, '/'), $this->resolvePath($path)]);
        }

        return '';
    }

    /**
     * Get a list of contents of a given path , the path should be of dir type.
     * if $path is null we will rely on the self::setPath() method or it will be empty path
     *
     * @param string $path
     *
     * @return array|bool
     */
    public function list($path = false)
    {
        $path = $this->resolvePath($path);
        if ($this->isDirectory()) {
            return $this->adapter->listContents($path);
        }

        return false;
    }

    /**
     * Get a list of contents of a given path recursively , the path should be of dir type.
     * if $path is null we will rely on the self::setPath() method or it will be empty path
     *
     * @param string $path
     *
     * @return array|bool
     */
    public function listRecursively($path = false)
    {
        $path = $this->resolvePath($path);
        if ($this->isDirectory()) {
            return $this->adapter->listContents($path, true);
        }

        return false;
    }

    /**
     * Create a directory.
     *
     * @param string $path
     *
     * @return bool
     */
    public function create($path = false)
    {
        return $this->adapter->createDir($this->resolvePath($path));
    }

    /**
     * Create a directory.
     *
     * @param string $path
     *
     * @return bool
     */
    public function createDir($path = false)
    {
        return $this->adapter->createDir($this->resolvePath($path));
    }

    /**
     * Change a given path permissions.
     * the allowed permissions is [ private, public, writable ]
     *
     * @param string $perm
     *
     * @return self
     */
    public function changeMod($perm)
    {
        if ($this->adapterNamespace == 'gcs' && $perm == 'writable') {
            $perm = 'public';
        }

        if ($this->adapterNamespace == 'gcs' && $this->isDirectory()) {
            return $this;
        }

        $this->adapter->setVisibility($this->resolvePath(false), $perm);

        return $this;
    }

    /**
     * Delete a given path.
     *
     * @param string $path
     *
     * @return bool
     */
    public function delete($path = false)
    {
        if ($this->type == 'dir') {
            return $this->deleteDir($path);
        } else if ($this->type == 'file') {
            return $this->deleteFile($path);
        }
    }

    /**
     * Delete a given file path.
     *
     * @param string $path
     *
     * @return bool
     */
    public function deleteFile($path = false)
    {
        if ($path == $this->getPathPrefix()) {
            return false;
        }

        $resolvedPath = $this->resolvePath($path);

        if ($resolvedPath == $this->getPathPrefix()) {
            return false;
        }

        return $this->adapter->delete($resolvedPath);
    }

    /**
     * Delete a given directory path.
     *
     * @param string $path
     *
     * @return bool
     */
    public function deleteDir($path = false)
    {
        if ($path == $this->getPathPrefix()) {
            return false;
        }

        $resolvedPath = $this->resolvePath($path);

        if ($resolvedPath == $this->getPathPrefix()) {
            return false;
        }

        return $this->adapter->deleteDir($resolvedPath);
    }

    /**
     * Put contents to a given path.
     * this method does not accept to alter path and will use setAdapter path
     *
     * @return bool
     */
    public function write($content)
    {
        return $this->adapter->write($this->resolvePath(), $content);
    }

    /**
     * Put contents to a given path.
     * this method does not accept to alter path and will use setAdapter path
     *
     * @return bool
     */
    public function put($content)
    {
        return $this->adapter->put($this->resolvePath(), $content);
    }

    /**
     * Remove file from a given path.
     *
     * @param string $path
     *
     * @return bool
     */
    public function remove($path = false)
    {
        return $this->adapter->delete($this->resolvePath($path));
    }

    /**
     * Soft remove a given path , soft remove will not actually delete the file.
     * it will only move it to a temporary path
     *
     * @return bool
     */
    public function softRemove()
    {
        return $this->rename($this->getTempPath());
    }

    /**
     * Rename given path to another path.
     * this methods does not accept to alter the path
     * to set the path you should use self::setPath() method
     *
     * @param string $to
     *
     * @return bool
     */
    public function rename($to)
    {
        return $this->adapter->rename($this->resolvePath(), $to);
    }

    /**
     * Move is an alias to self::rename() method.
     *
     * @param string $to
     *
     * @return bool
     */
    public function move($to)
    {
        return $this->rename($to);
    }

    /**
     * Move a file from a resolved path to another resolved path.
     * this methods does not accept to alter the path
     * to set the path you should use self::setPath() method
     *
     * @param string $to
     *
     * @return bool
     */
    public function moveTo($to)
    {
        return $this->adapter->rename($this->resolvePath(), $this->resolvePath($to));
    }

    /**
     * Move a file from a resolved path to another resolved path.
     * this methods does not accept to alter the path
     * to set the path you should use self::setPath() method
     *
     * @param string $from
     * @param string $to
     *
     * @return bool
     */
    public function moveFromTo($from, $to)
    {
        return $this->adapter->rename($this->resolvePath($from), $this->resolvePath($to));
    }

    /**
     * Duplicate a file in the same path with a new generated file.
     *
     * @param string $newname
     *
     * @return self
     */
    public function duplicate($newname = null)
    {
        $path = $this->resolvePath();
        $basename = $path->getBasename();
        $newCopy = $path->getDir() . ($newname ?: $basename . '_' . date());

        return $this->copy($this->resolvePath(), $newCopy);
    }

    /**
     * Copy file / directory from an old path to the new path.
     *
     * @param string $from
     * @param string $to
     *
     * @return self
     */
    public function copyFrom($from, $to)
    {
        $this->adapter->copy($this->resolvePath($from), $this->resolvePath($to));

        return $this;
    }

    /**
     * Copy the current file path to a new path.
     * this method relies on setPath method.
     *
     * @param string $to
     *
     * @return self
     */
    public function copy($to)
    {
        $this->adapter->copy($this->resolvePath(false), $to);

        return $this;
    }

    /**
     * Copy directory recursively with it's contents from a path to path
     * this method will try to create the new path directory before
     * start copying files
     *
     * @param string $from
     * @param string $to
     *
     * @return void
     * @throws \Exception
     */
    public function copyDirectoryFrom($from, $to)
    {
        $path = $this->resolvePath($from);

        if (!$this->isDirectory()) {
            throw new \Exception('Provided path is a file');
        }

        $this->createDir($to);
        $objects = $this->listRecursively($path);

        foreach ($objects as $object) {
            $newPath = str_replace(trim($path, '/'), $to, $object['path']);
            if ($object['type'] == 'dir') {
                $this->createDir($newPath);
            } else {
                // $this->setPathPrefix($object['path'])->copy($newPath);
                $this->copyFrom('/'.$object['path'], $newPath);
            }
        }
    }

    /**
     * Copy directory recursively with it's contents
     * this method will create a new folder if folder is not exists
     * this method rely on setPath method so you MUST set the path before
     * call it
     *
     * @param string $to
     *
     * @return void
     * @throws \Exception
     */
    public function copyDirectory($to)
    {
        $path = $this->resolvePath(false);

        if (!$this->isDirectory()) {
            throw new \Exception('Provided path is a file');
        }

        $this->createDir($to);
        $objects = $this->listRecursively($path);

        foreach ($objects as $object) {
            $newPath = str_replace($path, $to, $object['path']);
            if ($object['type'] == 'dir') {
                $this->createDir($newPath);
            } else {
                $this->setPath($object['path'])->copy($newPath);
            }
        }
    }

    /**
     * Clear a given file contents.
     * this method relies on self::setPath() method.
     * path can not be altered
     *
     * @param string $newname
     *
     * @return bool
     */
    public function empty()
    {
        return $this->put('');
    }

    /**
     * Check if a given path is exists.
     * note that this method is not applicaple in some adapters
     * like gcs when you are trying to check for directories.
     * to check if directory is exists in gcs use self::list() instead.
     *
     * @param string $path
     *
     * @return bool
     */
    public function isExists($path = false)
    {
        return $this->adapter->has($this->resolvePath($path));
    }

    /**
     * Check if a given directory path is exists.
     * for specific implementations reasons , GCS does not consider directories
     * so we need to check if directory has contents ( list ) to make sure
     * that it is exists
     *
     * @param string $path
     *
     * @return bool
     */
    public function isDirExists($path = false)
    {
        if ($this->adapterNamespace == 'gcs') {
            $list = $this->list($path);
            return is_array($list) && count($list) > 0;
        }

        return $this->adapter->has($this->resolvePath($path));
    }

    /**
     * Check if the given path is a valid file.
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->splfile->isFile();
    }

    /**
     * Read contents of a given file.
     *
     * @param string $path
     *
     * @return string
     */
    public function read($path = false) : string
    {
        return $this->adapter->read($this->resolvePath($path));
    }

    /**
     * Get the size of a given path.
     *
     * @param string $path
     *
     * @return int
     */
    public function getSize($path = false)
    {
        try {
            return $this->adapter->getSize($this->resolvePath($path));
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Upload a stream file to a the given path in self::setPath().
     * the resource should be "streamable" resource
     *
     * @param mixed $stream
     *
     * @return bool
     */
    public function upload($stream)
    {
        if (!is_resource($stream) && is_string($stream)) {
            $stream = fopen($stream, 'r+');
        }

        $this->adapter->putStream($this->resolvePath(), $stream);

        $this->path['size'] = 0;
        if (is_resource($stream)) {
            $stat = fstat($stream);
            $this->path['size'] = $stat['size'];
            fclose($stream);
        }

        return $this->path;
    }

    /**
     * Upload the file to a specific path
     * this will override the already given path.
     *
     * @param string $to
     * @param string $stream
     *
     * @return bool
     */
    public function uploadTo($to, $stream)
    {
        $to = ($to[0] == '/' ? $to : $this->resolvePath($to));
        $this->adapter->put($to, $stream);

        return $this->path;
    }

    public function getFullPath($path = false)
    {
        return $this->base . $this->getPathPrefix() . $path;
    }

    /**
     * Check if a specific path is writable
     *
     * @param string $path
     *
     * @return bool
     */
    public function isWritable($path = false)
    {
        $this->resolvePath($path);

        if ($this->adapterNamespace == 'gcs' && $this->path['type'] == 'dir') {
            return true;
        }

        $perm = $this->adapter->getVisibility($this->resolvePath(false), $perm);

        if ($this->adapterNamespace == 'gcs' && $perm == 'writable') {
            $perm = 'public';
        }

        if ($this->adapterNamespace == 'local' && (in_array($perm, [755, 775]))) {
            $owner = posix_getpwuid(fileowner($this->getFullPath($path)));

            if ($owner['name'] === 'www-data' && $owner['gecos'] === 'www-data') {
                $perm = 'writable';
            }
        }

        return $perm == 0777 || $perm == 'writable';
    }
}
