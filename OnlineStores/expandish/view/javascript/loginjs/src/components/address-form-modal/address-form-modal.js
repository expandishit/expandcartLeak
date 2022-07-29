/*eslint no-undef: 0*/
import intlTelInput from "intl-tel-input";
import {
    getElementInArray,
    isOdd,
    closestParentCallable,
} from "../../helper/helpers";
import Modal from "../modal/modal";
import "./address-form-modal.scss";
import "intl-tel-input/build/css/intlTelInput.css";

export default class AddressFormModal extends Modal {
    addressId = null;
    callbackSuccess = null;
    fields = [];
    mapIsLoaded = false;

    // eslint-disable-next-line no-unused-vars
    static async initialize(app) {}

    async show(addressId = null, callbackSuccess) {
        this.addressId = addressId;
        this.callbackSuccess = callbackSuccess;

        var result = await this.api.getAddressFields(addressId);

        if (result.success) {
            this.fields = result.fields;
            this.render();
            $(".loginjs__modal-container").show();
            $(`.${this.modalClassName}`).modal("show");
        }
    }

    hide() {
        $(`.${this.modalClassName}`).modal("hide");
    }

    handleOnHidden() {
        $(".loginjs__modal-container").html("");
        $(".loginjs__modal-container").hide();
    }

    render() {
        var formTitle = this.trans(
            this.addressId ? "edit_your_address" : "add_address"
        );

        var modal = $(`
            <div class="modal fade ${
                this.modalClassName
            }" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="false" data-keyboard="false">
                <div class="modal-dialog modal-lg modal-dialog-centered modal--address" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h5 class="modal-title">${formTitle}</h5>
                        </div>
                        <div class="modal-body">
                            <form id="login-js__form" novalidate>
                                <div class="row">
                                    ${this.fields
                                        // eslint-disable-next-line no-unused-vars
                                        .map((field, _i) => {
                                            field = this.renderField(field);
                                            // if (isOdd(i)) {
                                            //     field += `<div class="clearfix"></div>`;
                                            // }

                                            return field;
                                        })
                                        .join("")}
                                        ${
                                            this.addressId
                                                ? `<input type="hidden" name="address_id" value="${this.addressId}" />`
                                                : ""
                                        }
                                </div>
                            </form>                            
                        </div>
                        <div class="modal-footer d-flex">
                            <button type="submit" form="login-js__form" class="btn btn-inline login-js__btn-submit bg-color">
                                <div class="page-loader-container"><span class="page-loader-container__account"></span></div>
                                <span style="pointer-events: none;">${this.trans(
                                    this.addressId
                                        ? "btn_save_changes"
                                        : "btn_submit"
                                )}</span>
                            </button>
                            <button type="button" class="btn cancel-btn" data-dismiss="modal">${this.trans(
                                "btn_cancel"
                            )}</button>
                        </div>
                    </div>
                </div>
            </div>
        `);

        $(".loginjs__modal-container").html(modal);

        $(`.${this.modalClassName}`).on(
            "hidden.bs.modal",
            this.handleOnHidden.bind(this)
        );

        var _this = this;

        $("#login-js__form").on("submit", (event) => {
            event.preventDefault();
            event.stopPropagation();
            setTimeout(() => {
                this.handleSubmit(event);
            }, 0);
        });

        var country = $(`select[name=country_id]`),
            zone = $(`select[name=zone_id]`);

        if (country.length) {
            country.on("change", function () {
                var country = _this.getCountry(
                    _this.props.countries.slice(),
                    this.value
                );

                if (country) {
                    _this.renderZones(country);
                }
            });

            // required to render current country zones and area
            country.trigger("change");

            zone.on("change", function () {
                _this.renderAreas(this.value);
            });
        }

        // handle init map
        if (this.settingsHasValidGoogleMap()) this.initializeAddressMap();

        // telephone js
        var modalSelector = document.querySelector("." + this.modalClassName);
        var input = modalSelector.querySelector("input[type=tel]");
        // var currentCountry = this.getCountry(
        //     this.props.countries,
        //     this.props.countryId
        // );
        if (input) {
            // var name = input.name;
            // input.setAttribute("name", "");
            this.iti = intlTelInput(input, {
                initialCountry: "auto",
                nationalMode: true,
                separateDialCode: !true,
                autoPlaceholder: "aggressive",
                formatOnDisplay: true,
                preferredCountries: [],
                responsiveDropdown: true,
                placeholderNumberType: "MOBILE",
                // hiddenInput: name,
                geoIpLookup: function (callback) {
                    $.get("https://ipinfo.io", function () {}, "jsonp").always(
                        function (resp) {
                            var countryCode =
                                resp && resp.country ? resp.country : "us";
                            callback(countryCode);
                        }
                    );
                },
                utilsScript: "expandish/view/javascript/iti/js/utils.js",
            });

            // allow number only
            input.addEventListener("keypress", function (e) {
                e.stopPropagation ? e.stopPropagation() : (e.cancelBubble = !0);
                "number" != typeof e.which && (e.which = e.keyCode);
                if (
                    [13, 43, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57].indexOf(
                        e.which
                    ) === -1
                ) {
                    e.preventDefault();
                    return false;
                }
            });

            // input.addEventListener("keyup", () => {
            //     var value = this.iti.isValidNumber()
            //         ? this.iti.getNumber()
            //         : "";

            //     modalSelector.querySelector(
            //         "input[name=" + name + "]"
            //     ).value = value;
            // });

            input.iti = this.iti;

            if (country.length && !country.val().length) {
                var detectedCountryIntervalIndex;
                setInterval(
                    (detectedCountryIntervalIndex = () => {
                        var selectedCountry = this.iti.getSelectedCountryData();
                        if (selectedCountry && "iso2" in selectedCountry) {
                            var detectedCountryObj = getElementInArray(
                                _this.props.countries,
                                (country) =>
                                    country["iso_code_2"] ==
                                    selectedCountry.iso2.toUpperCase()
                            );

                            var index =
                                _this.props.countries.indexOf(
                                    detectedCountryObj
                                );

                            var countrySelect = document.querySelector(
                                '[name="country_id"]'
                            );

                            if (
                                countrySelect &&
                                !countrySelect.value.length &&
                                index > -1
                            ) {
                                countrySelect.selectedIndex = ++index;
                                $(countrySelect).trigger("change");
                            }
                            clearInterval(detectedCountryIntervalIndex);
                            return;
                        }
                    }),
                    100
                );
            }
        }
    } // end render

