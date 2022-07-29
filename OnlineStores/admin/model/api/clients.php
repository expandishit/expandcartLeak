<?php

class ModelApiClients extends Model
{
    private $clientTable = DB_PREFIX . 'api_clients';

    public function getAllClients()
    {
        $queryString = [];
        $queryString[] = 'SELECT *, DATE_FORMAT(created_at, "%e %M %Y") creation_date FROM `' . $this->clientTable . '` WHERE target NOT IN  ("dropna","knawat" ) OR target IS NULL';

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows > 0) {
            return $data->rows;
        }

        return false;
    }

    public function generateClientId()
    {
        $clientId = [];

        $clientId[] = str_shuffle(STORECODE);

        $clientId[] = substr(time(), -3);

        $clientId[] = bin2hex(openssl_random_pseudo_bytes(2));

        return strtoupper(implode('', $clientId));
    }

    public function generateSecretKey($clientId)
    {

        $encrypt = function ($plaintext, $key) {
            $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
            $iv = openssl_random_pseudo_bytes($ivlen);
            $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
            $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
            $ciphertext = base64_encode($iv . $hmac . $ciphertext_raw);
            return $ciphertext;
        };

        return $encrypt($clientId, STORECODE);
    }

    public function storeClient($clientId, $secretKey, $status)
    {
        $queryString = $fields = [];
        $queryString[] = 'INSERT INTO `' . $this->clientTable . '` SET';
        $fields[] = 'client_id="' . $clientId . '"';
        $fields[] = 'client_secret="' . $secretKey . '"';
        $fields[] = 'client_status="' . $status . '"';
        $queryString[] = implode(',', $fields);

        $this->db->query(implode(' ', $queryString));

        return $this->db->getLastId();
    }

    public function storeCustomClient($clientId, $secretKey, $status, $target)
    {
        $queryString = $fields = [];
        $queryString[] = 'INSERT INTO `' . $this->clientTable . '` SET';
        $fields[] = 'client_id="' . $clientId . '"';
        $fields[] = 'client_secret="' . $secretKey . '"';
        $fields[] = 'target="' . $target . '"';
        $fields[] = 'store_code="' . STORECODE . '"';
        $fields[] = 'client_status="' . $status . '"';
        $queryString[] = implode(',', $fields);

        $this->db->query(implode(' ', $queryString));

        return $this->db->getLastId();
    }

    public function getDropnaClient()
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM `' . $this->clientTable . '`';
        $queryString[] = 'WHERE target="dropna"';
        $queryString[] = 'AND store_code="' . STORECODE. '"';

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows > 0) {
            return $data->row;
        }

        return false;
    }

    public function getClientById($id)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM `' . $this->clientTable . '`';
        $queryString[] = 'WHERE id=' . $id;

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows > 0) {
            return $data->row;
        }

        return false;
    }

    public function getClientByClientId($client_id)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM `' . $this->clientTable . '`';
        $queryString[] = 'WHERE client_id="' . $this->db->escape($client_id).'"';

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows > 0) {
            return $data->row;
        }
        return false;
    }

    public function getClientByTarget($target)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM `' . $this->clientTable . '`';
        $queryString[] = 'WHERE target="'.$target.'"';
        $queryString[] = 'AND store_code="' . STORECODE. '"';

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows > 0) {
            return $data->row;
        }

        return false;
    }

    public function updateStatus($id, $status)
    {
        $queryString = $fields = [];
        $queryString[] = 'UPDATE `' . $this->clientTable . '` SET';
        $fields[] = 'client_status="' . $status . '"';
        $queryString[] = implode(',', $fields);
        $queryString[] = 'WHERE id=' . $id;

        $this->db->query(implode(' ', $queryString));
    }

    public function deleteClient($id)
    {
        $queryString = [];
        $queryString[] = 'DELETE FROM `' . $this->clientTable . '`';
        $queryString[] = 'WHERE id=' . $id;

        $this->db->query(implode(' ', $queryString));
    }

    public function deleteDropnaClient($id)
    {
        $queryString = [];
        $queryString[] = 'DELETE FROM `' . $this->clientTable . '`';
        $queryString[] = 'WHERE id=' . $id;
        $queryString[] = 'AND target=\'dropna\'';

        $this->db->query(implode(' ', $queryString));
    }
}