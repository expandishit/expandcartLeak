<div class="<?php echo $prefix; ?> box newsletter" id="newsletter_<?php echo $position.$module;?>">
	<p class="box-heading"><?php echo $this->language->get("entry_newsletter");?></p>
	
	<div class="block_content">
		<span><?php echo html_entity_decode( $description );?></span>
		<form method="post" action="<?php echo $action; ?>">
			<p>
				<input type="text" class="inputNew" <?php if(!isset($customer_email)): ?> onblur="javascript:if(this.value=='')this.value='<?php echo $this->language->get("default_input_text");?>';" onfocus="javascript:if(this.value=='<?php echo $this->language->get("default_input_text");?>')this.value='';"<?php endif; ?> value="<?php echo isset($customer_email)?$customer_email:$this->language->get("default_input_text");?>" size="18" name="email">

				<br/>
					<input type="submit" name="submitNewsletter" class="button button_mini" value="<?php echo $this->language->get("Subcrisbe");?>"/><span class="btn-arrow-right"></span>
					<input type="hidden" value="1" name="action"/>
			</p>
		</form>
	</div>



</div>