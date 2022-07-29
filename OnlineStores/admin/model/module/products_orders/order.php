<?php

class ModelModuleProductsOrdersOrder extends Model
{
    /**
     * @var string $table
     */
    protected $table = 'products_orders';

    /**
     * Get all products orders.
     *
     * @param array $data
     *
     * @return mixed
     */
    public function getOrders(array $data)
    {
        try {
            $query = [];
            $fields = ['*',"CONCAT(firstname,' ',lastname) as user"];
            $fields = implode(', ', $fields);
            $userJoin = "LEFT JOIN user on ({$this->table}.user_id = user.user_id) ";
            $query[] = sprintf("SELECT %s FROM %s $userJoin", $fields, $this->table);

            $total = $totalFiltered = $this->db->query(str_replace($fields, 'COUNT(*) as dc', implode(' ', $query)))->row['dc'];

            if (isset($data['search'])) {
                $query[] = "where " . $this->table . ".filename like '%" . trim($data['search']) . "%'";
                $countQuery = str_replace($fields, 'COUNT(*) as filterCount', implode(' ', $query));
                $totalFiltered = $this->db->query($countQuery)->row['filterCount'];
            }

            if (isset($data['start']) || isset($data['limit'])) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1) {
                    $data['limit'] = 20;
                }

                $query[] = sprintf("LIMIT %d,%d", (int)$data['start'], (int)$data['limit']);
            }

            $queryData = $this->db->query(implode(' ', $query));

            $data = [
                'data' => $queryData->rows,
                'total' => $total,
                'totalFiltered' => $totalFiltered
            ];
 
            return $data;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function insertProductsOrder($data)
    {
        try {
            $this->db->query(sprintf(
                "INSERT INTO %s (`filename`, `products`,`user_id`)
                VALUES
                ('%s', '%s','%s')",
                $this->table,
                $data['filename'],
                json_encode($data['products']),
                $data['user_id']
            ));

            return $this->db->getLastId();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get all products orders.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function getOrderById(int $id)
    {
        $query = sprintf("SELECT * FROM %s WHERE id = %d", $this->table, $id);

        $queryData = $this->db->query($query);

        if ($queryData->num_rows > 0) {
            return $queryData->row;
        }

        return false;
    }
}
