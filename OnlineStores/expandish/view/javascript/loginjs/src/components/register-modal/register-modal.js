/*eslint no-undef: 0*/
import intlTelInput from "intl-tel-input";
import { getElementInArray, isOdd, redirect } from "../../helper/helpers";
import Modal from "../modal/modal";

import "./register-modal.scss";
import "../../plugins/datepicker.js";
class RegisterModal extends Modal {
    iti = null;
    fields = [];
    sellerFields = [];

    // eslint-disable-next-line no-unused-vars
    static async initialize(app) {}

    show({ fields, seller_fields }) {
        if (!this.props.customer.id) return false;

        this.fields = fields;
        this.sellerFields = seller_fields || [];

        setTimeout(() => {
            this.render();
            $(".loginjs__modal-container").show();
            $(`.${this.modalClassName}`).modal("show");
        }, 350);
    }

    hide() {
        $(`.${this.modalClassName}`).modal("hide");
    }

    handleOnHidden() {
        $(".loginjs__modal-container").html("");
        $(".loginjs__modal-container").hide();
    }

    constraints() {
        let i,
            rules = {},
            field,
            fields = this.fields;

        // if (this.props.enableMultiseller && this.customerChosenSellerType()) {
        //     fields = [...fields, ...this.sellerFields];
        // }

        for (i = 0; i < (fields || []).length; i++) {
            field = fields[i];
            if (field.required) {
                var fieldName = field.name;
                if ("id" in field && field.id.length && "terms" != fieldName) {
                    fieldName = field.name + "_" + field.id;
                }

                rules[field.name] = {
                    presence: {
                        allowEmpty: false,
                        message:
                            "^" + this.trans("required_input_" + fieldName),
                    },
                };
            }
        }

        return rules;
    }

    async handleSubmit(event) {
        var telInput =
            event.target.querySelector("input[type=tel]") &&
            window.intlTelInputGlobals
                ? window.intlTelInputGlobals.getInstance(
                      event.target.querySelector("input[type=tel]")
                  )
                : null;

        if (
            !this.validator.validate(event.target, this.constraints(), [
                "date_[day]",
                "date_[month]",
                "date_[year]",
            ])
        )
            return;

        if (telInput && telInput.telInput.value.length) {
            if (!telInput.isValidNumber()) {
                this.validator.showErrorsForInput(telInput, [
                    this.trans("invalid_input_telephone"),
                ]);
                return;
            }
        }

        var button = $(".login-js__btn-submit");
        button.length && button.attr("disabled", 1);
        $(".login-js__btn-submit span").hide();
        button.length && button.addClass("check-code");

        var data = new FormData(event.target);

        data.delete("date_[day]");
        data.delete("date_[month]");
        data.delete("date_[year]");

        var response = await this.api.registerCustomer(data);

        if (response.success === true) {
            this.hide();
            if (response.fields && response.fields.length)
                this.view.registerModal.show(response);
            else redirect(response.redirect);

            return;
        }

        button.length && button.removeAttr("disabled");
        $(".login-js__btn-submit span").show();
        button.length && button.removeClass("check-code");

        for (var i in response.errors || {}) {
            var input = event.target.querySelector("[name=" + i.trim() + "]");
            input &&
                this.validator.showErrorsForInput(input, [response.errors[i]]);
        }
    }

    getCountry(countries, value, column = "country_id") {
        return getElementInArray(
            countries,
            (country) => country[column] == value
        );
    }

    renderField(field) {
        field.placeholder = field.placeholder.replace(
            field.name != "terms" ? ":" : "",
            ""
        );
        var value = field.value == null ? "" : field.value;

        switch (field.name) {
            case "email":
            case "name":
            case "firstname":
            case "lastname":
            case "address_1":
            case "address_2":
            case "company":
            case "city":
            case "nickname": // seller field
                return `<div class="form-group col-md-12"><label for="${
                    field.name
                }">${field.placeholder} ${
                    field.required ? '<span class="required">*</span>' : ""
                }</label><input type="text" name="${
                    field.name
                }" class="form-control" id="${
                    field.name
                }" value="${value}" placeholder="${this.trans("enter_your")} ${
                    field.placeholder
                }"/><div class="messages"></div></div>`;

            case "telephone":
                return `<div class="form-group col-md-12"><label for="${
                    field.name
                }">${field.placeholder} ${
                    field.required ? '<span class="required">*</span>' : ""
                }</label><input type="tel" class="form-control" id="${
                    field.name
                }" value="${value}"/><div class="messages"></div></div>`;

            case "dob":
                return `<div class="form-group col-md-12"><label for="dob">${
                    field.placeholder
                } ${
                    field.required ? '<span class="required">*</span>' : ""
                }</label>
           
                <input type="hidden" class="three-dob-datepicker" id="dob" name="dob" value="${value}"/>

                <div class="messages"></div></div>`;
            case "gender":
                return `<div class="form-group col-md-12"><label for="gender">${
                    field.placeholder
                } ${
                    field.required ? '<span class="required">*</span>' : ""
                }</label>
                <div class="custom-select">
                    <img class="custom-select__icon" src="expandish/view/theme/default/image/down-arrow.svg">
                    <select name="gender" class="form-control">
                        <option value="" selected="selected" disabled>${this.trans(
                            "text_none"
                        )}</option>
                        <option value="m" ${
                            value == "m" ? 'selected="selected"' : ""
                        }>${this.trans("gender_m")}</option>
                        <option value="f" ${
                            value == "f" ? 'selected="selected"' : ""
                        }>${this.trans("gender_f")}</option>
                    </select>
                </div>
                <div class="messages"></div>
            </div>`;
            case "customer_group_id":
                return `<div class="form-group col-md-12"><label for="customer_group_id">${
                    field.placeholder
                } ${
                    field.required ? '<span class="required">*</span>' : ""
                }</label>
                <div class="custom-select">
                    <img class="custom-select__icon" src="expandish/view/theme/default/image/down-arrow.svg">
                    <select name="customer_group_id" class="form-control">
                        <option value="" selected="selected" disabled>${this.trans(
                            "text_none"
                        )}</option>
                    ${(field.values || [])
                        .map((v) => {
                            return `<option value="${v.customer_group_id}" ${
                                v.customer_group_id == value
                                    ? 'selected="selected"'
                                    : ""
                            }> ${v.name} </option>`;
                        })
                        .join("")}
                    </select> 
                </div>
                <div class="messages"></div>
            </div>`;
            case "newsletter":
                return `<div class="col-md-12 form-group"><div class="checkbox my-account__form-info" style="margin: 0;"><label class="checkbox-inline">
                    <input type="checkbox" value="1" name="newsletter" ${
                        value ? "checked" : ""
                    }><span>${
                    field.placeholder
                }</span></label></div><div class="messages"></div></div>`;
            case "terms":
                return `<div class="col-md-12 form-group"><div class="checkbox my-account__form-info" style="margin: 0;"><label style="font-size: 98%;" class="checkbox-inline"><input type="checkbox" value="1" name="terms"><span class="terms-link-container">${
                    field.placeholder
                } ${
                    field.required
                        ? '<span class="required red--astr">*</span>'
                        : ""
                } </span>
                
                </label></div><div class="messages"></div></div>`;
        }

        return "";
    }

