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
  <form enctype="multipart/form-data" action="" method="post" id="calculate_rate" novalidate="novalidate">
    <table width="100%">
	 <tr>
        <td colspan="4" align="center">
			<h3>
			<?php 
			if(isset($rate_html) && !empty($rate_html))
			{
					echo $rate_html;
			}
			
			?>
			</h3>
		</td>
      </tr>
	 <tr>
        <td colspan="4" style="color:red;">
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
	 </tr>	
      <tr>
        <td>
		  <fieldset id="aramex_shipment_creation_general_info">
          <legend><?php echo $text_calc_rates; ?></legend>
		  <h3><?php echo $text_ship_origin; ?></h3>
          <table class="form">
            <tr>
              <td><span class="required">*</span><?php echo $entry_country; ?></td>
              <td>
			  
			    <select name="origin_country" id="origin_country" class="aramex_countries validate-select valid">
                 
				  <?php foreach ($countries as $country) { ?>
                  <?php if ($country['iso_code_2'] == $origin_country) { ?>
                  <option value="<?php echo $country['iso_code_2']; ?>" selected="selected"><?php echo $country['name']; ?></option>
                  <?php } else { ?>
                  <option value="<?php echo $country['iso_code_2']; ?>"><?php echo $country['name']; ?></option>
                  <?php } ?>
                  <?php } ?>
				  
                  </select>
                </td>
				<td><?php echo $entry_city; ?></td>
                <td>
					<input type="text" value="<?php echo $origin_city;?>" name="origin_city" id="origin_city">
                </td>
            
			</tr>
            <tr>
           	  <td><?php echo $entry_postal_code; ?></td>
              <td><input type="text" value="<?php echo $origin_zipcode;?>" name="origin_zipcode" id="origin_zipcode">
              </td>
			  <td><?php echo $entry_state; ?></td>
              <td><input type="text" value="<?php echo $origin_state;?>" name="origin_state" id="origin_state">
              </td>
			  
            </tr>
    
		    <tr>
              <td colspan="4"> <h3><?php echo $text_ship_dest; ?></h3></td>
		    </tr>
    
            <tr>
              <td><span class="required">*</span><?php echo $entry_country; ?></td>
              <td>
			  
			    <select name="destination_country" id="destination_country" class="aramex_countries validate-select valid">
                 
				  <?php foreach ($countries as $country) { ?>
                  <?php if ($country['iso_code_2'] == $destination_country) { ?>
                  <option value="<?php echo $country['iso_code_2']; ?>" selected="selected"><?php echo $country['name']; ?></option>
                  <?php } else { ?>
                  <option value="<?php echo $country['iso_code_2']; ?>"><?php echo $country['name']; ?></option>
                  <?php } ?>
                  <?php } ?>
				  
                  </select>
                </td>
				<td><?php echo $entry_city; ?></td>
                <td>
					<input type="text" value="<?php echo $destination_city;?>" name="destination_city" id="destination_city">
                </td>
            
			</tr>
            <tr>
           	  <td><?php echo $entry_postal_code; ?></td>
              <td><input type="text" value="<?php echo $destination_zipcode;?>" name="destination_zipcode" id="destination_zipcode">
              </td>
			  <td><?php echo $entry_state; ?></td>
              <td><input type="text" value="<?php echo $destination_state;?>" name="destination_state" id="destination_state">
              </td>
			  
            </tr>
			<tr>
				<td><span class="required">*</span><?php echo $entry_payment_type; ?></td>
                <td><select name="payment_type" id="payment_type" class="aramex_all_options">
                        <option value="P" <?php if($pay_type=='P'){ echo 'selected="selected"'; } ?>><?php echo $text_prepaid; ?></option>
						<option value="C" <?php if($pay_type=='C'){ echo 'selected="selected"'; } ?>><?php echo $text_collect; ?></option>
						<option value="3" <?php if($pay_type=='3'){ echo 'selected="selected"'; } ?>><?php echo $text_third_party; ?></option>

                      </select>
				</td>
				 <td><span class="required">*</span><?php echo $entry_product_group; ?></td>
                    <td>
					
		
					<?php
					$checkCountry=false;
					if($group==''){
						$checkCountry=($origin_country == $destination_country)?true:false;
					}
					?>
					<select class="aramex_all_options" id="product_group" name="product_group"  >

						<option <?php echo ($group =='DOM' or $checkCountry) ? 'selected="selected"' : ''; ?> value="DOM"><?php echo $text_domestic; ?></option>
						<option <?php echo ($group =='EXP' or ($group=='' and !$checkCountry)) ? 'selected="selected"' : ''; ?> value="EXP"><?php echo $text_inter_express; ?></option>

					</select>
					<div id="aramex_shipment_info_product_group_div" style="display: none;"></div>
					</td>
			</tr>
			<tr>		
					<td><span class="required">*</span><?php echo $entry_service_type; ?></td>
                    <td>
			
					<?php 
						
						$notHide='';
						if($origin_country == $destination_country and $type=='')
						{  
							$notHide='display: none'; 
						}
						$checkCountry=false;
						if($type==''){
							$checkCountry=($origin_country == $destination_country)?true:false;
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
					<select class="aramex_all_options" id="service_type" name="service_type">
					
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
					
					
					</td>
					<td><span class="required">*</span><?php echo $entry_total_weight; ?></td>
                    <td><div style="display:none" class="text_short"><?php echo $entry_total_weight; ?> <span id="order-total-weight">210.00</span> <?php echo $text_kg; ?></div>
                      <input type="text" class="fl width-60 mar-right-10" value="<?php echo $weighttot;?>" name="text_weight">
                      <select class="fl width-60" name="weight_unit">
                        <option value="KG" <?php echo (strtoupper($weight_unit)=='KG')?'selected="selected"':'';?>><?php echo $text_kg; ?></option>
                        <option value="LB" <?php echo (strtoupper($weight_unit)=='LB')?'selected="selected"':'';?>><?php echo $text_lb; ?></option>
                      </select>
                    </td>
			</tr>
			<tr>
				<tr>
                    <td><span class="required">*</span><?php echo $entry_no_of_pieces; ?></td>
                    <td>
						<input type="text" value="<?php echo $no_of_item;?>" name="total_count" class="fl" />
					</td>
                  </tr>
			</tr>
          </table>
          </fieldset>
		  </td>
      </tr>
      <tr>
        <td colspan="4" align="center">
   		    <div class="buttons">
        	    <a class="button btn btn-primary" onclick="$('#calculate_rate').submit();" id="aramex_shipment_creation_submit_id"><?php echo $text_calc_rates; ?></a>
 				
			<div>
          </td>
      </tr>
	  
    </table>
	<input type="hidden" value="<?php echo $reference;?>" name="reference" />
  </form>
  <script type="text/javascript">
			$(document).ready(function(){
					$("#service_type").chained("#product_group");
			});
  </script>
  <!-- code --->
</div>
</div>		
<?php echo $footer; ?>