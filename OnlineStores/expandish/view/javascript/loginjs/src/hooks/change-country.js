import { getElementInArray } from "../helper/helpers";

export default function onChangeCountryInput(target) {
    // in case edit
    if (
        !target ||
        ("address_id" in target.dataset &&
            parseInt(target.dataset.address_id) > 0)
    )
        return;

    const { countries } = this.props;

    var country = getElementInArray(countries, function (country) {
        return country.country_id == target.value;
    });

    [...document.querySelectorAll("input[type=tel]")]
        .filter((input) => !!input.iti)
        .map((input) => input.iti.setCountry(country.iso_code_2.toUpperCase()));
}