    getCountry(countries, value, column = "country_id") {
        return getElementInArray(
            countries,
            (country) => country[column] == value
        );
    }

    getCurrentZoneId(fields) {
        var field = getElementInArray(
            fields,
            (field) => field["name"] == "zone_id"
        );

        return field ? field["value"] : null;
    }

    getCurrentAreaId(fields) {
        var field = getElementInArray(
            fields,
            (field) => field["name"] == "area_id"
        );

        return field ? field["value"] : null;
    }

    async renderZones(country) {
        var zone = $("select[name='zone_id']");
        var zoneId = this.getCurrentZoneId(this.fields);
        if (!zone.length) return;

        var html = `<option value="">${this.trans("text_select")}</option>`;

        var result = await this.api.getCountryZones(country.country_id);

        if (result.success) {
            if (result.data["zone"] != "") {
                for (i = 0; i < result.data["zone"].length; i++) {
                    html +=
                        '<option value="' +
                        result.data["zone"][i]["zone_id"] +
                        '"';

                    if (result.data["zone"][i]["zone_id"] == zoneId) {
                        html += ' selected="selected"';
                    }

                    html += ">" + result.data["zone"][i]["name"] + "</option>";
                }
            } else {
                // html += `<option value="0" selected="selected">${this.trans(
                //     "text_none"
                // )}</option>`;
            }

            zone.html(html);
            zone.trigger("change");
        }
    }

