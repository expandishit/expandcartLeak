<?php if ($is_address_data_enabled):?>
<div class="row ms-address">
    <div class="col-md-12">
        <h2 class="col-md-12 form__header">
            <?php echo $text_your_address; ?>
            <input type="hidden" name="address_id" value="<?php echo $address_id;?>">
        </h2>
    </div>

   
    <div class="col-md-12">
        <?php if ((int)$address_fields['location'] >= 0 && in_array(ucfirst('google map location'),$seller_show_fields) ): ?>
        <div class="col-md-12">
            <div class="form-group" id="location-group">
                <!-- <label for="ms-location">
                    <?php echo $entry_location;?>
                    <?php if ((int) $address_fields['location'] === 1): ?>
                    <span class="text-danger">*</span>
                    <?php endif ?>
                </label> -->
                <input type="hidden" id="ms-location" value="<?php echo $location; ?>" name="location" />
                <style>
                    #map {
                        height: 200px;
                        width: 100%;
                    }
        
                    #map .controls {
                        margin-top: 10px;
                        border: 1px solid transparent;
                        border-radius: 2px 0 0 2px;
                        box-sizing: border-box;
                        -moz-box-sizing: border-box;
                        height: 32px;
                        outline: none;
                        box-shadow: 0 2px 6px rgb(0 0 0 / 30%);
                    }
        
                    #pac-input {
                        background-color: #fff;
                        font-family: Roboto;
                        font-size: 15px;
                        font-weight: 300;
                        margin-left: 12px;
                        margin-right: 12px;
                        padding: 0 11px 0 13px;
                        text-overflow: ellipsis;
                        width: 300px;
                    }
        
                    .pac-container {
                        z-index: 9999999999999999;
                    }
                    .loginjs__modal-container .modal .modal-dialog{
                        left: 0;
                    }
                </style>
                <div id="map">
                    location map...
                </div>
                <?php if ($error_location): ?>
                    <span class="error"><?php echo $error_location; ?></span>
                <?php endif ?>
            </div>
        </div>
        <?php endif ?>
        <div class="col-md-6">
            <div class="form-group">
                <label for="ms-country_id">
                    <?php echo $entry_country; ?>
                    <span class="required">*</span>
                </label>
                <select name="country_id" id="ms-country_id" class="form-control">
                    <option value="">
                        <?php echo $text_select; ?>
                    </option>
                    <?php foreach ($countries as $country) { ?>
                    <?php if ($country['country_id'] == $country_id) { ?>
                    <option value="<?php echo $country['country_id']; ?>" data-code="<?php echo $country['iso_code_2']; ?>" selected="selected">
                        <?php echo $country['name']; ?>
                    </option>
                    <?php } else { ?>
                    <option value="<?php echo $country['country_id']; ?>" data-code="<?php echo $country['iso_code_2']; ?>">
                        <?php echo $country['name']; ?>
                    </option>
                    <?php } ?>
                    <?php } ?>
                </select>
                <?php if ($error_country_id) { ?>
                <span class="error">
                    <?php echo $error_country_id; ?>
                </span>
                <?php } ?>
            </div>
        
        </div>
        <div class="col-md-6">
            <?php if((int) $address_fields['zone_id'] >= 0): ?>
            <div class="form-group">
                <label for="ms-zone_id">
                    <?php echo $entry_zone; ?>
                    <?php if((int) $address_fields['zone_id'] === 1): ?>
                    <span class="required">*</span>
                    <?php endif ?>
                </label>
                <select name="zone_id" id="ms-zone_id" class="form-control"></select>
                <?php if ($error_zone_id) { ?>
                <span class="error">
                    <?php echo $error_zone_id; ?>
                </span>
                <?php } ?>
            </div>
            <?php endif ?>
        </div>
        <div class="col-md-6">
            <?php if((int) $address_fields['area_id'] >= 0): ?>
            <div class="form-group">
                <label for="ms-area_id">
                    <?php echo $entry_area_id; ?>
                    <?php if((int) $address_fields['area_id'] === 1): ?>
                    <span class="required">*</span>
                    <?php endif ?>
                </label>
                <select name="area_id" id="ms-area_id" class="form-control"></select>
                <?php if ($error_area_id) { ?>
                <span class="error">
                    <?php echo $error_area_id; ?>
                </span>
                <?php } ?>
            </div>
            <?php endif ?>
        </div>
        <div class="col-md-6">
            <?php if((int) $address_fields['address_1'] >= 0): ?>
            <div class="form-group">
                <label for="ms-address_1">
                    <?php echo $entry_address_1; ?>
                    <?php if((int) $address_fields['address_1'] === 1): ?>
                    <span class="required">*</span>
                    <?php endif ?>
                </label>
                <input type="text" name="address_1" id="ms-address_1" class="form-control" value="<?php echo $address_1; ?>"
                    placeholder="<?php echo $entry_address_1_placeholder; ?>">
                <?php if ($error_address_1): ?>
                <span class="error">
                    <?php echo $error_address_1; ?>
                </span>
                <?php endif ?>
            </div>
            <?php endif ?>
        </div>
        <div class="col-md-6">
            <?php if((int) $address_fields['address_2'] >= 0): ?>
            <div class="form-group">
                <label for="ms-address_2">
                    <?php echo $entry_address_2; ?>
                    <?php if((int) $address_fields['address_2'] === 1): ?>
                    <span class="required">*</span>
                    <?php endif ?>
                </label>
                <input type="text" name="address_2" id="ms-address_2" class="form-control" value="<?php echo $address_2; ?>"
                    placeholder="<?php echo $entry_address_2_placeholder; ?>">
                <?php if ($error_address_2): ?>
                <span class="error">
                    <?php echo $error_address_2; ?>
                </span>
                <?php endif ?>
            </div>
            <?php endif ?>
        </div>
        <div class="col-md-6">
            <?php if((int) $address_fields['postcode'] >= 0): ?>
            <div class="form-group">
                <label for="ms-postcode">
                    <?php echo $entry_postcode; ?>
                    <?php if((int) $address_fields['postcode'] === 1): ?>
                    <span class="required">*</span>
                    <?php endif ?>
                </label>
                <input type="text" name="postcode" id="ms-postcode" class="form-control" value="<?php echo $postcode; ?>"
                    placeholder="<?php echo $entry_postcode_placeholder; ?>">
                <?php if ($error_postcode): ?>
                <span class="error">
                    <?php echo $error_postcode; ?>
                </span>
                <?php endif ?>
            </div>
            <?php endif ?>
        </div>
        <div class="col-md-6">
            <?php if((int) $address_fields['telephone'] >= 0): ?>
            <div class="form-group">
                <label for="cus-shipping-telephone">
                    <?php echo $entry_shipping_telephone; ?>
                    <?php if((int) $address_fields['telephone'] === 1): ?>
                    <span class="required">*</span>
                    <?php endif ?>
                </label>
                <input type="tel" name="shipping_telephone" id="cus-shipping-telephone" class="form-control" value="<?php echo $shipping_telephone; ?>"
                    placeholder="<?php echo $entry_shipping_telephone_placeholder; ?>">
                <?php if ($error_shipping_telephone): ?>
                <span class="error">
                    <?php echo $error_shipping_telephone; ?>
                </span>
                <?php endif ?>
            </div>
            <?php endif ?>
        </div>
    </div>
    
