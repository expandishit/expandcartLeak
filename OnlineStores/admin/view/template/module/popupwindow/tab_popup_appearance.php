<div class="tab-pane fade in">
	<div class="row">
		<!--Popup width-->
		<div class="col-md-4">
			<h5><strong><?php echo $this->language->get('Width:'); ?></strong></h5>
			<span class="help"><i class="fa fa-info-circle"></i>&nbsp;<?php echo $this->language->get('Define the width for the selected popup'); ?></span>
		</div>
		<div class="col-md-2">
			<div class="input-group">
				<input id="width_input" placeholder="<?php echo $this->language->get('Width'); ?>" required="required" min="0" type="number" class="form-control" name="<?php echo $popup_name; ?>[width]" value="<?php if(!empty($popup_data['width'])) echo $popup_data['width']; else echo"500"; ?>" />
				<span class="input-group-addon"><?php echo $this->language->get('px'); ?></span>
			</div>
		</div>
	</div>
	<!--End Popup width-->
	<br />
	<div class="row">
		<!--Height -->
		<div class="col-md-4">
			<h5><strong><?php echo $this->language->get('Height:'); ?></strong></h5>
			<span class="help"><i class="fa fa-info-circle"></i>&nbsp;<?php echo $this->language->get('Define the height for the selected popup'); ?></span>
		</div>
		<div class="col-md-2">
			<div class="input-group">
				<input id="height_input" placeholder="<?php echo $this->language->get('Height'); ?>" required="required" min="0" type="number" class="form-control" name="<?php echo $popup_name; ?>[height]" value="<?php if(!empty($popup_data['height'])) echo $popup_data['height']; else echo"500"; ?>" />
				<span class="input-group-addon"><?php echo $this->language->get('px'); ?></span>
               
			</div>
		</div>
	</div>
	<!--End Height -->
	<br />
	<div class="row">
		<div class="col-md-4">
			<h5><strong><?php echo $this->language->get('Fancybox auto resize:'); ?></strong></h5>
			<span class="help"><i class="fa fa-info-circle"></i>&nbsp;<?php echo $this->language->get('Enable fancybox auto resize option'); ?></span>
		</div>
		<div class="col-md-3">
 			<select name="<?php echo $popup_name; ?>[auto_resize]" class="form-control repeatSelect">
				<option value="false" <?php echo (!empty($popup_data['auto_resize']) && $popup_data['auto_resize'] == 'false') ? 'selected=selected' : '' ?>><?php echo $this->language->get('Disabled'); ?></option>
				<option value="true" <?php echo (!empty($popup_data['auto_resize']) && $popup_data['auto_resize'] == 'true') ? 'selected=selected' : '' ?>><?php echo $this->language->get('Enabled'); ?></option>
			</select>
       	</div>
	</div>
    
    <br />
     <div class="row">
		<div class="col-md-4">
			<h5><strong><?php echo $this->language->get('Fancybox aspect ratio:'); ?></strong></h5>
			<span class="help"><i class="fa fa-info-circle"></i>&nbsp;<?php echo $this->language->get('Enable fancybox aspect ratio option'); ?></span>
		</div>
		<div class="col-md-3">
 			<select name="<?php echo $popup_name; ?>[aspect_ratio]" class="form-control repeatSelect">
				<option value="false" <?php echo (!empty($popup_data['aspect_ratio']) && $popup_data['aspect_ratio'] == 'false') ? 'selected=selected' : '' ?>><?php echo $this->language->get('Disabled'); ?></option>
				<option value="true" <?php echo (!empty($popup_data['aspect_ratio']) && $popup_data['aspect_ratio'] == 'true') ? 'selected=selected' : '' ?>><?php echo $this->language->get('Enabled'); ?></option>
			</select>
       	</div>
	</div>
	<br/>
	<div class="row">
		<!-- Choose animation -->
		<div class="col-md-4">
			<h5><strong><?php echo $this->language->get('Animation:'); ?></strong></h5>
			<span class="help"><i class="fa fa-info-circle"></i>&nbsp;<?php echo $this->language->get('Choose animation for popup entrance'); ?></span>
		</div>
		<div class="col-md-2">
			<select name="<?php echo $popup_name; ?>[animation]" class="form-control">
				<option value="bounceIn" <?php echo (!empty($popup_data['animation']) && $popup_data['animation'] == 'bounceIn') ? 'selected=selected' : '' ?>>bounceIn</option>
				<option value="bounceInDown" <?php echo (!empty($popup_data['animation']) && $popup_data['animation'] == 'bounceInDown') ? 'selected=selected' : '' ?>>bounceInDown</option>
				<option value="bounceInLeft" <?php echo (!empty($popup_data['animation']) && $popup_data['animation'] == 'bounceInLeft') ? 'selected=selected' : '' ?>>bounceInLeft</option>
				<option value="bounceInRight" <?php echo (!empty($popup_data['animation']) && $popup_data['animation'] == 'bounceInRight') ? 'selected=selected' : '' ?>>bounceInRight</option>
				<option value="bounceInUp" <?php echo (!empty($popup_data['animation']) && $popup_data['animation'] == 'bounceInUp') ? 'selected=selected' : '' ?>>bounceInUp</option>
				<option value="fadeIn" <?php echo (!empty($popup_data['animation']) && $popup_data['animation'] == 'fadeIn') ? 'selected=selected' : '' ?>>fadeIn</option>
				<option value="fadeInDown" <?php echo (!empty($popup_data['animation']) && $popup_data['animation'] == 'fadeInDown') ? 'selected=selected' : '' ?>>fadeInDown</option>
				<option value="fadeInDownBig" <?php echo (!empty($popup_data['animation']) && $popup_data['animation'] == 'fadeInDownBig') ? 'selected=selected' : '' ?>>fadeInDownBig</option>
				<option value="fadeInLeft" <?php echo (!empty($popup_data['animation']) && $popup_data['animation'] == 'fadeInLeft') ? 'selected=selected' : '' ?>>fadeInLeft</option>
				<option value="fadeInLeftBig" <?php echo (!empty($popup_data['animation']) && $popup_data['animation'] == 'fadeInLeftBig') ? 'selected=selected' : '' ?>>fadeInLeftBig</option>
				<option value="fadeInRight" <?php echo (!empty($popup_data['animation']) && $popup_data['animation'] == 'fadeInRight') ? 'selected=selected' : '' ?>>fadeInRight</option>
				<option value="fadeInRightBig" <?php echo (!empty($popup_data['animation']) && $popup_data['animation'] == 'fadeInRightBig') ? 'selected=selected' : '' ?>>fadeInRightBig</option>
				<option value="fadeInUp" <?php echo (!empty($popup_data['animation']) && $popup_data['animation'] == 'fadeInUp') ? 'selected=selected' : '' ?>>fadeInUp</option>
				<option value="fadeInUpBig" <?php echo (!empty($popup_data['animation']) && $popup_data['animation'] == 'fadeInUpBig') ? 'selected=selected' : '' ?>>fadeInUpBig</option>
				<option value="rotateIn" <?php echo (!empty($popup_data['animation']) && $popup_data['animation'] == 'rotateIn') ? 'selected=selected' : '' ?>>rotateIn</option>
				<option value="rotateInDownLeft" <?php echo (!empty($popup_data['animation']) && $popup_data['animation'] == 'rotateInDownLeft') ? 'selected=selected' : '' ?>>rotateInDownLeft</option>
				<option value="rotateInDownRight" <?php echo (!empty($popup_data['animation']) && $popup_data['animation'] == 'rotateInDownRight') ? 'selected=selected' : '' ?>>rotateInDownRight</option>
				<option value="rotateInUpLeft" <?php echo (!empty($popup_data['animation']) && $popup_data['animation'] == 'rotateInUpLeft') ? 'selected=selected' : '' ?>>rotateInUpLeft</option>
				<option value="rotateInUpRight" <?php echo (!empty($popup_data['animation']) && $popup_data['animation'] == 'rotateInUpRight') ? 'selected=selected' : '' ?>>rotateInUpRight</option>
				<option value="zoomIn" <?php echo (!empty($popup_data['animation']) && $popup_data['animation'] == 'zoomIn') ? 'selected=selected' : '' ?>>zoomIn</option>
				<option value="zoomInDown" <?php echo (!empty($popup_data['animation']) && $popup_data['animation'] == 'zoomInDown') ? 'selected=selected' : '' ?>>zoomInDown</option>
				<option value="zoomInLeft" <?php echo (!empty($popup_data['animation']) && $popup_data['animation'] == 'zoomInLeft') ? 'selected=selected' : '' ?>>zoomInLeft</option>
				<option value="zoomInRight" <?php echo (!empty($popup_data['animation']) && $popup_data['animation'] == 'zoomInRight') ? 'selected=selected' : '' ?>>zoomInRight</option>
				<option value="zoomInUp" <?php echo (!empty($popup_data['animation']) && $popup_data['animation'] == 'zoomInUp') ? 'selected=selected' : '' ?>>zoomInUp</option>
			</select>
		</div>
	</div>
	<!-- End animation -->
</div>