    async renderAreas(zoneId) {
        var area = $("select[name='area_id']");
        var areaId = this.getCurrentAreaId(this.fields);

        if (!area.length) return;

        var html = `<option value="">${this.trans("text_select")}</option>`;

        if (zoneId) {
            var result = await this.api.getZoneAreas(zoneId);
            if (
                result.success &&
                result.data &&
                result.data["area"] &&
                result.data["area"] != ""
            ) {
                for (i = 0; i < result.data["area"].length; i++) {
                    html +=
                        '<option value="' +
                        result.data["area"][i]["area_id"] +
                        '"';

                    if (result.data["area"][i]["area_id"] == areaId) {
                        html += ' selected="selected"';
                    }

                    html += ">" + result.data["area"][i]["name"] + "</option>";
                }
            } else {
                // html += `<option value="0" selected="selected">${this.trans(
                //     "text_none"
                // )}</option>`;
            }

            area.html(html);
        } else {
            // html += `<option value="0" selected="selected">${this.trans(
            //     "text_none"
            // )}</option>`;
            area.html(html);
        }
    }

    renderField(field) {
        let { name, placeholder, required, value } = field;
        placeholder = placeholder.replace(":", "");

        switch (name) {
            case "email":
            case "name":
            case "firstname":
            case "address_1":
            case "address_2":
            case "postcode":
            case "company":
            case "city":
                return `<div class="col-md-6"><div class="form-group"><label for="${name}">${placeholder} ${
                    required ? '<span class="required">*</span>' : ""
                }</label><input type="text" name="${name}" class="form-control" id="${name}" value="${value}" placeholder="${this.trans(
                    "enter_your"
                )} ${placeholder}"/><div class="messages"></div></div></div>`;
            case "telephone":
                return `<div class="col-md-6"><div class="form-group"><label for="${name}">${placeholder} ${
                    required ? '<span class="required">*</span>' : ""
                }</label><input type="tel" name="${name}" class="form-control" id="${name}" value="${value}" placeholder="${this.trans(
                    "enter_your"
                )} ${placeholder}"/><div class="messages"></div></div></div>`;
            case "country_id":
                return `<div class="col-md-6"><div class="form-group"><label for="country_id">${placeholder} ${
                    required ? '<span class="required">*</span>' : ""
                }</label><select name="country_id" class="form-control" id="country_id" ${
                    this.addressId
                        ? 'data-address_id="' + this.addressId + '"'
                        : ""
                }><option value="" selected="selected">${this.trans(
                    "text_select"
                )}</option>${this.renderCountries(
                    value
                )}</select> <div class="messages"></div></div></div>`;

            case "zone_id":
            case "area_id":
                return `<div class="col-md-6"><div class="form-group"><label for="${name}">${placeholder} ${
                    required ? '<span class="required">*</span>' : ""
                }</label><select name="${name}" class="form-control" id="${name}"><option value="" selected="selected">${this.trans(
                    "text_select"
                )}</option></select> <div class="messages"></div></div></div>`;

            case "default":
                return `<div class="col-md-6 d-flex-center"><div class="form-group"><label for="default">${placeholder} ${
                    required ? '<span class="required">*</span>' : ""
                }</label>
                <div>
                    <label class="radio-inline"><input type="radio" name="default" id="default_yes" value="1" ${
                        value ? "checked" : ""
                    }> ${this.trans("default_yes")}</label>
                    <label class="radio-inline"><input type="radio" name="default" id="default_no" value="0" ${
                        !value ? "checked" : ""
                    }> ${this.trans("default_no")}</label>
                </div>
                <div class="messages"></div>
            </div></div>`;
            case "location":
                return this.settingsHasValidGoogleMap()
                    ? `<div class="col-md-12"><div class="form-group">
                        <input type="hidden" value='${value}' name="location"/>
                        <div class="valid-api-key">
                        <div id="map"><div class="text-wrong">Invalid google map api key</div></div></div>
                        <div class="messages"/>
                    </div></div>`
                    : `<input type="hidden" value='${value}' name="location"/>`;
        }

        return "";
    }

    settingsHasValidGoogleMap() {
        return (
            this.props.map &&
            "status" in this.props.map &&
            this.props.map.status
        );
    }

    renderCountries(value) {
        return this.props.countries.map((country) => {
            return `<option value="${country.country_id}" ${
                country.country_id == value ? 'selected="selected"' : ""
            }>${country.name}</option>`;
        });
    }

