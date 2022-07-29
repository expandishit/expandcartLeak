<?php require( DIR_TEMPLATE.$this->config->get('config_template')."/template/common/config.tpl" );
$themeConfig = $this->config->get('themecontrol');
$DISPLAY_MODE = 'grid';
if( isset($themeConfig['cateogry_display_mode']) ){
$DISPLAY_MODE = $themeConfig['cateogry_display_mode'];
}
$MAX_ITEM_ROW =3;
if( isset($themeConfig['cateogry_product_row']) && $themeConfig['cateogry_product_row'] ){
$MAX_ITEM_ROW = $themeConfig['cateogry_product_row'];
}
$categoryPzoom = isset($themeConfig['category_pzoom']) ? $themeConfig['category_pzoom']:0;

?>
<?php echo $header; ?>
<div class="span12">
<div id="content" class="ms-catalog-seller-products">
	<?php echo $content_top; ?>
	
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>
	
	<div class="ms-sellerprofile">
		<div class="seller-data">
			<div class="avatar-box">
				<a style="text-decoration: none" href="<?php echo $seller['href']; ?>"><h2><?php echo $ms_catalog_seller_products; ?></h2></a>
				<a href="<?php echo $seller['href']; ?>"><img src="<?php echo $seller['thumb']; ?>" style="max-width: 100px;"/></a>
			</div>
			
			<div class="info-box">
				<?php if ($seller['country']) { ?>
					<p><b><?php echo $ms_catalog_seller_profile_country; ?></b> <?php echo $seller['country']; ?></p>
				<?php } ?>
				
				<?php if ($seller['company']) { ?>
					<p><b><?php echo $ms_catalog_seller_profile_company; ?></b> <?php echo $seller['company']; ?></p>
				<?php } ?>
				
				<?php if ($seller['website']) { ?>
					<p><b><?php echo $ms_catalog_seller_profile_website; ?></b> <?php echo $seller['website']; ?></p>
				<?php } ?>
				
				<p><b><?php echo $ms_catalog_seller_profile_totalsales; ?></b> <?php echo $seller['total_sales']; ?></p>
				<p><b><?php echo $ms_catalog_seller_profile_totalproducts; ?></b> <?php echo $seller['total_products']; ?></p>
			</div>
		</div>
	</div>
	
	<?php if ($seller['products']) { ?>
		<div class="product-filter">
			<div class="display">
				<span><?php echo $text_display; ?></span>
				<span><?php echo $text_list; ?></span>
				<a onclick="display('grid');"><?php echo $text_grid; ?></a>
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
		</div>

		<div class="product-list">
			<?php
			$cols = $MAX_ITEM_ROW ;
			$span = floor(12/$cols);
			foreach ($seller['products'] as $product) { ?>
			<?php if( $i++%$cols == 0 ) { ?>
			<div class="row-fluid">
				<?php } ?>
				<div class="span<?php echo $span;?> product-block">
					<div class="product-inner">
						<?php if ($product['thumb']) { ?>
						<div class="image">
							<?php if( $product['special'] ) {   ?>
							<span class="product-label-special label"><?php echo $this->language->get( 'text_sale' ); ?></span>
							<?php } ?>
							<a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" title="<?php echo $product['name']; ?>" alt="<?php echo $product['name']; ?>" /></a>
						</div>
						<?php } ?>
						<div class="product-meta">
							<div class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></div>


							<div class="rating">
								<?php if ($product['rating']) { ?>
								<img src="catalog/view/theme/<?php echo $this->config->get('config_template');?>/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" />
								<?php } ?>
							</div>


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

							<div class="description"><?php echo substr( strip_tags($product['description']),0,100);?>...</div>
							<div class="group-action">
								<div class="cart">
									<input type="button" value="<?php echo $button_cart; ?>" onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button" />
								</div>
								<div class="wishlist">	<a onclick="addToWishList('<?php echo $product['product_id']; ?>');" title="<?php echo $button_wishlist; ?>" ><?php echo $button_wishlist; ?></a></div>
								<div class="compare" ><a onclick="addToCompare('<?php echo $product['product_id']; ?>');" title="<?php echo $button_compare; ?>" ><?php echo $button_compare; ?></a></div>

							</div>
						</div>
					</div>
				</div>
				<?php if( $i%$cols == 0 || $i==count($products) ) { ?>
			</div>
			<?php } ?>
			<?php } ?>
		</div>

	<div class="product-filter">
		<div class="pagination"><?php echo $pagination; ?></div>
	</div>
	<?php } else { ?>
		<div class="content"><?php echo $ms_catalog_seller_products_empty; ?></div>
	<?php }?>
	
	<?php echo $content_bottom; ?>
</div>
<script type="text/javascript"><!--
    function display(view) {
        if (view == 'list') {
            $('.product-grid').attr('class', 'product-list');

            $('.product-list div.product_block').each(function(index, element) {
                html  = '<div class="right">';
                html += '  <div class="cart">' + $(element).find('.cart').html() + '</div>';
                html += '  <div class="wishlist">' + $(element).find('.wishlist').html() + '</div>';
                html += '  <div class="compare">' + $(element).find('.compare').html() + '</div>';
                html += '</div>';

                html += '<div class="left">';

                var image = $(element).find('.image').html();

                if (image != null) {
                    html += '<div class="image">' + image + '</div>';
                }

                var price = $(element).find('.price').html();

                if (price != null) {
                    html += '<div class="price">' + price  + '</div>';
                }

                html += '  <div class="name">' + $(element).find('.name').html() + '</div>';
                html += '  <div class="description">' + $(element).find('.description').html() + '</div>';

                var rating = $(element).find('.rating').html();

                html += '<div class="rating">';
                if (rating != null) {
                    html += rating;
                }
                html += '</div>';

                html += '</div>';

                $(element).html(html);
            });

            $('.display').html('<span style="float: inherit;"><?php echo $text_display; ?></span><a class="list active"><?php echo $text_list; ?></a><a class="grid"  onclick="display(\'grid\');"><?php echo $text_grid; ?></a>');

            $.totalStorage('display', 'list');
        } else {
            $('.product-list').attr('class', 'product-grid');

            $('.product-grid div.product_block').each(function(index, element) {
                html = '';

                var image = $(element).find('.image').html();

                if (image != null) {
                    html += '<div class="image">' + image + '</div>';
                }

                html += '<div class="name">' + $(element).find('.name').html() + '</div>';
                html += '<div class="description">' + $(element).find('.description').html() + '</div>';

                var price = $(element).find('.price').html();

                if (price != null) {
                    html += '<div class="price">' + price  + '</div>';
                }

                var rating = $(element).find('.rating').html();

                html += '<div class="rating">';
                if (rating != null) {
                    html += rating;
                }
                html += '</div>';

                html += '<div class="cart">' + $(element).find('.cart').html() + '</div>';
                html += '<div class="wishlist">' + $(element).find('.wishlist').html() + '</div>';
                html += '<div class="compare">' + $(element).find('.compare').html() + '</div>';

                $(element).html(html);
            });

            $('.display').html('<span style="float: inherit;"><?php echo $text_display; ?></span><a class="list" onclick="display(\'list\');"><?php echo $text_list; ?></a><a class="grid active"><?php echo $text_grid; ?></a>');

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

</div>
<?php echo $footer; ?>