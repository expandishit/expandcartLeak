<?php require( DIR_TEMPLATE.$this->config->get('config_template')."/template/common/config.tpl" ); 
  $themeConfig = $this->config->get('themecontrol');
  $productConfig = array(
      'product_enablezoom'=>1,
      'product_zoommode'  => 'basic',
      'product_zoomeasing' => 1,
      'product_zoomlensshape' => "round",
      'product_zoomlenssize' => "150",
      'product_zoomgallery'  => 0,
      'extra_images_visiable' => 3,
      'product_related_column'=>'', 
  );
  $productConfig = array_merge( $productConfig, $themeConfig );
  
  $categoryPzoom = isset($themeConfig['category_pzoom']) ? $themeConfig['category_pzoom']:0;

	$useragent=$_SERVER['HTTP_USER_AGENT'];
	if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
	{
		$productConfig['product_enablezoom'] = 0;
	}

?>


<?php

$productOGimg = "";

$productOGimg .= '<meta property="og:image" content="' . str_replace(" ", "%20", $popup) . '">';

if ($images) {
foreach ($images as  $image) {
$productOGimg .= '<meta property="og:image" content="' . str_replace(" ", "%20", $image['popup']) . '">';
}
}

$header = str_replace("<!-- og:image -->", $productOGimg, $header);

?>

<?php echo $header; ?>

<?php if( $SPAN[0] ): ?>
	<aside class="span<?php echo $SPAN[0];?>">
		<?php echo $column_left; ?>
	</aside>
<?php endif; ?> 

