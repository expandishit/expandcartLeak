/*eslint no-undef: 0*/
import "./bootstrap.js";
import "./plugins/datepicker.js";

// components
import LoginModal from "./components/login-modal/login-modal.js";
import VerifyModal from "./components/verify-modal/verify-modal";
import RegisterModal from "./components/register-modal/register-modal.js";
import AddressFormModal from "./components/address-form-modal/address-form-modal.js";

// plugins
import Resources from "./plugins/resources.js";
import Translator from "./plugins/translator.js";
import Validator from "./plugins/validator.js";
import { addQueryParam, mergeDeep, redirect, toArray } from "./helper/helpers";

// styles
import "intl-tel-input/build/css/intlTelInput.css";
import "./index.scss";

export default class Loginjs {
    props = {};
    view = {};
    hooks = {};

    constructor(props = {}) {
        this.props = mergeDeep(
            {
                lang: "en",
                storeCode: "",
                ajax: {
                    baseURL: "",
                },
                customer: {},
                loginWithPhone: 0,
                connectionState: "online",
            },
            props
        );

        // setup plugins
        new Resources(this);
        new Translator(this);
        new Validator(this);

        // views
        this.view.loginModal = new LoginModal(this);
        this.view.verifyModal = new VerifyModal(this);
        this.view.registerModal = new RegisterModal(this);
        this.view.addressFormModal = new AddressFormModal(this);
    }

    beforeRender() {
        // set all links to inactive before render the plugin
        let head = document.head || document.getElementsByTagName("head")[0];
        if (head) {
            head.insertAdjacentHTML(
                "beforeend",
                `<style>.account-login a {pointer-events: none;}</style>`
            );
        }

        return Promise.all([
            Translator.initialize(this),
            LoginModal.initialize(this),
            VerifyModal.initialize(this),
            RegisterModal.initialize(this),
            AddressFormModal.initialize(this),
        ]).then(() => {
            return new Promise((resolve) => {
                // Add parent element modal
                document.body.insertAdjacentHTML(
                    "beforeend",
                    '<div class="loginjs__modal-container" style="display:none;"/>'
                );

                // network connection alert
                document.body.insertAdjacentHTML(
                    "afterbegin",
                    `<div class="connection connection-warn connection-full border-top-0 text-center text-bold connection-warn">${this.trans(
                        "network_connection_poor"
                    )}</div>`
                );

                this.handleNetworkConnectionStatus();

                resolve(1);
            });
        });
    }

    handleNetworkConnectionStatus() {
        var _this = this,
            sendRequest = XMLHttpRequest.prototype.send;

        XMLHttpRequest.prototype.send = function () {
            if ("online" === _this.props.connectionState) {
                _this.handleOnConnectionOnline();
                try {
                    sendRequest.apply(this, arguments);
                } catch (error) {
                    console.log(error);
                }
            } else {
                _this.handleOnConnectionOffline();
            }
        };
    }

    handleOnConnectionOffline() {
        var connectionWarn = document.querySelector(".connection-warn");
        connectionWarn && connectionWarn.classList.add("visible");
    }

    handleOnConnectionOnline() {
        var connectionWarn = document.querySelector(".connection-warn");
        connectionWarn && connectionWarn.classList.remove("visible");
    }

    handleOnClickCustomerLoginLink(event, searchParams) {
        this.view.loginModal.show(
            searchParams.has("seller"),
            "",
            searchParams.has("checkout")
        );
        return false;
    }

    prepareLoginElements(selector) {
        var elements = document.querySelectorAll(selector);
        if (!elements || !elements.length) return [];
        return Array.from(elements).map((element) => {
            if (element.nodeName === "A") {
                element.setAttribute("href", "javascript:void(0);");
                element.style.pointerEvents = "auto";
                element.classList.add("login--link");
                element.removeAttribute("data-toggle");
                element.removeAttribute("data-target");
            }

            // remove all previously attached click handles
            (element.getEventListeners("click") || []).map((event) =>
                element.removeEventListener(
                    event.type,
                    event.listener,
                    event.useCapture
                )
            );

            return element;
        });
    }

