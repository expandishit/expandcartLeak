<?php echo $header; ?>

<div id="content">
<ol class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php if ($breadcrumb === end($breadcrumbs)) { ?>
    <li class="active">
        <?php } else { ?>
    <li>
        <?php } ?>
        <a href="<?php echo $breadcrumb['href']; ?>">
            <?php if ($breadcrumb === reset($breadcrumbs)) { ?>
            <?php echo $breadcrumb['text']; ?>
            <?php } else { ?>
            <span><?php echo $breadcrumb['text']; ?></span>
            <?php } ?>
        </a>
    </li>
    <?php } ?>
</ol>
<div class="box">
<div class="heading">
  <h1><?php echo $heading_title; ?></h1>
  <div class="buttons"> 
  <a href="<?php echo $back_to_order; ?>" class="button btn btn-primary"><?php echo $text_back_to_order; ?></a>
  <?php
  if($is_shipment)
  {
  ?>
  <a href="<?php echo $aramex_create_sipment; ?>" class="button btn btn-primary"><?php echo $text_return_shipment; ?></a>
  <?php
  }else{
  ?>
   <a href="<?php echo $aramex_create_sipment; ?>" class="button btn btn-primary"><?php echo $text_create_sipment; ?></a>
  <?php } ?>
  
  <a href="<?php echo $aramex_rate_calculator; ?>"  class="button btn btn-primary"><?php echo $text_rate_calculator; ?></a>
  <?php
  if($is_shipment)
  {
  ?>
	<a href="<?php echo $aramex_print_label; ?>" target="_blank" class="button btn btn-primary"><?php echo $text_print_label; ?></a>
	<a href="<?php echo $aramex_schedule_pickup; ?>"  class="button btn btn-primary"><?php echo $text_schedule_pickup; ?></a>
	<a href="<?php echo $aramex_traking; ?>"  class="button btn btn-primary"><?php echo $text_track; ?></a>
  <?php
  }
  ?>
  </div>
