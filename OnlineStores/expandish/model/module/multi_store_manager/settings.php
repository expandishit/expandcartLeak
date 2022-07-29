<?php

/**
 * Multistore App Settings Module
 * 
 * @author MohamedHassanWD <hassan.mohamed.sf@gmail.com>
 */
class ModelModuleMultiStoreManagerSettings extends Model
{
  /**
   * Return all active stores 
   *
   * @return array|null
   */
  public function getStores()
  {
    //Get all stores from ecusersdb
    $sql = 'SELECT * FROM PaidUsers pu WHERE pu.USERID = ' . WHMCS_USER_ID . ' GROUP BY `STORECODE` ';
    $query = $this->ecusersdb->query($sql);

    return $query->rows;
  }
}
