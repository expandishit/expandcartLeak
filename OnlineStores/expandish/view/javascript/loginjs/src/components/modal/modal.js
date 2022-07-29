/* eslint-disable no-undef */
import { toArray } from "../../helper/helpers";

export default class Modal {
    props = null;
    view = null;
    trans = null;
    api = null;
    validator = null;

    modalClassName = "login--popup";

    constructor(app) {
        this.props = app.props;
        this.view = app.view;
        this.trans = app.trans;
        this.api = app.api;
        this.validator = app.validator;
    }

    displayWarningErrors(errors = []) {
        toArray(errors).map(this.renderWarningError.bind(this));
    }

    getWarningArea() {
        $('.login-js__warning-area').parent().addClass('pt-0');
        $('.login-js__warning-area').closest('.modal-content').find('.modal-header').addClass('pm-0');

        return document.querySelector(" .login-js__warning-area");
    }

    renderWarningError(error) {
        var warningArea = this.getWarningArea();

        if (!warningArea) {
            console.warn(error);
            return;
        }

        warningArea.insertAdjacentHTML(
            "afterbegin",
            this.warningTemplate(error)
        );
    }

    warningTemplate(error) {

        return `<div class="login-js__alert-warning">
        <div class="alert-warning__content">
            <?xml version="1.0" encoding="iso-8859-1"?>
                <!-- Generator: Adobe Illustrator 19.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0)  -->
                <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                    viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
                <g>
                    <g>
                        <path d="M256,0C114.497,0,0,114.507,0,256c0,141.503,114.507,256,256,256c141.503,0,256-114.507,256-256
                            C512,114.497,397.493,0,256,0z M256,472c-119.393,0-216-96.615-216-216c0-119.393,96.615-216,216-216
                            c119.393,0,216,96.615,216,216C472,375.393,375.385,472,256,472z"/>
                    </g>
                </g>
                <g>
                    <g>
                        <path d="M256,128.877c-11.046,0-20,8.954-20,20V277.67c0,11.046,8.954,20,20,20s20-8.954,20-20V148.877
                            C276,137.831,267.046,128.877,256,128.877z"/>
                    </g>
                </g>
                <g> 
                    <g>
                        <circle cx="256" cy="349.16" r="27"/>
                    </g>
                </g>
                </svg>

            <p> ${error} </p>
           </div>
            </div>`;  
    }
    clearWarningErrors() {
        var warningArea = this.getWarningArea();

        if (warningArea) {
            warningArea.innerHTML = "";
        } 
    } 
} 
