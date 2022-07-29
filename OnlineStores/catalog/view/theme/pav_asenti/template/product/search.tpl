<?php require( DIR_TEMPLATE.$this->config->get('config_template')."/template/common/config.tpl" ); 
$themeConfig = $this->config->get('themecontrol');

$DISPLAY_MODE = 'grid';
if( isset($themeConfig['cateogry_display_mode']) ){
	$DISPLAY_MODE = $themeConfig['cateogry_display_mode'];
}
$MAX_ITEM_ROW = 3; 
if( isset($themeConfig['cateogry_product_row']) && $themeConfig['cateogry_product_row'] ){
	$MAX_ITEM_ROW = $themeConfig['cateogry_product_row'];
}
$categoryPzoom = isset($themeConfig['category_pzoom']) ? $themeConfig['category_pzoom']:0; 
?>

<?php echo $header; ?>

<?php if( $SPAN[0] ): ?>
	<aside class="span<?php echo $SPAN[0];?>">
		<?php echo $column_left; ?>
	</aside>
<?php endif; ?>
 
<section class="span<?php echo $SPAN[1];?>">
	<div id="content" class="search">
		<?php echo $content_top; ?>
		<div class="breadcrumb">
			<?php foreach ($breadcrumbs as $breadcrumb) { ?>
			<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
			<?php } ?>
		</div>
		
		<h1><?php echo $heading_title; ?></h1>
  
		<p><b><?php echo $text_critea; ?></b></p>
		
		<div class="content">
			<div class="top-search row-fluid">
				<div class="span6">
					<div class="row-fluid">
						<div class="span2">
							<label for="entry_search"><?php echo $entry_search; ?></label>
						</div>
						<div class="span10">
							<?php if ($search) { ?>
								<input type="text" name="search" value="<?php echo $search; ?>" />
							<?php } else { ?>
								<input type="text" name="search" value="<?php echo $search; ?>" onclick="this.value = '';" onkeydown="this.style.color = '000000'" style="color: #999;" />
							<?php } ?>
						</div>
					</div>
				</div>
				
				<div class="span6">
					<select name="category_id">
						<option value="0"><?php echo $text_category; ?></option>
						<?php foreach ($categories as $category_1) { ?>
						<?php if ($category_1['category_id'] == $category_id) { ?>
						<option value="<?php echo $category_1['category_id']; ?>" selected="selected"><?php echo $category_1['name']; ?></option>
						<?php } else { ?>
						<option value="<?php echo $category_1['category_id']; ?>"><?php echo $category_1['name']; ?></option>
						<?php } ?>
						<?php foreach ($category_1['children'] as $category_2) { ?>
						<?php if ($category_2['category_id'] == $category_id) { ?>
						<option value="<?php echo $category_2['category_id']; ?>" selected="selected">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $category_2['name']; ?></option>
						<?php } else { ?>
						<option value="<?php echo $category_2['category_id']; ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $category_2['name']; ?></option>
						<?php } ?>
						<?php foreach ($category_2['children'] as $category_3) { ?>
						<?php if ($category_3['category_id'] == $category_id) { ?>
						<option value="<?php echo $category_3['category_id']; ?>" selected="selected">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $category_3['name']; ?></option>
						<?php } else { ?>
						<option value="<?php echo $category_3['category_id']; ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $category_3['name']; ?></option>
						<?php } ?>
						<?php } ?>
						<?php } ?>
						<?php } ?>
					</select>
				</div>
			</div>
			<div class="bottom-search row-fluid">
				<div class="span6">
					<?php if ($sub_category) { ?>
					<input type="checkbox" name="sub_category" value="1" id="sub_category" checked="checked" />
					<?php } else { ?>
					<input type="checkbox" name="sub_category" value="1" id="sub_category" />
					<?php } ?>
					<label for="sub_category"><?php echo $text_sub_category; ?></label>
				</div>
				<div class="span6">
					<?php if ($description) { ?>
					<input type="checkbox" name="description" value="1" id="description" checked="checked" />
					<?php } else { ?>
					<input type="checkbox" name="description" value="1" id="description" />
					<?php } ?>
					<label for="description"><?php echo $entry_description; ?></label>	
				</div>
			</div>
		</div>
  
		<div class="buttons clearfix">
			<div class="right">
				<input type="button" value="<?php echo $button_search; ?>" id="button-search" class="button" />
			</div>
		</div>
		
		<h2><?php echo $text_search; ?></h2>
  
  
	<?php if ($products) { ?>  
		<div class="product-filter">
			<div class="row-fluid">
				<div class="span7">
					<div class="filter pull-left">
						<div class="display">					
							<ul class="clearfix">
								<li class="first"><b><?php echo $text_display; ?></b></li>
								<li><?php echo $text_list; ?></li>
								<li class="last"><a onclick="display('grid');"><?php echo $text_grid; ?></a></li>
							</ul>
						</div>				
						<div class="sort">
							<b><?php echo $text_sort; ?></b>
							<select onchange="location = this.value;">
								<?php foreach ($sorts as $sorts) { ?>
								<?php if ($sorts['value'] == $sort . '-' . $order) { ?>
								<option value="<?php echo $sorts['href']; ?>" selected="selected"><?php echo $sorts['text']; ?></option>
								<?php } else { ?>
								<option value="<?php echo $sorts['href']; ?>"><?php echo $sorts['text']; ?></option>
								<?php } ?>
								<?php } ?>
							</select>
						</div>		
					</div>
				</div>
				<div class="span5">	
					<div class="filter pull-right">
						<div class="limit">
							<b><?php echo $text_limit; ?></b>
							<select onchange="location = this.value;">
								<?php foreach ($limits as $limits) { ?>
								<?php if ($limits['value'] == $limit) { ?>
								<option value="<?php echo $limits['href']; ?>" selected="selected"><?php echo $limits['text']; ?></option>
								<?php } else { ?>
								<option value="<?php echo $limits['href']; ?>"><?php echo $limits['text']; ?></option>
								<?php } ?>
								<?php } ?>
							</select>
						</div>			
						<div class="product-compare">
							<a href="<?php echo $compare; ?>" id="compare-total" class="button"><?php echo $text_compare; ?></a>
						</div>			
					</div>
				</div>
			</div>				
		</div>
   
	    <div class="product-list"> 
			<?php
			$cols = $MAX_ITEM_ROW ;
			$span = floor(12/$cols);
			foreach ($products as $i => $product) { ?>
			<?php if( $i++%$cols == 0 ) { ?>
			<div class="row-fluid box-product">
			<?php } ?>
				<div class="span<?php echo $span;?>">												
				
					<div class="product-inner clearfix">
						<?php if( $product['special'] ) {   ?>
							<span class="product-label-special label"><?php echo $this->language->get( 'text_sale' ); ?></span>
						<?php } ?>
						
						<?php if ($product['thumb']) { ?>
							<div class="image product-top">
								<a href="<?php echo $product['href']; ?>">
									<img src="<?php echo $product['thumb']; ?>" title="<?php echo $product['name']; ?>" alt="<?php echo $product['name']; ?>" />
								</a>

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
		
						<p class="description"><?php echo substr( strip_tags($product['description']),0,90);?>...</p>
						
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
								<?php if ($product['tax']) { ?>																
									<span class="price-tax"><?php echo $text_tax; ?> <?php echo $product['tax']; ?></span>
								<?php } ?>
							</div>
						<?php } ?>
					
						<div class="pav-group-button clearfix">
							<div class="cart pull-left">
								<input type="button" value="<?php echo $button_cart; ?>" onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button" />
							</div>
							<div class="wishlist pull-left">
								<a onclick="addToWishList('<?php echo $product['product_id']; ?>');"><?php echo $button_wishlist; ?></a>
							</div>
							<div class="compare pull-left">
								<a onclick="addToCompare('<?php echo $product['product_id']; ?>');"><?php echo $button_compare; ?></a>
							</div>
						</div>
					</div>
				</div>
			
				<?php if( $i%$cols == 0 || $i==count($products) ) { ?>
				</div>
				<?php } ?>	
				
			<?php } ?>
		</div>
  
		<div class="pagination clearfix">
			<?php echo $pagination; ?>
			<div class="product-compare pull-right">
				<a href="<?php echo $compare; ?>" id="compare-total" class="button"><?php echo $text_compare; ?></a>
			</div>
		</div>
     
		<?php } else { ?>
			<div class="content"><?php echo $text_empty; ?></div>
			
			
			
			
		<?php } ?>
			
		<?php echo $content_bottom; ?>
  
		<script type="text/javascript">
