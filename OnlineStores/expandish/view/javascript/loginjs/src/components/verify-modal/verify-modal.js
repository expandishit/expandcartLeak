/*eslint no-undef: 0*/
import { countdown, catchPaste } from "../../helper/helpers";
import { back, loader } from "../../svg";
import Modal from "../modal/modal";
import "./verify-modal.scss";

class VerifyModal extends Modal {
    callbackSuccess = null;
    resendCode = null;
    goBack = null;
    identity = null;
    isSeller = false;

    resendCodeMinutes = 1;
    resendCodeSeconds = 0;
    lastVerifiedCode = null;

    // eslint-disable-next-line no-unused-vars
    static async initialize(app) {}

    show(
        callbackSuccess = function () {},
        resendCode = function () {},
        goBack = function () {},
        identity = null,
        isSeller = false
    ) {
        if (
            !this.props.customer.id ||
            !this.props.customer.verification_provider
        )
            return false;

        this.callbackSuccess = callbackSuccess;

        this.resendCode = () => {
            this.lastVerifiedCode = null;
            return typeof resendCode === "function" ? resendCode() : void 0;
        };

        this.goBack = goBack;
        this.identity = identity;
        this.isSeller = isSeller ? 1 : 0;

        this.render();
        $(".loginjs__modal-container").show();
        $(`.${this.modalClassName}`).modal("show");
    }

    hide() {
        $(`.${this.modalClassName}`).modal("hide");
    }

    handleOnHidden() {
        $(".loginjs__modal-container").hide();
        $(".loginjs__modal-container").html("");
        this.callbackSuccess =
            this.resendCode =
            this.goBack =
            this.identity =
            this.lastVerifiedCode =
                null;
    }

    constraints() {
        return {
            code: {
                presence: {
                    allowEmpty: false,
                    message: "^" + this.trans("required_input_code"),
                },
            },
        };
    }

    async handleSubmit(event) {
        event.preventDefault();
        event.stopPropagation();

        if (!this.validator.validate(event.target, this.constraints())) {
            return;
        }

        var button = $(".login-js__btn-submit");
        button.length && button.attr("disabled", 1);
        $(".login-js__btn-submit span").hide();
        button.length && button.addClass("check-code");

        var input = event.target.querySelector("input[name=code]");
        var data = new FormData();

        data.append("id", this.props.customer.id);
        data.append(
            "verification_provider",
            this.props.customer.verification_provider
        );

        data.append("identity", this.identity);
        data.append("is_seller", this.isSeller);
        data.append("code", input.value);

        var result = await this.api.verifyCode(data);

        if (result.success == true) {
            this.hide();
            this.callbackSuccess(result);
            return;
        }

        button.length && button.removeAttr("disabled");
        $(".login-js__btn-submit span").show();
        button.length && button.removeClass("check-code");

        // show errors;
        input &&
            this.validator.showErrorsForInput(input, [
                this.trans("invalid_input_code"),
            ]);
        // for (var i in result.errors || {}) {
        //     input &&
        //         this.validator.showErrorsForInput(input, [result.errors[i]]);
        // }

        input.focus();
    }