</div>
<div class="content">
  <!--  code --->
  <form enctype="multipart/form-data" action="" method="post" id="aramex_shipment" novalidate="novalidate">
    <table>
	<tr>
        <td colspan="2" align="center">
			<h3>
			<?php 
			if(isset($success_html) && !empty($success_html))
			{
					echo $success_html;
			}
			
			?>
			</h3>
		</td>
      </tr>
	 <tr>
        <td colspan="2" style="color:red;">
				<?php
					if(isset($eRRORS) && !empty($eRRORS))
					{
						foreach($eRRORS as $val)
						{
								echo $val;
								echo "<br>";
						}
					}
				?>
		</td>
	 <tr>	
      <tr>
        <td colspan="2">
		  <input type="hidden" value="" name="aramex_shipment_referer">
          <input type="hidden" value="<?php echo $aramex_shipment_shipper_account;?>" name="aramex_shipment_shipper_account">
          <input type="hidden" value="<?php echo $reference;?>" name="aramex_shipment_original_reference">
          <fieldset id="aramex_shipment_creation_general_info">
          <legend><?php echo $text_billing_account; ?></legend>
          <table class="form">
            <tr>
              <td><?php echo $entry_account; ?></td>
              <td><input type="text" value="<?php echo $aramex_shipment_shipper_account;?>" disabled="disabled" name="aramex_shipment_shipper_account_show">
                <span><?php echo $text_global_setting_msg; ?></span> </td>
            </tr>
            <tr>
              <td><?php echo $entry_payment; ?></td>
              <td><select onchange="resetShipperDetail(this);" name="aramex_shipment_info_billing_account" class="aramex_all_options valid" id="payment_switcher">
                  <option value="1" <?php echo ($aramex_shipment_info_billing_account=='1')?'selected="selected"':''?>><?php echo $text_shipper_account; ?></option>
                  <option value="2" <?php echo ($aramex_shipment_info_billing_account=='2')?'selected="selected"':''?>><?php echo $text_consignee_account; ?></option>
                  <option value="3" <?php echo ($aramex_shipment_info_billing_account=='3')?'selected="selected"':''?>><?php echo $text_third_party; ?></option>
                </select>
              </td>
            </tr>
          </table>
          </fieldset></td>
      </tr>
      <tr>
        <td><fieldset class="aramex_shipment_creation_fieldset">
          <legend><?php echo $text_shipper_details; ?></legend>
          <table class="form aramex_shipment_creation_part" id="shipper_details">
            <tr>
              <td><?php echo $entry_reference; ?></td>
              <td><input type="text" value="<?php echo $reference;?>" name="aramex_shipment_shipper_reference">
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_name; ?></td>
              <td><input type="text" value="<?php echo $aramex_shipment_shipper_name;?>" name="aramex_shipment_shipper_name" id="aramex_shipment_shipper_name">
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_email; ?></td>
              <td><input type="text" value="<?php echo $aramex_shipment_shipper_email;?>" name="aramex_shipment_shipper_email" id="aramex_shipment_shipper_email">
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_company; ?></td>
              <td><input type="text" value="<?php echo $aramex_shipment_shipper_company;?>" name="aramex_shipment_shipper_company" id="aramex_shipment_shipper_company">
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_address; ?></td>
              <td><textarea name="aramex_shipment_shipper_street" id="aramex_shipment_shipper_street" type="text" cols="26" rows="4"><?php echo $aramex_shipment_shipper_street;?></textarea>
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_country; ?></td>
              <td><select name="aramex_shipment_shipper_country" id="aramex_shipment_shipper_country" class="aramex_countries validate-select valid">
                 
				 <?php foreach ($countries as $country) { ?>
                  <?php if ($country['iso_code_2'] == $aramex_shipment_shipper_country) { ?>
                  <option value="<?php echo $country['iso_code_2']; ?>" selected="selected"><?php echo $country['name']; ?></option>
                  <?php } else { ?>
                  <option value="<?php echo $country['iso_code_2']; ?>"><?php echo $country['name']; ?></option>
                  <?php } ?>
                  <?php } ?>
				  
                </select>
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_city; ?></td>
              <td><input type="text" value="<?php echo $aramex_shipment_shipper_city;?>" name="aramex_shipment_shipper_city" id="aramex_shipment_shipper_city">
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_postal_code; ?></td>
              <td><input type="text" value="<?php echo $aramex_shipment_shipper_postal;?>" name="aramex_shipment_shipper_postal" id="aramex_shipment_shipper_postal">
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_state; ?></td>
              <td><input type="text" value="<?php echo $aramex_shipment_shipper_state;?>" name="aramex_shipment_shipper_state" id="aramex_shipment_shipper_state">
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_phone; ?></td>
              <td><input type="text" value="<?php echo $aramex_shipment_shipper_phone;?>" name="aramex_shipment_shipper_phone" id="aramex_shipment_shipper_phone">
              </td>
            </tr>
          </table>
          </fieldset></td>
        <td>
		<fieldset class="aramex_shipment_creation_fieldset">
          <legend><?php echo $text_receiver_details; ?></legend>
          <table class="aramex_shipment_creation_part form" id="receiver_details">
            <tr>
              <td><?php echo $entry_reference; ?></td>
              <td><input type="text" value="<?php echo $reference;?>" name="aramex_shipment_receiver_reference" id="aramex_shipment_receiver_reference">
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_name; ?></td>
              <td><input type="text" value="<?php echo $aramex_shipment_receiver_name;?>" name="aramex_shipment_receiver_name" id="aramex_shipment_receiver_name">
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_email; ?></td>
              <td><input type="text" value="<?php echo $aramex_shipment_receiver_email;?>" name="aramex_shipment_receiver_email" id="aramex_shipment_receiver_email">
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_company; ?></td>
              <td><input type="text" value="<?php echo $aramex_shipment_receiver_company;?>" name="aramex_shipment_receiver_company" id="aramex_shipment_receiver_company">
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_address; ?></td>
              <td><textarea name="aramex_shipment_receiver_street" id="aramex_shipment_receiver_street" type="text" cols="26" rows="4"><?php echo $aramex_shipment_receiver_street;?></textarea>
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_country; ?></td>
              <td>
			  <select name="aramex_shipment_receiver_country" id="aramex_shipment_receiver_country" class="aramex_countries">
                  
			   <?php foreach ($countries as $country) { ?>
                  <?php if ($country['iso_code_2'] == $aramex_shipment_receiver_country) { ?>
                  <option value="<?php echo $country['iso_code_2']; ?>" selected="selected"><?php echo $country['name']; ?></option>
                  <?php } else { ?>
                  <option value="<?php echo $country['iso_code_2']; ?>"><?php echo $country['name']; ?></option>
                  <?php } ?>
               <?php } ?>
                </select>
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_city; ?></td>
              <td><input type="text" value="<?php echo $aramex_shipment_receiver_city;?>" name="aramex_shipment_receiver_city" id="aramex_shipment_receiver_city" class="">
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_postal_code; ?></td>
              <td><input type="text" value="<?php echo $aramex_shipment_receiver_postal;?>" name="aramex_shipment_receiver_postal" id="aramex_shipment_receiver_postal" class="">
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_state; ?></td>
              <td><input type="text" value="<?php echo $aramex_shipment_receiver_state;?>" name="aramex_shipment_receiver_state" id="aramex_shipment_receiver_state">
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_phone; ?></td>
              <td><input type="text" value="<?php echo $aramex_shipment_receiver_phone;?>" name="aramex_shipment_receiver_phone" id="aramex_shipment_receiver_phone">
              </td>
            </tr>
          </table>
          </fieldset></td>
      </tr>
      <tr>
        <td colspan="2"><fieldset class="aramex_shipment_creation_fieldset">
          <legend><?php echo $text_shipment_information; ?></legend>
          <table class="form" >
            <tr>
              <td width="50%"><table class="aramex_shipment_creation_part form" id="shipment_infromation">
                  <tr>
                    <td><?php echo $entry_total_weight; ?></td>
                    <td><div style="display:none" class="text_short"><?php echo $entry_total_weight; ?> <span id="order-total-weight">210.00</span> <?php echo $text_kg; ?></div>
                      <input type="text" class="fl width-60 mar-right-10" value="<?php echo $weighttot;?>" name="order_weight">
                      <select class="fl width-60" name="weight_unit">
                        <option value="KG" <?php echo (strtoupper($weight_unit)=='KG')?'selected="selected"':'';?>><?php echo $text_kg; ?></option>
                        <option value="LB" <?php echo (strtoupper($weight_unit)=='LB')?'selected="selected"':'';?>><?php echo $text_lb; ?></option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $entry_reference; ?></td>
                    <td>
                      <input type="text" value="<?php echo $reference;?>" name="aramex_shipment_info_reference" id="aramex_shipment_info_reference">
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $entry_product_group; ?></td>
                    <td>
					
		
					<?php
					$checkCountry=false;
					if($group==''){
						$checkCountry=($aramex_shipment_shipper_country == $aramex_shipment_receiver_country)?true:false;
					}
					?>
					<select class="aramex_all_options" id="aramex_shipment_info_product_group" name="aramex_shipment_info_product_group"  >

						<option <?php echo ($group =='DOM' or $checkCountry) ? 'selected="selected"' : ''; ?> value="DOM"><?php echo $text_domestic; ?></option>
						<option <?php echo ($group =='EXP' or ($group=='' and !$checkCountry)) ? 'selected="selected"' : ''; ?> value="EXP"><?php echo $text_inter_express; ?></option>

					</select>
					<div id="aramex_shipment_info_product_group_div" style="display: none;"></div>
					</td>
                  </tr>
                  <tr>
                    <td><?php echo $entry_service_type; ?></td>
                    <td>
			
					<?php 
						
						$notHide='';
						if($aramex_shipment_shipper_country == $aramex_shipment_receiver_country and $type=='')
						{  
							$notHide='display: none'; 
						}
						$checkCountry=false;
						if($type==''){
							$checkCountry=($aramex_shipment_shipper_country == $aramex_shipment_receiver_country)?true:false;
						}
					?>
					<?php 
					
					
						$allowed_domestic_methods =   $aramex_allowed_domestic_methods;
						$allowed_international_methods =  $aramex_allowed_international_methods;
						$domestic_methods = $all_allowed_domestic_methods;
						$international_methods = $all_allowed_international_methods;
						$allowed_domestic_methods_apply = array();
						$allowed_international_methods_apply = array();
						//print_r($domestic_methods);
						
					?>
					<select class="aramex_all_options" id="aramex_shipment_info_product_type" name="aramex_shipment_info_product_type">
					
					        <?php if(count($allowed_domestic_methods)>0){ 
							         $i=1;
									 foreach($domestic_methods as $key=>$val){
									   if(in_array($val['value'],$allowed_domestic_methods)){
                                        $selected_str='';
										if($i==1){ 
												$selected_str =($type ==$val['value'] or $checkCountry) ? 'selected="selected"' : '';}
										else {
												$selected_str =($type ==$val['value']) ? 'selected="selected"' : '';
											}
											$allowed_domestic_methods_apply[$val['value']] = $val['label'];
											/* style="<?php echo ($group!='DOM')?'display: none':''; ?>"*/
											
 									   ?>
									    
									   <option <?php echo $selected_str; ?> value="<?php echo $val['value']; ?>" id="<?php echo $val['value'];?>"  class="DOM"><?php echo $val['label']; ?></option>
							<?php $i++;
							      } 
                                 }								
								}
							?>
							
							
							
							 <?php if(count($allowed_international_methods)>0){ 
							         $i=1;
												
									 foreach($international_methods as $key=>$val){
									   if(in_array($val['value'],$allowed_international_methods)){						    
                                        $selected_str='';
										if($i==1){ 
										          
												if($type ==$val['value'] or (!$checkCountry and $type=='')){
													$selected_str = 'selected="selected"';
												}
											}
										else {
										        if($type ==$val['value']){
													$selected_str = 'selected="selected"';
												}												
											}
											$allowed_international_methods_apply[$val['value']] = $val['label'];  
											/* style="<?php echo ($checkCountry or $group=='DOM')?'display: none':''; ?>" */
											
 									   ?>									    
									   <option <?php echo $selected_str; ?> value="<?php echo $val['value']; ?>" id="<?php echo $val['value'];?>" class="EXP"><?php echo $val['label']; ?></option>
							<?php  $i++;
							     }							    
                                 }								 
								}
							?>
							
					</select>
					<div id="aramex_shipment_info_service_type_div" style="display: none;"></div>
					
					</td>
                  </tr>
                  <tr>
                    <td><?php echo $entry_additional_services; ?></td>
                    <td>
						
					
					<select class="aramex_all_options" id="aramex_shipment_info_service_type" name="aramex_shipment_info_service_type">

						<option value=""></option>
						
						<?php 
							$allowed_domestic_additional_services =   $aramex_allowed_domestic_additional_services;
							$allowed_international_additional_services =  $aramex_allowed_international_additional_services;
							$domestic_additional_services = $all_allowed_domestic_additional_services;
							$international_additional_services = $all_allowed_international_additional_services;
						?>
						
						 <?php if(count($allowed_domestic_additional_services)>0){ 
							         $i=1;												
									 foreach($domestic_additional_services as $key=>$val){
									   if(in_array($val['value'],$allowed_domestic_additional_services)){
									      
									     ?>
										<option <?php echo ($stype == $val['value'] and $group=='DOM') ? 'selected="selected"' : ''; ?> value="<?php echo $val['value'];?>" id="dom_as_<?php echo $val['value'];?>" class="DOM local"><?php echo $val['label']; ?></option>					 
										<?php	$i++;
									   }
									  }
									} ?>
						<?php if(count($allowed_international_additional_services)>0){ 
							         $i=1;												
									 foreach($international_additional_services as $key=>$val){
									   if(in_array($val['value'],$allowed_international_additional_services)){
									      
									     ?>
										<option <?php echo ($stype == $val['value'] and $group=='DOM') ? 'selected="selected"' : ''; ?> value="<?php echo $val['value'];?>" id="exp_as_<?php echo $val['value'];?>" class="non-local EXP"><?php echo $val['label']; ?></option>									 
										<?php	$i++;
									   }
									  }
									} ?>
						
	

					</select>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $entry_payment_type; ?></td>
                    <td><select name="aramex_shipment_info_payment_type" id="aramex_shipment_info_payment_type" class="aramex_all_options">
                        <option value="P" <?php if($pay_type=='P'){ echo 'selected="selected"'; } ?>><?php echo $text_prepaid; ?></option>
						<option value="C" <?php if($pay_type=='C'){ echo 'selected="selected"'; } ?>><?php echo $text_collect; ?></option>
						<option value="3" <?php if($pay_type=='3'){ echo 'selected="selected"'; } ?>><?php echo $text_third_party; ?></option>

                      </select>
                      <div style="display: none;" id="aramex_shipment_info_service_type_div"></div></td>
                  </tr>
                  <tr>
                    <td><?php echo $entry_payment_option; ?></td>
                    <td><select name="aramex_shipment_info_payment_option" id="aramex_shipment_info_payment_option" class="">
                        <option value=""></option>
                        <option style="display: none;" value="ASCC" id="ASCC"><?php echo $text_payment_shipper; ?></option>
                        <option style="display: none;" value="ARCC" id="ARCC"><?php echo $text_payment_consignee; ?></option>
                        <option id="CASH" value="CASH" <?php if($pay_option=='CASH'){ echo 'selected="selected"'; } ?>><?php echo $text_payment_cash; ?></option>
						<option id="ACCT" value="ACCT" <?php if($pay_option=='ACCT'){ echo 'selected="selected"'; } ?>><?php echo $text_payment_account; ?></option>
						<option id="PPST" value="PPST" <?php if($pay_option=='PPST'){ echo 'selected="selected"'; } ?>><?php echo $text_payment_prepaid; ?></option>
						<option id="CRDT" value="CRDT" <?php if($pay_option=='CRDT'){ echo 'selected="selected"'; } ?>><?php echo $text_payment_credit; ?></option>

                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $entry_cod_amount; ?></td>
                    <td><input type="text" value="<?php echo $cod_value;?>" name="aramex_shipment_info_cod_amount" id="aramex_shipment_info_cod_amount">
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $entry_custom_amount; ?></td>
                    <td><input type="text" value="<?php echo $custom_amount;?>" name="aramex_shipment_info_custom_amount" id="aramex_shipment_info_custom_amount">
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $entry_currency; ?></td>
                    <td><input type="text" value="<?php echo $currency_code;?>" name="aramex_shipment_currency_code" id="aramex_shipment_currency_code">
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $entry_comment; ?></td>
                    <td><textarea name="aramex_shipment_info_comment" id="aramex_shipment_info_comment" type="text" cols="29 " rows="4"><?php echo $aramex_shipment_info_comment;?></textarea>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $entry_foreign_shipment_no; ?></td>
                    <td><input type="text" value="<?php echo $aramex_shipment_info_foreignhawb;?>" name="aramex_shipment_info_foreignhawb" id="aramex_shipment_info_foreignhawb">
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $entry_filename_1; ?></td>
                    <td><div style="float: left;width: 145px;" id="file1_div">
                        <input type="file" size="7" id="file1" name="file1">
                      </div>
                      <div style="float: right;">
                        <input type="button" style="width: 60px;height: 24px;" value="<?php echo $text_Reset; ?>" id="filereset" name="filereset">
                      </div></td>
                  </tr>
                  <tr>
                    <td><?php echo $entry_filename_2; ?></td>
                    <td><div style="float: left;width: 145px;" id="file2_div">
                        <input type="file" size="7" id="file2" name="file2">
                      </div>
                      <div style="float: right;">
                        <input type="button" style="width: 60px;height: 24px;" value="<?php echo $text_Reset; ?>" id="file2reset" name="file2reset">
                      </div></td>
                  </tr>
                  <tr>
                    <td><?php echo $entry_filename_3; ?></td>
                    <td><div style="float: left;width: 145px;" id="file3_div">
                        <input type="file" size="7" id="file3" name="file3">
                      </div>
                      <div style="float: right;">
                        <input type="button" style="width: 60px;height: 24px;" value="<?php echo $text_Reset; ?>" id="file3reset" name="file3reset">
                      </div></td>
                  </tr>
                  <tr>
                    <td><?php echo $entry_description; ?></td>
                    <td><textarea name="aramex_shipment_description" id="aramex_shipment_description" type="text" cols="31" rows="4" class="valid" style="display: none;"><?php echo implode(",",$product_arr);?></textarea>
                      <div style="float: left;font-size: 11px;margin-bottom: 5px;margin-top: 2px;width: 202px;" id="aramex_shipment_description_div">
                        <?php echo implode(",",$product_arr);?>
                      </div></td>
                  </tr>
                  <tr>
                    <td><?php echo $entry_items_price; ?></td>
                    <td><input type="text" value="<?php echo $total;?>" disabled="disabled" name="aramex_shipment_info_items_subtotal" id="aramex_shipment_info_items_subtotal">
                      <span><?php echo $currency_code;?></span></td>
                  </tr>
                </table></td>
              <td width="50%" valign="top"><table id="aramex_items_table"  class="form">
                  <tr>
                    <!--<th class="aramex_item_options">Action</th>-->
                    <th class="aramex_item_name"><?php echo $text_product_name; ?></th>
                    <th class="aramex_item_qty"><?php echo $text_product_qty; ?></th>
                  </tr>
				  
				  <?php 
				  	$qty=0;
					foreach($order_products as $op)
				  	{
						//echo $op['quantity'];
					?>
                  <tr class="aramex_item_tobe_shipped" id="item<?php echo $op['order_product_id'];?>">
                    <!--<td class="aramex_item_options"><a href="javascript:aramexhide('<?php echo $op['order_product_id'];?>', '<?php echo $op['price']*$op['quantity'];?>', '<?php echo $op['weight']*$op['quantity'];?>');">Remove</a> </td>-->
                    <td class="aramex_item_name"><span title="<?php echo $op['name'];?>"><?php echo substr($op['name'], 0, 21); ?> ...</span>
                    <input type="hidden" value="<?php echo $op['quantity'];?>" name="aramex_items[<?php echo $op['order_product_id'];?>]" id="aramex_items_<?php echo $op['order_product_id'];?>">
                    </td>
                    <td class="aramex_item_qty">
					  <input type="text"   value="<?php echo $op['quantity'];?>" name="<?php echo $op['order_product_id'];?>" class="aramex_input_items_qty valid" readonly>
                      <input type="hidden" value="<?php echo $op['price'];?>" name="aramex_items_base_price_<?php echo $op['order_product_id'];?>" id="aramex_items_base_price_<?php echo $op['order_product_id'];?>">
                      <input type="hidden" value="<?php echo $op['weight'];?>" name="aramex_items_base_weight_<?php echo $op['order_product_id'];?>" id="aramex_items_base_weight_<?php echo $op['order_product_id'];?>">
                      <input type="hidden" value="<?php echo $op['quantity'];?>" name="aramex_items_total_<?php echo $op['order_product_id'];?>" id="aramex_items_total_<?php echo $op['order_product_id'];?>">
                    </td>
                  </tr>
				  <?php
				  	$qty= $qty+$op['quantity'];
				  }
				  ?>
                 
                  <tr>
                    <td colspan="2" valign="top" style="font-weight: bold;background: none repeat scroll 0% 0% rgb(224, 224, 224);"><?php echo $entry_number_of_items; ?></td>
                    <td><span id="items_tobe_shipped_number"><?php echo $qty;?></span> </td>
                  </tr>
                </table></td>
            </tr>
          </table>
          </fieldset></td>
      </tr>
		
      <tr>
        <td colspan="2">

             
     		  <div class="buttons">
			<?php
			  if(!$is_shipment)
			  {
			?>
        	 <a class="button btn btn-primary" onclick="$('#aramex_shipment').submit();" id="aramex_shipment_creation_submit_id"><?php echo $text_create_sipment; ?></a>
 				<input type="checkbox" value="yes" name="aramex_email_customer" style="">
              <span style=""><?php echo $entry_notify_customer; ?></span>
			<?php
			}else{
			?>
			<a class="button btn btn-primary" onclick="returnCheck();" id="aramex_shipment_creation_submit_id"><?php echo $text_return_shipment; ?></a>
			<?php
			}
			?>
			<div>
           
          </td>
      </tr>
	   
    </table>
  </form>
  <script>
  
	function returnCheck(){
		if($('#payment_switcher').val() == 1 || $('#payment_switcher').val() == 3) {
		alert("<?php echo $error_consignee_account; ?>");
		}else{
		$('#aramex_shipment').submit();
		}
	}
	
	//var $ = jQuery.noConflict();
    
	var allowed_domestic_methods = <?php echo json_encode($allowed_domestic_methods); ?>;
	var allowed_international_methods = <?php echo json_encode($allowed_international_methods); ?>;
	
	var allowed_domestic_methods_apply = <?php echo json_encode($allowed_domestic_methods_apply); ?>;
	var allowed_international_methods_apply = <?php echo json_encode($allowed_international_methods_apply); ?>;

	
	var aramex_shipment_shipper_name = document.getElementById('aramex_shipment_shipper_name').value;
	var aramex_shipment_shipper_email = document.getElementById('aramex_shipment_shipper_email').value;
	var aramex_shipment_shipper_company = document.getElementById('aramex_shipment_shipper_company').value;
	var aramex_shipment_shipper_street = document.getElementById('aramex_shipment_shipper_street').value;
	var aramex_shipment_shipper_country = document.getElementById('aramex_shipment_shipper_country').value;
	var aramex_shipment_shipper_city = document.getElementById('aramex_shipment_shipper_city').value;
	var aramex_shipment_shipper_postal = document.getElementById('aramex_shipment_shipper_postal').value;
	var aramex_shipment_shipper_state = document.getElementById('aramex_shipment_shipper_state').value;
	var aramex_shipment_shipper_phone = document.getElementById('aramex_shipment_shipper_phone').value;

	var aramex_shipment_receiver_name = document.getElementById('aramex_shipment_receiver_name').value;
	var aramex_shipment_receiver_email = document.getElementById('aramex_shipment_receiver_email').value;
	var aramex_shipment_receiver_company = document.getElementById('aramex_shipment_receiver_company').value;
	var aramex_shipment_receiver_street = document.getElementById('aramex_shipment_receiver_street').value;
	var aramex_shipment_receiver_country = document.getElementById('aramex_shipment_receiver_country').value;
	var aramex_shipment_receiver_city = document.getElementById('aramex_shipment_receiver_city').value;
	var aramex_shipment_receiver_postal = document.getElementById('aramex_shipment_receiver_postal').value;
	var aramex_shipment_receiver_state = document.getElementById('aramex_shipment_receiver_state').value;
	var aramex_shipment_receiver_phone = document.getElementById('aramex_shipment_receiver_phone').value;

	function resetShipperDetail(el){
		//alert(el.value);
		var elValue = el.value;
		var flag = 0;


		if(elValue == 2){
			document.getElementById('aramex_shipment_shipper_name').value = aramex_shipment_receiver_name;
			document.getElementById('aramex_shipment_shipper_email').value = aramex_shipment_receiver_email;
			document.getElementById('aramex_shipment_shipper_company').value = aramex_shipment_receiver_company;
			document.getElementById('aramex_shipment_shipper_street').value = aramex_shipment_receiver_street;
			document.getElementById('aramex_shipment_shipper_country').value = aramex_shipment_receiver_country;
			document.getElementById('aramex_shipment_shipper_city').value = aramex_shipment_receiver_city;
			document.getElementById('aramex_shipment_shipper_postal').value = aramex_shipment_receiver_postal;
			document.getElementById('aramex_shipment_shipper_state').value = aramex_shipment_receiver_state;
			document.getElementById('aramex_shipment_shipper_phone').value = aramex_shipment_receiver_phone;
			document.getElementById('aramex_shipment_receiver_name').value = aramex_shipment_shipper_name;
			document.getElementById('aramex_shipment_receiver_email').value = aramex_shipment_shipper_email;
			document.getElementById('aramex_shipment_receiver_company').value = aramex_shipment_shipper_company;
			document.getElementById('aramex_shipment_receiver_street').value = aramex_shipment_shipper_street;
			document.getElementById('aramex_shipment_receiver_country').value = aramex_shipment_shipper_country;
			document.getElementById('aramex_shipment_receiver_city').value = aramex_shipment_shipper_city;
			document.getElementById('aramex_shipment_receiver_postal').value = aramex_shipment_shipper_postal;
			document.getElementById('aramex_shipment_receiver_state').value = aramex_shipment_shipper_state;
			document.getElementById('aramex_shipment_receiver_phone').value = aramex_shipment_shipper_phone;
			flag = 1;
		}
		else if(elValue == 3){
			document.getElementById('aramex_shipment_shipper_name').value = "";
			document.getElementById('aramex_shipment_shipper_email').value = "";
			document.getElementById('aramex_shipment_shipper_company').value = "";
			document.getElementById('aramex_shipment_shipper_street').value = "";
			document.getElementById('aramex_shipment_shipper_country').value = "";
			document.getElementById('aramex_shipment_shipper_city').value = "";
			document.getElementById('aramex_shipment_shipper_postal').value = "";
			document.getElementById('aramex_shipment_shipper_state').value = "";
			document.getElementById('aramex_shipment_shipper_phone').value = "";

			document.getElementById('aramex_shipment_info_payment_type').value = '3';

			document.getElementById('ASCC').style.display = 'block';
			document.getElementById('ARCC').style.display = 'block';

			document.getElementById('CASH').style.display = 'none';
			document.getElementById('ACCT').style.display = 'none';
			document.getElementById('PPST').style.display = 'none';
			document.getElementById('CRDT').style.display = 'none';

			$('#aramex_shipment_info_payment_option').val("");



			flag = 2;
		}
		else{
			if(flag = 1){
			document.getElementById('aramex_shipment_receiver_name').value = aramex_shipment_receiver_name;
			document.getElementById('aramex_shipment_receiver_email').value = aramex_shipment_receiver_email;
			document.getElementById('aramex_shipment_receiver_company').value = aramex_shipment_receiver_company;
			document.getElementById('aramex_shipment_receiver_street').value = aramex_shipment_receiver_street;
			document.getElementById('aramex_shipment_receiver_country').value = aramex_shipment_receiver_country;
			document.getElementById('aramex_shipment_receiver_city').value = aramex_shipment_receiver_city;
			document.getElementById('aramex_shipment_receiver_postal').value = aramex_shipment_receiver_postal;
			document.getElementById('aramex_shipment_receiver_state').value = aramex_shipment_receiver_state;
			document.getElementById('aramex_shipment_receiver_phone').value = aramex_shipment_receiver_phone;


			document.getElementById('aramex_shipment_shipper_name').value = aramex_shipment_shipper_name;
			document.getElementById('aramex_shipment_shipper_email').value = aramex_shipment_shipper_email;
			document.getElementById('aramex_shipment_shipper_company').value = aramex_shipment_shipper_company;
			document.getElementById('aramex_shipment_shipper_street').value = aramex_shipment_shipper_street;
			document.getElementById('aramex_shipment_shipper_country').value = aramex_shipment_shipper_country;
			document.getElementById('aramex_shipment_shipper_city').value = aramex_shipment_shipper_city;
			document.getElementById('aramex_shipment_shipper_postal').value = aramex_shipment_shipper_postal;
			document.getElementById('aramex_shipment_shipper_state').value = aramex_shipment_shipper_state;
			document.getElementById('aramex_shipment_shipper_phone').value = aramex_shipment_shipper_phone;

			document.getElementById('aramex_shipment_info_payment_type').value = 'P';

			document.getElementById('ASCC').style.display = 'none';
			document.getElementById('ARCC').style.display = 'none';

			document.getElementById('CASH').style.display = 'block';
			document.getElementById('ACCT').style.display = 'block';
			document.getElementById('PPST').style.display = 'block';
			document.getElementById('CRDT').style.display = 'block';

			$('#aramex_shipment_info_payment_option').val("");




			}
			else if(flag = 2){
				document.getElementById('aramex_shipment_shipper_name').value = aramex_shipment_shipper_name;
				document.getElementById('aramex_shipment_shipper_email').value = aramex_shipment_shipper_email;
				document.getElementById('aramex_shipment_shipper_company').value = aramex_shipment_shipper_company;
				document.getElementById('aramex_shipment_shipper_street').value = aramex_shipment_shipper_street;
				document.getElementById('aramex_shipment_shipper_country').value = aramex_shipment_shipper_country;
				document.getElementById('aramex_shipment_shipper_city').value = aramex_shipment_shipper_city;
				document.getElementById('aramex_shipment_shipper_postal').value = aramex_shipment_shipper_postal;
				document.getElementById('aramex_shipment_shipper_state').value = aramex_shipment_shipper_state;
				document.getElementById('aramex_shipment_shipper_phone').value = aramex_shipment_shipper_phone;

				document.getElementById('aramex_shipment_receiver_name').value = aramex_shipment_receiver_name;
				document.getElementById('aramex_shipment_receiver_email').value = aramex_shipment_receiver_email;
				document.getElementById('aramex_shipment_receiver_company').value = aramex_shipment_receiver_company;
				document.getElementById('aramex_shipment_receiver_street').value = aramex_shipment_receiver_street;
				document.getElementById('aramex_shipment_receiver_country').value = aramex_shipment_receiver_country;
				document.getElementById('aramex_shipment_receiver_city').value = aramex_shipment_receiver_city;
				document.getElementById('aramex_shipment_receiver_postal').value = aramex_shipment_receiver_postal;
				document.getElementById('aramex_shipment_receiver_state').value = aramex_shipment_receiver_state;
				document.getElementById('aramex_shipment_receiver_phone').value = aramex_shipment_receiver_phone;

				document.getElementById('aramex_shipment_info_payment_type').value = 'P';

				document.getElementById('ASCC').style.display = 'none';
				document.getElementById('ARCC').style.display = 'none';

				document.getElementById('CASH').style.display = 'block';
				document.getElementById('ACCT').style.display = 'block';
				document.getElementById('PPST').style.display = 'block';
				document.getElementById('CRDT').style.display = 'block';

				$('#aramex_shipment_info_payment_option').val("");


			}
			flag = 0;
		}
	}

	$('#aramex_shipment_info_payment_type').change(function(){
		//alert('Hello');
		if($('#aramex_shipment_info_payment_type').val() == "P"){
			document.getElementById('ASCC').style.display = 'none';
			document.getElementById('ARCC').style.display = 'none';

			document.getElementById('CASH').style.display = 'block';
			document.getElementById('ACCT').style.display = 'block';
			document.getElementById('PPST').style.display = 'block';
			document.getElementById('CRDT').style.display = 'block';

			$('#aramex_shipment_info_payment_option').val("");


		}
		else{
			document.getElementById('ASCC').style.display = 'block';
			document.getElementById('ARCC').style.display = 'block';

			document.getElementById('CASH').style.display = 'none';
			document.getElementById('ACCT').style.display = 'none';
			document.getElementById('PPST').style.display = 'none';
			document.getElementById('CRDT').style.display = 'none';

			$('#aramex_shipment_info_payment_option').val("");

		}
	});


	function togglePickup(){
		$('#pickup_infromation').toggle('slow');
	}


	function aramexpop(itemscount)
	{		
		if(itemscount >= 0){
			$("#aramex_overlay").css("display", "block");
			$("#aramex_shipment_creation").fadeIn(1000);
		}
		else{
			alert('<?php echo $error_create_shipment; ?>');
		}

	}

	function aramexclose()
	{
		$("#aramex_overlay").css("display", "none");
		$("#aramex_shipment_creation").fadeOut(500);
	}

	function aramexhide(aramexid, item_price, order_total_weight)
	{

		if($(".aramex_item_tobe_shipped").length > 1){			
			$("#order-total-weight").html((parseFloat($("#order-total-weight").html()) - parseFloat(order_total_weight)).toFixed(2));
			$("#order_weight").val((parseFloat($("#order-total-weight").html()) - parseFloat(order_total_weight)).toFixed(2));

			$("input[name=aramex_shipment_info_items_subtotal]").val((parseFloat($("input[name=aramex_shipment_info_items_subtotal]").val())-parseFloat(item_price)).toFixed(2));

			//new
			$('#aramex_shipment_info_cod_amount').val((parseFloat($('#aramex_shipment_info_cod_amount').val()) - parseFloat(parseInt($("input[name="+ aramexid +"]").val()) * parseFloat($('#aramex_items_base_price_'+aramexid).val()))).toFixed(2));

			$("#item"+aramexid).css('background', '#F2C2C8');
			$("#item"+aramexid).fadeOut(500);
			$("#item"+aramexid+" input").val(0);
			$("#item"+aramexid).remove();
			$("#"+aramexid).remove();
			$("textarea[name=aramex_shipment_description]").text($("#aramex_shipment_description_div").text());
			get_tobe_shipped_items_number();
		} else {
			alert('<?php echo $error_one_shipment; ?>');
		}
	}

	function get_tobe_shipped_items_number()
	{
		items_tobe_shipped_number = 0;
		$("#aramex_items_table .aramex_input_items_qty").each(function(qty_index){
				items_tobe_shipped_number += parseFloat($(this).val());
		});
		//items_tobe_shipped_number = $(".aramex_item_tobe_shipped").length;
		$("#items_tobe_shipped_number").html(items_tobe_shipped_number);
	}

	var codTotal = $("#aramex_shipment_info_cod_amount").val();

	$(".aramex_input_items_qty").keyup(function(){
		var the_id = $(this).attr('name');
		var items_price = 0;

		var itemWeight = 0;
		var itemTotalPrice = 0;

		$("#aramex_items_"+the_id).val($(this).val());
		$(".aramex_input_items_qty").each(function(price_index){
			var the_id_qty = $(this).attr('name'); //alert(the_id_qty);
			//alert(the_id_qty);
			items_price += $(this).val() * $("#aramex_items_base_price_"+the_id_qty).val();
			//alert(items_price)
			itemWeight += $(this).val() * $("#aramex_items_base_weight_"+the_id_qty).val();
			itemTotalPrice += (parseInt($("#aramex_items_total_"+the_id_qty).val()) - parseInt($(this).val())) * $("#aramex_items_base_price_"+the_id_qty).val();

		});
		
		//$("input[name=aramex_shipment_info_items_subtotal]").val(items_price);

		$("input[name=aramex_shipment_info_items_subtotal]").val(items_price.toFixed(2));
		$("#order-total-weight").html(itemWeight.toFixed(2));

		if(itemTotalPrice){
			$("#aramex_shipment_info_cod_amount").val((parseFloat('<?php echo $total;?>') - parseFloat(itemTotalPrice)).toFixed(2));
		}
		else if(itemTotalPrice == 0){
			$("#aramex_shipment_info_cod_amount").val(codTotal);
		}

		$("input[name=aramex_shipment_info_cod_value]").val(parseFloat(items_price) + parseFloat($("input[name=aramex_shipment_info_shipping_charges]").val()));
		get_tobe_shipped_items_number();
	});

	var messages_content;

	$("input[name=aramex_shipment_info_shipping_charges]").change(function(){
		var cod_value = parseFloat($("input[name=aramex_shipment_info_shipping_charges]").val()) + parseFloat($("input[name=aramex_shipment_info_items_subtotal]").val());
		$("input[name=aramex_shipment_info_cod_value]").val(cod_value);
	});

	$(".aramex_countries").change(function(){
		if($("select[name=aramex_shipment_shipper_country]").val() != $("select[name=aramex_shipment_receiver_country]").val()){
			$("select[name=aramex_shipment_info_product_group]").val('EXP');
			$("#aramex_shipment_info_product_group").trigger('change');			
			$("select[name=aramex_shipment_info_additional_services] option:selected").removeAttr("selected");
			$("select[name=aramex_shipment_info_additional_services] .express_service").attr("selected", "selected");

			

		} else {
			$("select[name=aramex_shipment_info_product_group]").val('DOM');
			$("#aramex_shipment_info_product_group").trigger('change');			
			$("select[name=aramex_shipment_info_additional_services] option:selected").removeAttr("selected");
			$("select[name=aramex_shipment_info_additional_services] .domestic_service").attr("selected", "selected");
			
		}

		$("#aramex_shipment_info_product_group_div").html($("select[name=aramex_shipment_info_product_group] option:selected").text());
		$("#aramex_shipment_info_service_type_div").html($("select[name=aramex_shipment_info_service_type] option:selected").text());
		$("#aramex_shipment_info_additional_services_div").html($("select[name=aramex_shipment_info_additional_services] option:selected").text());
	});

	$(".aramex_all_options").change(function(){

	});
    $("#aramex_shipment_info_product_group").change(function(){
	    
        if($("select[name=aramex_shipment_info_product_group]").val()=='EXP'){
			$("select[name=aramex_shipment_info_additional_services] option:selected").removeAttr("selected");
			$("select[name=aramex_shipment_info_additional_services] .express_service").attr("selected", "selected");
            $("#aramex_shipment_info_product_type option").hide();
			/*$(allowed_international_methods).each(function(index,val){
				 if(index==0){
					document.getElementById('aramex_shipment_info_product_type').value = val;
				 }
				 document.getElementById(val).style.display = 'block';
			});*/		
			

        }else if($("select[name=aramex_shipment_info_product_group]").val()=='DOM'){
			$("select[name=aramex_shipment_info_additional_services] option:selected").removeAttr("selected");
			$("select[name=aramex_shipment_info_additional_services] .domestic_service").attr("selected", "selected");
            $("#aramex_shipment_info_product_type option").hide();
			/*$(allowed_domestic_methods).each(function(index,val){
				 if(index==0){
					document.getElementById('aramex_shipment_info_product_type').value = val;
				 }
				 document.getElementById(val).style.display = 'block';
			});*/			

        }
		$("#aramex_shipment_info_service_type_div").html($("select[name=aramex_shipment_info_service_type] option:selected").text());
		$("#aramex_shipment_info_additional_services_div").html($("select[name=aramex_shipment_info_additional_services] option:selected").text());

     });
	$("#aramex_shipment_info_product_group_div").html($("select[name=aramex_shipment_info_product_group] option:selected").text());
	$("#aramex_shipment_info_service_type_div").html($("select[name=aramex_shipment_info_service_type] option:selected").text());
	$("#aramex_shipment_info_additional_services_div").html($("select[name=aramex_shipment_info_additional_services] option:selected").text());

	//$("#aramex_shipment").validate();

	$(document).ready(function(){
				if(($('#aramex_messages').html() != "") && ($('.error-msg'))){
					$("#aramex_overlay").css("display", "block");
					$("#aramex_shipment_creation").fadeIn(1000);
				}

				 $(function() {
					 //$( "#datepicker" ).datepicker();
					 $( "#aramex_shipment_info_pickup_date" ).datepicker({ dateFormat: "yy-mm-dd" });
					 $( "#aramex_shipment_info_ready_time" ).datepicker({ dateFormat: "yy-mm-dd" });
					 $( "#aramex_shipment_info_last_pickup_time" ).datepicker({ dateFormat: "yy-mm-dd" });
					 $( "#aramex_shipment_info_closing_time" ).datepicker({ dateFormat: "yy-mm-dd" });
					 });

				 $('#filereset').click(function(){
					 	$("#file1_div").html($("#file1_div").html());
					 });
				 $('#file2reset').click(function(){
					 	$("#file2_div").html($("#file2_div").html());
					 });
				 $('#file3reset').click(function(){
					 	$("#file3_div").html($("#file3_div").html());
					 });
				//$("#aramex_shipment").validate();

		});
		
		jQuery("#aramex_shipment_info_product_type").chained("#aramex_shipment_info_product_group");
		jQuery("#aramex_shipment_info_service_type").chained("#aramex_shipment_info_product_group");			

</script>
  <!-- code --->
</div>
</div>		
<?php echo $footer; ?>