/* eslint-disable no-unused-vars */
/*eslint no-undef: 0*/

import { closestParentCallable } from "../helper/helpers";

const constraints = function constraints() {
    const {
        customerAccountFields: { registration },
    } = this.props;

    let rules = {};

    for (let k in registration) {
        if ("terms" === k || parseInt(registration[k]) <= 0) continue;
        if ("firstname" === k) k = "name";

        rules[k] = {
            presence: {
                allowEmpty: false,
                message: "^" + this.trans("required_input_" + k),
            },
        };
    }

    rules["email"] = {
        email: {
            message: "^" + this.trans("invalid_input_email"),
        },
    };

    if (this.props.loginWithPhone) {
        rules["telephone"] = {
            presence: {
                allowEmpty: false,
                message: "^" + this.trans("required_input_telephone"),
            },
        };
    } else {
        rules["email"]["presence"] = {
            allowEmpty: false,
            message: "^" + this.trans("required_input_email"),
        };
    }

    return rules;
};

const updateCustomer = async function updateCustomer(form, data, result) {
    if (result.success) {
        var response = await this.api.updateCustomer(data);
        if (response.success) {
            document
                .querySelector(".page-loader-container")
                .classList.remove("active");
            $(".continue-btn").show();
            handleOnUpdatedCustomer.call(this, response.data);
            document
                .querySelector(".btn--account-submit")
                .classList.remove("loading");
            // reset btn
            this.hooks.resetProfileForm.setInitialForm(form);
        } else {
            displayErrors.call(this, form, response.errors || {});
        }
    }
};

const handleOnUpdatedCustomer = function handleOnUpdatedCustomer({
    name,
    firstname,
}) {
    let slug = "",
        index,
        username = name ?? firstname;

    document.querySelector(".user__name").textContent = username;
    document
        .querySelector(".user__avatar")
        .setAttribute("data-avatar", username);

    typeof renderCustomerAvatar === "function" &&
        renderCustomerAvatar(null, username);
};

const showLoader = function showLoader() {
    document.querySelector(".btn--account-submit").classList.add("loading");

    let loader = document.querySelector(".page-loader-container"),
        continueBtn = document.querySelector(".continue-btn");

    loader && loader.classList.add("active");
    continueBtn && (continueBtn.style.display = "none");
};

const removeLoader = function removeLoader() {
    document.querySelector(".btn--account-submit").classList.remove("loading");

    let loader = document.querySelector(".page-loader-container"),
        continueBtn = document.querySelector(".continue-btn");

    loader && loader.classList.remove("active");
    continueBtn && (continueBtn.style.display = "inline");
};

const displayErrors = function (form, errors) {
    for (var k in errors || {}) {
        console.log(k, errors[k]);
        var input = form.querySelector("[name=" + k + "]");
        console.log(input);

        input && this.validator.showErrorsForInput(input, errors[k]);
    }

    removeLoader();
};

export default async function updateCustomerProfileHook(event) {
    event.preventDefault();
    event.stopPropagation();

    let form = closestParentCallable(
        event.target,
        (child) => child.nodeName === "FORM"
    );

    if (!form) return false;

    if (
        !this.validator.validate(form, constraints.call(this), [
            "date_[day]",
            "date_[month]",
            "date_[year]",
        ])
    )
        return false;

    var telInput = form.querySelector("input[type=tel]");

    if (
        telInput.iti &&
        telInput.value.length &&
        !telInput.iti.isValidNumber()
    ) {
        this.validator.showErrorsForInput(telInput, [
            this.trans("invalid_input_telephone"),
        ]);
        return false;
    }

    // show loader page
    showLoader();

    var data = (() => {
        var nForm = new FormData(form);
        nForm.delete("date_[day]");
        nForm.delete("date_[month]");
        nForm.delete("date_[year]");
        
        if (telInput.iti) {
            nForm.delete("telephone");
            nForm.append("telephone", telInput.iti.getNumber());
        }

        return nForm;
    })();

    var response = await this.api.validateCustomerProfile(data);

    var identity = (() => {
        return this.props.loginWithPhone
            ? data.get("telephone")
            : data.get("email");
    })();

    if (response.success === true) {
        if (response.has_verification) {
            // this.props.customer.id = response.id;
            // this.props.customer.id = response.id;
            this.view.verifyModal.show(
                updateCustomer.bind(this, form, data),
                async () => {
                    var response = await this.api.validateCustomerProfile(data);
                    return response;
                },
                function onHideVerificationModal() {
                    var formResetBtn = document.querySelector(
                        ".btn--account-reset"
                    );
                    formResetBtn && formResetBtn.click();
                    removeLoader();
                },
                identity
            );
        } else {
            updateCustomer.call(this, form, data, { success: true });
        }
    } else {
        // show errors validation
        // for (var i in response.errors || {}) {
        //     var input = form.querySelector("[name=" + i.trim() + "]");
        //     input &&
        //         this.validator.showErrorsForInput(input, response.errors[i]);
        // }

        // removeLoader();
        displayErrors.call(this, form, response.errors || {});
    }

    return false;
}
