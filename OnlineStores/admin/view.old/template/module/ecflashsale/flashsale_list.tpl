<?php echo $header; ?>
<div id="content">
    <ol class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php if ($breadcrumb === end($breadcrumbs)) { ?>
        <li class="active">
            <?php } else { ?>
        <li>
            <?php } ?>
            <a href="<?php echo $breadcrumb['href']; ?>">
                <?php if ($breadcrumb === reset($breadcrumbs)) { ?>
                <?php echo $breadcrumb['text']; ?>
                <?php } else { ?>
                <span><?php echo $breadcrumb['text']; ?></span>
                <?php } ?>
            </a>
        </li>
        <?php } ?>
    </ol>

    <?php if ($error_warning) { ?>
    <script>
        var notificationString = '<?php echo $error_warning; ?>';
        var notificationType = 'warning';
    </script>
    <?php } ?>

    <?php if ($success) { ?>
    <script>
        var notificationString = '<?php echo $success; ?>';
        var notificationType = 'success';
    </script>
    <?php } ?>

  <div class="box">
    <div class="heading">
      <h1><?php echo $heading_title; ?></h1>
       <div class="buttons"><a href="javascript:;" rel="<?php echo $featured_link; ?>" onclick="formAction(this)" class="button btn btn-primary"><?php echo $this->language->get("button_featured"); ?></a><a href="javascript:;" rel="<?php echo $unfeatured_link_link; ?>" onclick="formAction(this)" class="button btn btn-primary"><?php echo $this->language->get("text_unfeatured"); ?></a><a href="javascript:;" rel="<?php echo $enable_link; ?>" onclick="formAction(this)" class="button btn btn-primary"><?php echo $this->language->get("text_enabled"); ?></a><a href="javascript:;" rel="<?php echo $disable_link; ?>" onclick="formAction(this)" class="button btn btn-primary"><?php echo $this->language->get("text_disabled"); ?></a><a href="<?php echo $insert; ?>" class="button btn btn-primary"><?php echo $this->language->get("button_insert"); ?></a><a onclick="$('form').submit();" class="button btn btn-primary"><?php echo $button_delete; ?></a></div>
    </div>
    <div class="content">
     <div style="display: none;"> <label for="filter_store"><?php echo $this->language->get("text_filter_by_store");?></label> <select name="filter_store" id="filter_store" onchange="location = this.value;">
              <?php foreach($stores as $store):?>
              <?php if($store['store_id'] == $filter_store):?>
                <option value="<?php echo $store['option'];?>" selected="selected"><?php echo $store['name'];?></option>
              <?php else:?>
                <option value="<?php echo $store['option'];?>"><?php echo $store['name'];?></option>
              <?php endif;?>
              <?php endforeach;?>
            </select>
      </div>
      <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="table table-hover dataTable no-footer">
          <thead>
            <tr>
              <td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).is(':checked'));" /></td>
               <td class="left"><?php echo $this->language->get("text_image"); ?></td>
              <td class="left"><?php if ($sort == 'fd.name') { ?>
                <a href="<?php echo $sort_name; ?>" class="<?php echo strtolower($order); ?>"><?php echo $this->language->get("text_flashsale_name"); ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_name; ?>"><?php echo $this->language->get("text_flashsale_name"); ?></a>
                <?php } ?></td>
              <td class="left"><?php if ($sort == 'date_start') { ?>
                <a href="<?php echo $sort_date_start; ?>" class="<?php echo strtolower($order); ?>"><?php echo $this->language->get("text_date_start"); ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_date_start; ?>"><?php echo $this->language->get("text_date_start"); ?></a>
                <?php } ?></td>
              <td class="left"><?php if ($sort == 'date_end') { ?>
                <a href="<?php echo $sort_date_end; ?>" class="<?php echo strtolower($order); ?>"><?php echo $this->language->get("text_date_end"); ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_date_end; ?>"><?php echo $this->language->get("text_date_end"); ?></a>
                <?php } ?></td>
              <td class="left"><?php if ($sort == 'source_from') { ?>
                <a href="<?php echo $sort_type; ?>" class="<?php echo strtolower($order); ?>"><?php echo $this->language->get("text_type"); ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_type; ?>"><?php echo $this->language->get("text_type"); ?></a>
                <?php } ?></td>
              <td class="left"><?php if ($sort == 'featured') { ?>
                <a href="<?php echo $sort_featured; ?>" class="<?php echo strtolower($order); ?>"><?php echo $this->language->get("text_featured"); ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_featured; ?>"><?php echo $this->language->get("text_featured"); ?></a>
                <?php } ?></td>
              <td class="left"><?php if ($sort == 'status') { ?>
                <a href="<?php echo $sort_status; ?>" class="<?php echo strtolower($order); ?>"><?php echo $this->language->get("text_status"); ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_status; ?>"><?php echo $this->language->get("text_status"); ?></a>
                <?php } ?></td>
                <td class="left"><?php if ($sort == 'hits') { ?>
                <a href="<?php echo $sort_hits; ?>" class="<?php echo strtolower($order); ?>"><?php echo $this->language->get("text_hits"); ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_hits; ?>"><?php echo $this->language->get("text_hits"); ?></a>
                <?php } ?></td>
                <td><?php echo $this->language->get("text_action");?></td>
            </tr>
          </thead>
          <tbody>
            <tr class="filter">
              <td></td>
              <td></td>
              <td><input type="text" name="filter_name" value="<?php echo $filter_name; ?>" /></td>
              <td></td>
              <td></td>
              <td><select name="filter_type">
                  <option value="*"></option>
                  <?php if ($filter_type == 'category') { ?>
                  <option value="category" selected="selected"><?php echo $this->language->get("text_type_category"); ?></option>
                  <?php } else { ?>
                   <option value="category"><?php echo $this->language->get("text_type_category"); ?></option>
                  <?php } ?>
                  <?php if (!is_null($filter_type) && $filter_type == 'product') { ?>
                  <option value="product" selected="selected"><?php echo $this->language->get("text_type_product"); ?></option>
                  <?php } else { ?>
                  <option value="product"><?php echo $this->language->get("text_type_product"); ?></option>
                  <?php } ?>
                </select></td>
              <td><select name="filter_featured">
                  <option value="*"></option>
                  <?php if ($filter_featured) { ?>
                  <option value="1" selected="selected"><?php echo $this->language->get("text_featured"); ?></option>
                  <?php } else { ?>
                   <option value="1"><?php echo $this->language->get("text_featured"); ?></option>
                  <?php } ?>
                  <?php if (!is_null($filter_featured) && !$filter_featured) { ?>
                  <option value="0" selected="selected"><?php echo $this->language->get("text_non_featured"); ?></option>
                  <?php } else { ?>
                  <option value="0"><?php echo $this->language->get("text_non_featured"); ?></option>
                  <?php } ?>
                </select></td>
              <td><select name="filter_status">
                  <option value="*"></option>
                  <?php if ($filter_status) { ?>
                  <option value="1" selected="selected"><?php echo $this->language->get("text_enabled"); ?></option>
                  <?php } else { ?>
                   <option value="1"><?php echo $this->language->get("text_enabled"); ?></option>
                  <?php } ?>
                  <?php if (!is_null($filter_status) && !$filter_status) { ?>
                  <option value="0" selected="selected"><?php echo $this->language->get("text_disabled"); ?></option>
                  <?php } else { ?>
                  <option value="0"><?php echo $this->language->get("text_disabled"); ?></option>
                  <?php } ?>
                </select></td>
              <td></td>
              <td align="right"><a onclick="filter();" class="button btn btn-primary"><?php echo $button_filter; ?></a></td>
            </tr>
            <?php if (isset($flashsales) && $flashsales) { ?>
            <?php foreach ($flashsales as $flashsale) { ?>
            <tr>
              <td style="text-align: center;"><?php if ($flashsale['selected']) { ?>
                <input type="checkbox" name="selected[]" value="<?php echo $flashsale['ecflashsale_id']; ?>" checked="checked" />
                <?php } else { ?>
                <input type="checkbox" name="selected[]" value="<?php echo $flashsale['ecflashsale_id']; ?>" />
                <?php } ?></td>
              <td class="left"><img src="<?php echo $flashsale['image']; ?>" alt="<?php echo $flashsale['name']; ?>" style="padding: 1px; border: 1px solid #DDDDDD;" /></td>
              <td class="left"><a href="<?php echo $flashsale['link'];?>"><?php echo $flashsale['name']; ?></a></td>
              <td class="left"><?php echo $flashsale['date_start']; ?></td>
              <td class="left"><?php echo $flashsale['date_end']; ?></td>
              <td class="left"><strong><?php echo $flashsale['source_from']; ?></strong></td>
              <td class="left"><?php echo ($flashsale['featured']?'<span class="featured">'.$flashsale['featured'].'</span>':''); ?></td>
              <td class="left"><strong><?php echo $flashsale['status']; ?></strong></td>
              <td class="left"><?php echo $flashsale['hits']; ?></td>
              <td class="right"><?php foreach ($flashsale['action'] as $action) { ?>
                [ <a href="<?php echo $action['href']; ?>"><?php echo $action['text']; ?></a> ]
                <?php } ?></td>
            </tr>
            <?php } ?>
            <?php } else { ?>
            <tr>
              <td class="center" colspan="10"><?php echo $text_no_results; ?></td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </form>
      <div class="pagination"><?php echo $pagination; ?></div>
    </div>
  </div>
</div>
<script type="text/javascript"><!--
$(function() {

    // grab the initial top offset of the navigation 
    var sticky_navigation_offset_top = $('#sticky_navigation').offset().top;
    
    // our function that decides weather the navigation bar should have "fixed" css position or not.
    var sticky_navigation = function(){
        var scroll_top = $(window).scrollTop(); // our current vertical position from the top
        
        // if we've scrolled more than the navigation, change its position to fixed to stick to top,
        // otherwise change it back to relative
        if (scroll_top > sticky_navigation_offset_top) {
            $('#sticky_navigation').css({ 'position': 'fixed', 'top':0, 'left':0, 'width': '100%' });
        } else {
            $('#sticky_navigation').css({ 'position': 'relative', 'width': '98.8%' }); 
        }   
    };
    
    // run our function on load
    sticky_navigation();
    
    // and run it again every time you scroll
    $(window).scroll(function() {
         sticky_navigation();
    });

});
function resetCount(obj){
  $('form').attr("action", $(obj).attr("rel"));
  $('form').submit();
}

function formAction(obj){
  var count_checked = false;
  $(".table.table-hover.dataTable.no-footer").find(":checkbox").each(function(el){
     if($(this).is(':checked')){
      count_checked = true;
     }
  })
  if(count_checked){
    url = $(obj).attr("rel");
    url += '&'+$('#form').serialize();
    location = url;
  }else{
    alert("<?php echo $this->language->get("text_please_select_a_item");?>")
  }
  
}
function filter() {
  url = 'index.php?route=module/ecflashsale/flashsales&token=<?php echo $token; ?>';
  
  var filter_name = $('input[name=\'filter_name\']').attr('value');
  
  if (filter_name) {
    url += '&filter_name=' + encodeURIComponent(filter_name);
  }
  
  var filter_type = $('select[name=\'filter_type\']').attr('value');
  
  if (filter_type != '*') {
    url += '&filter_type=' + encodeURIComponent(filter_type);
  }

  var filter_featured = $('select[name=\'filter_featured\']').attr('value');
  
  if (filter_featured != '*') {
    url += '&filter_featured=' + encodeURIComponent(filter_featured);
  }

  var filter_status = $('select[name=\'filter_status\']').attr('value');
  
  if (filter_status != '*') {
    url += '&filter_status=' + encodeURIComponent(filter_status);
  } 

  location = url;
}
//--></script> 
<script type="text/javascript"><!--

$('#form .filter input').keydown(function(e) {
  if (e.keyCode == 13) {
    filter();
  }
});
//--></script>
<?php echo $footer; ?>