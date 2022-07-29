// JavaScript Document
$(document).ready(function(){
    $("input:checkbox, input:radio").uniform();

    $("a[class='tab-trigger']").click(function() {
        if (typeof(e) != 'undefined')
            ev = e;
        else
            ev = event;

        ev.preventDefault();

        $(".vtab-item[href='" + $(this).attr('href') + "']").click();
    })

    $('form input[type="checkbox"]').click(function(){
        var that = $(this);
        // Name : Ahmed abdelfattah hussien
        // Date : 8-1-2017
       // alert(($(this).is(':checked')));
        if($(this).is(':checked')) {
            that.parent().addClass('checked');
            $(this).attr("checked", "checked");
        }
        else {
            that.parent().removeClass('checked');
            $(this).removeAttr("checked");
        }
    })


    $('.radio').click(function(){
        var radioGroupName = $(this).find('input:radio').prop("name");
        $(this).parent().find(".checked").removeClass("checked");
        $('[name="' + radioGroupName + '"]').removeAttr( "checked" );
        $(this).find('input:radio').attr("checked", "checked");
        $(this).find("span").addClass("checked");
    })

})

function saveAndStay(){
    $.ajax( {
      type: "POST",
      url: $('#form').attr( 'action' ) + '&save',
      data: $('#form').serialize(),
	  beforeSend: function() {
		$('#form').fadeTo('slow', 0.5);
		},
	  complete: function() {
		$('#form').fadeTo('slow', 1);
		},
      success: function( response ) {
        console.log( response );
      }
    } );
}

function versionCheck(rout, placeholder, token){
	$.ajax( {
      type: "POST",
      url: 'index.php?route=' + rout + '/version_check&token=' + token,
	  dataType: 'json',
	  beforeSend: function() {
		$('#form').fadeTo('slow', 0.5);
		},
	  complete: function() {
		$('#form').fadeTo('slow', 1);
		},
      success: function( json ) {
        console.log( json );
		if(json['error']){
			$(placeholder).html('<div class="warning">' + json['error'] + '</div>')
		}
		if(json['attention']){
			$html = '';
			if(json['update']){
				 $.each(json['update'] , function(k, v) {
						$html += '<div>Version: ' +k+ '</div><div>'+ v +'</div>';
				 });
			}
			 $(placeholder).html('<div class="attention">' + json['attention'] + $html + '</div>')
		}
		if(json['success']){
			$(placeholder).html('<div class="success">' + json['success'] + '</div>')
		}
      }
	})
}