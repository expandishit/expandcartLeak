/*eslint no-undef: 0*/
import intlTelInput from "intl-tel-input";
import "./login-modal.scss";
import {
    addQueryParam,
    getElementInArray,
    redirect,
    removeQueryParam,
    toArray,
} from "../../helper/helpers";
import Modal from "../modal/modal.js";
import {
    loader,
    welcome as welcomeIcon,
    google,
    twitter,
    facebpock,
    instgram,
} from "../../svg";
class LoginModal extends Modal {
    iti = null;
    identity = "";
    onHideCallback = null;

    show(isSeller = false, identity = "", isCheckout = false) {
        addQueryParam("sign_in", 1);
        isSeller && addQueryParam("seller", 1);
        isCheckout && addQueryParam("checkout", 1);

        this.isSeller = isSeller;
        this.isCheckout = isCheckout;
        this.identity = identity;

        this.render();
        $(".loginjs__modal-container").show();
        $(`.${this.modalClassName}`).modal("show");
        this.onHideCallback = null;
    }

    hide() {
        $(`.${this.modalClassName}`).modal("hide");
    }

    handleOnHidden() {
        $(".loginjs__modal-container").html("");
        $(".loginjs__modal-container").hide();
        removeQueryParam("seller");
        removeQueryParam("checkout");
        removeQueryParam("sign_in");
        this.identity = "";
        Object.prototype.toString.call(this.onHideCallback) ===
            "[object Function]" && this.onHideCallback();
    }

    constraints() {
        var loginWithPhone = this.props.loginWithPhone;

        var rules = {
            identity: {
                // Email or phone is required
                presence: {
                    allowEmpty: false,
                    message:
                        "^" +
                        this.trans(
                            "required_input_" +
                                (loginWithPhone ? "telephone" : "email")
                        ),
                },
            },
        };

        if (!loginWithPhone) {
            rules.identity.email = {
                message: "^" + this.trans("invalid_input_email"),
            };
        }

        return rules;
    }

    async handleSubmit(event) {
        var button = $(".login-js__btn-submit");
        button && button.removeAttr("disabled");

        var form = event.target;
        form.querySelector('input[name="identity"]').value = form
            .querySelector('input[name="identity"]')
            .value.trim();

        if (!this.validator.validate(event.target, this.constraints())) {
            return;
        }

        var telInput =
            event.target.querySelector("input[type=tel]") &&
            window.intlTelInputGlobals
                ? window.intlTelInputGlobals.getInstance(
                      event.target.querySelector("input[type=tel]")
                  )
                : null;

        if (telInput) {
            if (!telInput.isValidNumber()) {
                this.validator.showErrorsForInput(
                    event.target.querySelector("input[type=tel]"),
                    [this.trans("invalid_input_telephone")]
                );
                return;
            }
        }

        button.length && button.attr("disabled", 1);
        $(".login-js__btn-submit span").hide();
        button.length && button.addClass("check-code");

        var data = (() => {
            if (!this.props.loginWithPhone) {
                return new FormData(form);
            }

            form = form.cloneNode(true);
            form.querySelector('input[name="identity"]').value = telInput
                .getNumber();

            return new FormData(form);
        })();

        var result = await this.api.sendVerificationCode(data);

        var identity = data.get("identity");

        result.errors = result.errors || {};

        if (result.success) {
            this.onHideCallback = () => {
                this.view.verifyModal.show(
                    this.handleOnVerificationCodeSuccess,
                    async () => {
                        var result = await this.api.sendVerificationCode(data);
                        return result;
                    },
                    () => {
                        this.view.loginModal.show(
                            this.isSeller,
                            identity,
                            this.isCheckout
                        );
                    },
                    identity,
                    this.isSeller
                );
            };

            this.hide();

            return;
        }

        button.length && button.removeAttr("disabled");
        $(".login-js__btn-submit span").show();
        button.length && button.removeClass("check-code");

        if ("identity" in result.errors) {
            this.validator.showErrorsForInput(
                event.target.querySelector('input[name="identity"]'),
                toArray(result.errors.identity)
            );
        } else if ("warning" in result.errors) {
            this.displayWarningErrors(result.errors.warning);
        } else if ("message" in result) {
            this.displayWarningErrors(result.message);
        } else if ("redirect" in result) {
            redirect(result.redirect);
        }

        return;
    }

    handleOnVerificationCodeSuccess(response) {
        if (response.fields && response.fields.length && !response.is_seller) {
            this.view.registerModal.show(response);
            return false;
        }

        redirect(response.redirect);
        return true;
    }

    getCountry(countries, value, column = "country_id") {
        return getElementInArray(
            countries,
            (country) => country[column] == value
        );
    }

