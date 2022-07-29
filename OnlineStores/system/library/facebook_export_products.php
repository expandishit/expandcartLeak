<?php
require(DIR_SYSTEM."../vendor/autoload.php");

class facebookExportProducts
{
  private $model_facebook_import;
  private $controller_facebook_import;
  private $errors;
  private $error;

  function __construct($registry)
  {

    $admin_app_dir = str_replace('system/', 'admin/', DIR_SYSTEM);

    require_once $admin_app_dir . "model/module/facebook_import/facebook_import.php";
    require_once $admin_app_dir . "controller/module/facebook_import.php";
    $this->model_facebook_import = new Modelmodulefacebookimportfacebookimport($registry);
    $this->controller_facebook_import = new ControllerModuleFacebookImport($registry);
  }

  public function export($job_id,$token,$http_catalog)
  {
    $this->controller_facebook_import->exportAllProductsQueueJob($job_id,$token,$http_catalog);
  }
}
