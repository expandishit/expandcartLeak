(function (factory) {
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
    g.checkout = factory(g, g.document, g.$ || g.jQuery);
})(function (w, d, $) {
    "use strict";
    var log = function () {
            let method = ["c", "o", "n", "s", "o", "l", "e"];
            method.join("") in w && w[method.join("")]["log"](...arguments);
        },
        assertNotEqual = function (target, equal) {
            if (target !== equal) return true;
            throw new TypeError(
                "assertion expected " + target + " not equal " + equal + "."
            );
        };

    /**
     * I am assuming that these constants have the expected value
     * because if one of them is not present,
     * there will be a malfunction in the code
     */
    assertNotEqual(typeof w, "undefined");
    assertNotEqual(typeof $, "undefined");
    assertNotEqual(typeof d, "undefined");
    assertNotEqual(typeof d.currentScript, "undefined");
    assertNotEqual(typeof d.currentScript.attributes, "undefined");

    var {
            currentScript: {
                attributes: {
                    version,
                    logged,
                    orderNo,
                    mode,
                    hasShipping,
                    map,
                },
            },
        } = d,
        ux,
        utilities,
        http,
        validator,
        hooks,
        addressFields,
        elAddressFormTemplate,
        elAddressesList,
        elAddressMapTemplate,
        elAddAddressContainer,
        elShipping,
        elShippingTitle,
        elPayment,
        elPaymentTitle,
        elOrderSummary,
        elContinueBtn,
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

    hasShipping = Boolean(
        typeof hasShipping === "undefined" ? true : parseInt(hasShipping.value)
    );

    map = typeof map === "undefined" ? { status: 0 } : JSON.parse(map.value);

    logged = parseInt(typeof logged === "undefined" ? 0 : logged.value);
    orderNo =
        typeof orderNo === "undefined" ? undefined : parseInt(orderNo.value);
    version = typeof version === "undefined" ? "" : "" + version.value;

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
    elAddressesList = d.querySelector("#shipping_addresses");

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
                                log("no results found");
                            }
                        } else {
                            log("geocoder failed due to: " + status);
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
    currentGeoIPLockup = async function (callback) {
        if (w.geoIpLookup) return callback(w.geoIpLookup);
        $.get("https://ipinfo.io", function () {}, "jsonp").always(function (
            resp
        ) {
            w.geoIpLookup = resp;

            if (
                map.status &&
                utilities.isString(resp.loc) &&
                !utilities.isEmpty(resp.loc)
            ) {
                try {
                    const [lat, lng] = resp.loc.split(",");
                    map.lat = lat;
                    map.lng = lng;
                } catch (error) {}
            }

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
            
            /* set tel placeholder for current selected country
            input.addEventListener("countrychange", function () {
                try {
                    input
                        .closest(".iti")
                        .querySelector(".form-floating__label").textContent =
                        input.placeholder;
                } catch (error) {}
            });
            */
            
            input.instance = intlTelInputGlobals.getInstance(input);
        });
    };

    elShipping =
        hasShipping && d.getElementById("shipping")
            ? d.getElementById("shipping").querySelector(".tap--shipping-info")
            : null;
    elShippingTitle =
        hasShipping && d.getElementById("shipping")
            ? d
                  .getElementById("shipping")
                  .querySelector(".tap--shipping-info__title")
            : null;
    elPayment = d
        .getElementById("payment")
        .querySelector(".tap--shipping-info");
    elPaymentTitle = d
        .getElementById("payment")
        .querySelector(".tap--shipping-info__title");
    elOrderSummary = d.querySelector(".main-checkout__order-summary");
    elContinueBtn = d.querySelector(".taps__next");

    /** abstract function */
    function checkout() {
        utilities = new checkout.utilities(this);
        ux = new checkout.ux(this);
        http = new checkout.http(this);
        validator = new checkout.validator(this);
        hooks = new checkout.hooks(this);
        log(`🚀 Checkout v${version} library loaded.`);
    }

    checkout.prototype = {};

    /** utilities */
    (function (checkout) {
        "use strict";
        checkout.utilities = function () {};
        checkout.utilities.prototype = {
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
                if (!element instanceof Element) return;

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
            addQueryParam: (name, value) => {
                const url = new URL(w.location);
                url.searchParams.set(name, value);
                w.history.pushState({}, "", decodeURIComponent(url));
            },
        };
    })(checkout);

    /** http */
    (function (checkout) {
        "use strict";
        checkout.http = function (checkout) {};
        checkout.http.prototype = {
            _url: function (url, withPrefix) {
                withPrefix = withPrefix || true;
                return withPrefix
                    ? "?route=checkout/checkout" +
                          (version.length ? "v" + version : "") +
                          url
                    : url;
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
                            log(
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
                var results = await this.fetch("/getZones", { country });

                return results;
            },
            getAreas: async function (zone) {
                var results = await this.fetch("/getAreas", { zone });

                return results;
            },
            validateInformation: async function (data) {
                data = this._addExtraAddressParams(data);

                var results = await this.fetch("/validateInformation", data);

                if (results.success == false) {
                    return results.errors || {};
                }

                await hooks.afterValidateInformation(results.data);
            },
            validateShipping: async function (data) {
                if (!hasShipping) return;
                var results = await this.fetch("/validateShipping", data);
                if (results.success == false) {
                    return results.errors || {};
                }

                await hooks.afterValidateShipping(results.data);
            },
            validatePayment: async function (data) {
                var results = await this.fetch("/validatePayment", data);
                if (results.success == false) {
                    return results.errors || {};
                }

                await hooks.afterValidatePayment(results.data);
            },
            validateCoupon: async function (data) {
                var results = await this.fetch("/validateCoupon", data);

                if (results.success == false) {
                    return results.errors || {};
                }

                await hooks.afterValidateCoupon(results.data);
            },
            validateReward: async function (data) {
                var results = await this.fetch("/validateReward", data);

                if (results.success == false) {
                    return results.errors || {};
                }

                await hooks.afterValidateReward(results.data);
            },
            updateShippingView: async function (data = {}) {
                if (!hasShipping) return;
                var results = await this.fetch("/updateShippingView", data);
                return results;
            },
            updatePaymentView: async function () {
                var results = await this.fetch("/updatePaymentView");

                return results;
            },
            updateOrderSummaryView: async function () {
                var results = await this.fetch("/updateOrderSummaryView");

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

                if (hasShipping) {
                    await ux._updateShippingView();
                }

                ux._updatePaymentView();
                ux._updateOrderSummaryView();
            },
            afterValidateShipping: async function () {
                if (!hasShipping) return;
                utilities.setFormClone(d.getElementById("shipping"));
                await ux._updateOrderSummaryView();
            },
            afterValidatePayment: async function () {
                utilities.setFormClone(d.getElementById("payment"));
                await ux._updatePaymentView();
                ux._updateOrderSummaryView();
            },
            afterValidateCoupon: async function () {
                await ux._updateOrderSummaryView();
            },
            afterValidateReward: async function () {
                await ux._updateOrderSummaryView();
            },
            completeOrder: async function () {
                await ux._completeOrder();
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
            format: function (value, options) {
                if (utilities.isString(options) || options instanceof RegExp) {
                    options = { pattern: options };
                }

                var message = options.message || this.message || "is invalid",
                    pattern = options.pattern,
                    match;

                // Empty values are allowed
                if (!utilities.isDefined(value)) {
                    return;
                }

                if (!utilities.isString(value)) {
                    return message;
                }

                if (utilities.isString(pattern)) {
                    pattern = new RegExp(options.pattern, options.flags);
                }

                match = pattern.exec(value);

                if (!match || match[0].length != value.length) {
                    return message;
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
                    (function () {
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
                                element.attributes["data-allow-empty"][
                                    "value"
                                ] == 1;
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
                                    element.attributes["data-number-msg"]
                                        ?.value ||
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

                        if (
                            element.attributes["data-format"] != undefined &&
                            element.attributes["data-format"]["value"] == 1
                        ) {
                            constraints[name] = constraints[name] || {};

                            constraints[name]["format"] = {
                                pattern:
                                    element.attributes["data-pattern"]["value"],
                                flags: element.attributes["data-flags"][
                                    "value"
                                ],
                                message:
                                    element.attributes["data-format-msg"]
                                        ?.value ||
                                    "the " + name + " is invalid!",
                            };
                        }
                    })();
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

                const attributes = utilities.formValues(form);

                const constraints = this._constraints(inputs);

                for (attr in constraints) {
                    (() => {
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
                            (() => {
                                validator =
                                    checkout.validator.validators[
                                        validatorName
                                    ];

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
                                    return;
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
                            })();
                        }
                    })();
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
                        log("❎ " + form.id + " section invalid!");
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
                            log("❎ " + form.id + " section invalid!");
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
            checkout.version = version;
            checkout.mode = mode;
            checkout.logged = logged;
            checkout.hasShipping = hasShipping;
            checkout.orderNo = orderNo;
            for (let i in this.__proto__) {
                i &&
                    i in this.__proto__ &&
                    !i.startsWith("_") &&
                    utilities.isFunction(this.__proto__[i]) &&
                    (checkout.__proto__[i] = this.__proto__[i].bind(this));
            }
            this._setup();
        };

        checkout.ux.prototype = {
            _setup: async function () {
                this._updateReturnBtnState();
                this._updateContinueBtnState();
                utilities.addEventListener(w, "beforeunload", async () => {
                    //Abandoned Cart schedule new cron job for sending retention mail to this customer
                    let abandonedCartSettings = w.abandonedCartSettings;
                    if(abandonedCartSettings.isInstalled
                        && abandonedCartSettings.status == 1
                        && abandonedCartSettings.auto_send_mails == 1
                        && abandonedCartSettings.userLogged !== ""
                    ){
                        await fetch(abandonedCartSettings.url);
                    }

                });
                utilities.addEventListener(w, "DOMContentLoaded", async () => {
                    if (typeof google !== "undefined") {
                        gMapInstance = initializeAddressMap();
                        this._reinitializeAddressMaps();
                    }

                    if (typeof w.intlTelInput !== "undefined") {
                        await w.intlTelInputGlobals.loadUtils(
                            "expandish/view/javascript/iti/js/utils.js"
                        );
                        initializeTelInput();
                    }

                    // toggle display add address form
                    if (!this._hasAddresses()) {
                        this.handleOnClickAddAddressBtn();
                    }

                    utilities.setFormClone(d.getElementById("information"));
                    if (hasShipping)
                        utilities.setFormClone(d.getElementById("shipping"));
                    utilities.setFormClone(d.getElementById("payment"));
                    d.getElementById("loading") &&
                        (d.getElementById("loading").style.display = "none");
                    $(".form-floating__label").insertAfter(
                        ".tel.data__input__entry"
                    );
                });
            },
            _updateReturnBtnState: function () {
                var returnBtn = d.querySelector(".step__return");
                if (!returnBtn) return false;

                if ("onePage" === mode) {
                    returnBtn.setAttribute("data-action", "redirect-step");
                    returnBtn.classList.remove("hide");
                    return;
                }

                var prevStep = this._prevStep();

                if (!prevStep) {
                    prevStep = "redirect-step";
                }

                var prevLabel = d.querySelector("[data-" + prevStep + "]");

                if (!prevLabel) {
                    returnBtn.classList.add("hide");
                    return;
                }

                returnBtn.innerText = prevLabel.dataset.returnLabel;
                returnBtn.setAttribute("data-action", prevStep);
                returnBtn.classList.remove("hide");
            },
            _updateContinueBtnState: function () {
                if (mode === "onePage") return;
                var continueBtn = d.querySelector(".taps__next__btn");
                if (!continueBtn) return false;

                var currentStep = this._currentStep();

                var label = d.querySelector("[data-" + currentStep + "]");

                if (!label) return;

                continueBtn.innerText = label.dataset.continueLabel;
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
            _prevStep() {
                var c, n;
                c = d.querySelector(".step.active");
                if (!c) return null;
                n = c.previousSibling;
                while (n && n.nodeType != 1) n = n.previousSibling;

                if (n && n.hasAttribute("data-step"))
                    return n.getAttribute("data-step");

                return null;
            },
            _currentStep() {
                let activeStep = $(".step.active");
                if (activeStep.length && activeStep.data("step")) {
                    return activeStep.data("step");
                }
                return null;
            },
            _animateToStep(step) {
                let activeStep = $(".step.active");

                if (activeStep.length && activeStep.data("step") == step) {
                    return;
                }

                activeStep.removeClass("active");

                $(".step[data-step=" + step + "]").addClass("active");

                $(".taps-container .tap").fadeOut(0).removeClass("active");

                $(".tap[data-tap=" + step + "]")
                    .fadeIn("fast")
                    .addClass("active");

                if ("shipping-step" === step) {
                    $(".contact-info-shipping").addClass("hide");
                } else {
                    $(".contact-info-shipping").removeClass("hide");
                }

                this._updateReturnBtnState();
                this._updateContinueBtnState();
            },
            _scrollTo: function (selector, duration, callback) {
                duration = duration || 400;
                callback = callback || function () {};

                if ($(selector).length) {
                    $("html, body").animate(
                        {
                            scrollTop: $(selector).offset().top,
                        },
                        duration,
                        callback
                    );
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
                    (function () {
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
                                    inputGroup.querySelector(
                                        ".input--invalid-msg"
                                    )
                                ) {
                                    inputGroup.querySelector(
                                        ".input--invalid-msg"
                                    ).textContent = errorText;
                                } else {
                                    msgInput = d.createElement("P");
                                    msgInput.textContent = errorText;
                                    msgInput.classList.add(
                                        "input--invalid-msg"
                                    );
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
                                    log(error);
                                }
                            }
                        }
                    })();
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
                    (function () {
                        m = mm.item(i);
                        g = m.closest(".input--invalid");
                        g && g.classList.remove("input--invalid");
                        m.remove();
                    })();
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
                        forms.map((s) => log("✅ " + s + " section valid"));
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

                        if (step /** && step != form.dataset.tap */) {
                            _this._animateToStep(form.dataset.tap);
                        } else {
                            if (form.id != "payment" && mode === "onePage") {
                                _this._scrollTo("#" + form.id);
                            }
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
                                        utilities.triggerEvent(
                                            "click",
                                            addrContainer.querySelector(
                                                ".info__details__edit"
                                            )
                                        );
                                        // addrContainer
                                        //     .querySelector(
                                        //         ".info__details__edit"
                                        //     )
                                        //     .click();
                                    }
                                } else if (
                                    elAddAddressContainer instanceof Element &&
                                    $(elAddAddressContainer).is(":hidden")
                                ) {
                                    utilities.triggerEvent(
                                        "click",
                                        d.querySelector(
                                            ".shipping__add-address"
                                        )
                                    );

                                    // d.querySelector(
                                    //     ".shipping__add-address"
                                    // )?.click();
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
            _updateInformationView: async function (data) {
                data.address_id = parseInt(data.address_id);

                if (0 === data.address_id) return;

                // update contact info details
                var shipTo = function () {
                    var shipTo = [];

                    if (data.address_2 && data.address_2.length) {
                        shipTo.push(data.address_2);
                    }

                    if (data.address_1 && data.address_1.length) {
                        shipTo.push(data.address_1);
                    }

                    if (data.area && data.area.length) {
                        shipTo.push(data.area);
                    }

                    if (data.zone && data.zone.length) {
                        shipTo.push(data.zone);
                    }

                    if (data.country && data.country.length) {
                        shipTo.push(data.country);
                    }

                    return shipTo.join(", ");
                };

                $(".contact-info-name").text(data.firstname);
                $(".contact-info-phone").text(data.telephone);
                $(".contact-info-ship-to").text(shipTo());

                var addressBoxTemplate = d.querySelector("#address-box-tpl");

                if (!addressBoxTemplate) return;

                addressBoxTemplate = addressBoxTemplate.innerHTML; //

                for (var key in data) {
                    (function () {
                        addressBoxTemplate = addressBoxTemplate.replace(
                            new RegExp("%" + key.toUpperCase() + "%", "g"),
                            data[key]
                        );
                    })();
                }

                var addressBox = d.querySelector("#address_" + data.address_id);

                if (addressBox) {
                    this._updateExistingAddressBox(
                        addressBox,
                        data,
                        addressBoxTemplate
                    );
                } else {
                    this._insertNewAddressBox(data, addressBoxTemplate);
                }

                return;
            },
            _insertNewAddressBox: function (data, addressBoxTemplate) {
                // hide form address
                $(elAddAddressContainer)
                    .stop()
                    .slideUp(100, function () {
                        // move form element to information form location
                        if (elAddressMapTemplate) {
                            elAddressMapTemplate.style.display = "none";
                            d.querySelector("#information").appendChild(
                                elAddressMapTemplate
                            );
                        }

                        if (elAddressFormTemplate) {
                            elAddressFormTemplate.style.display = "none";
                            d.querySelector("#information").appendChild(
                                elAddressFormTemplate
                            );
                        }
                    });

                var addressContainer = d.createElement("DIV");
                addressContainer.id = "address_" + data.address_id;
                addressContainer.classList.add("saved-address-container");
                addressContainer.innerHTML = addressBoxTemplate;

                elAddressesList.insertAdjacentElement(
                    "beforeEnd",
                    addressContainer
                );

                renderAddressMap(
                    addressContainer.querySelector("[data-location]")
                );

                $(addressContainer)
                    .addClass("active")
                    .find(".info__container.edit--address")
                    .fadeOut(0, function () {
                        logged != 0 && $(".saved-address__title").show();

                        $(addressContainer)
                            .find(
                                ".saved-address__control , .saved-address__info"
                            )
                            .fadeIn(100, function () {
                                addressContainer = null;
                            });
                    });
            },
            _updateExistingAddressBox: function (
                addressContainer,
                data,
                addressBoxTemplate
            ) {
                if (
                    !addressContainer.querySelector(
                        "#" + elAddressFormTemplate.id
                    )
                )
                    return;

                // replace all html
                let templateFragment = d.createElement("DIV");
                templateFragment.innerHTML = addressBoxTemplate;

                addressContainer
                    .querySelector(".saved-address__control")
                    .parentNode.replaceChild(
                        templateFragment.querySelector(
                            ".saved-address__control"
                        ),
                        addressContainer.querySelector(
                            ".saved-address__control"
                        )
                    );

                addressContainer.querySelector(
                    ".saved-address__control"
                ).style.display = "none";

                addressContainer
                    .querySelector(".info__details")
                    .parentNode.replaceChild(
                        templateFragment.querySelector(".info__details"),
                        addressContainer.querySelector(".info__details")
                    );

                templateFragment = null;
                renderAddressMap(
                    addressContainer.querySelector("[data-location]")
                );

                // hide form box fast & show address box fast

                $(addressContainer)
                    .find(".info__container.edit--address")
                    .fadeOut("fast", function () {
                        $(addressContainer)
                            .addClass("active")
                            .find(
                                ".saved-address__control , .saved-address__info"
                            )
                            .fadeIn("fast");

                        // move form element to information form location
                        if (elAddressMapTemplate) {
                            elAddressMapTemplate.style.display = "none";
                            d.querySelector("#information").appendChild(
                                elAddressMapTemplate
                            );
                        }

                        if (elAddressFormTemplate) {
                            elAddressFormTemplate.style.display = "none";
                            d.querySelector("#information").appendChild(
                                elAddressFormTemplate
                            );
                        }
                        logged != 0 && $(".saved-address__title").show();
                    });
            },
            _updateOrderSummaryView: async function () {
                var results = await http.updateOrderSummaryView();
                if (results.success) {
                    var rewardElement =
                            elOrderSummary.querySelector(".summary__hide"),
                        couponElement =
                            elOrderSummary.querySelector('[name="coupon"]');

                    elOrderSummary.innerHTML = "";

                    elOrderSummary.insertAdjacentHTML(
                        "beforeEnd",
                        results.data
                    );

                    if (couponElement) {
                        elOrderSummary.querySelector('[name="coupon"]').value =
                            couponElement.value;
                        couponElement = null;
                    }

                    if (rewardElement) {
                        elOrderSummary.querySelector(
                            ".summary__hide"
                        ).style.display = rewardElement.style.display;
                        elOrderSummary.querySelector('[name="reward"]').value =
                            rewardElement.querySelector('[name="reward"]')
                                .value || 0;
                        if (
                            !rewardElement
                                .querySelector(".reward__confirm")
                                .classList.contains("disabled")
                        ) {
                            elOrderSummary
                                .querySelector(".reward__confirm")
                                .classList.remove("disabled");
                        }

                        rewardElement = null;
                    }

                    d.querySelector("#mobile_summery_holder").textContent =
                        d.querySelector("#summary_total").textContent;
                }
            },
            _updateShippingView: async function (data = {}) {
                if (!hasShipping) return;
                var results = await http.updateShippingView(data);
                if (results.success && elShipping) {
                    elShipping.innerHTML = "";
                    elShipping.insertAdjacentElement(
                        "afterBegin",
                        elShippingTitle
                    );
                    elShipping.insertAdjacentHTML("beforeEnd", results.data);

                    if ($("[name=shipping_method]").length > 1) {
                        $(".contact-info-shipping-action").show();
                    } else {
                        $(".contact-info-shipping-action").hide();
                    }
                }
            },
            _updatePaymentView: async function () {
                var results = await http.updatePaymentView();
                if (results.success) {
                    $(elPayment).html(elPaymentTitle.outerHTML + results.data);
                }
            },
            _completeOrder: async function () {
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
                    log("❎ agreement section invalid!");
                    return false;
                }

                if (confirmOrderInput) {
                    log("✅ agreement section valid");
                }

                log("✅ complete order no. #%d", orderNo);

                $(".processing-payment").show();

                var confirmBtn = $(
                    "#confirm_payment .button, #confirm_payment .btn, #confirm_payment input[type=submit]"
                );

                var href = confirmBtn.attr("href");

                if (href != "" && href != undefined) {
                    d.location.href = href;
                    this._toggleDisableContainer();
                } else {
                    confirmBtn.trigger("click", [
                        function () {
                            this._toggleDisableContainer(),
                                this._spinnerShow(elContinueBtn);
                        }.bind(this),
                        function () {
                            this._toggleDisableContainer(),
                                this._spinnerHide(elContinueBtn);
                        }.bind(this),
                    ]);
                }
            },
            _spinnerShow: function (btn) {
                if (
                    !btn ||
                    !btn instanceof Element ||
                    !btn.classList.contains("js-spinner")
                )
                    return;
                btn.setAttribute("disabled", "1");
                btn.classList.add("js-spinner-show");
            },
            _spinnerHide: function (btn) {
                if (
                    !btn ||
                    !btn instanceof Element ||
                    !btn.classList.contains("js-spinner")
                )
                    return;
                btn.removeAttribute("disabled");
                btn.classList.remove("js-spinner-show");
            },
            goToStep: function (step, callback) {
                var currentStep,
                    forms = [],
                    stepsOrder = {
                        "information-step": 0,
                        "shipping-step": 1,
                        "payment-step": 2,
                    };

                currentStep = d.querySelector(".step.active").dataset.step;

                if (step == currentStep) return;

                if (step == "next") step = this._nextStep();

                if (step == "prev") {
                    step = this._prevStep();
                    if (!step) return; // no prev step!
                }

                // handle back to prev step
                if (
                    step in stepsOrder &&
                    currentStep in stepsOrder &&
                    stepsOrder[step] < stepsOrder[currentStep]
                ) {
                    this._animateToStep(step);
                    utilities.isFunction(callback) && callback();
                    return;
                }

                switch (step) {
                    case "information-step":
                        break;
                    case "shipping-step":
                        forms.push("information");
                        break;
                    case "payment-step":
                        forms.push("information");
                        if (hasShipping) forms.push("shipping");
                        break;
                    default:
                        forms.push("information");
                        if (hasShipping) forms.push("shipping");
                        forms.push("payment");
                }

                this._spinnerShow(elContinueBtn);

                this._validateSteps(
                    { forms, step },
                    () => {},
                    () => {},
                    async () => {
                        this._spinnerHide(elContinueBtn);
                        if (forms.indexOf("information") > -1) {
                            await this._updateInformationView(
                                utilities.formValues(
                                    d.querySelector("#information")
                                )
                            );
                        }

                        !step && hooks.completeOrder();
                    },
                    (errorObj) => {
                        this._spinnerHide(elContinueBtn);
                    }
                );
            },
            goToStepNext: function () {
                return this.goToStep("next");
            },
            goToStepPrev: function () {
                return this.goToStep("prev");
            },
            handleOnClickReturnTo: function (event) {
                event.preventDefault();
                switch (event.target.dataset.action) {
                    case "information-step":
                    case "shipping-step":
                    case "payment-step":
                        this.goToStepPrev();
                        return;
                    case "redirect-step":
                        w.location.href =
                            d.querySelector("[name=redirect_to]").value;
                        return;
                }
            },
            focus: function (selector) {
                var element = $(selector);
                if (element.length) {
                    element.focus();
                    // element.scrollIntoView();
                }
            },
            showAddressForm: async function () {
                var cAddr = d.querySelector('[name="address"]:checked');
                if (!cAddr) return;
                var addrContainer = cAddr.closest(".saved-address-container");
                if (!addrContainer) return;
                var addrEditBtn = addrContainer.querySelector(
                    ".info__details__edit"
                );
                if (!addrEditBtn) return;
                utilities.triggerEvent("click", addrEditBtn);
                var firstElement = addrContainer.querySelector("select");
                if (!firstElement) return;
                await utilities.wait(5);
                this._scrollTo(addrContainer, 250, () => {
                    this.focus(firstElement);
                });
            },
            validateAllSteps: function () {
                let forms = ["information"];
                if (hasShipping) {
                    forms.push("shipping");
                }
                forms.push("payment");

                this._validateSteps(
                    { forms: forms },
                    async (form) => {
                        var _;
                        if (form.id === "information") {
                            _ = await this._updateInformationView(
                                utilities.formValues(form)
                            );
                        }
                        return _;
                    },
                    async (form) => {
                        var _;
                        if (form.id === "information") {
                            _ = await this._updateInformationView(
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

                if ($(elAddAddressContainer).is(":hidden")) {
                    if (
                        elAddressMapTemplate &&
                        elAddAddressContainer.querySelector(
                            ".info__container__map"
                        )
                    ) {
                        elAddressMapTemplate.style.display = "none";
                        d.querySelector("#information").appendChild(
                            elAddressMapTemplate
                        );

                        elAddAddressContainer
                            .querySelector(".info__container__map")
                            .insertAdjacentElement(
                                "afterbegin",
                                elAddressMapTemplate
                            );
                        elAddressMapTemplate.style.display = "block";
                    }

                    if (elAddressFormTemplate) {
                        elAddressFormTemplate.style.display = "none";
                        d.querySelector("#information").appendChild(
                            elAddressFormTemplate
                        );

                        elAddAddressContainer
                            .querySelector(".info__container__data")
                            .insertAdjacentElement(
                                "afterbegin",
                                elAddressFormTemplate
                            );

                        elAddressFormTemplate.style.display = "block";
                    }

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
                let validated = await this.validateSingle(event);
                let countryInput = addressFields.find((f) => f.element.name === "country");

                var label =
                    event.target.options[
                        event.target.selectedIndex
                    ].textContent.trim();
                if (countryInput) {
                    countryInput.set(label);
                }
                
                addressFields
                .filter((f) => f.element.name == "zone_id")[0]
                ?.set([]);
                
                addressFields
                .filter((f) => f.element.name == "area_id")[0]
                    ?.set([]);
                
                if (validated) {
                    var results = await http.getZones(event.target.value);
                    addressFields
                        .filter((f) => f.element.name == "zone_id")[0]
                        ?.set(results.zone || []);
                }
                
                if ("onePage" === mode && hasShipping) {
                    await this._updateShippingView({country_id: event.target.value, zone_id: '0', area_id: '0'});
                    this._updatePaymentView();
                    this._updateOrderSummaryView();
                }
            },
            handleOnChangeZone: async function (event) {
                event = event || w.event || {};
                let validated = await this.validateSingle(event);
                addressFields
                    .filter((f) => f.element.name == "zone")[0]
                    ?.set(
                        event.target.options[
                            event.target.selectedIndex
                        ].textContent.trim()
                    );

                addressFields
                    .filter((f) => f.element.name == "area_id")[0]
                    .set([]);
                
                if (validated) {
                    var results = await http.getAreas(event.target.value);
                    addressFields
                        .filter((f) => f.element.name == "area_id")[0]
                        .set(results.area || []);
                }
                
                if ("onePage" === mode && hasShipping) {
                    await this._updateShippingView({zone_id: event.target.value, area_id: '0'});
                    this._updatePaymentView();
                    this._updateOrderSummaryView();
                }
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
                
                if ("onePage" === mode && hasShipping) {
                    await this._updateShippingView({area_id: event.target.value});
                    this._updatePaymentView();
                    this._updateOrderSummaryView();
                }
            },
            handleOnClickEditAddressBtn: async function (event) {
                event = event || w.event || {};
                event.preventDefault && event.preventDefault();
                var addressContainer = event.target.closest(
                    ".saved-address-container"
                );

                if (!addressContainer) return false;

                if (
                    addressContainer.querySelector(".info__container__map") &&
                    elAddressMapTemplate
                ) {
                    addressContainer
                        .querySelector(".info__container__map")
                        .insertAdjacentElement(
                            "afterbegin",
                            elAddressMapTemplate
                        );

                    elAddressMapTemplate.style.display = "block";
                }

                addressContainer
                    .querySelector(".info__container__data")
                    .insertAdjacentElement("afterbegin", elAddressFormTemplate);

                elAddressFormTemplate.style.display = "block";

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

                // show form box
                $(addressContainer)
                    .find(".saved-address__control , .saved-address__info")
                    .fadeOut(100, function () {
                        $(addressContainer)
                            .find(".info__container.edit--address")
                            .fadeIn();
                    });
            },
            handleOnChangeAddress: async function (event) {
                event = event || w.event || {};

                this._spinnerShow(elContinueBtn);

                $(elAddAddressContainer).stop().slideUp();

                $(".data__save")
                    .closest(".info__container.edit--address")
                    .fadeOut("fast")
                    .closest(".saved-address-container")
                    .find(".saved-address__control")
                    .fadeIn("fast");

                $(".saved-address__info").slideUp();
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
                        this._spinnerHide(elContinueBtn);
                    },
                    (errObj) => {
                        this._spinnerHide(elContinueBtn);
                    }
                );
            },
            handleOnClickAddressLabel(event) {
                if (["LABEL", "P"].indexOf(event.target.nodeName) === -1)
                    return;

                let target = $(event.target).closest(
                        ".saved-address-container"
                    ),
                    isChecked = Boolean(
                        $(target).find('[name="address"]:checked').length
                    );

                if (!isChecked) return;

                if ($(target).hasClass("active")) {
                    $(target)
                        .find(".saved-address__info")
                        .slideUp()
                        .closest(".saved-address-container")
                        .removeClass("active");
                } else {
                    $(target)
                        .find(".saved-address__info")
                        .slideDown()
                        .closest(".saved-address-container")
                        .addClass("active");
                }
            },
            handleOnClickCreateOrUpdateAddress: function () {
                this._spinnerShow(
                    elAddressFormTemplate
                        .closest(".info__container__data")
                        .querySelector(".data__save")
                );
                this._validateSteps(
                    { forms: ["information"] },
                    (form) => {},
                    (form) => {},
                    async () => {
                        await this._updateInformationView(
                            utilities.formValues(
                                d.querySelector("#information")
                            )
                        );
                        this._spinnerHide(
                            elAddressFormTemplate
                                .closest(".info__container__data")
                                .querySelector(".data__save")
                        );
                    },
                    (errObj) => {
                        this._spinnerHide(
                            elAddressFormTemplate
                                .closest(".info__container__data")
                                .querySelector(".data__save")
                        );
                    }
                );
            },
            handleOnChangeShippingMethod: async function (event) {
                if (!hasShipping) return;

                this._spinnerShow(elContinueBtn);

                this._validateSteps(
                    { forms: ["shipping"] },
                    null,
                    null,
                    () => {
                        this._spinnerHide(elContinueBtn);

                        var shippingLabel = d.querySelector(
                            "[data-total-code=shipping] .pricing__label"
                        );
                        if (!shippingLabel) return;
                        shippingLabel = shippingLabel.textContent.trim();
                        Array.from(
                            d.querySelectorAll(".contact-info-shipping-title")
                        ).map((el) => {
                            el.innerHTML = shippingLabel;
                        });
                    },
                    () => {
                        this._spinnerHide(elContinueBtn);
                    }
                );
            },
            handleOnChangePaymentMethod: async function (event) {
                this._spinnerShow(elContinueBtn);
                this._validateSteps(
                    { forms: ["payment"] },
                    (form) => {
                        $("#payment .saved-address__info").slideUp(
                            400,
                            function () {
                                this.remove();
                            }
                        );
                    },
                    null,
                    () => {
                        this._spinnerHide(elContinueBtn);
                    },
                    () => {
                        this._spinnerHide(elContinueBtn);
                    }
                );
            },
            handleOnClickApplyCoupon: async function (event) {
                event = event || w.event || {};
                event.preventDefault && event.preventDefault();

                var _this = this,
                    forms = ["coupon"];

                _this._toggleDisableContainer();
                _this._spinnerShow(event.target);

                validator.validateSteps(
                    forms.slice(0),
                    function beforeValidateForm(form) {
                        _this._resetFormErrors(form);
                    },
                    function jsAfterValidateForm(form) {},
                    function ajaxAfterValidateForm(form) {},
                    function success() {
                        _this._toggleDisableContainer();
                        _this._spinnerHide(event.target);
                    },
                    async function fail(form, errors, type) {
                        if ("AJAX-VALIDATION" === type) {
                            await _this._updateOrderSummaryView();
                        }

                        _this._spinnerHide(event.target);

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
            handleOnClickIncrementDecrementPoint: function (event) {
                const isNegative = $(event.target)
                    .closest(".btn-minus")
                    .is(".btn-minus");

                const input = $(event.target)
                    .closest(".input-group")
                    .find("input");

                if (input.is("input")) {
                    input[0][isNegative ? "stepDown" : "stepUp"]();
                }

                utilities.triggerEvent("keyup", input.get(0));
            },
            handleOnPointChange: function (event) {
                const value = parseInt(event.target.value);
                if (!isNaN(value) && value > 0) {
                    $(".reward__confirm").removeClass("disabled");
                } else {
                    $(".reward__confirm").addClass("disabled");
                }
            },
            handleOnClickApplyReward: async function (event) {
                event = event || w.event || {};
                event.preventDefault && event.preventDefault();

                var forms = ["reward"];

                this._toggleDisableContainer();
                this._spinnerShow(event.target);

                validator.validateSteps(
                    forms.slice(0),
                    function beforeValidateForm(form) {
                        ux._resetFormErrors(form);
                    },
                    function jsAfterValidateForm(form) {},
                    function ajaxAfterValidateForm(form) {},
                    function success() {
                        ux._toggleDisableContainer();
                        ux._spinnerHide(event.target);
                    },
                    async function fail(form, errors, type) {
                        if ("AJAX-VALIDATION" === type) {
                            await ux._updateOrderSummaryView();
                        }

                        ux._toggleDisableContainer();
                        ux._spinnerHide(event.target);

                        form = d.getElementById(form);

                        if (!form) return;

                        ux._showFormErrors(form, errors, type);
                    }
                );
            },
        };
    })(checkout);

    // return new instance
    return new checkout();
});
