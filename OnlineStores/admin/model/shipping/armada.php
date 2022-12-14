<?php

class ModelShippingArmada extends Model
{
    
    public function install()
    {
        $this->addAramdaAreas();
        $this->createArmadaShippingCostTable(); 
    }
    
    public function uninstall()
    {
         $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "armada_shipping_cost`;");
    }        

    private function createArmadaShippingCostTable()
    {
	    $query = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "armada_shipping_cost` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `area_id` int(11) NOT NULL,
                    `price` float NOT NULL,
                     PRIMARY KEY (`id`)
	      ) ENGINE=InnoDB DEFAULT COLLATE=utf8_general_ci;";
            
            $this->db->query($query);  
    }
    
    public function deleteArmadaShippingCost() {
        //in case customer wants to update shipping cost we remove all costs and insert them again

        $this->db->query('DELETE FROM' . DB_PREFIX . ' armada_shipping_cost');
    }

    public function addArmadaShippingCost($data)
    {
        $query = $fields = [];

        $query[] = 'INSERT INTO  ' . DB_PREFIX . 'armada_shipping_cost SET';
        $fields[] = 'area_id="' . $data['area_id'] . '"';
        $fields[] = 'price="' . $data['price'] . '"';
        $query[] = implode(', ', $fields);
        $this->db->query(implode(' ', $query));
    }
    
    public function addShipmentDetails($orderId, $details, $status)
    {
        $query = $fields = [];

        $query[] = 'INSERT INTO  ' . DB_PREFIX . 'shipments_details SET';
        $fields[] = 'order_id="' . $orderId . '"';
        $fields[] = 'details=\' '. json_encode($details) .' \'';
        $fields[] = 'shipment_status="' . $status . '"';
        $fields[] = 'shipment_operator="armada"';
        $query[] = implode(', ', $fields);
        $this->db->query(implode(' ', $query));
    }

    public function getShipmentDetails($orderId)
    {
        $result = $this->db->query("SELECT * FROM  " . DB_PREFIX . "shipments_details where `order_id` = $orderId AND `shipment_operator` = 'armada' ")->row;
        if (!empty($result)) {
            return json_decode($result['details'], true);
        }
        return [];
    }

    public function deleteShipment($orderId)
    {
        $this->db->query("DELETE FROM  " . DB_PREFIX . "shipments_details where `order_id` = $orderId AND `shipment_operator` = 'armada' ");
    }
    
    public function addAramdaAreas()
    {
        //this function is using for add areas and mapping them with zones in case areas is empty

        $countryId = $this->db->query("SELECT country_id FROM " . DB_PREFIX . " `country` WHERE iso_code_2 = 'KW'")->row['country_id'];

        $checkIfAreaExists = $this->db->query("SELECT area_id FROM " . DB_PREFIX . " `geo_area` WHERE country_id = '$countryId'")->rows;

        if (empty($checkIfAreaExists)) {
            
            //Al 'Asimah does not exists in new data so will insert it in case not inserted
            $asimahId = $this->db->query("SELECT zone_id FROM " . DB_PREFIX . " `zone` WHERE zone_id = '1788'")->row['zone_id'];
            if (empty($asimahId)) {
                $zoneName = $this->db->escape("Al 'Asimah");
                $query = "INSERT INTO " . DB_PREFIX . " `zone` (country_id,name,code,status) VALUES ('$countryId',$zoneName,'AL','1')";
                $this->db->query($query);
                $asimahId = $this->db->getLastId();
                $queryEn = "INSERT INTO " . DB_PREFIX . " `zones_locale` (zone_id,country_id,name,lang_id) VALUES ($asimahId,'$countryId'," . "'Al 'Asimah'," . "'1')";
                $this->db->query($queryEn);
                $queryAr = "INSERT INTO " . DB_PREFIX . " `zones_locale` (zone_id,country_id,name,lang_id) VALUES ($asimahId,'$countryId'," . "'??????????????'," . "'2')";
                $this->db->query($queryAr);
            }
            
            /*
             * here we check if kuwait areas are empty we starting to add areas and mapping with zones
             * first thing zones array has 2 keys [
             * old : using for old stores that using old zones before update zones in 2020
             * new : this for new stores that update zones data
             * ]
             * 
             */

            $zonesIdsArray = [
                //Ahmadi                
                ['old' => 1789, 'new' => 7315],
                //Farwaniya
                ['old' => 1790, 'new' => 7345],
                //Hawalli
                ['old' => 1792, 'new' => 7353],
                //Jahra
                ['old' => 1791, 'new' => 7360],
                //Mubarak al Kabeer
                ['old' => 7213, 'new' => 7379],
                //Al 'Asimah does not exists in new data so will insert it
                ['old' => 1788, 'new' => $asimahId]
            ];
            
            $zoneAreasData = $this->kuwaitAreas();
            
            foreach ($zonesIdsArray as $zonesIds) {
                $checkZoneId = $this->db->query("SELECT zone_id FROM " . DB_PREFIX . " `zone` WHERE zone_id = '$zonesIds[old]'")->row['zone_id'];
                $areaZoneId = (!empty($checkZoneId)) ? $checkZoneId : $zonesIds['new'];
                $zoneAreas = $zoneAreasData[$zonesIds['old']];
                foreach ($zoneAreas as $zoneArea) {
                    $areaName = $this->db->escape($zoneArea['name']);
                    if ($zoneArea['lang_id'] == 1) {
                        $queryArea = "INSERT INTO " . DB_PREFIX . " `geo_area` (zone_id,country_id,name,status) VALUES ('$areaZoneId','$countryId','$areaName','1')";
                        $this->db->query($queryArea);
                        $insertedAreaId = $this->db->getLastId();
                        $queryAreaEn = "INSERT INTO " . DB_PREFIX . " `geo_area_locale` (area_id,name,lang_id) VALUES ('$insertedAreaId','$areaName','1')";
                        $this->db->query($queryAreaEn);
                        $this->session->data['lastInsertedArea'] = $insertedAreaId;
                    } else if ($zoneArea['lang_id'] == 2) {
                        $insertedAreaId = $this->session->data['lastInsertedArea'];
                        $queryAreaAr = "INSERT INTO " . DB_PREFIX . " `geo_area_locale` (area_id,name,lang_id) VALUES ('$insertedAreaId','$areaName','2')";
                        $this->db->query($queryAreaAr);
                    }
                }
            }
            unset($this->session->data['lastInsertedArea']);
        }
    }

    private function kuwaitAreas() {
        
         return [
            '1789' => [//Ahmadi
                ['lang_id' => '1', 'name' => 'New Wafra'],
                ['lang_id' => '2', 'name' => '???????????? ??????????????'],
                ['lang_id' => '1', 'name' => 'Shalehat Zoor'],
                ['lang_id' => '2', 'name' => '???????????? ??????'],
                ['lang_id' => '1', 'name' => 'Khiran City'],
                ['lang_id' => '2', 'name' => '?????????? ??????????????'],
                ['lang_id' => '1', 'name' => 'Fahad Al-Ahmad'],
                ['lang_id' => '2', 'name' => '?????? ????????????'],
                ['lang_id' => '1', 'name' => 'Dhaher'],
                ['lang_id' => '2', 'name' => '???? ??????????????'],
                ['lang_id' => '1', 'name' => 'Hadiya'],
                ['lang_id' => '2', 'name' => '????????'],
                ['lang_id' => '1', 'name' => 'Riqqa'],
                ['lang_id' => '2', 'name' => '??????????'],
                ['lang_id' => '1', 'name' => 'Sabah Al-Ahmad 3'],
                ['lang_id' => '2', 'name' => '???????? ????????????-3'],
                ['lang_id' => '1', 'name' => 'Sabah Al-Ahmad 2'],
                ['lang_id' => '2', 'name' => '???????? ????????????-2'],
                ['lang_id' => '1', 'name' => 'Jaber Al-Ali'],
                ['lang_id' => '2', 'name' => '???????? ??????????'],
                ['lang_id' => '1', 'name' => 'Egaila'],
                ['lang_id' => '2', 'name' => '??????????????'],
                ['lang_id' => '1', 'name' => 'Sabah Al-Ahmad 4'],
                ['lang_id' => '2', 'name' => '???????? ???????????? 4'],
                ['lang_id' => '1', 'name' => 'Sabah Al-Ahmad 5'],
                ['lang_id' => '2', 'name' => '???????? ???????????? 5'],
                ['lang_id' => '1', 'name' => 'South-Sabahiya'],
                ['lang_id' => '2', 'name' => '???????? ????????????????'],
                ['lang_id' => '1', 'name' => 'Abu Halifa'],
                ['lang_id' => '2', 'name' => '?????? ??????????'],
                ['lang_id' => '1', 'name' => 'Middle of Ahmadi'],
                ['lang_id' => '2', 'name' => '?????? ??????????????'],
                ['lang_id' => '1', 'name' => 'Janobyia Aljawakheer'],
                ['lang_id' => '2', 'name' => '???????????? ????????????'],
                ['lang_id' => '1', 'name' => 'Sabah Al-Ahmad 1'],
                ['lang_id' => '2', 'name' => '???????? ???????????? 1'],
                ['lang_id' => '1', 'name' => 'North Ahmadi'],
                ['lang_id' => '2', 'name' => '???????? ??????????????'],
                ['lang_id' => '1', 'name' => 'Ali Subah Al-Salem'],
                ['lang_id' => '2', 'name' => '?????? ???????? ????????????'],
                ['lang_id' => '1', 'name' => 'Sabahiya'],
                ['lang_id' => '2', 'name' => '????????????????'],
                ['lang_id' => '1', 'name' => 'East Ahmadi'],
                ['lang_id' => '2', 'name' => '?????? ??????????????'],
                ['lang_id' => '1', 'name' => 'Sabah Al-Ahmad Investment'],
                ['lang_id' => '2', 'name' => '???????? ???????????? ??????????????????????'],
                ['lang_id' => '1', 'name' => 'South Ahmadi'],
                ['lang_id' => '2', 'name' => '???????? ??????????????'],
                ['lang_id' => '1', 'name' => 'Mahboula'],
                ['lang_id' => '2', 'name' => '????????????????'],
                ['lang_id' => '1', 'name' => 'Mangaf'],
                ['lang_id' => '2', 'name' => '????????????'],
                ['lang_id' => '1', 'name' => 'Mina Abdullah Refinery'],
                ['lang_id' => '2', 'name' => '?????????? ?????????? ??????????????'],
                ['lang_id' => '1', 'name' => 'Shalehat Jlea\'a'],
                ['lang_id' => '2', 'name' => '???????????? ??????????'],
                ['lang_id' => '1', 'name' => 'Shalehat Al-Nuwaiseeb'],
                ['lang_id' => '2', 'name' => '?????????????? ??????????????'],
                ['lang_id' => '1', 'name' => 'Sulaibyia Industrial 3'],
                ['lang_id' => '2', 'name' => '???????????????? ???????????????? 3'],
                ['lang_id' => '1', 'name' => 'Al-Fintas'],
                ['lang_id' => '2', 'name' => '??????????????'],
                ['lang_id' => '1', 'name' => 'Fahaheel'],
                ['lang_id' => '2', 'name' => '????????????????'],
                ['lang_id' => '1', 'name' => 'Shalehat Dba\'ayeh'],
                ['lang_id' => '2', 'name' => '???????????? ????????????'],
                ['lang_id' => '1', 'name' => 'kabd Agricultural'],
                ['lang_id' => '2', 'name' => '?????? ????????????????'],
                ['lang_id' => '1', 'name' => 'Shalehat Mina Abdullah'],
                ['lang_id' => '2', 'name' => '???????????? ?????????? ?????? ????????'],
                ['lang_id' => '1', 'name' => 'Mina Abdulla'],
                ['lang_id' => '2', 'name' => '?????????? ?????? ????????'],
                ['lang_id' => '1', 'name' => 'Shuaiba Industrial Western'],
                ['lang_id' => '2', 'name' => '?????????????? ???????????????? ??????????????'],
                ['lang_id' => '1', 'name' => 'Shuaiba Industrial esterly'],
                ['lang_id' => '2', 'name' => '?????????????? ???????????????? ????????????'],
                ['lang_id' => '1', 'name' => 'Al-Nuwaiseeb'],
                ['lang_id' => '2', 'name' => '??????????????'],
                ['lang_id' => '1', 'name' => 'Sabah Al-Ahmad Services'],
                ['lang_id' => '2', 'name' => '?????????? ?????????? ???????? ????????????'],
                ['lang_id' => '1', 'name' => 'Magwa'],
                ['lang_id' => '2', 'name' => '??????????'],
                ['lang_id' => '1', 'name' => 'Shalehat Al-Khiran'],
                ['lang_id' => '2', 'name' => '?????????????? ??????????????'],
                ['lang_id' => '1', 'name' => 'Mina Al-Ahmadi Refinery'],
                ['lang_id' => '2', 'name' => '???????? ?????????????? ??????????'],
                ['lang_id' => '1', 'name' => 'Zoor'],
                ['lang_id' => '2', 'name' => '??????????'],
                ['lang_id' => '1', 'name' => 'Shalehat Bneder'],
                ['lang_id' => '2', 'name' => '?????????????? ??????????'],
                ['lang_id' => '1', 'name' => 'Wafra'],
                ['lang_id' => '2', 'name' => '????????????'],
                ['lang_id' => '1', 'name' => 'Rajim Khashman'],
                ['lang_id' => '2', 'name' => '???????? ??????????'],
                ['lang_id' => '1', 'name' => 'Wafra Farms'],
                ['lang_id' => '2', 'name' => '?????????? ????????????'],
                ['lang_id' => '1', 'name' => 'Sabah Al-Ahmad Al-marine'],
                ['lang_id' => '2', 'name' => '???????? ???????????? ??????????????'],
                ['lang_id' => '1', 'name' => 'Ahmadi Governorate Desert'],
                ['lang_id' => '2', 'name' => '?????????? ???????????? ??????????????']
            ],
            '1790' => [//Farwaniya
                ['lang_id' => '1', 'name' => 'Ardhiya Herafiya'],
                ['lang_id' => '2', 'name' => '???????????????? ??????????'],
                ['lang_id' => '1', 'name' => 'Ardhiya 6'],
                ['lang_id' => '2', 'name' => '???????????????? 6'],
                ['lang_id' => '1', 'name' => 'Dajeej'],
                ['lang_id' => '2', 'name' => '????????????'],
                ['lang_id' => '1', 'name' => 'Rehab'],
                ['lang_id' => '2', 'name' => '????????????'],
                ['lang_id' => '1', 'name' => 'Omariya'],
                ['lang_id' => '2', 'name' => '??????????????'],
                ['lang_id' => '1', 'name' => 'Rabiya'],
                ['lang_id' => '2', 'name' => '??????????????'],
                ['lang_id' => '1', 'name' => 'Riggai'],
                ['lang_id' => '2', 'name' => '????????????'],
                ['lang_id' => '1', 'name' => 'Ashbeliah'],
                ['lang_id' => '2', 'name' => '??????????????'],
                ['lang_id' => '1', 'name' => 'Ardhiya 4'],
                ['lang_id' => '2', 'name' => '???????????????? 4'],
                ['lang_id' => '1', 'name' => 'Ferdous'],
                ['lang_id' => '2', 'name' => '??????????????'],
                ['lang_id' => '1', 'name' => 'Farwaniya'],
                ['lang_id' => '2', 'name' => '??????????????????'],
                ['lang_id' => '1', 'name' => 'Ardhiya'],
                ['lang_id' => '2', 'name' => '????????????????'],
                ['lang_id' => '1', 'name' => 'Rai'],
                ['lang_id' => '2', 'name' => '????????'],
                ['lang_id' => '1', 'name' => 'Andalus'],
                ['lang_id' => '2', 'name' => '??????????????'],
                ['lang_id' => '1', 'name' => 'Sabah Al-Nasser'],
                ['lang_id' => '2', 'name' => '???????? ????????????'],
                ['lang_id' => '1', 'name' => 'Khaitan'],
                ['lang_id' => '2', 'name' => '??????????'],
                ['lang_id' => '1', 'name' => 'Jleeb Al-Shiyoukh'],
                ['lang_id' => '2', 'name' => '???????? ????????????'],
                ['lang_id' => '1', 'name' => 'Sabah Al-Salem University City'],
                ['lang_id' => '2', 'name' => '???????? ???????????? ?????????????? ????????????????'],
                ['lang_id' => '1', 'name' => 'Abdullah Mubarak Al-Sabah'],
                ['lang_id' => '2', 'name' => '???????????? ?????????? ????????????'],
                ['lang_id' => '1', 'name' => 'International Airport'],
                ['lang_id' => '2', 'name' => '???????????? ????????????']
            ],
            '1792' => [//Hawalli
                ['lang_id' => '1', 'name' => 'Mubarakyia'],
                ['lang_id' => '2', 'name' => '??????????????????'],
                ['lang_id' => '1', 'name' => 'Al Bida\'a'],
                ['lang_id' => '2', 'name' => '??????????'],
                ['lang_id' => '1', 'name' => 'Ministries Area'],
                ['lang_id' => '2', 'name' => '?????????????? ????????????????'],
                ['lang_id' => '1', 'name' => 'Shuhada'],
                ['lang_id' => '2', 'name' => '??????????????'],
                ['lang_id' => '1', 'name' => 'Mubarak Al-Abdullah'],
                ['lang_id' => '2', 'name' => '?????????????? ??????????????'],
                ['lang_id' => '1', 'name' => 'Shaab'],
                ['lang_id' => '2', 'name' => '??????????'],
                ['lang_id' => '1', 'name' => 'Hitteen'],
                ['lang_id' => '2', 'name' => '????????'],
                ['lang_id' => '1', 'name' => 'Al-Siddiq'],
                ['lang_id' => '2', 'name' => '????????????'],
                ['lang_id' => '1', 'name' => 'Salam'],
                ['lang_id' => '2', 'name' => '????????'],
                ['lang_id' => '1', 'name' => 'Zahra'],
                ['lang_id' => '2', 'name' => '??????????????'],
                ['lang_id' => '1', 'name' => 'Rumaithiya'],
                ['lang_id' => '2', 'name' => '????????????????'],
                ['lang_id' => '1', 'name' => 'Anjafa'],
                ['lang_id' => '2', 'name' => '??????????'],
                ['lang_id' => '1', 'name' => 'Mishrif'],
                ['lang_id' => '2', 'name' => '????????'],
                ['lang_id' => '1', 'name' => 'Jabriya'],
                ['lang_id' => '2', 'name' => '????????????????'],
                ['lang_id' => '1', 'name' => 'Hawalli'],
                ['lang_id' => '2', 'name' => '????????'],
                ['lang_id' => '1', 'name' => 'Salwa'],
                ['lang_id' => '2', 'name' => '????????'],
                ['lang_id' => '1', 'name' => 'Bayan'],
                ['lang_id' => '2', 'name' => '????????????'],
                ['lang_id' => '1', 'name' => 'Salmiya'],
                ['lang_id' => '2', 'name' => '????????????????']
            ],
            '1791' => [//Jahra
                ['lang_id' => '1', 'name' => 'Jawakher Al Jahra'],
                ['lang_id' => '2', 'name' => '???????????? ??????????????'],
                ['lang_id' => '1', 'name' => 'Waha'],
                ['lang_id' => '2', 'name' => '????????????'],
                ['lang_id' => '1', 'name' => 'Naeem'],
                ['lang_id' => '2', 'name' => '????????????'],
                ['lang_id' => '1', 'name' => 'Nahda'],
                ['lang_id' => '2', 'name' => '????????????'],
                ['lang_id' => '1', 'name' => 'Oyoun'],
                ['lang_id' => '2', 'name' => '????????????'],
                ['lang_id' => '1', 'name' => 'Sulaibiya Industrial 1'],
                ['lang_id' => '2', 'name' => '???????????? ?????????????? ????????????????- 1'],
                ['lang_id' => '1', 'name' => 'Jahra'],
                ['lang_id' => '2', 'name' => '??????????????'],
                ['lang_id' => '1', 'name' => 'Nasseem'],
                ['lang_id' => '2', 'name' => '????????'],
                ['lang_id' => '1', 'name' => 'Taima'],
                ['lang_id' => '2', 'name' => '??????????'],
                ['lang_id' => '1', 'name' => 'Qasr'],
                ['lang_id' => '2', 'name' => '??????????'],
                ['lang_id' => '1', 'name' => 'Kaerawan'],
                ['lang_id' => '2', 'name' => '????????????????'],
                ['lang_id' => '1', 'name' => 'Sulaibiya Industrial 2'],
                ['lang_id' => '2', 'name' => '???????????????? ???????????????? 2'],
                ['lang_id' => '1', 'name' => 'Sulaibiya'],
                ['lang_id' => '2', 'name' => '????????????????'],
                ['lang_id' => '1', 'name' => 'North West Jahra'],
                ['lang_id' => '2', 'name' => '???????? ?????? ??????????????'],
                ['lang_id' => '1', 'name' => 'Jahra-Industrial'],
                ['lang_id' => '2', 'name' => '?????????????? ????????????????'],
                ['lang_id' => '1', 'name' => 'Amghara Industrial'],
                ['lang_id' => '2', 'name' => '?????????? ????????????????'],
                ['lang_id' => '1', 'name' => 'Saad Al-Abdulla City'],
                ['lang_id' => '2', 'name' => '?????????? ?????? ??????????????????'],
                ['lang_id' => '1', 'name' => 'Kabd'],
                ['lang_id' => '2', 'name' => '??????'],
                ['lang_id' => '1', 'name' => 'Al Naayem'],
                ['lang_id' => '2', 'name' => '????????????'],
                ['lang_id' => '1', 'name' => 'Jahra Camps'],
                ['lang_id' => '2', 'name' => '???????????? ??????????????'],
                ['lang_id' => '1', 'name' => 'Bhaith'],
                ['lang_id' => '2', 'name' => '????????????'],
                ['lang_id' => '1', 'name' => 'Sulaibiya Agricultural'],
                ['lang_id' => '2', 'name' => '???????????????? ????????????????'],
                ['lang_id' => '1', 'name' => 'Shalehat Subiya'],
                ['lang_id' => '2', 'name' => '???????? ????????????'],
                ['lang_id' => '1', 'name' => 'Umm Al-Aish'],
                ['lang_id' => '2', 'name' => '???? ??????????'],
                ['lang_id' => '1', 'name' => 'Shalehat Kazima'],
                ['lang_id' => '2', 'name' => '???????????? ??????????'],
                ['lang_id' => '1', 'name' => 'Kazima'],
                ['lang_id' => '2', 'name' => '??????????'],
                ['lang_id' => '1', 'name' => 'Abdally'],
                ['lang_id' => '1', 'name' => 'Salmy'],
                ['lang_id' => '2', 'name' => '??????????'],
                ['lang_id' => '1', 'name' => 'Al Sheqaya'],
                ['lang_id' => '2', 'name' => '???? ????????'],
                ['lang_id' => '1', 'name' => 'Rawdatain'],
                ['lang_id' => '2', 'name' => '????????????'],
                ['lang_id' => '1', 'name' => 'Subiya'],
                ['lang_id' => '2', 'name' => '????????????'],
                ['lang_id' => '1', 'name' => 'Bar Al-Jahra Governorate'],
                ['lang_id' => '2', 'name' => '???????????? ???? ??????????????']
            ],
            '7213' => [// Mubarak al Kabeer
                ['lang_id' => '1', 'name' => 'West Abu Ftirah Hirafyia'],
                ['lang_id' => '2', 'name' => '?????? ?????? ?????????? ??????????????'],
                ['lang_id' => '1', 'name' => 'Al Masayel'],
                ['lang_id' => '2', 'name' => '??????????????'],
                ['lang_id' => '1', 'name' => 'Abu Hassaniah'],
                ['lang_id' => '2', 'name' => '?????? ????????????????'],
                ['lang_id' => '1', 'name' => 'Abu Ftaira'],
                ['lang_id' => '2', 'name' => '?????? ??????????'],
                ['lang_id' => '1', 'name' => 'Al-Qurain'],
                ['lang_id' => '2', 'name' => '????????????'],
                ['lang_id' => '1', 'name' => 'Al-Qusour'],
                ['lang_id' => '2', 'name' => '????????????'],
                ['lang_id' => '1', 'name' => 'Mubarak Al-Kabeer'],
                ['lang_id' => '2', 'name' => '?????????? ????????????'],
                ['lang_id' => '1', 'name' => 'Al-Adan'],
                ['lang_id' => '2', 'name' => '????????????'],
                ['lang_id' => '1', 'name' => 'Subhan Industrial'],
                ['lang_id' => '2', 'name' => '?????????? ????????????????'],
                ['lang_id' => '1', 'name' => 'Al-Fnaitees'],
                ['lang_id' => '2', 'name' => '????????????????'],
                ['lang_id' => '1', 'name' => 'Sabah Al-Salem'],
                ['lang_id' => '2', 'name' => '???????? ????????????'],
                ['lang_id' => '1', 'name' => 'Messila'],
                ['lang_id' => '2', 'name' => '??????????????'],
                ['lang_id' => '1', 'name' => 'Wista'],
                ['lang_id' => '2', 'name' => '????????']
            ],
            '1788' => [//Al 'Asimah
                ['lang_id' => '1', 'name' => 'Dasman'],
                ['lang_id' => '2', 'name' => '??????????'],
                ['lang_id' => '1', 'name' => 'Mansouriya'],
                ['lang_id' => '2', 'name' => '??????????????????'],
                ['lang_id' => '1', 'name' => 'Nuzha'],
                ['lang_id' => '2', 'name' => '????????????'],
                ['lang_id' => '1', 'name' => 'Faiha'],
                ['lang_id' => '2', 'name' => '??????????????'],
                ['lang_id' => '1', 'name' => 'Bnaid Al-Qar'],
                ['lang_id' => '2', 'name' => '???????? ??????????'],
                ['lang_id' => '1', 'name' => 'Qadsiya'],
                ['lang_id' => '2', 'name' => '????????????????'],
                ['lang_id' => '1', 'name' => 'Dasma'],
                ['lang_id' => '2', 'name' => '????????????'],
                ['lang_id' => '1', 'name' => 'Mirqab'],
                ['lang_id' => '2', 'name' => '??????????????'],
                ['lang_id' => '1', 'name' => 'Shamiya'],
                ['lang_id' => '2', 'name' => '??????????????'],
                ['lang_id' => '1', 'name' => 'Daiya'],
                ['lang_id' => '2', 'name' => '????????????'],
                ['lang_id' => '1', 'name' => 'Yarmouk'],
                ['lang_id' => '2', 'name' => '??????????????'],
                ['lang_id' => '1', 'name' => 'Shuwaikh Industrial-3'],
                ['lang_id' => '2', 'name' => '???????????? ?????????????? ????????????????-3'],
                ['lang_id' => '1', 'name' => 'Abdulla Al-Salem'],
                ['lang_id' => '2', 'name' => '?????? ???????? ????????????'],
                ['lang_id' => '1', 'name' => 'Khaldiya'],
                ['lang_id' => '2', 'name' => '????????????????'],
                ['lang_id' => '1', 'name' => 'Adailiya'],
                ['lang_id' => '2', 'name' => '??????????????'],
                ['lang_id' => '1', 'name' => 'Shuwaikh Industrial-2'],
                ['lang_id' => '2', 'name' => '???????????? ?????????????? ????????????????-2'],
                ['lang_id' => '1', 'name' => 'Rawda'],
                ['lang_id' => '2', 'name' => '????????????'],
                ['lang_id' => '1', 'name' => 'Qibla'],
                ['lang_id' => '2', 'name' => '????????'],
                ['lang_id' => '1', 'name' => 'Sharq'],
                ['lang_id' => '2', 'name' => '??????'],
                ['lang_id' => '1', 'name' => 'Ghornata'],
                ['lang_id' => '2', 'name' => '????????????'],
                ['lang_id' => '1', 'name' => 'Kifan'],
                ['lang_id' => '2', 'name' => '??????????'],
                ['lang_id' => '1', 'name' => 'Qortuba'],
                ['lang_id' => '2', 'name' => '??????????'],
                ['lang_id' => '1', 'name' => 'Northwest Sulaibikhat'],
                ['lang_id' => '2', 'name' => '???????? ?????? ????????????????????'],
                ['lang_id' => '1', 'name' => 'Surra'],
                ['lang_id' => '2', 'name' => '??????????'],
                ['lang_id' => '1', 'name' => 'Al Sour Gardens'],
                ['lang_id' => '2', 'name' => '?????????? ??????????'],
                ['lang_id' => '1', 'name' => 'Shuwaikh'],
                ['lang_id' => '2', 'name' => '????????????'],
                ['lang_id' => '1', 'name' => 'Al shadadyia'],
                ['lang_id' => '2', 'name' => '????????????????'],
                ['lang_id' => '1', 'name' => 'Mubarakiya Camps'],
                ['lang_id' => '2', 'name' => '???????????? ??????????????????'],
                ['lang_id' => '1', 'name' => 'Health Area'],
                ['lang_id' => '2', 'name' => '?????????? ????????'],
                ['lang_id' => '1', 'name' => 'Doha'],
                ['lang_id' => '2', 'name' => '????????????'],
                ['lang_id' => '1', 'name' => 'Shalehat Doha'],
                ['lang_id' => '2', 'name' => '???????????? ????????????'],
                ['lang_id' => '1', 'name' => 'Sulaibikhat'],
                ['lang_id' => '2', 'name' => '????????????????????'],
                ['lang_id' => '1', 'name' => 'Shuwaikh Port'],
                ['lang_id' => '2', 'name' => '?????????? ????????????'],
                ['lang_id' => '1', 'name' => 'Shuwaikh Industrial-1'],
                ['lang_id' => '2', 'name' => '???????????? ?????????????? ????????????????-1'],
                ['lang_id' => '1', 'name' => 'Jaber Al-Ahmad'],
                ['lang_id' => '2', 'name' => '?????????? ???????? ????????????'],
                ['lang_id' => '1', 'name' => 'The Sea Front'],
                ['lang_id' => '2', 'name' => '???????? ??????????'],
                ['lang_id' => '1', 'name' => 'Failaka Island'],
                ['lang_id' => '2', 'name' => '?????????? ??????????'],
                ['lang_id' => '1', 'name' => 'Mina Doha'],
                ['lang_id' => '2', 'name' => '?????????? ????????????']
            ]
        ];
        
    }






    /* getAreas is using any more  becase we replacing with area level in our geo data*/
    
    public function getAreas()
    {
        return  [
                    [
                        "governorate" => "Asma Governorate", 
                        "areas" => [
                            "Dasman", 
                            "Mansouriya", 
                            "Nuzha", 
                            "Faiha", 
                            "Bnaid Al-Qar", 
                            "Qadsiya", 
                            "Dasma", 
                            "Mirqab", 
                            "Shamiya", 
                            "Daiya", 
                            "Yarmouk", 
                            "Shuwaikh Industrial-3", 
                            "Abdulla Al-Salem", 
                            "Khaldiya", 
                            "Adailiya", 
                            "Shuwaikh Industrial-2", 
                            "Rawda", 
                            "Qibla", 
                            "Sharq", 
                            "Ghornata", 
                            "Kifan", 
                            "Qortuba", 
                            "Northwest Sulaibikhat", 
                            "Surra", 
                            "Al Sour Gardens", 
                            "Shuwaikh", 
                            "Mubarakiya Camps", 
                            "Health Area", 
                            "Doha", 
                            "Shalehat Doha", 
                            "Sulaibikhat", 
                            "Shuwaikh Port", 
                            "Shuwaikh Industrial-1", 
                            "Jaber Al-Ahmad", 
                            "The Sea Front", 
                            "Failaka Island", 
                            "Mina Doha" 
                        ] 
                    ], 
                    [
                        "governorate" => "Hawalli Governorate", 
                        "areas" => [
                            "Mubarakyia", 
                            "Al Bida'a", 
                            "Ministries Area", 
                            "Shuhada", 
                            "Mubarak Al-Abdullah", 
                            "Shaab", 
                            "Hitteen", 
                            "Al-Siddiq", 
                            "Salam", 
                            "Zahra", 
                            "Rumaithiya", 
                            "Anjafa", 
                            "Mishrif", 
                            "Jabriya", 
                            "Hawalli", 
                            "Salwa", 
                            "Bayan", 
                            "Salmiya" 
                        ] 
                    ], 
                    [
                        "governorate" => "Farwaniya Governorate", 
                        "areas" => [
                            "Ardhiya Herafiya", 
                            "Ardhiya 6", 
                            "Dajeej", 
                            "Rehab", 
                            "Omariya", 
                            "Rabiya", 
                            "Riggai", 
                            "Ashbeliah", 
                            "Ardhiya 4", 
                            "Ferdous", 
                            "Farwaniya", 
                            "Ardhiya", 
                            "Rai", 
                            "Andalus", 
                            "Sabah Al-Nasser", 
                            "Khaitan", 
                            "Jleeb Al-Shiyoukh", 
                            "Sabah Al-Salem University City", 
                            "Abdullah Mubarak Al-Sabah", 
                            "International Airport" 
                        ] 
                    ], 
                    [
                        "governorate" => "Mubarak Al Kabeer Governorate", 
                        "areas" => [
                            "West Abu Ftirah Hirafyia", 
                            "Al Masayel", 
                            "Abu Hassaniah", 
                            "Abu Ftaira", 
                            "Al-Qurain", 
                            "Al-Qusour", 
                            "Mubarak Al-Kabeer", 
                            "Al-Adan", 
                            "Subhan Industrial", 
                            "Al-Fnaitees", 
                            "Sabah Al-Salem", 
                            "Messila", 
                            "Wista" 
                        ] 
                    ], 
                    [
                        "governorate" => "Jahra Governorate", 
                        "areas" => [
                            "Waha", 
                            "Naeem", 
                            "Nahda", 
                            "Oyoun", 
                            "Sulaibiya Industrial 1", 
                            "Jahra", 
                            "Nasseem", 
                            "Taima", 
                            "Qasr", 
                            "Kaerawan", 
                            "Sulaibiya Industrial 2", 
                            "Sulaibiya", 
                            "North West Jahra", 
                            "Jahra-Industrial", 
                            "Amghara Industrial", 
                            "Saad Al-Abdulla City", 
                            "Kabd", 
                            "Al Naayem", 
                            "Jahra Camps", 
                            "Bhaith", 
                            "Sulaibiya Agricultural", 
                            "Shalehat Subiya", 
                            "Umm Al-Aish", 
                            "Shalehat Kazima", 
                            "Kazima", 
                            "Abdally", 
                            "Salmy", 
                            "Al Sheqaya", 
                            "Rawdatain", 
                            "Subiya", 
                            "Bar Al-Jahra Governorate" 
                        ] 
                    ], 
                    [
                        "governorate" => "Ahmadi Governorate", 
                        "areas" => [
                            "New Wafra", 
                            "Shalehat Zoor", 
                            "Khiran City", 
                            "Fahad Al-Ahmad", 
                            "Dhaher", 
                            "Hadiya", 
                            "Riqqa", 
                            "Sabah Al-Ahmad 3", 
                            "Sabah Al-Ahmad 2", 
                            "Jaber Al-Ali", 
                            "Egaila", 
                            "Sabah Al-Ahmad 4", 
                            "Sabah Al-Ahmad 5", 
                            "South-Sabahiya", 
                            "Abu Halifa", 
                            "Middle of Ahmadi", 
                            "Janobyia Aljawakheer", 
                            "Sabah Al-Ahmad 1", 
                            "North Ahmadi", 
                            "Ali Subah Al-Salem", 
                            "Sabahiya", 
                            "East Ahmadi", 
                            "Sabah Al-Ahmad Investment", 
                            "South Ahmadi", 
                            "Mahboula", 
                            "Mangaf", 
                            "Mina Abdullah Refinery", 
                            "Shalehat Jlea'a", 
                            "Shalehat Al-Nuwaiseeb", 
                            "Sulaibyia Industrial 3", 
                            "Al-Fintas", 
                            "Fahaheel", 
                            "Shalehat Dba'ayeh", 
                            "kabd Agricultural", 
                            "Shalehat Mina Abdullah", 
                            "Mina Abdulla", 
                            "Shuaiba Industrial Western", 
                            "Shuaiba Industrial esterly", 
                            "Al-Nuwaiseeb", 
                            "Sabah Al-Ahmad Services", 
                            "Magwa", 
                            "Shalehat Al-Khiran", 
                            "Mina Al-Ahmadi Refinery", 
                            "Zoor", 
                            "Shalehat Bneder", 
                            "Wafra", 
                            "Rajim Khashman", 
                            "Wafra Farms", 
                            "Sabah Al-Ahmad Al-marine", 
                            "Ahmadi Governorate Desert" 
                    ] 
                ] 
        ]; 
    }
}
