$(function () {
    // select2 initialize
    $('.select').select2();

    // tooltip initialize
    $('[data-toggle="tooltip"]').tooltip();

    // (bot page) add class on parent when change collapses
    $('.panel-collapse').on('show.bs.collapse', function () {
        $(this).parent('.panel-default-border').addClass('panel-open');
    });

    $('.panel-collapse').on('hide.bs.collapse', function () {
        $(this).parent('.panel-default-border').removeClass('panel-open');
    });

    // (chatting page) select user to chat
    $('.side-users__item').on('click', function () {
        $('.side-users__item').removeClass('side-users__item-active');
        $(this).addClass('side-users__item-active');
        $('.whatsapp-chatting').addClass('whatsapp-chatting__side-conversation--open');
    });

    // (chatting page) open chat search
    $('.side-users-head__btn-search').on('click', function () {
        $('.side-users__search').slideDown();
        $('.whatsapp-input-search').focus();
        $('.whatsapp-chatting__side-users').addClass('js-search__open')
    });

    // (chatting page) get number of selected files
    $('#whatsapp-file-input').on('change', function () {
        var numFiles = $(this)[0].files.length;
        if (numFiles > 0) {
            $('.whatsapp-file-number').show().html(numFiles);
            $('.conversation-footer__btn').addClass('conversation-footer__btn-active');
        } else {
            $('.whatsapp-file-number').hide();
            $('.conversation-footer__btn').removeClass('conversation-footer__btn-active');
        }
    });

    // (chatting page) show btn send when input typing is active
    function emptyTyping() {
        var empty = false;
        $('.conversation-footer__input').each(function () {
            if ($(this).val() == '') {
                empty = true;
            }
        });
        if (empty) {
            $('.conversation-footer__btn').removeClass('conversation-footer__btn-active');
        } else {
            $('.conversation-footer__btn').addClass('conversation-footer__btn-active');
        }
    };
    $('.conversation-footer__input').keyup(function () {
        emptyTyping();
    });

    emptyTyping()

    /// (chatting page) scroll chat to bottom by default
    var element = document.getElementById("scrollBottom");
    element.scrollTop = element.scrollHeight;

    /// (chatting page) chat side bar on mobile
    $('.btn-open-users-side').on('click', function () {
        $('.whatsapp-chatting').removeClass('whatsapp-chatting__side-conversation--open');
    });

    /// (chatting page) open setting of chat
    $('.side-users-head__btn-settings').on('click', function () {
        $('.whatsapp-chatting').hide();
        $('.whatsapp-chatting-settings').css('display', 'flex');
    });

    $('.btn-back-chat').on('click', function () {
        $('.whatsapp-chatting').css('display', 'flex');;
        $('.whatsapp-chatting-settings').hide();
    });

    /// (chatting page) settings side bar on mobile
    $('.btn-open-settings').on('click', function () {
        $('.whatsapp-chatting-settings').addClass('whatsapp-chatting-settings__side-two--open');
    });

    $('.btn-back-settings').on('click', function () {
        $('.whatsapp-chatting-settings').removeClass('whatsapp-chatting-settings__side-two--open');
    });

    //// handel checkboxes on bot page
    if($('.customer-check').is(':checked')) {
        $('.customer-check-holder').slideDown();
    }else {
        $('.customer-check-holder').slideUp();
    }
    $('.customer-check').on('change', function () {
        if($('.customer-check').is(':checked')) {
            $('.customer-check-holder').slideDown();
        }else {
            $('.customer-check-holder').slideUp();
        }
    });

    if($('.owner-check').is(':checked')) {
        $('.owner-check-holder').slideDown();
    }else {
        $('.owner-check-holder').slideUp();
    }
    $('.owner-check').on('change', function () {
        if($('.owner-check').is(':checked')) {
            $('.owner-check-holder').slideDown();
        }else {
            $('.owner-check-holder').slideUp();
        }
    });
});