<?php require( DIR_TEMPLATE.$this->config->get('config_template')."/template/common/config.tpl" );
$themeConfig = $this->config->get('themecontrol');
$categoryConfig = array(
'listing_products_columns' 		     => 0,
'listing_products_columns_small' 	 => 2,
'listing_products_columns_minismall' => 1,
'cateogry_display_mode' 			 => 'grid',
'category_pzoom'				     => 1,
'quickview'                          => 0,
'show_swap_image'                    => 0,
);
$categoryConfig     = array_merge($categoryConfig, $themeConfig );
$DISPLAY_MODE 	    = $categoryConfig['cateogry_display_mode'];
$MAX_ITEM_ROW 	    = $themeConfig['listing_products_columns']?$themeConfig['listing_products_columns']:3;
$MAX_ITEM_ROW_SMALL = $categoryConfig['listing_products_columns_small']?$themeConfig['listing_products_columns']:2;
$MAX_ITEM_ROW_MINI  = $categoryConfig['listing_products_columns_minismall']?$themeConfig['listing_products_columns']:1;
$categoryPzoom 	    = $categoryConfig['category_pzoom'];
$quickview          = $categoryConfig['quickview'];
$swapimg            = $categoryConfig['show_swap_image'];
?>


<?php echo $header; ?>
<?php require( DIR_TEMPLATE.$this->config->get('config_template')."/template/common/breadcrumb.tpl" );  ?>  
<?php if( $SPAN[0] ): ?>
	<aside class="col-lg-<?php echo $SPAN[0];?> col-md-<?php echo $SPAN[0];?> col-sm-12 col-xs-12">
		<?php echo $column_left; ?>
	</aside>
<?php endif; ?> 
<section class="col-lg-<?php echo $SPAN[1];?> col-md-<?php echo $SPAN[1];?> col-sm-12 col-xs-12">
<div id="content">
<?php echo $content_top; ?>
  
  <div class="search">
  <h1><?php echo $heading_title; ?></h1>
  <h2><?php echo $text_critea; ?></h2>
  <div class="content">
    <div class="pav-label">
    	<div class="row">
    		<div class="col-lg-6 col-md-6 col-sm-6 sleft">
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2">
						<?php echo $entry_search; ?>
					</div>
					<div class="col-lg-10 col-md-10 col-sm-10">
						<?php if ($search) { ?>
						<input type="text" name="search" value="<?php echo $search; ?>" class="input-text form-control" />
						<?php } else { ?>
						<input type="text" name="search" value="<?php echo $search; ?>" onclick="this.value = '';" onkeydown="this.style.color = '000000'" style="color: #999;"  class="input-text form-control"/>
						<?php } ?>
					</div>
				</div>				
    		</div>
    		<div class="col-lg-6 col-md-6 col-sm-6 sright">
				<div class="row">
					<div class="col-lg-6 col-md-6 col-sm-6">
    			<select name="category_id" class="form-control">
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
			    <div class="col-lg-6 col-md-6 col-sm-6">
				<?php if ($sub_category) { ?>
				<input type="checkbox" name="sub_category" value="1" id="sub_category" checked="checked" />
				<?php } else { ?>
				<input type="checkbox" name="sub_category" value="1" id="sub_category" />
				<?php } ?>
				<label for="sub_category"><?php echo $text_sub_category; ?></label>
			    </div>
				</div>
    		</div>
    	</div>
    </div>
    <div class="pav-check-desc pav-check-desc checkbox">
    <?php if ($description) { ?>
    <input type="checkbox" name="description" value="1" id="description" checked="checked" />
    <?php } else { ?>
    <input type="checkbox" name="description" value="1" id="description" />
    <?php } ?>
    <label for="description"><?php echo $entry_description; ?></label>
	</div>
  </div>
  <div class="buttons">
    <div class="right"><input type="button" value="<?php echo $button_search; ?>" id="button-search" class="button" /></div>
  </div>
  <h2><?php echo $text_search; ?></h2>
  <?php if ($products) { ?>
  <div class="product-filter clearfix">
    <div class="display">
		<span><?php echo $text_display; ?></span>
		<span><?php echo $text_list; ?></span>
		<a onclick="display('grid');"><?php echo $text_grid; ?></a>
	</div>
    
    <div class="product-compare"><a href="<?php echo $compare; ?>" id="compare-total" class="btn btn-theme-primary"><?php echo $text_compare; ?></a></div>
	<div class="limit"><span><?php echo $text_limit; ?></span>
      <select class="form-control" onchange="location = this.value;">
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
      <select class="form-control" onchange="location = this.value;">
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

    <?php if ($products) { ?>
    <?php require( DIR_TEMPLATE.$this->config->get('config_template')."/template/product/product_collection.tpl" );  ?>
    <?php } ?>

  <?php } ?>
  <?php if (!$categories && !$products) { ?>
  <div class="content"><?php echo $text_empty; ?></div>
  <div class="buttons">
    <div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>
  </div>
  <?php } ?>
  <?php echo $content_bottom; ?></div>
<script type="text/javascript"><!--
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
		
		$('.products-block  .product-block').each(function(index, element) {
 
			 $(element).parent().addClass("col-fullwidth");
		});		
		
		$('.display').html('<span style="float: left;"><?php echo $text_display; ?></span><a class="list active"><em><?php echo $text_list; ?></em></a><a class="grid"  onclick="display(\'grid\');"><em><?php echo $text_grid; ?></em></a>');
	
		$.totalStorage('display', 'list'); 
	} else {
		$('.product-list').attr('class', 'product-grid');
		
		$('.products-block  .product-block').each(function(index, element) {
			 $(element).parent().removeClass("col-fullwidth");  
		});	
					
		$('.display').html('<span style="float: left;"><?php echo $text_display; ?></span><a class="list" onclick="display(\'list\');"></span><em><?php echo $text_list; ?></em></a><a class="grid active"><em><?php echo $text_grid; ?></em></a>');
	
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
	 
});
//--></script>
<?php } ?>
</section> 

<?php if( $SPAN[2] ): ?>
	<aside class="col-lg-<?php echo $SPAN[2];?> col-md-<?php echo $SPAN[2];?> col-sm-12 col-xs-12">	
		<?php echo $column_right; ?>
	</aside>
<?php endif; ?>
 
<?php echo $footer; ?>