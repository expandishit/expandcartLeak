<!DOCTYPE html>
<html dir="<?php echo $direction; ?>" lang="<?php echo $lang; ?>">
<head>
<meta charset="UTF-8" />
<?php if ($is_responsive_enabled):?>
<?php echo "<meta name='viewport' content='width=device-width, initial-scale=1' />"; ?>
<?php endif?>
<title><?php echo $title; ?></title>
<base href="<?php echo $base; ?>" />
<?php if ($description) { ?>
<meta name="description" content="<?php echo $description; ?>" />
<?php } ?>
<?php if ($keywords) { ?>
<meta name="keywords" content="<?php echo $keywords; ?>" />
<?php } ?>
<?php if ($icon) { ?>
<link href="<?php echo $icon; ?>" rel="icon" />
<?php } ?>
<?php foreach ($links as $link) { ?>
<link href="<?php echo $link['href']; ?>" rel="<?php echo $link['rel']; ?>" />
<?php } ?>

<link rel="stylesheet" href="catalog/view/theme/default/stylesheet/pavmegamenu/css/bootstrap.css" />

<?php if ($direction == 'rtl') { ?>
  <link href="https://fonts.googleapis.com/css?family=Cairo:200,300,400,600,700,900|Montserrat:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i&amp;subset=arabic" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="catalog/view/theme/default/stylesheet/stylesheet-a.css" />
<?php } else { ?>
<link rel="stylesheet" type="text/css" href="catalog/view/theme/default/stylesheet/stylesheet.css" />
<?php } ?>
<?php foreach ($styles as $style) { ?>
<link rel="<?php echo $style['rel']; ?>" type="text/css" href="<?php echo $style['href']; ?>" media="<?php echo $style['media']; ?>" />
<?php } ?>
<script type="text/javascript" src="catalog/view/javascript/jquery/jquery-1.7.1.min.js"></script>
<script>
/** 
 * Forward port jQuery.live()
 * Wrapper for newer jQuery.on()
 * Uses optimized selector context 
 * Only add if live() not already existing.
*/
if (typeof jQuery.fn.live == 'undefined' || !(jQuery.isFunction(jQuery.fn.live))) {
  jQuery.fn.extend({
      live: function (event, callback) {
         if (this.selector) {
              jQuery(document).on(event, this.selector, callback);
          }
      }
  });
}
jQuery.browser = {};
(function () {
    jQuery.browser.msie = false;
    jQuery.browser.version = 0;
    if (navigator.userAgent.match(/MSIE ([0-9]+)\./)) {
        jQuery.browser.msie = true;
        jQuery.browser.version = RegExp.$1;
    }
})();
</script>
<!-- <script src="catalog/view/javascript/jquery/jquery-migrate-3.3.2.js"></script> -->
<script type="text/javascript" src="catalog/view/javascript/jquery/ui/jquery-ui-1.8.16.custom.min.js"></script>
<link rel="stylesheet" type="text/css" href="catalog/view/javascript/jquery/ui/themes/ui-lightness/jquery-ui-1.8.16.custom.css" />
<!-- Select2 -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
<!-- <script type="text/javascript" src="view/assets/js/pages/form_select2.js"></script> -->
<!-- /Select2 -->
<script type="text/javascript" src="catalog/view/javascript/common.js"></script>
<?php foreach ($scripts as $script) { ?>
<script type="text/javascript" src="<?php echo $script; ?>"></script>
<?php } ?>

<!-- Inline Script:Start -->
<?php foreach($inlineScripts as $script) {
    if ($script['type'] == 'callable') echo base64_decode($script['script']);
}
?>
<!-- Inline Script:End -->
    
<script src="/expandish/view/javascript/jquery/bootstrap/bootstrap.js"></script>
<link rel="stylesheet" href="/expandish/view/javascript/jquery/bootstrap/bootstrap.min.css">

<script src="/expandish/view/javascript/jquery/bootstrap/bootstrap-multiselect.js"></script>
<link rel="stylesheet" href="/expandish/view/javascript/jquery/bootstrap/bootstrap-multiselect.css">


