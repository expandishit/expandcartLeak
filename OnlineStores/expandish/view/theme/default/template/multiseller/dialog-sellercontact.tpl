<div id="ms-sellercontact-dialog-div" title="{{ ms_sellercontact_title }}>">
	<div class="ms-sellercontact-image">
    {% if seller_thumb is defined and seller_thumb != '' %}
	<a href="{{ seller_href }}"><img src="{{ seller_thumb }}" /></a>
    {% endif %}
	<h3>{{ ms_sellercontact_sendmessage }}</h3>
	</div>

	<div class="ms-form">
	<form class="dialog">
		{% if msconf_enable_private_messaging == 1 %}
			<label for="ms-sellercontact-name">{{ms_sellercontact_name}}</label>
			<input type="text" name="ms-sellercontact-name" id="ms-sellercontact-name" value="{{customer_name}}"></input>
				
			<label for="ms-sellercontact-email">{{ms_sellercontact_email}}</label>
			<input type="text" name="ms-sellercontact-email" id="ms-sellercontact-email" value="{{customer_email}}"></input>
		{% endif %}

		<label for="ms-sellercontact-text">{{ms_sellercontact_text}}</label>
		<textarea rows="3" name="ms-sellercontact-text" id="ms-sellercontact-text"></textarea>
		
		<label for="ms-sellercontact-captcha">{{ms_sellercontact_captcha}}</label>
		<img src="index.php?route=common/captcha" id="ms-captcha" style="vertical-align:top; margin: 5px 0" />
		<input type="text" name="ms-sellercontact-captcha" id="ms-sellercontact-captcha" style="height: 25px; width:100px"></input>
			
		<input type="hidden" name="seller_id" value="{{seller_id}}" />
		<input type="hidden" name="product_id" value="{{product_id}}" />
	</form>
	</div>
</div>		
