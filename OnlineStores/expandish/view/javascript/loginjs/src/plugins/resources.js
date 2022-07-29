/*eslint no-undef: 0*/

import { mergeDeep } from "../helper/helpers";

export default class Resources {
    props = {};

    static initialState = {
        // to debug resources change it to true
        enableDebugMode: false,
    };

    constructor(app) {
        mergeDeep(app, this);
        this.props = app.props || {};
    }

    resolveUrl(url) {
        return (this.props.ajax.baseURL || "") + url;
    }

    resolveData(data) {
        if (data instanceof FormData || data instanceof Object)
            return new URLSearchParams(data).toString();
        return data;
    }

    resolveResponse(resolve, response, key, cache = false) {
        // debug
        if (Resources.initialState.enableDebugMode) {
            console.log(
                `${cache ? "Cache" : "Ajax"} [${key}] ${JSON.stringify(
                    response
                )}`
            );
        }

        // resolver
        resolve(response);

        // display login popup if session expired
        if (response.session_expired && window && "dispatchEvent" in window) {
            window.dispatchEvent(new CustomEvent("session-expired"));
        }
    }

    makeRequest(
        url,
        data = {},
        callbackSuccess,
        callBackError,
        method = "POST"
    ) {
        fetch(this.resolveUrl(url), {
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: this.resolveData(data),
            method: method,
            mode: "same-origin", // no-cors, *cors, same-origin
            cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
            credentials: "same-origin",
        })
            .then((response) => response.json())
            .then((response) => {
                callbackSuccess(response);
            })
            .catch((error) => {
                callBackError(error);
            });
    }