    constraints() {
        let i,
            rules = {},
            field;

        for (i = 0; i < (this.fields || []).length; i++) {
            field = this.fields[i];
            if (field.required && field.name != "location") {
                rules[field.name] = {
                    presence: {
                        allowEmpty: false,
                        message:
                            "^" +
                            this.trans(
                                "required_input_" +
                                    (field.name == "telephone"
                                        ? "shipping_telephone"
                                        : field.name)
                            ),
                    },
                };
            }
        }
        return rules;
    }

    async handleSubmit(event) {
        event.preventDefault();
        event.stopPropagation();
        var hasError = false;

        if (!this.validator.validate(event.target, this.constraints()))
            hasError = true;

        if (this.iti) {
            if (!this.iti.isValidNumber()) {
                this.validator.showErrorsForInput(
                    event.target.querySelector("input[type=tel]"),
                    [this.trans("invalid_input_shipping_telephone")]
                );
                hasError = true;
            }
        }

        if (hasError) return false;

        // var button = $(".login-js__btn-submit");
        // button.length && button.attr("disabled", 1);
        this.showLoader();

        var data = new FormData(event.target);
        data.delete("telephone");
        data.append("telephone", this.iti.getNumber());

        var response = await (this.addressId
            ? this.api.updateAddress(data)
            : this.api.addAddress(data));

        this.hideLoader();

        // button.length && button.removeAttr("disabled");

        if (response.success === true) {
            this.hide();
            response.data &&
                this.handleOnSuccess(this.addressId, response.data);
        } else {
            var modalContainer = document.querySelector(".login--popup");
            for (var i in response.errors || {}) {
                var input =
                    modalContainer &&
                    modalContainer.querySelector("[name=" + i.trim() + "]");
                input &&
                    this.validator.showErrorsForInput(input, [
                        response.errors[i],
                    ]);
            }
        }
        // $(".empty-address").remove();
    }

    showLoader() {
        let btn = document.querySelector(".login-js__btn-submit");
        if (btn) {
            btn.setAttribute("disabled", 1);
            btn.classList.add("loading");
            btn.querySelector(".page-loader-container") &&
                btn
                    .querySelector(".page-loader-container")
                    .classList.add("active");
        }
    }

    hideLoader() {
        let btn = document.querySelector(".login-js__btn-submit");
        if (btn) {
            btn.removeAttribute("disabled");
            btn.classList.remove("loading");
            btn.querySelector(".page-loader-container") &&
                btn
                    .querySelector(".page-loader-container")
                    .classList.remove("active");
        }
    }

