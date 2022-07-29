<?php
class ModelSettingDomainSetting extends Model {
    public function getDomains(){
        return '';
    }

    public function addDomain($domain_name) {
        return '1';
    }

    public function deleteDomain($uniqueid) {
        return true;
    }

    public function countDomain($domain_name=null) {
        return '2';
    }
}