</div>

<script>
var initializeAddressMap = function () {
    function initializeAddressMap(options) {
        var containerSelector = document.querySelector(
            options.containerSelector || "BODY"
        ),
            inputLocation =
                containerSelector &&
                containerSelector.querySelector("input[name=location]"),
            inputAddress =
                containerSelector &&
                containerSelector.querySelector("input[name=address_1]"),
            inputCity =
                containerSelector &&
                containerSelector.querySelector("[name=city]"),
            inputCountry =
                containerSelector &&
                containerSelector.querySelector("[name=country_id]"),
            inputPostalCode =
                containerSelector &&
                containerSelector.querySelector("[name=postcode]"),
            inputAutoComplete,
            buttonGetMyLocation,
            defaultCoords,
            map,
            infoWindow,
            marker,
            geocoder,
            autocomplete,
            defaultZoom,
            styles = [
                {
                    featureType: "all",
                    elementType: "labels",
                    stylers: [
                        {
                            visibility: "on",
                        },
                    ],
                },
            ],
            onClickConfirm;

        inputAutoComplete = document.createElement("INPUT");
        inputAutoComplete.setAttribute("id", "pac-input");
        inputAutoComplete.setAttribute("class", "controls");
        inputAutoComplete.setAttribute(
            "placeholder",
            options.local.enter_your_location || "Enter Your Location"
        );

        buttonGetMyLocation = document.createElement("BUTTON");
        buttonGetMyLocation.setAttribute("id", "pac-location");
        buttonGetMyLocation.setAttribute("class", "controls");
        buttonGetMyLocation.appendChild(
            document.createTextNode(
                options.local.detect_my_location || "Detect My Location"
            )
        );
        
        if(options.shouldConfirmLocation) {
            var btnClass = 'confirm-btn-' + Date.now();
            containerSelector.querySelector('#map').insertAdjacentHTML('afterend', '<div class="confirm_address"><button type="button" class="primary-btn ml-auto ' + btnClass + '">' + (options.local.confirm_location || 'Confirm Location') + '</button></div>');
            document.querySelector('.' + btnClass).addEventListener('click', function() {
                Object.prototype.toString.call(onClickConfirm) === "[object Function]" && onClickConfirm()
            });
        }
        try {
            var locationParts = inputLocation.value.split(',');
            if(Array.isArray(locationParts) && locationParts.length === 2) {
                defaultCoords = {
                    lat: locationParts[0],
                    lng: locationParts[1],
                };
            } else throw new Error('Invalid location string');
        } catch (error) {
            defaultCoords = {
                lat: options.lat || 0,
                lng: options.lng || 0,
            };
        }
        
        defaultCoords.lat = parseFloat(defaultCoords.lat);
        defaultCoords.lng = parseFloat(defaultCoords.lng);

        if (!defaultCoords.lat || isNaN(defaultCoords.lat))
            defaultCoords.lat = 0;

        if (!defaultCoords.lng || isNaN(defaultCoords.lng))
            defaultCoords.lng = 0;
        
        function _browserHasLocation() {
            return navigator.geolocation && window.isSecureContext;
        }

        function _getElementInArray(
            elements = [],
            condition = function () { }
        ) {
            if (!(elements instanceof Array) || !elements.length)
                return null;

            var map = function (elements) {
                if (!elements.length) return null;
                element = elements.splice(0, 1)[0];
                return condition(element) ? element : map(elements);
            },
                element;

            return map(elements.slice(0));
        }

        function _getCurrentUserCoords(event) {
            event.preventDefault();
            event.stopPropagation();
            //check if geolocation is available
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (
                    position
                ) {
                    if (position.coords.latitude) {
                        defaultCoords = {
                            lat: parseFloat(position.coords.latitude),
                            lng: parseFloat(position.coords.longitude),
                        };
                        // infoWindow.setPosition(defaultCoords);
                        // infoWindow.setContent('You are here.');
                        // infoWindow.open(map);
                        map.setCenter(defaultCoords);

                        marker.setPosition(defaultCoords);

                        _geoCodeLatLng(geocoder, map, infoWindow);
                    }
                });
            }
        }

        // eslint-disable-next-line no-unused-vars
        function _geoCodeLatLng(
            geocoder,
            map,
            infoWindow,
            countryOnly = false
        ) {
            geocoder.geocode(
                { location: defaultCoords },
                function (results, status) {
                    if (status === "OK") {
                        if (results[0]) {
                            _setAddress(
                                results[0].formatted_address,
                                results[0].geometry.location.toJSON(),
                                results[0].address_components,
                                countryOnly
                            );

                            // infoWindow.setContent(results[0].formatted_address);
                            // infoWindow.open(map, marker);
                        } else {
                            console.log("No results found");
                        }
                    } else {
                        console.log("Geocoder failed due to: " + status);
                    }
                }
            );
        }

        function _confirmAddress(address, location, address_components, countryOnly = false) {            
            !countryOnly &&
                inputAddress &&
                address &&
                address.length &&
                (inputAddress.value = address);

            inputLocation && location && (inputLocation.value = (location.lat + ',' + location.lng));
            
            Object.prototype.toString.call(options.onConfirmLocation) === "[object Function]" 
                && options.onConfirmLocation({address, location}) 
        
            if (!Array.isArray(address_components)) return;

            // reset fields
            // inputCity && (inputCity.value = "");
            inputCountry && (inputCountry.value = "");
            // inputPostalCode && (inputPostalCode.value = "");

            for (
                let index = 0;
                index < address_components.length;
                index++
            ) {
                const component = address_components[index];
                if (
                    component.types.indexOf("country") !== -1 &&
                    inputCountry &&
                    options.countries
                ) {
                    // country check
                    var country = _getElementInArray(
                        options.countries,
                        function (country) {
                            return (
                                country.iso_code_2.trim().toUpperCase() ===
                                component.short_name.trim().toUpperCase()
                            );
                        }
                    );

                    if (country) {
                        inputCountry.value = country.country_id;
                        $(`select[name=country_id]`).trigger("change"); // required to update zone
                        // set telephone country code
                        var inputs = document.querySelectorAll("input[type=tel]");
                        if(inputs.length && typeof intlTelInput !== "undefined") {
                            inputs.forEach(function(input) {
                                input.iti && input.iti.getNumber().length == 0 && (input.iti.setCountry(country.iso_code_2.trim().toLowerCase()));
                            });
                        }
                    }
                } else if (
                    !countryOnly &&
                    component.types.indexOf(
                        "administrative_area_level_2"
                    ) !== -1 &&
                    inputCity
                ) {
                    // city check
                    inputCity.value = component.long_name;
                } else if (
                    !countryOnly &&
                    component.types.indexOf("postal_code") !== -1 &&
                    inputPostalCode
                ) {
                    // postcode check
                    inputPostalCode.value = component.long_name;
                }
            }
        }
            
        function _setAddress(
            address,
            location,
            address_components,
            countryOnly = false
        ) {
                        
            if(!countryOnly && options.shouldConfirmLocation) {
                onClickConfirm = _confirmAddress.bind(null, address, location, address_components, false);
            } else {
                _confirmAddress( address, location, address_components, countryOnly);
            }
            
        }

        return (function () {
            defaultZoom = 8;
            infoWindow = new google.maps.InfoWindow();
            geocoder = new google.maps.Geocoder();

            map = new google.maps.Map(
                containerSelector.querySelector("#map"),
                {
                    center: defaultCoords,
                    zoom: defaultZoom,
                    panControl: !1,
                    gestureHandling: "greedy",
                    streetViewControl: !1,
                    mapTypeControl: !1,
                    styles: styles,
                }
            );

            marker = new google.maps.Marker({
                position: defaultCoords,
                map: map,
                draggable: true,
                status: "active",
            });

            autocomplete = new google.maps.places.Autocomplete(
                inputAutoComplete
            );

            // geolocation working only secure origin
            if (_browserHasLocation()) {
                map.controls[google.maps.ControlPosition.TOP_LEFT].push(
                    buttonGetMyLocation
                );

                buttonGetMyLocation.onclick = _getCurrentUserCoords;
            }
            
            map.controls[google.maps.ControlPosition.TOP_LEFT].push(
                inputAutoComplete
            );

            // Bind the map's bounds (viewport) property to the autocomplete object,
            // so that the autocomplete requests use the current map bounds for the
            // bounds option in the request.
            autocomplete.bindTo("bounds", map);

            // Set the data fields to return when the user selects a place.
            autocomplete.setFields([
                "address_components",
                "geometry",
                "icon",
                "name",
            ]);

            autocomplete.addListener("place_changed", function () {
                marker.setVisible(false);

                var place = autocomplete.getPlace();

                if (!place.geometry) {
                    // User entered the name of a Place that was not suggested and
                    // pressed the Enter key, or the Place Details request failed.
                    // console.log("No details available for input: '" + place.name + "'");
                    return;
                }

                defaultCoords = place.geometry.location.toJSON();

                // If the place has a geometry, then present it on a map.
                if (place.geometry.viewport) {
                    map.fitBounds(place.geometry.viewport);
                } else {
                    map.setCenter(defaultCoords);
                    map.setZoom(defaultZoom); // Why 17? Because it looks good.
                }

                marker.setPosition(defaultCoords);
                marker.setVisible(true);
                _geoCodeLatLng(geocoder, map, infoWindow);
            });

            map.addListener("click", function (e) {
                defaultCoords = e.latLng.toJSON();
                marker.setPosition(defaultCoords);
                map.setCenter(defaultCoords);
                marker.setVisible(true);
                _geoCodeLatLng(geocoder, map, infoWindow);
            });

            marker.addListener("dragend", function () {
                defaultCoords = marker.getPosition();
                _geoCodeLatLng(geocoder, map, infoWindow);
            });

            google.maps.event.addDomListener(
                inputAutoComplete,
                "keydown",
                function (event) {
                    if (event.keyCode === 13) {
                        event.preventDefault();
                    }
                }
            );

            marker.setMap(map);
            _geoCodeLatLng(geocoder, map, infoWindow, true);
        })();
    }

    typeof google !== "undefined" ?
        initializeAddressMap({
            containerSelector: ".ms-address",
            lat: '<?php echo $address_fields["map"]["lat"]; ?>',
            lng: '<?php echo $address_fields["map"]["lng"]; ?>',
            local: {
                enter_your_location: '<?php echo $entry_location; ?>',
                detect_my_location: '<?php echo $entry_detect_my_location; ?>',
                confirm_location: '<?php echo $entry_confirm_location;?>',
            },
            countries: <?php echo json_encode($countries); ?>,
            shouldConfirmLocation: true,
            onConfirmLocation: function(result) {
                document.getElementById('coordinates') && (document.getElementById('coordinates').value = result.location.lat + ',' + result.location.lng);
            }
        })
        : (function(){
            console.error('unknown google maps cdn');
            var locationArea = document.querySelector('#location-group');
            locationArea && locationArea.remove();
        }());
}

window.addEventListener('DOMContentLoaded', initializeAddressMap);

</script>
<?php endif; ?>
