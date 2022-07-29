/* eslint-disable no-undef */
import { closestParentCallable } from "../helper/helpers";

export default async function (event) {
    event.stopPropagation();

    var addressId = event.target.getAttribute("data-address");

    if (!addressId || event.target.getAttribute("disabled")) return false;

    event.target.setAttribute("disabled", 1);

    var result = await this.api.deleteAddress(addressId);

    if (!result.success) {
        if (result.errors?.warning) {
            var container = $(event.target).closest(".address-info__container");
            container.addClass("prevent--remove");

            if (!container.find(".result").length) {
                container
                    .find(".address-info__caption")
                    .append('<div class="result"></div>');
            }

            container
                .find(".result")
                .html(
                    `<p class="address-info__error">${result.errors.warning}</p>`
                );
        }

        event.target.removeAttribute("disabled");

        return false;
    }

    var addressThumb = closestParentCallable(
        document.querySelector(`[data-address="${addressId}"]`),
        function (element) {
            return element.classList.contains("address-info__container");
        }
    );

    addressThumb && addressThumb.remove();

    return false;
}
