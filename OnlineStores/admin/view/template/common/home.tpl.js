function skipTut() {
    $.ajax({
        type: 'get',
        url: hideGettingStartedURL,
        dataType: 'json',
        async: false,
        success: function(json) {
            if (json.success == "true") {
                $('#guideDiv').slideUp("slow");
                $('#shortStats').slideDown("slow");
                $('#yearlyData').slideDown("slow");
                $('#lastOrders').slideDown("slow");
                $('#breadcrumb').show("fast");

                plotStatsGraph();
            }
        }
    });
}

//CHARTS
function gd(year, day, month) {
    return new Date(year, month - 1, day).getTime();
}

function plotStatsGraph() {
    $.ajax({
        type: 'get',
        url: chartURL,
        dataType: 'json',
        async: false,
        success: function(json) {
            if ($('#graph-bar').length) {

                var data1 = []; // Orders
                var data2 = []; // Customers

                var monthsEN = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                var monthsAR = ["يناير", "فبراير", "مارس", "أبريل", "مايو", "يونيو", "يوليو", "أغسطس", "سبتمبر", "أكتوبر", "نوفمبر", "ديسمبر"];

                for (var i = 0; i < json.xaxis.length; i++)
                {
                    data1.push([gd(json.xaxis[i][1], 1, json.xaxis[i][0]), json.order.data[i][1]]);
                    data2.push([gd(json.xaxis[i][1], 1, json.xaxis[i][0]), json.customer.data[i][1]]);
                }

                var series = new Array();

                series.push({
                    data: data1,
                    bars: {
                        show : true,
                        barWidth: 24 * 60 * 60 * 12000,
                        lineWidth: 1,
                        fill: 1,
                        align: 'center'
                    },
                    label: text_orders
                });


                series.push({
                    data: data2,
                    color: '#e84e40',
                    lines: {
                        show : true,
                        lineWidth: 3,
                    },
                    points: {
                        fillColor: "#e84e40",
                        fillColor: '#ffffff',
                        pointWidth: 1,
                        show: true
                    },
                    label: text_customers
                });

                $.plot("#graph-bar", series, {
                    colors: ['#03a9f4', '#f1c40f', '#2ecc71', '#3498db', '#9b59b6', '#95a5a6'],
                    grid: {
                        tickColor: "#f2f2f2",
                        borderWidth: 0,
                        hoverable: true,
                        clickable: true
                    },
                    legend: {
                        noColumns: 1,
                        labelBoxBorderColor: "#000000",
                        position: "ne"
                    },
                    shadowSize: 0,
                    yaxis: {
                        position: selLang == 'rtl' ? "right" : "left"
                    },
                    xaxis: {
                        mode: "time",
                        tickSize: [1, "month"],
                        tickLength: 0,
                        monthNames: selLang == 'rtl' ? monthsAR : monthsEN,
                        // axisLabel: "Date",
                        axisLabelUseCanvas: true,
                        axisLabelFontSizePixels: 12,
                        axisLabelFontFamily: 'Droid Arabic Kufi, Open Sans, sans-serif',
                        axisLabelPadding: 10,
                        reserveSpace: true,

                        transform: function (v) { return selLang == 'rtl' ? -v : v; },
                        inverseTransform: function (v) { return selLang == 'rtl' ? -v : v; }
                    }
                });

                var previousPoint = null;
                $("#graph-bar").bind("plothover", function (event, pos, item) {
                    if (item) {
                        if (previousPoint != item.dataIndex) {

                            previousPoint = item.dataIndex;

                            $("#flot-tooltip").remove();

                            var x = data1[item.dataIndex][1];
                            var y = data2[item.dataIndex][1];

                            x = x ? x : 0;
                            y = y ? y : 0;

                            showTooltip(item.pageX, item.pageY, x, y);
                        }
                    }
                    else {
                        $("#flot-tooltip").remove();
                        previousPoint = [0,0,0];
                    }
                });

                function showTooltip(x, y, data1, data2) {
                    $('<div id="flot-tooltip">' + '<b>' + text_orders + ': </b><i>' + data1 + '</i>' +
                        '<br/><b>' + text_customers + ': </b><i>' + data2 + '</i>' +
                        '</div>').css({
                        top: y + 5,
                        left: x + 20,
                        'z-index': 100
                    }).appendTo("body").fadeIn(200);
                }
            }
        }
    });
}

$(document).ready(function() {
    if (gettingStarted == "1") {
        plotStatsGraph();
    }

    var placementRight = 'right';
    var placementLeft = 'left';

    if ($('body').hasClass('rtl')) {
        placementRight = 'left';
        placementLeft = 'right';
    }

    // Define the tour!
    var tour = {
        id: "Cube-intro",
        steps: [
            {
                target: "user-left-box",
                title: "Current online user",
                content: "You can find here status of user who's currently online.",
                placement: placementRight,
                yOffset: 10
            },
            {
                target: 'make-small-nav',
                title: "Small navigation button",
                content: "Click on the button and make sidebar navigation small.",
                placement: "bottom",
                zindex: 999,
                xOffset: -8,
                onNext: function () {
                    $('#system').css("background-color", "red");
                }
            },
            {
                target: 'system',
                title: "Sidebar navigation",
                content: "All template files are here.",
                placement: placementRight

            }
        ],
        showPrevButton: true,
        onClose: function () {
            alert(1);
        },
        onEnd: function () {
            alert(3);
        }
    };

    // Start the tour!
    //hopscotch.startTour(tour);
});