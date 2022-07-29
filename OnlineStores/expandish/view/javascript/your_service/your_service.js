window.addEventListener('DOMContentLoaded', function () {
    let settings = JSON.parse(window.ys_settings);
    $('ul.mmenu-js,ul.nav-level-1,.main-nav ul, .ui-menu,#slidemenu .navbar-nav, .navbar-nav,.push-sidebar ul.nav,.push-sidebar ul.nav>li.dropdown ul.dropdown-menu:not(.push-sidebar ul.nav>li.dropdown ul.dropdown-menu li ul)').append(`<li><a href="${window.location.origin}/index.php?route=module/your_service/requestService">${settings['request_service_link_name'][$('html').attr('lang')]}</a></li>`);
    $('#ys-title-base').html(settings['request_service_link_name'][$('html').attr('lang')]);
    if (settings['ms_notifications'] == 1)
    {
        $('body').append(`
            <style>
                .ys-notifications {
                    cursor: pointer
                }
                .ys-new-notification {
                    background: #F00;
                    border-radius: 50%;
                    height: 10px;
                    width: 10px;
                    display: inline-block;
                    position: relative;
                    left: 4px;
                    bottom: 4px;
                }
                .ys-notifications-menu {
                    position: fixed;
                    width: 250px;
                    border-radius: 5px;
                    background: #232f3e;
                    z-index: 9999999999;
                    padding: 10px;
                    transition: all .6s ease;
                    max-height: 450px;
                    overflow-y: auto;
                    top: -450px;
                    opacity: 0;
                }
                .ys-notifications-menu a {
                    color: #FFF;
                    border-left: none!important;
                }
                .ys-notifications-menu .content div {
                    padding-top: 5px;
                    padding-bottom: 5px;
                    border-bottom: 1px solid #FFF;
                }
                .ys-show {
                    top: 35px;
                    opacity: 1;
                }
            </style>
        `);
        $('.top-header .account-login:last').prepend(`
            <a class="ys-notifications"><i class="fa fa-bell"></i><span class="ys-new-notification"></span></a>
            <div class="ys-notifications-menu">
                <div class="content"></div>
                <div class="text-center">
                    <a href="${window.location.origin}/index.php?route=module/your_service/serviceRequests"><i class="fa fa-share"></i></a>
                </div>
            </div>
        `);
        $('.ys-new-notification').hide();
        getSellerNotifications();
        setInterval(getSellerNotifications, 5000);
    }
    $('body').on('click', '.ys-notifications', function (e) {
        e.preventDefault();
        $('.ys-new-notification').hide();
        $('.ys-notifications-menu').toggleClass('ys-show');
    });

    function getSellerNotifications() {
        var prevRes = $('.ys-notifications-menu .content').html();
        $.ajax({
            type: 'GET',
            url: `${window.location.origin}/index.php?route=module/your_service/getSellerNotifications`,
            success: function (res) {
                $('.ys-notifications-menu .content').html(res);
                if ($('.ys-notifications-menu .content').html() != prevRes && prevRes != '') {
                    $('.ys-new-notification').show();
                }
            }
        });
    }
});