<?php if(in_array(ucfirst('google map location'),$seller_show_fields)): ?>

	<tr>
		<td><?php if(in_array(ucfirst('google map location'),$seller_required_fields)): ?>
			<span class="required">*</span>
			<?php endif; ?> <?php echo $ms_account_sellerinfo_google_map_location; ?></td>
		<td>

    		<div id="google_map_location" style="width: 100%; height: 400px;"></div>

			<input type="hidden" id="coordinates" name="seller[seller_location]" value="<?php echo $seller['ms.google_map_location']; ?>"  class="form-control"/>
           
<!--             <input type="text" class="form-control map_coordinates" name="latitude" id="lat">
            <input type="text" class="form-control map_coordinates" name="longitude" id="long">
 -->
			<p class="ms-note">
				<?php echo $ms_account_sellerinfo_google_map_location_note; ?>
				<?php echo in_array(ucfirst('google map location'),$seller_required_fields) ? $ms_required : $ms_optional; ?>  		
			</p>
			<?php if ($error_seller_seller_location) { ?>
             <span class="error"><?php echo $error_seller_seller_location; ?></span>
            <?php } ?>
		</td>
	</tr>
<script>
   // In the following example, markers appear when the user clicks on the map.
    // The markers are stored in an array.
    var map;
    var markers = [];

    function initMap(){
        intLat = -25.344;
        initLog = 131.036;
        geocoder = new google.maps.Geocoder();
        var haightAshbury = {lat: intLat , lng: initLog};
        var zoom = 10;
        if (intLat === 0 && initLog === 0) {
            zoom = 3;
        }
        map = new google.maps.Map(document.getElementById('google_map_location'), {
            zoom: zoom,                        // Set the zoom level manually
            center: haightAshbury,
            mapTypeId: 'terrain'
        });
        addMarker(haightAshbury);
        // This event listener will call addMarker() when the map is clicked.
        map.addListener('click', function (event) {
            // alert(event.latLng)

            console.log(event.latLng);
            if (markers.length >= 1) {
                deleteMarkers();
            }
            addMarker(event.latLng);
            document.getElementById('coordinates').value = event.latLng.lat() + ',' + event.latLng.lng();
            // document.getElementById('lat').value = event.latLng.lat();
            // document.getElementById('long').value = event.latLng.lng();

        });
    }

    // Adds a marker to the map and push to the array.
    function addMarker(location, zoom=3) {
        console.log(location)
        var marker = new google.maps.Marker({
            position: location,
            map: map
        });

        map.setZoom(zoom);
        map.panTo(marker.position);

        markers.push(marker);
    }

    // Sets the map on all markers in the array.
    function setMapOnAll(map) {
        for (var i = 0; i < markers.length; i++) {
            markers[i].setMap(map);
        }
    }

    // Removes the markers from the map, but keeps them in the array.
    function clearMarkers() {
        setMapOnAll(null);
    }

    // Deletes all markers in the array by removing references to them.
    function deleteMarkers() {
        clearMarkers();
        markers = [];
    }
</script>
<script defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo $msconf_seller_google_api_key; ?>&callback=initMap"></script>
<?php endif; ?>
