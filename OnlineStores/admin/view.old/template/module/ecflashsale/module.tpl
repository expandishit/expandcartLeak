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
            <a href="#tab-deal-setting"><?php echo $this->language->get("tab_deal_setting"); ?></a>
             <a href="#tab-block-position"><?php echo $this->language->get("tab_block_position"); ?></a>
        </div>
            

         <div id="tab-contents">
            <div id="tab-deal-setting">
              <table class="form">
                      <tr><td colspan="2"><?php echo $this->language->get("text_deal_mode_description");?></td></tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_enable_today_deal"); ?></td>
                          <td><select name="ecflashsale_setting[mini_mode]">
                            <?php
                              if(!empty($mini_modes)){
                                foreach($mini_modes as $key=>$val){
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
                          <td><?php echo $this->language->get("entry_show_onsale_menu"); ?></td>
                          <td><select name="ecflashsale_setting[show_onsale_menu]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['show_onsale_menu']) && $key == $general['show_onsale_menu']){
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
                          <td><?php echo $this->language->get("entry_show_filter_by_category"); ?></td>
                          <td><select name="ecflashsale_setting[show_filter_category]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['show_filter_category']) && $key == $general['show_filter_category']){
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
                        <td><?php echo $this->language->get("entry_show_categories_in_filter"); ?></td>
                        <td><select name="ecflashsale_setting[show_category_id][]" multiple="multiple" size="10" onclick="checkSelect(this)">
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
                          <td><input type="text" name="ecflashsale_setting[deal_block_width]" value="<?php echo isset($general['deal_block_width'])?$general['deal_block_width']:"340px";?>"/></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_deal_image_width_height"); ?></td>
                          <td><input type="text" name="ecflashsale_setting[deal_image_width]" value="<?php echo isset($general['deal_image_width'])?$general['deal_image_width']:200;?>"/> x <input type="text" name="ecflashsale_setting[deal_image_height]" value="<?php echo isset($general['deal_image_height'])?$general['deal_image_height']:100;?>"/></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_min_day"); ?></td>
                          <td><input type="text" name="ecflashsale_setting[min_day]" value="<?php echo isset($general['min_day'])?$general['min_day']:3;?>"/></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_min_quantity"); ?></td>
                          <td><input type="text" name="ecflashsale_setting[min_quantity]" value="<?php echo isset($general['min_quantity'])?$general['min_quantity']:10;?>"/></td>
                        </tr>

                        <tr>
                          <td><?php echo $this->language->get("entry_order_status_id"); ?></td>
                          <td><select name="ecflashsale_setting[order_status_id][]" multiple="multiple" size="10">
                            <?php
                              if(!empty($order_status)){
                                $general['order_status_id'] = isset($general['order_status_id'])?$general['order_status_id']:array(5);
                                $general['order_status_id'] = !is_array($general['order_status_id'])?array($general['order_status_id']):$general['order_status_id'];
                                
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
                        <tr>
                          <td><?php echo $this->language->get("entry_deal_template_in_detail"); ?></td>
                          <td><select name="ecflashsale_setting[deal_template]">
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
                        <tr>
                          <td><?php echo $this->language->get("entry_deal_template_in_list"); ?></td>
                          <td><select name="ecflashsale_setting[list_deal_template]">
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
                          <td><?php echo $this->language->get("entry_enable_deal_image"); ?></td>
                          <td><select name="ecflashsale_setting[enable_deal_image]">
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
                          <td><select name="ecflashsale_setting[enable_deal_name]">
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
                          <td><select name="ecflashsale_setting[enable_deal_price]">
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
                          <td><select name="ecflashsale_setting[enable_buy_now]">
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
                          <td><?php echo $this->language->get("entry_enable_buy_now_in_detail"); ?></td>
                          <td><select name="ecflashsale_setting[enable_detail_buynow]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['enable_detail_buynow']) && $key == $general['enable_detail_buynow']){
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
                          <td><select name="ecflashsale_setting[enable_discount]">
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
                          <td><select name="ecflashsale_setting[show_rating]">
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
                          <td><select name="ecflashsale_setting[show_social]">
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
                          <td><select name="ecflashsale_setting[show_notify_message]">
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
                          <td><select name="ecflashsale_setting[show_reward_point]">
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
              </table>
              <h3 class="bg-group"><span class="group_title"><?php echo $this->language->get("entry_deal_description_on_listing");?></span></h3>
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
                          <td><?php echo $this->language->get("entry_onsale_title"); ?></td>
                          <td><input type="text" size="50" name="ecflashsale_setting[deal_title][<?php echo $language['language_id']; ?>]" value="<?php echo isset($general['deal_title'][$language['language_id']]) ? $general['deal_title'][$language['language_id']] : 'On Sale'; ?>"/>
                          </td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_description"); ?></td>
                          <td><textarea name="ecflashsale_setting[deal_description][<?php echo $language['language_id']; ?>]" id="description-1-<?php echo $language['language_id']; ?>"><?php echo isset($general['deal_description'][$language['language_id']]) ? $general['deal_description'][$language['language_id']] : ''; ?></textarea>
                          </td>
                        </tr>
                         
                      </table>
                    </div>
                  <?php } ?>
                  <table class="form">

                    <tr>
                        <td><?php echo $this->language->get("entry_image"); ?></td>
                        <td class="left"><div class="image"><img src="<?php echo isset($general['deal_thumb'])?$general['deal_thumb']:""; ?>" alt="" id="thumb1" />
                            <input type="hidden" name="ecflashsale_setting[deal_image]" value="<?php echo isset($general['deal_image'])?$general['deal_image']:""; ?>" id="image1"  />
                            <br />
                            <a onclick="image_upload('image1', 'thumb1');"><?php echo $this->language->get("text_browse"); ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="$('#thumb1').attr('src', '<?php echo $no_image; ?>'); $('#image1').attr('value', '');"><?php echo $this->language->get("text_clear"); ?></a></div></td>
                      </tr>
                       <tr>
                          <td><?php echo $this->language->get("text_image_size"); ?></td>
                          <td><input type="text" name="ecflashsale_setting[meta_deal_image_width]" value="<?php echo isset($general['meta_deal_image_width'])?$general['meta_deal_image_width']:"200"; ?>" size="12"/> x <input type="text" name="ecflashsale_setting[meta_deal_image_height]" value="<?php echo isset($general['meta_deal_image_height'])?$general['meta_deal_image_height']:"100"; ?>" size="12"/></td>
                        </tr>
                       <tr>
                          <td><?php echo $this->language->get("entry_meta_description"); ?></td>
                          <td><textarea name="ecflashsale_setting[deal_meta_description]" cols="40" rows="5"><?php echo isset($general['deal_meta_description']) ? $general['deal_meta_description'] : ''; ?></textarea>
                          </td>
                        </tr>
                         <tr>
                          <td><?php echo $this->language->get("entry_meta_keyword"); ?></td>
                          <td><textarea name="ecflashsale_setting[deal_meta_keyword]" cols="40" rows="5"><?php echo isset($general['deal_meta_keyword']) ? $general['deal_meta_keyword'] : ''; ?></textarea>
                          </td>
                        </tr>  
                  </table>
                </div>
            </div>
            <div id="tab-general-setting">
              <table class="form">
                <tr class="source_category_0">
                          <td><?php echo $this->language->get("entry_enable"); ?></td>
                          <td><select name="ecflashsale_setting[status]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['status']) && $key == $general['status']){
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
                          <td><?php echo $this->language->get("entry_show_flashsale_menu"); ?></td>
                          <td><select name="ecflashsale_setting[show_flashsale_menu]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['show_flashsale_menu']) && $key == $general['show_flashsale_menu']){
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
                          <td><?php echo $this->language->get("entry_limit_deal_in_product_detail"); ?></td>
                          <td><select name="ecflashsale_setting[show_deal_flashsale]">
                            <?php
                              if(!empty($limit_deals)){
                                foreach($limit_deals as $key=>$val){
                                  if(isset($general['show_deal_flashsale']) && $key == $general['show_deal_flashsale']){
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
                          <td><?php echo $this->language->get("entry_enable_deadline_message"); ?></td>
                          <td><select name="ecflashsale_setting[enable_message]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['enable_message']) && $key == $general['enable_message']){
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
                          <td><?php echo $this->language->get("entry_no_border"); ?></td>
                          <td><select name="ecflashsale_setting[no_border]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['no_border']) && $key == $general['no_border']){
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
                          <td><?php echo $this->language->get("entry_show_saleoff"); ?></td>
                          <td><select name="ecflashsale_setting[show_saleoff]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['show_saleoff']) && $key == $general['show_saleoff']){
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
                          <td><?php echo $this->language->get("entry_show_expire_date"); ?></td>
                          <td><select name="ecflashsale_setting[show_expire_date]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['show_expire_date']) && $key == $general['show_expire_date']){
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
                          <td><select name="ecflashsale_setting[flashsale_show_social]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['flashsale_show_social']) && $key == $general['flashsale_show_social']){
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
                          <td><select name="ecflashsale_setting[show_stock_bar]">
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
                          <td><?php echo $this->language->get("entry_show_countdown"); ?></td>
                          <td><select name="ecflashsale_setting[show_countdown]">
                            <?php
                              if(!empty($yesno)){
                                foreach($yesno as $key=>$val){
                                  if(isset($general['show_countdown']) && $key == $general['show_countdown']){
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
                        <td><?php echo $this->language->get("entry_flashsale_description_maxchars"); ?></td>
                        <td><input type="text" name="ecflashsale_setting[flashsale_desc_maxchars]" value="<?php echo isset($general['flashsale_desc_maxchars'])?$general['flashsale_desc_maxchars']:"100"; ?>" size="10" /></td>
                      </tr>
                        <tr>
                          <td><?php echo $this->language->get("text_flashsale_detail_image_size"); ?></td>
                          <td><input type="text" name="ecflashsale_setting[flashsale_detail_image_width]" value="<?php echo isset($general['flashsale_detail_image_width'])?$general['flashsale_detail_image_width']:"200"; ?>" size="12"/> x <input type="text" name="ecflashsale_setting[flashsale_detail_image_height]" value="<?php echo isset($general['flashsale_detail_image_height'])?$general['flashsale_detail_image_height']:"100"; ?>" size="12"/></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("text_flashsale_listing_image_size"); ?></td>
                          <td><input type="text" name="ecflashsale_setting[flashsale_listing_image_width]" value="<?php echo isset($general['flashsale_listing_image_width'])?$general['flashsale_listing_image_width']:"200"; ?>" size="12"/> x <input type="text" name="ecflashsale_setting[flashsale_listing_image_height]" value="<?php echo isset($general['flashsale_listing_image_height'])?$general['flashsale_listing_image_height']:"100"; ?>" size="12"/></td>
                        </tr>
                        <tr>
                        <td><?php echo $this->language->get("entry_keyword_seo_flashsale_listing_page"); ?></td>
                        <td><input type="text" name="ecflashsale_setting[keyword_seo]" value="<?php echo isset($general['keyword_seo'])?$general['keyword_seo']:"flashsales"; ?>" size="30" /></td>
                      </tr>
              </table>
                <div id="language-0" class="htabs">
                      <?php foreach ($languages as $language) { ?>
                      <a href="#tab-language-0-<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
                      <?php } ?>
                    </div>
                    <?php foreach ($languages as $language) { ?>
                    <div id="tab-language-0-<?php echo $language['language_id']; ?>">
                      <table class="form">
                        <tr>
                          <td><?php echo $this->language->get("entry_flashsale_description"); ?></td>
                          <td><textarea name="ecflashsale_setting[flashsale_description][<?php echo $language['language_id']; ?>]" id="description-0-<?php echo $language['language_id']; ?>"><?php echo isset($general['flashsale_description'][$language['language_id']]) ? $general['flashsale_description'][$language['language_id']] : ''; ?></textarea>
                          </td>
                        </tr>
                         
                      </table>
                    </div>
                  <?php } ?>
                  <table class="form">
                       <tr>
                          <td><?php echo $this->language->get("entry_meta_description"); ?></td>
                          <td><textarea name="ecflashsale_setting[flashsale_meta_description]" cols="40" rows="5"><?php echo isset($general['flashsale_meta_description']) ? $general['flashsale_meta_description'] : ''; ?></textarea>
                          </td>
                        </tr>
                         <tr>
                          <td><?php echo $this->language->get("flashsale_meta_keyword"); ?></td>
                          <td><textarea name="ecflashsale_setting[flashsale_meta_keyword]" cols="40" rows="5"><?php echo isset($general['flashsale_meta_keyword']) ? $general['flashsale_meta_keyword'] : ''; ?></textarea>
                          </td>
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
                  
                    <input type="hidden" name="ecflashsale_module[<?php echo $module_row; ?>][module_id]" value="<?php echo isset($module['module_id'])?$module['module_id']:$module_row;?>"/>
                    <table class="form">
                      <tr>
                        <td><?php echo $entry_layout; ?></td>
                        <td><select name="ecflashsale_module[<?php echo $module_row; ?>][layout_id]">
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
                              <input type="checkbox" name="ecflashsale_module[<?php echo $module_row; ?>][store_id][]" value="0" checked="checked" />
                              <?php } else { ?>
                              <input type="checkbox" name="ecflashsale_module[<?php echo $module_row; ?>][store_id][]" value="0" />
                              <?php } ?>
                    <?php echo $text_default; ?>
                            </div>
                            <?php foreach ($stores as $store) { ?>
                            <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                            <div class="<?php echo $class; ?>">
                              <?php if (isset($module['store_id']) && in_array($store['store_id'], $module['store_id'])) { ?>
                              <input type="checkbox" name="ecflashsale_module[<?php echo $module_row; ?>][store_id][]" value="<?php echo $store['store_id']; ?>" checked="checked" />
                              <?php echo $store['name']; ?>
                              <?php } else { ?>
                              <input type="checkbox" name="ecflashsale_module[<?php echo $module_row; ?>][store_id][]" value="<?php echo $store['store_id']; ?>" />
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
                        <td><select name="ecflashsale_module[<?php echo $module_row; ?>][position]">
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
                        <td><input type="text" name="ecflashsale_module[<?php echo $module_row; ?>][custom_position]" value="<?php echo $custom_position; ?>" size="30" /></td>
                      </tr>
                      <tr>
                        <td><?php echo $entry_status; ?></td>
                        <td><select name="ecflashsale_module[<?php echo $module_row; ?>][status]">
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
                        <td><input type="text" name="ecflashsale_module[<?php echo $module_row; ?>][sort_order]" value="<?php echo isset($module['sort_order'])?$module['sort_order']:''; ?>" size="3" /></td>
                      </tr>
                    </table>
                    
                    <table class="form">
                      
                        <tr>
                          <td colspan="2" class="bg-group"><span class="group_title"><?php echo $this->language->get("entry_module_setting"); ?></span></td>
                        </tr>
                        <tr>
                          <td><?php echo $this->language->get("entry_block_prefix"); ?></td>
                          <td><input size="30" type="text" name="ecflashsale_module[<?php echo $module_row; ?>][prefix]" value="<?php echo isset($module['prefix'])?$module['prefix']:""; ?>"/></td>
                       </tr>
                       <tr>
                        <td><?php echo $this->language->get("entry_show_mode"); ?></td>
                        <td><select name="ecflashsale_module[<?php echo $module_row; ?>][show_mode]">
                            <?php foreach($show_modes as $key=>$val) { ?>
                             <?php if($key == $module['show_mode']) { ?>
                             <option value="<?php echo $key; ?>" selected="selected"><?php echo $val; ?></option>
                             <?php } else { ?>
                             <option value="<?php echo $key; ?>"><?php echo $val; ?></option>
                             <?php } ?>
                            <?php } ?>
                          </select></td>
                      </tr>
                      <tr>
                        <td><?php echo $this->language->get("entry_popup_width"); ?></td>
                        <td><input type="text" name="ecflashsale_module[<?php echo $module_row; ?>][popup_width]" value="<?php echo isset($module['popup_width'])?$module['popup_width']:'60%'; ?>" size="10" /></td>
                      </tr>
                       <tr>
                        <td><?php echo $this->language->get("entry_mode"); ?></td>
                        <td><select name="ecflashsale_module[<?php echo $module_row; ?>][mode]" id="group_selector_<?php echo $module_row; ?>" onchange="change_mode(<?php echo $module_row; ?>)">
                           <?php
                             foreach($modes as $key=>$val) {
                               if(isset($module['mode']) && $key == $module['mode']) {
                                ?>
                                <option value="<?php echo $key;?>" selected="selected"><?php echo $val ?></option>
                                <?php
                               } else {
                                ?>
                                <option value="<?php echo $key;?>"><?php echo $val ?></option>
                                <?php
                               }
                             }
                           ?>
                          </select>
                          <script type="text/javascript">
                          $(document).ready(function(){
                             change_mode(<?php echo $module_row; ?>);
                          })
                          
                          </script>
                        </td>
                       </tr>
                       <tr id="group_field_mode_block_<?php echo $module_row; ?>" class="group_field_<?php echo $module_row; ?>" style="display:none">
                        <td colspan="2">
                          <table class="form">
                             <tr>
                              <td><?php echo $this->language->get("entry_flashsale"); ?></td>
                              <td><select name="ecflashsale_module[<?php echo $module_row; ?>][flashsale_id]" size="10">
                                  <option value=""><?php echo $this->language->get("text_choose_flashsale");?></option>
                                  <?php 
                                    foreach($flashsales as $flashsale){
                                      if(isset($module['flashsale_id']) && $flashsale['ecflashsale_id'] == $module['flashsale_id']){
                                        ?>
                                        <option value="<?php echo $flashsale['ecflashsale_id'];?>" selected="selected"><?php echo $flashsale['name'] ?></option>
                                        <?php
                                      }else{
                                        ?>
                                        <option value="<?php echo $flashsale['ecflashsale_id'];?>"><?php echo $flashsale['name'] ?></option>
                                        <?php
                                      }
                                    }
                                  ?>
                                </select></td>
                            </tr>
                            <tr>
                              <td><?php echo $this->language->get("entry_custom_url"); ?></td>
                              <td><input type="text" name="ecflashsale_module[<?php echo $module_row; ?>][custom_url]" value="<?php echo isset($module['custom_url'])?$module['custom_url']:""; ?>" size="60" /></td>
                            </tr>
                            <tr>
                              <td><?php echo $this->language->get("entry_description_maxchars"); ?></td>
                              <td><input type="text" name="ecflashsale_module[<?php echo $module_row; ?>][description_maxchars]" value="<?php echo isset($module['description_maxchars'])?$module['description_maxchars']:"100"; ?>" size="10" /></td>
                            </tr>
                              <tr>
                              <td><?php echo $this->language->get("entry_module_image"); ?></td>
                              <td class="left"><div class="image"><img src="<?php echo isset($module['thumb'])?$module['thumb']:""; ?>" alt="" id="thumb_1_<?php echo $module_row; ?>" />
                                  <input type="hidden" name="ecflashsale_module[<?php echo $module_row; ?>][image]" value="<?php echo isset($module['image'])?$module['image']:""; ?>" id="image_1_<?php echo $module_row; ?>"  />
                                  <br />
                                  <a onclick="image_upload('image_1_<?php echo $module_row; ?>', 'thumb_1_<?php echo $module_row; ?>');"><?php echo $this->language->get("text_browse"); ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="$('#thumb_1_<?php echo $module_row; ?>').attr('src', '<?php echo $no_image; ?>'); $('#image_1_<?php echo $module_row; ?>').attr('value', '');"><?php echo $this->language->get("text_clear"); ?></a></div></td>
                            </tr>
                             <tr>
                                <td><?php echo $this->language->get("text_image_size"); ?></td>
                                <td><input type="text" name="ecflashsale_module[<?php echo $module_row; ?>][image_width]" value="<?php echo isset($module['image_width'])?$module['image_width']:"600"; ?>" size="12"/> x <input type="text" name="ecflashsale_module[<?php echo $module_row; ?>][image_height]" value="<?php echo isset($module['image_height'])?$module['image_height']:"400"; ?>" size="12"/></td>
                              </tr>
                               <tr>
                                <td><?php echo $this->language->get("text_module_size"); ?></td>
                                <td><input type="text" name="ecflashsale_module[<?php echo $module_row; ?>][module_width]" value="<?php echo isset($module['module_width'])?$module['module_width']:"100%"; ?>" size="12"/> - <input type="text" name="ecflashsale_module[<?php echo $module_row; ?>][module_height]" value="<?php echo isset($module['module_height'])?$module['module_height']:"100%"; ?>" size="12"/></td>
                              </tr>
                              <tr>
                              <td><?php echo $this->language->get("entry_show_banner_image"); ?></td>
                              <td><select name="ecflashsale_module[<?php echo $module_row; ?>][show_image]">
                                  <?php if ($module['show_image']) { ?>
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
                              <td><select name="ecflashsale_module[<?php echo $module_row; ?>][show_name]">
                                  <?php if ($module['show_name']) { ?>
                                  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                  <option value="0"><?php echo $text_disabled; ?></option>
                                  <?php } else { ?>
                                  <option value="1"><?php echo $text_enabled; ?></option>
                                  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                  <?php } ?>
                                </select></td>
                            </tr>
                             <tr>
                              <td><?php echo $this->language->get("entry_show_description"); ?></td>
                              <td><select name="ecflashsale_module[<?php echo $module_row; ?>][show_description]">
                                  <?php if ($module['show_description']) { ?>
                                  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                  <option value="0"><?php echo $text_disabled; ?></option>
                                  <?php } else { ?>
                                  <option value="1"><?php echo $text_enabled; ?></option>
                                  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                  <?php } ?>
                                </select></td>
                            </tr>
                            <tr>
                              <td><?php echo $this->language->get("entry_show_saleoff"); ?></td>
                              <td><select name="ecflashsale_module[<?php echo $module_row; ?>][show_sale_off]">
                                  <?php if ($module['show_sale_off']) { ?>
                                  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                  <option value="0"><?php echo $text_disabled; ?></option>
                                  <?php } else { ?>
                                  <option value="1"><?php echo $text_enabled; ?></option>
                                  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                  <?php } ?>
                                </select></td>
                            </tr>
                            <tr>
                              <td><?php echo $this->language->get("entry_show_expire_date"); ?></td>
                              <td><select name="ecflashsale_module[<?php echo $module_row; ?>][show_expire_date]">
                                  <?php if ($module['show_expire_date']) { ?>
                                  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                  <option value="0"><?php echo $text_disabled; ?></option>
                                  <?php } else { ?>
                                  <option value="1"><?php echo $text_enabled; ?></option>
                                  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                  <?php } ?>
                                </select></td>
                            </tr>
                            <tr>
                                <td><?php echo $this->language->get("entry_enable_deadline_message"); ?></td>
                                <td><select name="ecflashsale_module[<?php echo $module_row; ?>][enable_message]">
                                  <?php
                                    if(!empty($yesno)){
                                      foreach($yesno as $key=>$val){
                                        if(isset($module['enable_message']) && $key == $module['enable_message']){
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
                        </td>
                       </tr>  
                       <tr id="group_field_mode_mini_<?php echo $module_row; ?>" class="group_field_<?php echo $module_row; ?>" style="display:none">
                          <td colspan="2">
                            <table class="form">
                              <tr>
                                <td><?php echo $this->language->get("entry_show_discount_percent"); ?></td>
                                <td><select name="ecflashsale_module[<?php echo $module_row; ?>][show_discount_percent]">
                                  <?php
                                    if(!empty($yesno)){
                                      foreach($yesno as $key=>$val){
                                        if(isset($module['show_discount_percent']) && $key == $module['show_discount_percent']){
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
                                <td><?php echo $this->language->get("entry_show_discount_price"); ?></td>
                                <td><select name="ecflashsale_module[<?php echo $module_row; ?>][show_discount_price]">
                                  <?php
                                    if(!empty($yesno)){
                                      foreach($yesno as $key=>$val){
                                        if(isset($module['show_discount_price']) && $key == $module['show_discount_price']){
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
                                <td><?php echo $this->language->get("entry_show_quantity"); ?></td>
                                <td><select name="ecflashsale_module[<?php echo $module_row; ?>][show_quantity]">
                                  <?php
                                    if(!empty($yesno)){
                                      foreach($yesno as $key=>$val){
                                        if(isset($module['show_quantity']) && $key == $module['show_quantity']){
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
                          </td>
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
CKEDITOR.replace('description-0-<?php echo $language['language_id']; ?>', {
  filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
  filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
  filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
  filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
  filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
});
<?php } ?>

<?php foreach ($languages as $language) { ?>
CKEDITOR.replace('description-1-<?php echo $language['language_id']; ?>', {
  filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
  filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
  filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
  filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
  filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
});
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

function change_mode(module_index) {
  var select_value = $("#group_selector_"+module_index).val();

  if($("#group_field_mode_"+select_value+"_"+module_index)) {
    $(".group_field_"+module_index).hide();
    $("#group_field_mode_"+select_value+"_"+module_index).show();
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
              
              $('#product_'+module_index).append('<div id="product_'+module_index+'_' + ui.item.value + '">' + ui.item.label + '<img src="view/image/delete.png" /><input type="hidden" name="ecflashsale_setting[products][]" value="' + ui.item.value + '" /></div>');

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
  function clearDeals(){
    var r=confirm("<?php echo $this->language->get("text_warning_clear_deals");?>")
    if (r==true)
      {
        $("#action").val("clear_deal");
        $("#form").submit();
      }
  }
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
  html +='<input type="hidden" name="ecflashsale_module['+module_row+'][module_id]" value="'+module_row+'"/>';
  html +='         <table class="form">';
  html +='          <tr>';
  html +='            <td><?php echo $entry_layout; ?></td>';
  html +='            <td><select name="ecflashsale_module['+module_row+'][layout_id]">';
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
  html +='                  <input type="checkbox" name="ecflashsale_module['+module_row+'][store_id][]" value="0" checked="checked"/>';
  html +='        <?php echo $text_default; ?>';
  html +='                </div>';
                  <?php foreach ($stores as $store) { ?>
                  <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
  html +='                <div class="<?php echo $class; ?>">';
  html +='                  <input type="checkbox" name="ecflashsale_module['+module_row+'][store_id][]" value="<?php echo $store['store_id']; ?>" />';
  html +='                  <?php echo $store['name']; ?>';
  html +='                </div>';
                  <?php } ?>
  html +='              </div></td>';
  html +='    </tr>';

  html +='          <tr>';
  html +='            <td><?php echo $entry_position; ?></td>';
  html +='            <td><select name="ecflashsale_module['+module_row+'][position]">';
                           <?php foreach( $positions as $pos ) { ?>
  html +='                                  <option value="<?php echo $pos;?>"><?php echo $this->language->get('text_'.$pos); ?></option>';
                                    <?php } ?> 
  html +='                                </select></td>';
  html +='          </tr>';
  html +=' <tr>';
  html +='                      <td><?php echo $this->language->get("entry_custom_position"); ?></td>';
  html +='                      <td><input type="text" name="ecflashsale_module['+module_row+'][custom_position]" value="" size="30" /></td>';
  html +='                    </tr>';
  html +='          <tr>';
  html +='            <td><?php echo $entry_status; ?></td>';
  html +='            <td><select name="ecflashsale_module['+module_row+'][status]">';
  html +='                <option value="1"><?php echo $text_enabled; ?></option>';
  html +='                <option value="0"><?php echo $text_disabled; ?></option>';

  html +='              </select></td>';
  html +='          </tr>';
  html +='          <tr>';
  html +='            <td><?php echo $entry_sort_order; ?></td>';
  html +='            <td><input type="text" name="ecflashsale_module['+module_row+'][sort_order]" value="" size="3" /></td>';
  html +='          </tr>';
  html +='        </table>';
  
  html +='                  <table class="form">';
  html +='<tr>';
  html +='                        <td colspan="2" class="bg-group"><span class="group_title"><?php echo $this->language->get("entry_module_setting"); ?></span></td>';
  html +='                      </tr>';
  html +='<tr>';
  html +='                        <td><?php echo $this->language->get("entry_block_prefix"); ?></td>';
  html +='                        <td><input size="30" type="text" name="ecflashsale_module['+module_row+'][prefix]" value=""/></td>';
  html +='                     </tr>';
  html +='<tr>';
  html +='                      <td><?php echo $this->language->get("entry_show_mode"); ?></td>';
  html +='                      <td><select name="ecflashsale_module['+module_row+'][show_mode]">';
                            <?php foreach($show_modes as $key=>$val) { ?>
  html +='                           <option value="<?php echo $key; ?>"><?php echo $val; ?></option>';
                            <?php } ?>
  html +='                       </select></td>';
  html +='                    </tr>';
  html +='  <tr>';
  html +='                       <td><?php echo $this->language->get("entry_popup_width"); ?></td>';
  html +='                       <td><input type="text" name="ecflashsale_module['+module_row+'][popup_width]" value="60%" size="10" /></td>';
  html +='                     </tr>';
  html +='<tr>';
  html +='                      <td><?php echo $this->language->get("entry_mode"); ?></td>';
  html +='                      <td><select name="ecflashsale_module['+module_row+'][mode]" id="group_selector_'+module_row+'" onchange="change_mode('+module_row+')">';
                           <?php
                             foreach($modes as $key=>$val) {
                               if($key == "block") {
                                ?>
  html +='                              <option value="<?php echo $key;?>" selected="selected"><?php echo $val ?></option>';
                                <?php
                               } else {
                                ?>
  html +='                              <option value="<?php echo $key;?>"><?php echo $val ?></option>';
                                <?php
                               }
                             }
                           ?>
  html +='                        </select>';
  html +='                      </td>';
  html +='                     </tr>';
  html +='<tr id="group_field_mode_block_'+module_row+'" class="group_field_'+module_row+'" style="display:none">';
  html +='                      <td colspan="2">';
  html +='                        <table class="form">';
  html +=' <tr>';
  html +='                      <td><?php echo $this->language->get("entry_flashsale"); ?></td>';
  html +='                      <td><select name="ecflashsale_module['+module_row+'][flashsale_id]" size="10">';
  html +='                          <option value="" selected="selected"><?php echo $this->language->get("text_choose_flashsale");?></option>';
                            <?php 
                              foreach($flashsales as $flashsale){
                                  ?>
  html +='                                <option value="<?php echo $flashsale['ecflashsale_id'];?>"><?php echo $flashsale['name'] ?></option>';
                                  <?php
                              }
                            ?>
  html +='                        </select></td>';
  html +='                    </tr>';
  html +='<tr>';
  html +='                            <td><?php echo $this->language->get("entry_custom_url"); ?></td>';
  html +='                            <td><input type="text" name="ecflashsale_module['+module_row+'][custom_url]" value="" size="60" /></td>';
  html +='                          </tr>';
  html +='<tr>';
  html +='                      <td><?php echo $this->language->get("entry_description_maxchars"); ?></td>';
  html +='                      <td><input type="text" name="ecflashsale_module['+module_row+'][description_maxchars]" value="<?php echo isset($default['description_maxchars'])?$default['description_maxchars']:"100"; ?>" size="10" /></td>';
  html +='                    </tr>';
  html +='                      <tr>';
  html +='                      <td><?php echo $this->language->get("entry_module_image"); ?></td>';
  html +='                      <td class="left"><div class="image"><img src="<?php echo isset($default['thumb'])?$default['thumb']:""; ?>" alt="" id="thumb'+module_row+'" />';
  html +='                          <input type="hidden" name="ecflashsale_module['+module_row+'][image]" value="<?php echo isset($default['image'])?$default['image']:""; ?>" id="image'+module_row+'"  />';
  html +='                          <br />';
  html +='                          <a onclick="image_upload(\'image'+module_row+'\', \'thumb'+module_row+'\');"><?php echo $this->language->get("text_browse"); ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="$(\'#thumb'+module_row+'\').attr(\'src\', \'<?php echo $no_image; ?>\'); $(\'#image'+module_row+'\').attr(\'value\', \'\');"><?php echo $this->language->get("text_clear"); ?></a></div></td>';
  html +='                    </tr>';
  html +='                     <tr>';
  html +='                        <td><?php echo $this->language->get("text_image_size"); ?></td>';
  html +='                        <td><input type="text" name="ecflashsale_module['+module_row+'][image_width]" value="<?php echo isset($default['image_width'])?$default['image_width']:"600"; ?>" size="12"/> x <input type="text" name="ecflashsale_module['+module_row+'][image_height]" value="<?php echo isset($default['image_height'])?$default['image_height']:"400"; ?>" size="12"/></td>';
  html +='                      </tr>';
  html +='<tr>';
  html +='                        <td><?php echo $this->language->get("text_module_size"); ?></td>';
  html +='                        <td><input type="text" name="ecflashsale_module['+module_row+'][module_width]" value="<?php echo isset($default['module_width'])?$default['module_width']:"100%"; ?>" size="12"/> - <input type="text" name="ecflashsale_module['+module_row+'][module_height]" value="<?php echo isset($default['module_height'])?$default['module_height']:"100%"; ?>" size="12"/></td>';
  html +='                      </tr>';
  html +=' <tr>';
  html +='                      <td><?php echo $this->language->get("entry_show_banner_image"); ?></td>';
  html +='                      <td><select name="ecflashsale_module['+module_row+'][show_image]">';
     
  html +='                          <option value="1"><?php echo $text_enabled; ?></option>';
  html +='                          <option value="0"><?php echo $text_disabled; ?></option>';

  html +='                        </select></td>';
  html +='                    </tr>';
  html +='                    <tr>';
  html +='                      <td><?php echo $this->language->get("entry_show_name"); ?></td>';
  html +='                      <td><select name="ecflashsale_module['+module_row+'][show_name]">';
  html +='                          <option value="1"><?php echo $text_enabled; ?></option>';
  html +='                          <option value="0"><?php echo $text_disabled; ?></option>';
  html +='                        </select></td>';
  html +='                    </tr>';
  html +='                     <tr>';
  html +='                      <td><?php echo $this->language->get("entry_show_description"); ?></td>';
  html +='                      <td><select name="ecflashsale_module['+module_row+'][show_description]">';
  html +='                          <option value="1"><?php echo $text_enabled; ?></option>';
  html +='                          <option value="0"><?php echo $text_disabled; ?></option>';
  html +='                        </select></td>';
  html +='                    </tr>';
  html +='<tr>';
  html +='                      <td><?php echo $this->language->get("entry_show_saleoff"); ?></td>';
  html +='                      <td><select name="ecflashsale_module['+module_row+'][show_sale_off]">';
  html +='                          <option value="1"><?php echo $text_enabled; ?></option>';
  html +='                          <option value="0"><?php echo $text_disabled; ?></option>';
  html +='                        </select></td>';
  html +='                    </tr>';
  html +='<tr>';
  html +='                      <td><?php echo $this->language->get("entry_show_expire_date"); ?></td>';
  html +='                      <td><select name="ecflashsale_module['+module_row+'][show_expire_date]">';

  html +='                          <option value="1"><?php echo $text_enabled; ?></option>';
  html +='                          <option value="0"><?php echo $text_disabled; ?></option>';
  html +='                        </select></td>';
  html +='                    </tr>';
  html +='<tr>';
  html +='                        <td><?php echo $this->language->get("entry_enable_deadline_message"); ?></td>';
  html +='                        <td><select name="ecflashsale_module['+module_row+'][enable_message]">';
  html +='                          <option value="1"><?php echo $text_enabled; ?></option>';
  html +='                          <option value="0"><?php echo $text_disabled; ?></option>';
  html +='                        </select></td>';
  html +='                      </tr>';
  html +='</table>';
  html +='                      </td>';
  html +='                     </tr>  ';
  html +='                     <tr id="group_field_mode_mini_'+module_row+'" class="group_field_'+module_row+'" style="display:none">';
  html +='                        <td colspan="2">';
  html +='                          <table class="form">';
  html +='<tr>';
  html +='                              <td><?php echo $this->language->get("entry_show_discount_percent"); ?></td>';
  html +='                              <td><select name="ecflashsale_module['+module_row+'][show_discount_percent]">';
                                  <?php
                                    if(!empty($yesno)){
                                      foreach($yesno as $key=>$val){
                                        ?>

  html +='                              <option value="<?php echo $key; ?>"><?php echo $val;?></option>';
                                        <?php
                                      }
                                    }
                                  ?>
  html +='                              </select></td>';
  html +='                            </tr>';
                              
  html +='                            <tr>';
  html +='                              <td><?php echo $this->language->get("entry_show_discount_price"); ?></td>';
  html +='                              <td><select name="ecflashsale_module['+module_row+'][show_discount_price]">';
                                  <?php
                                    if(!empty($yesno)){
                                      foreach($yesno as $key=>$val){
                                        ?>
  html +='                                      <option value="<?php echo $key; ?>"><?php echo $val;?></option>';
                                        <?php
                                      }
                                    }
                                  ?>
  html +='                              </select></td>';
  html +='                            </tr>';
  html +='                            <tr>';
  html +='                              <td><?php echo $this->language->get("entry_show_quantity"); ?></td>';
  html +='                              <td><select name="ecflashsale_module['+module_row+'][show_quantity]">';
                                  <?php
                                    if(!empty($yesno)){
                                      foreach($yesno as $key=>$val){
                                        ?>
  html +='                                      <option value="<?php echo $key; ?>"><?php echo $val;?></option>';
                                        <?php
                                      }
                                    }
                                  ?>
  html +='                              </select></td>';
  html +='                            </tr>';
  html +='                          </table>';
  html +='                        </td>';
  html +='                    </tr>';
  html +='                    </table>';
  html +='      </div>';
  
  $('#tab-block-position').find(".tab-inner").first().append(html);
  
  $('#module-add').before('<a href="#tab-module-' + module_row + '" id="module-' + module_row + '"><?php echo $tab_block; ?> ' + module_row + '&nbsp;<img src="view/image/delete.png" alt="" onclick="$(\'.vtabs a:first\').trigger(\'click\'); $(\'#module-' + module_row + '\').remove(); $(\'#tab-module-' + module_row + '\').remove(); return false;" /></a>');
  
  $('.vtabs a').tabs();

  change_mode( module_row );

  $('.date').datepicker({dateFormat: 'yy-mm-dd'});

  $('#language-' + module_row + ' a').tabs();

  $('#module-' + module_row).trigger('click');

  module_row++;
}
//--></script> 
<script type="text/javascript"><!--

$('.vtabs a').tabs();
$('#tabs a').tabs();

$('#tabs a').click( function(){
  $.cookie("actived_tab", $(this).attr("href") );
} );

if( $.cookie("actived_tab") !="undefined" ){
  $('#tabs a').each( function(){
    if( $(this).attr("href") ==  $.cookie("actived_tab") ){
      $(this).click();
      return ;
    }
  } );
  
}
//--></script>

<script type="text/javascript"><!--

$('#language-0 a').tabs();
$('#language-1 a').tabs();

//--></script> 
<?php echo $footer; ?>