<?php
class ModelShippingAramex extends Model {
	
	public function domesticmethods() {
	
		$arr[] = array('value'=>'BLK', 'label'=>'Special: Bulk Mail Delivery');
		$arr[] = array('value'=>'BLT', 'label'=>'Domestic - Bullet Delivery');	
		$arr[] = array('value'=>'CDA', 'label'=>'Special Delivery');
		$arr[] = array('value'=>'CDS', 'label'=>'Special: Credit Cards Delivery');
		$arr[] = array('value'=>'CGO', 'label'=>'Air Cargo - India');
		
		$arr[] = array('value'=>'COM', 'label'=>'Special: Cheque Collection');
		$arr[] = array('value'=>'DEC', 'label'=>'Special: Invoice Delivery');
		$arr[] = array('value'=>'EMD', 'label'=>'Early Morning delivery');
		$arr[] = array('value'=>'FIX', 'label'=>'Special: Bank Branches Run');
		$arr[] = array('value'=>'LGS', 'label'=>'Logistic Shipment');
		
		$arr[] = array('value'=>'OND', 'label'=>'Overnight - Document');
		$arr[] = array('value'=>'ONP', 'label'=>'Overnight - Parcel');
		$arr[] = array('value'=>'P24', 'label'=>'Road Freight 24 hours service');
		$arr[] = array('value'=>'P48', 'label'=>'Road Freight 48 hours service');
		$arr[] = array('value'=>'PEC', 'label'=>'Economy Delivery');
		
		$arr[] = array('value'=>'PEX', 'label'=>'Road Express');
		$arr[] = array('value'=>'SFC', 'label'=>'Surface  Cargo - India');
		$arr[] = array('value'=>'SMD', 'label'=>'Same Day - Document');
		$arr[] = array('value'=>'SMP', 'label'=>'Same Day - Parcel');
		$arr[] = array('value'=>'SPD', 'label'=>'Special: Legal Branches Mail Service');
		
		$arr[] = array('value'=>'SPL', 'label'=>'Special: Legal Notifications Delivery');
		
        return $arr;
	}
	
	public function domesticAdditionalServices()
	{
	
		$arr[] = array('value'=>'AM10', 'label'=>'Morning delivery');
		$arr[] = array('value'=>'CHST', 'label'=>'Chain Stores Delivery');	
		$arr[] = array('value'=>'CODS', 'label'=>'Cash On Delivery Service');
		$arr[] = array('value'=>'COMM', 'label'=>'Commercial');
		$arr[] = array('value'=>'CRDT', 'label'=>'Credit Card');
		
		$arr[] = array('value'=>'DDP', 'label'=>'DDP - Delivery Duty Paid - For European Use');
		$arr[] = array('value'=>'DDU', 'label'=>'DDU - Delivery Duty Unpaid - For the European Freight');
		$arr[] = array('value'=>'EXW', 'label'=>'Not An Aramex Customer - For European Freight');
		$arr[] = array('value'=>'INSR', 'label'=>'Insurance');
		$arr[] = array('value'=>'RTRN', 'label'=>'Return');
		
		$arr[] = array('value'=>'SPCL', 'label'=>'Special Services');	
		
		
        return $arr;
	}
	
	public function internationalmethods()
	{
		$arr[] = array('value'=>'DPX', 'label'=>'Value Express Parcels');
		$arr[] = array('value'=>'EDX', 'label'=>'Economy Document Express');
		$arr[] = array('value'=>'EPX', 'label'=>'Economy Parcel Express');
		$arr[] = array('value'=>'GDX', 'label'=>'Ground Document Express');
		$arr[] = array('value'=>'GPX', 'label'=>'Ground Parcel Express');
		
		$arr[] = array('value'=>'IBD', 'label'=>'International defered');
		$arr[] = array('value'=>'PDX', 'label'=>'Priority Document Express');
		$arr[] = array('value'=>'PLX', 'label'=>'Priority Letter Express <.5 kg Docs');
		$arr[] = array('value'=>'PPX', 'label'=>'Priority Parcel Express');
		
        return $arr;
	
	}
	
	public function internationalAdditionalServices()
	{
		$arr[] = array('value'=>'AM10', 'label'=>'Morning delivery');
		$arr[] = array('value'=>'CODS', 'label'=>'Cash On Delivery');
		$arr[] = array('value'=>'CSTM', 'label'=>'CSTM');
		$arr[] = array('value'=>'EUCO', 'label'=>'NULL');
		$arr[] = array('value'=>'FDAC', 'label'=>'FDAC');
		
		/*$arr[] = array('value'=>'FRD1', 'label'=>'Free Domicile');*/
		$arr[] = array('value'=>'FRDM', 'label'=>'FRDM');
		$arr[] = array('value'=>'INSR', 'label'=>'Insurance');
		$arr[] = array('value'=>'NOON', 'label'=>'Noon Delivery');
		$arr[] = array('value'=>'ODDS', 'label'=>'Over Size');
		
		$arr[] = array('value'=>'RTRN', 'label'=>'RTRN');
		$arr[] = array('value'=>'SIGR', 'label'=>'Signature Required');
		$arr[] = array('value'=>'SPCL', 'label'=>'Special Services');
		
		
		
        return $arr;
	
	}
}
?>
