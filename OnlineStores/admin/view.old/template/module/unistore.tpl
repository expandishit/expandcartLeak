<?php echo $header; ?>
<div id="content">
<div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
  <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
</div>
<?php if ($error_warning) { ?>
<script>
    var notificationString = '<?php echo $error_warning; ?>';
    var notificationType = 'warning';
</script>
<?php } ?>

<!-- Uni-store Theme Options -->

<div class="bg-uni-store">
<div class="bg-uni-store-bottom">
<div class="bg-uni-store-top">

	<!-- Title -->

	<h1>UNI-STORE Theme Options</h1>
	
	<!-- Text -->
	
	<div class="text">
	<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
	
		<!-- Select color settings -->
		
		<div class="select-color-settings">
		
			<p>Select color settings</p>
			
			<ul>
			
				<li><a href="javascript:;" rel="0"<?php if($unistore_color < 1) { echo ' class="active"'; } ?>><img src="view/image/uni-store/tools.png" alt=""></a></li>
				<li><a href="javascript:;" rel="1"<?php if($unistore_color == 1) { echo ' class="active"'; } ?>><img src="view/image/uni-store/sport.png" alt=""></a></li>
				<li><a href="javascript:;" rel="2"<?php if($unistore_color == 2) { echo ' class="active"'; } ?>><img src="view/image/uni-store/kids.png" alt=""></a></li>
				<li><a href="javascript:;" rel="3"<?php if($unistore_color == 3) { echo ' class="active"'; } ?>><img src="view/image/uni-store/jew.png" alt=""></a></li>
				<li><a href="javascript:;" rel="4"<?php if($unistore_color == 4) { echo ' class="active"'; } ?>><img src="view/image/uni-store/jew2.png" alt=""></a></li>
				<li><a href="javascript:;" rel="5"<?php if($unistore_color == 5) { echo ' class="active"'; } ?>><img src="view/image/uni-store/fash.png" alt=""></a></li>
			
			</ul>
			<input name="unistore_color" value="<?php echo $unistore_color; ?>" id="unistore_color" type="hidden" />
		
		</div>
		
		<?php $selected_disabled = false; $selected_enabled = false; if($unistore_options_status == 0) { $selected_disabled = ' selected="selected"'; } if($unistore_options_status == 1) { $selected_enabled = ' selected="selected"'; } ?>
		<select name="unistore_options_status"><option value="0"<?php echo $selected_disabled; ?>>Disabled</option><option value="1"<?php echo $selected_enabled; ?>>Enabled</option></select><p style="font-size:1px;line-height:1px;height:1px;clear:both;margin:0px;padding:0px;"></p>
		
		<!-- LEFT -->
		
		<div class="left">
		
			<h3>Text Color:</h3>
			<!-- Input -->
			<div>
			
				<p>Body text- content, Old price</p>
				<div class="input"><input type="text" value="<?php echo $body_text_content; ?>" id="body_text_content" name="body_text_content" style="width:41px" /></div>
			
			</div>
			<!-- End Input -->
			<!-- Input -->
			<div>
			
				<p>Body text- footer</p>
				<div class="input"><input type="text" value="<?php echo $body_text_footer; ?>" id="body_text_footer" name="body_text_footer" style="width:41px" /></div>
			
			</div>
			<!-- End Input -->
			<!-- Input -->
			<div>
			
				<p>New Price and footer contact</p>
				<div class="input"><input type="text" value="<?php echo $new_price_and_footer_contact; ?>" id="new_price_and_footer_contact" name="new_price_and_footer_contact" style="width:41px" /></div>
			
			</div>
			<!-- End Input -->
			<!-- Input -->
			<div>
			
				<p>Headlines content</p>
				<div class="input"><input type="text" value="<?php echo $headlines_content; ?>" id="headlines_content" name="headlines_content" style="width:41px" /></div>
			
			</div>
			<!-- End Input -->
			<!-- Input -->
			<div>
			
				<p>Headlines footer</p>
				<div class="input"><input type="text" value="<?php echo $headlines_footer; ?>" id="headlines_footer" name="headlines_footer" style="width:41px" /></div>
			
			</div>
			<!-- End Input -->
			<!-- Input -->
			<div>
			
				<p>Product name and categories on subpages</p>
				<div class="input"><input type="text" value="<?php echo $product_name_and_categories_on_subpages; ?>" id="product_name_and_categories_on_subpages" name="product_name_and_categories_on_subpages" style="width:41px" /></div>
			
			</div>
			<!-- End Input -->
			<!-- Input -->
			<div>
			
				<p>Categories in top</p>
				<div class="input"><input type="text" value="<?php echo $categories_in_top; ?>" id="categories_in_top" name="categories_in_top" style="width:41px" /></div>
			
			</div>
			<!-- End Input -->
			<!-- Input -->
			<div>
			
				<p>Text in top</p>
				<div class="input"><input type="text" value="<?php echo $text_in_top; ?>" id="text_in_top" name="text_in_top" style="width:41px" /></div>
			
			</div>
			<!-- End Input -->
			<!-- Input -->
			<div>
			
				<p>Text in Bar in top</p>
				<div class="input"><input type="text" value="<?php echo $text_in_bar_in_top; ?>" id="text_in_bar_in_top" name="text_in_bar_in_top" style="width:41px" /></div>
			
			</div>
			<!-- End Input -->
			<h3>Text options:</h3>
			<!-- Input -->
			<div>
			
				<p>Body font:</p>
				<div class="input">          <select name="body_font" style="width:170px">

              <?php if (isset($body_font)) {
              $selected = "selected";
              ?>
              <option value="standard" <?php if($body_font=='standrd'){echo $selected;} ?>>Standard</option>
              <option value="Arial" <?php if($body_font=='Arial'){echo $selected;} ?>>Arial</option>
              <option value="Verdana" <?php if($body_font=='Verdana'){echo $selected;} ?>>Verdana</option>
              <option value="Helvetica" <?php if($body_font=='Helvetica'){echo $selected;} ?>>Helvetica</option>
              
              <option value="Lucida Grande" <?php if($body_font=='Lucida Grande'){echo $selected;} ?>>Lucida Grande</option>
              <option value="Trebuchet MS" <?php if($body_font=='Helvetica'){echo $selected;} ?>>Trebuchet MS</option>
              <option value="Times New Roman" <?php if($body_font=='Times New Roman'){echo $selected;} ?>>Times New Roman</option>
              <option value="Tahoma" <?php if($body_font=='Tahoma'){echo $selected;} ?>>Tahoma</option>
              <option value="Georgia" <?php if($body_font=='Georgia'){echo $selected;} ?>>Georgia</option>
                           
              <?php } else { ?>
              <option value="standard" selected="selected">Standad</option>
              <option value="Arial">Arial</option>
              <option value="Verdana">Verdana</option>    
           <option value="Helvetica">Helvetica</option>
              <option value="Lucida Grande">Lucida Grande</option>
             <option value="Trebuchet MS">Trebuchet MS</option>
            <option value="Times New Roman">Times New Roman</option>
             <option value="Tahoma">Tahoma</option>
            <option value="Georgia">Georgia</option>
              
              <?php } ?>
            </select></div>
			
			</div>
			<!-- End Input -->	
			<!-- Input -->
			<div>
			
				<p>Headlines font:</p>
				<div class="input">          <select name="headlines_font" style="width:170px">
              <?php if (isset($headlines_font)) {
              $selected = "selected"; }
              ?>
