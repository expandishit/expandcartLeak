<?php echo $header; ?>
<style>
	tr {
		height: 50px;
	}
</style>
<script>
    var langCode = '<?php echo $direction == "rtl" ? "ar" : "en"; ?>';
</script>

<div id="content" class="ms-account-product-form">
	<?php echo $content_top; ?>

	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>

	<h1><?php echo $heading; ?></h1>

	<div class="alert alert-success success-add hidden" role="alert">
		<?php echo $ms_account_ad_added; ?>
	</div>
	<div class="alert alert-danger general-err hidden" role="alert">
	</div>

	<p class="warning main"></p>
	<form id="ms-new-ad" method="post" enctype="multipart/form-data" action="<?php echo $form_action; ?>">
		<input type="hidden" name="ad_id" value="<?php echo $ad['id']; ?>" />

		<div class="content">

     	<!-- general tab -->
     	<div id="tab-general">
     		<?php if (count($languages) > 1) { ?>
			<div class="htabs" id="language-tabs" style="display: inline-block;margin-bottom:30px;">
				<?php foreach ($languages as $language) { ?>
				<a class="lang" href="#language<?php echo $language['language_id']; ?>"><img src="admin/view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
				<?php } ?>
			</div>
			<?php } ?>

			<?php
			reset($languages); $first = key($languages);
			foreach ($languages as $k => $language) {
				$langId = $language['language_id'];
				?>

				<div class="ms-language-div" id="language<?php echo $langId; ?>" style="border: none!important">
				<table class="ms-ad">

					<tr>
						<td style="max-width: 5.2rem;"><span class="required">*</span> <?php echo $ms_account_ad_title; ?></td>
						<td>
							<input type="text" name="languages[<?php echo $langId; ?>][ad_title]" value="<?php echo $ad['languages'][$langId]['ad_title']; ?>" />
							<p class="error" id="error_ad_title<?php echo $langId; ?>"></p>
						</td>
                    </tr>
                    
				</table>
				</div>
            <?php } ?>
            <table class="ms-ad">
					<tr>
						<td><span class="required">*</span> <?php echo $ms_account_ad_link; ?></td>
						<td>
							<input type="text" name="ad_link" value="<?php echo $ad['ad_link']; ?>" />
							<p class="error" id="error_ad_link"></p>
						</td>
					</tr>
                    <tr>
						<td><span class="required">*</span> <?php echo $ms_account_ad_img; ?></td>
						<td> 
							<input type="file" name="ad_img">
							<p class="error" id="error_ad_img"></p>
						</td>
					</tr>
				<tr>
					<td><span class="required">*</span> <?php echo $ms_account_ad_package; ?></td>
					<td> 
					<select class="thumb-option-selector" style="min-width: 100%; max-width: 100%" name="ad_type">
						<option value="square">Square (<?php echo $seller_ads_settings['square_ad_display_days'] ?> days) with <?php echo $seller_ads_settings['square_ad_price'] . ' ' . $default_currency ?></option>
						<option value="banner">Banner (<?php echo $seller_ads_settings['banner_ad_display_days'] ?> days) with <?php echo $seller_ads_settings['banner_ad_price'] . ' ' . $default_currency ?></option>
					</select>
					<p class="error" id="error_ad_type"></p>
					</td>
				</tr>
				<tr>
					<td><span class="required">*</span> <?php echo $ms_account_ad_payment_method; ?></td>
					<td> 
					<select class="thumb-option-selector" id="payment_method" name="payment_method" style="min-width: 100%; max-width: 100%">
						<option value="cc"><?php echo $ms_account_credit_card; ?></option>
					</select>
					<p class="error" id="error_ad_payment"></p>
					</td>
				</tr>
				<tr id="payment_form">
					<td></td>
					<td> 
						<input type="text" name="card_name" placeholder="<?php echo $ms_account_ad_card_name; ?>" style="max-width: 50%; margin-bottom: 5px;padding-right: 5px;">
						<p class="error" id="error_ad_card_name"></p>
						<input type="text" name="card_number" placeholder="<?php echo $ms_account_ad_card_number; ?>" style="max-width: 50%; margin-bottom: 5px;padding-right: 5px;">
						<p class="error" id="error_ad_card_number"></p>
						<br>
						<input type="text" name="cvc" placeholder="CVC" style="max-width: 20%; margin-bottom: 5px;padding-right: 5px;">
						<p class="error" id="error_ad_cvc"></p>
						<br>
						<select name="expire_year" id="">
							<option value="">-- <?php echo $ms_account_ad_exp_year; ?> --</option>
							<option value="2020">2020</option>
							<option value="2021">2021</option>
							<option value="2022">2022</option>
							<option value="2023">2023</option>
							<option value="2024">2024</option>
							<option value="2025">2025</option>
							<option value="2026">2026</option>
							<option value="2027">2027</option>
							<option value="2028">2028</option>
							<option value="2029">2029</option>
							<option value="2030">2030</option>
						</select>
						<select name="expire_month" id="">
							<option value="">-- <?php echo $ms_account_ad_exp_month; ?> --</option>
							<option value="01">01</option>
							<option value="02">02</option>
							<option value="03">03</option>
							<option value="04">04</option>
							<option value="05">05</option>
							<option value="06">06</option>
							<option value="07">07</option>
							<option value="08">08</option>
							<option value="09">09</option>
							<option value="10">10</option>
							<option value="11">11</option>
							<option value="12">12</option>
						</select>
						<p class="error" id="error_ad_exp_year"></p>
						<p class="error" id="error_ad_exp_month"></p>
					</td>
				</tr>
            </table>

		</div>
		<div class="buttons" style="border: none!important">
			<div class="right">
				<a class="button" id="save_add">
					<span><?php echo $ms_button_submit; ?></span>
				</a>
			</div>
		</div>
		</div>
		</form>

		<div class="container row">
			<div class="col-md-12">
                <div class="panel panel-flat">
                    <div class="panel-body">
                        <fieldset>

                            <legend class="text-semibold">
                                <i class="fas fa-check-circle fa-lg position-left"></i>
                                <?php echo $ms_account_ad_my_ads; ?>
                            </legend>

                            <div class="collapse in" id="fields-pane2">
                                <table class="table table-striped" id="selle-ads">
                                    <thead>
                                    <tr>
                                        <th style="text-align: start;"><?php echo $ms_account_ad_title; ?></th>
                                        <th style="text-align: start;"><?php echo $ms_account_ad_link; ?></th>
                                        <th style="text-align: start;"><?php echo $ms_account_ad_type; ?></th>
                                        <th style="text-align: start;"><?php echo $ms_account_ad_status; ?></th>
                                        <th style="text-align: start;"><?php echo $ms_account_ad_start_date; ?></th>
                                        <th style="text-align: start;"><?php echo $ms_account_ad_exp_month; ?></th>
                                        <th style="text-align: start;"><?php echo $ms_account_ad_img; ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
									<?php foreach ($seller_ads as $ad){ ?>
										<tr>
											<td style="vertical-align: middle;"><a href="<?php echo $ad['edit_page'] ?>"><?php echo $ad['title'][$default_language_id]['ad_title']; ?></a></td>
											<td style="vertical-align: middle;"><?php echo $ad['link']; ?></td>
											<td style="vertical-align: middle;"><?php echo $ad['type']; ?></td>
											<td style="vertical-align: middle;"><?php echo $ad['ad_remaining_days'] >= 0 ? "<span class='label label-success'>" . $ms_account_ad_active . "</span>"  : "<span class='label label-danger'>" . $ms_account_ad_expired . "</span>" ?></td>
											<td style="vertical-align: middle;"><?php echo $ad['start_date']; ?></td>
											<td style="vertical-align: middle;"><?php echo $ad['expire_date']; ?></td>
											<td style="vertical-align: middle;"><img src="<?php echo $ad['image']; ?>" width="60" height="60" alt=""></td>
										</tr>
										<?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>
		</div>

	<?php echo $content_bottom; ?>
</div>

<script>

    $(document).ready(function() {
		$('#language-tabs a.lang').tabs();
	});
	
	$('#payment_method').on('change', function() {
		$('#payment_form').css('visibility', 'hidden');
		if ($(this).val() == 'cc') {
			$('#payment_form').css('visibility', '');
		}
	});

	$('#save_add').on('click', function(e) {
		e.preventDefault();

		$('.error').html('');
		$('.success-add').addClass('hidden');
		$('.general-err').addClass('hidden');

		let formData = new FormData();
		formData.append('ad_img', $("input[name='ad_img']")[0].files[0])
		Array.from($('form').serializeArray()).forEach(function(element) {
			formData.append(element.name, element.value);
		});

		$.ajax({
			url: $(this).closest('form').prop('action'),
			type: 'post',
			cache: false,
			contentType: false,
			processData: false,
			data: formData,
			success: function (response) {

				response = JSON.parse(response);

				if (!response.status && response.err_type == 'validation_err') {
					for (let key in response.errors) {
						$("#" + key).html(response.errors[key]);
					};
					return;
				}

				if (!response.status) {
					$('.general-err').html(response.msg);
					$('.general-err').removeClass('hidden');
					return;
				}

				$('.success-add').removeClass('hidden');

			}
		});
	});


</script>
<?php echo $footer; ?>
