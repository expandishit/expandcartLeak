$(document).ready(function() {
   $('div.count input[type="text"]').val(1);
   $('div.count input[type="text"]').keydown(function (e) {
      // Allow: backspace, delete, tab, escape, enter and .
      if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
             // Allow: Ctrl/cmd+A
          (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
             // Allow: Ctrl/cmd+C
          (e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
             // Allow: Ctrl/cmd+X
          (e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
             // Allow: home, end, left, right
          (e.keyCode >= 35 && e.keyCode <= 39)) {
         // let it happen, don't do anything
         return;
      }
      // Ensure that it is a number and stop the keypress
      if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
         e.preventDefault();
      }
   });

   $('div.count').click(function(e) {
      e.preventDefault();
   });

   $('div.count i.btn-minus').click(function(e) {
      e.preventDefault();
      var oldvalue = $(this).parent('div.count').find('input[type="text"]').val();
      if(oldvalue > 1)
         $(this).parent('div.count').find('input[type="text"]').val(parseInt(oldvalue) - 1);
   });
   $('div.count i.btn-plus').on('click',function(e) {
      e.preventDefault();
      var oldvalue = $(this).parent('div.count').find('input[type="text"]').val();
      $(this).parent('div.count').find('input[type="text"]').val(parseInt(oldvalue) + 1);
   });

   $('a.qty-buy').on('click', function(e){
      e.preventDefault();
      var qtyValue = $(this).parent().find('div.count input[type="text"]').val();
      if(qtyValue == "")
          qtyValue = 1;
      //debugger;
      openInNewTab($(this).attr('href') + '%26configoption[1]%3D' + qtyValue);
   });
});

function openInNewTab(url) {
   var win = window.open(url, '_blank');
   win.focus();
}
