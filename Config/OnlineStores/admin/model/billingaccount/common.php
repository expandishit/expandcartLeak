<?php 
class ModelBillingAccountCommon extends Model {
    public function getBillingEmail() {
        return 'test@test.com';
    }

    public function getBillingDetails() {
        $billingDetails = array(
            'email' => 'test@test.com',
            'name' => 'Test'
        );

        return $billingDetails;
    }

    public function getTrialDaysLeft() {
        return '-2';
    }

    public function getAgentDetails() {
        return array(
            'displayname_ar' => '',
            'displayname_en' => '',
            'coveragehtml_ar' => '',
            'coveragehtml_en' => '',
            'image' => ''
        );
    }
}
?>