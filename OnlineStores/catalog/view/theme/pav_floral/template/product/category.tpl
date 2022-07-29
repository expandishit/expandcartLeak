<?php require( DIR_TEMPLATE.$this->config->get('config_template')."/template/common/config.tpl" ); 
	$themeConfig = $this->config->get('themecontrol');
	 
	 $categoryConfig = array( 
		'listing_products_columns' 		     => 0,
		'listing_products_columns_small' 	 => 2,
		'listing_products_columns_minismall' => 1,
		'cateogry_display_mode' 			 => 'grid',
		'category_pzoom'				     => 1,
		'quickview'                             => 1,
		'show_swap_image'                       => 1,
	); 
	$categoryConfig  = array_merge($categoryConfig, $themeConfig );
	$DISPLAY_MODE 	 = $categoryConfig['cateogry_display_mode'];
	$MAX_ITEM_ROW 	 = $themeConfig['listing_products_columns']?$themeConfig['listing_products_columns']:3; 
	$MAX_ITEM_ROW_SMALL = $categoryConfig['listing_products_columns_small'] ;
	$MAX_ITEM_ROW_MINI  = $categoryConfig['listing_products_columns_minismall']; 
	$categoryPzoom 	    = $categoryConfig['category_pzoom']; 
	$quickview          = $categoryConfig['quickview'];
	$swapimg            = ($categoryConfig['show_swap_image'])?'swap':'';
?>
<?php echo $header; ?>
<?php require( DIR_TEMPLATE.$this->config->get('config_template')."/template/common/breadcrumb.tpl" );  ?>	 
<?php if( $SPAN[0] ): ?>
	<aside class="col-lg-<?php echo $SPAN[0];?> col-md-<?php echo $SPAN[0];?> col-xs-12">
		<?php echo $column_left; ?>
	</aside>	
<?php endif; ?> 
<section class="col-lg-<?php echo $SPAN[1];?> col-md-<?php echo $SPAN[1];?> col-sm-12 col-xs-12">

<div id="content"><?php echo $content_top; ?>
  <h1><?php echo $heading_title; ?></h1>
 	
  <?php if ($thumb || $description) { ?>
  <div class="category-info clearfix">
    <?php if ($thumb) { ?>
    <div class="image"><img src="<?php echo $thumb; ?>" alt="<?php echo $heading_title; ?>" /></div>
    <?php } ?>
    <?php if ($description) { ?>
    <?php echo $description; ?>
    <?php } ?>
  </div>
  <?php } ?> 
  <?php if ($categories) { ?>
 <div class="refine ">
  <h4><?php echo $text_refine; ?></h4>
  <div class="category-list clearfix">
    <?php if (count($categories) <= 5) { ?>
    <ul>
      <?php foreach ($categories as $category) { ?>
      <li><a href="<?php echo $category['href']; ?>"><?php echo $category['name']; ?></a></li>
      <?php } ?>
    </ul>
    <?php } else { ?>
    <?php for ($i = 0; $i < count($categories);) { ?>
    <ul>
      <?php $j = $i + ceil(count($categories) / 4); ?>
      <?php for (; $i < $j; $i++) { ?>
      <?php if (isset($categories[$i])) { ?>
      <li><a href="<?php echo $categories[$i]['href']; ?>"><?php echo $categories[$i]['name']; ?></a></li>
      <?php } ?>
      <?php } ?>
    </ul>
    <?php } ?>
    <?php } ?>
  </div>
</div>
  <?php } ?>
  <?php if ($products) { ?>
  <div class="product-filter clearfix">
    <div class="display">
		<span><?php echo $text_display; ?></span>
		<span><?php echo $text_list; ?></span>
		<a onclick="display('grid');"><span class="fa fa-th-large"></span><?php echo $text_grid; ?></a>
	</div>

	<div class="product-compare"><a href="<?php echo $compare; ?>" id="compare-total"><?php echo $text_compare; ?></a></div>
    
	<div class="limit"><span><?php echo $text_limit; ?></span>
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
 	<div class="sort"><span><?php echo $text_sort; ?></span>
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

  
<div class="product-list"> <div class="products-block">
    <?php
	$cols = $MAX_ITEM_ROW ;
	$span = floor(12/$cols);
	$small = floor(12/$MAX_ITEM_ROW_SMALL);
	$mini = floor(12/$MAX_ITEM_ROW_MINI);
	foreach ($products as $i => $product) { ?>
	<?php if( $i++%$cols == 0 ) { ?>
		  <div class="row">
	<?php } ?>
    <div class="col-xs-<?php echo $mini;?> col-lg-<?php echo $span;?> col-md-<?php echo $span;?> col-sm-<?php echo $small;?>">
    	<div class="product-block">	
	      <?php if ($product['thumb']) { ?>
	      <div class="image <?php echo $swapimg; ?>">
			<?php if( $product['special'] ) {   ?>
				<span class="product-label-special label"><?php echo $this->language->get( 'text_sale' ); ?></span>
	    	<?php } ?>
	    	
	      	
			<?php if( $categoryPzoom ) { $zimage = str_replace( "cache/","", preg_replace("#-\d+x\d+#", "",  $product['thumb'] ));  ?>
	      	<a href="<?php echo $zimage;?>" class="colorbox product-zoom" rel="colorbox" title="<?php echo $product['name']; ?>"><span class="fa fa-search-plus"></span></a>
	      	<?php } ?>
			
				<div class="image_container">
					<a href="<?php echo $product['href']; ?>" class="img front"><img src="<?php echo $product['thumb']; ?>" title="<?php echo $product['name']; ?>" alt="<?php echo $product['name']; ?>" class="img-responsive" /></a>
					<!-- Show Swap -->
					<?php 
					if( $categoryConfig['show_swap_image'] ){
						$product_images = $this->model_catalog_product->getProductImages( $product['product_id'] );
						if(isset($product_images) && !empty($product_images)) {
							$thumb2 = $this->model_tool_image->resize($product_images[0]['image'],  $this->config->get('config_image_product_width'),  $this->config->get('config_image_product_height') );
						?>	
						<a class="img back" href="<?php echo $product['href']; ?>">
							<img src="<?php echo $thumb2; ?>">
						</a>
					<?php } } ?>
				</div>								


				<?php //#2 Start fix quickview in fw?>
				<?php if ($quickview) { ?>
					<a class="pav-colorbox" href="index.php?route=themecontrol/product&product_id=<?php echo $product['product_id']; ?>"><span class='fa fa-eye'></span><?php echo $this->language->get('quick_view'); ?></a>
				<?php } ?>
				<?php //#2 End fix quickview in fw?>


				<div class="img-overlay"></div>
			
			
	      </div>
	      <?php } ?>
	      <h3 class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></h3>
	       <?php if ($product['rating']) { ?>
			<div class="rating"><img src="catalog/view/theme/<?php echo $this->config->get('config_template');?>/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" /></div>
	      <?php } ?>
		  
		  <div class="description"><?php echo utf8_substr( strip_tags($product['description']),0,160);?>...</div>
		 
		 <div class="group-item"> 
		  <div class="price-cart">
	      <?php if ($product['price']) { ?>
	      <div class="price">
	        <?php if (!$product['special']) { ?>
	        <?php echo $product['price']; ?>
	        <?php } else { ?>
	        <span class="price-old"><?php echo $product['price']; ?></span> <span class="price-new"><?php echo $product['special']; ?></span>
	        <?php } ?>
	        <?php if ($product['tax']) { ?>
	        <br />
	        <span class="price-tax"><?php echo $text_tax; ?> <?php echo $product['tax']; ?></span>
	        <?php } ?>
	      </div>
	      <?php } ?>
	      
	      <div class="cart">
	        <button class="button" onclick="addToCart('<?php echo $product['product_id']; ?>');">
	        	<span class="fa fa-shopping-cart icon"></span>
	        	<span><?php echo $button_cart; ?></span>
	        </button>
	      </div>
		  </div>
		  </div>
		  <div class="wishlist-compare">
			  <a class="wishlist" onclick="addToWishList('<?php echo $product['product_id']; ?>');" title="<?php echo $button_wishlist; ?>" ><?php echo $button_wishlist; ?></a>
			  <a class="compare"  onclick="addToCompare('<?php echo $product['product_id']; ?>');" title="<?php echo $button_compare; ?>" ><?php echo $button_compare; ?></a>
		 </div>
	    </div>
		</div>
	 <?php if( $i%$cols == 0 || $i==count($products) ) { ?>
	 </div>
	 <?php } ?>
				
    <?php } ?>
  </div>
  </div>
 
   <div class="pagination"><?php echo $pagination; ?></div>
  <?php } ?>
  <?php if (!$categories && !$products) { ?>
  <div class="content"><?php echo $text_empty; ?></div>
  <div class="buttons">
    <div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>
  </div>
  <?php } ?>
  <?php echo $content_bottom; ?></div>
