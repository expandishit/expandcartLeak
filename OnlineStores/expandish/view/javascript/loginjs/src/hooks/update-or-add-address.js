export default function (event) {
    event.preventDefault();
    event.stopPropagation();
    this.view.addressFormModal.show(
        event.target.getAttribute("data-address") || null
    );
    return false;
}
