 //// handel checkboxes on bot page
$( document ).ready(function() { 
	
	// this line fix collapse performance issue at first collapse open 
	$('.panel-default-border').removeClass('panel-open');
 
 // (bot page) add class on parent when change collapses
 
    $('.panel-collapse').on('show.bs.collapse', function () {
        $(this).parent('.panel-default-border').addClass('panel-open');
    });

    $('.panel-collapse').on('hide.bs.collapse', function () {
        $(this).parent('.panel-default-border').removeClass('panel-open');
    });


	if($('.customer-check:checkbox').first().is(':checked')) {
		$('.customer-check-holder').slideDown();
	}else {
		$('.customer-check-holder').slideUp();
	} 

	if($('.owner-check:checkbox').is(':checked')) {
		$('.owner-check-holder').slideDown();
	}else {
		$('.owner-check-holder').slideUp();
	}
	
	if($('.seller-check:checkbox').is(':checked')) {
		$('.seller-check-holder').slideDown();
	}else {
		$('.seller-check-holder').slideUp();
	}
});
	
$('.customer-check').on('change', function () {
	var $this = $(this);
    
	if($this.is(":checked")) {
        $('.customer-check-holder').slideDown();
    }else {
        $('.customer-check-holder').slideUp();
    }
});

$('.owner-check').on('change', function () {
	var $this = $(this);
    
	if($this.is(":checked")) {
        $('.owner-check-holder').slideDown();
    }else {
        $('.owner-check-holder').slideUp();
    }
});

$('.seller-check').on('change', function () {
	var $this = $(this);
    
	if($this.is(":checked")) {
        $('.seller-check-holder').slideDown();
    }else {
        $('.seller-check-holder').slideUp();
    }
});


// ========== XHR Methods calls =================//
updateDeafultTemplate = (data) => {

	$.ajax({
		url			: links['update_default_template'] ??"",
		data		: data,
		dataType	: 'JSON',
		method		: 'POST',
		processData	: false,
		contentType	: false,
		success		: function (response) {

			if (response.success) {
				self.notify('success', 'success', 'template updated successfully');
				location.reload();
			} else {
				var error = '';
				for (const resp_error of response.errors) {
					error += ' ' + resp_error.message;
				}
				self.notify('error!', 'error', error);
			}

			$('.update-default-template').removeAttr("disabled").removeAttr("data-loading");
		}
	});

	return;
}

updateCustomTemplate  = (data) => {

	$.ajax({
		url			: links['update_custom_template']??"",
		data		: data,
		dataType	: 'JSON',
		method		: 'POST',
		processData	: false,
		contentType	: false,
		success		: function (response) {

			if (response.success) {
				self.notify('success', 'success', 'template updated successfully');
				location.reload();
			} else {
				var error = '';
				for (const resp_error of response.errors) {
					error += ' ' + resp_error.message;
				}
				self.notify('error!', 'error', error);
			}

			$('.update-custom-template').removeAttr("disabled").removeAttr("data-loading");
		}
	});
}

deleteCustomTemplate  = (data) => {

	$.ajax({
		url			: links['delete_custom_template']??"",
		data		: data,
		dataType	: 'JSON',
		method		: 'POST',
		processData	: false,
		contentType	: false,
		success		: function (response) {

			if (response.success) {
				self.notify('success', 'success', 'template deleted successfully');
				location.reload();
			} else {
				var error = '';
				for (const resp_error of response.errors) {
					error += ' ' + resp_error.message;
				}
				self.notify('error!', 'error', error);
			}

			$('.delete-custom-template').removeAttr("disabled").removeAttr("data-loading");
		}
	});
}
