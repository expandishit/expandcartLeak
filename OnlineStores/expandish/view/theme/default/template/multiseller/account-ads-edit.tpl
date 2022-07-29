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
							<input type="text" name="languages[<?php echo $langId; ?>][ad_title]" value="<?php echo $ad['title'][$langId]['ad_title']; ?>" />
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
							<input type="text" name="ad_link" value="<?php echo $ad['link']; ?>" />
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


	<?php echo $content_bottom; ?>
</div>

<script>

    $(document).ready(function() {
		$('#language-tabs a.lang').tabs();
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
