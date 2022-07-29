<?php $themeName = $this->config->get('config_template') ; ?>
<div id="cart" class="pull-right"> 
    <div class="heading">
        <div class="pull-left"><i class="icon-cart is-round is-sprite">&nbsp;</i></div>
        <div class="pav-cart-title pull-left">
            <h3 class="f-normal"><?php echo $heading_title; ?></h3>
            <a><span id="cart-total"><?php echo $text_items; ?></span></a>
        </div>
    </div>
    <div class="content">
        <?php if ($products || $vouchers) { ?>
        <div class="mini-cart-info">
            <table class="table no-border">
                <?php foreach ($products as $product) { ?>
                <tr>
                    <td class="image">
						<div class="cart-image">
							<?php if ($product['thumb']) { ?>
							<a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" title="<?php echo $product['name']; ?>" /></a>
							<?php } ?>
							
							<a class="remove">
								<img src="catalog/view/theme/<?php echo $themeName;?>/image/remove-small.png" alt="<?php echo $button_remove; ?>" title="<?php echo $button_remove; ?>" onclick="(getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') ? location = 'index.php?route=checkout/cart&remove=<?php echo $product['key']; ?>' : $('#cart').load('index.php?route=module/cart&remove=<?php echo $product['key']; ?>' + ' #cart > *');" />
							</a>
						</div>
                    </td>
                    <td class="name">
                        <div class="cart-name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></div>					
						
                        <div class="mini-cart-content">
                            <?php foreach ($product['option'] as $option) { ?>
                            - <small><?php echo $option['name']; ?> <?php echo $option['value']; ?></small><br />
                            <?php } ?>
                        </div>
						
						<div class="quantity">
							x&nbsp;<?php echo $product['quantity']; ?>
						</div>
						
						<div class="total price">
							<?php echo $product['total']; ?>
						</div>
                    </td>
                </tr>
                <?php } ?>
                <?php foreach ($vouchers as $voucher) { ?>
                <tr>
                    <td class="image"></td>
                    <td class="name"><?php echo $voucher['description']; ?></td>
                    <td class="quantity">x&nbsp;1</td>
                    <td class="total"><?php echo $voucher['amount']; ?></td>
                    <td class="remove">
                        <img src="catalog/view/theme/<?php echo $themeName;?>/image/remove-small.png" alt="<?php echo $button_remove; ?>" title="<?php echo $button_remove; ?>" onclick="(getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') ? location = 'index.php?route=checkout/cart&remove=<?php echo $voucher['key']; ?>' : $('#cart').load('index.php?route=module/cart&remove=<?php echo $voucher['key']; ?>' + ' #cart > *');" />
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
        <div class="mini-cart-total">
            <table class="table no-border">
                <?php foreach ($totals as $total) { ?>
                <tr>
                    <td class="right"><b><?php echo $total['title']; ?>:</b></td>
                    <td class="right"><?php echo $total['text']; ?></td>
                </tr>
                <?php } ?>
            </table>
        </div>
        <div class="checkout">
            <a href="<?php echo $cart; ?>" class="button"><?php echo $text_cart; ?></a>
            <a href="<?php echo $checkout; ?>" class="button"><?php echo $text_checkout; ?></a>
        </div>
        <?php } else { ?>
        <div class="empty"><?php echo $text_empty; ?></div>
        <?php } ?>
    </div>
</div>