<section class="span<?php echo $SPAN[1];?>">
	<div id="content">

		<?php echo $content_top; ?>
		<div class="breadcrumb">
			<?php foreach ($breadcrumbs as $breadcrumb) { ?>
			<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
			<?php } ?>
		</div>    

		<div class="product-info">
			<div class="row-fluid">			
			
				<?php if ($thumb || $images) { ?>
				<div class="span5 product-img-box">
				
					<!-- Lable Special Product -->
					<?php if( $special )  { ?>
						<span class="product-label-special label"><?php echo $this->language->get( 'text_sale' ); ?></span>
					<?php } ?>	
				
					<!-- Image -->
					<?php if ($thumb) { ?>
					<div class="image">
						<a href="<?php echo $popup; ?>" title="<?php echo $heading_title; ?>" class="colorbox">
							<img src="<?php echo $thumb; ?>" title="<?php echo $heading_title; ?>" alt="<?php echo $heading_title; ?>" id="image"  data-zoom-image="<?php echo $popup; ?>" class="product-image-zoom"/>
						</a>
					</div>
					<?php } ?>
					
					<?php if ($images) { ?>
					<div class="image-additional slide carousel" id="image-additional">
						<div class="carousel-inner">
						<?php 
						if( $productConfig['product_zoomgallery'] == 'slider' && $thumb ) {  
							$eimages = array( 0=> array( 'popup'=>$popup,'thumb'=> $popup )  ); 
							$images = array_merge( $eimages, $images );
						}
							
						$icols = 3; $i= 0;
						foreach ($images as  $image) { ?>
						<?php if( (++$i)%$icols == 1 ) { ?>
						<div class="item">
						<?php } ?>
						<a href="<?php echo $image['popup']; ?>" title="<?php echo $heading_title; ?>" class="colorbox" data-zoom-image="<?php echo $image['popup']; ?>" data-image="<?php echo $image['popup']; ?>">
							<img src="<?php echo $image['thumb']; ?>" style="max-width:<?php echo $this->config->get('config_image_additional_width');?>px"  title="<?php echo $heading_title; ?>" alt="<?php echo $heading_title; ?>" data-zoom-image="<?php echo $image['popup']; ?>" class="product-image-zoom" />
						</a>
						<?php if( $i%$icols == 0 || $i==count($images) ) { ?>
						</div>
						<?php } ?>
						<?php } ?>						
						</div>						
						
						<div class="carousel-control icon-chevron-sign-left left" href="#image-additional" data-slide="prev"></div>
						<div class="carousel-control icon-chevron-sign-right right" href="#image-additional" data-slide="next"></div>
					</div>
					
					<script type="text/javascript">
						$('#image-additional .item:first').addClass('active');
						$('#image-additional').carousel({interval:false})
					</script>
					<?php } ?>
					
				</div>
				<?php } ?>

				<div class="span7">
				
					<h1><?php echo $heading_title; ?></h1>
					
					<?php if ($review_status) { ?>
					<div class="review clearfix">
						<div class="rating">
							<img src="catalog/view/theme/pav_asenti/image/stars-<?php echo $rating; ?>.png" alt="<?php echo $reviews; ?>" />&nbsp;&nbsp;<a onclick="$('a[href=\'#tab-review\']').trigger('click');"><?php echo $reviews; ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="$('a[href=\'#tab-review\']').trigger('click');"><?php echo $text_write; ?></a>
						</div>
						<div class="share">
                            <!-- Go to www.addthis.com/dashboard to customize your tools -->
                            <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-56c3976c6618d23f"></script>

                            <!-- Go to www.addthis.com/dashboard to customize your tools -->
                            <div class="addthis_sharing_toolbox"></div>
						</div>
					</div>
					<?php } ?>					
					
					<p class="description">
						<?php if ($manufacturer) { ?>
						<span><?php echo $text_manufacturer; ?></span> <a href="<?php echo $manufacturers; ?>"><?php echo $manufacturer; ?></a><br />
						<?php } ?>
						<span><?php echo $text_model; ?></span> <?php echo $model; ?><br />
						<?php if ($reward) { ?>
						<span><?php echo $text_reward; ?></span> <?php echo $reward; ?><br />
						<?php } ?>
						<span><?php echo $text_stock; ?></span> <?php echo $stock; ?>
					</p>

					<?php if ($price) { ?>
					<div class="price">
						<?php echo $text_price; ?>

						<?php if (!$special) { ?>
						<?php echo $price; ?>
						<?php } else { ?>
						<span class="price-old"><?php echo $price; ?></span> <span class="price-new"><?php echo $special; ?></span>
						<?php } ?>
						<br />
						<?php if ($tax) { ?>
						<span class="price-tax"><?php echo $text_tax; ?> <?php echo $tax; ?></span>
						<br />
						<?php } ?>
						<?php if ($points) { ?>
						<span class="reward"><small><?php echo $text_points; ?> <?php echo $points; ?></small></span>
						<?php } ?>
						<?php if ($discounts) { ?>
						<br />
						<div class="discount">
							<?php foreach ($discounts as $discount) { ?>
							<?php echo sprintf($text_discount, $discount['quantity'], $discount['price']); ?><br />
							<?php } ?>
						</div>
						<?php } ?>
					</div>
					<?php } ?>		

					<?php if ($options) { ?>
					<div class="options">
						<h2><?php echo $text_option; ?></h2>
						<br />
						<?php foreach ($options as $option) { ?>
						<?php if ($option['type'] == 'select') { ?>
						<div id="option-<?php echo $option['product_option_id']; ?>" class="option">
							<?php if ($option['required']) { ?>
							<span class="required">*</span>
							<?php } ?>
							<b><?php echo $option['name']; ?>:</b><br />
							<select name="option[<?php echo $option['product_option_id']; ?>]">
								<option value=""><?php echo $text_select; ?></option>
								<?php foreach ($option['option_value'] as $option_value) { ?>
								<option value="<?php echo $option_value['product_option_value_id']; ?>"><?php echo $option_value['name']; ?>
									<?php if ($option_value['price']) { ?>
									(<?php echo $option_value['price_prefix']; ?><?php echo $option_value['price']; ?>)
									<?php } ?>
								</option>
								<?php } ?>
							</select>
						</div>		  
						<br />
						<?php } ?>
						<?php if ($option['type'] == 'radio') { ?>
						<div id="option-<?php echo $option['product_option_id']; ?>" class="option">
							<?php if ($option['required']) { ?>
							<span class="required">*</span>
							<?php } ?>
							<b><?php echo $option['name']; ?>:</b><br />
							<?php foreach ($option['option_value'] as $option_value) { ?>
							<input type="radio" name="option[<?php echo $option['product_option_id']; ?>]" value="<?php echo $option_value['product_option_value_id']; ?>" id="option-value-<?php echo $option_value['product_option_value_id']; ?>" />
							<label for="option-value-<?php echo $option_value['product_option_value_id']; ?>"><?php echo $option_value['name']; ?>
								<?php if ($option_value['price']) { ?>
								(<?php echo $option_value['price_prefix']; ?><?php echo $option_value['price']; ?>)
								<?php } ?>
							</label>
							<br />
							<?php } ?>
						</div>
						<br />
						<?php } ?>
						<?php if ($option['type'] == 'checkbox') { ?>
						<div id="option-<?php echo $option['product_option_id']; ?>" class="option">
							<?php if ($option['required']) { ?>
							<span class="required">*</span>
							<?php } ?>
							<b><?php echo $option['name']; ?>:</b><br />
							<?php foreach ($option['option_value'] as $option_value) { ?>
							<input type="checkbox" name="option[<?php echo $option['product_option_id']; ?>][]" value="<?php echo $option_value['product_option_value_id']; ?>" id="option-value-<?php echo $option_value['product_option_value_id']; ?>" />
							<label for="option-value-<?php echo $option_value['product_option_value_id']; ?>"><?php echo $option_value['name']; ?>
								<?php if ($option_value['price']) { ?>
								(<?php echo $option_value['price_prefix']; ?><?php echo $option_value['price']; ?>)
								<?php } ?>
							</label>
							<br />
							<?php } ?>
						</div>
						<br />
						<?php } ?>
						<?php if ($option['type'] == 'image') { ?>
						<div id="option-<?php echo $option['product_option_id']; ?>" class="option">
							<?php if ($option['required']) { ?>
							<span class="required">*</span>
							<?php } ?>
							<b><?php echo $option['name']; ?>:</b><br />
							<table class="option-image">
								<?php foreach ($option['option_value'] as $option_value) { ?>
								<tr>
									<td style="width: 1px;"><input type="radio" name="option[<?php echo $option['product_option_id']; ?>]" value="<?php echo $option_value['product_option_value_id']; ?>" id="option-value-<?php echo $option_value['product_option_value_id']; ?>" /></td>
									<td><label for="option-value-<?php echo $option_value['product_option_value_id']; ?>"><img src="<?php echo $option_value['image']; ?>" alt="<?php echo $option_value['name'] . ($option_value['price'] ? ' ' . $option_value['price_prefix'] . $option_value['price'] : ''); ?>" /></label></td>
									<td><label for="option-value-<?php echo $option_value['product_option_value_id']; ?>"><?php echo $option_value['name']; ?>
										<?php if ($option_value['price']) { ?>
										(<?php echo $option_value['price_prefix']; ?><?php echo $option_value['price']; ?>)
										<?php } ?>
									</label></td>
								</tr>
								<?php } ?>
							</table>
						</div>
						<br />
						<?php } ?>
						<?php if ($option['type'] == 'text') { ?>
						<div id="option-<?php echo $option['product_option_id']; ?>" class="option">
							<?php if ($option['required']) { ?>
							<span class="required">*</span>
							<?php } ?>
							<b><?php echo $option['name']; ?>:</b><br />
							<input type="text" class="span12" name="option[<?php echo $option['product_option_id']; ?>]" value="<?php echo $option['option_value']; ?>" />
						</div>
						<br />
						<?php } ?>
						<?php if ($option['type'] == 'textarea') { ?>
						<div id="option-<?php echo $option['product_option_id']; ?>" class="option">
							<?php if ($option['required']) { ?>
							<span class="required">*</span>
							<?php } ?>
							<b><?php echo $option['name']; ?>:</b><br />
							<textarea name="option[<?php echo $option['product_option_id']; ?>]" cols="40" rows="5" class="span12"><?php echo $option['option_value']; ?></textarea>
						</div>
						<br />
						<?php } ?>
						<?php if ($option['type'] == 'file') { ?>
						<div id="option-<?php echo $option['product_option_id']; ?>" class="option">
							<?php if ($option['required']) { ?>
							<span class="required">*</span>
							<?php } ?>
							<b><?php echo $option['name']; ?>:</b><br />
							<input type="button" value="<?php echo $button_upload; ?>" id="button-option-<?php echo $option['product_option_id']; ?>" class="button">
							<input type="hidden" name="option[<?php echo $option['product_option_id']; ?>]" value="" />
						</div>
						<br />
						<?php } ?>
						<?php if ($option['type'] == 'date') { ?>
						<div id="option-<?php echo $option['product_option_id']; ?>" class="option">
							<?php if ($option['required']) { ?>
							<span class="required">*</span>
							<?php } ?>
							<b><?php echo $option['name']; ?>:</b><br />
							<input type="text" name="option[<?php echo $option['product_option_id']; ?>]" value="<?php echo $option['option_value']; ?>" class="date span12" />
						</div>
						<br />
						<?php } ?>
						<?php if ($option['type'] == 'datetime') { ?>
						<div id="option-<?php echo $option['product_option_id']; ?>" class="option">
							<?php if ($option['required']) { ?>
							<span class="required">*</span>
							<?php } ?>
							<b><?php echo $option['name']; ?>:</b><br />
							<input type="text" name="option[<?php echo $option['product_option_id']; ?>]" value="<?php echo $option['option_value']; ?>" class="datetime span12" />
						</div>
						<br />
						<?php } ?>
						<?php if ($option['type'] == 'time') { ?>
						<div id="option-<?php echo $option['product_option_id']; ?>" class="option">
							<?php if ($option['required']) { ?>
							<span class="required">*</span>
							<?php } ?>
							<b><?php echo $option['name']; ?>:</b><br />
							<input type="text" name="option[<?php echo $option['product_option_id']; ?>]" value="<?php echo $option['option_value']; ?>" class="time span12" />
						</div>
						<br />
						<?php } ?>
						<?php } ?>
					</div>
					<?php } ?>
					
					<div class="cart">
						<div class="clearfix">
						
							<div class="quantity-adder pull-left">							
							
								<?php echo $text_qty; ?>							
								<input type="text" name="quantity" size="2" value="<?php echo $minimum; ?>" />
								<span class="add-up add-action">
									<span class="icon-plus"></span>
								</span>
								<span class="add-down add-action">
									<span class="icon-minus"></span>
								</span>
							
							</div>
							
							<input type="hidden" name="product_id" size="2" value="<?php echo $product_id; ?>" />
							<input type="button" value="<?php echo $button_cart; ?>" id="button-cart" class="button" />
							
							<?php /* <span>&nbsp;&nbsp;<?php echo $text_or; ?>&nbsp;&nbsp;</span> */ ?>
							
							<span class="links">
								<a onclick="addToWishList('<?php echo $product_id; ?>');" class="wishlist"><?php echo $button_wishlist; ?></a>
								<a onclick="addToCompare('<?php echo $product_id; ?>');" class="compare"><?php echo $button_compare; ?></a>
							</span>
						</div>
						<?php if ($minimum > 1) { ?>
						<div class="minimum"><?php echo $text_minimum; ?></div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>

		<div id="tabs" class="htabs clearfix">
			<a href="#tab-description"><?php echo $tab_description; ?></a>
			<?php if ($attribute_groups) { ?>
			<a href="#tab-attribute"><?php echo $tab_attribute; ?></a>
			<?php } ?>
			<?php if ($review_status) { ?>
			<a href="#tab-review"><?php echo $tab_review; ?></a>
			<?php } ?>			
		</div>

		<div id="tab-description" class="tab-content">
			<?php echo $description; ?>
		</div>
		
		<?php if ($attribute_groups) { ?>
		<div id="tab-attribute" class="tab-content">
			<table class="attribute">
				<?php foreach ($attribute_groups as $attribute_group) { ?>
				<thead class="hidden-phone">
					<tr>
						<td colspan="2"><?php echo $attribute_group['name']; ?></td>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($attribute_group['attribute'] as $attribute) { ?>
					<tr>
						<td><?php echo $attribute['name']; ?></td>
						<td><?php echo $attribute['text']; ?></td>
					</tr>
					<?php } ?>
				</tbody>
				<?php } ?>
			</table>
		</div>
		<?php } ?>
		
		<?php if ($review_status) { ?>
		<div id="tab-review" class="tab-content">
			<div id="review"></div>
			<h2 id="review-title"><?php echo $text_write; ?></h2>
			<b><?php echo $entry_name; ?></b><br />
			<input type="text" name="name" value="" class="span12" />
			<br />
			<br />
			<b><?php echo $entry_review; ?></b>
			<textarea name="text" cols="40" rows="8" class="span12"></textarea>
			<span style="font-size: 11px;"><?php echo $text_note; ?></span><br />
			<br />
			<b><?php echo $entry_rating; ?></b> <span><?php echo $entry_bad; ?></span>&nbsp;
			<input type="radio" name="rating" value="1" />
			&nbsp;
			<input type="radio" name="rating" value="2" />
			&nbsp;
			<input type="radio" name="rating" value="3" />
			&nbsp;
			<input type="radio" name="rating" value="4" />
			&nbsp;
			<input type="radio" name="rating" value="5" />
			&nbsp;<span><?php echo $entry_good; ?></span><br />
			<br />
			<b><?php echo $entry_captcha; ?></b><br />
			<input type="text" name="captcha" value="" class="span12" />
			<br />
			<img src="index.php?route=common/captcha" alt="" id="captcha" /><br />
			<br />
			<div class="buttons clearfix">
				<div class="right"><a id="button-review" class="button"><?php echo $button_continue; ?></a></div>
			</div>
		</div>		
		<?php } ?>		
	</div>	    

	<?php if ($tags) { ?>
	<div class="tags">
		<b><?php echo $text_tags; ?></b>
		<?php for ($i = 0; $i < count($tags); $i++) { ?>
		<?php if ($i < (count($tags) - 1)) { ?>
		<a href="<?php echo $tags[$i]['href']; ?>"><?php echo $tags[$i]['tag']; ?></a>,
		<?php } else { ?>
		<a href="<?php echo $tags[$i]['href']; ?>"><?php echo $tags[$i]['tag']; ?></a>
		<?php } ?>
		<?php } ?>
	</div>
	<?php } ?>
	
	<!-- Related Product -->
	
	<?php if ($products) { ?>	
	
	<div class="product-related box">	
		<h3 class="box-heading">
			<span><?php echo $tab_related; ?> (<?php echo count($products); ?>)</span>
		</h3>	
		
		<?php $cols = $productConfig['product_related_column']?$productConfig['product_related_column']:3;
		$ispan = 12/$cols; ?>
		<?php $i =0 ; foreach ($products as $product) { ?>
		<?php if( $cols > 1 && ($i+1)%$cols == 1 ) {   ?>
		<div class="row-fluid">
		<?php } ?>
			
			<div class="span<?php echo $ispan;?> <?php if($i%$cols == 0) { echo "last";} ?> product-block">	
				<!-- Lable Special Product -->
				<?php if( $product['special'] )  { ?>
				<span class="product-label-special label"><?php echo $this->language->get( 'text_sale' ); ?></span>
				<?php } ?>
			
				<div class="product-inner">
					<?php if ($product['thumb']) { ?>
						<div class="image product-top">
							<a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" title="<?php echo $product['name']; ?>" alt="<?php echo $product['name']; ?>" /></a>
							<?php if( $categoryPzoom ) { 
								$zimage = str_replace( "cache/","", preg_replace("#-\d+x\d+#", "",  $product['thumb'] ));  ?>
								<a href="<?php echo $zimage;?>" class="colorbox product-zoom" rel="colorbox" title="<?php echo $product['name']; ?>">
									<span class="icon-zoom-in"></span>
								</a>
							<?php } ?>
						</div>
					<?php } ?>	
					
					<h3 class="name">
						<a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a>
					</h3>
					
					<?php if( isset($product['description']) ){ ?>
						<div class="description"><?php echo substr( strip_tags($product['description']),0,58);?>...</div>
					<?php } ?>

					<?php if ($product['rating']) { ?>
					<div class="rating">
						<img src="catalog/view/theme/pav_asenti/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" />
					</div>
					<?php } ?>

					<?php if ($product['price']) { ?>
					<div class="price">
						<?php if (!$product['special']) { ?>
						<?php echo $product['price']; ?>
						<?php } else { ?>
						<span class="price-old"><?php echo $product['price']; ?></span> 
						<span class="price-new"><?php echo $product['special']; ?></span>
						<?php } ?>
					</div>
					<?php } ?>

					<div class="pav-group-button clearfix">
						<div class="cart pull-left">
							<input type="button" value="<?php echo $button_cart; ?>" onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button" />
						</div>
						<div class="wishlist pull-left">
							<a class="wishlist" onclick="addToWishList('<?php echo $product['product_id']; ?>');" title="<?php echo $button_wishlist; ?>" ><?php echo $button_wishlist; ?></a>							  
						</div>						
						<div class="compare pull-left">
							<a class="compare"  onclick="addToCompare('<?php echo $product['product_id']; ?>');" title="<?php echo $button_compare; ?>" ><?php echo $button_compare; ?></a>
						</div>
					</div>
				</div>				
			</div>	

			
		<?php $i++; if(  $cols > 1 && ( $i%$cols == 0 || $i == count($products) ) ) { ?>
		</div>
		<?php } ?>
		<?php } ?>  
		
	</div>
	
	<?php } ?>

	<?php echo $content_bottom; ?>		
	
	<?php if( $productConfig['product_enablezoom'] ) { ?>

    <?php if ($this->language->get('direction') == 'rtl') { ?>
        <script type="text/javascript" src=" catalog/view/theme/<?php echo $this->config->get('config_template'); ?>/javascript/elevatezoom/elevatezoom-min-ar.js"></script>
    <?php } else { ?>
        <script type="text/javascript" src=" catalog/view/theme/<?php echo $this->config->get('config_template'); ?>/javascript/elevatezoom/elevatezoom-min.js"></script>
    <?php } ?>

	<script type="text/javascript">
		<!--
			<?php if( $productConfig['product_zoomgallery'] == 'slider' ) {  ?>
			$("#image").elevateZoom({gallery:'image-additional', cursor: 'pointer', galleryActiveClass: 'active'}); 
			<?php } else { ?>
			var zoomCollection = '<?php echo $productConfig["product_zoomgallery"]=="basic"?".product-image-zoom":"#image";?>';
			$( zoomCollection ).elevateZoom({
			<?php if( $productConfig['product_zoommode'] != 'basic' ) { ?>
			zoomType        : "<?php echo $productConfig['product_zoommode'];?>",
			<?php } ?>
			lensShape : "<?php echo $productConfig['product_zoomlensshape'];?>",
			lensSize    : <?php echo (int)$productConfig['product_zoomlenssize'];?>,

			});
			<?php } ?> 
		//-->
	</script>
	<?php } ?>
	
	
	<?php if( $categoryPzoom ) {  ?>
	<script type="text/javascript">
		<!--
		$(document).ready(function() {
		$('.colorbox').colorbox({
		overlayClose: true,
		opacity: 0.5,
		rel: false,
		onLoad:function(){
			$("#cboxNext").remove(0);
			$("#cboxPrevious").remove(0);
			$("#cboxCurrent").remove(0);
		}
		});
		});
		//-->
	</script>
	<?php } ?>
	
	
	<script type="text/javascript">
	<!--
	$(document).ready(function() {
		$('.colorbox').colorbox({
			overlayClose: true,
			opacity: 0.5,
			rel: "colorbox"
		});
	});
	//-->
	</script>
	
	
	<script type="text/javascript">
	<!--
	$('#button-cart').bind('click', function() {
		$.ajax({
			url: 'index.php?route=checkout/cart/add',
			type: 'post',
			data: $('.product-info input[type=\'text\'], .product-info input[type=\'hidden\'], .product-info input[type=\'radio\']:checked, .product-info input[type=\'checkbox\']:checked, .product-info select, .product-info textarea'),
			dataType: 'json',
			success: function(json) {
				$('.success, .warning, .attention, information, .error').remove();
				
				if (json['error']) {
					if (json['error']['option']) {
						for (i in json['error']['option']) {
							$('#option-' + i).after('<span class="error">' + json['error']['option'][i] + '</span>');
						}
					}
				} 
				
				if (json['success']) {
					$('#notification').html('<div class="success" style="display: none;">' + json['success'] + '<img src="catalog/view/theme/default/image/close.png" alt="" class="close" /></div>');
						
					$('.success').fadeIn('slow');
						
					$('#cart-total').html(json['total']);

                    if (json['enable_order_popup'] != '1')
                        $('html, body').animate({ scrollTop: 0 }, 'slow');

                    if (json['enable_order_popup'] == '1') {
                        $('head').append("<style type='text/css'>.customAddtoCart .ui-dialog-titlebar.ui-widget-header.ui-corner-all.ui-helper-clearfix {display: none;} .customAddtoCart div#add-to-cart-dialog {min-height: 1px !important;} [dir=rtl] .ui-widget {font-family: 'Droid Arabic Kufi', 'droid_serifregular' !important;}</style>");

                        $('body').append('<div id="add-to-cart-dialog" style="display:none;"><div style="margin: 13px 0;">' + json['text_cart_dialog'] + '</div></div>');

                        $("#add-to-cart-dialog").dialog({
                            modal: true,
                            draggable: false,
                            resizable: false,
                            position: ['center', 'center'],
                            show: 'blind',
                            hide: 'blind',
                            width: 500,
                            dialogClass: 'ui-dialog-osx customAddtoCart',
                            buttons: [{
                                text: json['text_cart_dialog_continue'],
                                click: function() {
                                    $(this).dialog("close");
                                }
                            },
                                {
                                    text: json['text_cart_dialog_cart'],
                                    click: function() {
                                        window.location.href = json['cart_link'];
                                    }
                                }
                            ]
                        });
                    }
				}	
			}
		});
	});
	//-->
	</script>
	
	
	<?php if ($options) { ?>
		<script type="text/javascript" src="catalog/view/javascript/jquery/ajaxupload.js"></script>
		<?php foreach ($options as $option) { ?>
		<?php if ($option['type'] == 'file') { ?>
		<script type="text/javascript">
			<!--
			new AjaxUpload('#button-option-<?php echo $option['product_option_id']; ?>', {
			action: 'index.php?route=product/product/upload',
			name: 'file',
			autoSubmit: true,
			responseType: 'json',
			onSubmit: function(file, extension) {
				$('#button-option-<?php echo $option['product_option_id']; ?>').after('<img src="catalog/view/theme/default/image/loading.gif" class="loading" style="padding-left: 5px;" />');
				$('#button-option-<?php echo $option['product_option_id']; ?>').attr('disabled', true);
			},
			onComplete: function(file, json) {
				$('#button-option-<?php echo $option['product_option_id']; ?>').attr('disabled', false);
				
				$('.error').remove();
				
				if (json['success']) {
					alert(json['success']);
					
					$('input[name=\'option[<?php echo $option['product_option_id']; ?>]\']').attr('value', json['file']);
				}
				
				if (json['error']) {
					$('#option-<?php echo $option['product_option_id']; ?>').after('<span class="error">' + json['error'] + '</span>');
				}
				
				$('.loading').remove();	
			}
		});
		//-->
		</script>
	<?php } ?>
	<?php } ?>
	<?php } ?>
	
	<script type="text/javascript">
		<!--
		$('#review .pagination a').live('click', function() {
			$('#review').fadeOut('slow');
				
			$('#review').load(this.href);
			
			$('#review').fadeIn('slow');
			
			return false;
		});			

		$('#review').load('index.php?route=product/product/review&product_id=<?php echo $product_id; ?>');

		$('#button-review').bind('click', function() {
			$.ajax({
				url: 'index.php?route=product/product/write&product_id=<?php echo $product_id; ?>',
				type: 'post',
				dataType: 'json',
				data: 'name=' + encodeURIComponent($('input[name=\'name\']').val()) + '&text=' + encodeURIComponent($('textarea[name=\'text\']').val()) + '&rating=' + encodeURIComponent($('input[name=\'rating\']:checked').val() ? $('input[name=\'rating\']:checked').val() : '') + '&captcha=' + encodeURIComponent($('input[name=\'captcha\']').val()),
				beforeSend: function() {
					$('.success, .warning').remove();
					$('#button-review').attr('disabled', true);
					$('#review-title').after('<div class="attention"><img src="catalog/view/theme/default/image/loading.gif" alt="" /> <?php echo $text_wait; ?></div>');
				},
				complete: function() {
					$('#button-review').attr('disabled', false);
					$('.attention').remove();
				},
				success: function(data) {
					if (data['error']) {
						$('#review-title').after('<div class="warning">' + data['error'] + '</div>');
					}
					
					if (data['success']) {
						$('#review-title').after('<div class="success">' + data['success'] + '</div>');
										
						$('input[name=\'name\']').val('');
						$('textarea[name=\'text\']').val('');
						$('input[name=\'rating\']:checked').attr('checked', '');
						$('input[name=\'captcha\']').val('');
					}
				}
			});
		});
		//-->
	</script> 
	
	<script type="text/javascript">
		<!--
		$('#tabs a').tabs();
		//-->
	</script> 
	<script type="text/javascript" src="catalog/view/javascript/jquery/ui/jquery-ui-timepicker-addon.js"></script> 
	<script type="text/javascript">
		<!--
		$(document).ready(function() {
			if ($.browser.msie && $.browser.version == 6) {
				$('.date, .datetime, .time').bgIframe();
			}

			$('.date').datepicker({dateFormat: 'yy-mm-dd'});
			$('.datetime').datetimepicker({
				dateFormat: 'yy-mm-dd',
				timeFormat: 'h:m'
			});
			$('.time').timepicker({timeFormat: 'h:m'});
		});
		//-->
	</script> 
</section> 

<?php if( $SPAN[2] ): ?>
	<aside class="span<?php echo $SPAN[2];?>">	
		<?php echo $column_right; ?>
	</aside>
<?php endif; ?>

<?php echo $footer; ?>