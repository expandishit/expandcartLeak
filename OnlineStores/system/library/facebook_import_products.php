<?php
require(DIR_SYSTEM."../vendor/autoload.php");

class facebookImportProducts
{
  private $model_facebook_import;

  private $controller_facebook_import;

  function __construct($registry)
  {

    $admin_app_dir = str_replace('system/', 'admin/', DIR_SYSTEM);

    require_once $admin_app_dir . "model/module/facebook_import/facebook_import.php";
    require_once $admin_app_dir . "controller/module/facebook_import.php";
    $this->model_facebook_import = new Modelmodulefacebookimportfacebookimport($registry);
    $this->controller_facebook_import = new ControllerModuleFacebookImport($registry);
  }

    /**
     * @param $job_id
     * @param string $token
     */
  public function import($job_id, string $token): void
  {
      $this->controller_facebook_import->importAllProductsQueueJob($job_id,$token);
  }
}