<option value="standard" <?php if($headlines_font=='standard'){echo $selected;} ?>>Standard</option>
<option value="Francois+One" <?php if($headlines_font=='Francois+One'){echo $selected;} ?>>Francois One</option>
		  <option value="Arial" <?php if($headlines_font=='Arial'){echo $selected;} ?>>Arial</option>
<option value="Aclonica" <?php if($headlines_font=='Aclonica'){echo $selected;} ?>>Aclonica</option>
<option value="Allan" <?php if($headlines_font=='Allan'){echo $selected;} ?>>Allan</option>
<option value="Annie+Use+Your+Telescope" <?php if($headlines_font=='Annie+Use+Your+Telescope'){echo $selected;} ?>>Annie Use Your Telescope</option>
<option value="Anonymous+Pro" <?php if($headlines_font=='Anonymous+Pro'){echo $selected;} ?>>Anonymous Pro</option>
<option value="Allerta+Stencil" <?php if($headlines_font=='Allerta+Stencil'){echo $selected;} ?>>Allerta Stencil</option>
<option value="Allerta" <?php if($headlines_font=='Allerta'){echo $selected;} ?>>Allerta</option>
<option value="Amaranth" <?php if($headlines_font=='Amaranth'){echo $selected;} ?>>Amaranth</option>
<option value="Anton" <?php if($headlines_font=='Anton'){echo $selected;} ?>>Anton</option>
<option value="Architects+Daughter" <?php if($headlines_font=='Architects+Daughter'){echo $selected;} ?>>Architects Daughter</option>
<option value="Arimo" <?php if($headlines_font=='Arimo'){echo $selected;} ?>>Arimo</option>
<option value="Artifika" <?php if($headlines_font=='Artifika'){echo $selected;} ?>>Artifika</option>
<option value="Arvo" <?php if($headlines_font=='Arvo'){echo $selected;} ?>>Arvo</option>
<option value="Asset" <?php if($headlines_font=='Asset'){echo $selected;} ?>>Asset</option>
<option value="Astloch" <?php if($headlines_font=='Astloch'){echo $selected;} ?>>Astloch</option>
<option value="Bangers" <?php if($headlines_font=='Bangers'){echo $selected;} ?>>Bangers</option>
<option value="Bentham" <?php if($headlines_font=='Bentham'){echo $selected;} ?>>Bentham</option>
<option value="Bevan" <?php if($headlines_font=='Bevan'){echo $selected;} ?>>Bevan</option>
<option value="Bigshot+One" <?php if($headlines_font=='Bigshot+One'){echo $selected;} ?>>Bigshot One</option>
<option value="Bowlby+One" <?php if($headlines_font=='Bowlby+One'){echo $selected;} ?>>Bowlby One</option>
<option value="Bowlby+One+SC" <?php if($headlines_font=='Bowlby+One+SC'){echo $selected;} ?>>Bowlby One SC</option>
<option value="Brawler" <?php if($headlines_font=='Brawler'){echo $selected;} ?>>Brawler </option>
<option value="Buda" <?php if($headlines_font=='Buda'){echo $selected;} ?>>Buda</option>
<option value="Cabin" <?php if($headlines_font=='Cabin'){echo $selected;} ?>>Cabin</option>
<option value="Calligraffitti" <?php if($headlines_font=='Calligraffitti'){echo $selected;} ?>>Calligraffitti</option>
<option value="Candal" <?php if($headlines_font=='Candal'){echo $selected;} ?>>Candal</option>
<option value="Cantarell" <?php if($headlines_font=='Cantarell'){echo $selected;} ?>>Cantarell</option>
<option value="Cardo" <?php if($headlines_font=='Cardo'){echo $selected;} ?>>Cardo</option>
<option value="Carter One" <?php if($headlines_font=='Carter One'){echo $selected;} ?>>Carter One</option>
<option value="Caudex" <?php if($headlines_font=='Caudex'){echo $selected;} ?>>Caudex</option>
<option value="Cedarville+Cursive" <?php if($headlines_font=='Cedarville+Cursive'){echo $selected;} ?>>Cedarville Cursive</option>
<option value="Cherry+Cream+Soda" <?php if($headlines_font=='Cherry+Cream+Soda'){echo $selected;} ?>>Cherry Cream Soda</option>
<option value="Chewy" <?php if($headlines_font=='Chewy'){echo $selected;} ?>>Chewy</option>
<option value="Coda" <?php if($headlines_font=='Coda'){echo $selected;} ?>>Coda</option>
<option value="Coming+Soon" <?php if($headlines_font=='Coming+Soon'){echo $selected;} ?>>Coming Soon</option>
<option value="Copse" <?php if($headlines_font=='Copse'){echo $selected;} ?>>Copse</option>
<option value="Corben" <?php if($headlines_font=='Corben'){echo $selected;} ?>>Corben</option>
<option value="Cousine" <?php if($headlines_font=='Cousine'){echo $selected;} ?>>Cousine</option>
<option value="Covered+By+Your+Grace" <?php if($headlines_font=='Covered+By+Your+Grace'){echo $selected;} ?>>Covered By Your Grace</option>
<option value="Crafty+Girls" <?php if($headlines_font=='Crafty+Girls'){echo $selected;} ?>>Crafty Girls</option>
<option value="Crimson+Text" <?php if($headlines_font=='Crimson+Text'){echo $selected;} ?>>Crimson Text</option>
<option value="Crushed" <?php if($headlines_font=='Crushed'){echo $selected;} ?>>Crushed</option>
<option value="Cuprum" <?php if($headlines_font=='Cuprum'){echo $selected;} ?>>Cuprum</option>
<option value="Damion" <?php if($headlines_font=='Damion'){echo $selected;} ?>>Damion</option>
<option value="Dancing+Script" <?php if($headlines_font=='Dancing+Script'){echo $selected;} ?>>Dancing Script</option>
<option value="Dawning+of+a+New+Day" <?php if($headlines_font=='Dawning+of+a+New+Day'){echo $selected;} ?>>Dawning of a New Day</option>
<option value="Didact+Gothic" <?php if($headlines_font=='Didact+Gothic'){echo $selected;} ?>>Didact Gothic</option>
<option value="Droid+Sans" <?php if($headlines_font=='Droid+Sans'){echo $selected;} ?>>Droid Sans</option>
<option value="Droid+Sans+Mono" <?php if($headlines_font=='Droid+Sans+Mono'){echo $selected;} ?>>Droid Sans Mono</option>
<option value="Droid+Serif" <?php if($headlines_font=='Droid+Serif'){echo $selected;} ?>>Droid Serif</option>
<option value="EB+Garamond" <?php if($headlines_font=='EB+Garamond'){echo $selected;} ?>>EB Garamond</option>
<option value="Expletus+Sans" <?php if($headlines_font=='Expletus+Sans'){echo $selected;} ?>>Expletus Sans</option>
<option value="Fontdiner+Swanky" <?php if($headlines_font=='Fontdiner+Swanky'){echo $selected;} ?>>Fontdiner Swanky</option>
<option value="Forum" <?php if($headlines_font=='Forum'){echo $selected;} ?>>Forum</option>
<option value="Geo" <?php if($headlines_font=='Geo'){echo $selected;} ?>>Geo</option>
<option value="Give+You+Glory" <?php if($headlines_font=='Give+You+Glory'){echo $selected;} ?>>Give You Glory</option>
<option value="Goblin+One" <?php if($headlines_font=='Goblin+One'){echo $selected;} ?>>Goblin One</option>
<option value="Goudy+Bookletter+1911" <?php if($headlines_font=='Goudy+Bookletter+1911'){echo $selected;} ?>>Goudy Bookletter 1911</option>
<option value="Gravitas+One" <?php if($headlines_font=='Gravitas+One'){echo $selected;} ?>>Gravitas One</option>
<option value="Gruppo" <?php if($headlines_font=='Gruppo'){echo $selected;} ?>>Gruppo</option>
<option value="Hammersmith+One" <?php if($headlines_font=='Hammersmith+One'){echo $selected;} ?>>Hammersmith One</option>
<option value="Holtwood+One+SC" <?php if($headlines_font=='Holtwood+One+SC'){echo $selected;} ?>>Holtwood One SC</option>
<option value="Homemade+Apple" <?php if($headlines_font=='Homemade+Apple'){echo $selected;} ?>>Homemade Apple</option>
<option value="Inconsolata" <?php if($headlines_font=='Inconsolata'){echo $selected;} ?>>Inconsolata</option>
<option value="Indie+Flower" <?php if($headlines_font=='Indie+Flower'){echo $selected;} ?>>Indie Flower</option>
<option value="IM+Fell+DW+Pica" <?php if($headlines_font=='IM+Fell+DW+Pica'){echo $selected;} ?>>IM Fell DW Pica</option>
<option value="IM+Fell+DW+Pica+SC" <?php if($headlines_font=='IM+Fell+DW+Pica+SC'){echo $selected;} ?>>IM Fell DW Pica SC</option>
<option value="IM+Fell+Double+Pica" <?php if($headlines_font=='IM+Fell+Double+Pica'){echo $selected;} ?>>IM Fell Double Pica</option>
<option value="IM+Fell+Double+Pica+SC" <?php if($headlines_font=='IM+Fell+Double+Pica+SC'){echo $selected;} ?>>IM Fell Double Pica SC</option>
<option value="IM+Fell+English" <?php if($headlines_font=='IM+Fell+English'){echo $selected;} ?>>IM Fell English</option>
<option value="IM+Fell+English+SC" <?php if($headlines_font=='IM+Fell+English+SC'){echo $selected;} ?>>IM Fell English SC</option>
<option value="IM+Fell+French+Canon" <?php if($headlines_font=='IM+Fell+French+Canon'){echo $selected;} ?>>IM Fell French Canon</option>
<option value="IM+Fell+French+Canon+SC" <?php if($headlines_font=='IM+Fell+French+Canon+SC'){echo $selected;} ?>>IM Fell French Canon SC</option>
<option value="IM+Fell+Great+Primer" <?php if($headlines_font=='IM+Fell+Great+Primer'){echo $selected;} ?>>IM Fell Great Primer</option>
<option value="IM+Fell+Great+Primer+SC" <?php if($headlines_font=='IM+Fell+Great+Primer+SC'){echo $selected;} ?>>IM Fell Great Primer SC</option>
<option value="Irish+Grover" <?php if($headlines_font=='Irish+Grover'){echo $selected;} ?>>Irish Grover</option>
<option value="Irish+Growler" <?php if($headlines_font=='Irish+Growler'){echo $selected;} ?>>Irish Growler</option>
<option value="Istok+Web" <?php if($headlines_font=='Istok+Web'){echo $selected;} ?>>Istok Web</option>
<option value="Josefin+Sans" <?php if($headlines_font=='Josefin+Sans'){echo $selected;} ?>>Josefin Sans Regular 400</option>
<option value="Josefin+Slab" <?php if($headlines_font=='Josefin+Slab'){echo $selected;} ?>>Josefin Slab Regular 400</option>
<option value="Judson" <?php if($headlines_font=='Judson'){echo $selected;} ?>>Judson</option>
<option value="Jura" <?php if($headlines_font=='Jura'){echo $selected;} ?>> Jura Regular</option>
<option value="Just+Another+Hand" <?php if($headlines_font=='Just+Another+Hand'){echo $selected;} ?>>Just Another Hand</option>
<option value="Just+Me+Again+Down+Here" <?php if($headlines_font=='Just+Me+Again+Down+Here'){echo $selected;} ?>>Just Me Again Down Here</option>
<option value="Kameron" <?php if($headlines_font=='Kameron'){echo $selected;} ?>>Kameron</option>
<option value="Kenia" <?php if($headlines_font=='Kenia'){echo $selected;} ?>>Kenia</option>
<option value="Kranky" <?php if($headlines_font=='Kranky'){echo $selected;} ?>>Kranky</option>
<option value="Kreon" <?php if($headlines_font=='Kreon'){echo $selected;} ?>>Kreon</option>
<option value="Kristi" <?php if($headlines_font=='Kristi'){echo $selected;} ?>>Kristi</option>
<option value="La+Belle+Aurore" <?php if($headlines_font=='La+Belle+Aurore'){echo $selected;} ?>>La Belle Aurore</option>
<option value="Lato" <?php if($headlines_font=='Lato'){echo $selected;} ?>>Lato</option>
<option value="League+Script" <?php if($headlines_font=='League+Script'){echo $selected;} ?>>League Script</option>
<option value="Lekton" <?php if($headlines_font=='Lekton'){echo $selected;} ?>> Lekton </option>
<option value="Limelight" <?php if($headlines_font=='Limelight'){echo $selected;} ?>> Limelight </option>
<option value="Lobster" <?php if($headlines_font=='Lobster'){echo $selected;} ?>>Lobster</option>
<option value="Lobster Two" <?php if($headlines_font=='Lobster Two'){echo $selected;} ?>>Lobster Two</option>
<option value="Lora" <?php if($headlines_font=='Lora'){echo $selected;} ?>>Lora</option>
<option value="Love+Ya+Like+A+Sister" <?php if($headlines_font=='Love+Ya+Like+A+Sister'){echo $selected;} ?>>Love Ya Like A Sister</option>
<option value="Loved+by+the+King" <?php if($headlines_font=='Loved+by+the+King'){echo $selected;} ?>>Loved by the King</option>
<option value="Luckiest+Guy" <?php if($headlines_font=='Luckiest+Guy'){echo $selected;} ?>>Luckiest Guy</option>
<option value="Maiden+Orange" <?php if($headlines_font=='Maiden+Orange'){echo $selected;} ?>>Maiden Orange</option>
<option value="Mako" <?php if($headlines_font=='Mako'){echo $selected;} ?>>Mako</option>
<option value="Maven+Pro" <?php if($headlines_font=='Maven+Pro'){echo $selected;} ?>> Maven Pro</option>
<option value="Meddon" <?php if($headlines_font=='Meddon'){echo $selected;} ?>>Meddon</option>
<option value="MedievalSharp" <?php if($headlines_font=='MedievalSharp'){echo $selected;} ?>>MedievalSharp</option>
<option value="Megrim" <?php if($headlines_font=='Megrim'){echo $selected;} ?>>Megrim</option>
<option value="Merriweather" <?php if($headlines_font=='Merriweather'){echo $selected;} ?>>Merriweather</option>
<option value="Metrophobic" <?php if($headlines_font=='Metrophobic'){echo $selected;} ?>>Metrophobic</option>
<option value="Michroma" <?php if($headlines_font=='Michroma'){echo $selected;} ?>>Michroma</option>
<option value="Miltonian Tattoo" <?php if($headlines_font=='Miltonian Tattoo'){echo $selected;} ?>>Miltonian Tattoo</option>
<option value="Miltonian" <?php if($headlines_font=='Miltonian'){echo $selected;} ?>>Miltonian</option>
<option value="Modern Antiqua" <?php if($headlines_font=='Modern Antiqua'){echo $selected;} ?>>Modern Antiqua</option>
<option value="Monofett" <?php if($headlines_font=='Monofett'){echo $selected;} ?>>Monofett</option>
<option value="Molengo" <?php if($headlines_font=='Molengo'){echo $selected;} ?>>Molengo</option>
<option value="Mountains of Christmas" <?php if($headlines_font=='Mountains of Christmas'){echo $selected;} ?>>Mountains of Christmas</option>
<option value="Muli" <?php if($headlines_font=='Muli'){echo $selected;} ?>>Muli Regular</option>
<option value="Neucha" <?php if($headlines_font=='Neucha'){echo $selected;} ?>>Neucha</option>
<option value="Neuton" <?php if($headlines_font=='Neuton'){echo $selected;} ?>>Neuton</option>
<option value="News+Cycle" <?php if($headlines_font=='News+Cycle'){echo $selected;} ?>>News Cycle</option>
<option value="Nixie+One" <?php if($headlines_font=='Nixie+One'){echo $selected;} ?>>Nixie One</option>
<option value="Nobile" <?php if($headlines_font=='Nobile'){echo $selected;} ?>>Nobile</option>
<option value="Nova+Cut" <?php if($headlines_font=='Nova+Cut'){echo $selected;} ?>>Nova Cut</option>
<option value="Nova+Flat" <?php if($headlines_font=='Nova+Flat'){echo $selected;} ?>>Nova Flat</option>
<option value="Nova+Mono" <?php if($headlines_font=='Nova+Mono'){echo $selected;} ?>>Nova Mono</option>
<option value="Nova+Oval" <?php if($headlines_font=='Nova+Oval'){echo $selected;} ?>>Nova Oval</option>
<option value="Nova+Round" <?php if($headlines_font=='Nova+Round'){echo $selected;} ?>>Nova Round</option>
<option value="Nova+Script" <?php if($headlines_font=='Nova+Script'){echo $selected;} ?>>Nova Script</option>
<option value="Nova+Slim" <?php if($headlines_font=='Nova+Slim'){echo $selected;} ?>>Nova Slim</option>
<option value="Nova+Square" <?php if($headlines_font=='Nova+Square'){echo $selected;} ?>>Nova Square</option>
<option value="Nunito:light" <?php if($headlines_font=='Nunito:light'){echo $selected;} ?>> Nunito Light</option>
<option value="Nunito" <?php if($headlines_font=='Nunito'){echo $selected;} ?>> Nunito Regular</option>
<option value="OFL+Sorts+Mill+Goudy+TT" <?php if($headlines_font=='OFL+Sorts+Mill+Goudy+TT'){echo $selected;} ?>>OFL Sorts Mill Goudy TT</option>
<option value="Old+Standard+TT" <?php if($headlines_font=='Old+Standard+TT'){echo $selected;} ?>>Old Standard TT</option>
<option value="Open+Sans" <?php if($headlines_font=='Open+Sans'){echo $selected;} ?>>Open Sans regular</option>
<option value="Open+Sans+Condensed" <?php if($headlines_font=='Open+Sans+Condensed'){echo $selected;} ?>>Open Sans Condensed</option>
<option value="Orbitron" <?php if($headlines_font=='Orbitron'){echo $selected;} ?>>Orbitron Regular (400)</option>
<option value="Oswald" <?php if($headlines_font=='Oswald'){echo $selected;} ?>>Oswald</option>
<option value="Over+the+Rainbow" <?php if($headlines_font=='Over+the+Rainbow'){echo $selected;} ?>>Over the Rainbow</option>
<option value="Reenie+Beanie" <?php if($headlines_font=='Reenie+Beanie'){echo $selected;} ?>>Reenie Beanie</option>
<option value="Pacifico" <?php if($headlines_font=='Pacifico'){echo $selected;} ?>>Pacifico</option>
<option value="Patrick+Hand" <?php if($headlines_font=='Patrick+Hand'){echo $selected;} ?>>Patrick Hand</option>
<option value="Paytone+One" <?php if($headlines_font=='Paytone+One'){echo $selected;} ?>>Paytone One</option>
<option value="Permanent+Marker" <?php if($headlines_font=='Permanent+Marker'){echo $selected;} ?>>Permanent Marker</option>
<option value="Philosopher" <?php if($headlines_font=='Philosopher'){echo $selected;} ?>>Philosopher</option>
<option value="Play" <?php if($headlines_font=='Play'){echo $selected;} ?>>Play</option>
<option value="Playfair+Display" <?php if($headlines_font=='Playfair+Display'){echo $selected;} ?>> Playfair Display </option>
<option value="Podkova" <?php if($headlines_font=='Podkova'){echo $selected;} ?>> Podkova </option>
<option value="PT+Sans" <?php if($headlines_font=='PT+Sans'){echo $selected;} ?>>PT Sans</option>
<option value="PT+Sans+Narrow" <?php if($headlines_font=='PT+Sans+Narrow'){echo $selected;} ?>>PT Sans Narrow</option>
<option value="PT+Sans+Narrow:regular,bold" <?php if($headlines_font=='PT+Sans+Narrow:regular,bold'){echo $selected;} ?>>PT Sans Narrow (plus bold)</option>
<option value="PT+Serif" <?php if($headlines_font=='PT+Serif'){echo $selected;} ?>>PT Serif</option>
<option value="PT+Serif Caption" <?php if($headlines_font=='PT+Serif Caption'){echo $selected;} ?>>PT Serif Caption</option>
<option value="Puritan" <?php if($headlines_font=='PT+Serif Caption'){echo $selected;} ?>>Puritan</option>
<option value="Quattrocento" <?php if($headlines_font=='Quattrocento'){echo $selected;} ?>>Quattrocento</option>
<option value="Quattrocento+Sans" <?php if($headlines_font=='Quattrocento+Sans'){echo $selected;} ?>>Quattrocento Sans</option>
<option value="Radley" <?php if($headlines_font=='Radley'){echo $selected;} ?>>Radley</option>
<option value="Raleway" <?php if($headlines_font=='Raleway'){echo $selected;} ?>>Raleway</option>
<option value="Redressed" <?php if($headlines_font=='Redressed'){echo $selected;} ?>>Redressed</option>
<option value="Rock+Salt" <?php if($headlines_font=='Rock+Salt'){echo $selected;} ?>>Rock Salt</option>
<option value="Rokkitt" <?php if($headlines_font=='Rokkitt'){echo $selected;} ?>>Rokkitt</option>
<option value="Ruslan+Display" <?php if($headlines_font=='Ruslan+Display'){echo $selected;} ?>>Ruslan Display</option>
<option value="Schoolbell" <?php if($headlines_font=='Schoolbell'){echo $selected;} ?>>Schoolbell</option>
<option value="Shadows+Into+Light" <?php if($headlines_font=='Shadows+Into+Light'){echo $selected;} ?>>Shadows Into Light</option>
<option value="Shanti" <?php if($headlines_font=='Shanti'){echo $selected;} ?>>Shanti</option>
<option value="Sigmar+One" <?php if($headlines_font=='Sigmar+One'){echo $selected;} ?>>Sigmar One</option>
<option value="Six+Caps" <?php if($headlines_font=='Six+Caps'){echo $selected;} ?>>Six Caps</option>
<option value="Slackey" <?php if($headlines_font=='Slackey'){echo $selected;} ?>>Slackey</option>
<option value="Smythe" <?php if($headlines_font=='Smythe'){echo $selected;} ?>>Smythe</option>
<option value="Sniglet" <?php if($headlines_font=='Sniglet'){echo $selected;} ?>>Sniglet</option>
<option value="Special+Elite" <?php if($headlines_font=='Special+Elite'){echo $selected;} ?>>Special Elite</option>
<option value="Stardos+Stencil" <?php if($headlines_font=='Stardos+Stencil'){echo $selected;} ?>>Stardos Stencil</option>
<option value="Sue+Ellen+Francisco" <?php if($headlines_font=='Sue+Ellen+Francisco'){echo $selected;} ?>>Sue Ellen Francisco</option>
<option value="Sunshiney" <?php if($headlines_font=='Sunshiney'){echo $selected;} ?>>Sunshiney</option>
<option value="Swanky+and+Moo+Moo" <?php if($headlines_font=='Swanky+and+Moo+Moo'){echo $selected;} ?>>Swanky and Moo Moo</option>
<option value="Syncopate" <?php if($headlines_font=='Syncopate'){echo $selected;} ?>>Syncopate</option>
<option value="Tangerine" <?php if($headlines_font=='Tangerine'){echo $selected;} ?>>Tangerine</option>
<option value="Tenor+Sans" <?php if($headlines_font=='Tenor+Sans'){echo $selected;} ?>> Tenor Sans </option>
<option value="Terminal+Dosis+Light" <?php if($headlines_font=='Terminal+Dosis+Light'){echo $selected;} ?>>Terminal Dosis Light</option>
<option value="The+Girl+Next+Door" <?php if($headlines_font=='The+Girl+Next+Door'){echo $selected;} ?>>The Girl Next Door</option>
<option value="Tinos" <?php if($headlines_font=='Tinos'){echo $selected;} ?>>Tinos</option>
<option value="Ubuntu" <?php if($headlines_font=='Ubuntu'){echo $selected;} ?>>Ubuntu</option>
<option value="Ultra" <?php if($headlines_font=='Ultra'){echo $selected;} ?>>Ultra</option>
<option value="Unkempt" <?php if($headlines_font=='Unkempt'){echo $selected;} ?>>Unkempt</option>
<option value="UnifrakturCook:bold" <?php if($headlines_font=='UnifrakturCook:bold'){echo $selected;} ?>>UnifrakturCook</option>
<option value="UnifrakturMaguntia" <?php if($headlines_font=='UnifrakturMaguntia'){echo $selected;} ?>>UnifrakturMaguntia</option>
<option value="Varela" <?php if($headlines_font=='Varela'){echo $selected;} ?>>Varela</option>
<option value="Varela Round" <?php if($headlines_font=='Varela Round'){echo $selected;} ?>>Varela Round</option>
<option value="Vibur" <?php if($headlines_font=='Vibur'){echo $selected;} ?>>Vibur</option>
<option value="Vollkorn" <?php if($headlines_font=='Vollkorn'){echo $selected;} ?>>Vollkorn</option>
<option value="VT323" <?php if($headlines_font=='VT323'){echo $selected;} ?>>VT323</option>
<option value="Waiting+for+the+Sunrise" <?php if($headlines_font=='Waiting+for+the+Sunrise'){echo $selected;} ?>>Waiting for the Sunrise</option>
<option value="Wallpoet" <?php if($headlines_font=='Wallpoet'){echo $selected;} ?>>Wallpoet</option>
<option value="Walter+Turncoat" <?php if($headlines_font=='Walter+Turncoat'){echo $selected;} ?>>Walter Turncoat</option>
<option value="Wire+One" <?php if($headlines_font=='Wire+One'){echo $selected;} ?>>Wire One</option>
<option value="Yanone+Kaffeesatz" <?php if($headlines_font=='Yanone+Kaffeesatz'){echo $selected;} ?>>Yanone Kaffeesatz</option>
<option value="Yeseva+One" <?php if($headlines_font=='Yeseva+One'){echo $selected;} ?>>Yeseva One</option>
<option value="Zeyada" <?php if($headlines_font=='Zeyada'){echo $selected;} ?>>Zeyada</option>  
            </select> </div>
			
			</div>
			<!-- End Input -->		
		
		</div>
		
		<!-- End Left -->
		
		<!-- RIGHT -->
		
		<div class="right">
		
			<h3>Button Color:</h3>
			<!-- Input -->
			<div>
			
				<p>Content top gradient</p>
				<div class="input"><input type="text" value="<?php echo $content_top_gradient; ?>" id="content_top_gradient" name="content_top_gradient" style="width:41px" /></div>
			
			</div>
			<!-- End Input -->
			<!-- Input -->
			<div>
			
				<p>Content bottom gradient</p>
				<div class="input"><input type="text" value="<?php echo $content_bottom_gradient; ?>" id="content_bottom_gradient" name="content_bottom_gradient" style="width:41px" /></div>
			
			</div>
			<!-- End Input -->
			<!-- Input -->
			<div>
			
				<p>Top and footer, top gradient</p>
				<div class="input"><input type="text" value="<?php echo $top_and_footer_top_gradient; ?>" id="top_and_footer_top_gradient" name="top_and_footer_top_gradient" style="width:41px" /></div>
			
			</div>
			<!-- End Input -->
			<!-- Input -->
			<div>
			
				<p>Top and footer, bottom gradient</p>
				<div class="input"><input type="text" value="<?php echo $top_and_footer_bottom_gradient; ?>" id="top_and_footer_bottom_gradient" name="top_and_footer_bottom_gradient" style="width:41px" /></div>
			
			</div>
			<!-- End Input -->
			<!-- Input -->
			<div>
			
				<p>Sale ribbon, top gradient</p>
				<div class="input"><input type="text" value="<?php echo $sale_ribbon_top_gradient; ?>" id="sale_ribbon_top_gradient" name="sale_ribbon_top_gradient" style="width:41px" /></div>
			
			</div>
			<!-- End Input -->
			<!-- Input -->
			<div>
			
				<p>Sale ribbon, bottom gradient</p>
				<div class="input"><input type="text" value="<?php echo $sale_ribbon_bottom_gradient; ?>" id="sale_ribbon_bottom_gradient" name="sale_ribbon_bottom_gradient" style="width:41px" /></div>
			
			</div>
			<!-- End Input -->
		
		</div>
		
		<!-- End Right -->
		
		<p class="separator"></p>
		
		<!-- LEFT -->
		
		<div class="left">
		
			<h3>Rest elements:</h3>
			<!-- Input -->
			<div>
			
				<p>Content color (ex. Featured products frame)</p>
				<div class="input"><input type="text" value="<?php echo $content_color; ?>" id="content_color" name="content_color" style="width:41px" /></div>
			
			</div>
			<!-- End Input -->
			<!-- Input -->
			<div>
			
				<p>Hover for products and product description frame</p>
				<div class="input"><input type="text" value="<?php echo $hover_for_products; ?>" id="hover_for_products" name="hover_for_products" style="width:41px" /></div>
			
			</div>
			<!-- End Input -->
			<!-- Input -->
			<div>
			
				<p>Categories in top, top gradient</p>
				<div class="input"><input type="text" value="<?php echo $categories_in_top_top_gradient; ?>" id="categories_in_top_top_gradient" name="categories_in_top_top_gradient" style="width:41px" /></div>
			
			</div>
			<!-- End Input -->
			<!-- Input -->
			<div>
			
				<p>Categories in top, bottom gradient</p>
				<div class="input"><input type="text" value="<?php echo $categories_in_top_bottom_gradient; ?>" id="categories_in_top_bottom_gradient" name="categories_in_top_bottom_gradient" style="width:41px" /></div>
			
			</div>
			<!-- End Input -->
			<!-- Input -->
			<div>
			
				<p>Top, top gradient</p>
				<div class="input"><input type="text" value="<?php echo $top_top_gradient; ?>" id="top_top_gradient" name="top_top_gradient" style="width:41px" /></div>
			
			</div>
			<!-- End Input -->
			<!-- Input -->
			<div>
			
				<p>Top, bottom gradient</p>
				<div class="input"><input type="text" value="<?php echo $top_bottom_gradient; ?>" id="top_bottom_gradient" name="top_bottom_gradient" style="width:41px" /></div>
			
			</div>
			<!-- End Input -->
			<!-- Input -->
			<div>
			
				<p>Bar in top, top gradient</p>
				<div class="input"><input type="text" value="<?php echo $bar_in_top_top_gradient; ?>" id="bar_in_top_top_gradient" name="bar_in_top_top_gradient" style="width:41px" /></div>
			
			</div>
			<!-- End Input -->
			<!-- Input -->
			<div>
			
				<p>Bar in top, bottom gradient</p>
				<div class="input"><input type="text" value="<?php echo $bar_in_top_bottom_gradient; ?>" id="bar_in_top_bottom_gradient" name="bar_in_top_bottom_gradient" style="width:41px" /></div>
			
			</div>
			<!-- End Input -->
		
		</div>
		
		<!-- END Left -->
		
		<!-- RIGHT -->
		
		<div class="right">
		
			<h3>Background color:</h3>
			<!-- Input -->
			<div>
			
				<p>General</p>
				<div class="input"><input type="text" value="<?php echo $bg_general; ?>" id="bg_general" name="bg_general" style="width:41px" /></div>
			
			</div>
			<!-- End Input -->
			<!-- Input -->
			<div>
			
				<p>Footer</p>
				<div class="input"><input type="text" value="<?php echo $bg_footer; ?>" id="bg_footer" name="bg_footer" style="width:41px" /></div>
			
			</div>
			<!-- End Input -->
			<!-- Input -->
			<div>
			
				<p>Content</p>
				<div class="input"><input type="text" value="<?php echo $bg_content; ?>" id="bg_content" name="bg_content" style="width:41px" /></div>
			
			</div>
			<!-- End Input -->
			<h3>Background pattern</h3>
			<!-- Input -->
			<div>
			
				<p>Body pattern</p>
				<div class="input"><select name="body_pattern" style="width:170px"><option value="-1"<?php if($body_pattern < 0) { echo ' selected="selected"'; } ?>>Standard</option><option value="100"<?php if($body_pattern == '100') { echo ' selected="selected"'; } ?>>Own</option><option value="0"<?php if($body_pattern == 0) { echo ' selected="selected"'; } ?>>No pattern</option><option value="4"<?php if($body_pattern == 4) { echo ' selected="selected"'; } ?>>1</option><option value="5"<?php if($body_pattern == 5) { echo ' selected="selected"'; } ?>>2</option><option value="6"<?php if($body_pattern == 6) { echo ' selected="selected"'; } ?>>3</option><option value="7"<?php if($body_pattern == 7) { echo ' selected="selected"'; } ?>>4</option><option value="8"<?php if($body_pattern == 8) { echo ' selected="selected"'; } ?>>5</option><option value="9"<?php if($body_pattern == 9) { echo ' selected="selected"'; } ?>>6</option><option value="10"<?php if($body_pattern == 10) { echo ' selected="selected"'; } ?>>7</option><option value="11"<?php if($body_pattern == 11) { echo ' selected="selected"'; } ?>>8</option><option value="12"<?php if($body_pattern == 12) { echo ' selected="selected"'; } ?>>9</option><option value="13"<?php if($body_pattern == 13) { echo ' selected="selected"'; } ?>>10</option></select></div>
			
			</div>
			<!-- End Input -->
			<!-- Input -->
			<div>
			
				<p>Own pattern</p>
				<div class="input">
				
					<div class="own_pattern" onclick="image_upload('own_pattern', 'preview1');">
					
						<input type="hidden" name="own_pattern" value="<?php echo $own_pattern; ?>" id="own_pattern" />
						<img src="../image/<?php echo $own_pattern; ?>" alt="" id="preview1" />
					
					</div>
				
				</div>
			
			</div>
			<!-- End Input -->
			<h3>Background image</h3>
			<!-- Input -->
			<div style="float:left;width:90px">
				
				<div class="own_image" onclick="image_upload('own_image', 'preview');">
				
					<input type="hidden" name="own_image" value="<?php echo $own_image; ?>" id="own_image" />
					<img src="../image/<?php echo $own_image; ?>" alt="" id="preview" />
				
				</div>
			
			</div>
			<div style="clear:none;width:280px;float:left;">
				
				<p>Background:</p>
				<div class="input"><select name="background_image" style="width:164px;"><option value="0"<?php if($background_image == 0) { echo ' selected="selected"'; } ?>>Standard</option><option value="1"<?php if($background_image == 1) { echo ' selected="selected"'; } ?>>None</option><option value="2"<?php if($background_image == 2) { echo ' selected="selected"'; } ?>>Own</option></select></div>
				<p style="float:none;font-size:1px;line-height:1px;height:13px;clear:both;margin:0px;padding:0px;"></p>
				<p>Position:</p>
				<div class="input"><select name="background_image_position" style="width:164px;"><option value="top left"<?php if($background_image_position == 'top left') { echo ' selected="selected"'; } ?>>Top left</option><option value="top center"<?php if($background_image_position == 'top center') { echo ' selected="selected"'; } ?>>Top center</option><option value="top right"<?php if($background_image_position == 'top right') { echo ' selected="selected"'; } ?>>Top right</option><option value="bottom left"<?php if($background_image_position == 'bottom left') { echo ' selected="selected"'; } ?>>Bottom left</option><option value="bottom center"<?php if($background_image_position == 'bottom center') { echo ' selected="selected"'; } ?>>Bottom center</option><option value="bottom right"<?php if($background_image_position == 'bottom right') { echo ' selected="selected"'; } ?>>Bottom right</option></select></div>
				<p style="float:none;font-size:1px;line-height:1px;height:13px;clear:both;margin:0px;padding:0px;"></p>
				<p>Repeat:</p>
				<div class="input"><select name="background_image_repeat" style="width:164px;"><option value="no-repeat"<?php if($background_image_repeat == 'no-repeat') { echo ' selected="selected"'; } ?>>no-repeat</option><option value="repeat-x"<?php if($background_image_repeat == 'repeat-x') { echo ' selected="selected"'; } ?>>repeat-x</option><option value="repeat-y"<?php if($background_image_repeat == 'repeat-y') { echo ' selected="selected"'; } ?>>repeat-y</option></select></div>
				<p style="float:none;font-size:1px;line-height:1px;height:13px;clear:both;margin:0px;padding:0px;"></p>
				<p>Attachment:</p>
				<div class="input"><select name="background_image_attachment" style="width:164px;"><option value="scroll"<?php if($background_image_attachment == 'scroll') { echo ' selected="selected"'; } ?>>scroll</option><option value="fixed"<?php if($background_image_attachment == 'fixed') { echo ' selected="selected"'; } ?>>fixed</option></select></div>
							
			</div>
			<!-- End Input -->
		
		</div>
		
		<!-- End Right -->
		
		<p class="separator"></p>
		
		<div class="buttons"><a onclick="$('#form').submit();" class="button-save"><span><?php echo $button_save; ?></span></a><a onclick="location = '<?php echo $cancel; ?>';" class="button-cancel"><span><?php echo $button_cancel; ?></span></a></div>
	
	</form>
	</div>
		
