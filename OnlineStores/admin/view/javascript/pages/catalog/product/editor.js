(function () {
    const Component = (function () {
        var minimizeSideBar = function () {
            $(".sidebar-main-toggle").trigger("click");
        };

        var initializeEditor = function () {
            // Simple warn notifier
            var origWarn = console.warn;
            toastr.options = {
                closeButton: true,
                preventDuplicates: true,
                showDuration: 250,
                hideDuration: 150,
            };
            console.warn = function (msg) {
                if (msg.indexOf("[undefined]") == -1) {
                    toastr.warning(msg);
                }
                origWarn(msg);
            };

            var appendToContent = function (target, content) {
                if (content instanceof HTMLElement) {
                    target.appendChild(content);
                } else if (content) {
                    target.insertAdjacentHTML("beforeend", content);
                }
            };

            /** Custom Editor Plugins */
            var productCustomPlugin = function productShortCuts(editor) {
                var bm = editor.BlockManager;
                bm.add("product-price", {
                    label: "Price",
                    category: "Product Attributes",
                    attributes: {
                        class: "fa fa-dollar-sign",
                        contenteditable: false,
                    },
                    content:
                        "<div class='product__option' data-gjs-editable='false'>{{price}}</div>",
                });

                bm.add("product-name", {
                    label: "Name",
                    category: "Product Attributes",
                    attributes: {
                        class: "fa fa-heading",
                        contenteditable: false,
                    },
                    content:
                        "<div class='product__option' data-gjs-editable='false'>{{product_name}}</div>",
                });

                bm.add("product-image", {
                    label: "Image",
                    category: "Product Attributes",
                    attributes: {
                        class: "fa fa-image",
                        contenteditable: false,
                    },
                    content: `<div class='product__option' data-gjs-editable='false'>
                            <img src="{{image}}" alt="{{product_name}}"/>
                        </div>`,
                });

                bm.add("product-model", {
                    label: "Model",
                    category: "Product Attributes",
                    attributes: {
                        class: "fa fa-icons fa-trademark",
                        contenteditable: false,
                    },
                    content:
                        "<div class='product__option' data-gjs-editable='false'>{{model}}</div>",
                });

                bm.add("product-description", {
                    label: "Description",
                    category: "Product Attributes",
                    attributes: {
                        class: "fa fa-list-alt",
                        contenteditable: false,
                    },
                    content:
                        "<div class='product__option' data-gjs-editable='false'>{{description}}</div>",
                });

                bm.add("redirect-link", {
                    label: "Redirect Link",
                    category: "Extra",
                    attributes: {
                        class: "fa fa-link",
                    },
                    content: `
                            <a class="redirect-btn" 
                                href="${Page.editorRedirectLink}">Redirect to Product Options</a>
                        <style>
                            .redirect-btn:link{
                                color: #FFF;
                                text-decoration: none;
                                display: inline-block;
                                margin-top: 15px;
                                padding: 10px;
                                min-height: 30px;
                                text-align: center;
                                letter-spacing: 3px;
                                line-height: 30px;
                                background-color: #d983a6;
                                border-radius: 5px;
                                transition: all 0.5s ease;
                                cursor: pointer;
                            }
                            .redirect-btn:hover {
                                background-color: #ffffff !important;
                                color: #4c114e !important;
                            }
                        </style>
                    `,
                });
            };

            var editor = grapesjs.init({
                avoidInlineStyle: 1,
                height: "100%",
                container: "#gjs",
                fromElement: 1,
                noticeOnUnload: true,
                showOffsets: false,
                showOffsetsSelected: true,
                forceClass: true,
                log: true,
                allowScripts: 1,
                canvas: {
                    scripts: [],
                    styles: [],
                },
                mediaCondition: "min-width",
                keepEmptyTextNodes: 0,
                jsInHtml: true,
                nativeDnD: 1,
                multipleSelection: 1,
                exportWrapper: 1,
                wrappesIsBody: 1,
                avoidDefaults: 0,
                clearStyles: 0,

                assetManager: {
                    embedAsBase64: 1,
                    assets: [],
                },
                // Default configurations
                storageManager: {
                    type: "local", // Type of the storage
                    autosave: false, // Store data automatically
                    autoload: false, // Autoload stored data on init
                },

                selectorManager: { componentFirst: true },
                styleManager: { clearProperties: 1 },
                plugins: [
                    "grapesjs-lory-slider",
                    "grapesjs-tabs",
                    "grapesjs-custom-code",
                    "grapesjs-touch",
                    "grapesjs-parser-postcss",
                    "grapesjs-tooltip",
                    "grapesjs-tui-image-editor",
                    "grapesjs-typed",
                    "grapesjs-style-bg",
                    "gjs-preset-webpage",
                    productCustomPlugin,
                ],
                pluginsOpts: {
                    "grapesjs-custom-code": {},
                    "grapesjs-lory-slider": {
                        sliderBlock: {
                            category: "Extra",
                        },
                    },
                    "grapesjs-tabs": {
                        tabsBlock: {
                            category: "Extra",
                        },
                    },
                    "grapesjs-typed": {
                        block: {
                            category: "Extra",
                            content: {
                                type: "typed",
                                "type-speed": 40,
                                strings: [
                                    "Text row one",
                                    "Text row two",
                                    "Text row three",
                                ],
                            },
                        },
                    },
                    "gjs-preset-webpage": {
                        modalImportTitle: "Import Template",
                        modalImportLabel:
                            '<div style="margin-bottom: 10px; font-size: 13px;">Paste here your HTML/CSS and click Import</div>',
                        modalImportContent: function (editor) {
                            return (
                                editor.getHtml() +
                                "<style>" +
                                editor.getCss() +
                                "</style>"
                            );
                        },
                        filestackOpts: null,
                        aviaryOpts: false,
                        blocksBasicOpts: { flexGrid: 1 },
                        customStyleManager: [
                            {
                                name: "General",
                                buildProps: [
                                    "float",
                                    "display",
                                    "position",
                                    "top",
                                    "right",
                                    "left",
                                    "bottom",
                                ],
                                properties: [
                                    {
                                        name: "Alignment",
                                        property: "float",
                                        type: "radio",
                                        defaults: "none",
                                        list: [
                                            {
                                                value: "none",
                                                className: "fa fa-times",
                                            },
                                            {
                                                value: "left",
                                                className: "fa fa-align-left",
                                            },
                                            {
                                                value: "right",
                                                className: "fa fa-align-right",
                                            },
                                        ],
                                    },
                                    { property: "position", type: "select" },
                                ],
                            },
                            {
                                name: "Dimension",
                                open: false,
                                buildProps: [
                                    "width",
                                    "flex-width",
                                    "height",
                                    "max-width",
                                    "min-height",
                                    "margin",
                                    "padding",
                                ],
                                properties: [
                                    {
                                        id: "flex-width",
                                        type: "integer",
                                        name: "Width",
                                        units: ["px", "%"],
                                        property: "flex-basis",
                                        toRequire: 1,
                                    },
                                    {
                                        property: "margin",
                                        properties: [
                                            {
                                                name: "Top",
                                                property: "margin-top",
                                            },
                                            {
                                                name: "Right",
                                                property: "margin-right",
                                            },
                                            {
                                                name: "Bottom",
                                                property: "margin-bottom",
                                            },
                                            {
                                                name: "Left",
                                                property: "margin-left",
                                            },
                                        ],
                                    },
                                    {
                                        property: "padding",
                                        properties: [
                                            {
                                                name: "Top",
                                                property: "padding-top",
                                            },
                                            {
                                                name: "Right",
                                                property: "padding-right",
                                            },
                                            {
                                                name: "Bottom",
                                                property: "padding-bottom",
                                            },
                                            {
                                                name: "Left",
                                                property: "padding-left",
                                            },
                                        ],
                                    },
                                ],
                            },
                            {
                                name: "Typography",
                                open: false,
                                buildProps: [
                                    "font-family",
                                    "font-size",
                                    "font-weight",
                                    "letter-spacing",
                                    "color",
                                    "line-height",
                                    "text-align",
                                    "text-decoration",
                                    "text-shadow",
                                ],
                                properties: [
                                    { name: "Font", property: "font-family" },
                                    { name: "Weight", property: "font-weight" },
                                    { name: "Font color", property: "color" },
                                    {
                                        property: "text-align",
                                        type: "radio",
                                        defaults: "left",
                                        list: [
                                            {
                                                value: "left",
                                                name: "Left",
                                                className: "fa fa-align-left",
                                            },
                                            {
                                                value: "center",
                                                name: "Center",
                                                className: "fa fa-align-center",
                                            },
                                            {
                                                value: "right",
                                                name: "Right",
                                                className: "fa fa-align-right",
                                            },
                                            {
                                                value: "justify",
                                                name: "Justify",
                                                className:
                                                    "fa fa-align-justify",
                                            },
                                        ],
                                    },
                                    {
                                        property: "text-decoration",
                                        type: "radio",
                                        defaults: "none",
                                        list: [
                                            {
                                                value: "none",
                                                name: "None",
                                                className: "fa fa-times",
                                            },
                                            {
                                                value: "underline",
                                                name: "underline",
                                                className: "fa fa-underline",
                                            },
                                            {
                                                value: "line-through",
                                                name: "Line-through",
                                                className:
                                                    "fa fa-strikethrough",
                                            },
                                        ],
                                    },
                                    {
                                        property: "text-shadow",
                                        properties: [
                                            {
                                                name: "X position",
                                                property: "text-shadow-h",
                                            },
                                            {
                                                name: "Y position",
                                                property: "text-shadow-v",
                                            },
                                            {
                                                name: "Blur",
                                                property: "text-shadow-blur",
                                            },
                                            {
                                                name: "Color",
                                                property: "text-shadow-color",
                                            },
                                        ],
                                    },
                                ],
                            },
                            {
                                name: "Decorations",
                                open: false,
                                buildProps: [
                                    "opacity",
                                    "border-radius",
                                    "border",
                                    "box-shadow",
                                    "background-bg",
                                ],
                                properties: [
                                    {
                                        type: "slider",
                                        property: "opacity",
                                        defaults: 1,
                                        step: 0.01,
                                        max: 1,
                                        min: 0,
                                    },
                                    {
                                        property: "border-radius",
                                        properties: [
                                            {
                                                name: "Top",
                                                property:
                                                    "border-top-left-radius",
                                            },
                                            {
                                                name: "Right",
                                                property:
                                                    "border-top-right-radius",
                                            },
                                            {
                                                name: "Bottom",
                                                property:
                                                    "border-bottom-left-radius",
                                            },
                                            {
                                                name: "Left",
                                                property:
                                                    "border-bottom-right-radius",
                                            },
                                        ],
                                    },
                                    {
                                        property: "box-shadow",
                                        properties: [
                                            {
                                                name: "X position",
                                                property: "box-shadow-h",
                                            },
                                            {
                                                name: "Y position",
                                                property: "box-shadow-v",
                                            },
                                            {
                                                name: "Blur",
                                                property: "box-shadow-blur",
                                            },
                                            {
                                                name: "Spread",
                                                property: "box-shadow-spread",
                                            },
                                            {
                                                name: "Color",
                                                property: "box-shadow-color",
                                            },
                                            {
                                                name: "Shadow type",
                                                property: "box-shadow-type",
                                            },
                                        ],
                                    },
                                    {
                                        id: "background-bg",
                                        property: "background",
                                        type: "bg",
                                    },
                                ],
                            },
                            {
                                name: "Extra",
                                open: false,
                                buildProps: [
                                    "transition",
                                    "perspective",
                                    "transform",
                                ],
                                properties: [
                                    {
                                        property: "transition",
                                        properties: [
                                            {
                                                name: "Property",
                                                property: "transition-property",
                                            },
                                            {
                                                name: "Duration",
                                                property: "transition-duration",
                                            },
                                            {
                                                name: "Easing",
                                                property:
                                                    "transition-timing-function",
                                            },
                                        ],
                                    },
                                    {
                                        property: "transform",
                                        properties: [
                                            {
                                                name: "Rotate X",
                                                property: "transform-rotate-x",
                                            },
                                            {
                                                name: "Rotate Y",
                                                property: "transform-rotate-y",
                                            },
                                            {
                                                name: "Rotate Z",
                                                property: "transform-rotate-z",
                                            },
                                            {
                                                name: "Scale X",
                                                property: "transform-scale-x",
                                            },
                                            {
                                                name: "Scale Y",
                                                property: "transform-scale-y",
                                            },
                                            {
                                                name: "Scale Z",
                                                property: "transform-scale-z",
                                            },
                                        ],
                                    },
                                ],
                            },
                            {
                                name: "Flex",
                                open: false,
                                properties: [
                                    {
                                        name: "Flex Container",
                                        property: "display",
                                        type: "select",
                                        defaults: "block",
                                        list: [
                                            { value: "block", name: "Disable" },
                                            { value: "flex", name: "Enable" },
                                        ],
                                    },
                                    {
                                        name: "Flex Parent",
                                        property: "label-parent-flex",
                                        type: "integer",
                                    },
                                    {
                                        name: "Direction",
                                        property: "flex-direction",
                                        type: "radio",
                                        defaults: "row",
                                        list: [
                                            {
                                                value: "row",
                                                name: "Row",
                                                className:
                                                    "icons-flex icon-dir-row",
                                                title: "Row",
                                            },
                                            {
                                                value: "row-reverse",
                                                name: "Row reverse",
                                                className:
                                                    "icons-flex icon-dir-row-rev",
                                                title: "Row reverse",
                                            },
                                            {
                                                value: "column",
                                                name: "Column",
                                                title: "Column",
                                                className:
                                                    "icons-flex icon-dir-col",
                                            },
                                            {
                                                value: "column-reverse",
                                                name: "Column reverse",
                                                title: "Column reverse",
                                                className:
                                                    "icons-flex icon-dir-col-rev",
                                            },
                                        ],
                                    },
                                    {
                                        name: "Justify",
                                        property: "justify-content",
                                        type: "radio",
                                        defaults: "flex-start",
                                        list: [
                                            {
                                                value: "flex-start",
                                                className:
                                                    "icons-flex icon-just-start",
                                                title: "Start",
                                            },
                                            {
                                                value: "flex-end",
                                                title: "End",
                                                className:
                                                    "icons-flex icon-just-end",
                                            },
                                            {
                                                value: "space-between",
                                                title: "Space between",
                                                className:
                                                    "icons-flex icon-just-sp-bet",
                                            },
                                            {
                                                value: "space-around",
                                                title: "Space around",
                                                className:
                                                    "icons-flex icon-just-sp-ar",
                                            },
                                            {
                                                value: "center",
                                                title: "Center",
                                                className:
                                                    "icons-flex icon-just-sp-cent",
                                            },
                                        ],
                                    },
                                    {
                                        name: "Align",
                                        property: "align-items",
                                        type: "radio",
                                        defaults: "center",
                                        list: [
                                            {
                                                value: "flex-start",
                                                title: "Start",
                                                className:
                                                    "icons-flex icon-al-start",
                                            },
                                            {
                                                value: "flex-end",
                                                title: "End",
                                                className:
                                                    "icons-flex icon-al-end",
                                            },
                                            {
                                                value: "stretch",
                                                title: "Stretch",
                                                className:
                                                    "icons-flex icon-al-str",
                                            },
                                            {
                                                value: "center",
                                                title: "Center",
                                                className:
                                                    "icons-flex icon-al-center",
                                            },
                                        ],
                                    },
                                    {
                                        name: "Flex Children",
                                        property: "label-parent-flex",
                                        type: "integer",
                                    },
                                    {
                                        name: "Order",
                                        property: "order",
                                        type: "integer",
                                        defaults: 0,
                                        min: 0,
                                    },
                                    {
                                        name: "Flex",
                                        property: "flex",
                                        type: "composite",
                                        properties: [
                                            {
                                                name: "Grow",
                                                property: "flex-grow",
                                                type: "integer",
                                                defaults: 0,
                                                min: 0,
                                            },
                                            {
                                                name: "Shrink",
                                                property: "flex-shrink",
                                                type: "integer",
                                                defaults: 0,
                                                min: 0,
                                            },
                                            {
                                                name: "Basis",
                                                property: "flex-basis",
                                                type: "integer",
                                                units: ["px", "%", ""],
                                                unit: "",
                                                defaults: "auto",
                                            },
                                        ],
                                    },
                                    {
                                        name: "Align",
                                        property: "align-self",
                                        type: "radio",
                                        defaults: "auto",
                                        list: [
                                            {
                                                value: "auto",
                                                name: "Auto",
                                            },
                                            {
                                                value: "flex-start",
                                                title: "Start",
                                                className:
                                                    "icons-flex icon-al-start",
                                            },
                                            {
                                                value: "flex-end",
                                                title: "End",
                                                className:
                                                    "icons-flex icon-al-end",
                                            },
                                            {
                                                value: "stretch",
                                                title: "Stretch",
                                                className:
                                                    "icons-flex icon-al-str",
                                            },
                                            {
                                                value: "center",
                                                title: "Center",
                                                className:
                                                    "icons-flex icon-al-center",
                                            },
                                        ],
                                    },
                                ],
                            },
                        ],
                    },
                },
            });

            editor.I18n.addMessages({
                en: {
                    styleManager: {
                        properties: {
                            "background-repeat": "Repeat",
                            "background-position": "Position",
                            "background-attachment": "Attachment",
                            "background-size": "Size",
                        },
                    },
                },
            });

            var pn = editor.Panels;
            var modal = editor.Modal;
            var cmdm = editor.Commands;

            cmdm.add("save-html", {
                run: function (editor) {
                    Ladda.startAll();
                    var html = editor.getHtml();

                    var custom_html = null;

                    if (html.length) {
                        var style = editor.getCss();
                        custom_html =
                            (style.length
                                ? "<style>" + style + "</style>"
                                : "") + html;
                    }

                    var data = {
                        language_id: Page.currentLanguage,
                        custom_html: custom_html,
                        custom_html_status: document.querySelector(
                            "input[name=custom_html_status]"
                        ).checked
                            ? 1
                            : 0,
                        display_main_page_layout: document.querySelector(
                            "input[name=display_main_page_layout]"
                        ).checked
                            ? 1
                            : 0,
                    };

                    $.ajax({
                        url: Page.saveUrl,
                        type: "POST",
                        data: data,
                        dataType: "json",
                        success: function (returnResult) {
                            Ladda.stopAll();
                            notify("", "success", returnResult.success_msg);
                        },
                        error: function () {
                            Ladda.stopAll();
                        },
                    });
                },
            });

            cmdm.add("toggle-language", {
                run: function (editor, sender) {
                    if (!$("#lang-list").length) {
                        var listHtml = $("#language-list").html();
                        var list = $(
                            '<div id="lang-list" class="hide">' +
                                listHtml +
                                "</div>"
                        );
                        $(".gjs-editor").append(list);
                    }
                    $("#lang-list").toggleClass("hide");
                },
            });

            cmdm.add("import-html-link", {
                keyCustomCode: "import-html-link__command",
                run(editor, sender, opts = {}) {
                    this.sender = sender;
                    this.editor = editor;
                    this.options = opts;
                    this.showCustomHtmlModal(this.target);
                },

                showCustomHtmlModal(target) {
                    const { editor, options } = this;
                    const title = Page.lang.importHtmlLinkTitle;
                    const content = this.getContent();
                    this.content = content;
                    editor.Modal.open({ title, content })
                        .getModel()
                        .once("change:open", () => editor.stopCommand(this.id));
                },

                getContent() {
                    const { editor } = this;
                    const content = document.createElement("div");
                    const pfx = editor.getConfig("stylePrefix");
                    content.className = `${pfx}custom-html-link`;

                    appendToContent(content, this.getContentBody());
                    appendToContent(content, this.getContentActions());

                    return content;
                },

                getContentBody() {
                    const body = document.createElement("div");
                    body.innerHTML = `<div class="form-group"> 
                    <label for="customHtmlInput" class="control-label">${Page.lang.importHtmlLinkInputLabel}</label>
                    <input type="text" class="form-control" id="customHtmlInput" aria-describedby="inputHelp"
                    placeholder="${Page.lang.importHtmlLinkInputLabel}">
                    <small id="inputHelp" class="help-block">${Page.lang.importHtmlLinkInputHelp}</small>
                    </div>
                  `;
                    return body;
                },

                getContentActions() {
                    const { editor } = this;
                    const btn = document.createElement("button");
                    const pfx = editor.getConfig("stylePrefix");
                    btn.innerHTML = Page.lang.importHtmlLinkButtonLabel;
                    btn.className = `${pfx}btn-prim ${pfx}btn-import__custom-code`;
                    btn.onclick = () => this.handleOnClick();

                    return btn;
                },

                handleOnClick() {
                    const { editor, target, content } = this;

                    const input = content.querySelector("#customHtmlInput");
                    if (!input.value.length) {
                        content
                            .querySelector(".form-group")
                            .classList.add("has-error");
                        return false;
                    }

                    content
                        .querySelector(".form-group")
                        .classList.remove("has-error");

                    // var req = new XMLHttpRequest();

                    // req.open("GET", input.value, false);

                    // req.send(null);

                    // if (req.status == 200) {
                    //     editor.setComponents(req.responseText);
                    //     editor.stopCommand(this.id);
                    // } else {
                    //     content
                    //         .querySelector(".form-group")
                    //         .classList.add("has-error");
                    // }

                    var data = {
                        page: input.value,
                    };

                    var _this = this;

                    $.ajax({
                        url: Page.fetchPage,
                        type: "POST",
                        data: data,
                        dataType: "json",
                        success: function (returnResult) {
                            if (
                                returnResult.success == 1 &&
                                returnResult.data
                            ) {
                                editor.setComponents(returnResult.data);
                                editor.stopCommand(_this.id);
                            } else {
                                content
                                    .querySelector(".form-group")
                                    .classList.add("has-error");
                            }
                        },
                        error: function () {
                            content
                                .querySelector(".form-group")
                                .classList.add("has-error");
                        },
                    });
                },

                stop(editor) {
                    const { sender } = this;
                    sender && sender.set && sender.set("active", 0);
                    editor.Modal.close();
                    editor.refresh();
                },
            });

            cmdm.add("canvas-clear", function () {
                if (confirm("Are you sure to clean the canvas?")) {
                    var comps = editor.DomComponents.clear();
                    setTimeout(function () {
                        localStorage.clear();
                    }, 0);
                }
            });

            cmdm.add("set-device-desktop", {
                run: function (ed) {
                    ed.setDevice("Desktop");
                },
                stop: function () {},
            });

            cmdm.add("set-device-tablet", {
                run: function (ed) {
                    ed.setDevice("Tablet");
                },
                stop: function () {},
            });

            cmdm.add("set-device-mobile", {
                run: function (ed) {
                    ed.setDevice("Mobile portrait");
                },
                stop: function () {},
            });

            cmdm.add("preview", {
                run(editor, sender) {
                    window.open(Page.previewUrl, "_blank");
                    editor.stopCommand("preview");
                },
            });

            pn.addButton("options", [
                {
                    id: "import-html-link",
                    className: "import-html-link fa fa-link",
                    command: "import-html-link",
                },
                {
                    id: "save-html",
                    className: "fa fa-floppy-o",
                    command: "save-html",
                },
                {
                    id: "toggle-language",
                    className: "toggle-language fa fa-language",
                    command: "toggle-language",
                },
            ]);

            // Add and beautify tooltips
            [
                ["sw-visibility", "Show Borders"],
                ["preview", Page.lang.preview],
                ["fullscreen", "Fullscreen"],
                ["export-template", "Export"],
                ["undo", "Undo"],
                ["redo", "Redo"],
                ["gjs-open-import-webpage", "Import"],
                ["canvas-clear", "Clear canvas"],
                ["import-html-link", Page.lang.importHtmlLink],
                ["save-html", Page.lang.save],
                ["toggle-language", Page.lang.language],
            ].forEach(function (item) {
                pn.getButton("options", item[0]).set("attributes", {
                    title: item[1],
                    "data-tooltip-pos": "bottom",
                });
            });

            [
                ["open-sm", "Style Manager"],
                ["open-layers", "Layers"],
                ["open-blocks", "Blocks"],
            ].forEach(function (item) {
                pn.getButton("views", item[0]).set("attributes", {
                    title: item[1],
                    "data-tooltip-pos": "bottom",
                });
            });
            var titles = document.querySelectorAll("*[title]");

            for (var i = 0; i < titles.length; i++) {
                var el = titles[i];
                var title = el.getAttribute("title");
                title = title ? title.trim() : "";
                if (!title) break;
                el.setAttribute("data-tooltip", title);
                el.setAttribute("title", "");
            }

            // Show borders by default
            pn.getButton("options", "sw-visibility").set("active", 1);

            // Store and load events
            editor.on("storage:load", function (e) {
                // console.log("Loaded ", e);
            });

            editor.on("storage:store", function (e) {
                // console.log("Stored ", e);
            });

            // Do stuff on load
            editor.on("load", function () {
                var $ = grapesjs.$;

                // Load and show settings and style manager
                var openTmBtn = pn.getButton("views", "open-tm");
                openTmBtn && openTmBtn.set("active", 1);

                var openSm = pn.getButton("views", "open-sm");
                openSm && openSm.set("active", 1);

                // Add Settings Sector
                var traitsSector = $(
                    '<div class="gjs-sm-sector no-select">' +
                        '<div class="gjs-sm-title"><span class="icon-settings fa fa-cog"></span> Settings</div>' +
                        '<div class="gjs-sm-properties" style="display: none;"></div></div>'
                );
                var traitsProps = traitsSector.find(".gjs-sm-properties");
                traitsProps.append($(".gjs-trt-traits"));
                $(".gjs-sm-sectors").before(traitsSector);
                traitsSector.find(".gjs-sm-title").on("click", function () {
                    var traitStyle = traitsProps.get(0).style;
                    var hidden = traitStyle.display == "none";
                    if (hidden) {
                        traitStyle.display = "block";
                    } else {
                        traitStyle.display = "none";
                    }
                });

                // Open block manager
                var openBlocksBtn = editor.Panels.getButton(
                    "views",
                    "open-blocks"
                );
                openBlocksBtn && openBlocksBtn.set("active", 1);

                $(".top-save-button").on("click", function (e) {
                    cmdm.run("save-html");
                });
            });
        };

        var handleOnChangeStatus = function () {
            $("input[name=custom_html_status]").on("change", function (e) {
                var self = $(this);
                var switch_status = self.siblings(".switchery-status");
                if (self.is(":checked")) {
                    switch_status.html(Page.lang.enableStatus);
                } else {
                    switch_status.html(Page.lang.disableStatus);
                }
            });

            $("input[name=display_main_page_layout]").on("change", function (
                e
            ) {
                var self = $(this);
                var switch_status = self.siblings(".switchery-status");
                if (self.is(":checked")) {
                    switch_status.html(Page.lang.enableDisplayMainPageLayout);
                } else {
                    switch_status.html(Page.lang.disableDisplayMainPageLayout);
                }
            });
        };

        return {
            init: function () {
                if (typeof grapesjs === "undefined") {
                    console.warn("unable to load grapesjs library!");
                    return false;
                }
                minimizeSideBar();
                initializeEditor();
                handleOnChangeStatus();
            },
        };
    })();
    // INITIALIZE COMPONENT AFTER JQUERY LOADED
    $(function () {
        Component.init();
    });
})();
