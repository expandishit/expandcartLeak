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
        $data = $this->db->query(vsprintf('SELECT * FROM %s WHERE id = %d', [
            $this->table,
            $id,
        ]));

        if ($data->num_rows > 0) {
            return $data->row;
        }

        return false;
    }

    /**
     * Download a file.
     *
     * @param string $filePath
     *
     * @return mixed
     */
    public function download(string $filePath)
    {
        $downloadTo = TEMP_DIR_PATH;

        $filePath = str_replace('https://storage.googleapis.com/onlinestores/', '', $filePath);

        try {
            $storageClient = new StorageClient([
                'projectId' => FS['project_id'],
                'keyFilePath' => FS['key'],
            ]);

            $bucket = $storageClient->bucket('onlinestores');

            $adapter = new GoogleStorageAdapter($storageClient, $bucket);

            $filesystem = new Filesystem($adapter, ['visibility' => 'public']);

            header('Content-Type: ' . $filesystem->getMimetype($filePath));
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . $filesystem->getSize($filePath));

            return [
                'contents' => $filesystem->read($filePath),
                'type' => $filesystem->getMimetype($filePath),
                'name' => basename($filePath)
            ];
        } catch (\Exception $e) {
            dd($e->getMessage());
            return false;
        }
    }

    /**
     * Update download counters.
     *
     * @param int $id
     * @param array $count
     *
     * @return bool
     */
    public function updateDownloadCountBySessionId(int $id, array $count) : bool
    {
        try {
            $this->db->query(vsprintf("UPDATE %s SET download_count = '%s' WHERE id = %d", [
                'app_online_courses_orders',
                json_encode($count, JSON_UNESCAPED_UNICODE),
                $id
            ]));

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get course order by course order id.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function getCourseOrderByOrderId(int $id)
    {
        $data = $this->db->query(vsprintf('SELECT * FROM %s WHERE id = %d', [
            'app_online_courses_orders',
            $id
        ]));

        if ($data->num_rows > 0) {
            return $data->row;
        }

        return false;
    }

    /**
     * Get course by customer id.
     *
     * @param int $customerId
     *
     * @return mixed
     */
    public function getCoursesByCustomerId(int $customerId)
    {
        $query = [];

        $fields = [
            'op.product_id',
            'op.name',
            'o.order_id',
            'o.date_added',
            'oco.id as course_order_id'
        ];

        $query[] = 'SELECT %s FROM `%s` o INNER JOIN `%s` op ON o.order_id = op.order_id';
        $query[] = 'INNER JOIN %s oco ON op.order_product_id = oco.order_product_id';
        $query[] = 'WHERE o.customer_id = %d';

        $queryString = vsprintf(implode(' ', $query), [
            implode(',', $fields),
            'order',
            'order_product',
            'app_online_courses_orders',
            $customerId,
        ]);

        $data = $this->db->query($queryString);

        if ($data->num_rows > 0) {
            return $data->rows;
        }

        return false;
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

    /**
     * List available courses by product ids.
     *
     * @param array $ids
     *
     * @return mixed
     */
    public function getCoursesByProductIds($ids)
    {
        if (!is_array($ids) || count($ids) < 1) {
            return false;
        }

        $data = $this->db->query(vsprintf('SELECT * FROM %s WHERE product_id IN (%s)', [
            $this->table,
            implode(',', $ids)
        ]));

        if ($data->num_rows > 0) {
            return $data->rows;
        }

        return false;
    }

    /**
     * Get order prodcuts by order id.
     *
     * @param int $order_id
     *
     * @return array
     */
    public function getOrderProducts($order_id)
    {
        return $this->db->query("SELECT `order_product_id`, `product_id`, `name`, `price`, `quantity` FROM `" . DB_PREFIX . "order_product` WHERE order_id='{$order_id}'")->rows;
    }

    /**
     * Get order prodcuts by order product id.
     *
     * @param int $order_id
     *
     * @return array
     */
    public function getOrderByOrderProductId($orderProductId)
    {
        $query = [];
        $query[] = 'SELECT * FROM `order` o INNER JOIN order_product op';
        $query[] = 'ON o.order_id = op.order_id WHERE op.order_product_id = %d';

        $data = $this->db->query(sprintf(implode(' ', $query), $orderProductId));

        if ($data->num_rows > 0) {
            return $data->row;
        }

        return false;
    }

    /**
     * Factory method to insert an order
     * this method :
     * - check for status
     * - get order products
     * - refactor order products and extract products ids
     * - insert into app_online_courses_orders table where order product has course
     *
     * @param array $data
     *
     * @return bool
     */
    public function insertOrder(array $data) : bool
    {
        $onlineCourses = $this->config->get('online_courses');

        if ($onlineCourses['status'] != 1) {
            return false;
        }

        $this->load->model('checkout/order');
        $originalOrderProducts = $this->getOrderProducts($data['order_id']);

        $productIds = $orderProducts = [];
        foreach ($originalOrderProducts as $orderProduct) {
            $productIds[] = $orderProduct['product_id'];
            $orderProducts[$orderProduct['product_id']] = $orderProduct;
        }

        $coursesProducts = $this->getCoursesByProductIds($productIds);

        unset($productIds);

        $query = $columns = [];

        $query[] = 'INSERT INTO %s (`order_product_id`, `customer_id`) VALUES';

        foreach ($coursesProducts as $coursesProduct) {
            if (!isset($products[$coursesProduct['product_id']])) {
                $products[$coursesProduct['product_id']] = $coursesProduct;
                $columns[] = vsprintf('(%d, %d)', [
                    $orderProducts[$coursesProduct['product_id']]['order_product_id'],
                    $data['customer_id'],
                ]);
            }
        }

        $query[] = implode(',', $columns);

        try {
            $this->db->query(vsprintf(implode(' ', $query), ['app_online_courses_orders']));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
