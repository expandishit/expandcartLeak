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

    <?php if ($error_warning) { ?>
    <script>
        var notificationString = '<?php echo $error_warning; ?>';
        var notificationType = 'warning';
    </script>
    <?php } ?>

  <div class="box">
    <div class="heading">
      <h1><?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button btn btn-primary"><?php echo $button_save; ?></a><a onclick="$('#action').val('save_stay');$('#form').submit();" class="button btn btn-primary"><?php echo $button_save_stay; ?></a><a onclick="location = '<?php echo $cancel; ?>';" class="button btn btn-primary"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <input type="hidden" name="action" id="action" value=""/>
         <div id="tabs" class="htabs">
            <a href="#tab-general-setting"><?php echo $this->language->get("tab_general_setting"); ?></a>
            <a href="#tab-advanced-setting"><?php echo $this->language->get("tab_block_advanced"); ?></a>
            <a href="#tab-block-position"><?php echo $this->language->get("tab_block_position"); ?></a>
        </div>
            

         <div id="tab-contents">
            <div id="tab-general-setting">
                <div class="tab-inner">
                   <table class="form">
                      <tr><td colspan="2"><?php echo $this->language->get("text_deal_mode_description");?></td></tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_enable_today_deal"); ?></td>
                          <td><select name="ecdeals_setting[mini_mode]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['mini_mode']) && $key == $general['mini_mode']){
                                  ?>
                                  <option value="<?php echo $key; ?>" selected="selected"><?php echo $val;?></option>
                                  <?php
                                  }else{
                                     ?>
                                  <option value="<?php echo $key; ?>"><?php echo $val;?></option>
                                  <?php
                                  }
                                }
                              }
                            ?>
                          </select></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_show_menu_deals"); ?></td>
                          <td><select name="ecdeals_setting[show_menu]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['show_menu']) && $key == $general['show_menu']){
                                  ?>
                                  <option value="<?php echo $key; ?>" selected="selected"><?php echo $val;?></option>
                                  <?php
                                  }else{
                                     ?>
                                  <option value="<?php echo $key; ?>"><?php echo $val;?></option>
                                  <?php
                                  }
                                }
                              }
                            ?>
                          </select></td>
                        </tr>
                        <tr>
                        <td><?php echo $this->language->get("entry_show_deals_in_categories"); ?></td>
                        <td><select name="ecdeals_setting[show_category_id][]" multiple="multiple" size="10" onclick="checkSelect(this)">
                          <option value="0"><?php echo $this->language->get("text_all_category");?></opion>
                            <?php
                             if($categories){
                              $general['show_category_id'] = isset($general['show_category_id'])?$general['show_category_id']:array();
                              foreach($categories as $category){
                                if(is_array($general['show_category_id']) && in_array($category['category_id'], $general['show_category_id'])){
                                  ?>
                                  <option value="<?php echo $category['category_id'];?>" selected="selected"><?php echo $category['name'];?></option>
                                  <?php
                                }else{
                                  ?>
                                  <option value="<?php echo $category['category_id'];?>"><?php echo $category['name'];?></option>
                                  <?php
                                }
                              }
                             }
                            ?>
                          </select></td>
                      </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_deal_block_width"); ?></td>
                          <td><input type="text" name="ecdeals_setting[deal_block_width]" value="<?php echo isset($general['deal_block_width'])?$general['deal_block_width']:"340px";?>"/></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_deal_image_width_height"); ?></td>
                          <td><input type="text" name="ecdeals_setting[deal_image_width]" value="<?php echo isset($general['deal_image_width'])?$general['deal_image_width']:200;?>"/> x <input type="text" name="ecdeals_setting[deal_image_height]" value="<?php echo isset($general['deal_image_height'])?$general['deal_image_height']:100;?>"/></td>
                        </tr>
                        <tr>
                          <td><?php echo $entry_expire_date_format; ?></td>
                          <td><input type="text" name="ecdeals_setting[date_format]" value="<?php echo isset($general['date_format'])?$general['date_format']:"Y-m-d H:i:s";?>"/></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_min_day"); ?></td>
                          <td><input type="text" name="ecdeals_setting[min_day]" value="<?php echo isset($general['min_day'])?$general['min_day']:3;?>"/></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_min_quantity"); ?></td>
                          <td><input type="text" name="ecdeals_setting[min_quantity]" value="<?php echo isset($general['min_quantity'])?$general['min_quantity']:10;?>"/></td>
                        </tr>

                        <tr>
                          <td><?php echo $this->language->get("entry_order_status_id"); ?></td>
                          <td><select name="ecdeals_setting[order_status_id][]" multiple="multiple" size="10">
                            <?php
                              if(!empty($order_status)){
                                $general['order_status_id'] = isset($general['order_status_id'])?$general['order_status_id']:5;
                                $general['order_status_id'] = array($general['order_status_id'])?$general['order_status_id']:array($general['order_status_id']);
                                foreach($order_status as $key=>$val){
                                  if(isset($general['order_status_id']) && in_array($val['order_status_id'], $general['order_status_id'])){
                                  ?>
                                  <option value="<?php echo $val['order_status_id']; ?>" selected="selected"><?php echo $val['name'];?></option>
                                  <?php
                                  }else{
                                     ?>
                                  <option value="<?php echo $val['order_status_id']; ?>"><?php echo $val['name'];?></option>
                                  <?php
                                  }
                                }
                              }
                            ?>
                          </select></td>
                        </tr>
                        <tr style="display: none;">
                          <td><?php echo $this->language->get("entry_deal_template_in_detail"); ?></td>
                          <td><select name="ecdeals_setting[deal_template]">
                            <?php
                              if(!empty($themes)){
                                foreach($themes as $key=>$val){
                                  if(isset($general['deal_template']) && $key == $general['deal_template']){
                                  ?>
                                  <option value="<?php echo $key; ?>" selected="selected"><?php echo $val;?></option>
                                  <?php
                                  }else{
                                     ?>
                                  <option value="<?php echo $key; ?>"><?php echo $val;?></option>
                                  <?php
                                  }
                                }
                              }
                            ?>
                          </select></td>
                        </tr>
                        <tr style="display: none;">
                          <td><?php echo $this->language->get("entry_deal_template_in_list"); ?></td>
                          <td><select name="ecdeals_setting[list_deal_template]">
                            <?php
                              if(!empty($themes)){
                                foreach($themes as $key=>$val){
                                  if(isset($general['list_deal_template']) && $key == $general['list_deal_template']){
                                  ?>
                                  <option value="<?php echo $key; ?>" selected="selected"><?php echo $val;?></option>
                                  <?php
                                  }else{
                                     ?>
                                  <option value="<?php echo $key; ?>"><?php echo $val;?></option>
                                  <?php
                                  }
                                }
                              }
                            ?>
                          </select></td>
                        </tr>
                        
                        <tr>
                          <td><?php echo $this->language->get("entry_keyword_seo"); ?></td>
                          <td><input type="text" name="ecdeals_setting[keyword_seo]" value="<?php echo isset($general['keyword_seo'])?$general['keyword_seo']:"deals"; ?>" size="40"/></td>
                        </tr>
              </table>
              <h3><span><?php echo $this->language->get("entry_deal_description_on_listing");?></span></h3>
                <div class="tab-inner">
                  <div id="language-1" class="htabs">
                      <?php foreach ($languages as $language) { ?>
                      <a href="#tab-language-1-<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
                      <?php } ?>
                    </div>
                    <?php foreach ($languages as $language) { ?>
                    <div id="tab-language-1-<?php echo $language['language_id']; ?>">
                      <table class="form">
                        <tr>
                          <td><?php echo $this->language->get("entry_description"); ?></td>
                          <td><textarea name="ecdeals_setting[deal_description][<?php echo $language['language_id']; ?>]" id="description-1-<?php echo $language['language_id']; ?>"><?php echo isset($general['deal_description'][$language['language_id']]) ? $general['deal_description'][$language['language_id']] : ''; ?></textarea>
                          </td>
                        </tr>
                         
                      </table>
                    </div>
                  <?php } ?>
                  <table class="form">

                    <tr>
                        <td><?php echo $this->language->get("entry_image"); ?></td>
                        <td class="left"><div class="image"><img src="<?php echo isset($general['thumb'])?$general['thumb']:""; ?>" alt="" id="thumb1" />
                            <input type="hidden" name="ecdeals_setting[deal_image]" value="<?php echo isset($general['image'])?$general['image']:""; ?>" id="image1"  />
                            <br />
                            <a onclick="image_upload('image1', 'thumb1');"><?php echo $this->language->get("text_browse"); ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="$('#thumb1').attr('src', '<?php echo $no_image; ?>'); $('#image1').attr('value', '');"><?php echo $this->language->get("text_clear"); ?></a></div></td>
                      </tr>
                       <tr>
                          <td><?php echo $this->language->get("text_image_size"); ?></td>
                          <td><input type="text" name="ecdeals_setting[meta_deal_image_width]" value="<?php echo isset($general['meta_deal_image_width'])?$general['meta_deal_image_width']:"200"; ?>" size="12"/> x <input type="text" name="ecdeals_setting[meta_deal_image_height]" value="<?php echo isset($general['meta_deal_image_height'])?$general['meta_deal_image_height']:"100"; ?>" size="12"/></td>
                        </tr>
                       <tr>
                          <td><?php echo $this->language->get("entry_meta_description"); ?></td>
                          <td><textarea name="ecdeals_setting[deal_meta_description]" cols="40" rows="5"><?php echo isset($general['deal_meta_description']) ? $general['deal_meta_description'] : ''; ?></textarea>
                          </td>
                        </tr>
                         <tr>
                          <td><?php echo $this->language->get("entry_meta_keyword"); ?></td>
                          <td><textarea name="ecdeals_setting[deal_meta_keyword]" cols="40" rows="5"><?php echo isset($general['deal_meta_keyword']) ? $general['deal_meta_keyword'] : ''; ?></textarea>
                          </td>
                        </tr>  
                  </table>
                </div>
                </div>
            </div>
            <div id="tab-advanced-setting">
                <div class="tab-inner">
                   <table class="form">
                      <tr>
                            <td><?php echo $this->language->get("entry_columns_on_listing"); ?></td>
                            <td><select name="ecdeals_setting[cols]">
                              <?php
                                if(!empty($columns)){
                                  $general['cols'] = isset($general['cols'])?$general['cols']:3;
                                  foreach($columns as $key=>$val){
                                    if(isset($general['cols']) && $val == $general['cols']){
                                    ?>
                                    <option value="<?php echo $val; ?>" selected="selected"><?php echo $val." ".$this->language->get("text_column");?></option>
                                    <?php
                                    }else{
                                       ?>
                                    <option value="<?php echo $val; ?>"><?php echo $val." ".$this->language->get("text_column");?></option>
                                    <?php
                                    }
                                  }
                                }
                              ?>
                            </select></td>
                      </tr>
                       <tr>
                          <td><?php echo $this->language->get("entry_show_expired_deal"); ?></td>
                          <td><select name="ecdeals_setting[show_expired_deal]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['show_expired_deal']) && $key == $general['show_expired_deal']){
                                  ?>
                                  <option value="<?php echo $key; ?>" selected="selected"><?php echo $val;?></option>
                                  <?php
                                  }else{
                                     ?>
                                  <option value="<?php echo $key; ?>"><?php echo $val;?></option>
                                  <?php
                                  }
                                }
                              }
                            ?>
                          </select></td>
                        </tr>
                         <tr>
                          <td><?php echo $this->language->get("entry_allow_customer_buy_expired_deals"); ?></td>
                          <td><select name="ecdeals_setting[allow_buy]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['allow_buy']) && $key == $general['allow_buy']){
                                  ?>
                                  <option value="<?php echo $key; ?>" selected="selected"><?php echo $val;?></option>
                                  <?php
                                  }else{
                                     ?>
                                  <option value="<?php echo $key; ?>"><?php echo $val;?></option>
                                  <?php
                                  }
                                }
                              }
                            ?>
                          </select></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_enable_deal_image"); ?></td>
                          <td><select name="ecdeals_setting[enable_deal_image]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['enable_deal_image']) && $key == $general['enable_deal_image']){
                                  ?>
                                  <option value="<?php echo $key; ?>" selected="selected"><?php echo $val;?></option>
                                  <?php
                                  }else{
                                     ?>
                                  <option value="<?php echo $key; ?>"><?php echo $val;?></option>
                                  <?php
                                  }
                                }
                              }
                            ?>
                          </select></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_enable_deal_name"); ?></td>
                          <td><select name="ecdeals_setting[enable_deal_name]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['enable_deal_name']) && $key == $general['enable_deal_name']){
                                  ?>
                                  <option value="<?php echo $key; ?>" selected="selected"><?php echo $val;?></option>
                                  <?php
                                  }else{
                                     ?>
                                  <option value="<?php echo $key; ?>"><?php echo $val;?></option>
                                  <?php
                                  }
                                }
                              }
                            ?>
                          </select></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_enable_deal_price"); ?></td>
                          <td><select name="ecdeals_setting[enable_deal_price]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['enable_deal_price']) && $key == $general['enable_deal_price']){
                                  ?>
                                  <option value="<?php echo $key; ?>" selected="selected"><?php echo $val;?></option>
                                  <?php
                                  }else{
                                     ?>
                                  <option value="<?php echo $key; ?>"><?php echo $val;?></option>
                                  <?php
                                  }
                                }
                              }
                            ?>
                          </select></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_enable_buy_now"); ?></td>
                          <td><select name="ecdeals_setting[enable_buy_now]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['enable_buy_now']) && $key == $general['enable_buy_now']){
                                  ?>
                                  <option value="<?php echo $key; ?>" selected="selected"><?php echo $val;?></option>
                                  <?php
                                  }else{
                                     ?>
                                  <option value="<?php echo $key; ?>"><?php echo $val;?></option>
                                  <?php
                                  }
                                }
                              }
                            ?>
                          </select></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_enable_discount"); ?></td>
                          <td><select name="ecdeals_setting[enable_discount]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['enable_discount']) && $key == $general['enable_discount']){
                                  ?>
                                  <option value="<?php echo $key; ?>" selected="selected"><?php echo $val;?></option>
                                  <?php
                                  }else{
                                     ?>
                                  <option value="<?php echo $key; ?>"><?php echo $val;?></option>
                                  <?php
                                  }
                                }
                              }
                            ?>
                          </select></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_show_rating"); ?></td>
                          <td><select name="ecdeals_setting[show_rating]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['show_rating']) && $key == $general['show_rating']){
                                  ?>
                                  <option value="<?php echo $key; ?>" selected="selected"><?php echo $val;?></option>
                                  <?php
                                  }else{
                                     ?>
                                  <option value="<?php echo $key; ?>"><?php echo $val;?></option>
                                  <?php
                                  }
                                }
                              }
                            ?>
                          </select></td>
                        </tr>
                         <tr>
                          <td><?php echo $this->language->get("entry_show_social"); ?></td>
                          <td><select name="ecdeals_setting[show_social]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['show_social']) && $key == $general['show_social']){
                                  ?>
                                  <option value="<?php echo $key; ?>" selected="selected"><?php echo $val;?></option>
                                  <?php
                                  }else{
                                     ?>
                                  <option value="<?php echo $key; ?>"><?php echo $val;?></option>
                                  <?php
                                  }
                                }
                              }
                            ?>
                          </select></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_show_notify_message"); ?></td>
                          <td><select name="ecdeals_setting[show_notify_message]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['show_notify_message']) && $key == $general['show_notify_message']){
                                  ?>
                                  <option value="<?php echo $key; ?>" selected="selected"><?php echo $val;?></option>
                                  <?php
                                  }else{
                                     ?>
                                  <option value="<?php echo $key; ?>"><?php echo $val;?></option>
                                  <?php
                                  }
                                }
                              }
                            ?>
                          </select></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_show_reward_point"); ?></td>
                          <td><select name="ecdeals_setting[show_reward_point]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['show_reward_point']) && $key == $general['show_reward_point']){
                                  ?>
                                  <option value="<?php echo $key; ?>" selected="selected"><?php echo $val;?></option>
                                  <?php
                                  }else{
                                     ?>
                                  <option value="<?php echo $key; ?>"><?php echo $val;?></option>
                                  <?php
                                  }
                                }
                              }
                            ?>
                          </select></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_show_stock_bar"); ?></td>
                          <td><select name="ecdeals_setting[show_stock_bar]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['show_stock_bar']) && $key == $general['show_stock_bar']){
                                  ?>
                                  <option value="<?php echo $key; ?>" selected="selected"><?php echo $val;?></option>
                                  <?php
                                  }else{
                                     ?>
                                  <option value="<?php echo $key; ?>"><?php echo $val;?></option>
                                  <?php
                                  }
                                }
                              }
                            ?>
                          </select></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_show_tab_active_deals"); ?></td>
                          <td><select name="ecdeals_setting[show_tab_active]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['show_tab_active']) && $key == $general['show_tab_active']){
                                  ?>
                                  <option value="<?php echo $key; ?>" selected="selected"><?php echo $val;?></option>
                                  <?php
                                  }else{
                                     ?>
                                  <option value="<?php echo $key; ?>"><?php echo $val;?></option>
                                  <?php
                                  }
                                }
                              }
                            ?>
                          </select></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_show_tab_past_deals"); ?></td>
                          <td><select name="ecdeals_setting[show_tab_past]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['show_tab_past']) && $key == $general['show_tab_past']){
                                  ?>
                                  <option value="<?php echo $key; ?>" selected="selected"><?php echo $val;?></option>
                                  <?php
                                  }else{
                                     ?>
                                  <option value="<?php echo $key; ?>"><?php echo $val;?></option>
                                  <?php
                                  }
                                }
                              }
                            ?>
                          </select></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_show_tab_upcomming_deals"); ?></td>
                          <td><select name="ecdeals_setting[show_tab_upcomming]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['show_tab_upcomming']) && $key == $general['show_tab_upcomming']){
                                  ?>
                                  <option value="<?php echo $key; ?>" selected="selected"><?php echo $val;?></option>
                                  <?php
                                  }else{
                                     ?>
                                  <option value="<?php echo $key; ?>"><?php echo $val;?></option>
                                  <?php
                                  }
                                }
                              }
                            ?>
                          </select></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_show_tab_today_deals"); ?></td>
                          <td><select name="ecdeals_setting[show_tab_today]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['show_tab_today']) && $key == $general['show_tab_today']){
                                  ?>
                                  <option value="<?php echo $key; ?>" selected="selected"><?php echo $val;?></option>
                                  <?php
                                  }else{
                                     ?>
                                  <option value="<?php echo $key; ?>"><?php echo $val;?></option>
                                  <?php
                                  }
                                }
                              }
                            ?>
                          </select></td>
                        </tr>
                   </table>
                </div>
            </div>
            
            <div id="tab-block-position">
                <div class="tab-inner">
                  <div class="vtabs">
                    <?php $module_row = 1; ?>
                    <?php foreach ($modules as $module) { ?>

                    <a href="#tab-module-<?php echo $module_row; ?>" id="module-<?php echo $module_row; ?>"><?php echo $tab_block . ' ' . $module_row; ?>&nbsp;<img src="view/image/delete.png" alt="" onclick="$('.vtabs a:first').trigger('click'); $('#module-<?php echo $module_row; ?>').remove(); $('#tab-module-<?php echo $module_row; ?>').remove(); return false;" /></a>
                    <?php $module_row++; ?>
                    <?php } ?>
                    <span id="module-add"><?php echo $button_add_new_block; ?>&nbsp;<img src="view/image/add.png" alt="" onclick="addModule();" /></span> 
                  </div>
                  <?php $module_row = 1; ?>
                  <?php foreach ($modules as $module) { ?>
                  
                  <div id="tab-module-<?php echo $module_row; ?>" class="vtabs-content">
                    <div id="language-module-<?php echo $module_row; ?>" class="htabs">
                      <?php foreach ($languages as $language) { ?>
                      <a href="#tab-language-module-<?php echo $module_row; ?>-<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
                      <?php } ?>
                    </div>
                    <?php foreach ($languages as $language) { ?>
                      <div id="tab-language-module-<?php echo $module_row; ?>-<?php echo $language['language_id']; ?>">
                        <table class="form">
                          <tr>
                            <td><?php echo $this->language->get( 'entry_title' ); ?></td>
                            <td><input size="60" name="ecdeals_module[<?php echo $module_row; ?>][title][<?php echo $language['language_id']; ?>]" id="title-<?php echo $module_row; ?>-<?php echo $language['language_id']; ?>" value="<?php echo isset($module['title'][$language['language_id']]) ? $module['title'][$language['language_id']] : ''; ?>"/></td>
                          </tr>
                          
                          <tr>
                            <td><?php echo $this->language->get('entry_description'); ?></td>
                            <td><textarea name="ecdeals_module[<?php echo $module_row; ?>][description][<?php echo $language['language_id']; ?>]" id="description-module-<?php echo $module_row; ?>-<?php echo $language['language_id']; ?>"><?php echo isset($module['description'][$language['language_id']]) ? $module['description'][$language['language_id']] : ''; ?></textarea></td>
                          </tr>
                        </table>
                      </div>
                      <?php } ?>
                    <input type="hidden" name="ecdeals_module[<?php echo $module_row; ?>][module_id]" value="<?php echo isset($module['module_id'])?$module['module_id']:$module_row;?>"/>
                    <table class="form">
                      <tr>
                        <td><?php echo $entry_layout; ?></td>
                        <td><select name="ecdeals_module[<?php echo $module_row; ?>][layout_id]">
                            <?php if ($module['layout_id'] == 0) { ?>
                            <option value="0" selected="selected"><?php echo $text_alllayout; ?></option>
                            <?php } else { ?>
                            <option value="0"><?php echo $text_alllayout; ?></option>
                            <?php } ?>
                            <?php foreach ($layouts as $layout) { ?>
                            <?php if ($layout['layout_id'] == $module['layout_id']) { ?>
                            <option value="<?php echo $layout['layout_id']; ?>" selected="selected"><?php echo $layout['name']; ?></option>
                            <?php } else { ?>
                            <option value="<?php echo $layout['layout_id']; ?>"><?php echo $layout['name']; ?></option>
                            <?php } ?>
                            <?php } ?>
                          </select></td>
                      </tr>
                <tr>
                  <td><?php echo $entry_store; ?></td>
                        <td><div class="scrollbox">
                            <?php $class = 'even'; ?>
                            <div class="<?php echo $class; ?>">
                              <?php if (isset($module['store_id']) && in_array(0, $module['store_id'])) { ?>
                              <input type="checkbox" name="ecdeals_module[<?php echo $module_row; ?>][store_id][]" value="0" checked="checked" />
                              <?php } else { ?>
                              <input type="checkbox" name="ecdeals_module[<?php echo $module_row; ?>][store_id][]" value="0" />
                              <?php } ?>
                    <?php echo $text_default; ?>
                            </div>
                            <?php foreach ($stores as $store) { ?>
                            <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                            <div class="<?php echo $class; ?>">
                              <?php if (isset($module['store_id']) && in_array($store['store_id'], $module['store_id'])) { ?>
                              <input type="checkbox" name="ecdeals_module[<?php echo $module_row; ?>][store_id][]" value="<?php echo $store['store_id']; ?>" checked="checked" />
                              <?php echo $store['name']; ?>
                              <?php } else { ?>
                              <input type="checkbox" name="ecdeals_module[<?php echo $module_row; ?>][store_id][]" value="<?php echo $store['store_id']; ?>" />
                              <?php echo $store['name']; ?>
                              <?php } ?>
                            </div>
                            <?php } ?>
                          </div></td>
                      </tr>
                      <tr>
                        <td><?php echo $entry_position; ?></td>
                         <?php 
                          $custom_position = (isset($module['custom_position']) && !empty($module['custom_position']))?$module['custom_position']:'';
                          $tmp_positions = $positions;
                          if(!empty($custom_position)){
                            $tmp_positions[] = $custom_position;
                          }
                          
                          ?>
                        <td><select name="ecdeals_module[<?php echo $module_row; ?>][position]">
                                     <?php foreach( $tmp_positions as $pos ) { ?>
                                              <?php if ($module['position'] == $pos) { ?>
                                              <option value="<?php echo $pos;?>" selected="selected"><?php echo $this->language->get('text_'.$pos); ?></option>
                                              <?php } else { ?>
                                              <option value="<?php echo $pos;?>"><?php echo $this->language->get('text_'.$pos); ?></option>
                                              <?php } ?>
                                              <?php } ?> 
                                            </select></td>
                      </tr>
                      <tr>
                        <td><?php echo $this->language->get("entry_custom_position"); ?></td>
                        <td><input type="text" name="ecdeals_module[<?php echo $module_row; ?>][custom_position]" value="<?php echo $custom_position; ?>" size="30" /></td>
                      </tr>
                      <tr>
                        <td><?php echo $entry_status; ?></td>
                        <td><select name="ecdeals_module[<?php echo $module_row; ?>][status]">
                            <?php if ($module['status']) { ?>
                            <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                            <option value="0"><?php echo $text_disabled; ?></option>
                            <?php } else { ?>
                            <option value="1"><?php echo $text_enabled; ?></option>
                            <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                            <?php } ?>
                          </select></td>
                      </tr>
                      <tr>
                        <td><?php echo $entry_sort_order; ?></td>
                        <td><input type="text" name="ecdeals_module[<?php echo $module_row; ?>][sort_order]" value="<?php echo isset($module['sort_order'])?$module['sort_order']:''; ?>" size="3" /></td>
                      </tr>
                    </table>
                    
                    <table class="form">
                      
                        <tr>
                          <td colspan="2"><h3><?php echo $this->language->get("entry_module_setting"); ?></h3></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_block_prefix"); ?></td>
                          <td><input size="30" type="text" name="ecdeals_module[<?php echo $module_row; ?>][prefix]" value="<?php echo isset($module['prefix'])?$module['prefix']:""; ?>"/></td>
                       </tr>
                       <tr>
                        <td><?php echo $this->language->get("entry_limit_deals"); ?></td>
                        <td><input type="text" name="ecdeals_module[<?php echo $module_row; ?>][limit_deals]" value="<?php echo isset($module['limit_deals'])?$module['limit_deals']:"12"; ?>" size="10" /></td>
                      </tr>
                      <tr>
                        <td><?php echo $this->language->get("entry_show_featured"); ?></td>
                        <td><select name="ecdeals_module[<?php echo $module_row; ?>][show_featured]">
                           <?php
                           foreach($featureds as $key=>$val){
                            if(isset($module['show_featured']) && $key == $module['show_featured']){
                              ?>
                              <option value="<?php echo $key; ?>" selected="selected"><?php echo $val; ?></option>
                              <?php
                            }else{
                              ?>
                               <option value="<?php echo $key; ?>"><?php echo $val; ?></option>
                              <?php
                            }
                           }
                           ?>
                          </select></td>
                      </tr>
                      <tr>
                        <td><?php echo $this->language->get("entry_category_mode"); ?></td>
                        <td><select name="ecdeals_module[<?php echo $module_row; ?>][category_mode]">
                           <?php
                           foreach($yesno as $key=>$val){
                            if(isset($module['category_mode']) && $key == $module['category_mode']){
                              ?>
                              <option value="<?php echo $key; ?>" selected="selected"><?php echo $val; ?></option>
                              <?php
                            }else{
                              ?>
                               <option value="<?php echo $key; ?>"><?php echo $val; ?></option>
                              <?php
                            }
                           }
                           ?>
                          </select></td>
                      </tr>
                      <tr>
                        <td><?php echo $this->language->get("entry_filter_category"); ?></td>
                        <td><select name="ecdeals_module[<?php echo $module_row; ?>][category_id][]" multiple="multiple" size="10">
                          <option value=""><?php echo $this->language->get("text_select_category_to_filter");?></opion>
                            <?php

                             if($categories){
                              $module['category_id'] = isset($module['category_id'])?$module['category_id']:array();
                              foreach($categories as $category){
                                if(is_array($module['category_id']) && in_array($category['category_id'], $module['category_id'])){
                                  ?>
                                  <option value="<?php echo $category['category_id'];?>" selected="selected"><?php echo $category['name'];?></option>
                                  <?php
                                }else{
                                  ?>
                                  <option value="<?php echo $category['category_id'];?>"><?php echo $category['name'];?></option>
                                  <?php
                                }
                              }
                             }
                            ?>
                          </select></td>
                      </tr>
                      <tr>
                        <td><?php echo $this->language->get("entry_show_deals_from"); ?></td>
                        <td><select name="ecdeals_module[<?php echo $module_row; ?>][deal_mode]">
                          <option value=""><?php echo $this->language->get("text_select_mode");?></opion>
                            <?php

                             if($modes){
                              $module['deal_mode'] = isset($module['deal_mode'])?$module['deal_mode']:"";
                              foreach($modes as $val=>$text){
                                if($val == $module['deal_mode']){
                                  ?>
                                  <option value="<?php echo $val;?>" selected="selected"><?php echo $text;?></option>
                                  <?php
                                }else{
                                  ?>
                                  <option value="<?php echo $val;?>"><?php echo $text;?></option>
                                  <?php
                                }
                              }
                             }
                            ?>
                          </select></td>
                      </tr>
                        <tr>
                          <td><?php echo $this->language->get("text_deal_image_width_height"); ?></td>
                          <td><input type="text" name="ecdeals_module[<?php echo $module_row; ?>][deal_image_width]" value="<?php echo isset($module['deal_image_width'])?$module['deal_image_width']:"200"; ?>" size="12"/> - <input type="text" name="ecdeals_module[<?php echo $module_row; ?>][deal_image_height]" value="<?php echo isset($module['deal_image_height'])?$module['deal_image_height']:"150"; ?>" size="12"/></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("text_items_cols"); ?></td>
                          <td><input type="text" name="ecdeals_module[<?php echo $module_row; ?>][item_cols]" value="<?php echo isset($module['item_cols'])?$module['item_cols']:"3"; ?>" size="12"/></td>
                        </tr>
                         <tr>
                        <td><?php echo $this->language->get("entry_show_module_title"); ?></td>
                        <td><select name="ecdeals_module[<?php echo $module_row; ?>][show_module_title]">
                            <?php if ($module['show_module_title']) { ?>
                            <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                            <option value="0"><?php echo $text_disabled; ?></option>
                            <?php } else { ?>
                            <option value="1"><?php echo $text_enabled; ?></option>
                            <option value="0"  selected="selected"><?php echo $text_disabled; ?></option>
                            <?php } ?>
                          </select></td>
                      </tr>
                       <tr>
                        <td><?php echo $this->language->get("entry_show_description"); ?></td>
                        <td><select name="ecdeals_module[<?php echo $module_row; ?>][show_module_description]">
                            <?php if ($module['show_module_description']) { ?>
                            <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                            <option value="0"><?php echo $text_disabled; ?></option>
                            <?php } else { ?>
                            <option value="1"><?php echo $text_enabled; ?></option>
                            <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                            <?php } ?>
                          </select></td>
                      </tr>
                        <tr>
                        <td><?php echo $this->language->get("entry_show_deal_image"); ?></td>
                        <td><select name="ecdeals_module[<?php echo $module_row; ?>][enable_deal_image]">
                            <?php if ($module['enable_deal_image']) { ?>
                            <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                            <option value="0"><?php echo $text_disabled; ?></option>
                            <?php } else { ?>
                            <option value="1"><?php echo $text_enabled; ?></option>
                            <option value="0"  selected="selected"><?php echo $text_disabled; ?></option>
                            <?php } ?>
                          </select></td>
                      </tr>
                      <tr>
                        <td><?php echo $this->language->get("entry_show_name"); ?></td>
                        <td><select name="ecdeals_module[<?php echo $module_row; ?>][enable_deal_name]">
                            <?php if ($module['enable_deal_name']) { ?>
                            <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                            <option value="0"><?php echo $text_disabled; ?></option>
                            <?php } else { ?>
                            <option value="1"><?php echo $text_enabled; ?></option>
                            <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                            <?php } ?>
                          </select></td>
                      </tr>
                      
                      <tr>
                        <td><?php echo $this->language->get("entry_show_discount"); ?></td>
                        <td><select name="ecdeals_module[<?php echo $module_row; ?>][enable_discount]">
                            <?php if ($module['enable_discount']) { ?>
                            <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                            <option value="0"><?php echo $text_disabled; ?></option>
                            <?php } else { ?>
                            <option value="1"><?php echo $text_enabled; ?></option>
                            <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                            <?php } ?>
                          </select></td>
                      </tr>
                      <tr>
                        <td><?php echo $this->language->get("entry_show_social"); ?></td>
                        <td><select name="ecdeals_module[<?php echo $module_row; ?>][show_social]">
                            <?php if ($module['show_social']) { ?>
                            <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                            <option value="0"><?php echo $text_disabled; ?></option>
                            <?php } else { ?>
                            <option value="1"><?php echo $text_enabled; ?></option>
                            <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                            <?php } ?>
                          </select></td>
                      </tr>
                      <tr>
                        <td><?php echo $this->language->get("entry_show_rating"); ?></td>
                        <td><select name="ecdeals_module[<?php echo $module_row; ?>][show_rating]">
                            <?php if ($module['show_rating']) { ?>
                            <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                            <option value="0"><?php echo $text_disabled; ?></option>
                            <?php } else { ?>
                            <option value="1"><?php echo $text_enabled; ?></option>
                            <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                            <?php } ?>
                          </select></td>
                      </tr>
                      <tr>
                        <td><?php echo $this->language->get("entry_show_reward_point"); ?></td>
                        <td><select name="ecdeals_module[<?php echo $module_row; ?>][show_reward_point]">
                            <?php if ($module['show_reward_point']) { ?>
                            <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                            <option value="0"><?php echo $text_disabled; ?></option>
                            <?php } else { ?>
                            <option value="1"><?php echo $text_enabled; ?></option>
                            <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                            <?php } ?>
                          </select></td>
                      </tr>
                      <tr>
                        <td><?php echo $this->language->get("entry_enable_deal_price"); ?></td>
                        <td><select name="ecdeals_module[<?php echo $module_row; ?>][enable_deal_price]">
                            <?php if ($module['enable_deal_price']) { ?>
                            <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                            <option value="0"><?php echo $text_disabled; ?></option>
                            <?php } else { ?>
                            <option value="1"><?php echo $text_enabled; ?></option>
                            <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                            <?php } ?>
                          </select></td>
                      </tr>
                      
                      <tr>
                          <td colspan="2"><h3><?php echo $this->language->get("entry_carousel_setting"); ?></h3></td>
                        </tr>

                      <tr>
                        <td><?php echo $this->language->get("entry_enable_carousel"); ?></td>
                        <td><select name="ecdeals_module[<?php echo $module_row; ?>][enable_carousel]">
                            <?php if ($module['enable_carousel']) { ?>
                            <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                            <option value="0"><?php echo $text_disabled; ?></option>
                            <?php } else { ?>
                            <option value="1"><?php echo $text_enabled; ?></option>
                            <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                            <?php } ?>
                          </select></td>
                      </tr>
                      <tr>
                          <td><?php echo $this->language->get("entry_number_scroll"); ?></td>
                          <td><input type="text" name="ecdeals_module[<?php echo $module_row; ?>][number_scroll]" value="<?php echo isset($module['number_scroll'])?$module['number_scroll']:"1"; ?>" size="12"/></td>
                        </tr>
                      <tr>
                          <td><?php echo $this->language->get("text_carousel_items_width"); ?></td>
                          <td><input type="text" name="ecdeals_module[<?php echo $module_row; ?>][carousel_item_width]" value="<?php echo isset($module['carousel_item_width'])?$module['carousel_item_width']:"400"; ?>" size="12"/></td>
                        </tr>
                         <tr>
                          <td><?php echo $this->language->get("text_min_max_items"); ?></td>
                          <td><input type="text" name="ecdeals_module[<?php echo $module_row; ?>][carousel_min_items]" value="<?php echo isset($module['carousel_min_items'])?$module['carousel_min_items']:"2"; ?>" size="12"/> - <input type="text" name="ecdeals_module[<?php echo $module_row; ?>][carousel_max_items]" value="<?php echo isset($module['carousel_max_items'])?$module['carousel_max_items']:"6"; ?>" size="12"/></td>
                        </tr>
                         <tr>
                        <td><?php echo $this->language->get("entry_carousel_auto"); ?></td>
                        <td><select name="ecdeals_module[<?php echo $module_row; ?>][carousel_auto]">
                            <?php if ($module['carousel_auto']) { ?>
                            <option value="1" selected="selected"><?php echo $this->language->get("text_yes"); ?></option>
                            <option value="0"><?php echo $this->language->get("text_no"); ?></option>
                            <?php } else { ?>
                            <option value="1"><?php echo $this->language->get("text_yes"); ?></option>
                            <option value="0" selected="selected"><?php echo $this->language->get("text_no"); ?></option>
                            <?php } ?>
                          </select></td>
                      </tr>
                       <tr>
                        <td><?php echo $this->language->get("entry_scroll_effect"); ?></td>
                        <td><select name="ecdeals_module[<?php echo $module_row; ?>][scroll_effect]">
                            <?php foreach($effects as $key) { ?>
                              <?php if(isset($module['scroll_effect']) && $key == $module['scroll_effect']){ ?>
                              <option value="<?php echo $key; ?>" selected="selected"><?php echo $key; ?></option>
                              <?php }else{ ?>
                              <option value="<?php echo $key; ?>"><?php echo $key; ?></option>
                              <?php } ?>
                            <?php } ?>
                          </select></td>
                      </tr>
                       <tr>
                        <td><?php echo $this->language->get("entry_duration"); ?></td>
                        <td><input type="text" name="ecdeals_module[<?php echo $module_row; ?>][duration]" value="<?php echo isset($module['duration'])?$module['duration']:1000; ?>" size="10" /></td>
                      </tr>
                       <tr>
                        <td><?php echo $this->language->get("entry_carousel_mousewhell"); ?></td>
                        <td><select name="ecdeals_module[<?php echo $module_row; ?>][carousel_mousewhell]">
                            <?php if ($module['carousel_mousewhell']) { ?>
                            <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                            <option value="0"><?php echo $text_disabled; ?></option>
                            <?php } else { ?>
                            <option value="1"><?php echo $text_enabled; ?></option>
                            <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                            <?php } ?>
                          </select></td>
                      </tr>
                       <tr>
                        <td><?php echo $this->language->get("entry_carousel_responsive"); ?></td>
                        <td><select name="ecdeals_module[<?php echo $module_row; ?>][carousel_responsive]">
                            <?php if ($module['carousel_responsive']) { ?>
                            <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                            <option value="0"><?php echo $text_disabled; ?></option>
                            <?php } else { ?>
                            <option value="1"><?php echo $text_enabled; ?></option>
                            <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                            <?php } ?>
                          </select></td>
                      </tr>
                      </table>

                  </div>
                  <?php $module_row++; ?>
                  <?php } ?>
                </div>
            </div>
         </div>
      </form>
    </div>
  </div>
