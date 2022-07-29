$(document).ready(function() {
    // Start the tour!
    if (guidestate == '1' && $(document).width() >= 992) {
        var placementRight = 'right';
        var placementLeft = 'left';

        if ($('body').hasClass('rtl')) {
            placementRight = 'left';
            placementLeft = 'right';
        }

        mgr = hopscotch.getCalloutManager();

        setTimeout(function() {
            $('#dashboard').css('background-color', 'rgba(3, 169, 244, 0.37)');
            mgr.createCallout({
                id: "setting-intro",
                target: 'dashboard',
                placement: placementRight,
                title: callout_title,
                content: callout_content,
                yOffset: -10,
                onClose: function() {
                    $('#dashboard').css('background-color', 'initial');
                }
            });
        }, 100);
    }
});