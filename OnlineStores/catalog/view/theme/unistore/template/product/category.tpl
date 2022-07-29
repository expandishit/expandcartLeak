<?php echo $header; ?>
<?php $grid = 12; if($column_left != '') { $grid = $grid-3; } if($column_right != '') { $grid = $grid-3; } ?>
<?php echo $content_top; ?>
		<!-- Content Center -->
		
		<div id="content-center">
		
			<!-- Breadcrumb -->
			
			<div class="breadcrumb">
			
			    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
			    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
			    <?php } ?>
				<h2><?php echo $heading_title; ?></h2>
			
			</div>
			
			<!-- End Breadcrumb -->
			
			<?php echo $column_left; ?>
			
			<div class="grid-<?php echo $grid; ?> float-left">
		

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
  <div class="category-list">

    <ul>
	 	<?php foreach ($categories as $category) { ?>
		<?php if($category['thumb'] != '') { $image = $this->model_tool_image->resize($category['thumb'], $this->config->get('config_image_thumb_width'), $this->config->get('config_image_thumb_height')); } else { $image = 'catalog/view/theme/unistore/images/no_image.png'; } ?>
      <li><a href="<?php echo $category['href']; ?>"><img src="<?php echo $image; ?>" alt="<?php echo $category['name']; ?>" /><br /><?php echo $category['name']; ?></a></li>
      <?php } ?>
    </ul>
	 
  </div>
  <?php } ?>
  <?php if ($products) { ?>
  <div class="product-filter">
    <div class="display"><h3><?php echo $text_display; ?></h3> <div class="display-grid"><a onclick="display('grid');"><?php echo $text_grid; ?></a></div><div class="active-display-list"><?php echo $text_list; ?></div></div>
	<div class="product-compare"><a href="<?php echo $compare; ?>" id="compare_total"><?php echo $text_compare; ?></a></div>
    <div class="limit"><?php echo $text_limit; ?>
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
    <div class="sort"><?php echo $text_sort; ?>
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
  <div class="product-list">
    <?php foreach ($products as $product) { ?>
    <div>
      <?php if ($product['thumb']) { ?>
		<div class="image"><?php if ($product['special']) { ?><div class="product-sale"></div><?php } ?><a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" title="<?php echo $product['name']; ?>" alt="<?php echo $product['name']; ?>" /></a></div>
      <?php } ?>
		<div class="description">
			<div class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></div>
			<p>
			
				<?php echo $product['description']; ?>
		      <?php if ($product['rating']) { ?><br />
		      <img src="catalog/view/theme/default/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" />
		      <?php } ?>
							
			</p>
		</div>
						<div class="right">
						
							<?php if ($product['price']) { ?>
								<?php if (!$product['special']) { ?>
					        <div class="price"><?php echo $product['price']; ?></div>
					        <?php } else { ?>
					        <div class="price"><span class="price-old"><?php echo $product['price']; ?></span><br /><?php echo $product['special']; ?></span></div>
					        <?php } ?>
							<?php } ?>
							<div class="cart"><a onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button"><span><?php echo $button_cart; ?></span></a></div>
							<div class="wish-list"><a onclick="addToWishList('<?php echo $product['product_id']; ?>');">+ Wish List</a><br /><a onclick="addToCompare('<?php echo $product['product_id']; ?>');">+ Compare</a></div>
							<div class="wish-list_list" style="display:none"><a onclick="addToWishList('<?php echo $product['product_id']; ?>');">+ Wish List</a><br /><a onclick="addToCompare('<?php echo $product['product_id']; ?>');">+ Compare</a></div>
							<div class="wish-list_grid" style="display:none;"><a onclick="addToWishList('<?php echo $product['product_id']; ?>');">+ Wish List</a>&nbsp;&nbsp;&nbsp;&nbsp;<a onclick="addToCompare('<?php echo $product['product_id']; ?>');">+ Compare</a></div>
						</div>

    </div>
    <?php } ?>
  </div>
  <div class="product-grid" style="display:none">
    <?php foreach ($products as $product) { ?>
    <div>
      <?php if ($product['thumb']) { ?>
		<div class="image"><?php if ($product['special']) { ?><div class="product-sale"></div><?php } ?><a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" title="<?php echo $product['name']; ?>" alt="<?php echo $product['name']; ?>" /></a></div>
      <?php } ?>
		<div class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></div>
		<?php if ($product['price']) { ?>
			<?php if (!$product['special']) { ?>
			<div class="price"><?php echo $product['price']; ?></div>
			<?php } else { ?>
			<div class="price"><?php echo $product['special']; ?></span><br /><span class="price-old"><?php echo $product['price']; ?></span></div>
			<?php } ?>
		<?php } ?>
		<div class="cart"><a onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button"><span><?php echo $button_cart; ?></span></a></div>
		<div class="wish-list"><a onclick="addToWishList('<?php echo $product['product_id']; ?>');">+ Wish List</a>&nbsp;&nbsp;&nbsp;&nbsp;<a onclick="addToCompare('<?php echo $product['product_id']; ?>');">+ Compare</a></div>
    </div>
    <?php } ?>
  </div>
  <div class="pagination"><?php echo $pagination; ?></div>
  <?php } ?>
  <?php if (!$categories && !$products) { ?>
  <div class="content"><?php echo $text_empty; ?></div>
  <div class="buttons">
    <div class="right"><a href="<?php echo $continue; ?>" class="button"><span><?php echo $button_continue; ?></span></a></div>
  </div>
  <?php } ?>
	
			</div>
			
			<?php echo $column_right; ?>
			
			<p class="clear"></p>
		
		</div>
		
		<!-- End Content Center -->
	
  <?php echo $content_bottom; ?></div>
<script type="text/javascript"><!--
function display(view) {
	if (view == 'list') {
		$('.product-grid').css("display", "none");
		$('.product-list').css("display", "block");

		$('.display').html('<h3><?php echo $text_display; ?></h3> <div class="display-grid"><a onclick="display(\'grid\');"><?php echo $text_grid; ?></a></div><div class="active-display-list"><?php echo $text_list; ?></div>');
		
		$.cookie('display', 'list'); 
	} else {
	
		$('.product-grid').css("display", "block");
		$('.product-list').css("display", "none");
					
		$('.display').html('<h3><?php echo $text_display; ?></h3> <div class="active-display-grid"><?php echo $text_grid; ?></div><div class="display-list"><a onclick="display(\'list\');"><?php echo $text_list; ?></a></div>');
		
		$.cookie('display', 'grid');
	}
}

view = $.cookie('display');

if (view) {
	display(view);
} else {
	display('list');
}
//--></script> 
<?php echo $footer; ?>