<?php echo $header; ?>
<div id="content" class="ms-account-product">
	<?php echo $content_top; ?>
	
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>

    <h3><?=$ms_allowed_payment_methods?></h3>
    <hr>
    <form method="POST" action="<?=$form_action?>">
        <div class="row">
            <?php foreach($payment_methods as $payment_method):?>
                <div class="col-md-4">
                    <input id="<?=$payment_method['code']?>" type="checkbox" name="ms_allowed_payment_methods[]" value="<?=$payment_method['code']?>" <?=in_array($payment_method['code'], $ms_seller_allowed_payment_methods) ? 'checked' : ''?>/>
                    <label for="<?=$payment_method['code']?>"><?=$payment_method['title']?></label>
                </div>    
            <?php endforeach?>
        </div>
        <hr>
        <button class="btn btn-primary"><?=$ms_button_save?></button>
    </form>
</div>
<?php echo $footer; ?>