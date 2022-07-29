export default class ResetForm {
    initialForm = null;
    initialInputs = {};

    getInitialForm() {
        return this.initialForm;
    }

    setInitialForm(form) {
        this.initialForm = form;
        this._setInitialInputs();
    }

    _setInitialInputs() {
        const form = this.getInitialForm();
        if (!form) return;

        setTimeout(() => {
            [...form].forEach((input) => {
                if ("BUTTON" === input.nodeName) return;

                this.initialInputs[input.name] = {};

                switch (input.nodeName) {
                    case "INPUT":
                        if (input.type == "text" || input.type == "hidden") {
                            this.initialInputs[input.name].value = input.value;
                        }

                        if ("tel" === input.type && "iti" in input) {
                            this.initialInputs[
                                input.name
                            ].value = input.iti.getNumber();
                        }

                        if ("checkbox" === input.type) {
                            this.initialInputs[input.name].checked =
                                input.checked;
                        }

                        break;
                    case "SELECT":
                        this.initialInputs[input.name].value = input.value;
                        this.initialInputs[input.name].selectedIndex =
                            input.selectedIndex;
                        break;
                }
            });
        }, 1000);
    }

    reset(event) {
        event.preventDefault();
        event.stopPropagation();

        var form = this.getInitialForm();

        if (!form) return;

        [...form].forEach((input) => {
            if (
                "BUTTON" === input.nodeName ||
                !(input.name in this.initialInputs)
            )
                return;

            switch (input.nodeName) {
                case "INPUT":
                    if (input.type == "text" || input.type == "hidden") {
                        input.value = this.initialInputs[input.name].value;
                    }

                    if ("tel" === input.type && "iti" in input) {
                        input.iti.setNumber(
                            this.initialInputs[input.name].value
                        );
                    }

                    if ("checkbox" === input.type) {
                        input.checked = this.initialInputs[input.name].checked;
                    }

                    break;
                case "SELECT":
                    input.selectedIndex = this.initialInputs[
                        input.name
                    ].selectedIndex;
                    break;
            }
        });
    }
}
