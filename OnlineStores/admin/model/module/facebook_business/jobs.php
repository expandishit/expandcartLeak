<?php

class Modelmodulefacebookbusinessjobs extends model
{

  /**
   * The queue jobs table name
   *
   * @var string $table_name The queue jobs table name
   */
  private $table_name = 'facebook_business_catalog_jobs';
  private $batches_table_name = 'facebook_business_batches';

  /**
   * Add a job to the queue
   *
   * @param int $catalog_id The catalog ID
   * @param string $operation The job operation type
   * @return int|null Created Job Id
   */
  public function addJob($data): ?int
  {
	  
	 $catalog_id 	 = $data["catalog_id"] ?? "";
	 $operation  	 = $data["operation"]  ?? "import";
	 $operation_type = $data["operation_type"] ?? "queue";
	 $status 		 = $data["status"] ?? "created";
	 $product_count  = $data["product_count"] ?? 0;
	 
	 $query = [];
        $query[] = 'INSERT INTO  `' . DB_PREFIX . $this->table_name .'` SET';
        $query[] = ' user_id  = "' .$this->user->getId() . '",';
        $query[] = ' catalog_id  = "' . (int) $this->db->escape($catalog_id) . '",';
        $query[] = ' status  = "' . $this->db->escape($status) . '",';
        $query[] = ' operation  = "' . $this->db->escape($operation)  . '",';
        $query[] = ' operation_type  = "' .  $this->db->escape($operation_type) . '",';
        $query[] = ' product_count  = "' . (int) $product_count . '",';
        
		if($operation_type=='runtime'){
			$query[] = ' finished_at  = NOW(),';
		}
		
        $query[] = ' created_at  = NOW()';

    $this->db->query(implode(' ', $query));
    return $this->db->getLastId();
  }

  /**
   *
   * Get the job by ID
   *
   * @param int $job_id
   * @return array|null
   *
   */
  public function getJob($job_id): ?array
  {
    $job_id = (int) $this->db->escape($job_id);

    $sql = 'SELECT * FROM `' . DB_PREFIX . $this->table_name . '` WHERE `job_id` =' . $job_id . ' LIMIT 1';
    
	$job = $this->db->query($sql)->row;

    //$job['count'] = $this->db->query('SELECT count(*) as count FROM `'.DB_PREFIX.'facebook_business_catalog_queue_product` WHERE job_id=' . $job_id)->row['count'];
	$job['count'] =	0;
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

    $sql = "SELECT * FROM  `". DB_PREFIX . $this->table_name."`";
    $sql .= " WHERE catalog_id=".$catalog_id;
    $sql .= " ORDER BY created_at DESC LIMIT 1";

    $job = $this->db->query($sql)->row;

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
    $sql = "UPDATE `". DB_PREFIX . $this->table_name ."` SET";
    foreach ($data as $key => $val) {
      $sql .= " $key='" . $this->db->escape($val) . "',";
    }
    $sql .= " updated_at=NOW() WHERE `job_id`=$job_id LIMIT 1";

		$q = $this->db->query($sql);
    return $q ? true : false;
  }

  /**
   * Get All queue jobs
   *
   * @param int $limit
   * @return array|null
   */
  public function getJobs($start = 0,$limit = 50 , $options = []): ?array
  {
	  
    $sql = 'SELECT j.*,b.fb_status,b.errors_total_count,b.products_total_count FROM `' . DB_PREFIX . $this->table_name . '` j 
			LEFT JOIN `' . DB_PREFIX . $this->batches_table_name . '` b  on j.job_id = b.job_id';
	
	$sort_options = array('j.job_id');
	
	$order= '';
	if(isset($options["sort"]) && in_array($options["sort"],$sort_options)){
		
		$order_type = $options["sort_type"] ?? "ASC";
		$order = ' ORDER BY ' . $options["sort"] . ' '.$order_type ;
		
	}
	$sql .=	$order ;	
	$sql .= ' LIMIT ' . (int) $limit. ' OFFSET  ' . (int) $start;
   
	$data = $this->db->query($sql)->rows;
	
    $sql = 'SELECT count(*) as total_jobs FROM `' . DB_PREFIX . $this->table_name . '`';

	$total_jobs = $this->db->query($sql)->row;
	$total_jobs = $total_jobs['total_jobs'];
	
    return [
			'data' => $data ,
			'total_jobs' => $total_jobs
			];
  }

  /**
     * @return mixed
     */
  public function resetCurrentJobs()
  {
      $sql = "DELETE  FROM `" . DB_PREFIX . $this->table_name . "` WHERE `status`='processing'"  ;
      return  $this->db->query($sql);
  }

}
