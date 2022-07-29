<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>

<div id="content"><?php echo $content_top; ?>
  <?php /*Show deals breadcrumb*/ ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>

  <?php /*Show deal informatin: title, image, description*/ ?>

  <?php if ($thumb || $description) { ?>
  <div class="deal-info">
    <?php if ($thumb) { ?>
    <div class="image left"><img src="<?php echo $thumb; ?>" alt="<?php echo $heading_title; ?>" /></div>
    <?php } ?>
    <?php if ($description) { ?>
    <div class="description right">
    <?php echo $description; ?>
   </div>
    <?php } ?>
    <br class="clear clr"/>

  </div>
  <?php } ?>

  <?php /*Show filter by categories, show list categories*/ ?>

  <?php if(isset($show_filter_category) && $show_filter_category) { ?>
    <h2><?php echo $text_refine; ?></h2>
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

  <?php } ?>

  <?php /*Show filter form*/ ?>

  <div class="form">
    <?php echo $this->language->get("text_deal_search"); ?><input type="text" value="<?php echo isset($search)?$search:''; ?>" name="search"> <input type="button" class="button" id="button-search" value="<?php echo $this->language->get("text_search"); ?>">
  </div>

  <?php /*Show list deals*/ ?>

  <?php if ($products) { ?>
  <div class="product-filter">
    <div class="total" style="float:left;">
      <?php echo sprintf($this->language->get("text_total_products"), $total ); ?>
    </div>
    <div class="limit"><b><?php echo $text_limit; ?></b>
      <select onchange="location = this.value;">
        <?php foreach ($limits as $limit_item) { ?>
        <?php if ($limit_item['value'] == $limit) { ?>
        <option value="<?php echo $limit_item['href']; ?>" selected="selected"><?php echo $limit_item['text']; ?></option>
        <?php } else { ?>
        <option value="<?php echo $limit_item['href']; ?>"><?php echo $limit_item['text']; ?></option>
        <?php } ?>
        <?php } ?>
      </select>
    </div>
    <div class="sort"><b><?php echo $text_sort; ?></b>
      <select onchange="location = this.value;">
        <?php foreach ($sorts as $sort_item) { ?>
        <?php if ($sort_item['value'] == $sort . '-' . $order) { ?>
        <option value="<?php echo $sort_item['href']; ?>" selected="selected"><?php echo $sort_item['text']; ?></option>
        <?php } else { ?>
        <option value="<?php echo $sort_item['href']; ?>"><?php echo $sort_item['text']; ?></option>
        <?php } ?>
        <?php } ?>
      </select>
    </div>
  </div>
  <div class="product-compare"><a href="<?php echo $compare; ?>" id="compare-total"><?php echo $text_compare; ?></a></div>
  <div id="list_product_item" class="row product-list">
    <?php
    $cols = 3;
    $span = floor(12/$cols);
    ?>
    <?php foreach ($products as $i=>$product) { ?>
    <?php if( $i++%$cols == 0 ) { ?>
      <div class="row product-row">
    <?php } ?>
      <div class="col-md-<?php echo $span;?> product-item">
        <?php
          echo $this->getChild('module/ecflashsale/ecflashsale', array($product['product_id'], array()));
        ?>
      </div>
       <?php if( $i%$cols == 0 || $i==count($products) ) { ?>
       </div>
       <?php } ?>
    <?php } ?>
  </div>
  <div class="pagination"><?php echo $pagination; ?></div>
  <?php } ?>

  <?php /*If empty deals then show the text empty*/ ?>

  <?php if (!$products) { ?>
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

$('#button-search').bind('click', function() {
  url = 'index.php?route=ecflashsale/sales';
  
  var search = $('#content input[name=\'search\']').attr('value');
  
  if (search) {
    url += '&search=' + encodeURIComponent(search);
  }
  
  location = url;
});
//--></script> 
<?php echo $footer; ?>