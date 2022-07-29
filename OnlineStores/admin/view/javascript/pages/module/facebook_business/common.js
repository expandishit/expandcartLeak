
function alertHTML(type,title,message){
		return ` <div  class="alert alert-${type} generated_alert" role="alert">
					 <strong>${title}</strong>
					 <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<p id="error_message">${message}</p>
					</div>`;
}
