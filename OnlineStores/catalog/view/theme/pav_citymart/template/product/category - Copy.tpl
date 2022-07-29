<?php require(  DIR_TEMPLATE.$this->config->get('config_template')."/template/common/config.tpl" ); ?>
<?php echo $header; ?>
<?php if( $SPAN[0] ): ?>
	<div class="span<?php echo $SPAN[0];?>">
		<?php echo $column_left; ?>
	</div>
<?php endif; ?> 
<div class="span<?php echo $SPAN[1];?> category">
<?php require( DIR_TEMPLATE.$this->config->get('config_template')."/template/common/breadcrumb.tpl" ); ?>
<div id="content"><?php echo $content_top; ?>

  <h1 class="category-title"><?php echo $heading_title; ?></h1>
  <?php if ($thumb || $description) { ?>
  <div class="category-info">
    <?php if ($thumb) { ?>
    <div class="image"><img src="<?php echo $thumb; ?>" alt="<?php echo $heading_title; ?>" /></div>
    <?php } ?>
    <?php if ($description) { ?>
    <?php echo $description; ?>
    <?php } ?>
  </div>
  <?php } ?>
  <?php if ($categories) { ?>
	<div class="refine">
	  <h2><?php echo $text_refine; ?></h2>
	  <div class="category-list">
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
  <div class="product-filter">
    <div class="display">
		<b><?php echo $text_display; ?></b> 
		<?php echo $text_list; ?> 
		<b>/</b> 
		<a onclick="display('grid');"><?php echo $text_grid; ?></a>
	</div>
    <div class="limit"><b><?php echo $text_limit; ?></b>
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
    <div class="sort"><b><?php echo $text_sort; ?></b>
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
	<div class="product-compare"><a href="<?php echo $compare; ?>" id="compare-total"><?php echo $text_compare; ?></a></div>
  </div>
<div class="product-list">
    <?php
	$cols = 4;
	$ispan = 12/$cols;
	foreach ($products as $i => $product) { $i=$i+1;?>
	<?php if( $i%$cols == 1 ) { ?>
		  <div class="row-fluid box-product">
	<?php } ?>
    <div class="myspan<?php echo $ispan;?> product_block <?php if($i%$cols == 0) { echo "last";} ?>"><div class="product-inner">
      <?php if ($product['thumb']) { ?>
      <div class="image"><a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" title="<?php echo $product['name']; ?>" alt="<?php echo $product['name']; ?>" /></a></div>
      <?php } ?>
		<div class="wrap-infor">
		  <div class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></div>
		  <div class="description"><?php echo substr( strip_tags($product['description']),0,58);?>...</div>
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
		  <?php if ($product['rating']) { ?>
		  <div class="rating"><img src="catalog/view/theme/default/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" /></div>
		  <?php } ?>
			<div class="action">
				<div class="wishlist"><a onclick="addToWishList('<?php echo $product['product_id']; ?>');"><?php echo $button_wishlist; ?></a></div>
				<div class="compare"><a onclick="addToCompare('<?php echo $product['product_id']; ?>');"><?php echo $button_compare; ?></a></div>
			</div>
			<div class="cart">
				<input type="button" value="<?php echo $button_cart; ?>" onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button" />
			</div>
		</div>
  </div></div>
	 <?php if( $i%$cols == 0 || $i==count($products) ) { ?>
	 </div>
	 <?php } ?>
				
    <?php } ?>
 
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
		
		$('.product-list div.product_block').each(function(index, element) {
			html  = '';
			
			
			
			
			var image = $(element).find('.image').html();
			
			if (image != null) { 
				html += '<div class="image left">' + image + '</div>';
			}
			
			html += '<div class="wrap-infor right">';
			html += '  <div class="name">' + $(element).find('.name').html() + '</div>';
			var rating = $(element).find('.rating').html();			
			if (rating != null) {
				html += '<div class="rating">' + rating + '</div>';
			}			
			html += '  <div class="description">' + $(element).find('.description').html() + '</div>';
			var price = $(element).find('.price').html();
			
			if (price != null) {
				html += '<div class="price">' + price  + '</div>';
			}
			
			
			html += '  <div class="cart">' + $(element).find('.cart').html() + '</div>';
			html += '  <div class="wishlist">' + $(element).find('.wishlist').html() + '</div>';
			html += '  <div class="compare">' + $(element).find('.compare').html() + '</div>';
				
			html += '</div>';
						
			$(element).html(html);
		});		
		
		$('.display').html('<b><?php echo $text_display; ?></b> <?php echo $text_list; ?> <b>/</b> <a onclick="display(\'grid\');"><?php echo $text_grid; ?></a>');
		
		$.totalStorage('display', 'list'); 
	} else {
		$('.product-list').attr('class', 'product-grid');
		
		$('.product-grid div.product_block').each(function(index, element) {
			html = '';
			
			var image = $(element).find('.image').html();
			
			if (image != null) {
				html += '<div class="product-inner"><div class="image">' + image + '</div>';
			}
			
			html += '<div class="wrap-infor"><div class="name">' + $(element).find('.name').html() + '</div>';
			html += '<div class="description">' + $(element).find('.description').html() + '</div>';
			
			var price = $(element).find('.price').html();
			
			if (price != null) {
				html += '<div class="price">' + price  + '</div>';
			}
			
			var rating = $(element).find('.rating').html();
			
			if (rating != null) {
				html += '<div class="rating">' + rating + '</div>';
			}
						
			
			html += '<div class="action"><div class="wishlist">' + $(element).find('.wishlist').html() + '</div>';
			html += '<div class="compare">' + $(element).find('.compare').html() + '</div></div>';
			html += '<div class="cart">' + $(element).find('.cart').html() + '</div></div></div>';
			
			$(element).html(html);
		});	
					
		$('.display').html('<b><?php echo $text_display; ?></b> <a onclick="display(\'list\');"><?php echo $text_list; ?></a> <b>/</b> <?php echo $text_grid; ?>');
		
		$.totalStorage('display', 'grid');
	}
}

view = $.totalStorage('display');

if (view) {
	display(view);
} else {
	display('grid');
}
//--></script> 
</div> 
<?php if( $SPAN[2] ): ?>
<div class="span<?php echo $SPAN[2];?>">	
	<?php echo $column_right; ?>
</div>
<?php endif; ?>
<?php echo $footer; ?>