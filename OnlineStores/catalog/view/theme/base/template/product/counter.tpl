<?php
$date_end = date("Y-m-d", strtotime($endbidtime));

$date_stop = getdate(strtotime($endbidtime));
$seconds=$date_stop['seconds'];
$minutes=$date_stop['minutes'];
$hours=$date_stop['hours'];
$mday=$date_stop['mday'];
$wday=$date_stop['wday'];
$mon=$date_stop['mon'];
$year=$date_stop['year'];
?>

<div id="countdown"></div>
<style>
.hasCountdown {
	width:200px;
	margin-bottom:1px;
}
</style>
<link rel="stylesheet" href="catalog/view/javascript/jquery.countdown.css">
<script type="text/javascript" src="catalog/view/javascript/jquery.countdown.js"></script>
<script type="text/javascript">

$(document).ready(function() {
var mon=<?php echo($mon);  ?>;
mon=mon-1;
	
$('#countdown').countdown({until: new Date( <?php echo($year);  ?> ,mon , <?php echo($mday);  ?> , <?php echo($hours);  ?> , <?php echo($minutes);  ?> , <?php echo($seconds);  ?> , 0 )});
	

	
  });
 
</script>