</div>
</div>
</div>

<!-- Uni-store Theme Options -->

</div>

<script type="text/javascript">
jQuery(document).ready(function($) {

	$(".select-color-settings ul li a").click(function () {
		
		var styl = $(this).attr("rel");
		var element_index = $('.select-color-settings ul li a').index(this);
		$(".select-color-settings ul li a").removeClass("active");
		$(".select-color-settings ul li a").eq(element_index).addClass("active");
		$("#unistore_color").val(styl);
		
	});

});	
</script>

<script type="text/javascript">

$(document).ready(function() {

	$('#body_text_content, #body_text_footer, #new_price_and_footer_contact, #headlines_content, #headlines_footer, #product_name_and_categories_on_subpages, #categories_in_top, #text_in_top, #content_top_gradient, #content_bottom_gradient, #top_and_footer_top_gradient, #top_and_footer_bottom_gradient, #sale_ribbon_top_gradient, #sale_ribbon_bottom_gradient, #hover_for_products, #content_color, #categories_in_top_top_gradient, #categories_in_top_bottom_gradient, #top_top_gradient, #top_bottom_gradient, #bar_in_top_top_gradient, #bar_in_top_bottom_gradient, #bg_general, #bg_footer, #bg_content, #text_in_bar_in_top').ColorPicker({
		onSubmit: function(hsb, hex, rgb, el) {
			$(el).val(hex);
			$(el).ColorPickerHide();
		},
		onBeforeShow: function () {
			$(this).ColorPickerSetColor(this.value);
		}
	})
	.bind('keyup', function(){
		$(this).ColorPickerSetColor(this.value);
	});
	 });
</script>
<script type="text/javascript"><!--
function image_upload(field, thumb) {
    $.startImageManager(field, thumb);
};
//--></script> 
<script type="text/javascript">
<?php echo $footer; ?>