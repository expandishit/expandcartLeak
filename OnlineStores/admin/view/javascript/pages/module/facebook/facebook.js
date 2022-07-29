/*global $, console*/

$(function () {
    'use strict';

    //// Start integration setup steps collapse
    $('.integration-setup-steps-holder .integration-setup-step .panel-collapse').collapse('hide');
    $('.integration-setup-steps-holder .IntegrationSetupActive .panel-collapse').collapse('show');
    //// End integration setup steps collapse

    //// Start select2
    $('select.selectSearch').select2();
    //// End select2
});