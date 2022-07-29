<?php

namespace Api\Models;

use Psr\Container\ContainerInterface;

class Guide
{
    /**
     * The container instance
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * The database client instance
     * should be renamed to another meaningful name
     *
     * @var DB
     */
    protected $guideDB;

    /**
     * The parent model constructor
     *
     * @param bool|\Psr\Container\ContainerInterface $container
     *
     * @return void
     */
    public function __construct($container = null)
    {
        if ($container) {
            //$this->setContainer($container);
            $this->guideDB = $container->guidedb;
        }
    }

    public function get($UserName, $Password, $StoreCode, $Route)
    {
        if($this->ValidatePermission($UserName, $Password))
        {
            $queryString = "SELECT guide.* 
                            FROM guide 
                            JOIN storeguide ON guide.Key = storeguide.GuideKey 
                            WHERE storeguide.StoreCode = '$StoreCode' AND storeguide.Route = '$Route' AND storeguide.Status = '1' ORDER BY guide.SortOrder";

            $data = $this->guideDB->query($queryString);

            if ($data->num_rows) {
                foreach ($data->rows as $key => $row) {
                    $data->rows[$key]["Settings"] = json_decode($row["Options"]);
                    unset($data->rows[$key]["Options"]);
                }
                return $data->rows;
            }

            return false;
        }
    }

    public function enable($UserName, $Password, $StoreCode, $GuideKey, $Route)
    {
        if($this->ValidatePermission($UserName, $Password))
        {
            $data = $this->guideDB->query("SELECT * FROM storeguide WHERE StoreCode='$StoreCode' AND GuideKey='$GuideKey' AND Route='$Route'");
            if ($data->num_rows) {
                $this->guideDB->query("UPDATE storeguide SET Status='1' WHERE id=" . $data->row['id']);
            } else {
                $queryString = "INSERT INTO storeguide(`StoreCode`, `GuideKey`, `Route`, `Status`) VALUES('$StoreCode', '$GuideKey', '$Route', '1')";

                $this->guideDB->query($queryString);
            }

            return true;
        }
    }

    public function disable($UserName, $Password, $StoreCode, $GuideKey, $Route)
    {
        if($this->ValidatePermission($UserName, $Password))
        {
            if($GuideKey) {
                $this->guideDB->query("UPDATE storeguide SET Status='0' WHERE StoreCode='$StoreCode' AND GuideKey='$GuideKey' AND Route='$Route'");
            } else {
                $this->guideDB->query("UPDATE storeguide SET Status='0' WHERE StoreCode='$StoreCode' AND Route='$Route'");
            }
            return true;
        }
    }

    private function ValidatePermission($UserName, $Password)
    {
        $data = $this->guideDB->query("SELECT * FROM auth WHERE UserName = '$UserName' AND Password='$Password'");

        if ($data->num_rows) {
            return true;
        }

        return false;
    }
}
