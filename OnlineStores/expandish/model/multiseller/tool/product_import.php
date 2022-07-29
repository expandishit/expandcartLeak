<?php

class ModelMultisellerToolProductImport extends Model
{
    private $import_files_tb = DB_PREFIX . 'import_files';

    public function checkImportProcessStatus()
    {

        date_default_timezone_set($this->config->get('config_timezone') ? $this->config->get('config_timezone') : 'UTC');
        # code...
        $sql = [];
        $sql[] = 'SELECT * from ' . $this->import_files_tb;
        $sql[] = 'WHERE 1=1';
        $sql[] = 'ORDER BY id desc LIMIT 1';

        $data = $this->db->query(implode(' ', $sql));

        if ($data->row) {
            $dateSaved = strtotime($data->row['import_date']);
            $dateNow = strtotime(date('Y-m-d G:i:s'));
            $interval  = abs($dateNow - $dateSaved);
            $minutes   = round($interval / 60);

            if (DEV_MODE) { // For Testing Purpose
                $log = new Log("importingTime.log");
                if (date_default_timezone_get()) {
                    $log->write('date.timezone: ' . date_default_timezone_get());
                }
                $log->write('BEFORE CONVERTING DATA -> Saved:' . $data->row['import_date'] . ' Date Now:' . date('Y-m-d H:i:s'));
                $log->write('AFTER CONVERTING DATA -> Saved:' . $dateSaved . ' Date Now:' . $dateNow . ' Diff. in minutes is: ' . $minutes);
            }

            if ($minutes < 60) {
                return $data->row;
            }
        }

        return array();
    }

    public function add_import_file($data)
    {
        $sql = [];
        $sql[] = 'INSERT INTO ' . $this->import_files_tb;
        $sql[] = '(`filename`, `import_type`, `import_date`, `file_mapping`, `import_status`, `message`, `error`, `number_of_records`)';
        $sql[] = "VALUES ('" . $data['filename'] . "','" . $data['import_type'] . "','"
            . $data['import_date'] . "','" . $data['file_mapping'] . "'," . $data['import_status'] . ",null,null,null)";

        $this->db->query(implode(' ', $sql));
        return $this->db->getLastId();
    }
}