</div>
<script type="text/javascript" src="view/javascript/ckeditor/ckeditor.js"></script>
<script type="text/javascript"><!--

<?php foreach ($languages as $language) { ?>
CKEDITOR.replace('description-1-<?php echo $language['language_id']; ?>', {
  filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
  filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
  filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
  filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
  filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
});
<?php } ?>

<?php $module_row = 1; ?>
<?php foreach ($modules as $module) { ?>
  <?php foreach ($languages as $language) { ?>
  CKEDITOR.replace('description-module-<?php echo $module_row; ?>-<?php echo $language['language_id']; ?>', {
    filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
    filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
    filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
    filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
    filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
    filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
  });
  <?php } ?>
  <?php $module_row++; ?>
<?php } ?>
//--></script> 
<script type="text/javascript"><!--

function checkSelect( obj ){
  var current_value = $(obj).val();
  if(current_value == "0") {
    $(obj).find("option").prop('selected','selected');
    $(obj).find("option[value='0']").prop('selected','');
  }
}

function importDefaultDescription(module_index, language_id){
  $("#description-"+module_index+"-"+language_id).html("default description here!");
  var html = '<?php echo isset($default_description)?$default_description:''; ?>';
  if (CKEDITOR.instances["description-"+module_index+"-"+language_id]) {
    CKEDITOR.instances["description-"+module_index+"-"+language_id].setData(html);
  }
}
  function initAutocomplete(module_index){
   
    $('#product_autocomplete_'+module_index).autocomplete({
            delay: 0,
            source: function(request, response) {
              $.ajax({
                url: 'index.php?route=catalog/product/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request.term),
                dataType: 'json',
                data: {token: '<?php echo $token; ?>', filter_name: encodeURIComponent(request.term)},
                type: 'POST',
                success: function(json) {   
                  response($.map(json, function(item) {
                    return {
                      label: item.name,
                      value: item.product_id
                    }
                  }));
                }
              });
            }, 
            select: function(event, ui) {
              $('#product_'+module_index+'_' + ui.item.value).remove();
              
              $('#product_'+module_index).append('<div id="product_'+module_index+'_' + ui.item.value + '">' + ui.item.label + '<img src="view/image/delete.png" /><input type="hidden" name="ecdeals_setting[products][]" value="' + ui.item.value + '" /></div>');

              $('#product_'+module_index+' div:odd').attr('class', 'odd');
              $('#product_'+module_index+' div:even').attr('class', 'even');
                  
              return false;
            }
          });

          $('#product_'+module_index+' div img').live('click', function() {
            $(this).parent().remove();
            
            $('#product_'+module_index+' div:odd').attr('class', 'odd');
            $('#product_'+module_index+' div:even').attr('class', 'even'); 
          });
  }
  $('.date').datepicker({dateFormat: 'yy-mm-dd'});
