<!-- 
 * @package Zopim Live Chat
 * @version 0.2.2
 -->

<!-- Start of  Zendesk Widget script -->
<script id="ze-snippet" src="https://static.zdassets.com/ekr/snippet.js?key=<?php echo $code; ?>"> </script>
<!-- End of  Zendesk Widget script -->

<script type="text/javascript"> 
 zE(function(){
$zopim( function() {
	$zopim.livechat.setLanguage('<?php echo $language; ?>');
	$zopim.livechat.setName('<?php echo $username; ?>');
    	$zopim.livechat.setEmail('<?php echo $useremail; ?>');
	$zopim.livechat.button.setPosition('<?php echo $position; ?>');
	$zopim.livechat.window.setTheme('<?php echo $theme; ?>');
	$zopim.livechat.window.setColor('<?php echo $color; ?>');
	$zopim.livechat.bubble.setTitle('<?php echo addslashes($bubbleTitle); ?>');
	$zopim.livechat.bubble.setText('<?php echo addslashes($bubbleText); ?>');
	<?php if($bubbleEnable=="show") { ?>
	$zopim.livechat.bubble.show(true);
	<?php } ?>
	<?php if($bubbleEnable=="hide") { ?>
	$zopim.livechat.bubble.hide(true);
	<?php } ?>
	$zopim.livechat.setGreetings({
		  'online' : ['<?php echo addslashes($OnlineShort); ?>', '<?php echo addslashes($OnlineLong); ?>'],
		  'offline': ['<?php echo addslashes($OfflineShort); ?>', '<?php echo addslashes($OfflineLong); ?>'],
		  'away'   : ['<?php echo addslashes($AwayShort); ?>', '<?php echo addslashes($AwayLong); ?>']  
	  });
	<?php if($hideonoffline){ ?>
	$zopim.livechat.button.setHideWhenOffline(true);
	<?php } ?>
})
});
</script>
