
$(document).ready(function() {
/**
 * [getURL to open current filter tab]
 * @type {[type]}
 */
var getURL = window.location.search.substring(1);
var getARGU = getURL.split("&");

for (var i=0;i < getARGU.length;i++) {
  var getSTATUS = getARGU[i].split("=");

  if(getSTATUS[0] && getSTATUS[0] == 'status'){
    $('#accordion_amazon li').removeClass('active');
    $('#amazon_right_link .tab-pane').removeClass('active');
    $('#accordion_amazon li > a').each(function(key, val){
      var getHRF = $(val).attr('href');
      if(getHRF == '#'+getSTATUS[1]){
        $(val).parent().addClass('active');
      }
    })
    $('#amazon_right_link .tab-pane').each(function(key, val){
      var getID = $(val).attr('id');
      if(getID == getSTATUS[1]){
        $(val).addClass('active');
      }
    })
  }
}

});