    render() {
        const { fields, trans, modalClassName } = this;
        const { customer } = this.props;

        var modal = $(` 
            <div class="modal fade ${modalClassName}" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="false" data-keyboard="false">
            <div class="modal-dialog login--popup__register-modal modal-dialog-centered" role="document">
                <div class="modal-content">
                <div class="modal-header">
      
                    <h5 class="modal-title">
                    ${this.trans("complete_your_profile")}</h5>
               
                </div>
                <div class="modal-body">
                    <form id="login-js__form" novalidate>
                        <div class="row">
                            ${fields
                                .map((field, i) => {
                                    field = this.renderField(field);
                                    if (isOdd(i))
                                        field += `<div class="clearfix"></div>`;
                                    return field;
                                })
                                .join("")}
                            <input value="${
                                customer.id
                            }" type="hidden" name="id" />
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                <button type="submit" form="login-js__form" class="btn login-js__btn-submit btn-inline bg-color">
                    <span>${trans("btn_continue")}</span>
                    <svg class="confirm__loader" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="19.805" height="19.805" viewBox="0 0 19.805 19.805">
                        <defs>
                            <linearGradient id="linear-gradient" x1="0.5" y1="0.201" x2="0.5" y2="1" gradientUnits="objectBoundingBox">
                            <stop offset="0" stop-color="#fff"/>
                            <stop offset="1" stop-color="#fff" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                        <path id="loader" d="M19.8,9.9A9.9,9.9,0,1,1,9.9,0,9.9,9.9,0,0,1,19.8,9.9Zm-1.547,0A8.355,8.355,0,1,0,9.9,18.258,8.35,8.35,0,0,0,18.257,9.9Zm0,0" transform="translate(0 0)" fill="url(#linear-gradient)"/>
                    </svg>
                </button>
           
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
            $("#login-js__fname").focus();
        });

        var _this = this;

        $("#login-js__form").on("submit", function (event) {
            event.preventDefault();
            event.stopPropagation();
            setTimeout(() => {
                _this.handleSubmit(event);
            }, 100);
        });

        $(document).ready(function () {
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
            $(".checkbox a b").addClass("custom-color");
        });

        // telephone js
        var modalSelector = document.querySelector("." + this.modalClassName);

        var input = modalSelector.querySelector("input[type=tel]");

        input &&
            intlTelInput(input, {
                initialCountry: "auto",
                nationalMode: true,
                separateDialCode: !true,
                formatOnDisplay: true,
                autoPlaceholder: "aggressive",
                responsiveDropdown: true,
                preferredCountries: [],
                hiddenInput: "telephone",
                placeholderNumberType: "MOBILE",
                utilsScript: `${this.props.ajax.baseURL}/expandish/view/javascript/iti/js/utils.js`,
                geoIpLookup: function (callback) {
                    $.get("https://ipinfo.io", function () {}, "jsonp").always(
                        function (resp) {
                            callback(
                                (resp && resp.country
                                    ? resp.country
                                    : "us"
                                ).toLowerCase()
                            );
                        }
                    );
                },
            });

        // seller fields
        var accountTypes = document.querySelectorAll(
            "input[name=account_type]"
        );
        accountTypes.forEach((input) => {
            input.addEventListener(
                "change",
                this.handleOnChangeAccountType.bind(this),
                true
            );
        });
    } // end render

    handleOnChangeAccountType(event) {
        const { value } = event.target;
        var sellerFieldsContainer = $(".seller-fields");

        if (value == "buyer") {
            sellerFieldsContainer.html("");
            return false;
        }

        sellerFieldsContainer.html(this.renderSellerFields());

        return false;
    }

    renderSellerFields() {
        return this.sellerFields
            .map((field, i) => {
                field = this.renderField(field);
                if (isOdd(i)) field += `<div class="clearfix"></div>`;
                return field;
            })
            .join("");
    }

    customerChosenSellerType() {
        let value = $(
            "input[name=account_type]:checked",
            "#login-js__form"
        ).val();

        return "seller" === value;
    }
}

export default RegisterModal;
