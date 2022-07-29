<?php

/**
 * Facebook catalog Import/Export Queue Model
 * 
 * @author MohamedHassanWD <hassan.mohamed.sf@gmail.com>
 * @copyright 2020 ExpandCart
 */
class Modelmodulefacebookimportfacebookcatalogqueue extends model
{

  /**
   * The queue jobs table name
   *
   * @var string $table_name The queue jobs table name
   */
  private $table_name = 'facebook_catalog_queue_jobs';

  /**
   * Add a job to the queue
   *
   * @param int $catalog_id The catalog ID
   * @param string $operation The job operation type
   * @return int|null Created Job Id
   */
  public function addJob($catalog_id,$operation="import"): ?int
  {
    $sql = "
      INSERT INTO " . DB_PREFIX . $this->table_name . " (`user_id`,`catalog_id`,`status`,`created_at`,`operation`) 
      VALUES ('{$this->user->getId()}','" . (int) $this->db->escape($catalog_id) . "','created',NOW(),'".(string) $this->db->escape($operation)."')";

    $this->db->query($sql);
    return $this->db->getLastId();
  }

  /**
   * Get the job by ID
   *
   * @param int $job_id
   * @return array|null
   */
  public function getJob($job_id): ?array
  {
    $job_id = (int) $this->db->escape($job_id);

    $sql = 'SELECT * FROM ' . DB_PREFIX . $this->table_name . ' WHERE job_id=' . $job_id . ' LIMIT 1';
    $job = $this->db->query($sql)->row;

    $job['count'] = $this->db->query('SELECT count(*) as count FROM facebook_catalog_queue_product WHERE job_id=' . $job_id)->row['count'];

    return $job;
  }

  /**
   * Get a job with catalog id
   *
   * @param int $catalog_id Catalog id to get it by
   * @return array|null
   */
  public function getLatestJobByCatalogId($catalog_id): ?array
  {
    $catalog_id = (int) $this->db->escape($catalog_id);

    $sql = "SELECT * FROM " . DB_PREFIX . $this->table_name;
    $sql .= " WHERE catalog_id=".$catalog_id;
    $sql .= " ORDER BY created_at DESC LIMIT 1";

    $job = $this->db->query($sql)->row;

    return $job;
  }

  /**
   * Get the job by ID with product IDS
   *
   * @param int $job_id
   * @param string $id_column The type of id expandcart|facebook
   * @return array|null The job or null
   */
  public function getJobWithProductIds($job_id, $id_column = 'facebook'): ?array
  {
    $job_id = (int) $this->db->escape($job_id);
    $id_column = in_array($id_column, ['facebook', 'expandcart']) ? $id_column : 'facebook';

    $sql = 'SELECT * FROM ' . DB_PREFIX . $this->table_name . ' WHERE job_id=' . $job_id . ' LIMIT 1';
    $job = $this->db->query($sql)->row;

    $job['products'] = $this->db->query("SELECT " . $id_column . "_product_id FROM " . DB_PREFIX . "facebook_catalog_queue_product WHERE job_id=" . $job_id)->rows;

    return $job;
  }

  /**
   * Update a job
   *
   * @param int $job_id The job id to update
   * @param array $data The fields to update
   * @return bool
   */
  public function updateJob($job_id, $data): bool
  {
    $cols = implode(',', array_keys($data));
    $vals = implode(',', array_values($data));

    $sql = "UPDATE $this->table_name SET";
    foreach ($data as $key => $val) {
      $sql .= " $key='" . $this->db->escape($val) . "',";
    }
    $sql .= " updated_at=NOW() WHERE job_id=$job_id LIMIT 1";

    $q = $this->db->query($sql);
    return $q ? true : false;
  }

  /**
   * Get All queue jobs
   *
   * @param int $limit
   * @return array|null
   */
  public function getJobs($limit = 50): ?array
  {
    $sql = 'SELECT * FROM ' . DB_PREFIX . $this->table_name . ' LIMIT ' . (int) $limit;

    return $this->db->query($sql)->rows;
  }

    /**
     * @return mixed
     */
    public function resetCurrentJobs(){
      $sql = "DELETE  FROM " . DB_PREFIX . $this->table_name . " WHERE `status` IN ('processing', 'created')"  ;
      return  $this->db->query($sql);
  }
}
