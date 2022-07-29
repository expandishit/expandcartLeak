(function (global, factory) {
    var g;
    if (typeof window !== "undefined") {
        g = window;
    } else if (typeof global !== "undefined") {
        g = global;
    } else if (typeof self !== "undefined") {
        g = self;
    } else {
        g = this;
    }
    g.checkout = factory(global || {}, document);
})(this, function (w, d) {
    "use strict";
    var {
            currentScript: {
                attributes: { mode, map },
            },
        } = d,
        ux,
        utilities,
        http,
        validator,
        hooks,
        addressFields,
        elAddressFormTemplate,
        elAddressMapTemplate,
        elAddAddressContainer,
        elShipping,
        elShippingTitle,
        elPayment,
        elPaymentTitle,
        elOrderSummary,
        initializeAddressMap,
        renderAddressMap,
        currentGeoIPLockup,
        initializeTelInput,
        gMapInstance;

    mode =
        typeof mode === "undefined"
            ? "3Steps"
            : mode.value == "3"
            ? "3Steps"
            : "onePage";

    map = typeof map === "undefined" ? { status: 0 } : JSON.parse(map.value);

    addressFields = [
        {
            element: d.querySelector("[name=address_id]"),
            reset: function () {
                this.element.value = "0";
            },
            set: function (value) {
                this.element.value = value;
            },
        },
        {
            element: d.querySelector("[name=country_id]"),
            reset: function (toDefault = false) {
                this.element.value = "";
                if (toDefault) {
                    currentGeoIPLockup((myIpObj) => {
                        let countryCode = (myIpObj.country || "").toUpperCase();
                        Array.from(this.element.options).forEach(
                            (value, index) => {
                                if (
                                    this.element.options[index].dataset.code ==
                                    countryCode
                                ) {
                                    this.element.selectedIndex = index;
                                    utilities.triggerEvent(
                                        "change",
                                        this.element
                                    );
                                }
                            }
                        );
                    });
                }
            },
            set: function (value) {
                this.element.value = value;
            },
        },
        {
            element: d.querySelector("[name=zone_id]"),
            reset: function () {
                this.element.value = "";
                this.element.options.length = 1;
                return this;
            },
            set: function (zones, selected = null) {
                selected = selected || this.element.value;
                this.reset();
                zones.forEach((zone) => {
                    this.element.options.add(
                        new Option(
                            zone.name,
                            zone.zone_id,
                            false,
                            selected == zone.zone_id
                        )
                    );
                });
            },
        },
        {
            element: d.querySelector("[name=area_id]"),
            reset: function () {
                this.element.value = "";
                this.element.options.length = 1;
                return this;
            },
            set: function (areas, selected = null) {
                selected = selected || this.element.value;
                this.reset();
                areas.forEach((area) => {
                    this.element.options.add(
                        new Option(
                            area.name,
                            area.area_id,
                            false,
                            selected == area.area_id
                        )
                    );
                });
            },
        },
        {
            element: d.querySelector("[name=address_1]"),
            reset: function () {
                this.element.value = "";
            },
            set: function (value) {
                this.element.value = value;
            },
        },
        {
            element: d.querySelector("[name=address_2]"),
            reset: function () {
                this.element.value = "";
            },
            set: function (value) {
                this.element.value = value;
            },
        },
        {
            element: d.querySelector("[name=postcode]"),
            reset: function () {
                this.element.value = "";
            },
            set: function (value) {
                this.element.value = value;
            },
        },
        {
            element: d.querySelector("[name=iso_code_2]"),
            reset: function () {
                this.element.value = "";
            },
            set: function (value) {
                this.element.value = value;
            },
        },
        {
            element: d.querySelector("[name=phonecode]"),
            reset: function () {
                this.element.value = "";
            },
            set: function (value) {
                this.element.value = value;
            },
        },
        {
            element: d.querySelector("[name=location]"),
            reset: function () {
                this.element.value = "";
            },
            set: function (value) {
                this.element.value = value;
            },
        },
        {
            element: d.querySelector("[name=country]"),
            reset: function () {
                this.element.value = "";
            },
            set: function (value) {
                this.element.value = value;
            },
        },
        {
            element: d.querySelector("[name=zone]"),
            reset: function () {
                this.element.value = "";
            },
            set: function (value) {
                this.element.value = value;
            },
        },
        {
            element: d.querySelector("[name=area]"),
            reset: function () {
                this.element.value = "";
            },
            set: function (value) {
                this.element.value = value;
            },
        },
        {
            element: d.querySelector("[name=short_address]"),
            reset: function () {
                this.element.value = "";
            },
            set: function (value) {
                this.element.value = value;
            },
        },
    ];

    elAddressFormTemplate = d.querySelector("#address-fields-template");
    elAddressMapTemplate = d.querySelector("#address-map-template");
    elAddAddressContainer = d.querySelector(".shipping__address-info");
    initializeAddressMap = function () {
        function initializeAddressMap(options) {
            var containerSelector = d.querySelector(options.containerSelector),
                inputLocation =
                    containerSelector &&
                    containerSelector.querySelector("input[name='location']"),
                inputAddress =
                    containerSelector &&
                    containerSelector.querySelector("input[name='address_1']"),
                inputPostalCode =
                    containerSelector &&
                    containerSelector.querySelector("[name='postcode']"),
                mapDiv =
                    containerSelector &&
                    containerSelector.querySelector("#map"),
                inputAutoComplete,
                buttonGetMyLocation,
                defaultCoords,
                map,
                infoWindow,
                marker,
                geocoder,
                autocomplete,
                defaultZoom,
                mapMaXZoomService,
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

            if (!mapDiv) return false; // #map div not found

            mapDiv.innerHTML = "";

            inputAutoComplete = d.createElement("INPUT");
            inputAutoComplete.setAttribute("id", "pac-input");
            inputAutoComplete.setAttribute("class", "controls");
            inputAutoComplete.setAttribute(
                "placeholder",
                options.local.text_enter_your_location || "Enter Your Location"
            );

            buttonGetMyLocation = d.createElement("BUTTON");
            buttonGetMyLocation.setAttribute("id", "pac-location");
            buttonGetMyLocation.setAttribute("class", "controls");
            buttonGetMyLocation.setAttribute(
                "title",
                options.local.text_detect_location || "Detect My Location"
            );
            let fontAwesome = d.createElement("i");
            fontAwesome.className = "fas fa-map-pin";
            buttonGetMyLocation.appendChild(fontAwesome);
            // buttonGetMyLocation.appendChild(
            //     d.createTextNode(
            //         options.local.text_detect_location ||
            //             "Detect My Location"
            //     )
            // );

            if (options.shouldConfirmLocation) {
                if (!containerSelector.querySelector(".confirm_address")) {
                    var btnClass = "confirm-btn-" + Date.now();
                    mapDiv.insertAdjacentHTML(
                        "afterend",
                        '<button type="button" class="map__confirm-location ' +
                            btnClass +
                            '">' +
                            (options.local.text_confirm_location ||
                                "Confirm Location") +
                            "</button>"
                    );

                    utilities.addEventListener(
                        d.querySelector("." + btnClass),
                        "click",
                        function () {
                            utilities.isFunction(onClickConfirm) &&
                                onClickConfirm();
                        }
                    );
                }
            }

            try {
                var locationParts = inputLocation.value.split(",");
                if (
                    Array.isArray(locationParts) &&
                    locationParts.length === 2
                ) {
                    defaultCoords = {
                        lat: locationParts[0],
                        lng: locationParts[1],
                    };
                } else throw new Error("Invalid location string");
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
                return navigator.geolocation && w.isSecureContext;
            }

            function _getElementInArray(
                elements = [],
                condition = function () {}
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
                event = event || w.event || {};
                event.preventDefault && event.preventDefault();
                event.stopPropagation && event.stopPropagation();
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
                            mapMaXZoomService =
                                mapMaXZoomService ||
                                new google.maps.MaxZoomService();
                            mapMaXZoomService.getMaxZoomAtLatLng(
                                defaultCoords,
                                function (r) {
                                    var maxZoom =
                                        r.status === "OK" && r.zoom > 4
                                            ? r.zoom
                                            : 20;
                                    defaultZoom = parseInt(maxZoom - 4);
                                    map.setCenter(defaultCoords);
                                    map.setZoom(defaultZoom);
                                    marker.setPosition(defaultCoords);
                                    _geoCodeLatLng(geocoder, map, infoWindow);
                                }
                            );
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
                            } else {
                                utilities.log("no results found");
                            }
                        } else {
                            utilities.log("geocoder failed due to: " + status);
                        }
                    }
                );
            }

            async function _confirmAddress(
                address,
                location,
                address_components,
                countryOnly = false
            ) {
                !countryOnly &&
                    inputAddress &&
                    address &&
                    address.length &&
                    ((inputAddress.value = address),
                    utilities.triggerEvent("blur", inputAddress));

                inputLocation &&
                    location &&
                    (inputLocation.value = location.lat + "," + location.lng);

                Object.prototype.toString.call(options.onConfirmLocation) ===
                    "[object Function]" &&
                    options.onConfirmLocation({
                        address,
                        location,
                    });

                if (!Array.isArray(address_components)) return;

                // reset fields
                // inputRegion && (inputRegion.value = "");
                // inputCity && (inputCity.value = "");

                // postcode
                var postcodeComponent = _getElementInArray(
                    address_components,
                    function (el) {
                        return el.types.indexOf("postal_code") !== -1;
                    }
                );

                if (!countryOnly && postcodeComponent && inputPostalCode) {
                    inputPostalCode.value = postcodeComponent.long_name;
                    utilities.triggerEvent("blur", inputPostalCode);
                }

                var countryElement = addressFields.filter(
                    (f) => f.element && f.element.name == "country_id"
                )[0];

                var stateElement = addressFields.filter(
                    (f) => f.element && f.element.name == "zone_id"
                )[0];

                var areaElement = addressFields.filter(
                    (f) => f.element && f.element.name == "area_id"
                )[0];

                countryElement && countryElement.reset();
                stateElement && stateElement.reset();
                areaElement && areaElement.reset();

                // Country
                var countryComponent = _getElementInArray(
                        address_components,
                        function (el) {
                            return el.types.indexOf("country") !== -1;
                        }
                    ),
                    countryStatus = 0;

                if (countryComponent && countryElement) {
                    // country check
                    var country = _getElementInArray(
                        Array.from(countryElement.element.options),
                        function (option) {
                            return (
                                (option.dataset.code || "")
                                    .trim()
                                    .toUpperCase() ===
                                countryComponent.short_name.trim().toUpperCase()
                            );
                        }
                    );

                    if (
                        country &&
                        countryElement.element.value != country.value
                    ) {
                        countryStatus = 1;
                        countryElement.set(country.value);
                        utilities.triggerEvent(
                            "change",
                            countryElement.element
                        );
                        await utilities.wait(2000);
                    }
                }

                // Government / State
                var stateStatus = 0;

                if (!countryOnly && countryStatus && stateElement) {
                    var governmentStateComponent = _getElementInArray(
                        address_components,
                        function (el) {
                            return (
                                el.types.indexOf(
                                    "administrative_area_level_1"
                                ) !== -1
                            );
                        }
                    );

                    if (governmentStateComponent) {
                        governmentStateComponent.short_name =
                            governmentStateComponent.short_name.trim();
                        var state = _getElementInArray(
                            Array.from(stateElement.element.options),
                            (el) => {
                                return (
                                    utilities.similarity(
                                        governmentStateComponent.short_name,
                                        el.innerText.trim()
                                    ) > 0.45
                                );
                            }
                        );

                        if (
                            state &&
                            stateElement.element.value != state.value
                        ) {
                            stateStatus = 1;
                            stateElement.element.value = state.value;
                            utilities.triggerEvent(
                                "change",
                                stateElement.element
                            );
                            await utilities.wait(2000);
                        }
                    }
                }

                // City / Area
                if (
                    !countryOnly &&
                    countryStatus &&
                    stateStatus &&
                    areaElement
                ) {
                    var areaComponent = _getElementInArray(
                        address_components,
                        function (el) {
                            return (
                                el.types.indexOf(
                                    "administrative_area_level_2"
                                ) !== -1
                            );
                        }
                    );

                    if (areaComponent) {
                        areaComponent.short_name =
                            areaComponent.short_name.trim();
                        var area = _getElementInArray(
                            Array.from(areaElement.element.options),
                            (el) => {
                                return (
                                    utilities.similarity(
                                        areaComponent.short_name,
                                        el.innerText.trim()
                                    ) > 0.45
                                );
                            }
                        );

                        if (area && areaElement.element.value != area.value) {
                            areaElement.element.value = area.value;
                            utilities.triggerEvent(
                                "change",
                                areaElement.element
                            );
                        }
                    }
                }

                return true;
            }

            function _setAddress(
                address,
                location,
                address_components,
                countryOnly = false
            ) {
                if (countryOnly) {
                    _confirmAddress(
                        address,
                        location,
                        address_components,
                        countryOnly
                    );
                }

                if (options.shouldConfirmLocation) {
                    onClickConfirm = _confirmAddress.bind(
                        null,
                        address,
                        location,
                        address_components,
                        false
                    );
                } else {
                    _confirmAddress(
                        address,
                        location,
                        address_components,
                        countryOnly
                    );
                }
            }
            return (function () {
                infoWindow = new google.maps.InfoWindow();
                geocoder = new google.maps.Geocoder();
                mapMaXZoomService =
                    mapMaXZoomService || new google.maps.MaxZoomService();
                mapMaXZoomService.getMaxZoomAtLatLng(
                    defaultCoords,
                    function (r) {
                        var maxZoom = r.status === "OK" ? r.zoom : 20;
                        defaultZoom = parseInt(maxZoom / 2.5);
                        map = new google.maps.Map(mapDiv, {
                            center: defaultCoords,
                            zoom: defaultZoom,
                            panControl: false,
                            gestureHandling: "greedy",
                            streetViewControl: !1,
                            styles: styles,
                            mapTypeControl: !1,
                        });
                        marker = new google.maps.Marker({
                            position: defaultCoords,
                            map: map,
                            draggable: true,
                            status: "active",
                        });
                        autocomplete = new google.maps.places.Autocomplete(
                            inputAutoComplete
                        );
                        map.controls[google.maps.ControlPosition.TOP_LEFT].push(
                            inputAutoComplete
                        );
                        // geolocation working only secure origin
                        if (_browserHasLocation()) {
                            map.controls[
                                google.maps.ControlPosition.TOP_LEFT
                            ].push(buttonGetMyLocation);

                            buttonGetMyLocation.onclick = _getCurrentUserCoords;
                        }
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
                                event = event || w.event;

                                if (event.keyCode === 13) {
                                    event.preventDefault &&
                                        event.preventDefault();
                                }
                            }
                        );
                        marker.setMap(map);

                        // _geoCodeLatLng(
                        //     geocoder,
                        //     map,
                        //     infoWindow,
                        //     true
                        // );
                    } // callback success
                );
                return {
                    setDefaultCoords: function (coords) {
                        var locationParts = coords.split(",");
                        if (
                            Array.isArray(locationParts) &&
                            locationParts.length === 2
                        ) {
                            defaultCoords = {
                                lat: locationParts[0],
                                lng: locationParts[1],
                            };
                            if (!defaultCoords.lat) defaultCoords.lat = 0;
                            if (!defaultCoords.lng) defaultCoords.lng = 0;
                            defaultCoords.lat = parseFloat(defaultCoords.lat);
                            defaultCoords.lng = parseFloat(defaultCoords.lng);
                            mapMaXZoomService =
                                mapMaXZoomService ||
                                new google.maps.MaxZoomService();
                            mapMaXZoomService.getMaxZoomAtLatLng(
                                defaultCoords,
                                function (r) {
                                    var maxZoom =
                                        r.status === "OK" ? r.zoom : 20;
                                    defaultZoom = parseInt(3 * (maxZoom / 4));
                                    defaultZoom = parseInt(
                                        map.lat == defaultCoords.lat &&
                                            map.lng == defaultCoords.lng
                                            ? maxZoom / 2.5
                                            : 3 * (maxZoom / 4)
                                    );
                                    marker.setVisible(false);
                                    map.setCenter(defaultCoords);
                                    map.setZoom(defaultZoom);
                                    marker.setPosition(defaultCoords);
                                    marker.setVisible(true);
                                    _geoCodeLatLng(geocoder, map, infoWindow);
                                }
                            );
                        }
                    },
                };
            })();
        }

        return typeof google !== "undefined"
            ? initializeAddressMap({
                  containerSelector: "#information",
                  lat: parseFloat(map.lat),
                  lng: parseFloat(map.lng),
                  local: map.local || {},
                  shouldConfirmLocation: true,
                  onConfirmLocation: function () {},
              })
            : {};
    };
    renderAddressMap = (function () {
        const MAP_THUMB_STYLE = [
            {
                featureType: "water",
                elementType: "geometry",
                stylers: [
                    {
                        color: "#b1b1ff",
                    },
                    {
                        lightness: 17,
                    },
                ],
            },
            {
                featureType: "landscape",
                elementType: "geometry",
                stylers: [
                    {
                        color: "#ffffff",
                    },
                    {
                        lightness: 10,
                    },
                ],
            },
            {
                featureType: "road.highway",
                elementType: "geometry.fill",
                stylers: [
                    {
                        visibility: "on",
                    },
                ],
            },
            {
                featureType: "road.highway",
                elementType: "geometry.stroke",
                stylers: [
                    {
                        visibility: "on",
                    },
                ],
            },
            {
                featureType: "road.arterial",
                elementType: "geometry",
                stylers: [
                    {
                        visibility: "on",
                    },
                ],
            },
            {
                featureType: "road.local",
                elementType: "geometry",
                stylers: [
                    {
                        visibility: "off",
                    },
                ],
            },
            {
                featureType: "poi",
                elementType: "geometry",
                stylers: [
                    {
                        visibility: "off",
                    },
                ],
            },
            {
                featureType: "poi.park",
                elementType: "geometry",
                stylers: [
                    {
                        visibility: "off",
                    },
                ],
            },
            {
                elementType: "labels.text.stroke",
                stylers: [
                    {
                        visibility: "off",
                    },
                ],
            },
            {
                elementType: "labels.text.fill",
                stylers: [
                    {
                        visibility: "on",
                    },
                    {
                        color: "#79afb7",
                    },
                    {
                        lightness: 1,
                    },
                ],
            },
            {
                elementType: "labels.icon",
                stylers: [
                    {
                        visibility: "off",
                    },
                ],
            },
            {
                featureType: "transit",
                elementType: "geometry",
                stylers: [
                    {
                        visibility: "off",
                    },
                ],
            },
            {
                featureType: "administrative",
                elementType: "geometry.fill",
                stylers: [
                    {
                        visibility: "off",
                    },
                ],
            },
            {
                featureType: "administrative",
                elementType: "geometry.stroke",
                stylers: [
                    {
                        visibility: "on",
                    },
                ],
            },
        ];

        function errorRenderMap(mapBox) {}

        return function (mapBox) {
            if (!map || map.status == 0) return false;

            if (!mapBox) return false;

            if (typeof google === "undefined") return errorRenderMap(mapBox); // undefined google map cdn

            var locationParts = (
                mapBox.getAttribute("data-location") || ""
            ).split(",");

            if (locationParts.length !== 2) {
                locationParts = [map.lat || 0, map.lng || 0];
            }

            var coords = {
                lat: locationParts[0],
                lng: locationParts[1],
            };

            if (!coords.lat) coords.lat = 0;
            if (!coords.lng) coords.lng = 0;

            coords.lat = parseFloat(coords.lat);
            coords.lng = parseFloat(coords.lng);

            if (isNaN(coords.lat)) coords.lat = 0;
            if (isNaN(coords.lng)) coords.lng = 0;

            var mapContainer = mapBox.closest(".saved-address-container");
            if (!mapContainer) return false;
            mapContainer = mapContainer.querySelector(".mapouter");
            if (!mapContainer) return false;

            var mapMaXZoomService = new google.maps.MaxZoomService();

            mapMaXZoomService.getMaxZoomAtLatLng(coords, function (r) {
                var maxZoom = r.status === "OK" ? r.zoom : 20;
                var minZoom = parseInt(maxZoom / 2.2);
                var mapThumbOptions = {
                    zoom: minZoom,
                    center: coords,
                    disableDefaultUI: true,
                    draggable: false,
                    center: new google.maps.LatLng(coords.lat, coords.lng),
                    mapTypeId: google.maps.MapTypeId.TERRAIN,
                    styles: MAP_THUMB_STYLE,
                };

                var mapThumb = new google.maps.Map(
                    mapContainer,
                    mapThumbOptions
                );

                new google.maps.Marker({
                    position: new google.maps.LatLng(coords.lat, coords.lng),
                    map: mapThumb,
                });
            });
        };
    })();
    currentGeoIPLockup = function (callback) {
        if (w.geoIpLookup) return callback(w.geoIpLookup);
        $.get("https://ipinfo.io", function () {}, "jsonp").always(function (
            resp
        ) {
            w.geoIpLookup = resp;
            utilities.isFunction(callback) && callback(resp);
        });
    };
    initializeTelInput = function () {
        var inputs = d.querySelectorAll(".tel");
        inputs.forEach(function (input) {
            w.intlTelInput(input, {
                initialCountry: "auto",
                nationalMode: true,
                separateDialCode: !true,
                autoPlaceholder: "aggressive",
                formatOnDisplay: true,
                preferredCountries: [],
                responsiveDropdown: true,
                placeholderNumberType: "MOBILE",
                geoIpLookup: function (callback) {
                    return currentGeoIPLockup((resp) => {
                        var countryCode =
                            resp && resp.country ? resp.country : "us";
                        callback(countryCode);
                        var countrySelect =
                            d.querySelector("[name=country_id]");
                        if (!countrySelect || countrySelect.value.length)
                            return;
                        var countryOption = countrySelect.querySelector(
                            'option[data-code="' +
                                countryCode.toUpperCase() +
                                '"]'
                        );
                        if (!countryOption) return;
                        var index = Array.from(countrySelect.options).indexOf(
                            countryOption
                        );
                        if (index === -1) return;
                        countrySelect.selectedIndex = index;
                        $(countrySelect).trigger("change");
                    });
                },
            });
            input.instance = intlTelInputGlobals.getInstance(input);
        });
    };
    elShipping = d
        .getElementById("shipping")
        .querySelector(".tap--shipping-info");
    elShippingTitle = d
        .getElementById("shipping")
        .querySelector(".tap--shipping-info__title");
    elPayment = d
        .getElementById("payment")
        .querySelector(".tap--shipping-info");
    elPaymentTitle = d
        .getElementById("payment")
        .querySelector(".tap--shipping-info__title");
    elOrderSummary = d.querySelector(".main-checkout__order-summary");

    /** abstract function */
    function checkout() {
        this.version = "1.0.0";
        utilities = new checkout.utilities(this);
        ux = new checkout.ux(this);
        http = new checkout.http(this);
        validator = new checkout.validator(this);
        hooks = new checkout.hooks(this);
        utilities.log(`🚀 Checkout v${this.version} library loaded.`);
    }

    checkout.prototype = {};

    /** utilities */
    (function (checkout) {
        "use strict";
        checkout.utilities = function () {};
        checkout.utilities.prototype = {
            log: function () {
                "console" in w && w.console.log(...arguments);
            },
            extend: function (obj) {
                [].slice.call(arguments, 1).forEach(function (source) {
                    for (var attr in source) {
                        obj[attr] = source[attr];
                    }
                });
                return obj;
            },
            isEmpty: function (value) {
                var attr;
                // Null and undefined are empty
                if (!this.isDefined(value)) {
                    return true;
                }

                // functions are non empty
                if (this.isFunction(value)) {
                    return false;
                }

                // Whitespace only strings are empty
                if (this.isString(value)) {
                    return /^\s*$/.test(value);
                }

                // For arrays we use the length property
                if (this.isArray(value)) {
                    return value.length === 0;
                }

                // Dates have no attributes but aren't empty
                if (this.isDate(value)) {
                    return false;
                }

                // If we find at least one property we consider it non empty
                if (this.isObject(value)) {
                    for (attr in value) {
                        return false;
                    }
                    return true;
                }

                return false;
            },
            isDate: function (obj) {
                return obj instanceof Date;
            },
            isArray: function (value) {
                return {}.toString.call(value) === "[object Array]";
            },
            isDefined: function (obj) {
                return obj !== null && obj !== undefined;
            },
            isObject: function (obj) {
                return obj === Object(obj);
            },
            isFunction: function (value) {
                return typeof value === "function";
            },
            isString: function (value) {
                return typeof value === "string";
            },
            isNumber: function (value) {
                return typeof value === "number" && !isNaN(value);
            },
            sanitizeFormValue: function (value, options) {
                if (options.trim && this.isString(value)) {
                    value = value.trim();
                }

                if (options.nullify !== false && value === "") {
                    return null;
                }
                return value;
            },
            formValues: function (
                form,
                options = {
                    trim: true,
                    nullify: false,
                }
            ) {
                var values = {},
                    i,
                    j,
                    input,
                    inputs,
                    option,
                    value;

                if (!form) {
                    return values;
                }

                options = options || {};

                inputs = form.querySelectorAll("input[name], textarea[name]");
                for (i = 0; i < inputs.length; ++i) {
                    input = inputs.item(i);

                    if (this.isDefined(input.getAttribute("data-ignored"))) {
                        continue;
                    }

                    var name = input.name.replace(/\./g, "\\\\.");
                    value = this.sanitizeFormValue(input.value, options);
                    if (input.type === "number") {
                        value = value ? +value : null;
                    } else if (input.type === "checkbox") {
                        if (input.attributes.value) {
                            if (!input.checked) {
                                value = values[name] || null;
                            }
                        } else {
                            value = input.checked;
                        }
                    } else if (input.type === "radio") {
                        if (!input.checked) {
                            value = values[name] || null;
                        }
                    }
                    values[name] = value;
                }

                inputs = form.querySelectorAll("select[name]");
                for (i = 0; i < inputs.length; ++i) {
                    input = inputs.item(i);
                    if (this.isDefined(input.getAttribute("data-ignored"))) {
                        continue;
                    }

                    if (input.multiple) {
                        value = [];
                        for (j in input.options) {
                            option = input.options[j];
                            if (option && option.selected) {
                                value.push(
                                    this.sanitizeFormValue(
                                        option.value,
                                        options
                                    )
                                );
                            }
                        }
                    } else {
                        var _val =
                            typeof input.options[input.selectedIndex] !==
                            "undefined"
                                ? input.options[input.selectedIndex].value
                                : /* istanbul ignore next */ "";
                        value = this.sanitizeFormValue(_val, options);
                    }
                    values[input.name] = value;
                }

                return values;
            },
            forEachKeyInKeyPath: function (object, keyPath, callback) {
                if (!this.isString(keyPath)) {
                    return undefined;
                }

                var key = "",
                    i,
                    escape = false;

                for (i = 0; i < keyPath.length; ++i) {
                    switch (keyPath[i]) {
                        case ".":
                            if (escape) {
                                escape = false;
                                key += ".";
                            } else {
                                object = callback(object, key, false);
                                key = "";
                            }
                            break;

                        case "\\":
                            if (escape) {
                                escape = false;
                                key += "\\";
                            } else {
                                escape = true;
                            }
                            break;

                        default:
                            escape = false;
                            key += keyPath[i];
                            break;
                    }
                }

                return callback(object, key, true);
            },
            getDeepObjectValue: function (obj, keyPath) {
                let _this = this;
                if (!this.isObject(obj)) {
                    return undefined;
                }

                return this.forEachKeyInKeyPath(
                    obj,
                    keyPath,
                    function (obj, key) {
                        if (_this.isObject(obj)) {
                            return obj[key];
                        }
                    }
                );
            },
            setFormClone: function (form) {
                if (!form) return;
                form.clone = JSON.stringify(this.formValues(form));
            },
            deepEqual: function (x, y) {
                const ok = Object.keys,
                    tx = typeof x,
                    ty = typeof y;
                return x && y && tx === "object" && tx === ty
                    ? ok(x).length === ok(y).length &&
                          ok(x).every((key) => this.deepEqual(x[key], y[key]))
                    : x == y;
            },
            isModifiedForm: function (form) {
                form = d.getElementById(form.id);
                if (!form || !("clone" in form)) return true;
                var current = this.formValues(form),
                    old = JSON.parse(form.clone);

                if (
                    form.id === "information" &&
                    !this.deepEqual(current, old)
                ) {
                    current = http._addExtraAddressParams(current);
                }

                return !this.deepEqual(current, old);
            },
            triggerEvent: function (eventName, element) {
                if ("createEvent" in d) {
                    var event = d.createEvent("HTMLEvents");
                    event.initEvent(eventName, false, true);
                    element.dispatchEvent(event);
                } else {
                    element.fireEvent("on" + eventName);
                }
            },
            addEventListener: function (a, b, c) {
                if (a.addEventListener) a.addEventListener(b, c, !1);
                else if (a.attachEvent) a.attachEvent("on" + b, c);
                else throw Error("P`" + b);
            },
            sleep: function (delay) {
                var start = new Date().getTime();
                while (new Date().getTime() < start + delay);
            },
            editDistance: function (s1, s2) {
                s1 = s1.toLowerCase();
                s2 = s2.toLowerCase();
                var costs = new Array();
                for (var i = 0; i <= s1.length; i++) {
                    var lastValue = i;
                    for (var j = 0; j <= s2.length; j++) {
                        if (i == 0) costs[j] = j;
                        else {
                            if (j > 0) {
                                var newValue = costs[j - 1];
                                if (s1.charAt(i - 1) != s2.charAt(j - 1))
                                    newValue =
                                        Math.min(
                                            Math.min(newValue, lastValue),
                                            costs[j]
                                        ) + 1;
                                costs[j - 1] = lastValue;
                                lastValue = newValue;
                            }
                        }
                    }
                    if (i > 0) costs[s2.length] = lastValue;
                }
                return costs[s2.length];
            },
            similarity: function (s1, s2) {
                var longer = s1;
                var shorter = s2;
                if (s1.length < s2.length) {
                    longer = s2;
                    shorter = s1;
                }
                var longerLength = longer.length;
                if (longerLength == 0) {
                    return 1.0;
                }
                return (
                    (longerLength - this.editDistance(longer, shorter)) /
                    parseFloat(longerLength)
                );
            },
            timeout: function (ms) {
                return new Promise((resolve) => setTimeout(resolve, ms));
            },
            wait: async function (ms) {
                await this.timeout(ms);
                return true;
            },
        };
    })(checkout);

    /** http */
    (function (checkout) {
        "use strict";
        checkout.http = function (checkout) {};
        checkout.http.prototype = {
            _url: function (url) {
                return "" + url;
            },
            _data: function (data) {
                if (data instanceof FormData || data instanceof Object)
                    return new URLSearchParams(data).toString();
                return data;
            },
            _addExtraAddressParams(data) {
                var telephone = d.querySelector("[name=telephone]");
                if (
                    telephone &&
                    telephone.instance &&
                    telephone.instance.isValidNumber()
                ) {
                    data["telephone"] = telephone.instance.getNumber();
                    data["iso_code_2"] = telephone.instance
                        .getSelectedCountryData()
                        ["iso2"].toUpperCase();
                    data["phonecode"] =
                        telephone.instance.getSelectedCountryData()["dialCode"];
                }

                return data;
            },
            fetch: async function (url = "", data = {}, method = "POST") {
                var result = await fetch(this._url(url), {
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: this._data(data),
                    method: method,
                    mode: "same-origin", // no-cors, *cors, same-origin
                    cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
                    credentials: "same-origin",
                });

                var responseClone = result.clone();

                return result
                    .json()
                    .then((json) => {
                        try {
                            return typeof json === "object"
                                ? json
                                : JSON.parse(json);
                        } catch (error) {
                            throw error;
                        }
                    })
                    .catch(function (error) {
                        return responseClone.text().then((txt) => {
                            utilities.log(
                                `Response was not OK.\nStatus code: ${
                                    responseClone.status
                                }\nStatus text: ${
                                    responseClone.statusText
                                }\nResponse: ${txt.trim()}`
                            );
                            return `${txt.trim()}`;
                        });
                    });
            },
            getZones: async function (country) {
                var results = await this.fetch(
                    "?route=checkout/checkoutv3/getZones",
                    { country }
                );

                return results;
            },
            getAreas: async function (zone) {
                var results = await this.fetch(
                    "?route=checkout/checkoutv3/getAreas",
                    { zone }
                );

                return results;
            },
            validateInformation: async function (data) {
                data = this._addExtraAddressParams(data);

                var results = await this.fetch(
                    "?route=checkout/checkoutv3/validateInformation",
                    data
                );

                if (results.success == false) {
                    return results.errors || {};
                }

                await hooks.afterValidateInformation(results.data);
            },
            validateShipping: async function (data) {
                var results = await this.fetch(
                    "?route=checkout/checkoutv3/validateShipping",
                    data
                );

                if (results.success == false) {
                    return results.errors || {};
                }

                await hooks.afterValidateShipping(results.data);
            },
            validatePayment: async function (data) {
                var results = await this.fetch(
                    "?route=checkout/checkoutv3/validatePayment",
                    data
                );
                if (results.success == false) {
                    return results.errors || {};
                }

                await hooks.afterValidatePayment(results.data);
            },
            validateCoupon: async function (data) {
                var results = await this.fetch(
                    "?route=checkout/checkoutv3/validateCoupon",
                    data
                );

                if (results.success == false) {
                    return results.errors || {};
                }

                await hooks.afterValidateCoupon(results.data);
            },
            validateReward: async function (data) {
                var results = await this.fetch(
                    "?route=checkout/checkoutv3/validateReward",
                    data
                );

                if (results.success == false) {
                    return results.errors || {};
                }

                await hooks.afterValidateReward(results.data);
            },
            updateShippingView: async function () {
                // var data = utilities.formValues(
                //     d.getElementById("information")
                // );

                // data = this._addExtraAddressParams(data);
                var data = {};

                var results = await this.fetch(
                    "?route=checkout/checkoutv3/updateShippingView",
                    data
                );

                return results;
            },
            updatePaymentView: async function () {
                // var data = utilities.formValues(
                //     d.getElementById("information")
                // );

                // data = this._addExtraAddressParams(data);

                var data = {};

                var results = await this.fetch(
                    "?route=checkout/checkoutv3/updatePaymentView",
                    data
                );

                return results;
            },
            updateOrderSummaryView: async function () {
                // var data = utilities.formValues(
                //     d.getElementById("information")
                // );

                // data = this._addExtraAddressParams(data);

                var data = {};

                var results = await this.fetch(
                    "?route=checkout/checkoutv3/updateOrderSummaryView",
                    data
                );

                return results;
            },
        };
    })(checkout);

    /** hooks */
    (function (checkout) {
        "use strict";
        checkout.hooks = function (checkout) {};
        checkout.hooks.prototype = {
            afterValidateInformation: async function (data) {
                if (data && utilities.isObject(data)) {
                    addressFields
                        .filter(
                            (f) => f.element && f.element.name == "address_id"
                        )[0]
                        ?.set(data.address_id);

                    addressFields
                        .filter(
                            (f) => f.element && f.element.name == "iso_code_2"
                        )[0]
                        ?.set(data.iso_code_2);

                    addressFields
                        .filter(
                            (f) => f.element && f.element.name == "phonecode"
                        )[0]
                        ?.set(data.phonecode);

                    addressFields
                        .filter(
                            (f) => f.element && f.element.name == "country"
                        )[0]
                        ?.set(data.country);

                    addressFields
                        .filter((f) => f.element && f.element.name == "zone")[0]
                        ?.set(data.zone);

                    addressFields
                        .filter((f) => f.element && f.element.name == "area")[0]
                        ?.set(data.area);

                    addressFields
                        .filter(
                            (f) => f.element && f.element.name == "location"
                        )[0]
                        ?.set(data.location);

                    addressFields
                        .filter(
                            (f) =>
                                f.element && f.element.name == "short_address"
                        )[0]
                        ?.set(data.short_address);

                    utilities.setFormClone(d.getElementById("information"));
                }

                await ux.updateShippingView();
                ux.updatePaymentView();
                ux.updateOrderSummaryView();
            },
            afterValidateShipping: async function () {
                utilities.setFormClone(d.getElementById("shipping"));
                await ux.updateOrderSummaryView();
            },
            afterValidatePayment: async function () {
                utilities.setFormClone(d.getElementById("payment"));
                await ux.updatePaymentView();
                ux.updateOrderSummaryView();
            },
            afterValidateCoupon: async function () {
                await ux.updateOrderSummaryView();
            },
            afterValidateReward: async function () {
                await ux.updateOrderSummaryView();
            },
            completeOrder: async function () {
                await ux.completeOrder();
            },
        };
    })(checkout);

    /** validator */
    (function (checkout) {
        "use strict";
        checkout.validator = function (checkout) {};

        checkout.validator.validators = {
            presence: function (value, options) {
                if (
                    options.allowEmpty !== false
                        ? !utilities.isDefined(value)
                        : utilities.isEmpty(value)
                ) {
                    return options.message || this.message || "can't be blank";
                }
            },
            tel: function (value, options) {
                if (
                    utilities.isObject(options.instance) &&
                    !options.instance.isValidNumber()
                ) {
                    return (
                        options.message ||
                        this.message ||
                        "it invalid telephone!"
                    );
                }
            },
            number: function (value, options) {
                // Empty values are fine
                if (!utilities.isDefined(value) || utilities.isEmpty(value)) {
                    return;
                }

                // Coerce the value to a number unless we're being strict.
                if (utilities.isString(value) && !utilities.isEmpty(value)) {
                    value = +value;
                }

                // If it's not a number we shouldn't continue since it will compare it.
                if (!utilities.isNumber(value)) {
                    return options.message || this.message || "is not a number";
                }
            },
        };

        checkout.validator.prototype = {
            _inputs: function (form) {
                let _inputs = [];
                form.querySelectorAll(
                    "input[name][data-validate='validate'], select[name][data-validate='validate']"
                ).forEach((input) => _inputs.push(input));
                return _inputs;
            },
            _constraints: function (inputs) {
                let constraints = {},
                    index,
                    element,
                    name,
                    allowEmpty;

                for (index = 0; index < inputs.length; index++) {
                    element = inputs[index];
                    name = element.name.replace(/\./g, "\\\\.");
                    if (
                        element.attributes["data-presence"] != undefined &&
                        element.attributes["data-presence"]["value"] == 1
                    ) {
                        constraints[name] = constraints[name] || {};
                        allowEmpty =
                            element.attributes["data-allow-empty"] !=
                                undefined &&
                            element.attributes["data-allow-empty"]["value"] ==
                                1;
                        constraints[name]["presence"] = {
                            allowEmpty: allowEmpty,
                            message:
                                element.attributes["data-presence-msg"]
                                    ?.value ||
                                "the " + name + " field is required!",
                        };
                    }

                    if (
                        element.attributes["data-number"] != undefined &&
                        element.attributes["data-number"]["value"] == 1
                    ) {
                        constraints[name] = constraints[name] || {};
                        constraints[name]["number"] = {
                            message:
                                element.attributes["data-number-msg"]?.value ||
                                "the " + name + " field is not a number!",
                        };
                    }

                    if (
                        element.attributes["data-tel"] != undefined &&
                        element.attributes["data-tel"]["value"] == 1
                    ) {
                        constraints[name] = constraints[name] || {};
                        constraints[name]["tel"] = {
                            instance: element.instance,
                            message:
                                element.attributes["data-tel-msg"]?.value ||
                                "the " + name + " field is not a tel!",
                        };
                    }
                }

                return constraints;
            },
            _result: function (value) {
                var args = [].slice.call(arguments, 1);
                if (typeof value === "function") {
                    value = value.apply(null, args);
                }
                return value;
            },
            validate: async function (form) {
                var results = [],
                    attr,
                    validatorName,
                    value,
                    validators,
                    validator,
                    validatorOptions,
                    error;

                const inputs = this._inputs(form);

                const attributes = utilities.formValues(
                    form /*, {
                            trim: true,
                            nullify: true,
                        }*/
                );

                const constraints = this._constraints(inputs);

                for (attr in constraints) {
                    value = utilities.getDeepObjectValue(attributes, attr);

                    validators = this._result(
                        constraints[attr],
                        value,
                        attributes,
                        attr,
                        {},
                        constraints
                    );
                    for (validatorName in validators) {
                        validator =
                            checkout.validator.validators[validatorName];

                        if (!validator) {
                            throw new Error(
                                `Unknown validator ${validatorName}`
                            );
                        }

                        validatorOptions = validators[validatorName];
                        validatorOptions = this._result(
                            validatorOptions,
                            value,
                            attributes,
                            attr,
                            {},
                            constraints
                        );

                        if (!validatorOptions) {
                            continue;
                        }

                        results.push({
                            attribute: attr,
                            value: value,
                            validator: validatorName,
                            globalOptions: {},
                            attributes: attributes,
                            options: validatorOptions,
                            error: validator.call(
                                this,
                                value,
                                validatorOptions,
                                attr,
                                attributes,
                                {}
                            ),
                        });
                    }
                }

                let errors = results.filter((error) => {
                    return !utilities.isEmpty(error.error);
                });

                errors = (() => {
                    let ret = [];
                    errors.forEach((error) => {
                        // Removes errors without a message
                        if (utilities.isArray(error.error)) {
                            error.error.forEach((msg) => {
                                ret.push(
                                    utilities.extend({}, error, {
                                        error: msg,
                                    })
                                );
                            });
                        } else {
                            ret.push(error);
                        }
                    });
                    return ret;
                })();

                errors = (() => {
                    let ret = {};
                    errors.forEach(function (error) {
                        var list = ret[error.attribute];
                        if (list) {
                            list.push(error);
                        } else {
                            ret[error.attribute] = [error];
                        }
                    });
                    return ret;
                })();

                for (attr in errors) {
                    errors[attr] = errors[attr]
                        .map(function (error) {
                            return error.error;
                        })
                        .filter(function (value, index, self) {
                            return self.indexOf(value) === index;
                        });
                }

                results = utilities.isEmpty(errors) ? undefined : errors;

                return results;
            },
            validateSteps: async function (
                formsName,
                beforeValidateForm,
                jsAfterValidateForm,
                ajaxAfterValidateForm,
                success,
                fail
            ) {
                if (!formsName.length) {
                    return utilities.isFunction(success) ? success() : null;
                }

                var form = d.getElementById(formsName.splice(0, 1)[0]),
                    errors,
                    _;

                if (form) {
                    utilities.isFunction(beforeValidateForm) &&
                        (await beforeValidateForm(form));

                    errors = await this.validate(form);

                    if (errors) {
                        utilities.log("❎ " + form.id + " section invalid!");
                        return utilities.isFunction(fail)
                            ? fail(form.id, errors, "JS-VALIDATION")
                            : null;
                    } else if (utilities.isFunction(jsAfterValidateForm)) {
                        _ = await jsAfterValidateForm(form);
                    }

                    if (utilities.isModifiedForm(d.getElementById(form.id))) {
                        errors = await http[
                            "validate" +
                                form.id.charAt(0).toUpperCase() +
                                form.id.substr(1, form.id.length - 1)
                        ](utilities.formValues(d.getElementById(form.id)));

                        if (errors) {
                            utilities.log("❎ " + form.id + " section invalid!");
                            return utilities.isFunction(fail)
                                ? fail(form.id, errors, "AJAX-VALIDATION")
                                : null;
                        } else {
                            utilities.isFunction(ajaxAfterValidateForm) &&
                                (await ajaxAfterValidateForm(form));
                        }
                    } else {
                        utilities.isFunction(ajaxAfterValidateForm) &&
                            (await ajaxAfterValidateForm(form));
                    }
                }

                return this.validateSteps(
                    formsName,
                    beforeValidateForm,
                    jsAfterValidateForm,
                    ajaxAfterValidateForm,
                    success,
                    fail
                );
            },
        };
    })(checkout);

    /** ux */
    (function (checkout) {
        "use strict";
        checkout.ux = function (checkout) {
            for (let i in this.__proto__) {
                i && i in this.__proto__ && !i.startsWith('_') && utilities.isFunction(this.__proto__[i]) && (checkout.__proto__[i] = this.__proto__[i].bind(this));
            }
            this._setup();
        };
        
        checkout.ux.prototype = {
            _setup: async function () {
                utilities.addEventListener(w, "DOMContentLoaded", async () => {
                    if (typeof google !== "undefined") {
                        gMapInstance = initializeAddressMap();
                        this._reinitializeAddressMaps();
                    }
    
                    // toggle display add address form
                    if (!this._hasAddresses()) {
                        this.handleOnClickAddAddressBtn();
                    }
                    
                    if (
                        typeof w.intlTelInput !== "undefined" &&
                        typeof w.$ !== "undefined"
                    ) {
                        await w.intlTelInputGlobals.loadUtils(
                            "expandish/view/javascript/iti/js/utils.js"
                        );
                        initializeTelInput();
                    }

                    utilities.setFormClone(d.getElementById("information"));
                    utilities.setFormClone(d.getElementById("shipping"));
                    utilities.setFormClone(d.getElementById("payment"));
                    d.getElementById('loading') && (d.getElementById('loading').style.display = "none");
                });
            },
            _hasAddresses() {
                return !(d.querySelector("[data-address_id]") === null);
            },
            _nextStep() {
                var c, n;
                c = d.querySelector(".step.active");
                if (!c) return null;
                n = c.nextSibling;
                while (n && n.nodeType != 1) n = n.nextSibling;

                if (n && n.hasAttribute("data-step"))
                    return n.getAttribute("data-step");

                return null;
            },
            _animateToStep(step) {
                let activeStep = d.querySelector(".step.active");

                if (activeStep && activeStep.dataset.step == step) {
                    return;
                }

                activeStep && activeStep.classList.remove("active");

                d.querySelector(".step[data-step=" + step + "]")?.classList.add(
                    "active"
                );

                $(".taps-container .tap").fadeOut(0).removeClass("active");

                $(".tap[data-tap=" + step + "]")
                    .fadeIn("fast")
                    .addClass("active");
            },
            _scrollTo: function (selector) {
                if (
                    selector != "payment" &&
                    $("#" + selector).length &&
                    mode === "onePage"
                ) {
                    $("html, body").animate({
                        scrollTop: $("#" + selector).offset().top,
                    });
                }
            },
            _toggleDisableContainer() {
                const container = d.querySelector(".main-checkout");
                if (!container) return false;
                container.classList.toggle("disable");
            },
            _showFormErrors(f, errs, t) {
                var input,
                    i,
                    inputGroup,
                    msgInput,
                    errorText,
                    errorArea,
                    errorsItems;
                for (i in errs) {
                    input = f.querySelector("[name='" + i + "']");
                    if (input) {
                        inputGroup = input.closest(".data__input");
                        inputGroup &&
                            inputGroup.classList.add("input--invalid");

                        errorText = utilities.isArray(errs[i])
                            ? errs[i][0]
                            : utilities.isString(errs[i])
                            ? errs[i]
                            : "unknown error";

                        if (inputGroup) {
                            if (
                                inputGroup.querySelector(".input--invalid-msg")
                            ) {
                                inputGroup.querySelector(
                                    ".input--invalid-msg"
                                ).textContent = errorText;
                            } else {
                                msgInput = d.createElement("P");
                                msgInput.textContent = errorText;
                                msgInput.classList.add("input--invalid-msg");
                                inputGroup.insertAdjacentElement(
                                    "beforeEnd",
                                    msgInput
                                );
                            }
                        } else {
                            errorArea = f.querySelector(".error--area");
                            try {
                                errorArea.insertAdjacentHTML(
                                    "beforeEnd",
                                    `<p class="input--invalid-msg">${errorText}</p>`
                                );

                                $(errorArea).css("display", "flex");
                            } catch (error) {
                                utilities.log(error);
                            }
                        }
                    }
                }
                errorsItems = f.querySelectorAll(".input--invalid-msg");
                if (errorsItems.length) {
                    setTimeout(() => {
                        try {
                            $("body").animate(
                                {
                                    scrollTop: $(
                                        errorsItems.item(0).parentNode
                                    ).offset().top,
                                },
                                0
                            );
                            errorsItems.item(0).focus();
                        } catch (err) {}
                    }, 0);
                }
            },
            _resetFormErrors(f) {
                var mm = f.querySelectorAll(".input--invalid-msg"),
                    m,
                    i,
                    g;
                for (i = 0; i < mm.length; ++i) {
                    m = mm.item(i);
                    g = m.closest(".input--invalid");
                    g && g.classList.remove("input--invalid");
                    m.remove();
                }

                $(".error--area", f).css("display", "none");
            },
            _validateSteps: async function (
                { forms, step },
                jsAfterValidateForm,
                ajaxAfterValidateForm,
                successCallable,
                errorCallable
            ) {
                var _this = this;
                _this._toggleDisableContainer();
                validator.validateSteps(
                    forms.slice(0),
                    async function beforeValidateForm(form) {
                        _this._resetFormErrors(form);
                    },
                    async function callJsAfterValidateForm(form) {
                        var _;
                        if (utilities.isFunction(jsAfterValidateForm)) {
                            _ = await jsAfterValidateForm(form);
                        }
                        return _;
                    },
                    async function callAjaxAfterValidateForm(form) {
                        var _;
                        if (utilities.isFunction(ajaxAfterValidateForm)) {
                            _ = await ajaxAfterValidateForm(form);
                        }
                        return _;
                    },
                    async function success() {
                        _this._toggleDisableContainer();
                        forms.map((s) =>
                            utilities.log("✅ " + s + " section valid")
                        );
                        if (step) {
                            _this._animateToStep(step);
                        }
                        var _;
                        if (utilities.isFunction(successCallable)) {
                            _ = await successCallable();
                        }
                        return _;
                    },
                    async function fail(form, errors, type) {
                        _this._toggleDisableContainer();
                        form = d.getElementById(form);
                        if (!form) return;

                        if (step && step != form.dataset.tap) {
                            _this._animateToStep(form.dataset.tap);
                        } else {
                            _this._scrollTo(form.id);
                        }

                        _this._showFormErrors(form, errors, type);

                        if (form.id === "information") {
                            var shouldShowAddressBox =
                                Object.keys(errors).filter(
                                    (f) =>
                                        f !== "firstname" && f !== "telephone"
                                ).length > 0;

                            if (shouldShowAddressBox) {
                                var curAddr = d.querySelector(
                                    '[name="address"]:checked'
                                );
                                if (curAddr instanceof Element) {
                                    var addrContainer = curAddr.closest(
                                        ".saved-address-container"
                                    );

                                    if (
                                        addrContainer instanceof Element &&
                                        $(
                                            addrContainer.querySelector(
                                                ".edit--address"
                                            )
                                        ).is(":hidden")
                                    ) {
                                        addrContainer
                                            .querySelector(
                                                ".info__details__edit"
                                            )
                                            .click();
                                    }
                                } else if (
                                    elAddAddressContainer instanceof Element &&
                                    $(elAddAddressContainer).is(":hidden")
                                ) {
                                    d.querySelector(
                                        ".shipping__add-address"
                                    )?.click();
                                }
                            }
                        }

                        var _;
                        if (utilities.isFunction(errorCallable)) {
                            _ = await errorCallable({
                                form,
                                step,
                                errors,
                                type,
                            });
                        }
                        return _;
                    }
                );
            },
            _reinitializeAddressMaps() {
                Array.from(d.querySelectorAll("[data-location]")).map(
                    renderAddressMap.bind(null)
                );
            },
            _resetAddressFields: function (toDefault = false) {
                addressFields.forEach((element) => {
                    element.reset(toDefault);
                    var group = element.element.closest(".data__input");
                    group && group.classList.remove("input--invalid");
                    var msgInput = group
                        ? group.querySelector(".input--invalid-msg")
                        : null;
                    msgInput && msgInput.remove();
                });
            },
            _fillAddressFields: function ({ dataset }) {
                this._resetAddressFields(false);
                addressFields
                    .filter(
                        (f) => f.element && f.element.name == "address_id"
                    )[0]
                    ?.set(dataset.address_id);
                addressFields
                    .filter(
                        (f) => f.element && f.element.name == "iso_code_2"
                    )[0]
                    ?.set(dataset.iso_code_2);
                addressFields
                    .filter(
                        (f) => f.element && f.element.name == "phonecode"
                    )[0]
                    ?.set(dataset.phonecode);
                addressFields
                    .filter((f) => f.element && f.element.name == "default")[0]
                    ?.set(1);
                addressFields
                    .filter((f) => f.element && f.element.name == "location")[0]
                    ?.set(dataset.location);
                addressFields
                    .filter(
                        (f) => f.element && f.element.name == "address_1"
                    )[0]
                    ?.set(dataset.address_1);
                addressFields
                    .filter(
                        (f) => f.element && f.element.name == "address_2"
                    )[0]
                    ?.set(dataset.address_2);

                addressFields
                    .filter((f) => f.element && f.element.name == "postcode")[0]
                    ?.set(dataset.postcode);

                addressFields
                    .filter((f) => f.element && f.element.name == "country")[0]
                    ?.set(dataset.country);

                addressFields
                    .filter((f) => f.element && f.element.name == "zone")[0]
                    ?.set(dataset.zone);

                addressFields
                    .filter((f) => f.element && f.element.name == "area")[0]
                    ?.set(dataset.area);

                addressFields
                    .filter(
                        (f) => f.element && f.element.name == "short_address"
                    )[0]
                    ?.set(dataset.short_address);

                addressFields
                    .filter(
                        (f) => f.element && f.element.name == "country_id"
                    )[0]
                    ?.set(dataset.country_id);

                if (
                    dataset.country_id &&
                    dataset.country_id != "0" &&
                    dataset.country_id != "" &&
                    dataset.country_id != "null" &&
                    dataset.zone_id &&
                    dataset.zone_id != "0" &&
                    dataset.zone_id != "" &&
                    dataset.zone_id != "null"
                ) {
                    addressFields
                        .filter((f) => f.element.name == "zone_id")[0]
                        ?.set(
                            [
                                {
                                    name: dataset.zone,
                                    zone_id: dataset.zone_id,
                                },
                            ],
                            dataset.zone_id
                        );
                } else {
                    addressFields
                        .filter((f) => f.element.name == "zone_id")[0]
                        ?.reset();
                }

                if (
                    dataset.country_id &&
                    dataset.country_id != "0" &&
                    dataset.country_id != "" &&
                    dataset.country_id != "null" &&
                    dataset.zone_id &&
                    dataset.zone_id != "0" &&
                    dataset.zone_id != "" &&
                    dataset.zone_id != "null" &&
                    dataset.area_id &&
                    dataset.area_id != "0" &&
                    dataset.area_id != "" &&
                    dataset.area_id != "null"
                ) {
                    addressFields
                        .filter((f) => f.element.name == "area_id")[0]
                        ?.set(
                            [
                                {
                                    name: dataset.area,
                                    area_id: dataset.area_id,
                                },
                            ],
                            dataset.area_id
                        );
                } else {
                    addressFields
                        .filter((f) => f.element.name == "area_id")[0]
                        ?.reset();
                }
            },
            goToStep: function (step) {
                var currentStep,
                    stepsPositions = {
                        "information-step": 0,
                        "shipping-step": 1,
                        "payment-step": 2,
                    };

                currentStep = d.querySelector(".step.active").dataset.step;

                if (step == currentStep) return;

                // handle back to prev step
                if (
                    step in stepsPositions &&
                    currentStep in stepsPositions &&
                    stepsPositions[step] < stepsPositions[currentStep]
                ) {
                    this._animateToStep(step);
                    return;
                }

                if (step == "next") step = this._nextStep();

                var forms = [];

                switch (step) {
                    case "information-step":
                        break;
                    case "shipping-step":
                        forms.push("information");
                        break;
                    case "payment-step":
                        forms.push("information");
                        forms.push("shipping");
                        break;
                    default:
                        forms.push("information");
                        forms.push("shipping");
                        forms.push("payment");
                }

                this._validateSteps(
                    { forms, step },
                    () => {},
                    () => {},
                    async () => {
                        // success handler...
                        if (forms.indexOf("information") > -1) {
                            await this.updateInformationView(
                                utilities.formValues(
                                    d.querySelector("#information")
                                )
                            );
                        }

                        !step && hooks.completeOrder();
                    },
                    (errorObj) => {}
                );
            },
            goToStepNext: function () {
                return this.goToStep("next");
            },
            validateAllSteps: function () {
                this._validateSteps(
                    { forms: ["information", "shipping", "payment"] },
                    async (form) => {
                        var _;
                        if (form.id === "information") {
                            _ = await this.updateInformationView(
                                utilities.formValues(form)
                            );
                        }
                        return _;
                    },
                    async (form) => {
                        var _;
                        if (form.id === "information") {
                            _ = await this.updateInformationView(
                                utilities.formValues(form)
                            );
                        }
                        return _;
                    },
                    () => {
                        hooks.completeOrder();
                    },
                    (errorObj) => {
                        //
                    }
                );
            },
            validateSingle: async function (event) {
                await utilities.wait(50);
                event = event || w.event || {};

                event.stopPropagation
                    ? event.stopPropagation()
                    : (event.cancelBubble = !0);
                "number" != typeof event.which && (event.which = event.keyCode);

                if (
                    event.which &&
                    event.type &&
                    event.which == 9 &&
                    event.type != "keydown"
                )
                    return;

                let target = event.target;
                if (!target) return;
                
                let inputGroup = target.closest(".data__input");
                if (inputGroup) {
                    let errors = await validator.validate(inputGroup);
                    if (errors) {
                        this._showFormErrors(
                            inputGroup,
                            errors,
                            "JS-VALIDATION"
                        );
                        return false;
                    } else {
                        this._resetFormErrors(inputGroup);
                        return true;
                    }
                }
                return true;
            },
            handleOnClickAddAddressBtn: function (event) {
                event = event || w.event || {};
                event.preventDefault && event.preventDefault();

                $(".data__save")
                    .closest(".info__container.edit--address")
                    .fadeOut("fast")
                    .closest(".saved-address-container")
                    .find(".saved-address__control")
                    .fadeIn("fast");

                $(".saved-address-container.active")
                    .find(".form-check-input")
                    .prop("checked", false)
                    .closest(".saved-address-container")
                    .removeClass("active")
                    .find(".saved-address__info")
                    .slideUp();

                $("[name=address]").prop("checked", false);

                elAddAddressContainer
                    .querySelector(".info__container__map")
                    ?.insertAdjacentElement("afterbegin", elAddressMapTemplate);

                elAddAddressContainer
                    .querySelector(".info__container__data")
                    .insertAdjacentElement("afterbegin", elAddressFormTemplate);

                elAddressFormTemplate.style.display = "block";
                elAddressMapTemplate &&
                    (elAddressMapTemplate.style.display = "block");

                if ($(elAddAddressContainer).is(":hidden")) {
                    this._resetAddressFields(true);
                    if (
                        utilities.isDefined(gMapInstance) &&
                        utilities.isFunction(gMapInstance.setDefaultCoords)
                    ) {
                        gMapInstance.setDefaultCoords(map.lat + "," + map.lng);
                    }
                    $(elAddAddressContainer).stop().slideDown();
                }
            },
            handleOnChangeCountry: async function (event) {
                event = event || w.event || {};
                this.validateSingle(event);
                addressFields
                    .filter((f) => f.element.name == "country")[0]
                    ?.set(
                        event.target.options[
                            event.target.selectedIndex
                        ].textContent.trim()
                    );

                var results = await http.getZones(event.target.value);
                addressFields
                    .filter((f) => f.element.name == "zone_id")[0]
                    ?.set(results.zone || []);
            },
            handleOnChangeZone: async function (event) {
                event = event || w.event || {};
                this.validateSingle(event);
                addressFields
                    .filter((f) => f.element.name == "zone")[0]
                    ?.set(
                        event.target.options[
                            event.target.selectedIndex
                        ].textContent.trim()
                    );

                var results = await http.getAreas(event.target.value);
                addressFields
                    .filter((f) => f.element.name == "area_id")[0]
                    .set(results.area || []);
            },
            handleOnChangeArea: async function (event) {
                event = event || w.event || {};
                this.validateSingle(event);
                addressFields
                    .filter((f) => f.element.name == "area")[0]
                    ?.set(
                        event.target.options[
                            event.target.selectedIndex
                        ].textContent.trim()
                    );
            },
            updateInformationView: async function (data) {
                data.address_id = parseInt(data.address_id);

                if (0 == data.address_id) return;

                var addressBox = d.querySelector("#address_" + data.address_id);

                var isNewAddress = addressBox === null;

                var addressBoxTemplate = d.querySelector("#address-box-tpl");

                if (!addressBoxTemplate) return;

                addressBoxTemplate = addressBoxTemplate.innerHTML;

                for (var key in data) {
                    addressBoxTemplate = addressBoxTemplate.replace(
                        new RegExp("%" + key.toUpperCase() + "%", "g"),
                        data[key]
                    );
                }

                if (!addressBox) {
                    addressBox = d.createElement("DIV");
                    addressBox.id = "address_" + data.address_id;
                    addressBox.classList.add("saved-address-container");
                    d.querySelector(
                        ".saved-address-ship-order"
                    ).insertAdjacentElement("beforeBegin", addressBox);
                    $(elAddAddressContainer).stop().slideUp();
                    addressBox.innerHTML = addressBoxTemplate;
                } else {
                    let templateFragment = d.createElement("DIV");
                    templateFragment.innerHTML = addressBoxTemplate;

                    addressBox
                        .querySelector(".saved-address__control")
                        .parentNode.replaceChild(
                            templateFragment.querySelector(
                                ".saved-address__control"
                            ),
                            addressBox.querySelector(".saved-address__control")
                        );

                    addressBox
                        .querySelector(".info__details")
                        .parentNode.replaceChild(
                            templateFragment.querySelector(".info__details"),
                            addressBox.querySelector(".info__details")
                        );

                    addressBox.querySelector(
                        ".saved-address__info"
                    ).style.display = addressBox.classList.contains("active")
                        ? "block"
                        : "none";

                    addressBox.querySelector(
                        ".info__container.edit--address"
                    ).style.display = "none";

                    templateFragment = null;
                }

                elAddressMapTemplate &&
                    (elAddressMapTemplate.style.display = "none");
                elAddressFormTemplate &&
                    (elAddressFormTemplate.style.display = "none");
                elAddressMapTemplate &&
                    d
                        .querySelector("#information")
                        .appendChild(elAddressMapTemplate);
                elAddressFormTemplate &&
                    d
                        .querySelector("#information")
                        .appendChild(elAddressFormTemplate);

                isNewAddress &&
                    renderAddressMap(
                        addressBox.querySelector("[data-location]")
                    );

                // update contact info details
                var shipTo = async function () {
                    var shipTo = "";
                    if (data.country && data.country.length)
                        shipTo += data.country + ", ";
                    if (data.zone && data.zone.length)
                        shipTo += data.zone + ", ";
                    if (data.area && data.area.length)
                        shipTo += data.area + ", ";
                    if (data.address_1 && data.address_1.length)
                        shipTo += data.address_1 + " ";

                    return shipTo;
                };

                shipTo = await shipTo();

                $(".contact-info-name").text(data.firstname);
                $(".contact-info-phone").text(data.telephone);
                $(".contact-info-ship-to").text(shipTo);

                if (isNewAddress) {
                    setTimeout(() => {
                        $(addressBox.querySelector(".saved-address__control"))
                            .next()
                            .stop()
                            .slideDown()
                            .closest(".saved-address-container")
                            .addClass("active");
                    }, 0);
                }
            },
            handleOnClickEditAddressBtn: async function (event) {
                event = event || w.event || {};
                event.preventDefault && event.preventDefault();
                var addressContainer = event.target.closest(
                    ".saved-address-container"
                );
                if (!addressContainer) return false;

                addressContainer
                    .querySelector(".info__container__map")
                    ?.insertAdjacentElement("afterbegin", elAddressMapTemplate);

                addressContainer
                    .querySelector(".info__container__data")
                    .insertAdjacentElement("afterbegin", elAddressFormTemplate);

                elAddressFormTemplate.style.display = "block";
                elAddressMapTemplate &&
                    (elAddressMapTemplate.style.display = "block");

                // set coords on map
                if (
                    utilities.isDefined(gMapInstance) &&
                    utilities.isFunction(gMapInstance.setDefaultCoords)
                ) {
                    var locationElement =
                        addressContainer.querySelector("[data-location]");
                    if (locationElement) {
                        var coords = locationElement.dataset.location;
                        if (!coords.length || coords == "null") {
                            coords = map.lat + "," + map.lng;
                        }

                        gMapInstance.setDefaultCoords(coords);
                    }
                }

                var countryElement = addressFields.filter(
                    (f) =>
                        f.element &&
                        f.element.name == "country_id" &&
                        f.element.value != "" &&
                        f.element.value != "0"
                );

                if (countryElement.length) {
                    http.getZones(countryElement[0].element.value).then(
                        (results) => {
                            addressFields
                                .filter(
                                    (f) =>
                                        f.element && f.element.name == "zone_id"
                                )[0]
                                ?.set(results.zone);
                        }
                    );
                }

                var zoneElement = addressFields.filter(
                    (f) =>
                        f.element &&
                        f.element.name == "zone_id" &&
                        f.element.value != "" &&
                        f.element.value != "0"
                );

                if (zoneElement.length) {
                    http.getAreas(zoneElement[0].element.value).then(
                        (results) => {
                            addressFields
                                .filter(
                                    (f) =>
                                        f.element && f.element.name == "area_id"
                                )[0]
                                ?.set(results.area);
                        }
                    );
                }

                $(addressContainer)
                    .find(".saved-address__control , .saved-address__info")
                    .fadeOut()
                    .closest(".saved-address-container")
                    .find(".info__container.edit--address")
                    .fadeIn();
            },
            handleOnChangeAddress: async function (event) {
                event = event || w.event || {};

                $(elAddAddressContainer).stop().slideUp();

                $(".data__save")
                    .closest(".info__container.edit--address")
                    .fadeOut("fast")
                    .closest(".saved-address-container")
                    .find(".saved-address__control")
                    .fadeIn("fast");

                $(".saved-address__info").stop().slideUp();
                $(".saved-address-container").removeClass("active");

                var target = event.target;

                target = target.closest(".saved-address__control");

                this._fillAddressFields(target);

                this._validateSteps(
                    { forms: ["information"] },
                    (form) => {},
                    (form) => {},
                    () => {
                        target.click();
                    },
                    (errObj) => {}
                );
            },
            handleOnClickAddressLabel(event) {
                if (!(event.target.nodeName === "INPUT")) return;

                let target = event.currentTarget;
                if (
                    $(target)
                        .closest(".saved-address-container")
                        .hasClass("active")
                ) {
                    $(target)
                        .next()
                        .slideUp()
                        .closest(".saved-address-container")
                        .removeClass("active");
                } else {
                    $(target)
                        .next()
                        .stop()
                        .slideDown()
                        .closest(".saved-address-container")
                        .addClass("active");
                }
            },
            handleOnClickCreateOrUpdateAddress: function () {
                this._validateSteps(
                    { forms: ["information"] },
                    (form) => {},
                    (form) => {},
                    async () => {
                        await this.updateInformationView(
                            utilities.formValues(
                                d.querySelector("#information")
                            )
                        );
                    },
                    (errObj) => {}
                );
            },
            updateShippingView: async function () {
                var results = await http.updateShippingView();
                if (results.success) {
                    elShipping.innerHTML = "";
                    elShipping.insertAdjacentElement(
                        "afterBegin",
                        elShippingTitle
                    );
                    elShipping.insertAdjacentHTML("beforeEnd", results.data);
                }
            },
            updatePaymentView: async function () {
                var results = await http.updatePaymentView();
                if (results.success) {
                    $(elPayment).html(
                        elPaymentTitle.outerHTML + results.data
                    );
                }
            },
            updateOrderSummaryView: async function () {
                var results = await http.updateOrderSummaryView();
                if (results.success) {
                    elOrderSummary.innerHTML = "";
                    elOrderSummary.insertAdjacentHTML(
                        "beforeEnd",
                        results.data
                    );

                    d.querySelector("#mobile_summery_holder").textContent =
                        d.querySelector("#summary_total").textContent;
                }
            },
            handleOnChangeShippingMethod: async function (event) {
                this._validateSteps({ forms: ["shipping"] });
            },
            handleOnChangePaymentMethod: async function (event) {
                this._validateSteps({ forms: ["payment"] }, (form) => {
                    $("#payment .saved-address__info").slideUp(
                        400,
                        function () {
                            this.remove();
                        }
                    );
                });
            },
            completeOrder: async function () {
                var confirmOrderInput = d.querySelector(
                        "[name=order_agree_confirmed]"
                    ),
                    confirmed = confirmOrderInput === null;

                if (confirmOrderInput) {
                    confirmOrderInput.setAttribute("data-validate", "validate");
                    confirmed = await this.validateSingle({
                        target: confirmOrderInput,
                    });
                    confirmOrderInput.setAttribute(
                        "data-validate",
                        "novalidate"
                    );
                }

                if (!confirmed) {
                    return false;
                }

                utilities.log("✅ complete order...");

                $(".processing-payment").show();

                var confirmBtn = $(
                    "#confirm_payment .button, #confirm_payment .btn, #confirm_payment input[type=submit]"
                );

                var href = confirmBtn.attr("href");

                if (href != "" && href != undefined) {
                    document.location.href = href;
                    this._toggleDisableContainer();
                } else {
                    confirmBtn.trigger("click", [
                        this._toggleDisableContainer.bind(this),
                        this._toggleDisableContainer.bind(this),
                    ]);
                }
            },
            handleOnClickApplyCoupon: async function (event) {
                event = event || w.event || {};
                event.preventDefault && event.preventDefault();

                var _this = this;

                _this._toggleDisableContainer();

                var forms = ["coupon"];

                validator.validateSteps(
                    forms.slice(0),
                    function beforeValidateForm(form) {
                        _this._resetFormErrors(form);
                    },
                    function jsAfterValidateForm(form) {},
                    function ajaxAfterValidateForm(form) {},
                    function success() {
                        _this._toggleDisableContainer();
                    },
                    async function fail(form, errors, type) {
                        if ("AJAX-VALIDATION" === type) {
                            await _this.updateOrderSummaryView();
                        }

                        _this._toggleDisableContainer();

                        form = d.getElementById(form);

                        if (!form) return;

                        _this._showFormErrors(form, errors, type);
                    }
                );
            },
            handleOnClickAddReward: function (event) {
                event = event || w.event || {};
                event.preventDefault && event.preventDefault();

                var _this = event.target;
                $(_this)
                    .next()
                    .slideToggle(function () {
                        $(this).stop(1000);
                    });
            },
            incrementDecrementPoint: function (event) {
                const isNegative = $(event.target)
                    .closest(".btn-minus")
                    .is(".btn-minus");

                const input = $(event.target)
                    .closest(".input-group")
                    .find("input");

                if (input.is("input")) {
                    input[0][isNegative ? "stepDown" : "stepUp"]();
                }

                if (input.val() == 0) {
                    $(".reward__confirm").addClass("disabled");
                } else {
                    $(".reward__confirm").removeClass("disabled");
                }
            },
            handleOnClickApplyReward: async function (event) {
                event = event || w.event || {};
                event.preventDefault && event.preventDefault();

                var _this = this;

                _this._toggleDisableContainer();

                var forms = ["reward"];

                validator.validateSteps(
                    forms.slice(0),
                    function beforeValidateForm(form) {
                        _this._resetFormErrors(form);
                    },
                    function jsAfterValidateForm(form) {},
                    function ajaxAfterValidateForm(form) {},
                    function success() {
                        _this._toggleDisableContainer();
                    },
                    async function fail(form, errors, type) {
                        if ("AJAX-VALIDATION" === type) {
                            await _this.updateOrderSummaryView();
                        }

                        _this._toggleDisableContainer();

                        form = d.getElementById(form);

                        if (!form) return;

                        _this._showFormErrors(form, errors, type);
                        form.querySelector(".summary__input__label")?.click();
                    }
                );
            },
        };
    })(checkout);

    // return new instance
    return new checkout();
});
