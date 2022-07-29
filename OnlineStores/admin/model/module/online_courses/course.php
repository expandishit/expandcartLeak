<?php

use Google\Cloud\Storage\StorageClient;
use League\Flysystem\Filesystem;
use Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter;

use ExpandCart\Foundation\String\Slugify;

class ModelModuleOnlineCoursesCourse extends Model
{
    /**
     * @var string $table
     */
    protected $table = 'app_online_courses';

    /**
     * Get lesson by course id.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function getLessonById($id)
    {
        $data = $this->db->query(vsprintf('SELECT * FROM %s WHERE id = %d AND parent_id = 0', [
            $this->table,
            $id
        ]));

        if ($data->num_rows > 0) {
            return $data->row;
        }

        return false;
    }

    /**
     * Get lesson by product id.
     *
     * @param int $productId
     *
     * @return mixed
     */
    public function getLessonByProductId($productId)
    {
        $data = $this->db->query(vsprintf('SELECT * FROM %s WHERE product_id = %d AND parent_id = 0', [
            $this->table,
            $productId
        ]));

        if ($data->num_rows > 0) {
            return $data->row;
        }

        return false;
    }

    /**
     * Get session by course id.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function getSessionById($id)
    {
        $data = $this->db->query(vsprintf('SELECT * FROM %s WHERE id = %d AND parent_id > 0', [
            $this->table,
            $id
        ]));

        if ($data->num_rows > 0) {
            return $data->row;
        }

        return false;
    }

    /**
     * Update lesson by course id.
     *
     * @param int $id
     * @param array $data
     *
     * @return bool
     */
    public function updateLessonById(int $id, array $data) : bool
    {
        try {
            $this->db->query(vsprintf('UPDATE %s SET title = "%s" WHERE id = %d', [
                $this->table,
                $data['title'],
                $id,
            ]));

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update session by course id.
     *
     * @param int $id
     * @param array $data
     *
     * @return bool
     */
    public function updateSessionById(int $id, array $data) : bool
    {
        try {
            $this->db->query(vsprintf("UPDATE %s SET title = '%s', download_count = %d WHERE id = %d", [
                $this->table,
                json_encode($data['session_title'], JSON_UNESCAPED_UNICODE),
                $data['download_count'],
                $id,
            ]));

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update course expiration by id.
     *
     * @param int $id
     * @param string $expiration
     *
     * @return bool
     */
    public function updateExpirationByProductId(int $productId, string $expiration) : bool
    {
        try {
            $this->db->query(vsprintf("UPDATE %s SET expiration_period = '%s' WHERE product_id = %d", [
                $this->table,
                $expiration,
                $productId,
            ]));

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Factory method to upload file to google cloud.
     *
     * @param string $fileName
     * @param int $productId
     * @param string $contents
     *
     * @return mixed
     */
    public function upload(string $fileName, int $productId, string $contents)
    {
        $baseDir = rtrim(STORECODE, '/');
        $fileName = basename($fileName);
        $fileInfo = pathinfo($fileName);
        $filePath = vsprintf('%s/courses/%d/%s_%s.%s', [
            $baseDir, $productId, time(), (new Slugify)->slug($fileInfo['filename']), $fileInfo['extension']
        ]);

        try {
            $storageClient = new StorageClient([
                'projectId' => FS['project_id'],
                'keyFilePath' => FS['key'],
            ]);

            $bucket = $storageClient->bucket('onlinestores');

            $adapter = new GoogleStorageAdapter($storageClient, $bucket);

            $filesystem = new Filesystem($adapter, ['visibility' => 'public']);

            $filesystem->put($filePath, $contents);
        } catch (\Exception $e) {
            return false;
        }

        try {
            return $filesystem->getUrl($filePath);
        } catch (\Exception $e) {
            return 'https://storage.googleapis.com/onlinestores/' . $filePath;
        }
    }

    /**
     * Insert lesson to courses.
     *
     * @param array $data
     *
     * @return mixed
     */
    public function insertCourseLesson(array $data)
    {
        $query = $columns = [];

        $query[] = 'INSERT INTO %s (`title`, `product_id`) VALUES';

        $columns[] = vsprintf('("%s", %d)', [
            $data['title'],
            $data['product_id']
        ]);

        $query[] = implode(',', $columns);

        try {
            $this->db->query(sprintf(implode(' ', $query), $this->table));

            return $this->db->getLastId();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Insert session to courses.
     *
     * @param array $data
     *
     * @return mixed
     */
    public function insertCourseSession(array $data)
    {
        $query = $columns = [];

        $query[] = 'INSERT INTO %s (`title`, `parent_id`, `file_path`, `product_id`, `download_count`) VALUES';

        $columns[] = vsprintf("('%s', %d, '%s', %d, %d)", [
            json_encode($data['session_title'], JSON_UNESCAPED_UNICODE),
            $data['lesson_id'],
            $data['file_path'],
            $data['product_id'],
            $data['download_count'],
        ]);

        $query[] = implode(',', $columns);

        try {
            $this->db->query(sprintf(implode(' ', $query), $this->table));

            return $this->db->getLastId();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get compact courses as an array contains lessons and it's related sessions.
     *
     * @param int $productId
     *
     * @return mixed
     */
    public function getCompactCourseByProductId(int $productId)
    {
        $query = [];

        $fields = [
            'l.id as lesson_id',
            'l.product_id',
            'l.title lesson_title',
            'l.expiration_period',
            's.id as session_id',
            's.title as session_title',
            's.file_path as session_file_path',
            's.download_count as session_download_count',
        ];

        $query[] = 'SELECT %s FROM %s l LEFT JOIN %s s ON l.id=s.parent_id WHERE l.parent_id = 0 AND l.product_id = %d';
        $query[] = 'ORDER BY l.id';

        $query = vsprintf(implode(' ', $query), [
            implode(' , ', $fields),
            $this->table,
            $this->table,
            $productId
        ]);

        $data = $this->db->query($query);

        if ($data->num_rows > 0) {
            return $this->resolveCourses($data->rows);
        }

        return false;
    }

    /**
     * Helper method to refactor courses array into tree-view like array.
     *
     * @param data $rows
     *
     * @return array
     */
    public function resolveCourses(array $rows) : array
    {
        $lessons = [];

        foreach ($rows as $key => $row) {
            if (isset($lessons[$row['lesson_id']]) == false) {
                $lessons[$row['lesson_id']] = [
                    'id' => $row['lesson_id'],
                    'title' => $row['lesson_title'],
                    'expiration_period' => $row['expiration_period'],
                ];
            }

            if ($row['session_id']) {
                $lessons[$row['lesson_id']]['sessions'][$row['session_id']] = [
                    'id' => $row['session_id'],
                    'title' => json_decode($row['session_title'], true),
                    'file' => $row['session_file_path'],
                    'file_string' => basename($row['session_file_path']),
                    'product_id' => $row['product_id'],
                    'download_count' => $row['session_download_count'],
                ];
            }
        }

        return $lessons;
    }
}
