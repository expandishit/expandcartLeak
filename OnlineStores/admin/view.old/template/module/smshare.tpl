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
    <div class="buttons">
		<a onclick="$('#form').submit();" class="button btn btn-primary"><?php echo $button_save; ?></a>
		<a onclick="location = '<?php echo $cancel; ?>';" class="button btn btn-primary"><?php echo $button_cancel; ?></a>
	</div>
  </div>
  <div class="content">
    <ul class="nav nav-tabs" id="module-tabs">
        <li class="active"><a href="#tab-supported" data-toggle="tab"><?php echo $text_tab_supported; ?></a></li>
		<li><a href="#tab-general" data-toggle="tab"><?php echo $text_gateway_setup; ?></a></li>
		<li><a href="#tab-templating" data-toggle="tab"><?php echo $text_sms_tem; ?></a></li>
		<li><a href="#tab-customer" data-toggle="tab"><?php echo $text_customer_notif; ?></a></li>
		<li><a href="#tab-owner" data-toggle="tab"><?php echo $text_admin_notif; ?></a></li>
		<li><a href="#tab-filtering" data-toggle="tab"><?php echo $text_sms_filter; ?></a></li>
		<li><a href="#tab-rewriting" data-toggle="tab"><?php echo $text_number_rewrite; ?></a></li>
		<li><a href="#tab-troubleshooting" data-toggle="tab"><?php echo $text_logs; ?></a></li>
    </ul>
    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <div class="tab-content">
            <div class="tab-pane active in" id="tab-supported">
                    <h4><?php echo $text_supported_providers; ?></h4>
                <br>
                    <ul style="font-size: 16px;">
                        <li>
                            <a href="https://www.mobily.ws/" target="_blank">mobily.ws</a>
                        </li>
                        <li>
                            <a href="http://www.oursms.net/" target="_blank">oursms.net</a>
                        </li>
                        <li>
                            <a href="http://www.msegat.com/" target="_blank">msegat.com</a>
                        </li>
                        <li>
                            <a href="https://www.clicksend.com/" target="_blank">clicksend.com</a>
                        </li>
                        <li>
                            <a href="https://smsgateway.me/" target="_blank">smsgateway.me</a>
                        </li>
                        <li>
                            <a href="https://www.twilio.com/" target="_blank">twilio.com</a>
                        </li>
                        <li>
                            <a href="https://www.nexmo.com/" target="_blank">nexmo.com</a>
                        </li>
                        <li>
                            <a href="https://www.plivo.com/" target="_blank">plivo.com</a>
                        </li>
                        <li>
                            <a href="https://msg91.com/" target="_blank">msg91.com</a>
                        </li>
                    </ul>
                <br>
                    <h5><?php echo $text_supported_providers_help; ?></h5>
            </div>

            <div class="tab-pane" id="tab-general">
                <table class="form">
                    <tr>
                      <td><?php echo $text_api_url; ?></td>
                      <td><input type="url" name="smshare_api_url" value="<?php echo $smshare_api_url; ?>" size="44"/></td>
                    </tr>
                    <tr>
                      <td><?php echo $text_api_http_method; ?></td>
                      <td>
                        <select name="smshare_api_http_method">
                            <option value="get"   <?php if($smshare_api_http_method == 'get')  echo 'selected'; ?> ><?php echo $text_get; ?></option>
                            <option value="post (multipart/form-data)"                <?php if($smshare_api_http_method == 'post (multipart/form-data)')               echo 'selected'; ?> ><?php echo $text_post_1; ?></option>
                            <option value="post (application/x-www-form-urlencoded)"  <?php if($smshare_api_http_method == 'post (application/x-www-form-urlencoded)') echo 'selected'; ?> ><?php echo $text_post_2; ?></option>
                        </select>
                        <div class="help">
                            <?php echo $text_api_method_help; ?>
                        </div>
                      </td>
                    </tr>

                    <tr>
                      <td>
                          <?php echo $text_dest_field; ?>
                        <div class="help">
                            <?php echo $text_dest_field_help; ?>
                        </div>
                      </td>
                      <td><input type="text" name="smshare_api_dest_var" value="<?php echo $smshare_api_dest_var; ?>" size="12" placeholder="<?php echo $text_dest_field_placeholder; ?>" /></td>
                    </tr>

                    <tr>
                      <td>
                          <?php echo $text_msg_field; ?>
                          <div class="help">
                              <?php echo $text_msg_field_help; ?>
                          </div>
                      </td>
                      <td>
                        <input type="text" id="smshare_api_msg_var" name="smshare_api_msg_var" value="<?php echo $smshare_api_msg_var; ?>" size="12" placeholder="<?php echo $text_msg_field_placeholder; ?>" />
                        <label><input type="checkbox"
                                      name="smshare_api_msg_to_unicode"
                                      <?php if(isset($smshare_api_msg_to_unicode) && $smshare_api_msg_to_unicode === 'on') echo 'checked' ?>

                               ><?php echo $text_unicode; ?></label>
                        <div class="help"><?php echo $text_unicode_help; ?></div>
                        <div class="help"><?php echo $text_unicode_help_2; ?></div>
                      </td>
                    </tr>

                    <tr>
                      <td><?php echo $text_additional_fields; ?> <a id="smshare-add-new-field" class="button btn btn-primary" href="#"><?php echo $text_add_new_field; ?></a></td>
                      <td>

                        <div id="fields-wrapper">

                            <?php

                                if(! is_array($smshare_api_kv)){
                                    $smshare_api_kv = array();
                                }

                                $tmpl_arr = array("key" => "", "val" => "", "encode" => "", "is_tmpl" => true);
                                array_push($smshare_api_kv, $tmpl_arr);

                                foreach ($smshare_api_kv as $api_kv) {
                            ?>

                                <div <?php if(isset($api_kv['is_tmpl'])) {	echo 'id="data-tv-fields-tmpl"';} ?> style="margin-bottom: 5px;" data-tv-kv>
                                    <label>
                                        <?php echo $text_name; ?>
                                        <input type="text" size="22" data-tv-kv-key placeholder="<?php echo $text_field_name; ?>" value="<?php echo $api_kv['key']; ?>"  />
                                    </label>

                                    <label>
                                        <?php echo $text_value; ?>
                                        <input type="text" size="22" data-tv-kv-val placeholder="<?php echo $text_field_value; ?>" value="<?php echo $api_kv['val']; ?>" />
                                    </label>

                                    <label>
                                      <?php echo $text_url_encode; ?> <input type="checkbox" data-tv-kv-encode
                                      <?php if( (isset($api_kv['is_tmpl'])) ||
                                                (isset($api_kv['encode'])   && $api_kv['encode'] === 'on') ){
                                                    echo "checked";
                                            }
                                      ?> >
                                    </label>

                                    <a class="button btn btn-primary" href="#" style="" title="<?php echo $text_remove_field; ?>" data-tv-remove>×</a>
                                </div>

                            <?php
                                }
                            ?>

                        </div>

                        <div class="help" style="margin-top: 22px;">
                            <?php echo $text_url_encode_help; ?>
                        </div>

                      </td>
                    </tr>
                </table>
		    </div>

	        <div class="tab-pane" id="tab-templating">
                <table class="form">
                    <tr>
                        <td style="vertical-align: top;">
                            <div><?php echo $text_sms_template_system; ?></div>
                            <div class="help"><?php echo $text_sms_temp_sys_1; ?></div>
                        </td>
                        <td>
                            <div class="help">
                                <span><?php echo $text_available_var; ?></span>
                                <table>
                                    <tr><td><?php echo $text_firstname; ?>          </td><td> <?php echo $text_arrow; ?> </td><td>{firstname}</td></tr>
                                    <tr><td><?php echo $text_lastname; ?>           </td><td> <?php echo $text_arrow; ?> </td><td>{lastname}</td></tr>
                                    <tr><td><?php echo $text_phonenumber; ?>       </td><td> <?php echo $text_arrow; ?> </td><td>{phonenumber}</td></tr>
                                    <tr><td><?php echo $text_orderid; ?>           </td><td> <?php echo $text_arrow; ?> </td><td>{order_id}</td></tr>
                                    <tr><td><?php echo $text_total; ?>              </td><td> <?php echo $text_arrow; ?> </td><td>{total}</td></tr>
                                    <tr><td><?php echo $text_storeurl; ?>          </td><td> <?php echo $text_arrow; ?> </td><td>{store_url}</td></tr>
                                    <tr><td><?php echo $text_shippingadd1; ?> </td><td> <?php echo $text_arrow; ?> </td><td>{shipping_address_1}</td></tr>
                                    <tr><td><?php echo $text_shippingadd2; ?> </td><td> <?php echo $text_arrow; ?> </td><td>{shipping_address_2}</td></tr>
                                    <tr><td><?php echo $text_payadd1; ?>  </td><td> <?php echo $text_arrow; ?> </td><td>{payment_address_1}</td></tr>
                                    <tr><td><?php echo $text_payadd2; ?>  </td><td> <?php echo $text_arrow; ?> </td><td>{payment_address_2}</td></tr>
                                    <tr><td><?php echo $text_paymethod; ?>     </td><td> <?php echo $text_arrow; ?> </td><td>{payment_method}</td></tr>
                                    <tr><td><?php echo $text_shipmethod; ?>    </td><td> <?php echo $text_arrow; ?> </td><td>{shipping_method}</td></tr>
                                </table>
                            </div>
                            <br />
                            <div>
                                <?php echo $text_sms_system_example; ?>
                            </div>
                        </td>
                    </tr>
                </table>
	        </div>

		    <div class="tab-pane" id="tab-customer">
                <table class="form">

                    <!-- Notify customer on registration -->
                    <tr>
                      <td><?php echo $smshare_entry_notify_customer_by_sms_on_registration; ?></td>
                        <td><?php if ($smshare_config_notify_customer_by_sms_on_registration) { ?>
                            <input type="radio" name="smshare_config_notify_customer_by_sms_on_registration" value="1" checked="checked" />
                            <?php echo $text_yes; ?>
                            <input type="radio" name="smshare_config_notify_customer_by_sms_on_registration" value="0" />
                            <?php echo $text_no; ?>
                            <?php } else { ?>
                            <input type="radio" name="smshare_config_notify_customer_by_sms_on_registration" value="1" />
                            <?php echo $text_yes; ?>
                            <input type="radio" name="smshare_config_notify_customer_by_sms_on_registration" value="0" checked="checked" />
                            <?php echo $text_no; ?>
                            <?php } ?>
                        </td>
                    </tr>
                    <!-- TODO entry i18n -->
                    <tr>
                        <td>
                            <div><?php echo $text_cus_reg_temp; ?></div>
                            <div class="help"><?php echo $smshare_entry_cstmr_reg_available_vars; ?></div>
                        </td>
                        <td>
                            <textarea name="smshare_config_sms_template_for_customer_notif_on_registration" cols="40" rows="5"><?php echo $smshare_config_sms_template_for_customer_notif_on_registration; ?></textarea>
                        </td>
                    </tr>


                    <!-- SMS Notification -->
                    <tr>
                        <td><?php echo $text_sms_confirm; ?></td>
                        <td><?php if ($smshare_config_sms_confirm) { ?>
                            <input type="radio" name="smshare_config_sms_confirm" value="1" checked="checked" />
                            <?php echo $text_yes; ?>
                            <input type="radio" name="smshare_config_sms_confirm" value="0" />
                            <?php echo $text_no; ?>
                            <?php } else { ?>
                            <input type="radio" name="smshare_config_sms_confirm" value="1" />
                            <?php echo $text_yes; ?>
                            <input type="radio" name="smshare_config_sms_confirm" value="0" checked="checked" />
                            <?php echo $text_no; ?>
                            <?php } ?>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <div><?php echo $text_sms_confirm_trials; ?></div>
                            <div class="help"><?php echo $text_sms_confirm_trials_help; ?></div>
                        </td>
                        <td>
                            <input type="text" id="smshare_config_sms_trials" name="smshare_config_sms_trials" value="<?php echo $smshare_config_sms_trials; ?>" size="12" />
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <div><?php echo $text_sms_confirm_template; ?></div>
                            <div class="help"><?php echo $text_sms_confirm_template_help; ?></div>
                        </td>
                        <td>
                            <textarea name="smshare_config_sms_template" cols="40" rows="5"><?php echo $smshare_config_sms_template; ?></textarea>
                        </td>
                    </tr>


                    <!-- Notify customer on new order -->
                    <tr>
                      <td><?php echo $smshare_entry_notify_customer_by_sms_on_checkout; ?></td>
                        <td><?php if ($smshare_config_notify_customer_by_sms_on_checkout) { ?>
                            <input type="radio" name="smshare_config_notify_customer_by_sms_on_checkout" value="1" checked="checked" />
                            <?php echo $text_yes; ?>
                            <input type="radio" name="smshare_config_notify_customer_by_sms_on_checkout" value="0" />
                            <?php echo $text_no; ?>
                            <?php } else { ?>
                            <input type="radio" name="smshare_config_notify_customer_by_sms_on_checkout" value="1" />
                            <?php echo $text_yes; ?>
                            <input type="radio" name="smshare_config_notify_customer_by_sms_on_checkout" value="0" checked="checked" />
                            <?php echo $text_no; ?>
                            <?php } ?>
                        </td>
                    </tr>
                    <!-- TODO entry i18n -->
                    <tr>
                        <td>
                            <span><?php echo $text_cus_order_temp; ?> </span>
                        </td>
                        <td>
                            <textarea name="smshare_config_sms_template_for_customer_notif_on_checkout" cols="40" rows="5"><?php echo $smshare_config_sms_template_for_customer_notif_on_checkout; ?></textarea>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <div><?php echo $text_no_send_kw; ?> </div>
                            <div class="help" style="text-align: justify;">
                                <?php echo $text_no_send_help; ?>
                            </div>
                        </td>
                        <td>
                            <textarea name="smshare_cfg_donotsend_sms_on_checkout_coupon_keywords" cols="40" rows="5"><?php echo $smshare_cfg_donotsend_sms_on_checkout_coupon_keywords; ?></textarea>
                        </td>
                    </tr>

                    <tr data-tv-observer-container>
                      <td>
                          <div><?php echo $text_sms_order_status; ?></div>
                          <div class="help" style="text-align: justify;">
                              <?php echo $text_sms_order_status_help; ?>
                          </div>
                          <div style="text-align:center;">
                            <a data-add-order-observer class="button btn btn-primary" href="#"><?php echo $text_add_new_fields; ?></a>
                          </div>
                      </td>
                      <style>
                          .tv-statuses select, .tv-statuses textarea{
                              vertical-align:middle;
                          }
                      </style>

                      <td class="tv-statuses">
                        <div data-observers-wrapper>
                            <?php
                            foreach ($smshare_cfg_odr_observers as $observer) {
                            ?>
                              <div <?php if(isset($observer['is_tmpl'])) { echo 'data-tv-observer-tmpl';} ?> data-tv-observer data-tv-config-name="smshare_cfg_odr_observers" style="margin-bottom: 11px;">
                                <select data-tv-observer-status>
                                    <option value="0"><?php echo $text_status; ?></option>
                                    <option disabled><?php echo $text_seperator; ?></option>
                                    <?php foreach ($order_statuses as $order_status) { ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"
                                            <?php if($order_status['order_status_id'] == $observer['key']) echo 'selected' ?>
                                    ><?php echo $order_status['name']; ?></option>
                                    <?php } ?>
                                </select>

                                <textarea cols="40" rows="5" data-tv-observer-msg><?php echo $observer['val']; ?></textarea>
                              </div>
                            <?php
                            }
                            ?>
                        </div>
                      </td>
                    </tr>

                </table>
            </div>

	        <div class="tab-pane" id="tab-owner">
                <table class="form">

                <!-- Notify store owner on registration -->
                    <tr>
                      <td><?php echo $smshare_entry_ntfy_admin_by_sms_on_reg; ?></td>
                      <td><?php if ($smshare_cfg_ntfy_admin_by_sms_on_reg) { ?>
                          <input type="radio" name="smshare_cfg_ntfy_admin_by_sms_on_reg" value="1" checked="checked" />
                          <?php echo $text_yes; ?>
                          <input type="radio" name="smshare_cfg_ntfy_admin_by_sms_on_reg" value="0" />
                          <?php echo $text_no; ?>
                          <?php } else { ?>
                          <input type="radio" name="smshare_cfg_ntfy_admin_by_sms_on_reg" value="1" />
                          <?php echo $text_yes; ?>
                          <input type="radio" name="smshare_cfg_ntfy_admin_by_sms_on_reg" value="0" checked="checked" />
                          <?php echo $text_no; ?>
                          <?php } ?>
                      </td>
                    </tr>
                    <!-- TODO entry i18n and make common with the customer one. -->
                    <tr>
                        <td>
                            <div><?php echo $text_admin_cust_reg; ?></div>
                            <div class="help"><?php echo $smshare_entry_cstmr_reg_available_vars; ?></div>
                        </td>
                        <td>
                            <textarea name="smshare_cfg_sms_tmpl_for_admin_notif_on_reg" cols="40" rows="5"><?php echo $smshare_cfg_sms_tmpl_for_admin_notif_on_reg; ?></textarea>
                        </td>
                    </tr>


                    <tr>
                      <td><?php echo $smshare_entry_notify_admin_by_sms_on_checkout; ?></td>
                        <td><?php if ($smshare_config_notify_admin_by_sms_on_checkout) { ?>
                            <input type="radio" name="smshare_config_notify_admin_by_sms_on_checkout" value="1" checked="checked" />
                            <?php echo $text_yes; ?>
                            <input type="radio" name="smshare_config_notify_admin_by_sms_on_checkout" value="0" />
                            <?php echo $text_no; ?>
                            <?php } else { ?>
                            <input type="radio" name="smshare_config_notify_admin_by_sms_on_checkout" value="1" />
                            <?php echo $text_yes; ?>
                            <input type="radio" name="smshare_config_notify_admin_by_sms_on_checkout" value="0" checked="checked" />
                            <?php echo $text_no; ?>
                            <?php } ?>
                        </td>
                    </tr>

                    <!-- TODO entry i18n -->
                    <tr>
                        <td>
                            <div><?php echo $text_admin_sms_temp; ?></div>
                            <div class="help" style="text-align: justify;">
                                <?php echo $text_admin_sms_temp_help; ?></div>
                        </td>
                        <td>
                            <textarea name="smshare_config_sms_template_for_storeowner_notif_on_checkout" cols="40" rows="5"><?php echo $smshare_config_sms_template_for_storeowner_notif_on_checkout; ?></textarea>
                        </td>
                    </tr>

                    <tr>
                        <td><?php echo $smshare_entry_notify_extra_by_sms_on_checkout; ?></td>
                        <td>
                            <textarea name="smshare_config_notify_extra_by_sms_on_checkout" cols="40" rows="5"><?php echo $smshare_config_notify_extra_by_sms_on_checkout; ?></textarea>
                        </td>
                    </tr>

                    <tr data-tv-observer-container>
                      <td>
                          <div><?php echo $text_admin_order_status; ?></div>
                          <div class="help" style="text-align: justify;">
                              <?php echo $text_admin_order_status_help; ?>
                          </div>
                          <div style="text-align:center;">
                            <a data-add-order-observer class="button btn btn-primary" href="#"><?php echo $text_add_new_fields_2; ?></a>
                          </div>
                      </td>

                      <td class="tv-statuses">
                        <div data-observers-wrapper>
                            <?php
                            foreach ($smshare_cfg_odr_observers_for_admin as $observer) {
                            ?>
                              <div <?php if(isset($observer['is_tmpl'])) { echo 'data-tv-observer-tmpl';} ?> data-tv-observer data-tv-config-name="smshare_cfg_odr_observers_for_admin" style="margin-bottom: 11px;">
                                <select data-tv-observer-status>
                                    <option value="0"><?php echo $text_status_2; ?></option>
                                    <option disabled><?php echo $text_seperator_2; ?></option>
                                    <?php foreach ($order_statuses as $order_status) { ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"
                                            <?php if($order_status['order_status_id'] == $observer['key']) echo 'selected' ?>
                                    ><?php echo $order_status['name']; ?></option>
                                    <?php } ?>
                                </select>

                                <textarea cols="40" rows="5" data-tv-observer-msg><?php echo $observer['val']; ?></textarea>
                              </div>
                            <?php
                            }
                            ?>
                        </div>
                      </td>
                    </tr>

                </table>
            </div>

	        <div class="tab-pane" id="tab-filtering">
                <table class="form">
                    <tr>
                        <td>
                            <div><?php echo $text_phone_num_filter; ?></div>
                            <div class="help" style="text-align: justify;"><?php echo $text_phone_num_filter_help; ?>
                            </div>
                        </td>
                        <td>
                            <input type="text" name="smshare_config_number_filtering" value="<?php echo $smshare_config_number_filtering; ?>" size="41" />
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <div><?php echo $text_filter_size; ?></div>
                            <div class="help" style="text-align: justify;"><?php echo $text_filter_size_help; ?>
                            </div>
                        </td>
                        <td>
                            <input type="text" name="smshare_cfg_num_filt_by_size" value="<?php echo $smshare_cfg_num_filt_by_size; ?>" size="2"/>
                        </td>
                    </tr>
                </table>
	        </div>

	        <div class="tab-pane" id="tab-rewriting">
              <table class="form">
                <tr>
                    <td>
                        <div><?php echo $text_phone_rewrite; ?></div>
                        <div class="help" style="text-align: justify;"><?php echo $text_phone_rewrite_help; ?>
                        </div>
                    </td>
                    <td>
                        <?php echo $text_replace_1_occ; ?>
                        <input type="text" name="smshare_cfg_number_rewriting_search"  value="<?php echo $smshare_cfg_number_rewriting_search; ?>"  size="8" placeholder="<?php echo $text_pattern; ?>" />
                        <?php echo $text_by; ?>
                        <input type="text" name="smshare_cfg_number_rewriting_replace" value="<?php echo $smshare_cfg_number_rewriting_replace; ?>" size="8"  placeholder="<?php echo $text_substitution; ?>" />
                    </td>
                </tr>
              </table>
	        </div>

	        <div class="tab-pane" id="tab-troubleshooting">
              <table class="form">
                <tr>
                    <td>
                        <div><?php echo $text_enable_logs; ?> </div>
                        <div class="help" style="text-align: justify;"><?php echo $text_enable_logs_help; ?></div>
                    </td>
                    <td><?php if ($smshare_cfg_log) { ?>
                        <input type="radio" name="smshare_cfg_log" value="1" checked="checked" />
                        <?php echo $text_yes; ?>
                        <input type="radio" name="smshare_cfg_log" value="0" />
                        <?php echo $text_no; ?>
                        <?php } else { ?>
                        <input type="radio" name="smshare_cfg_log" value="1" />
                        <?php echo $text_yes; ?>
                        <input type="radio" name="smshare_cfg_log" value="0" checked="checked" />
                        <?php echo $text_no; ?>
                        <?php } ?>
                    </td>
                </tr>
              </table>
            </div>
	    </div>
    </form>
  </div>
