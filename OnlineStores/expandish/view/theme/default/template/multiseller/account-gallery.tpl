<?php echo $header; ?>

<div id="content" class="ms-account-profile row">
	<?php echo $content_top; ?>
	
	<div class="breadcrumb col-md-12">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?>
		<a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>
	
	<h2 class="heading-profile"> <?php echo $ms_account_sellergallery_breadcrumbs; ?></h2>
	<p> <?php echo $ms_account_sellergallery_ratio; ?></p>
	<?php if (isset($success) && ($success)) { ?>
		<div class="success"><?php echo $success; ?></div>
	<?php } ?>
	
	<?php if (isset($statustext) && ($statustext)) { ?>
		<div class="<?php echo $statusclass; ?>"><?php echo $statustext; ?></div>
	<?php } ?>

	<p class="warning main"></p>
	
	<form enctype="multipart/form-data">
        <div class="form-group">
            <div class="file-loading">
                <label>Preview File Icon</label>
                <input id="file-3" type="file" multiple>
            </div>
        </div>
    </form>

    
    <div class="container mt-5">
        <div class="added-videos">
            <div class="video-collection">

            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card-body">
                <div class="card">
                    <h1 class="text-center my-2">Add video</h1>

                        <form id="video-form">
                            <div class="row">
                            <div class="col-md-10">
                                <input class="form-control" type="text" name="videoURL" id="video-ins">
                            </div>
                            
                            <div class="col-md-2">
                                <input type="submit" value="add video" class="btn btn-primary btn-block">
                            </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card mt-3" id="listCard">
                    <ul class="list-group list-group-flush collection"></ul>
                </div>
            </div>

    </div>
    </div>
</div>



<?php echo $content_bottom; ?>
<?php $timestamp = time(); ?>
<script>
    $("#file-3").fileinput({
        theme: 'fas',
        uploadUrl: $('base').attr('href') + 'index.php?route=seller/account-gallery/jxUploadImage',
        deleteUrl: $('base').attr('href') + 'index.php?route=seller/account-gallery/jxDeleteImage',
        allowedFileExtensions: ['jpg', 'png', 'gif'],
        //showUpload: false,
        //showCaption: false,
        //browseClass: "btn btn-primary btn-lg",
        allowedFileTypes: ['image'],
        showCancel: true,
        previewFileIcon: "<i class='glyphicon glyphicon-king'></i>",
        overwriteInitial: false,
        initialPreviewAsData: true,
        initialPreview: [
            <?php echo $seller_images ?>
        ],
        initialPreviewConfig: [
            <?php echo $seller_captions ?>
        ]
    }).on('fileuploaded', function(event, previewId, index, fileId) {
        console.log('File Uploaded', 'ID: ' + fileId + ', Thumb ID: ' + previewId);
    }).on('fileuploaderror', function(event, data, msg) {
        console.log('File Upload Error', 'ID: ' + data.fileId + ', Thumb ID: ' + data.previewId);
    }).on('filebatchuploadcomplete', function(event, preview, config, tags, extraData) {
        console.log('File Batch Uploaded', preview, config, tags, extraData);
    });

    
const form = document.querySelector('#video-form');
const videoInput = document.querySelector('#video-ins');
const thumContainer = document.getElementsByClassName('added-videos');
const youtubeRegEx = /^((?:https?:)?\/\/)?((?:www|m)\.)?((?:youtube\.com|youtu.be))(\/(?:[\w\-]+\?v=|embed\/|v\/)?)([\w\-]+)(\S+)?$/

loadEvent();

function loadEvent() {
    document.addEventListener('DOMContentLoaded', getvidz)
    form.addEventListener('submit', addvideo);
    removeItem();
}
let DataArray = []

function getvidz(){
    $.ajax({
        type: 'GET',
        url:  $('base').attr('href') + 'index.php?route=seller/account-gallery/jxGetSellerVideos',
        success: function(videos){
            let vidz = JSON.parse(videos)
            vidz.forEach(function(id) {
    
                let videoItem = document.createElement('div')
                videoItem.className = 'video-container'
                videoItem.innerHTML = 
                ` <img src="https://img.youtube.com/vi/${id}/hqdefault.jpg" class="video-prev" alt="video thumb">
                    <div class="card-body">
                        <a id=${id} class="videoIds btn btn-danger del">Delete Video</a>
                    </div>`;
                document.querySelector('.video-collection').appendChild(videoItem)
             })
        },
        error: function(err){
            console.log("ERROR",err)
        },
        fail : function(fail){
            console.log("FAIL",fail)
        }
    })

    
}

function YouTubeGetID(url){
    var ID = '';
    url = url.replace(/(>|<)/gi,'').split(/(vi\/|v=|\/v\/|youtu\.be\/|\/embed\/)/);
    if(url[2] !== undefined) {
      ID = url[2].split(/[^0-9a-z_\-]/i);
      ID = ID[0];
    }
    else {
      ID = url;
    }
      return ID;
  }

  function SubmitRenderedVideos() {
      console.log("in Submit");
    let videoIds = [];
    $('.videoIds').each(function(index,elem){
      videoIds.push($(elem).attr("id"));
  })
  postvidz(videoIds);
  }


function addvideo(e) {

    if(youtubeRegEx.test(videoInput.value)) {
        e.preventDefault()
        if(videoInput.value != '') {
            e.preventDefault()
            let vid = YouTubeGetID(videoInput.value)
            let videoItem = document.createElement('div')
            videoItem.className = 'video-container'
            videoItem.innerHTML = 
            `<img src="https://img.youtube.com/vi/${vid}/hqdefault.jpg" class="video-prev" alt="video thumb">
                   <div class="card-body">
                     <a id=${vid} class="btn videoIds btn-danger del">Delete Video</a>
                   </div>`;
            document.querySelector('.video-collection').appendChild(videoItem)
            SubmitRenderedVideos()
        } else {
            e.preventDefault()
            alert('No task added !')
            videoInput.value = '';
        }
        videoInput.value = '';
    }else{
        alert('Please add a correct Youtube URL')
    }
    
}


function postvidz(ids){
    $.ajax({
        type: 'POST',
        url:  $('base').attr('href') + 'index.php?route=seller/account-gallery/jxUpdateSellerVideos',
        data: {"videoIDs": ids.length > 0 ? ids : "[]"},
        success: function(result){
            console.log("DATA",result);
        }
    })
}


function removeItem() {

    document.querySelector('.video-collection').addEventListener('click',removeIt)
    
}

function removeIt(e) {
    if(e.target.classList.contains('del')) {
        e.target.parentElement.parentElement.remove();
    }
    SubmitRenderedVideos()
}


</script>
<?php echo $footer; ?>