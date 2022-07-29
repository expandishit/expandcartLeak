<?php require( DIR_TEMPLATE.$this->config->get('config_template')."/template/common/config.tpl" );
$themeConfig = $this->config->get('themecontrol');
$DISPLAY_MODE = 'grid';
if( isset($themeConfig['cateogry_display_mode']) ){
$DISPLAY_MODE = $themeConfig['cateogry_display_mode'];
}

$categoryPzoom = isset($themeConfig['category_pzoom']) ? $themeConfig['category_pzoom']:0;
?><?php echo $header; ?>
<div class="span12">
    <div id="content" class="special">
        <?php if ($thumb || $description) { ?>
            <div class="deal-info">
                <?php if ($thumb) { ?>
                    <div class="image"><img src="<?php echo $thumb; ?>" alt="<?php echo $heading_title; ?>" /></div>
                <?php } ?>

                <?php if ($description) { ?>
                    <div class="description" style="margin-top: 15px;"><?php echo $description; ?></div>
                <?php } ?>
                <br class="clear clr"/>
            </div>
        <?php } ?>

        <div class="category-list">
            <?php if (count($categories) <= 5) { ?>
            <ul>
                <?php foreach ($categories as $category) { ?>
                <?php
        $class = "";
        if(isset($category_id) && $category_id == $category['category_id']) {
          $class = "active";
        }
      ?>
                <li class="<?php echo $class; ?>"><a href="<?php echo $category['href']; ?>"><?php echo $category['name']; ?></a></li>
                <?php } ?>
            </ul>
            <?php } else { ?>
            <?php for ($i = 0; $i < count($categories);) { ?>
            <ul>
                <?php $j = $i + ceil(count($categories) / 4); ?>
                <?php for (; $i < $j; $i++) { ?>
                <?php if (isset($categories[$i])) { ?>
                <?php
        $class = "";
        if(isset($category_id) && $category_id == $categories[$i]['category_id']) {
          $class = "active";
        }
      ?>
                <li class="<?php echo $class; ?>"><a href="<?php echo $categories[$i]['href']; ?>"><?php echo $categories[$i]['name']; ?></a></li>
                <?php } ?>
                <?php } ?>
            </ul>
            <?php } ?>
            <?php } ?>
        </div>

        <div class="deals-types" style="display: none;">
          <ul>
            <?php if(isset($deal_types)) {
             foreach($deal_types as $key=>$val) {
               $class_active = "";
                if(isset($status) && $key == $status) {
                  $class_active = "actived";
                }

             ?>
             <li class="<?php echo $class_active; ?>"><a href="<?php echo $val["link"]; ?>"><?php echo $val['name']; ?></a></li>
             <?php
              }
             } ?>
          </ul>
        </div>

        <div class="form" style="text-align: center; margin-bottom: 19px;">
          <select name="category_id" id="category_id" style="display: none;">
            <option value=""><?php echo $this->language->get("text_filter_by_category"); ?></option>
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
          <input type="text" style="height: 26px; margin-bottom: 0px; width: auto;" value="<?php echo isset($search)?$search:''; ?>" name="search"> <input type="button" class="button" id="button-search" value="<?php echo $this->language->get("text_search"); ?>">
        </div>

        <?php if ($products) { ?>
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

                <div class="product-compare">
                    <?php echo sprintf($this->language->get("text_total_products"), $total ); ?>
                </div>
            </div>

            <div class="product-list">
                <?php

                if ($cols == '5')
                    $span = "2-5";
                else
		            $span = floor(12/$cols);

	foreach ($products as $i => $product) { ?>
                <?php if( $i++%$cols == 0 ) { ?>
                <div class="row-fluid">
                    <?php } ?>
                    <div class="span<?php echo $span;?> product-block"><?php echo $this->getChild('module/ecdeals/deal', array($product['product_id'], array())); ?></div>
                    <?php if( $i%$cols == 0 || $i==count($products) ) { ?>
                </div>
                <?php } ?>

                <?php } ?>
            </div>

            <div class="product-filter">
                <div class="pagination"><?php echo $pagination; ?></div>
            </div>
        <?php } ?>

        <?php echo $content_bottom; ?>
    </div>
</div>

  <script type="text/javascript"><!--
$('#content input[name=\'search\']').keydown(function(e) {
  if (e.keyCode == 13) {
    $('#button-search').trigger('click');
  }
});

$('#button-search').bind('click', function() {
  url = 'index.php?route=ecdeals/sales';
  
  var category_id = $('#content select[name=\'category_id\']').attr('value');
  
  if (category_id) {
    url += '&category_id=' + encodeURIComponent(category_id);
  }

  var search = $('#content input[name=\'search\']').attr('value');
  
  if (search) {
    url += '&search=' + encodeURIComponent(search);
  }
  <?php if (isset($status) && $status) { ?>
    url += "<?php echo '&status='.$status; ?>";
  <?php } ?>

  
  location = url;
});
//--></script>

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

                var rating = $(element).find('.rating').html();
                html += '<div class="rating">';
                if (rating != null) {
                    html += rating;
                }
                html += '</div>';

                html += '  <div class="description"><p>' + $(element).find('.description').html() + '</p></div>';

                html += '  <div class="pav-action clearfix"><div class="cart">' + $(element).find('.cart').html() + '</div>';
                html += '  <div class="wishlist">' + $(element).find('.wishlist').html() + '</div>';
                html += '  <div class="compare">' + $(element).find('.compare').html() + '</div></div>';
                html += '</div></div></div>';

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
                    html += '<div class="pav-product-grid"><div class="image">' + image + '</div>';
                }

                html += '<h3 class="name">' + $(element).find('.name').html() + '</h3>';
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

                html += '<div class="pav-action clearfix"><div class="cart">' + $(element).find('.cart').html() + '</div>';
                html += '<div class="wishlist">' + $(element).find('.wishlist').html() + '</div>';
                html += '<div class="compare">' + $(element).find('.compare').html() + '</div></div></div>';

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

<?php echo $footer; ?>