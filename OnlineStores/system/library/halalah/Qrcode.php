<?php

require_once 'halalah_crc16.php';

class HQrcode
{
    const VERSION = '000201';
    const POI = '010212';
    const HALALAH_ID = '33';
    const HALALAH_GUI = 'sa.halalah';
    const COUNTRY = 'SA';
    const LANG = 'AR';
    const CURRENCY = '682';

    private $error = '';

    private $merchant_id = '';
    private $branch_id = '';
    private $terminal_id = '';
    private $merchant_category_code = '';
    private $merchant_name = '';
    private $merchant_name_ar = '';
    private $merchant_city_ar = '';
    private $merchant_city = '';
    private $postal_code = '';
    private $amount = '';
    private $bill = '';
    private $mobile = '';
    private $store = '';
    private $loyalty = '';
    private $reference = '';
    private $consumer = '';
    private $terminal = '';
    private $purpose = '';
    private $request = '';
    private $tip = '';

    /*
     * AUTOMATIC FUNCTIONS
     */
    public function __construct($inputs){
        foreach ($inputs as $key=>$val)
        {
            $this->set_vars($key, strip_tags(trim($val)));
        }
    } // end: __construct($inputs)

    private function set_vars($key, $val){
        if(isset($this->$key))
        {
            $this->$key = $val;
        }
    } // end: set_vars($key, $val)

    private function get_length($str){
        $length = mb_strlen($str);
        if($length <=9){
            return '0'.$length;
        }else{
            return ''.$length;
        }
    } // end: get_length($str)

    private function id_len_val($id, $val){
        return $id.$this->get_length($val).$val;
    } // end: id_len_val($id, $val)

    function add_error($err) {
        $this->error .= ', ERROR: ' . $err . '.';
        return true;
    }

    /*
     * PREPARE FUNCTIONS
     */
    private function merchant_account_info(){
        $gui_line = '00'.$this->get_length(self::HALALAH_GUI).self::HALALAH_GUI;

        $merchant_line = '';
        if(!empty($this->merchant_id)){
            $merchant_id = $this->merchant_id;
            $merchant_line = '04'.$this->get_length($merchant_id).$merchant_id;
        }

        $branch_line = '';
        if(!empty($this->branch_id)){
            $branch_id = $this->branch_id;
            $branch_line = '05'.$this->get_length($branch_id).$branch_id;
        }

        $terminal_line = '';
        if(empty($this->terminal_id)){
            $this->add_error('MUST ADD TERMINAL ID');
        }else{
            $terminal_id = $this->terminal_id;
            $terminal_line = '06'.$this->get_length($terminal_id).$terminal_id;
        }

        $z_line = $gui_line.$merchant_line.$branch_line.$terminal_line;
        $mai_len = $this->get_length($z_line);
        $mai_o = self::HALALAH_ID.$mai_len.$z_line;

        return $mai_o;
    } // end: merchant_account_info()

    private function mmc(){
        if(empty($this->merchant_category_code)){
            $this->add_error('MUST ADD MERCHANT CATEGORY CODE');
        }
        return $this->id_len_val('52', $this->merchant_category_code);
    } // end: function mmc()

    private function country(){
        return $this->id_len_val('58', self::COUNTRY);
    } // end: function country()

    private function merchant_name(){
        if(empty($this->merchant_name)){
            $this->add_error('MUST ADD MERCHANT NAME');
        }
        return $this->id_len_val('59', $this->merchant_name);
    } // end: function merchant_name()

    private function merchant_city(){
        if(!empty($this->merchant_city)){
            return $this->id_len_val('60', $this->merchant_city);
        }
    } // end: function merchant_city()

    private function postal_code(){
        if(!empty($this->postal_code)){
            return $this->id_len_val('61', $this->postal_code);
        }
    } // end: function postal_code()

    private function local_name_city(){
        $local_line = $this->id_len_val('00', self::LANG);

        $name_line = '';
        $city_line = '';

        if(!empty($this->merchant_name_ar)){
            $name_line = $this->id_len_val('01', $this->merchant_name_ar);
        }
        if(!empty($this->merchant_city_ar)){
            $city_line = $this->id_len_val('02', $this->merchant_city_ar);
        }

        $z_line = $local_line.$name_line.$city_line;
        $al_len = $this->get_length($z_line);
        return '64'.$al_len.$z_line;

    } // end: function local_name_city()

    private function amount(){
        if(empty($this->amount)){
            $this->add_error('MUST ADD AMOUNT');
        }
        return $this->id_len_val('54', $this->amount);
    } // end: function amount()

    private function currency(){
        return $this->id_len_val('53', SELF::CURRENCY);
    } // end: function currency()

    private function tip(){
        if(!empty($this->tip)) {
            return '550201';
        }
    } // end: function tip()


    private function additional_data(){

        $bill_line = '';
        $mobile_line = '';
        $store_line = '';
        $loyalty_line = '';
        $reference_line = '';
        $consumer_line = '';
        $terminal_line = '';
        $purpose_line = '';
        $request = '';

        if(!empty($this->bill)){
            $bill_line = $this->id_len_val('01', $this->bill);
        }
        if(!empty($this->mobile)){
            $mobile_line = $this->id_len_val('02', $this->mobile);
        }
        if(!empty($this->store)){
            $store_line = $this->id_len_val('03', $this->store);
        }
        if(!empty($this->loyalty)){
            $loyalty_line = $this->id_len_val('04', $this->loyalty);
        }
        if(!empty($this->reference)){
            $reference_line = $this->id_len_val('05', $this->reference);
        }
        if(!empty($this->consumer)){
            $consumer_line = $this->id_len_val('06', $this->consumer);
        }
        if(!empty($this->terminal)){
            $terminal_line = $this->id_len_val('07', $this->terminal);
        }
        if(!empty($this->purpose)){
            $purpose_line = $this->id_len_val('08', $this->purpose);
        }
        if(!empty($this->request)){
            $request = $this->id_len_val('09', $this->request);
        }

        $z_line = $bill_line.$mobile_line.$store_line.$loyalty_line.$reference_line.$consumer_line.$terminal_line.$purpose_line.$request;

        $al_len = $this->get_length($z_line);

        if(!empty($al_len) && !empty($z_line)){
            return '62'.$al_len.$z_line;
        }
    } // end: function additional_data($val)

    private function crc($o){
        $crc_id_len = '6304';
        $crc = generate_crc($o.$crc_id_len);
        return $crc_id_len.$crc;
//        $crc_id_len = '6308';
//        return $crc_id_len.sprintf("%x\n", crc32($o.$crc_id_len));
    }

    /*
     * OUTPUT
     */
    public function output(){

        $o = '';

        $o .= self::VERSION;
        $o .= self::POI;
        $o .= $this->merchant_account_info();
        $o .= $this->mmc();
        $o .= $this->country();
        $o .= $this->merchant_name();
        $o .= $this->merchant_city();
        $o .= $this->postal_code();
        $o .= $this->local_name_city();
        $o .= $this->amount();
        $o .= $this->currency();
//        $o .= $this->tip();
        $o .= $this->additional_data();
        $o .= $this->crc($o);
//        $o = base64_encode($o);

        return $o;

    } // end:public function output()



} // end: class Qrcode