$('#content input[name=\'search\']').keydown(function(e) {
	if (e.keyCode == 13) {
		$('#button-search').trigger('click');
	}
});

$('select[name=\'category_id\']').bind('change', function() {
	if (this.value == '0') {
		$('input[name=\'sub_category\']').attr('disabled', 'disabled');
		$('input[name=\'sub_category\']').removeAttr('checked');
	} else {
		$('input[name=\'sub_category\']').removeAttr('disabled');
	}
});

$('select[name=\'category_id\']').trigger('change');

$('#button-search').bind('click', function() {
	url = 'index.php?route=product/search';
	
	var search = $('#content input[name=\'search\']').attr('value');
	
	if (search) {
		url += '&search=' + encodeURIComponent(search);
	}

	var category_id = $('#content select[name=\'category_id\']').attr('value');
	
	if (category_id > 0) {
		url += '&category_id=' + encodeURIComponent(category_id);
	}
	
	var sub_category = $('#content input[name=\'sub_category\']:checked').attr('value');
	
	if (sub_category) {
		url += '&sub_category=true';
	}
		
	var filter_description = $('#content input[name=\'description\']:checked').attr('value');
	
	if (filter_description) {
		url += '&description=true';
	}

	location = url;
});
			function display(view) {
				if (view == 'list') {
					$('.product-grid').attr('class', 'product-list');						
					
					$('.product-list div.product-block').each(function(index, element) {
						html  = '<div class="product-inner"><div class="row-fluid"><div class="span3">';
						
						var image = $(element).find('.image').html();						
						if (image != null) { 
							html += '<div class="image">' + image + '</div>';
						}
						
						html += '</div>';			
						
						html += '<div class="span9">';
						
						html += '  <h3 class="name">' + $(element).find('.name').html() + '</h3>';
						html += '  <p class="description">' + $(element).find('.description').html() + '</p>';					
						
						var rating = $(element).find('.rating').html();						
						if (rating != null) {
							html += '<div class="rating">' + rating + '</div>';
						}
						
						var price = $(element).find('.price').html();						
						if (price != null) {
							html += '<div class="price">' + price  + '</div>';
						}
						
						html += '  <div class="pav-group-button clearfix"><div class="cart pull-left">' + $(element).find('.cart').html() + '</div>';
						html += '  <div class="wishlist pull-left">' + $(element).find('.wishlist').html() + '</div>';
						html += '  <div class="compare pull-left">' + $(element).find('.compare').html() + '</div></div></div></div></div>';						
							
						
									
						$(element).html(html);
					});							
					
					$('.display').html('<ul class="clearfix"><li class="first"><b><?php echo $text_display; ?></b></li><li class="list"><span class="pav-icon active"><?php echo $text_list; ?></span></li><li class="grid"><a class="pav-icon" onclick="display(\'grid\');"><?php echo $text_grid; ?></a></li></ul>');
					
					$.totalStorage('display', 'list'); 

				} else {

					$('.product-list').attr('class', 'product-grid');
					
					$('.product-grid .product-block').each(function(index, element) {
						html = '';
						
						var image = $(element).find('.image').html();						
						if (image != null) {
							html += '<div class="product-inner"><div class="image">' + image + '</div>';
						}
						
						html += '<h3 class="name">' + $(element).find('.name').html() + '</h3>';
						html += '<p class="description">' + $(element).find('.description').html() + '</p>';
						
						var rating = $(element).find('.rating').html();						
						if (rating != null) {
							html += '<div class="rating">' + rating + '</div>';
						}
						
						var price = $(element).find('.price').html();						
						if (price != null) {
							html += '<div class="price">' + price  + '</div>';
						}						
									
						html += '<div class="pav-group-button clearfix"><div class="cart pull-left">' + $(element).find('.cart').html() + '</div>';
						html += '<div class="wishlist pull-left">' + $(element).find('.wishlist').html() + '</div>';
						html += '<div class="compare pull-left">' + $(element).find('.compare').html() + '</div></div></div>';
						
						$(element).html(html);
					});	
													
					$('.display').html('<ul class="clearfix"><li class="first"><b><?php echo $text_display; ?></b></li><li class="list"><a class="pav-icon" onclick="display(\'list\');"><?php echo $text_list; ?></a></li><li class="grid"><span class="pav-icon active"><?php echo $text_grid; ?></span></li></ul>');
					
					$.totalStorage('display', 'grid');
				}
			}

			view = $.totalStorage('display');

			if (view) {
				display(view);
			} else {
			display('<?php echo $DISPLAY_MODE;?>');
			}

			//-->
		</script> 

		<!-- <script type="text/javascript">
				$(function(){				
					$('.product-list .row-fluid > div').removeClass("span4");
					$('.product-grid .row-fluid > div').addClass("span4");
				});	
			</script> -->
			
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



		
	</div>
</section> 

<?php if( $SPAN[2] ): ?>
<aside class="span<?php echo $SPAN[2];?>">	
	<?php echo $column_right; ?>
</aside>
<?php endif; ?>

<?php echo $footer; ?>