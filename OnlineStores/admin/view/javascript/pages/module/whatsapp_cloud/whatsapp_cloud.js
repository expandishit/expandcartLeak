$(function () {
    // select2 initialize
    $('.select').select2();

    // tooltip initialize
    $('[data-toggle="tooltip"]').tooltip();


});

//main status change 
$('.update-config-status').on('change', function (e) {
	
	var $this 		 = $(this);	
	var config_name  = this.getAttribute("config-name");
	var config_value = $this.is(":checked") ? 1 : 0;
	
	var formData	 = new FormData();
	formData.append("config_name", config_name);
	formData.append("config_value", config_value);

	updateConfigStatus(formData);

});

function updateConfigStatus (data) {
	
	var result = [];
	
	$.ajax({
		url			: links['update_status']??"",
		data		: data,
		dataType	: 'JSON',
		method		: 'POST',
		processData	: false,
		contentType	: false,
		success		: function (response) {
						if (response.success){
							self.notify('success', 'success', 'status changed successfully');
						}else{
							self.notify('error', 'error', 'something went wrong!');
						}
					}
	});
	
	
}

function IsJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}