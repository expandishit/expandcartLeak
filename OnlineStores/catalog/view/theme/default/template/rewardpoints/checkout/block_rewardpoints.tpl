<div class="content" style="overflow: hidden">
    <div class="box_rewardpoints">
        <h2>
            <span class='icon'>
                <img src="catalog/view/javascript/rewardpoints/image/icon_gift.png" alt="reward points icon" style="width: 22px; vertical-align: middle" />
            </span>
            <?php echo $this->language->get('entry_title')?>
        </h2>
        <div class="form">
	        <?php if($total_reward_points > 0){ ?>
            <?php echo sprintf($this->language->get('text_you_awarded_total'), number_format($total_reward_points), $this->config->get('text_points_'.$this->language->get('code')))?>
	        <?php } ?>
            <ul style="">
                <?php echo $html_awarded;?>
            </ul>
            <div style="float:left;border-top: 1px dashed;min-width: 99%;padding: 10px 0;">
                <?php if($this->customer->isLogged()){ ?>
                <?php echo sprintf( $this->language->get('text_customer_point_available'), $customer_reward_points, $this->config->get('text_points_'.$this->language->get('code')) )?><br>
                <span style="margin-top:5px;display: block">(<?php echo $exchange_rate['point']; ?> <?php echo $this->config->get('text_points_'.$this->language->get('code'))?> = <?php echo $exchange_rate['rate']; ?>)</span>
	                <div style="margin: 10px 0;">
                </div>
                <div id="range"></div>
                <div style="clear: both"></div>
                <div style="margin: 51px 0 0 0;"><?php echo $this->language->get('entry_point_redeem'); ?> <input type="text" size="5" id="input-with-keypress"/></div>
                <div style="margin: 10px 10px 0 -5px" class="box_check_use_maxium">
                    <input type="checkbox" class="use_maxium" <?php echo (isset($this->session->data['points_to_checkout']) && $this->session->data['points_to_checkout'] == $max_redeem_point ? 'checked="checked"' : '')?> value="<?php echo $max_redeem_point?>"/><span><?php echo sprintf( $this->language->get('entry_maximum_redeem'), number_format($max_redeem_point), $this->config->get('text_points_'.$this->language->get('code')) )?></span>
                </div>
                <?php } else { ?>
                <?php echo sprintf( $this->language->get('entry_login_redeem'), $this->url->link("account/account", "", 'SSL'), $this->config->get('text_points_'.$this->language->get('code')) )?>
                <?php } ?>
            </div>


        </div>
    </div>
</div>