<script type="text/javascript"><!--
function display(view) {
	if (view == 'list') {
		$('.product-grid').attr('class', 'product-list');
		
		$('.products-block  .product-block').each(function(index, element) {
 
			 $(element).parent().addClass("col-fullwidth");
		});		
		
		$('.display').html('<span style="float: left;"><?php echo $text_display; ?></span><a class="list active"><span class="fa fa-th-large"></span><em><?php echo $text_list; ?></em></a><a class="grid"  onclick="display(\'grid\');"><span class="fa fa-th-list"></span><em><?php echo $text_grid; ?></em></a>');
	
		$.totalStorage('display', 'list'); 
	} else {
		$('.product-list').attr('class', 'product-grid');
		
		$('.products-block  .product-block').each(function(index, element) {
			 $(element).parent().removeClass("col-fullwidth");  
		});	
					
		$('.display').html('<span style="float: left;"><?php echo $text_display; ?></span><a class="list" onclick="display(\'list\');"><span class="fa fa-th-large"></span><em><?php echo $text_list; ?></em></a><a class="grid active"><span class="fa fa-th-list"></span><em><?php echo $text_grid; ?></em></a>');
	
		$.totalStorage('display', 'grid');
	}
}

view = $.totalStorage('display');

if (view) {
	display(view);
} else {
	display('<?php echo $DISPLAY_MODE;?>');
}
//--></script> 
<?php if( $categoryPzoom ) {  ?>
<script type="text/javascript"><!--
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
	
	$('.pav-colorbox').colorbox({
        width: '800px', 
        height: '550px',
        overlayClose: true,
        opacity: 0.5,
        iframe: true, 
    }); 
	
});
//--></script>
<?php } ?>
</section> 

<?php if( $SPAN[2] ): ?>
	<aside class="col-lg-<?php echo $SPAN[2];?> col-md-<?php echo $SPAN[2];?> col-xs-12">	
		<?php echo $column_right; ?>
	</aside>
<?php endif; ?>
 
<?php echo $footer; ?>