    elementMatch(elements, element) {
        if (!elements.length) return false;
        var currentElement = elements.splice(0, 1)[0];

        if (
            // currentElement.name === element.name &&
            // currentElement.value === element.value &&
            currentElement.id === element.id
        )
            return true;

        return this.elementMatch(elements, element);
    }

    render() {
        console.log("🚀 Login-js v1.0.0 library loaded.");
        const { libraryStatus, customer, loginSelectors } = this.props;
        let _this = this;
        this.beforeRender().then(() => {
            if (libraryStatus === "on") {
                if (!customer.logged_in) {
                    const INIT_LOGIN_ELEMENTS = {};

                    toArray(loginSelectors.customer?.login || []).map(
                        (selector) => {
                            INIT_LOGIN_ELEMENTS.login = (
                                INIT_LOGIN_ELEMENTS.login || []
                            ).concat(this.prepareLoginElements(selector));
                        }
                    );

                    toArray(loginSelectors.seller?.login || []).map(
                        (selector) => {
                            INIT_LOGIN_ELEMENTS.seller = (
                                INIT_LOGIN_ELEMENTS.seller || []
                            ).concat(this.prepareLoginElements(selector));
                        }
                    );

                    toArray(loginSelectors.checkout?.login || []).map(
                        (selector) => {
                            INIT_LOGIN_ELEMENTS.checkout = (
                                INIT_LOGIN_ELEMENTS.checkout || []
                            ).concat(this.prepareLoginElements(selector));
                        }
                    );

                    document.documentElement.addEventListener(
                        "click",
                        (event) => {
                            const { target } = event;

                            if (
                                INIT_LOGIN_ELEMENTS.login?.indexOf(target) > -1
                            ) {
                                event.preventDefault();
                                addQueryParam("sign_in", 1);
                                this.handleOnClickCustomerLoginLink(
                                    event,
                                    new URL(location).searchParams
                                );
                                return false;
                            }

                            if (
                                INIT_LOGIN_ELEMENTS.seller?.indexOf(target) > -1
                            ) {
                                event.preventDefault();
                                addQueryParam("sign_in", 1);
                                addQueryParam("seller", 1);
                                this.handleOnClickCustomerLoginLink(
                                    event,
                                    new URL(location).searchParams
                                );
                                return false;
                            }

                            if (
                                INIT_LOGIN_ELEMENTS.checkout?.indexOf(target) >
                                    -1 ||
                                this.elementMatch(
                                    INIT_LOGIN_ELEMENTS.checkout?.slice(0) ||
                                        [],
                                    target
                                )
                            ) {
                                addQueryParam("sign_in", 1);
                                addQueryParam("checkout", 1);
                                this.handleOnClickCustomerLoginLink(
                                    event,
                                    new URL(location).searchParams
                                );

                                event.preventDefault();
                                event.stopPropagation();
                                return false;
                            }
                        },
                        !1
                    );

                    // check if current url has sign_in param
                    var locationParams = new URL(location).searchParams;
                    if (locationParams.has("sign_in"))
                        this.view.loginModal.show(
                            locationParams.has("seller"),
                            "",
                            locationParams.has("checkout")
                        );
                } else {
                    // set as a clickable links
                    var head =
                        document.head ||
                        document.getElementsByTagName("head")[0];
                    head &&
                        head.insertAdjacentHTML(
                            "beforeend",
                            `<style>.account-login a {pointer-events: auto;}</style>`
                        );

                    // setup hooks events
                    const updateProfileHook = require("./hooks/update-customer-profile.js");
                    const resetProfileFormHook = require("./hooks/reset-customer-profile-form.js");
                    const resetProfileFormHookInstance =
                        new resetProfileFormHook.default();
                    const updateOrCreateAddressHook = require("./hooks/update-or-add-address.js");
                    const deleteAddressHook = require("./hooks/delete-address.js");
                    const changeCountryHook = require("./hooks/change-country.js");

                    resetProfileFormHookInstance.setInitialForm(
                        document.querySelector("#customer_profile_form")
                    );

                    this.hooks.resetProfileForm = resetProfileFormHookInstance;

                    document.documentElement.addEventListener(
                        "click",
                        (event) => {
                            const { target } = event;
                            // form update profile
                            if (
                                target.classList.contains("btn--account-submit")
                            ) {
                                return updateProfileHook.default.call(
                                    this,
                                    event
                                );
                            }
                            // reset profile form
                            if (
                                target.classList.contains("btn--account-reset")
                            ) {
                                return resetProfileFormHookInstance.reset(
                                    event
                                );
                            }

                            // Addresses hooks
                            // 1. add, edit address
                            if (
                                target.classList.contains(
                                    "btn--address-create-update"
                                )
                            ) {
                                return updateOrCreateAddressHook.default.call(
                                    this,
                                    event
                                );
                            }

                            // 2. delete address
                            if (
                                target.classList.contains("btn--address-delete")
                            ) {
                                return deleteAddressHook.default.call(
                                    this,
                                    event
                                );
                            }

                            return false;
                        }
                    );

                    // country events
                    var counterChangedEvent;
                    document.documentElement.addEventListener(
                        "change",
                        (counterChangedEvent = (event) => {
                            var targetElement = event.detail || event.target;

                            var isCountry =
                                targetElement &&
                                ["country_id"].indexOf(targetElement.name) > -1;

                            if (!isCountry) return false;

                            changeCountryHook.default.call(this, targetElement);
                        })
                    );

                    document.documentElement.addEventListener(
                        "country-changed",
                        counterChangedEvent
                    );

                    window.addEventListener("session-expired", async () => {
                        for (let view in this.view) this.view[view].hide();
                        redirect(""); // reload page
                    });
                }
            } else {
                let head =
                    document.head || document.getElementsByTagName("head")[0];
                if (head) {
                    head.insertAdjacentHTML(
                        "beforeend",
                        `<style>.account-login a {pointer-events: unset;}</style>`
                    );
                }
            }

            // Network connection events
            window.addEventListener(
                "online",
                () => {
                    this.props.connectionState = "online";
                    this.handleOnConnectionOnline();
                },
                !1
            );
            window.addEventListener(
                "offline",
                () => {
                    this.props.connectionState = "offline";
                    this.handleOnConnectionOffline();
                },
                !1
            );

            $(function () {
                // Generic datepicker three inputs
                $(".my-account")
                    .closest(".inner--my-account")
                    .find("#content")
                    .addClass("container no-padding-xs");
                $(".my-account").closest(".col-md-9").removeClass("col-md-9");

                jQuery(".three-dob-datepicker").dateDropdowns();
                $(".date-dropdowns .day option:first-child").text(
                    _this.trans("day")
                );
                $(".date-dropdowns .month option:first-child").text(
                    _this.trans("month")
                );
                $(".date-dropdowns .month option:nth-child(2)").text(
                    _this.trans("janMonth")
                );
                $(".date-dropdowns .month option:nth-child(3)").text(
                    _this.trans("febMonth")
                );
                $(".date-dropdowns .month option:nth-child(4)").text(
                    _this.trans("marMonth")
                );
                $(".date-dropdowns .month option:nth-child(5)").text(
                    _this.trans("aprMonth")
                );
                $(".date-dropdowns .month option:nth-child(6)").text(
                    _this.trans("mayMonth")
                );
                $(".date-dropdowns .month option:nth-child(7)").text(
                    _this.trans("JunMonth")
                );
                $(".date-dropdowns .month option:nth-child(8)").text(
                    _this.trans("julMonth")
                );
                $(".date-dropdowns .month option:nth-child(9)").text(
                    _this.trans("augMonth")
                );
                $(".date-dropdowns .month option:nth-child(10)").text(
                    _this.trans("sepMonth")
                );
                $(".date-dropdowns .month option:nth-child(11)").text(
                    _this.trans("octMonth")
                );
                $(".date-dropdowns .month option:nth-child(12)").text(
                    _this.trans("novMonth")
                );
                $(".date-dropdowns .month option:nth-child(13)").text(
                    _this.trans("decMonth")
                );

                $(".date-dropdowns .year option:first-child").text(
                    _this.trans("year")
                );

                $(".day").append('<span class="seprate">-</span>');
                //dash for select
                $(".date-dropdowns select").after(
                    '<span class="select-dash"></span>'
                );
                $(".order-success .continue a").addClass("bg-color");
            });
        });

        // __FOR_TEST__
        return this;
    }
}