//--></script>
<script type="text/javascript"><!--
    function image_upload(field, thumb) {
        $.startImageManager(field, thumb);
    };
    //--></script>
<script type="text/javascript"><!--
var module_row = <?php echo $module_row; ?>;
function showDataSource(group_name,module_index){
  $(".source_category_"+module_index).first().hide();
  $(".source_product_"+module_index).first().hide();

  if($(".source_"+group_name+"_"+module_index)){
    $(".source_"+group_name+"_"+module_index).first().show();
  }
}
function addModule() {
  html ='<div id="tab-module-'+module_row+'" class="vtabs-content"> ';
  html +='           <div id="language-module-'+module_row+'" class="htabs">';
              <?php foreach ($languages as $language) { ?>
  html +='              <a href="#tab-language-module-'+module_row+'-<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>';
              <?php } ?>
  html +='            </div>';
            <?php foreach ($languages as $language) { ?>
  html +='             <div id="tab-language-module-'+module_row+'-<?php echo $language['language_id']; ?>">';
  html +='              <table class="form">';
  html +=' <tr>';
  html +='                  <td><?php echo $this->language->get( 'entry_title' ); ?></td>';
  html +='                  <td><input size="60" name="ecdeals_module['+module_row+'][title][<?php echo $language['language_id']; ?>]" id="title-'+module_row+'-<?php echo $language['language_id']; ?>" value=""/></td>';
  html +='                </tr>';
                  
  html +='                <tr>';
  html +='                  <td><?php echo $this->language->get('entry_description'); ?></td>';
  html +='                  <td><textarea name="ecdeals_module['+module_row+'][description][<?php echo $language['language_id']; ?>]" id="description-module-'+module_row+'-<?php echo $language['language_id']; ?>"></textarea></td>';
  html +='                </tr>';
  html +='              </table>';
  html +='            </div>';
              <?php } ?>
  html +='<input type="hidden" name="ecdeals_module['+module_row+'][module_id]" value="'+module_row+'"/>';
  html +='         <table class="form">';
  html +='          <tr>';
  html +='            <td><?php echo $entry_layout; ?></td>';
  html +='            <td><select name="ecdeals_module['+module_row+'][layout_id]">';
  html +='                <option value="0"><?php echo $text_alllayout; ?></option>';
                  <?php foreach ($layouts as $layout) { ?>
  html +='                <option value="<?php echo $layout['layout_id']; ?>"><?php echo $layout['name']; ?></option>';
                  <?php } ?>
  html +='              </select></td>';
  html +='          </tr>';
  html +='    <tr>';
  html +='      <td><?php echo $entry_store; ?></td>';
  html +='            <td><div class="scrollbox">';
                  <?php $class = 'even'; ?>
  html +='                <div class="<?php echo $class; ?>">';
  html +='                  <input type="checkbox" name="ecdeals_module['+module_row+'][store_id][]" value="0" checked="checked"/>';
  html +='        <?php echo $text_default; ?>';
  html +='                </div>';
                  <?php foreach ($stores as $store) { ?>
                  <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
  html +='                <div class="<?php echo $class; ?>">';
  html +='                  <input type="checkbox" name="ecdeals_module['+module_row+'][store_id][]" value="<?php echo $store['store_id']; ?>" />';
  html +='                  <?php echo $store['name']; ?>';
  html +='                </div>';
                  <?php } ?>
  html +='              </div></td>';
  html +='    </tr>';

  html +='          <tr>';
  html +='            <td><?php echo $entry_position; ?></td>';
  html +='            <td><select name="ecdeals_module['+module_row+'][position]">';
                           <?php foreach( $positions as $pos ) { ?>
  html +='                                  <option value="<?php echo $pos;?>"><?php echo $this->language->get('text_'.$pos); ?></option>';
                                    <?php } ?> 
  html +='                                </select></td>';
  html +='          </tr>';
  html +=' <tr>';
  html +='                      <td><?php echo $this->language->get("entry_custom_position"); ?></td>';
  html +='                      <td><input type="text" name="ecdeals_module['+module_row+'][custom_position]" value="" size="30" /></td>';
  html +='                    </tr>';
  html +='          <tr>';
  html +='            <td><?php echo $entry_status; ?></td>';
  html +='            <td><select name="ecdeals_module['+module_row+'][status]">';
  html +='                <option value="1"><?php echo $text_enabled; ?></option>';
  html +='                <option value="0"><?php echo $text_disabled; ?></option>';

  html +='              </select></td>';
  html +='          </tr>';
  html +='          <tr>';
  html +='            <td><?php echo $entry_sort_order; ?></td>';
  html +='            <td><input type="text" name="ecdeals_module['+module_row+'][sort_order]" value="" size="3" /></td>';
  html +='          </tr>';
  html +='        </table>';
  
  html +='                  <table class="form">';
  html +='<tr>';
  html +='                        <td colspan="2"><h3><?php echo $this->language->get("entry_module_setting"); ?></h3></td>';
  html +='                      </tr>';
  html +='<tr>';
  html +='                        <td><?php echo $this->language->get("entry_block_prefix"); ?></td>';
  html +='                        <td><input size="30" type="text" name="ecdeals_module['+module_row+'][prefix]" value=""/></td>';
  html +='                     </tr>';
  html +='<tr>';
  html +='                      <td><?php echo $this->language->get("entry_limit_deals"); ?></td>';
  html +='                      <td><input type="text" name="ecdeals_module['+module_row+'][limit_deals]" value="<?php echo isset($module['limit_deals'])?$module['limit_deals']:"12"; ?>" size="10" /></td>';
  html +='                    </tr>';
  html +='                    <tr>';
  html +='                      <td><?php echo $this->language->get("entry_show_featured"); ?></td>';
  html +='                      <td><select name="ecdeals_module['+module_row+'][show_featured]">';
                                 <?php
                                 foreach($featureds as $key=>$val){ ?>
  html +='                             <option value="<?php echo $key; ?>"><?php echo $val; ?></option>';
                                    <?php
                                 }
                                 ?>
  html +='                        </select></td>';
  html +='                    </tr>';
  html +='                    <tr>';
  html +='                      <td><?php echo $this->language->get("entry_category_mode"); ?></td>';
  html +='                      <td><select name="ecdeals_module['+module_row+'][category_mode]">';
                                 <?php
                                 foreach($yesno as $key=>$val){ ?>
  html +='                             <option value="<?php echo $key; ?>"><?php echo $val; ?></option>';
                                    <?php
                                 }
                                 ?>
  html +='                        </select></td>';
  html +='                    </tr>';
  html +='                    <tr>';
  html +='                     <td><?php echo $this->language->get("entry_filter_category"); ?></td>';
  html +='                      <td><select name="ecdeals_module['+module_row+'][category_id][]" multiple="multiple" size="10">';
  html +='                      <option value=""><?php echo $this->language->get("text_select_category_to_filter");?></opion>';
                            <?php
                             if($categories){
                              foreach($categories as $category){
                                $category['name'] = str_replace("'", "&#39;", $category['name']);
                                ?>

  html +='                       <option value="<?php echo $category['category_id'];?>"><?php echo $category['name'];?></option>';
                                  <?php
                              }
                             }
                            ?>
  html +='                        </select></td>';
  html +='                    </tr>';
  html +=' <tr>';
  html +='                       <td><?php echo $this->language->get("entry_show_deals_from"); ?></td>';
  html +='                       <td><select name="ecdeals_module['+module_row+'][deal_mode]">';
  html +='                         <option value=""><?php echo $this->language->get("text_select_mode");?></opion>';
                            <?php

                             if($modes){
                              foreach($modes as $val=>$text){ ?>
  html +='                         <option value="<?php echo $val;?>"><?php echo $text;?></option>';
                                  <?php
                              }
                             }
                            ?>
  html +='                         </select></td>';
  html +='                     </tr>';
  html +=' <tr>';
  html +='                        <td><?php echo $this->language->get("text_deal_image_width_height"); ?></td>';
  html +='                        <td><input type="text" name="ecdeals_module['+module_row+'][deal_image_width]" value="200" size="12"/> - <input type="text" name="ecdeals_module['+module_row+'][deal_image_height]" value="150" size="12"/></td>';
  html +='                      </tr>';
  html +='                      <tr>';
  html +='                        <td><?php echo $this->language->get("text_items_cols"); ?></td>';
  html +='                        <td><input type="text" name="ecdeals_module['+module_row+'][item_cols]" value="3" size="12"/></td>';
  html +='                      </tr>';
  html +='                       <tr>';
  html +='                      <td><?php echo $this->language->get("entry_show_module_title"); ?></td>';
  html +='                      <td><select name="ecdeals_module['+module_row+'][show_module_title]">';
  html +='                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>';
  html +='                          <option value="0"><?php echo $text_disabled; ?></option>';

  html +='                        </select></td>';
  html +='                    </tr>';
  html +='                     <tr>';
  html +='                      <td><?php echo $this->language->get("entry_show_description"); ?></td>';
  html +='                      <td><select name="ecdeals_module['+module_row+'][show_module_description]">';
  html +='                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>';
  html +='                          <option value="0"><?php echo $text_disabled; ?></option>';
  html +='                        </select></td>';
  html +='                    </tr>';
  html +='                      <tr>';
  html +='                      <td><?php echo $this->language->get("entry_show_deal_image"); ?></td>';
  html +='                      <td><select name="ecdeals_module['+module_row+'][enable_deal_image]">';
  html +='                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>';
  html +='                          <option value="0"><?php echo $text_disabled; ?></option>';
  html +='                        </select></td>';
  html +='                    </tr>';
  html +='                    <tr>';
  html +='                      <td><?php echo $this->language->get("entry_show_name"); ?></td>';
  html +='                     <td><select name="ecdeals_module['+module_row+'][enable_deal_name]">';
  html +='                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>';
  html +='                          <option value="0"><?php echo $text_disabled; ?></option>';
  html +='                        </select></td>';
  html +='                    </tr>';
              
  html +='                    <tr>';
  html +='                      <td><?php echo $this->language->get("entry_show_discount"); ?></td>';
  html +='                      <td><select name="ecdeals_module['+module_row+'][enable_discount]">';
  html +='                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>';
  html +='                          <option value="0"><?php echo $text_disabled; ?></option>';

  html +='                        </select></td>';
  html +='                    </tr>';
  html +='                    <tr>';
  html +='                      <td><?php echo $this->language->get("entry_show_social"); ?></td>';
  html +='                      <td><select name="ecdeals_module['+module_row+'][show_social]">';
  html +='                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>';
  html +='                          <option value="0"><?php echo $text_disabled; ?></option>';
  html +='                        </select></td>';
  html +='                    </tr>';
  html +='                    <tr>';
  html +='                      <td><?php echo $this->language->get("entry_show_rating"); ?></td>';
  html +='                      <td><select name="ecdeals_module['+module_row+'][show_rating]">';
  html +='                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>';
  html +='                          <option value="0"><?php echo $text_disabled; ?></option>';
  html +='                        </select></td>';
  html +='                    </tr>';
  html +='                    <tr>';
  html +='                      <td><?php echo $this->language->get("entry_show_reward_point"); ?></td>';
  html +='                      <td><select name="ecdeals_module['+module_row+'][show_reward_point]">';
 html +='                           <option value="1" selected="selected"><?php echo $text_enabled; ?></option>';
  html +='                          <option value="0"><?php echo $text_disabled; ?></option>';
  html +='                        </select></td>';
  html +='                    </tr>';
  html +='                    <tr>';
  html +='                      <td><?php echo $this->language->get("entry_enable_deal_price"); ?></td>';
  html +='                      <td><select name="ecdeals_module['+module_row+'][enable_deal_price]">';

  html +='                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>';
  html +='                          <option value="0"><?php echo $text_disabled; ?></option>';
  html +='                        </select></td>';
  html +='                    </tr>';               
  html +='                    <tr>';
  html +='                        <td colspan="2"><h3><?php echo $this->language->get("entry_carousel_setting"); ?></h3></td>';
  html +='                      </tr>';
  html +='                   <tr>';
  html +='                      <td><?php echo $this->language->get("entry_enable_carousel"); ?></td>';
  html +='                      <td><select name="ecdeals_module['+module_row+'][enable_carousel]">';
  html +='                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>';
  html +='                          <option value="0"><?php echo $text_disabled; ?></option>';
  html +='                        </select></td>';
  html +='                    </tr>';
  html +='<tr>';
  html +='                        <td><?php echo $this->language->get("entry_number_scroll"); ?></td>';
  html +='                        <td><input type="text" name="ecdeals_module['+module_row+'][number_scroll]" value="1" size="12"/></td>';
  html +='                      </tr>';
  html +='                    <tr>';
  html +='                        <td><?php echo $this->language->get("text_carousel_items_width"); ?></td>';
  html +='                        <td><input type="text" name="ecdeals_module['+module_row+'][carousel_item_width]" value="400" size="12"/></td>';
  html +='                      </tr>';
  html +='                       <tr>';
  html +='                        <td><?php echo $this->language->get("text_min_max_items"); ?></td>';
  html +='                        <td><input type="text" name="ecdeals_module['+module_row+'][carousel_min_items]" value="2" size="12"/> - <input type="text" name="ecdeals_module['+module_row+'][carousel_max_items]" value="6" size="12"/></td>';
  html +='                      </tr>';
  html +='                       <tr>';
  html +='                      <td><?php echo $this->language->get("entry_carousel_auto"); ?></td>';
  html +='                      <td><select name="ecdeals_module['+module_row+'][carousel_auto]">';
  html +='                          <option value="1"><?php echo $this->language->get("text_yes"); ?></option>';
  html +='                          <option value="0" selected="selected"><?php echo $this->language->get("text_no"); ?></option>';
  html +='                        </select></td>';
  html +='                    </tr>';
   html +=' <tr>';
  html +='              <td><?php echo $this->language->get("entry_scroll_effect"); ?></td>';
  html +='              <td><select name="ecdeals_module[' + module_row + '][scroll_effect]">';
                    <?php foreach($effects as $key) { ?>
                      <?php if($key == 'scroll'){ ?>
  html +='                   <option value="<?php echo $key; ?>" selected="selected"><?php echo $key; ?></option>';
                      <?php }else{ ?>
  html +='                    <option value="<?php echo $key; ?>"><?php echo $key; ?></option>';
                      <?php } ?>
                    <?php } ?>
  html +='                </select></td>';
  html +='            </tr>';
  html +='             <tr>';
  html +='              <td><?php echo $this->language->get("entry_duration"); ?></td>';
  html +='              <td><input type="text" name="ecdeals_module[' + module_row + '][duration]" value="1000" size="10" /></td>';
  html +='            </tr>';
  html +='                     <tr>';
  html +='                      <td><?php echo $this->language->get("entry_carousel_mousewhell"); ?></td>';
  html +='                      <td><select name="ecdeals_module['+module_row+'][carousel_mousewhell]">';
  html +='                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>';
  html +='                          <option value="0"><?php echo $text_disabled; ?></option>';
  html +='                        </select></td>';
  html +='                    </tr>';
  html +='                     <tr>';
  html +='                      <td><?php echo $this->language->get("entry_carousel_responsive"); ?></td>';
  html +='                      <td><select name="ecdeals_module['+module_row+'][carousel_responsive]">';
  html +='                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>';
  html +='                          <option value="0"><?php echo $text_disabled; ?></option>';
  html +='                        </select></td>';
  html +='                   </tr>';
  html +='                    </table>';
  html +='      </div>';
  
  $('#tab-block-position').find(".tab-inner").first().append(html);
  
  $('#module-add').before('<a href="#tab-module-' + module_row + '" id="module-' + module_row + '"><?php echo $tab_block; ?> ' + module_row + '&nbsp;<img src="view/image/delete.png" alt="" onclick="$(\'.vtabs a:first\').trigger(\'click\'); $(\'#module-' + module_row + '\').remove(); $(\'#tab-module-' + module_row + '\').remove(); return false;" /></a>');
  
  $('.vtabs a').tabs();
  $('.date').datepicker({dateFormat: 'yy-mm-dd'});

  $('#language-module-' + module_row + ' a').tabs();

   <?php foreach ($languages as $language) { ?>
  CKEDITOR.replace('description-module-'+module_row+'-<?php echo $language['language_id']; ?>', {
    filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
    filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
    filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
    filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
    filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
    filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
  });
  <?php } ?>

  $('#module-' + module_row).trigger('click');

  module_row++;
}
//--></script> 
<script type="text/javascript"><!--

$('.vtabs a').tabs();
$('#tabs a').tabs();

$('#tabs a').each( function(){
    if( $(this).attr("href") ==  "#tab-discount-setting" ){
      $(this).click();
      return ;
    }
  } );
//--></script>


<script type="text/javascript"><!--
$('#language-1 a').tabs();
//--></script>
<script type="text/javascript"><!--
<?php $module_row = 1; ?>
<?php foreach ($modules as $module) { ?>
$('#language-module-<?php echo $module_row; ?> a').tabs();
<?php $module_row++; ?>
<?php } ?> 
//--></script>
<?php echo $footer; ?>