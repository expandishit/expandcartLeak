<?php

class ModelModuleManualShippingGateways extends Model
{
    /**
     * @var string $table
     */
    protected $table = 'manual_shipping_gateways';

    /**
     * Get a compact manual shipping gateway data including the description(s) by id.
     *
     * @param int $id
     * @param array $opts
     *
     * @return mixed
     */
    public function getCompactShippingGatewayById(int $id, $opts = [])
    {
        $query = $wheres = $columns = [];

        $columns = ['*', 'msg.id as id', 'msgd.id as description_id'];

        $query[] = 'SELECT %s FROM %s msg INNER JOIN %s msgd ON msg.id = msgd.shipping_gateway_id';

        $query[] = sprintf('WHERE msg.id = %d', $id);

        if (isset($opts['language_id'])) {
            $query[] = sprintf('AND msgd.language_id = %d', $opts['language_id']);
        }

        $queryString = vsprintf(implode(' ', $query), [
            implode(',', $columns),
            $this->table,
            "{$this->table}_description",
        ]);

        $data = $this->db->query($queryString);

        if ($data->num_rows > 0) {
            return $data->rows;
        }

        return false;
    }

    /**
     * Get a compact manual shipping gateways data including the description.
     *
     * @param array $opts
     *
     * @return mixed
     */
    public function getCompactShippingGateways($opts = [])
    {
        $query = $wheres = $columns = [];

        $columns = ['*', 'msg.id as id', 'msgd.id as description_id'];

        $query[] = vsprintf('SELECT %s FROM %s msg INNER JOIN %s msgd', [
            implode(',', $columns),
            $this->table,
            "{$this->table}_description",
        ]);

        $query[] = 'ON msg.id = msgd.shipping_gateway_id';

        $wheres[] = sprintf('AND msgd.language_id = %d', $opts['language_id']);

        if (isset($opts['search']) && mb_strlen($opts['search']) > 0) {
            $wheres[] = vsprintf('AND (msgd.title LIKE "%s" OR msgd.description LIKE "%s")', [
                '%' . $opts['search'] . '%',
                '%' . $opts['search'] . '%',
            ]);
        }

        if (isset($opts['status']) && isset($opts['search']) && in_array($opts['search'], [0, 1])) {
            $wheres[] = sprintf('AND msg.status = %d', $opts['status']);
        }

        if (count($wheres)) {
            $query[] = 'WHERE 1 = 1 ' . implode(' ', $wheres);
        }

        if (isset($opts['start']) && $opts['start'] == -1) {
            unset($opts['start']);
        }

        if (isset($opts['limit']) && $opts['limit'] == -1) {
            unset($opts['limit']);
        }

        if (isset($opts['start']) && isset($opts['limit'])) {
           $query[] = sprintf('LIMIT %d, %d', $opts['start'], $opts['limit']);
        }

        $queryString = implode(' ', $query);

        $total = $this->db->query(str_replace($columns, 'COUNT(*) as dc', $queryString))->row['dc'];

        $data = $this->db->query($queryString);

        $results = ['data' => [], 'total' => 0, 'totalFiltered' => 0];

        if ($data->num_rows > 0) {
            $results = [
                'data' => $data->rows,
                'total' => $total,
                'totalFiltered' => $data->num_rows
            ];
        }

        return $results;
    }

    /**
     * Store the manual shipping gateway.
     *
     * @param array $data
     *
     * @return mixed
     */
    public function insertShippingGateway($data = [])
    {
        try {
            $this->db->query(sprintf("INSERT INTO " . DB_PREFIX . "manual_shipping_gateways SET status = 1 , shipping_company_id = " . $data['shipping_company_id'] .", sku_number = ".$data['sku_number']." "));
            return $this->db->getLastId();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Store the manual shipping gateway description.
     *
     * @param int $id
     * @param array $opts
     *
     * @return bool
     */
    public function insertShippingGatewayDescription(int $id, array $data) : bool
    {
        $query = $columns = [];

        $query[] = 'INSERT INTO %s (`title`, `description`, `language_id`, `shipping_gateway_id`) VALUES';

        foreach ($data as $key => $value) {
            $columns[] = vsprintf('("%s", "%s", %d, %d)', [
                $value['title'],
                $value['description'],
                $key,
                $id
            ]);
        }

        $query[] = implode(',', $columns);

        try {
            $this->db->query(sprintf(implode(' ', $query), "{$this->table}_description"));

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * update manual shipping gateway description.
     *
     * @param int $id
     * @param array $opts
     *
     * @return bool
     */
    public function updateShippingGatewayDescription(int $id, array $data) : bool
    {
        $query = $columns = [];
        $query[] = 'UPDATE %s SET';

        $cases = [];
        $cases[] = sprintf('`%s` = (CASE', 'title');
        foreach ($data as $key => $value) {
            $cases[] = sprintf('WHEN language_id="%d" THEN "%s"', $key, $value['title']);
        }
        $cases[] = 'END)';

        $columns[] = implode(' ', $cases);

        $cases = [];
        $cases[] = sprintf('`%s` = (CASE', 'description');
        foreach ($data as $key => $value) {
            $cases[] = sprintf('WHEN language_id="%d" THEN "%s"', $key, $value['description']);
        }
        $cases[] = 'END)';

        $columns[] = implode(' ', $cases);
        $query[] = implode(', ', $columns);

        $query[] = sprintf('WHERE shipping_gateway_id = "%d"', $id);

        try {
            $this->db->query(sprintf(implode(' ', $query), "{$this->table}_description"));

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    public function updateShippingGateway(int $id, array $data) : bool
    {
        $query = $columns = [];
        $query[] = 'UPDATE %s SET';

        $cases = [];
        $cases[] = sprintf("%s =",'sku_number');
        $cases[] = sprintf('"%s"', $data['sku_number']);
        $columns[] = implode(' ', $cases);
        $cases = [];
        $cases[] = sprintf("%s =",'shipping_company_id');
        $cases[] = sprintf('"%s"', $data['shipping_company_id']);
        $columns[] = implode(' ', $cases);
        $query[] = implode(', ', $columns);

        $query[] = sprintf('WHERE id = "%d"', $id);

        try {
            $this->db->query(sprintf(implode(' ', $query), $this->table));

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
  

    /**
     * delete manual shipping gateway with it's description.
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteShippingGateway(int $id) : bool
    {
        try {
            $this->db->query(sprintf('DELETE FROM %s WHERE id = %d', $this->table, $id));

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
