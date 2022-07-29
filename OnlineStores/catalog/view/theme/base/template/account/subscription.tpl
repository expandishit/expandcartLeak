<?php echo $header; ?>

<?php if ($success) { ?>

<div class="success"><?php echo $success; ?><img src="catalog/view/theme/default/image/close.png" alt="" class="close" /></div>

<?php } ?>

<?php echo $column_left; ?><?php echo $column_right; ?>

<div id="content"><?php echo $content_top; ?>

  <div class="breadcrumb">

    <?php foreach ($breadcrumbs as $breadcrumb) { ?>

    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>

    <?php } ?>

  </div>

  <h1><?php echo $heading_title; ?></h1>

  <?php if ($subscriptions) { ?>

  <div class="wishlist-info">

    <table>

      

      <?php foreach ($subscriptions as $subscription) { ?>

      <tbody id="wishlist-row<?php echo $subscription['subscription_id']; ?>">

        <tr>

          <td class="image"><?php if ($subscription['image']) { ?>

            <a href="<?php echo $subscription['href']; ?>"><img src="<?php echo $subscription['image']; ?>" alt="<?php echo $subscription['name']; ?>" title="<?php echo $subscription['name']; ?>" /></a>

            <?php } ?></td>

          <td class="name"><a href="<?php echo $subscription['href']; ?>"><?php echo $subscription['name']; ?></a><br/>

          
 <?php echo substr($subscription['description'],0,250); ?></td>
            

          <td class="action">
		  <a id="button-unsub<?php echo $subscription['subscription_id']; ?>">
		  <img src="catalog/view/theme/default/image/remove.png" alt="<?php echo $button_remove; ?>" title="<?php echo $button_remove; ?>" /></a></td>
		  
		  

        </tr>

      </tbody>
	  
	  
	  <div id="button-unsub"></div>
		  
		  <script>
		  
		  $('#button-unsub<?php echo $subscription['subscription_id']; ?>').bind('click', function() {
$.ajax({
	url: 'index.php?route=account/subscription/unsubscription&product_id=<?php echo $subscription['product_id']; ?>',
	type: 'post',
	dataType: 'json',
      success: function(data) {
	  
	       $('.success, .warning, .attention, .abc, .error').remove();
		   
			
			if (data['success']) {			
					
				$('#button-unsub').after('<div style="color:green" class="abc">' + data['success'] + '</div>');
				
				window.location.reload(true);
				
				
				
											
			}
		}
	});
});
		  </script>

      <?php } ?>

    </table>

  </div>

  <div class="buttons">

    <div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>

  </div>

  <?php } else { ?>

  <div class="content"><?php echo $text_empty; ?></div>

  <div class="buttons">

    <div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>

  </div>

  <?php } ?>

  <?php echo $content_bottom; ?></div>

<?php echo $footer; ?>