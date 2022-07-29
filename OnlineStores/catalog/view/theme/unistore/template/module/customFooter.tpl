	<?php $footer_grid = 4; $block = 0; if($about_status == '1') { $block++; } if($contact_status == '1') { $block++; } if($facebook_status == '1' || $followus_column_status == '1') { $block++; } $footer_grid = 12/$block; ?>
	<!-- Footer top outside -->
	
	<div class="footer-top-outside set-size">
		
		<?php if ($about_status == '1') : ?>
		<!-- Uni-Store -->
		
		<div class="grid-<?php echo $footer_grid; ?> float-left">
			
			<h2><?php echo $about_header; ?></h2>
			
			<p>
			
				<?php echo html_entity_decode($about_text); ?>
				
			</p>
		
		</div>
		
		<!-- End Uni-Store -->
		<?php endif; ?>
		<?php if ($contact_status == '1') : ?>
		<!-- Contact -->
		
		<div class="grid-<?php echo $footer_grid; ?> float-left">
		
			<h2><?php echo $contact_header; ?></h2>
		
			<ul id="contact-us">

				<li>
					<?php if (! empty ( $gsm )) {	?> 
					<!-- TELEPHONE -->
					<ul id="tel">

						<li><?php echo $gsm; ?></li>
						
					</ul>
					<?php	} ?>	
					<?php if (! empty ( $fax )) {	?> 
					<!-- FAX  -->
					<ul id="fax">
							
						<li><?php echo $fax; ?></li>
	
					</ul>
					<?php	} ?>	
					<?php if (! empty ( $email )) {	?> 
					<!-- EMAIL -->
					<ul id="mail">
		
						<li><?php echo $email; ?></li>
			
					</ul>
					<?php	} ?>	
					<?php if (! empty ( $skype )) {	?> 
					<!-- SKYPE -->
					<ul id="skype">
		
						<li><?php echo $skype; ?></li>
	
					</ul>
					<?php	} ?>	

				</li>

			</ul>
			
			<p style="padding-top:11px">Contact with us or &nbsp;&nbsp;<a href="index.php?route=information/contact" class="button"><span>Fill in the Form</span></a></p>
		
		</div>
		
		<!-- End contact -->
		<?php endif; ?>
		<?php if ($facebook_status == '1' || $followus_column_status == '1') : ?>
		<!-- Follow us and facebook -->
		
		<div class="grid-<?php echo $footer_grid; ?> float-left">
			<?php if ($followus_column_status == '1') : ?>
			<!-- Follow us -->
			
			<h2><?php echo $followus_header; ?></h2>
			
			<?php echo html_entity_decode($followus_column_content); ?>
			
			<!-- End follow us -->
			<?php endif; ?>
			<?php if ($facebook_status == '1') : ?>
			<?php $styl = false; if($this->config->get('unistore_color') == '3' || $this->config->get('unistore_color') == '4') { $styl = "jew-"; } if($this->config->get('unistore_color') == '5') { $styl = "fash-"; } ?> 
			<!-- Facebook -->
						
			<script type="text/javascript" src="//static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php/en_US"></script>
			<script type="text/javascript">FB.init("1690883eb733618b294e98cb1dfba95a");</script>
			<fb:fan profile_id="<?php echo $facebook_id; ?>" stream="0" connections="4" logobar="0" height="110px" width="300" 
			css="<?php echo HTTPS_SERVER; ?>catalog/view/theme/unistore/stylesheet/<?php echo $styl; ?>facebook.css"></fb:fan>

			<!-- End Facebook -->
			<?php endif; ?>
					
		
		</div>
		
		<!-- End follow us and facebook -->
		<?php endif; ?>
	
		<p class="clear"></p>
	
	</div>
	
	<!-- End footer top outside -->
	
	<!-- Separator --><div class="set-size-grid"><p class="separator"></p></div>