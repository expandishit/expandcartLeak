import { mergeDeep } from "../helper/helpers";

var validate = require("validate.js");

class Validator {
    constructor(app) {
        mergeDeep(app, this);
    }

    validator = {
        validate(form, constraints, ignoreInputs = []) {
            // validate the form against the constraints
            var errors = validate(form, constraints);
            // then we update the form to reflect the results
            this.showErrors(form, errors || {}, ignoreInputs);

            if (!errors) {
                this.showSuccess();
            }

            return !errors;
        },
        // Updates the inputs with the validation errors
        showErrors(form, errors, ignoreInputs = []) {
            // We loop through all the inputs and show the errors for that input
            form.querySelectorAll("input[name], select[name]").forEach(
                (input) => {
                    if (ignoreInputs.indexOf(input.name) === -1) {
                        // Since the errors can be null if no errors were found we need to handle
                        // that
                        this.showErrorsForInput(
                            input,
                            errors && errors[input.name]
                        );
                    }
                }
            );
        },

        showErrorsForInput(input, errors) {
            // This is the root of the input
            var formGroup = this.closestParent(input.parentNode, "form-group");
            if (!formGroup) return;
            // Find where the error messages will be insert into
            var messages = formGroup.querySelector(".messages");
            // First we remove any old messages and resets the classes
            this.resetFormGroup(formGroup);
            // If we have errors
            if (errors && errors.length) {
                // we first mark the group has having errors
                formGroup.classList.add("has-error");
                // then we append the first error!
                var error = errors[0];
                this.addError(messages, error);
            } else {
                // otherwise we simply mark it as success
                formGroup.classList.add("has-success");
            }
        },
        // Recursively finds the closest parent that has the specified class
        closestParent(child, className) {
            if (!child || child == document) {
                return null;
            }

            if (child.classList.contains(className)) {
                return child;
            }

            return this.closestParent(child.parentNode, className);
        },
        resetFormGroup(formGroup) {
            // Remove the success and error classes
            formGroup.classList.remove("has-error");
            formGroup.classList.remove("has-success");
            // and remove any old messages
            formGroup.querySelectorAll(".help-block.error").forEach((el) => {
                el.parentNode.removeChild(el);
            });
        },
        // Adds the specified error with the following markup
        // <p class="help-block error">[message]</p>
        addError(messages, error) {
            var block = document.createElement("p");
            block.classList.add("help-block");
            block.classList.add("error");
            block.innerText = error;
            messages.appendChild(block);
        },
        showSuccess() {},
    };
}

export default Validator;
