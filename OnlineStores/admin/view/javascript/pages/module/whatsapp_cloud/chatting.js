
//======= helpers ==========//
goToEnd	   			= () 		 => {
setTimeout(function(){conversation_body.scrollTop(conversation_body[0].scrollHeight); }, 700);
}
filterChats  		= () 		 => {
	
	var value = search_contacts.val().toLowerCase();
	
	$("#chats_cont li").filter(function() {
		$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
}
changeChat			= ()		 => {
	$('.changeChat').click(function(){
		chat_id		= $(this).data("chat-id");
		chat_name	= $(this).data("chat-name");
		chat_name 	= chat_name != '' ? chat_name : '+' + $(this).data("chat-number");
		
		if(chat_id != "" ){
			changeCurrentChat(chat_id);
			
			
			$('.side-conversation__user-name').html(chat_name);
			loadMessages();
			goToEnd();
		}
		
	});
}
dataGrouping 		= (data)	 => {

// this gives an object with dates as keys
const groups = data.reduce((groups, message) => {
 // const date_object = new Date(parseInt(message.fb_timestamp));//we have changed this column to be timestamp instead of number 
  const date_object = new Date(message.fb_timestamp);
 var date ='';
  if(isToday(date_object)){
    date = today_string;
  }
  else if(isYesterday(date_object)){
    date = yesterday_string;
  }else { 
   date = date_object.toDateString();
  }
  if (!groups[date]) {
    groups[date] = [];
  }
  groups[date].push(message);
  return groups;
}, {});

// Edit: to add it in the array format instead
const groupArrays = Object.keys(groups).map((date) => {
  return {
    date,
    messages: groups[date]
  };
});

return groupArrays;
}
formatTime   		= (date)	 => {
	hours	= date.getHours();
	minutes	= "0" + date.getMinutes();
	seconds	= "0" + date.getSeconds();
	suffix	= hours >= 12 ? " PM" : " AM";
	hours	= ((hours + 11) % 12 + 1);
	
	return hours + ':' + minutes.substr(-2) + suffix ;
}
statusSymbol 		= (status)	 => {
	var result='';
	
	if(status == "sent"){	
		result = sent_symbol; 
	}else if(status == "delivered"){			
		result = recieved_symbol;
	}else if(status == "read"){
		result = readed_symbol;
	}else {
		//TO:DO | handle  failed 
		//TO:DO | handle deleted at chat render 
	}
	return result;
}
updateLastStatus	= (message)	 => {
	
	if(last_sent_status == "failed" ){
		$('#status_'+last_id+'_not_sent').remove();
	}
	
	if(message.fb_status == "failed"){
	  $('#'+message.key_id).prepend('<span id="status_'+message.id+'_not_sent" class="not_sent" >'+not_sent_symbol+'</span>');	
	}
	
	$('#status_'+last_id).html(statusSymbol(message.fb_status));
}
isToday				= (someDate) => {
  const today = new Date()
  
  return someDate.getDate()		== today.getDate() &&
		 someDate.getMonth()	== today.getMonth() &&
		 someDate.getFullYear() == today.getFullYear()
}
isYesterday			= (someDate) => {
  
  var yesterday = new Date();
	yesterday.setDate(yesterday.getDate() - 1);
   
  return someDate.getDate()		== yesterday.getDate() &&
		 someDate.getMonth()	== yesterday.getMonth() &&
		 someDate.getFullYear()	== yesterday.getFullYear()
}	

removeInprogressMessage	= (messages) => {
	
	messages.forEach( function (message) {
		
		if(message.from_me == 1){
			$('div.inporgress-message-sending').remove();
			return ;
		}
	});
	return false  ;
}

//====== XHR Methods  ==========//
getChats		  = () => {
	$.ajax({
			url: links['get_chats'] ,
			type: 'get',
			dataType	: 'JSON',
			success: function(data){
						var chats =  data;
						if(chats.length > 0){
							var chats_html = renderChat(chats);
							$("#chats_cont").html(chats_html);
							filterChats ();
							changeChat();
							sidebarEffects();
						}else {
							$("#chats_cont").html(noChatRender());
						}
				setTimeout(function(){getChats();}, 3000);
			}
	});
}
loadMessages	  = () => {
	oldMessages(function(new_messages){
   		
		//fo no more old messages stop requesting ajax 
		if(new_messages.length < 1 ){
			no_more_old = true;
			messages_loader.hide();
		}
		
		if(new_messages.length > 0 ){
		//loop throw new_message to create messages_html 
			var messages_html= renderMessages (dataGrouping(new_messages));
		}
		
		//last message at the init 
		if(page == 1){
			if(new_messages.length > 0 ){
			//var last_element = new_messages[new_messages.length -1 ];
			//message returned desc so the first is the last element in this batch
			var last_element = new_messages[0];
			changeLastId(parseInt(last_element.id));
			changeLastFromMe(last_element.from_me);
			
			if(last_element.from_me == 1){
					changeLastSentStatus(last_element.status);
				}
			}
			
			message_container.html(messages_html);
		}else {
			message_container.prepend(messages_html);
		}
		
		downloadEffect();
		pageInc();
	});	
}
readMessages	  = () => {
	$.ajax({
		url		:  links['read_messages'],
		type	: 'get',
		data	: {to: current_chat },
		success	: function(data){}
	});
}
refreshMessages   = () => {
	
	if(current_chat == '' || current_chat == null || current_chat == '0' || !old_messages_loaded ){
		setTimeout(function(){refreshMessages();}, 3000);
		return ;
	}
	
	$.ajax({
			url		: links['new_messages'],
			type	: 'get',
			dataType: 'JSON',
			data	: {
						to: current_chat,
						last_id : last_id
			},
			success :function(data){
					
					var new_messages =  data;
					
					if (new_messages != 'undefined'  && new_messages != null ){
						
						if(new_messages.length > 0 ){
							
							removeInprogressMessage(new_messages); //TO:DO | should un-comment this line 
		
							//var last_element = new_messages[new_messages.length -1 ];
							//message returned desc so the first is the last element in this batch
							var last_element = new_messages[0];
							
							changeLastId(parseInt(last_element.id));
							changeLastFromMe(last_element.from_me);
							
							if(last_element.from_me == 1){
								changeLastSentStatus(last_element.status);
							}
							setTimeout(function(){goToEnd();}, 1000);
								
							var messages_html  = renderMessages(dataGrouping(new_messages),true);
							message_container.append(messages_html);
							downloadEffect();
								
						}else {
							if(last_from_me && last_sent_status != 13){   //13 readed 
								//lastMessageStatus();
							}
						}
					}
					
				setTimeout(function(){refreshMessages();}, 3000);
			},
			error	: function(){}
	});
}
lastMessageStatus = () => {
	$.ajax({
			url: links['get_message'],
			type: 'get',
			data: {id : last_id },
			success: function(data){
				if(IsJsonString(data)){
					var message =  JSON.parse(data);
					if(message.fb_status != last_sent_status){
						 updateLastStatus (message);
						 last_sent_status = message.fb_status;
					}
				}
			 }
	});
	
}
oldMessages		  = (handleData) => {

  var messages = [];
  	messages_loader.hide();
  if(no_more_old){
    return [];	
  }
  
  messages_loader.show();
	load_more_inprogress=true;
	
	$.ajax({
			url		: links['get_messages'],
			type	: 'get',
			dataType: 'JSON',
			data	: {to: current_chat, page : page},
			success	: function(data){
					
					messages_loader.hide();
					handleData(data);
					load_more_inprogress = false;
					old_messages_loaded  = true ;
			},
			error: function(){
				load_more_inprogress = false;
			}
	});
	
	return messages;						
};
sendMessage		  = (chat_id,message='',media='') => {
 
	if(message == '' && media == '' )
		return false; 
	
	message_container.append(renderSentMessage(message,media));
	goToEnd();
	
	var formData = new FormData();
	formData.append('chat_id',chat_id);
	formData.append('message',message);
	
	if(media != '')
		formData.append('file', media);
	
	$('.whatsapp-file-number').hide();
	
	$.ajax({
			url			: links['send_message'],
			type 		: 'POST',
			data 		: formData,
			dataType	: 'JSON',
			processData	: false,  
			contentType	: false,  
			success		: function(data){					

			}
	});
	
	message_input.val(null);								
};
downloadMedia	  = (ele,media_id='',message_id='') => {
	
	var result	= false;
	
	$.ajax({
			url			: links['download_fb_media'],
			type 		: 'POST',
			data 		: {media_id : media_id },
			dataType	: 'JSON',
			success		: function(result){					
				

			var html = '';
			
			if(result.success == '1'){
				data   = result.data ;
				
				media_data = JSON.parse(data.media) ; 
				
				if(data.url != null && data.url != ''){
					if(media_data.type == 'image'){
						html += `<img src="${media_path}/${data.url}"  class="rounded" style="width:250px;border-radius: 10px;margin-bottom: 5px;" >`;
					} else if(media_data.type == 'audio') {
						html += `<audio controls>
						  <source src="${media_path}/${data.url}" type="audio/ogg">
						</audio>`;
					} else {
							html += `<a class="conversation-body__msg-file" target="_blank" href="${links["download_media"]}?id=${data.media_id}" >
							<i class="far fa-file"></i>
							<span class="conversation-body__msg-file-name"> ${data.name} </span>
						</a>`;
					}
				
					ele.parent().html(html);
				}
			}else{
				self.notify('error!', 'error', "something went wrong");
				ele.find(".download-file-content").html('<span class="conversation-body__msg-file-name"> download </span>');
				
			}
		}
	});	
}
getProfileData	  = () => {

profile_image_loader.show();

	$.ajax({
			url		: links['get_profile'],
			type	: 'get',
			dataType: 'JSON',
			success	: function(result){
				if(result.success){
					var data = result.data;
					
					profile_image_loader.hide();
					
					if (typeof data.address !== 'undefined') {
						$("#address").val(data.address);
					}
					if (typeof data.description !== 'undefined') {
						$("#description").val(data.description);
						$("#description_text").html(data.description);
					}
					if (typeof data.email !== 'undefined') {
						$("#email").val(data.email);
					}
					if (typeof data.vertical !== 'undefined') {
						$("#vertical").val(data.vertical);
						$("#vertical").change();
					}
					if (typeof data.profile_picture_url !== 'undefined') {
						//TO:DO | update image src 
						$("#profile_picture_url").attr("src",data.profile_picture_url);
					}
				
				}else {
					//TO:DO | handle failed/error case 
					self.notify("error","error","something went wrong!");
				}
			 }
	});
};
updateProfileData = (data) => {
	//todo show chat setting loader 
	$.ajax({
			url			: links['update_profile'],
			data		: data,
			dataType	: 'JSON',
			method		: 'POST',
			processData	: false,
			contentType	: false,
			success		: function(response){
				if (response.success) {
					self.notify('success', 'success', 'profile updated successfully');
					location.reload();
				} else {
					
					if(response.error == 'VALIDATION_ERROR'){
						
						 for (err in response.errors) {
							 console.log(err);
								var error = response.errors[err];
								$("input[name='"+err+"']").closest('.form-group').addClass('has-error');
								$("input[name='"+err+"']").closest('.form-group').find('.help-block').append(response.errors[err])
							}
					}else {
						var error = '';
						for (const resp_error of response.errors) {
							error += ' ' + resp_error.message;
						}
						self.notify('error!', 'error', error);
					}
				}
				
				$('.update-chat-settings').removeAttr("disabled").removeAttr("data-loading");
			}
	});
};

//======= render UI ==========//
renderMessages	   = (messages_groups,is_new=false) => {
	
	var all_messages_html = '';
	
	//groups are sorted DESC 
	for (let i= messages_groups.length-1 ; i >= 0  ; i--){
		
		var group			= messages_groups[i];
		var date			= group.date;
		var messages		= group.messages ;
		var messages_html	= '';

		if( (date !== today_string || !is_new )
	     || (date == today_string && is_new && !today_set ) 
		 )
		 {
			messages_html = "<div  class='conversation-body__new'><span>"+date+"</span></div>";
			if(date == today_string  && !today_set) 
				today_set = true;
			
		 }
		//message are returned DESC we should show them ASC 
		for (let j = messages.length-1 ; j >= 0 ; j--){
			
			message			= messages[j];
			messages_html  += renderMessage(message);
		}
		
		all_messages_html += messages_html;
	}
	//message_container.append(renderSentMessage("","test"));

	return all_messages_html;
}
renderSentMessage  = (message=null,media = null) => {
	
	var html = ` <div  class="conversation-body__msg  inporgress-message-sending">	
				<span  class="in_progress" >${inprogress_symbol}</span>			
				<div class="conversation-body__msg-item" title="">`;
				
	if (message != null &&  message != ''){
		html += `<span class="conversation-body__msg-text">${message}</span>`;
	}		
	if (media != null && media != ''){
		html += `<div style="width:100px;height:100px"><div class="custom-loader sending-media-loader"></div></div>`;
	}		

	status = '';
	formatted_time = '';
					
	html += '<span class="conversation-body__msg-time">'+formatted_time + status + '</span>';
	html += '</div></div>';
	
	return html;
}
renderMessage	   = (message) => {
	
	var message_type = message.type ;
	
	if(message_type == 'image'  || message_type == 'audio' || message_type == 'video'  || message_type == 'document' )
		return renderMediaMessage(message);
	
	
	var from_me			 	 = message.from_me;
	var date			 	 = convertTZ(new Date(message.fb_timestamp), merchant_timezone);
	var formatted_datetime 	 = date.toLocaleString().split('T')[0];
	var formatted_time		 = formatTime(date);
	var ele_classes		 	 = (from_me == "1")? "" : "conversation-body__msg-receiver";
	
	
	
	var html = `<div data-message-id="${message.id}" id="msg-${message.id}" class="conversation-body__msg ${ele_classes}">`;
			
	if(from_me == "1" && message.fb_status == "failed"){
		html += `<span id="status_${message.id}_not_sent" class="not_sent" >${not_sent_symbol}</span>`;
	}
						
		html +=  `<div class="conversation-body__msg-item" title="${formatted_datetime}">`;
		

	if (message.text != null ){
		html += `<span class="conversation-body__msg-text">${message.text}</span>`;
	}		
	
	if(message.template != null ){
		if(IsJsonString(message.template)){
			template = JSON.parse(message.template);
			html += `<span class="conversation-body__msg-text template-message-header">${template.HEADER}</span><br>
			<span class="conversation-body__msg-text template-message-body">${template.BODY}</span><br>
			 <span class="conversation-body__msg-text template-message-footer">${template.FOOTER}</span><br>`;
		}
	}

	status =` <span id="status_${message.id}"></span>`;
			
	if(from_me == "1"){
		status =`<span id="status_${message.id}">`+statusSymbol(message.fb_status) +`</span>`;
	}
					
	html += `<span class="conversation-body__msg-time">${formatted_time} ${status}</span></div></div>`;
	
	return html;
}
renderMediaMessage = (message) => {
	
	var from_me			 	 = message.from_me;
	var date			 	 = convertTZ(new Date(message.fb_timestamp), merchant_timezone);
	var formatted_datetime 	 = date.toLocaleString().split('T')[0];
	var formatted_time		 = formatTime(date);
	var ele_classes		 	 = (from_me == "1")? "" : "conversation-body__msg-receiver";
	var html = '';
	
	if(message.media != null &&  message.media != ''){
	
		media_data = JSON.parse(message.media) ; 

		 html	 = ` <div data-message-id=${message.id} id="msg-${message.id}" class="conversation-body__msg ${ele_classes}">`;
				
			if(from_me == "1" && message.fb_status == "failed"){
				html += `<span id="status_${message.id}_not_sent" class="not_sent" >${not_sent_symbol}</span>`;
			}
							
			html +=  `<div class="conversation-body__msg-item" title="${formatted_datetime}">`;
			
			html +=  `<div class="msg-media-element">`;
			
			if(message.url != null && message.url != ''){
				if(media_data.type == 'image'){
				
					html += `<img src="${media_path}/${message.url}"  class="rounded" style="width:250px;border-radius: 10px;margin-bottom: 5px;" >`;
				
				} else if(media_data.type == 'audio'){
					
					html += `<audio controls>
							  <source src="${media_path}/${message.url}" type="audio/ogg">
							</audio>`;
				}
				else if(media_data.type == 'video'){
					html += `<video  controls>
							  <source src="${media_path}/${message.url}" type="audio/ogg">
							</video>`;
				}
				else {
					console.log("test here",message.id);
					html += `<a class="conversation-body__msg-file" target="_blank" href="${links["download_media"]}?id=${message.media_id}" >
								<i class="far fa-file"></i>
								<span class="conversation-body__msg-file-name"> ${media_data.name} </span>
							</a>`;
				}
				
			} else {
					console.log("test here2",message.id);
					html += renderUndownloadedMedia(message.media_id,message.id);
			}	
			
		html +=  `</div >`;

		if(media_data.caption != null )
			html += `<br><span class="conversation-body__msg-text">${media_data.caption}</span>`;

		status =`<span id="status_${message.id}"></span>`;
				
		if(from_me == "1"){
			status =` <span id="status_${message.id}">` +statusSymbol(message.fb_status)+`</span>`;
		}
						
		html += `<span class="conversation-body__msg-time">${formatted_time} ${status} </span>`;
		html += `</div></div>`;
	}
	return html;
}
renderUndownloadedMedia= (media_id,message_id) => {
	return `<span class="conversation-body__msg-file download-media" data-media-id = "${media_id}" data-message-id = "${message_id}" >
							<i class="far fa-file"></i>
							<div class="download-file-content">
								<span class="conversation-body__msg-file-name"> download </span>
							</div>
						</span>`;
}
renderChat		   = (chats)   => {
	
	var timezone  = (typeof default_timezone === 'undefined') ? 'UTC' : default_timezone;
	
	var chats_html = '';
	
	for (let i = 0 ; i < chats.length ; i++){
		
		var chat 			= chats[i];
		var chat_id 		= chat.id;
		var active  		= (chat_id  == current_chat) ? 'side-users__item-active' : '';
		var profile_name 	= (chat.profile_name)? chat.profile_name : '+' + chat.phone_number;
		var last_message 	= "";
		
		var last_datetime 	= new Date(chat.last_timestamp + " "+ timezone);
			
		if(isToday(last_datetime))
			last_datetime 	= formatTime(last_datetime);
		else 
			last_datetime 	= last_datetime.toISOString().replace('-', '/').split('T')[0].replace('-', '/');
			
			if(chat.type == "text" ){
				last_message = chat.text;
			}
		
			chats_html += `<li data-chat-id= "${chat_id}" data-chat-name= "${chat.profile_name}"  data-chat-number= "${chat.phone_number}" 
							class="side-users__item chat-item changeChat ${active} ">
                        <div class="side-users-item__one">
                            <img src="view/assets/images/default-avatar.png" alt="" class="side-users-item__avatar">
                            <div class="side-users-item__info">
                                <p class="side-users-item__name">${profile_name}</p>
                                <p class="side-users-item__desc">${last_message}</p>
                            </div>
                        </div>
                        <div class="side-users-item__two">
                            <span class="side-users-item__date">${last_datetime}</span>
                            `;
			if(parseInt(chat.unread_count) > 0 )			
			chats_html += `<span class="side-users-item__unread">${chat.unread_count}</span>`;
							
			chats_html += `
                        </div>
                    </li>`;
		}
		
	return chats_html;
	
}
noChatRender	   = ()		   => {
	return `<li class="side-users__item">
                        <div class="side-users-item__one" style="margin:auto;">
                            <div class="side-users-item__info">
                                <p class="side-users-item__name">${no_contacts_text}</p>
                                <p class="side-users-item__desc"></p>
                            </div>
                        </div>
                    </li>`;
}


// (chatting page) show btn send when input typing is active

emptyTyping();

function emptyTyping() {
        var empty = false;
        $('.conversation-footer__input').each(function () {
            if ($(this).val() == '') {
                empty = true;
            }
        });
        if (empty) {
            $('.conversation-footer__btn').removeClass('conversation-footer__btn-active');
        } else {
            $('.conversation-footer__btn').addClass('conversation-footer__btn-active');
        }
};
function sidebarEffects(){
	  // (chatting page) select user to chat
    $('.chat-item').on('click', function () {
        $('.chat-item').removeClass('side-users__item-active');
        $(this).addClass('side-users__item-active');
        $('.whatsapp-chatting').addClass('whatsapp-chatting__side-conversation--open');
    });

    // (chatting page) open chat search
    $('.side-users-head__btn-search').on('click', function () {
        $('.side-users__search').slideDown();
        $('.whatsapp-input-search').focus();
        $('.whatsapp-chatting__side-users').addClass('js-search__open')
    });

    /// (chatting page) chat side bar on mobile
    $('.btn-open-users-side').on('click', function () {
        $('.whatsapp-chatting').removeClass('whatsapp-chatting__side-conversation--open');
    });

    /// (chatting page) open setting of chat
    $('.side-users-head__btn-settings').on('click', function () {
        $('.whatsapp-chatting').hide();
        $('.whatsapp-chatting-settings').css('display', 'flex');
    });

    $('.btn-back-chat').on('click', function () {
        $('.whatsapp-chatting').css('display', 'flex');;
        $('.whatsapp-chatting-settings').hide();
    });

    /// (chatting page) settings side bar on mobile
    $('.btn-open-settings').on('click', function () {
        $('.whatsapp-chatting-settings').addClass('whatsapp-chatting-settings__side-two--open');
    });

    $('.btn-back-settings').on('click', function () {
        $('.whatsapp-chatting-settings').removeClass('whatsapp-chatting-settings__side-two--open');
    });

}
function convertTZ(date, tzString) {
	//the timezone of the saved data in UTC & we convert it to the merchant timezone 
	var timezone =   (typeof default_timezone === 'undefined') ? 'UTC' : default_timezone;
	var new_date = new Date((typeof date === "string" ? new Date(date + " "+ timezone) : date+ " "+timezone).toLocaleString("en-US", {timeZone: tzString})); 
	return new_date ;
}
function downloadEffect(){
	$(".download-media").click(function(){
		
		var media_id	= $(this).data("media-id");
		var message_id	= $(this).data("message-id");
		
		$(this).find(".download-file-content").html('<div class="custom-loader"></div>');
		
	
		 downloadMedia($(this),media_id,message_id);
		
		
		
	});
}

$('.conversation-footer__input').keyup(function () {
    emptyTyping();
});

 // (chatting page) get number of selected files
$('#whatsapp-file-input').on('change', function () {
    var numFiles = $(this)[0].files.length;
    if (numFiles > 0) {
        $('.whatsapp-file-number').show().html(numFiles);
        $('.conversation-footer__btn').addClass('conversation-footer__btn-active');
    } else {
        $('.whatsapp-file-number').hide();
        $('.conversation-footer__btn').removeClass('conversation-footer__btn-active');
    }
});
