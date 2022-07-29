/* eslint-disable no-undef */
export const toArray = function toArray($value) {
    return $value instanceof Array ? $value : [$value];
};

export const getElementInArray = function getElementInArray(
    elements = [],
    condition = function () {}
) {
    if (!(elements instanceof Array) || !elements.length) return null;

    var map = function (elements) {
            if (!elements.length) return null;
            element = elements.splice(0, 1)[0];
            return condition(element) ? element : map(elements);
        },
        element;

    return map(elements.slice(0));
};

export const loadSdk = function loadSdk(src, id) {
    return (function (d, s, id) {
        var js,
            fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {
            return false;
        }
        js = d.createElement(s);
        js.id = id;
        js.src = src;
        js.async = 1;
        js.defer = 1;
        fjs.parentNode.insertBefore(js, fjs);
    })(document, "script", id);
};

export const isOdd = function (x) {
    return x & 1;
};

export const isEven = function (x) {
    return !(x & 1);
};

// Recursively finds the closest parent that has the specified condition
export const closestParentCallable = (child, callable) => {
    if (!child || child == document) {
        return null;
    }

    if (callable(child)) {
        return child;
    }

    return closestParentCallable(child.parentNode, callable);
};

export const redirect = (to = null) => {
    if (to && ("" + to).length) {
        location.href = to;
        return;
    }

    let currentUrl = new URL(location);

    if (!currentUrl.searchParams.has("route")) {
        location.reload();
        return;
    }

    let currentRoute = currentUrl.searchParams.get("route");

    if ("account/logout" == currentRoute) {
        currentUrl.searchParams.delete("route");
    }

    location.href = currentUrl.toString();
};

export function countdown(elementName, minutes, seconds, labelIn, labelOut) {
    var element, endTime, hours, mins, msLeft, time, countdown;

    function twoDigits(n) {
        return n <= 9 ? "0" + n : n;
    }

    function updateTimer() {
        msLeft = endTime - +new Date();
        if (msLeft < 1000) {
            element.innerHTML = `${labelOut}`;
            // $(".login--popup__resend").addClass("active");
            element.classList.add("active");
        } else {
            // $(".login--popup__resend").removeClass("active");
            element.classList.remove("active");
            time = new Date(msLeft);
            hours = time.getUTCHours();
            mins = time.getUTCMinutes();
            countdown =
                (hours ? hours + ":" + twoDigits(mins) : mins) +
                ":" +
                twoDigits(time.getUTCSeconds());

            element.innerHTML = `${labelIn}<span id="countdown">${countdown}</span>`;
            setTimeout(updateTimer, time.getUTCMilliseconds() + 500);
        }
    }

    element = document.querySelector(elementName);
    endTime = +new Date() + 1000 * (60 * minutes + seconds) + 500;
    element && updateTimer();
}

export const serialize = (form) => {
    if (!form || form.nodeName !== "FORM") {
        return;
    }
    var i,
        j,
        q = [];
    for (i = form.elements.length - 1; i >= 0; i = i - 1) {
        if (form.elements[i].name === "") {
            continue;
        }
        switch (form.elements[i].nodeName) {
            case "INPUT":
                switch (form.elements[i].type) {
                    case "text":
                    case "hidden":
                    case "password":
                    case "button":
                    case "reset":
                    case "submit":
                        q.push(
                            form.elements[i].name +
                                "=" +
                                encodeURIComponent(form.elements[i].value)
                        );
                        break;
                    case "checkbox":
                    case "radio":
                        if (form.elements[i].checked) {
                            q.push(
                                form.elements[i].name +
                                    "=" +
                                    encodeURIComponent(form.elements[i].value)
                            );
                        }
                        break;
                    case "file":
                        break;
                }
                break;
            case "TEXTAREA":
                q.push(
                    form.elements[i].name +
                        "=" +
                        encodeURIComponent(form.elements[i].value)
                );
                break;
            case "SELECT":
                switch (form.elements[i].type) {
                    case "select-one":
                        q.push(
                            form.elements[i].name +
                                "=" +
                                encodeURIComponent(form.elements[i].value)
                        );
                        break;
                    case "select-multiple":
                        for (
                            j = form.elements[i].options.length - 1;
                            j >= 0;
                            j = j - 1
                        ) {
                            if (form.elements[i].options[j].selected) {
                                q.push(
                                    form.elements[i].name +
                                        "=" +
                                        encodeURIComponent(
                                            form.elements[i].options[j].value
                                        )
                                );
                            }
                        }
                        break;
                }
                break;
            case "BUTTON":
                switch (form.elements[i].type) {
                    case "reset":
                    case "submit":
                    case "button":
                        q.push(
                            form.elements[i].name +
                                "=" +
                                encodeURIComponent(form.elements[i].value)
                        );
                        break;
                }
                break;
        }
    }
    return q.join("&");
};

export const addQueryParam = (name, value) => {
    const url = new URL(window.location);
    url.searchParams.set(name, value);
    window.history.pushState({}, "", decodeURIComponent(url));
};

export const removeQueryParam = (name) => {
    const url = new URL(window.location);
    if (url.searchParams.has(name)) url.searchParams.delete(name);
    window.history.pushState({}, "", decodeURIComponent(url));
};

export const isObject = (obj) => obj && typeof obj === "object";

/**
 * Performs a deep merge of `source` into `target`.
 * Mutates `target` only but not its objects and arrays.
 *
 * @author inspired by [jhildenbiddle](https://stackoverflow.com/a/48218209).
 */
export function mergeDeep(target, source) {
    // $.extend(target, source);

    if (!isObject(target) || !isObject(source)) {
        return source;
    }

    Object.keys(source).forEach((key) => {
        const targetValue = target[key];
        const sourceValue = source[key];

        if (Array.isArray(targetValue) && Array.isArray(sourceValue)) {
            target[key] = targetValue.concat(sourceValue);
        } else if (isObject(targetValue) && isObject(sourceValue)) {
            target[key] = mergeDeep(
                Object.assign({}, targetValue),
                sourceValue
            );
        } else {
            target[key] = sourceValue;
        }
    });

    return target;
}

export function parseAlertHtml(type, message, display = "none") {
    return (
        '<div class="alert alert-' +
        type +
        ' alert-dismissible" style="display: ' +
        display +
        ';" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
        message +
        "</div>"
    );
}

export function bootStrapAlert(type = "success", message = "Success") {
    if (
        ["success", "info", "warning", "danger"].indexOf(type) === -1 ||
        typeof $ === "undefined"
    )
        return;

    $("#notification").html("<br>" + parseAlertHtml(type, message));

    $(".alert-" + type).fadeIn("slow");
    $(".alert-" + type)
        .delay(3000)
        .fadeOut("slow");
}

export function catchPaste(evt, elem, callback) {
    if (navigator.clipboard && navigator.clipboard.readText) {
        // modern approach with Clipboard API
        navigator.clipboard.readText().then(callback);
    } else if (evt.originalEvent && evt.originalEvent.clipboardData) {
        // OriginalEvent is a property from jQuery, normalizing the event object
        callback(evt.originalEvent.clipboardData.getData("text"));
    } else if (evt.clipboardData) {
        // used in some browsers for clipboardData
        callback(evt.clipboardData.getData("text/plain"));
    } else if (window.clipboardData) {
        // Older clipboardData version for Internet Explorer only
        callback(window.clipboardData.getData("Text"));
    } else {
        // Last resort fallback, using a timer
        setTimeout(function () {
            callback(elem.value);
        }, 100);
    }
}
