
var langCode;

$.ajax({
    type: "get",
    url: "/index.php?route=seller/account-profile/getConfig",
    success: function (response) {
        langCode=response;
    }
});