    renderLoginWithField() {
        var inputName = this.props.loginWithPhone ? "telephone" : "email",
            placeholder = this.props.loginWithPhone
                ? "entry_telephone"
                : "fixed_email_placeholder";

        switch (inputName) {
            case "telephone":
                return `<input style="direction: ltr;text-align: left;" type="tel" id="identity" name="identity" class="form-control" autofocus="1" value="${this.identity}"/>`;
            default:
                return `<input type="text" id="identity" class="form-control" name="identity" placeholder="${this.trans(
                    placeholder
                )}" autofocus="1" value="${this.identity}"/>`;
        }
    }

    render() {
        const { loginWithPhone, lang, socialLogin } = this.props;
        let placeholder = loginWithPhone ? "entry_telephone" : "entry_email";
        let modal = $(`
            <div class="modal fade ${this.modalClassName} ${
            this.isSeller ? `${this.modalClassName}-seller` : ""
        }" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="false" data-keyboard="false">
            <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                <div class="modal-content">
            
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                
                    ${this.renderHeader()}
                </div> 
                <div class="modal-body">
                    <div class="login-js__warning-area"></div>
                    <form id="login-js__form-login" novalidate>
                        <div class="form-group"> 
                            <label for="identity">${this.trans(
                                placeholder
                            )} <span class="required">*</span></label> 
                            ${this.renderLoginWithField()}
                            <div class="messages"></div>
                        </div>
                        <input type="hidden" name="lang" value="${lang}"/> 
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" form="login-js__form-login" class="btn btn-primary login-js__btn-submit btn-inline bg-color">
                    <span>${this.trans("btn_login")}</span>
                        ${loader}
                    </button>
                        ${
                            !this.isSeller &&
                            socialLogin &&
                            socialLogin.status &&
                            socialLogin.content.length > 10
                                ? `<div class="modal-footer__container">
                                <div class="login--popup-social">
                                    ${socialLogin.content}
                                </div></div>
                        `
                                : ""
                        } 
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

        $(`.${this.modalClassName}`).on("shown.bs.modal", function () {
            $("#identity").focus();
        });

        $(document).ready(() => {
            $(".login--popup-social").before(
                `<p class="login-via">${this.trans("loginVia")}</p>`
            );
            $(".login--popup-social #dsl_facebook_button").html(`${facebpock}`);
            $(".login--popup-social #dsl_google_button").html(`${google}`);
            $(".login--popup-social #dsl_twitter_button").html(`${twitter}`);
            $(".login--popup-social #dsl_instagram_button").html(`${instgram}`);
            if ($(".login--popup-social  .dsl-button").length < 3) {
                // console.log('less than 3 please add class');
                $(".login--popup-social #d_social_login").addClass(
                    "center--social"
                );
            } else {
                // console.log('greater than 3 dont make anything');
            }
        });

        $("#login-js__form-login").on("submit", (event) => {
            event.preventDefault();
            event.stopPropagation();
            var button = $(".login-js__btn-submit");
            button.length && button.attr("disabled", 1);
            this.clearWarningErrors();
            this.handleSubmit(event);
        });

        // telephone js
        var modalSelector = document.querySelector("." + this.modalClassName);
        var input = modalSelector.querySelector("input[type=tel]");
        if (input) {
            intlTelInput(
                input,
                Object.assign(
                    {
                        initialCountry: "auto",
                        nationalMode: true,
                        separateDialCode: !true,
                        autoPlaceholder: "aggressive",
                        formatOnDisplay: true,
                        preferredCountries: [],
                        // hiddenInput: "identity",
                        responsiveDropdown: true,
                        placeholderNumberType: "MOBILE",
                        utilsScript: `${this.props.ajax.baseURL}/expandish/view/javascript/iti/js/utils.js`,
                    },
                    (function () {
                        return {
                            geoIpLookup: function (callback) {
                                $.get(
                                    "https://ipinfo.io",
                                    function () {},
                                    "jsonp"
                                ).always(function (resp) {
                                    var countryCode =
                                        resp && resp.country
                                            ? resp.country
                                            : "us";
                                    callback(countryCode);
                                });
                            },
                        };
                    })()
                )
            );

            // allow number only
            input.addEventListener("keypress", function (e) {
                e.stopPropagation ? e.stopPropagation() : (e.cancelBubble = !0);
                "number" != typeof e.which && (e.which = e.keyCode);

                if (13 === e.which) {
                    e.preventDefault();
                    $("#login-js__form-login").submit();
                    return false;
                }

                if (
                    [43, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57].indexOf(
                        e.which
                    ) === -1
                ) {
                    e.preventDefault();
                    return false;
                }
            });
        }
    }

    renderHeader() {
        if (this.isSeller)
            return `<h5 class="modal-title">${this.trans(
                "seller_login_modal_title"
            )}</h5>`;
        else
            return `<h5 class="modal-title">${welcomeIcon}${this.trans(
                "login_modal_title"
            )}</h5>`;
    }
    // eslint-disable-next-line no-unused-vars
    static async initialize(app) {}
}

export default LoginModal;