<!--[if IE 7]> 
<link rel="stylesheet" type="text/css" href="catalog/view/theme/default/stylesheet/ie7.css" />
<![endif]-->
<!--[if lt IE 7]>
<link rel="stylesheet" type="text/css" href="catalog/view/theme/default/stylesheet/ie6.css" />
<script type="text/javascript" src="catalog/view/javascript/DD_belatedPNG_0.0.8a-min.js"></script>
<script type="text/javascript">
DD_belatedPNG.fix('#logo img');
</script>
<![endif]-->
<?php if ($stores) { ?>
<script type="text/javascript"><!--
$(document).ready(function() {
<?php foreach ($stores as $store) { ?>
$('body').prepend('<iframe src="<?php echo $store; ?>" style="display: none;"></iframe>');
<?php } ?>
});
//--></script>
<?php } ?>
<?php echo $google_analytics; ?>

 <style>
 #logo img {
 	max-height: 60px !important;
 }
 /* handle print page issue */
 @media print {
	@page {
		size: auto;
		margin: 0;
	}
	a[href]:after {
		content: none !important;
	}

}
 </style>

</head>
<body>
<div id="container" class="container">
  <div id="header" class='row header'>
   
   <?php if ($logo) { ?>
      <div id="logo" class="col-md-5 col-sm-5 hidden-EG">
        <a href="<?php echo $home; ?>">
          <img src="<?php echo $logo; ?>" title="<?php echo $name; ?>" alt="<?php echo $name; ?>" />
        </a>
      </div>
    <?php } ?>

    <?php echo $language; ?>

      <div id="welcome" class='col-md-7 col-sm-7 text-right welcome-name'>
         <?php if (!$logged) { ?>
            <?php echo $text_welcome; ?>
               <?php } else { ?>
            <?php echo $text_logged; ?>
         <?php } ?>
      </div>
      
      <?php if ($logo) { ?>
      <div id="logo" class="col-md-5 col-sm-5 text-right hidden-AR">
        <a href="<?php echo $home; ?>">
          <img src="<?php echo $logo; ?>" title="<?php echo $name; ?>" alt="<?php echo $name; ?>" />
        </a>
      </div>
    <?php } ?>


      <div class="clear"></div>
      <div class="links text-right col-md-12">
         <a href="<?php echo $home; ?>">
            <?php echo $text_home; ?>
         </a>
          <?php if ($this->customer->isCustomerAllowedToView_products ){ ?>
         <a href="<?php echo $wishlist; ?>" id="wishlist-total">
            <?php echo $text_wishlist; ?>
         </a>
          <?php } ?>
         <a href="<?php echo $account; ?>">
            <?php echo $text_account; ?>
         </a>
          <?php if ($this->customer->isCustomerAllowedToAdd_cart && $this->customer->isCustomerAllowedToView_products ){ ?>
         <a href="<?php echo $shopping_cart; ?>">
            <?php echo $text_shopping_cart; ?>
         </a>
         <a href="<?php echo $checkout; ?>">
            <?php echo $text_checkout; ?>
         </a>
        <?php } ?>
      </div>
  </div>

<?php if ($categories) { ?>
<div id="menu">
  <ul>
    <?php foreach ($categories as $category) { ?>
    <li><a href="<?php echo $category['href']; ?>"><?php echo $category['name']; ?></a>
      <?php if ($category['children']) { ?>
      <div>
        <?php for ($i = 0; $i < count($category['children']);) { ?>
        <ul>
          <?php $j = $i + ceil(count($category['children']) / $category['column']); ?>
          <?php for (; $i < $j; $i++) { ?>
          <?php if (isset($category['children'][$i])) { ?>
          <li><a href="<?php echo $category['children'][$i]['href']; ?>"><?php echo $category['children'][$i]['name']; ?></a></li>
          <?php } ?>
          <?php } ?>
        </ul>
        <?php } ?>
      </div>
      <?php } ?>
    </li>
    <?php } ?>
  </ul>
</div>
<?php } ?>
<div id="notification"></div>
