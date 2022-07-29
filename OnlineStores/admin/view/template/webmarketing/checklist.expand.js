// update checked items
$(document).ready(function(){
    $('.checked_item').click(function(){
        var items = [];
    $('.checked_item:checked').each(function(data){
        items.push($(this).data('id'))
    });
        $.ajax({
            url: "/admin/webmarketing/lists/NewUpdate",
            method: "POST",
            data: {items}
        });
    });
});

$(document).ready(function() {
  
    // get box count
    var count = 0;
    var checked = 0;
    console.log(checklister)
    function countBoxes() { 
      count = $("input[type='checkbox']").length;
    }
    
    countBoxes();
    $(":checkbox").click(countBoxes);
    
    // count checks

    function countChecked() {
      checked = $("input:checked").length;
      
      var percentage = parseInt(((checked / count) * 100),10);

      $('.progress-bar').css('width', percentage+ '%');

      $(".progressbar-label").text(percentage + "%");

      $("#valuepro").html(percentage + ".00%");
      
      $("#checkedpro").html(checked + "of" +count );
    }

    countChecked();
    $(":checkbox").click(countChecked);
  });

// Start checked update

$(document).ready(function() {

$('input[type=checkbox]').on('change',function(e) {
  if ($(this).prop('checked')) {
    $(this).closest("tr").addClass("read");
    $(this).closest("tr").removeClass("unread");
    $(this).closest("tr").find(".label-danger").text("In Progess");
  } else {
    $(this).closest("tr").addClass("unread");
    $(this).closest("tr").removeClass("read");
  };
});

});