

var printful = function () {
    this.ord = 2;
};

printful.prototype.Order = function (e) {

    let url = $(e).data('href'),
        orderId = $(e).data('order')

    $.ajax({
        url: url,
        data: {order_id: orderId},
        method: 'POST',
        dataType: 'JSON',
        success: (r) => {
            if (r.status != 'OK') {
                // Handle errors
                return;
            }

            // if type != undefined then check order status
            if (typeof r.orderId == 'undefined') {

            }
        }
    });

};

var Printful = new printful();