    initializeAddressMap() {
        var _this = this;
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

            if (options.shouldConfirmLocation) {
                var btnClass = "confirm-btn-" + Date.now();
                containerSelector
                    .querySelector("#map")
                    .insertAdjacentHTML(
                        "afterend",
                        '<div class="confirm_address"><button type="button" class="primary-btn ml-auto ' +
                            btnClass +
                            '">' +
                            (options.local.confirm_location ||
                                "Confirm Location") +
                            "</button></div>"
                    );
                document
                    .querySelector("." + btnClass)
                    .addEventListener("click", function () {
                        Object.prototype.toString.call(onClickConfirm) ===
                            "[object Function]" && onClickConfirm();
                    });
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
                return (
                    navigator.geolocation &&
                    (window.isSecureContext ||
                        location.hostname === "qaz123.expandcart.com")
                );
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

            function _confirmAddress(
                address,
                location,
                address_components,
                countryOnly = false
            ) {
                !countryOnly &&
                    inputAddress &&
                    address &&
                    address.length &&
                    (inputAddress.value = address);

                inputLocation &&
                    location &&
                    (inputLocation.value = location.lat + "," + location.lng);

                Object.prototype.toString.call(options.onConfirmLocation) ===
                    "[object Function]" &&
                    options.onConfirmLocation({ address, location });

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
                            // required to update telephone iso_code
                            var _event = new CustomEvent("country-changed", {
                                detail: inputCountry,
                            });
                            document.documentElement.dispatchEvent(_event);
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
                if (!countryOnly && options.shouldConfirmLocation) {
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
                var mapMaXZoomService = new google.maps.MaxZoomService();
                mapMaXZoomService.getMaxZoomAtLatLng(
                    defaultCoords,
                    function (r) {
                        var maxZoom = r.status === "OK" ? r.zoom : 20;
                        var minZoom = parseInt(
                            _this.addressId ? 3 * (maxZoom / 4) : maxZoom / 2.5
                        );

                        infoWindow = new google.maps.InfoWindow();
                        geocoder = new google.maps.Geocoder();
                        map = new google.maps.Map(
                            containerSelector.querySelector("#map"),
                            {
                                center: defaultCoords,
                                zoom: minZoom,
                                panControl: !1,
                                gestureHandling: "greedy",
                                streetViewControl: !1,
                                styles: styles,
                                // controlSize: 25
                                mapTypeControl: !1,
                            }
                        );

                        // map.setOptions({
                        // });

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
                            map.controls[
                                google.maps.ControlPosition.TOP_LEFT
                            ].push(buttonGetMyLocation);

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
                    }
                );
            })();
        }

        typeof google !== "undefined" &&
            initializeAddressMap({
                containerSelector: ".login--popup",
                lat: this.props.map.lat || 0,
                lng: this.props.map.lng || 0,
                local: {
                    enter_your_location: this.trans("enter_location"),
                    detect_my_location: this.trans("detect_my_location"),
                },
                countries: this.props.countries,
                shouldConfirmLocation: !true,
            });
    }

    handleOnSuccess(addressId, result) {
        if (!result) return false;

        var addressContainer;

        $(".btn--address-create-update").show();
        $(".empty-address").remove();

        if (addressId) {
            addressContainer = closestParentCallable(
                document.querySelector(`[data-address="${addressId}"]`),
                function (element) {
                    return element.classList.contains(
                        "address-info__container"
                    );
                }
            );

            if (!addressContainer) return false;

            addressContainer.setAttribute("data-location", result.location);

            addressContainer.querySelector(
                ".caption__result--header-building .caption__result--value"
            ).innerHTML = result.address_2;
            addressContainer.querySelector(
                ".caption__result--header-street .caption__result--value"
            ).innerHTML = result.address_1;
            addressContainer.querySelector(
                ".caption__result--footer-city .caption__result--value"
            ).innerHTML = result.area;
            addressContainer.querySelector(
                ".caption__result--footer-region .caption__result--value"
            ).innerHTML = result.zone;
            addressContainer.querySelector(
                ".caption__result--footer-country .caption__result--value"
            ).innerHTML = result.country;
        } else {
            var addressTemplateHtml = document.querySelector(
                ".address-preview-template"
            );

            if (!addressTemplateHtml) return false;

            addressContainer = addressTemplateHtml.cloneNode(true);

            addressContainer.classList.remove("address-preview-template");
            addressContainer.style.display = "block";

            addressContainer.setAttribute("data-location", result.location);

            addressContainer.querySelector(
                ".caption__result--header-building .caption__result--value"
            ).innerHTML = result.address_2;
            addressContainer.querySelector(
                ".caption__result--header-street .caption__result--value"
            ).innerHTML = result.address_1;
            addressContainer.querySelector(
                ".caption__result--footer-city .caption__result--value"
            ).innerHTML = result.area;
            addressContainer.querySelector(
                ".caption__result--footer-region .caption__result--value"
            ).innerHTML = result.zone;
            addressContainer.querySelector(
                ".caption__result--footer-country .caption__result--value"
            ).innerHTML = result.country;

            var l = addressContainer.querySelectorAll("A");
            l.length &&
                l.forEach((a) =>
                    a.setAttribute("data-address", result.address_id)
                );

            addressTemplateHtml.parentNode.insertBefore(
                addressContainer,
                addressTemplateHtml
            );
        }

        if (
            Object.prototype.toString.call(window.renderAddressMap) ===
            "[object Function]"
        ) {
            window.renderAddressMap(addressContainer);
        }

        return false;
    }
}