</div>

<script type="text/javascript">
    var Tv = Tv || {};

    Tv.addEventListenersToObserver = function($observerContainer){
        $("[data-add-order-observer]", $observerContainer).click(function(e){
            e.preventDefault();

            var $tmplClone = $("[data-tv-observer-tmpl]", $observerContainer).clone();
            $tmplClone.attr('id', null);
            $tmplClone.find('select option').first().attr('selected', true);
            $tmplClone.find('textarea').val('');
            $("[data-observers-wrapper]", $observerContainer).append($tmplClone);
        });

    }

    Tv.addEventListeners = function(){


        $("#smshare-add-new-field").click(function(e){

            e.preventDefault();

            var $tmplClone = $("#data-tv-fields-tmpl").clone();
            $tmplClone.attr('id', null);
            $tmplClone.find('input').val('');
            $("#fields-wrapper").append($tmplClone);

        });

        $('[data-tv-observer-container]').each(function(index, element){
            Tv.addEventListenersToObserver($(element));
        });

        $(document.body).on('click', 'a[data-tv-remove]', function(e){
            e.preventDefault();
            $(this).parents("div[data-tv-kv]").not('[id=data-tv-fields-tmpl]').remove();
        });


        $("#form").submit(function(){

            /*
             * Gateway params
             */
            var i = 0;
            $("div[data-tv-kv]").each(function(index, element){
                $("input[data-tv-kv-key]", 	  element).attr("name", "smshare_api_kv[" + i + "][key]");
                $("input[data-tv-kv-val]", 	  element).attr("name", "smshare_api_kv[" + i + "][val]");
                $("input[data-tv-kv-encode]", element).attr("name", "smshare_api_kv[" + i + "][encode]");
                i++;
            });

            /*
             * Order observers
             */
            i = 0;
            $("div[data-tv-observer]").each(function(index, element){

                if($("select[data-tv-observer-status]", element).val() === "0") return;

                var name = $(element).attr('data-tv-config-name');
                $("select[data-tv-observer-status]", element).attr("name", name + "[" + i + "][key]");
                $("textarea[data-tv-observer-msg]",  element).attr("name", name + "[" + i + "][val]");
                i++;
            });


        });

    };

    $(function() {
        Tv.addEventListeners();
    });
</script>

<?php echo $footer; ?>