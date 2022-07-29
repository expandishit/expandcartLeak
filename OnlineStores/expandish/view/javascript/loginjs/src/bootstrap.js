import "intl-tel-input/build/css/intlTelInput.css";

/* IE Polyfill */

if (typeof Object.assign != "function") {
    Object.defineProperty(Object, "assign", {
        // eslint-disable-next-line no-unused-vars
        value: function assign(target, varArgs) {
            "use strict";
            if (target == null) {
                throw new TypeError(
                    "Cannot convert undefined or null to object"
                );
            }
            var to = Object(target);
            for (var index = 1; index < arguments.length; index++) {
                var nextSource = arguments[index];
                if (nextSource != null) {
                    for (var nextKey in nextSource) {
                        if (
                            Object.prototype.hasOwnProperty.call(
                                nextSource,
                                nextKey
                            )
                        ) {
                            to[nextKey] = nextSource[nextKey];
                        }
                    }
                }
            }
            return to;
        },
        writable: true,
        configurable: true,
    });
}

if (!Array.from) {
    Array.from = (function () {
        var toStr = Object.prototype.toString;
        var isCallable = function (fn) {
            return (
                typeof fn === "function" ||
                toStr.call(fn) === "[object Function]"
            );
        };
        var toInteger = function (value) {
            var number = Number(value);
            if (isNaN(number)) {
                return 0;
            }
            if (number === 0 || !isFinite(number)) {
                return number;
            }
            return (number > 0 ? 1 : -1) * Math.floor(Math.abs(number));
        };
        var maxSafeInteger = Math.pow(2, 53) - 1;
        var toLength = function (value) {
            var len = toInteger(value);
            return Math.min(Math.max(len, 0), maxSafeInteger);
        };

        return function from(arrayLike) {
            var C = this;
            var items = Object(arrayLike);
            if (arrayLike == null) {
                throw new TypeError(
                    "Array.from requires an array-like object - not null or undefined"
                );
            }
            var mapFn = arguments.length > 1 ? arguments[1] : void undefined;
            var T;
            if (typeof mapFn !== "undefined") {
                if (!isCallable(mapFn)) {
                    throw new TypeError(
                        "Array.from: when provided, the second argument must be a function"
                    );
                }
                if (arguments.length > 2) {
                    T = arguments[2];
                }
            }
            var len = toLength(items.length);
            var A = isCallable(C) ? Object(new C(len)) : new Array(len);
            var k = 0;
            var kValue;
            while (k < len) {
                kValue = items[k];
                if (mapFn) {
                    A[k] =
                        typeof T === "undefined"
                            ? mapFn(kValue, k)
                            : mapFn.call(T, kValue, k);
                } else {
                    A[k] = kValue;
                }
                k += 1;
            }
            A.length = len;
            return A;
        };
    })();
}

try {
    window.Popper = require("popper.js").default;
    var jquery = window.$ || window.jQuery;
    typeof jquery !== "function" &&
        (window.$ = window.jQuery = require("jquery"));

    // require("bootstrap");
} catch (e) {
    console.log(e.message);
}

(function () {
    "use strict";

    // save the original methods before overwriting them
    Element.prototype._addEventListener = Element.prototype.addEventListener;
    Element.prototype._removeEventListener =
        Element.prototype.removeEventListener;

    /**
     * [addEventListener description]
     * @param {[type]}  type       [description]
     * @param {[type]}  listener   [description]
     * @param {Boolean} useCapture [description]
     */
    Element.prototype.addEventListener = function (
        type,
        listener,
        useCapture = false
    ) {
        // declare listener
        this._addEventListener(type, listener, useCapture);

        if (!this.eventListenerList) this.eventListenerList = {};
        if (!this.eventListenerList[type]) this.eventListenerList[type] = [];

        // add listener to event tracking list
        this.eventListenerList[type].push({ type, listener, useCapture });
    };

    /**
     * [removeEventListener description]
     * @param  {[type]}  type       [description]
     * @param  {[type]}  listener   [description]
     * @param  {Boolean} useCapture [description]
     * @return {[type]}             [description]
     */
    Element.prototype.removeEventListener = function (
        type,
        listener,
        useCapture = false
    ) {
        // remove listener
        this._removeEventListener(type, listener, useCapture);

        if (!this.eventListenerList) this.eventListenerList = {};
        if (!this.eventListenerList[type]) this.eventListenerList[type] = [];

        // Find the event in the list, If a listener is registered twice, one
        // with capture and one without, remove each one separately. Removal of
        // a capturing listener does not affect a non-capturing version of the
        // same listener, and vice versa.
        for (let i = 0; i < this.eventListenerList[type].length; i++) {
            if (
                this.eventListenerList[type][i].listener === listener &&
                this.eventListenerList[type][i].useCapture === useCapture
            ) {
                this.eventListenerList[type].splice(i, 1);
                break;
            }
        }
        // if no more events of the removed event type are left,remove the group
        if (this.eventListenerList[type].length == 0)
            delete this.eventListenerList[type];
    };

    /**
     * [getEventListeners description]
     * @param  {[type]} type [description]
     * @return {[type]}      [description]
     */
    Element.prototype.getEventListeners = function (type) {
        if (!this.eventListenerList) this.eventListenerList = {};

        // return requested listeners type or all them
        if (type === undefined) return this.eventListenerList;
        return this.eventListenerList[type];
    };

    /*
    Element.prototype.clearEventListeners = function(a){
        if(!this.eventListenerList)
            this.eventListenerList = {};
        if(a==undefined){
            for(var x in (this.getEventListeners())) this.clearEventListeners(x);
            return;
        }
        var el = this.getEventListeners(a);
        if(el==undefined)
            return;
        for(var i = el.length - 1; i >= 0; --i) {
            var ev = el[i];
            this.removeEventListener(a, ev.listener, ev.useCapture);
        }
    };
    */
})();

window.intlTelInput = require("intl-tel-input");