    api = {
        getLocalization: () => {
            return new Promise((resolve) => {
                if ("local" in Resources.initialState) {
                    this.resolveResponse(
                        resolve,
                        Resources.initialState.local,
                        "getLocalization",
                        true
                    );
                    return;
                }

                var dictionary = localStorage.getItem("identity_dictionary");

                if (dictionary) {
                    dictionary = JSON.parse(dictionary);
                    if (dictionary.code == this.props.lang) {
                        Resources.initialState.local = {
                            success: true,
                            dictionary,
                        };
                        this.resolveResponse(
                            resolve,
                            Resources.initialState.local,
                            "getLocalization"
                        );
                        return;
                    }
                }

                this.makeRequest(
                    "?route=account/identity/getLocalization",
                    "",
                    (response) => {
                        Resources.initialState.local = response;
                        this.resolveResponse(
                            resolve,
                            response,
                            "getLocalization"
                        );

                        localStorage.setItem(
                            "identity_dictionary",
                            JSON.stringify(response.dictionary)
                        );
                    },
                    // eslint-disable-next-line no-unused-vars
                    (_error) => {
                        this.resolveResponse(
                            resolve,
                            { success: false },
                            "getLocalization"
                        );
                    },
                    "POST",
                    true
                );
            });
        },
        getIdentityProps: () => {
            return new Promise((resolve) => {
                $.ajax({
                    url: this.resolveUrl(
                        "?route=account/identity/getIdentityProps"
                    ),
                    data: {},
                    type: "POST",
                })
                    .done((response) => {
                        this.resolveResponse(
                            resolve,
                            response,
                            "getIdentityProps"
                        );
                    })
                    .fail(() => {
                        this.resolveResponse(
                            resolve,
                            { success: false },
                            "getIdentityProps"
                        );
                    });
            });
        },
        sendVerificationCode: (attributes) => {
            return new Promise((resolve) => {
                this.makeRequest(
                    "?route=account/identity/sendVerificationCode",
                    attributes,
                    (response) => {
                        this.props.customer = this.props.customer || {};
                        if (response.success) {
                            this.props.customer.id = response.data;
                            this.props.customer.verification_provider =
                                response.verification_provider;
                        }
                        this.resolveResponse(
                            resolve,
                            response,
                            "sendVerificationCode"
                        );
                    },
                    // eslint-disable-next-line no-unused-vars
                    (_error) => {
                        this.resolveResponse(
                            resolve,
                            { success: false },
                            "sendVerificationCode"
                        );
                    }
                );
            });
        },
        verifyCode: (attributes) => {
            return new Promise((resolve) => {
                this.makeRequest(
                    "?route=account/identity/verifyCode",
                    attributes,
                    (response) => {
                        this.resolveResponse(resolve, response, "verifyCode");
                    },
                    // eslint-disable-next-line no-unused-vars
                    (_error) => {
                        this.resolveResponse(
                            resolve,
                            { success: false },
                            "verifyCode"
                        );
                    }
                );
            });
        },
        registerCustomer: (attributes) => {
            return new Promise((resolve) => {
                this.makeRequest(
                    "?route=account/identity/registerCustomer",
                    attributes,
                    (response) => {
                        if (
                            response.success === true &&
                            response.fields &&
                            response.fields.length
                        ) {
                            this.props.registerFields = response.fields;
                        } else {
                            this.props.registerFields = [];
                        }
                        this.resolveResponse(
                            resolve,
                            response,
                            "registerCustomer"
                        );
                    },
                    // eslint-disable-next-line no-unused-vars
                    (_error) => {
                        this.resolveResponse(
                            resolve,
                            { success: false },
                            "registerCustomer"
                        );
                    }
                );
            });
        },
        getCustomer: () => {
            return new Promise((resolve) => {
                $.ajax({
                    url: this.resolveUrl("?route=account/identity/getCustomer"),
                    data: {},
                    type: "POST",
                })
                    .done((response) => {
                        this.resolveResponse(resolve, response, "getCustomer");
                    })
                    .fail(() => {
                        this.resolveResponse(
                            resolve,
                            { success: false },
                            "getCustomer"
                        );
                    });
            });
        },
        validateCustomerProfile: (attributes) => {
            return new Promise((resolve) => {
                $.ajax({
                    url: this.resolveUrl(
                        "?route=account/identity/validateCustomerProfile"
                    ),
                    type: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    data: this.resolveData(attributes),
                })
                    .done((response) => {
                        this.props.customer = this.props.customer || {};

                        if (response.success) {
                            this.props.customer.id = response.id;

                            if (response.has_verification) {
                                this.props.customer.verification_provider =
                                    response.verification_provider;
                            }
                        }

                        this.resolveResponse(
                            resolve,
                            response,
                            "validateCustomerProfile"
                        );
                    })
                    .fail(() => {
                        this.resolveResponse(
                            resolve,
                            { success: false },
                            "validateCustomerProfile"
                        );
                    });
            });
        },
        updateCustomer: (attributes) => {
            return new Promise((resolve) => {
                $.ajax({
                    url: this.resolveUrl(
                        "?route=account/identity/updateCustomer"
                    ),
                    type: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    data: this.resolveData(attributes),
                })
                    .done((response) => {
                        this.resolveResponse(
                            resolve,
                            response,
                            "updateCustomer"
                        );
                    })
                    .fail(() => {
                        this.resolveResponse(
                            { success: false },
                            response,
                            "updateCustomer"
                        );
                    });
            });
        },
        /** address apis */
        getAddresses: () => {
            return new Promise((resolve) => {
                $.ajax({
                    url: this.resolveUrl(
                        "?route=account/identity/getAddresses"
                    ),
                    data: {},
                    type: "POST",
                })
                    .done((response) => {
                        this.resolveResponse(resolve, response, "getAddresses");
                    })
                    .fail(() => {
                        this.resolveResponse(
                            resolve,
                            { success: false },
                            "getAddresses"
                        );
                    });
            });
        },
        getAddressFields: (address_id) => {
            return new Promise((resolve) => {
                $.ajax({
                    url: this.resolveUrl(
                        "?route=account/identity/getAddressFields"
                    ),
                    type: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    data: this.resolveData({ address_id }),
                })
                    .done((response) => {
                        this.resolveResponse(
                            resolve,
                            response,
                            "getAddressFields"
                        );
                    })
                    .fail(() => {
                        this.resolveResponse(
                            resolve,
                            { success: false },
                            "getAddressFields"
                        );
                    });
            });
        },
        addAddress: (attributes) => {
            return new Promise((resolve) => {
                $.ajax({
                    url: this.resolveUrl("?route=account/identity/addAddress"),
                    type: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    data: this.resolveData(attributes),
                })
                    .done((response) => {
                        this.resolveResponse(resolve, response, "addAddress");
                    })
                    .fail(() => {
                        this.resolveResponse(
                            resolve,
                            { success: false },
                            "addAddress"
                        );
                    });
            });
        },
        updateAddress: (attributes) => {
            return new Promise((resolve) => {
                $.ajax({
                    url: this.resolveUrl(
                        "?route=account/identity/updateAddress"
                    ),
                    type: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    data: this.resolveData(attributes),
                })
                    .done((response) => {
                        this.resolveResponse(
                            resolve,
                            response,
                            "updateAddress"
                        );
                    })
                    .fail(() => {
                        this.resolveResponse(
                            resolve,
                            { success: false },
                            "updateAddress"
                        );
                    });
            });
        },
        deleteAddress: (address_id) => {
            return new Promise((resolve) => {
                $.ajax({
                    url: this.resolveUrl(
                        "?route=account/identity/deleteAddress"
                    ),
                    type: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    data: this.resolveData({ address_id }),
                })
                    .done((response) => {
                        this.resolveResponse(
                            resolve,
                            response,
                            "deleteAddress"
                        );
                    })
                    .fail(() => {
                        this.resolveResponse(
                            resolve,
                            { success: false },
                            "deleteAddress"
                        );
                    });
            });
        },
        /** others */
        getCountryZones: (country_id) => {
            Resources.initialState.countryZones =
                Resources.initialState.countryZones || {};
            return new Promise((resolve) => {
                if (country_id in Resources.initialState.countryZones) {
                    this.resolveResponse(
                        resolve,
                        Resources.initialState.countryZones[country_id],
                        "getCountryZones",
                        true
                    );
                    return;
                }

                $.ajax({
                    url: this.resolveUrl("?route=account/identity/country"),
                    type: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    data: this.resolveData({ country_id }),
                })
                    .done((response) => {
                        Resources.initialState.countryZones[country_id] =
                            response;
                        this.resolveResponse(
                            resolve,
                            response,
                            "getCountryZones"
                        );
                    })
                    .fail(() => {
                        let response = { success: false };
                        Resources.initialState.countryZones[country_id] =
                            response;
                        this.resolveResponse(
                            resolve,
                            response,
                            "getCountryZones"
                        );
                    });
            });
        },
        getZoneAreas: (zone_id) => {
            Resources.initialState.zoneAreas =
                Resources.initialState.zoneAreas || {};

            return new Promise((resolve) => {
                if (zone_id in Resources.initialState.zoneAreas) {
                    this.resolveResponse(
                        resolve,
                        Resources.initialState.zoneAreas[zone_id],
                        "getZoneAreas",
                        true
                    );
                    return;
                }

                $.ajax({
                    url: this.resolveUrl("?route=account/identity/zone"),
                    type: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    data: this.resolveData({ zone_id }),
                })
                    .done((response) => {
                        Resources.initialState.zoneAreas[zone_id] = response;
                        this.resolveResponse(resolve, response, "getZoneAreas");
                    })
                    .fail(() => {
                        let response = { success: false };
                        Resources.initialState.zoneAreas[zone_id] = response;
                        this.resolveResponse(resolve, response, "getZoneAreas");
                    });
            });
        },
    };
}
