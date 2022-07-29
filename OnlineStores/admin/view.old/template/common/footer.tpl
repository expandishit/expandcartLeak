<?php if ($logged) { ?>
<footer id="footer-bar" class="row" style="opacity: 1;">
    <p id="footer-copyright" class="col-xs-12">
        Powered by <a href="https://www.expandcart.com" target="_blank">ExpandCart</a>
    </p>
</footer>
</div>
</div>
</div>
</div>
<!--
<div id="config-tool" class="closed">
    <a id="config-tool-cog">
        <i class="fa fa-cog"></i>
    </a>

    <div id="config-tool-options">
        <h4>Layout Options</h4>
        <ul>
            <li>
                <div class="checkbox-nice">
                    <input type="checkbox" id="config-fixed-header" />
                    <label for="config-fixed-header">
                        Fixed Header
                    </label>
                </div>
            </li>
            <li>
                <div class="checkbox-nice">
                    <input type="checkbox" id="config-fixed-sidebar" />
                    <label for="config-fixed-sidebar">
                        Fixed Left Menu
                    </label>
                </div>
            </li>
            <li>
                <div class="checkbox-nice">
                    <input type="checkbox" id="config-fixed-footer" />
                    <label for="config-fixed-footer">
                        Fixed Footer
                    </label>
                </div>
            </li>
            <li>
                <div class="checkbox-nice">
                    <input type="checkbox" id="config-boxed-layout" />
                    <label for="config-boxed-layout">
                        Boxed Layout
                    </label>
                </div>
            </li>
            <li>
                <div class="checkbox-nice">
                    <input type="checkbox" id="config-rtl-layout" />
                    <label for="config-rtl-layout">
                        Right-to-Left
                    </label>
                </div>
            </li>
        </ul>
        <br/>
        <h4>Skin Color</h4>
        <ul id="skin-colors" class="clearfix">
            <li>
                <a class="skin-changer" data-skin="" data-toggle="tooltip" title="Default" style="background-color: #34495e;">
                </a>
            </li>
            <li>
                <a class="skin-changer" data-skin="theme-white" data-toggle="tooltip" title="White/Green" style="background-color: #2ecc71;">
                </a>
            </li>
            <li>
                <a class="skin-changer blue-gradient" data-skin="theme-blue-gradient" data-toggle="tooltip" title="Gradient">
                </a>
            </li>
            <li>
                <a class="skin-changer" data-skin="theme-turquoise" data-toggle="tooltip" title="Green Sea" style="background-color: #1abc9c;">
                </a>
            </li>
            <li>
                <a class="skin-changer" data-skin="theme-amethyst" data-toggle="tooltip" title="Amethyst" style="background-color: #9b59b6;">
                </a>
            </li>
            <li>
                <a class="skin-changer" data-skin="theme-blue" data-toggle="tooltip" title="Blue" style="background-color: #2980b9;">
                </a>
            </li>
            <li>
                <a class="skin-changer" data-skin="theme-red" data-toggle="tooltip" title="Red" style="background-color: #e74c3c;">
                </a>
            </li>
            <li>
                <a class="skin-changer" data-skin="theme-whbl" data-toggle="tooltip" title="White/Blue" style="background-color: #3498db;">
                </a>
            </li>
        </ul>
    </div>
</div>
-->
<!-- global scripts -->
<!--
<script src="view/javascript/cube/demo-skin-changer.js"></script> <!-- only for demo
-->

<script src="view/javascript/cube/jquery.nanoscroller.min.js"></script>

<script src="view/javascript/cube/demo.js"></script> <!-- only for demo -->

<!-- this page specific scripts -->
<script src="view/javascript/cube/jquery.nanoscroller.min.js"></script>
<script src="view/javascript/cube/modernizr.custom.js"></script>
<script src="view/javascript/cube/moment.min.js"></script>
<script src="view/javascript/cube/gdp-data.js"></script>
<script src="view/javascript/cube/flot/jquery.flot.min.js"></script>
<script src="view/javascript/cube/flot/jquery.flot.resize.min.js"></script>
<script src="view/javascript/cube/flot/jquery.flot.time.min.js"></script>
<script src="view/javascript/cube/flot/jquery.flot.threshold.js"></script>
<script src="view/javascript/cube/flot/jquery.flot.axislabels.js"></script>
<script src="view/javascript/cube/jquery.sparkline.min.js"></script>
<script src="view/javascript/cube/skycons.js"></script>
<script src="view/javascript/cube/snap.svg-min.js"></script>
<script src="view/javascript/cube/classie.js"></script>
<script src="view/javascript/cube/notificationFx.js"></script>
<script src="view/javascript/cube/hopscotch.js"></script>
<script src="view/javascript/cube/modalEffects.js"></script>
<script src="view/javascript/cube/dropzone.min.js"></script>

<!-- theme scripts -->
<script src="view/javascript/cube/scripts.js"></script>
<script src="view/javascript/cube/pace.min.js"></script>

<script>
    if (typeof(notificationString) != 'undefined') {
        $(document).ready(function () {
            // create the notification
            var notification = new NotificationFx({
                message : notificationString,
                layout : 'growl',
                effect : 'jelly',
                ttl : notificationType == 'success' ? 2500 : 20000,
                type : notificationType, // notice, warning, error or success
            });

            // show the notification
            notification.show();
        } );
    }
</script>

<scriptholder id="footerScriptsContent"></scriptholder>

</body>
</html>
<?php } ?>