
<style type="text/css">

#specialcount {
text-align:center;
}
#column-left .countoffer ,#column-left .countdate  {
font-size:12px !important;
}
.hasCountdown {
	width:99%;
	margin-bottom:1px;
	margin-top:5px;
}


.countoffer , .countdate {

font-size:14px;
font-weight:bold ;
}

.special2{
font-weight:lighter !important;
}
.special2 {

font-size:16px;
font-weight:lighter;
line-height :120%;
}
.specialh {
min-height:35px;
}
.specialh a {
color:#38B0E3;
}
#specialcount {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	color: white;
	padding: 4px 1px;
	background: 
		#87b846;
	background: -webkit-gradient(
		linear, left top, left bottom, 
		from(#adda4d),
		to(#87b846));
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	border-radius: 3px;
	border: 1px solid #6d8f29;
	-moz-box-shadow:
		0px 1px 0px rgba(000,000,000,0.2),
		inset 0px 1px 0px rgba(255,255,255,0.5);
	-webkit-box-shadow:
		0px 1px 0px rgba(000,000,000,0.2),
		inset 0px 1px 0px rgba(255,255,255,0.5);
	box-shadow:
		0px 1px 0px rgba(000,000,000,0.2),
		inset 0px 1px 0px rgba(255,255,255,0.5);
	text-shadow:
		0px 1px 0px rgba(255,255,255,0.3),
		0px 1px 0px rgba(255,255,255,0.3);
		margin-bottom:2px;
}


#specialcount1 {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 13px;
	color: white;
	padding: 12px;
	
	background: #FF0000;
	background: -webkit-gradient(
		linear, left top, left bottom, 
		from(#FF0000),
		to(#FF0000));
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	border-radius: 3px;
	border: 1px solid #6d8f29;
	-moz-box-shadow:
		0px 1px 0px rgba(000,000,000,0.2),
		inset 0px 1px 0px rgba(255,255,255,0.5);
	-webkit-box-shadow:
		0px 1px 0px rgba(000,000,000,0.2),
		inset 0px 1px 0px rgba(255,255,255,0.5);
	box-shadow:
		0px 1px 0px rgba(000,000,000,0.2),
		inset 0px 1px 0px rgba(255,255,255,0.5);
	text-shadow:
		0px 1px 0px rgba(255,255,255,0.3),
		0px 1px 0px rgba(255,255,255,0.3);
		margin-bottom:2px;
}

.button1 {
width:99%;
	font-family: 'Oswald',sans-serif;
	font-size: 14px;
	color: #ffffff;
	padding: 2px 7px;
	background: -moz-linear-gradient(
		top,
		#33a0e8 0%,
		#2180ce);
	background: -webkit-gradient(
		linear, left top, left bottom, 
		from(#33a0e8),
		to(#2180ce));
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	border-radius: 3px;
	border: 1px solid #18649a;
	-moz-box-shadow:
		0px 1px 1px rgba(000,000,000,0.3),
		inset 0px 1px 0px rgba(131,197,241,1);
	-webkit-box-shadow:
		0px 1px 1px rgba(000,000,000,0.3),
		inset 0px 1px 0px rgba(131,197,241,1);
	box-shadow:
		0px 1px 1px rgba(000,000,000,0.3),
		inset 0px 1px 0px rgba(131,197,241,1);
	text-shadow:
		0px 1px 2px rgba(053,086,130,1),
		0px 0px 0px rgba(000,000,000,0);
}

.orange:hover {
	background: #f47c20;
	background: -webkit-gradient(linear, left top, left bottom, from(#f88e11), to(#f06015));
	background: -moz-linear-gradient(top,  #f88e11,  #f06015);
	filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#f88e11', endColorstr='#f06015');
}
.orange:active {
	color: #fcd3a5;
	background: -webkit-gradient(linear, left top, left bottom, from(#f47a20), to(#faa51a));
	background: -moz-linear-gradient(top,  #f47a20,  #faa51a);
	filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#f47a20', endColorstr='#faa51a');
}
.countdown_rtl {
	direction: rtl;
}
.countdown_holding span {
	color: #888;
}
.countdown_row {
	clear: both;
	width: 100%;
	padding: 0px 2px;
	text-align: center;
}
.countdown_show1 .countdown_section {
	width: 98%;
}
.countdown_show2 .countdown_section {
	width: 48%;
}
.countdown_show3 .countdown_section {
	width: 32.5%;
}
.countdown_show4 .countdown_section {
	width: 24.5%;
}
.countdown_show5 .countdown_section {
	width: 19.5%;
}
.countdown_show6 .countdown_section {
	width: 16.25%;
}
.countdown_show7 .countdown_section {
	width: 14%;
}
.box-product > div{
width:160px !important;
}
.countdown_section {
	display: block;
	width:98%;
	float: left;
	font-size: 9px;
	text-align: center;
}
.countdown_amount {
	font-size: 14px;
	color:orange;
}
.countdown_descr {
	display: block;
	font-size: 8px;
	width: 100%;
}

</style>

<script>
function myFunction(k,product_id) {
	var i = 10;
	setInterval(function(){			 
		$('.auction-time'+k).text(i)
			if(i==1) {
				updateprice(product_id,k);
				 $(".price"+k).css({backgroundColor: "#FED700"});
				i=10;
			}
			if(i==9){
				 $(".price"+k).css({backgroundColor: "#fff"});
				 }
			i--;
		
		},1000
	);
}

</script>
<script>
	function updateprice(pid,k){
		var productid = pid;
		
		$.ajax({
				url: 'index.php?route=module/special_count_down/updateprice&productid='+productid,
				dataType: 'json',
				success: function(json){
					if(json['maxbid']!=''){
					$(".price"+k).html(json['maxbid']);
					}
					if(json['biddername']!=''){
					$("#biddername"+k).html(json['biddername']);
					}
				},
			});	
	}
	</script>

<div class="box">
  <div class="box-heading limitedoffer"><?php echo $heading_title; ?></div>
  <div class="box-content">
    <div class="box-product">
	
	<?php $k=1; ?>
      <?php foreach ($products as $product) { ?>
      <div>
        <?php if ($product['thumb']) { ?>
	
        <div class="image"><a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" /></a></div>
        <?php } ?>
        <div class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></div>
        		
		
		<?php if ($product['maxcustomerbidamt']) { ?>
		
        <div class="price<?php echo $k; ?>" style="color:#00CFDC;font-size:13px;font-weight:bold">
		 <?php echo $product['maxcustomerbidamt']; ?>
        </div>
		<span id="biddername<?php echo $k; ?>"></span>
        <?php } ?>
		
		
	
		
		<?php if($product['checkstatebid']==0) { ?>
		<script type="text/javascript">myFunction(<?php echo $k; ?>,<?php echo $product['product_id'] ; ?>);</script>
        <?php } ?>
		
		
		
		
		<div id="countdown_<?php echo $product['product_id'] ; ?>"></div>
		
       
 <?php $k++; ?>
      </div>
      <?php } ?>
    </div>
  </div>
</div>
<link rel="stylesheet" href="catalog/view/javascript/jquery.countdown.css">
<script type="text/javascript" src="catalog/view/javascript/jquery.countdown.js"></script>
<script type="text/javascript">
<?php foreach ($products as $product) { ?>
$(document).ready(function() {
var mon=<?php echo($product['mon']);  ?>;
mon=mon-1;
var close = <?php echo($product['checkstatebid']);  ?>;


if(close==0){
	
	$('#countdown_<?php echo $product['product_id'] ; ?>').countdown({until: new Date( <?php echo($product['year']);  ?> ,mon , <?php echo($product['day']);  ?> , <?php echo($product['hours']);  ?> , <?php echo($product['minutes']);  ?> , <?php echo($product['seconds']);  ?> , 0 )});
	
} else {

$('#countdown_<?php echo $product['product_id'] ; ?>').append('<p id="specialcount1">Auction Ended</p>');

}
	
  });
    <?php } ?>
</script>