    render() {
        var loginWithPhone = this.props.loginWithPhone,
            verifyCode = loginWithPhone
                ? "verifyCode_telephone"
                : "verifyCode_email",
            verifyTitle = loginWithPhone
                ? "verifyTitle_telephone"
                : "verifyTitle_email";

        var identity = this.identity;

        var modal = $(`
        <div class="modal fade ${this.modalClassName}" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="false" data-keyboard="false">
        <div class="modal-dialog modal-md modal-dialog-centered verfiy" role="document">
            <div class="modal-content">
            <div class="modal-header">
                
                <h5 class="modal-title">${back} ${this.trans(verifyTitle)} </h5>
            </div>
            <div class="modal-body">
                <form id="login-js__form-verify" novalidate autocomplete="off">
                    <div class="form-group">
                        <p class="login-js__code">${this.trans(verifyCode)}</p>

                        <input type="hidden" class="form-control" id="login-js__code" placeholder="${this.trans(
                            "input_code"
                        )}" autocomplete="off" name="code"/>

                        <div class="form-wrap">
                            <input focus data-length="0" autocomplete="off" autocorrect="off" autocapitalize="none" spellcheck="false" type="text" maxlength="1"> 
                            <input data-length="1" autocomplete="off" autocorrect="off" autocapitalize="none" spellcheck="false" type="text" maxlength="1">
                            <input data-length="2" autocomplete="off" autocorrect="off" autocapitalize="none" spellcheck="false" type="text" maxlength="1">
                            <input data-length="3" autocomplete="off" autocorrect="off" autocapitalize="none" spellcheck="false" type="text" maxlength="1">
                        </div>
                        <div class="messages"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
            ${
                identity
                    ? `
            <div class="enter__code">
                <p> 
               <span> ${this.trans(
                   "enter_code_send_to"
               )}</span> <span class="text-lower">${identity}</span></p>
            </div>    
            `
                    : ""
            }
            
            <div class="login--popup__timer">
                    <a class="login--popup__resend alert-link" href="#">${this.trans(
                        "btn_resend_code"
                    )}<span id="countdown">04:59</span>
                    </a>
                    
                </div>


            <button type="submit" form="login-js__form-verify" class="btn btn-inline  login-js__btn-submit bg-color">
               <span> ${this.trans("btn_verify")} </span> ${loader}
            </button>
            </div>
            </div>
        </div>
        </div>
        `);

        $(".loginjs__modal-container").append(modal);

        $(`.${this.modalClassName}`).on(
            "hidden.bs.modal",
            this.handleOnHidden.bind(this)
        );
        // $(`.${this.modalClassName}`).on("shown.bs.modal", function () {
        //         console.log('shown 2');
        //         //Do something if necessary
        //         $(".form-wrap input:first-child").focus();
        // });
        setTimeout(function() { $(".form-wrap input:first-child").focus() }, 500);


        //auto switch to next input

        $(".form-wrap input").keyup(function (e) {
            if (e.keyCode == 8) {
                $(this).prev().focus();
            }
        });

        var _this = this;
        
        $(".form-wrap input").keyup(function () {
            // if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
            //     return false;
            // }
            $(".form-wrap input").each(function () {
                var inputValue = $(this).val();
                if (inputValue == "") {
                    $(this).addClass("empty").removeClass("not-empty");
                } else {
                    $(this).addClass("not-empty").removeClass("empty");
                }
            });

            var lengthOfInputhasValue = $(".not-empty").length;

            if (lengthOfInputhasValue == 4) {
                var verfyNumbers = "";
                $(".not-empty").each(function () {
                    verfyNumbers += $(this).val();
                });
                $("#login-js__code").val(verfyNumbers);
                if (_this.lastVerifiedCode != verfyNumbers) {
                    $("#login-js__form-verify").submit();
                }
                _this.lastVerifiedCode = verfyNumbers;
            }
        });

        // $(".form-wrap input").keypress(function (e) {
        //     //if the letter is not digit then don't type anything
        //     if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
        //         return false;
        //     }
        // });

        

        // to be used as put every char in one input

        $(window).on("paste", function (e) {
            e.preventDefault();
            
            // var generalCopiedNumberArray = [];
            catchPaste(e, this, function (clipData) {
                // var copiedNumberArray = clipData.split("");
                // generalCopiedNumberArray = copiedNumberArray;
                if (isNaN(Number(clipData))) {
                    return false;
                }
                
                $("#login-js__form-verify .form-wrap input").each(function () {
                    var dataLength = Number($(this).attr("data-length"));
                    if (isNaN(dataLength) || clipData[dataLength] === "undefined" || isNaN(Number(clipData[dataLength]))) {
                        return false;
                    }
                    $(this).trigger("focus").val(clipData[dataLength]).trigger("change");
                });
            });


            // $(".form-wrap input").each(function () {
            //     var dataLength = $(this).attr("data-length");
            //     if (isNaN(Number(generalCopiedNumberArray[dataLength]))) {
            //         return false;
            //     }
            //     $(this).val(generalCopiedNumberArray[dataLength]);
            // });
        });

        $(".login--popup__back").on("click", (event) => {
            event.preventDefault();
            this.hide();
            this.goBack && this.goBack();
        });

        var container = document.getElementsByClassName("form-wrap")[0];
        container.onkeyup = function (e) {
            // if (
            //     e.which != 8 &&
            //     e.which != 0 &&
            //     (e.which < 48 || e.which > 57)
            // ) {
            //     return false;
            // }

            var target = e.srcElement;
            var maxLength = 1;
            var myLength = target.value.length;
            if (myLength >= maxLength) {
                var next = target;
                while ((next = next.nextElementSibling)) {
                    if (next == null) break;
                    if (next.tagName.toLowerCase() == "input") {
                        next.focus();
                        break;
                    }
                }
            }
        };

        /*
        function toEnglishNumber(strNum) {
            var ar = [
                "٠",
                "١",
                "٢",
                "٣",
                "٤",
                "٥",
                "٦",
                "٧",
                "٨",
                "٩",
                " ",
                "-",
                "/",
                "|",
                "~",
                "٫",
            ];

            var en = [
                "0",
                "1",
                "2",
                "3",
                "4",
                "5",
                "6",
                "7",
                "8",
                "9",
                "",
                "",
                "",
                "",
                "",
                ".",
            ];

            var cache = strNum;

            for (var i = 0; i < 10; i++) {
                var regex_ar = new RegExp(ar[i], "g");
                cache = cache.replace(regex_ar, en[i]);
            }
            return cache;
        }
        */
        
        $(".form-wrap input").on("keypress", function (e) {
            e.stopPropagation ? e.stopPropagation() : (e.cancelBubble = !0);
            "number" != typeof e.which && (e.which = e.keyCode);
            
            if (
                e.which != 8 &&
                e.which != 0 &&
                (e.which < 48 || e.which > 57)
            ) {
                $(".messages p").remove();
                $(".messages").append(
                    `<p class="help-block error">${_this.trans(
                        "invalid_input_code"
                    )}</p>`
                );

                return false;
            }
            // Only ASCII charactar in that range allowed
            // var ASCIICode = (e.which) ? e.which : e.keyCode
            // if (ASCIICode > 31 && (ASCIICode < 48 || ASCIICode > 57)){
            //     return false;
            // }
            // $(this).addClass('plato');
        });
        
        $("#login-js__form-verify").on("submit", this.handleSubmit.bind(this));

        $(".login--popup__resend").on("click", async (event) => {
            $(".form-wrap input").val("");
            $(".form-wrap input:first-child").focus();
            event.preventDefault();
            $(".login--popup__resend").removeClass("active");
            $("#login-js__form-verify .messages .error").remove();
            var result = await this.resendCode();
            if (result.success) {
                this.startCountDown();
            }
            return false;
        });
        
        this.startCountDown();
    }

    startCountDown() {
        countdown(
            ".login--popup__resend",
            this.resendCodeMinutes,
            this.resendCodeSeconds,
            this.trans("btn_resend_code"),
            this.trans("btn_resend_code")
        );
    }
}

export default VerifyModal;
