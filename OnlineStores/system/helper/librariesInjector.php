<?php
if (!function_exists('include_lib')) {
	function include_lib(&$header, &$footer, $libs) {
        $libHeaderString = "";
        $libFooterString = "";

        $libsArray = explode(',', $libs);
        foreach($libsArray as $libName)
        {
            switch (strtolower($libName)) {
                case "datatables":
                    $libHeaderString .= '<link rel="stylesheet"type="text/css" href="view/stylesheet/cube/css/libs/dataTables.tableTools.css" />';
                    $libFooterString .= '<script type="text/javascript" src="view/javascript/cube/jquery.dataTables.min.js"></script>';
                    $libFooterString .= '<script type="text/javascript" src="view/javascript/cube/dataTables.tableTools.min.js"></script>';
                    $libFooterString .= '<script type="text/javascript" src="view/javascript/cube/jquery.dataTables.bootstrap.js"></script>';
                    break;
//                case "icheck":
//                    $libHeaderString .= '<link href="view/javascript/jquery.icheck/skins/square/blue.css" rel="stylesheet">';
//                    $libFooterString .= '<script type="text/javascript" src="view/javascript/jquery.icheck/icheck.min.js"></script>';
//                    break;
            }
        }

        $libHeaderString = $libHeaderString . '<scriptholder id="headerScriptsContent"></scriptholder>';
        $libFooterString = $libFooterString . '<scriptholder id="footerScriptsContent"></scriptholder>';

        $header = str_replace('<scriptholder id="headerScriptsContent"></scriptholder>', $libHeaderString, $header);
        $footer = str_replace('<scriptholder id="footerScriptsContent"></scriptholder>', $libFooterString, $footer);
	}
}

if (!function_exists('include_jsfile')) {
    function include_jsfile(&$header, &$footer, $filepath) {
        $libFooterString = '<script type="text/javascript" src="' . $filepath . '"></script>';
        $libFooterString = $libFooterString . '<scriptholder id="footerScriptsContent"></scriptholder>';
        $footer = str_replace('<scriptholder id="footerScriptsContent"></scriptholder>', $libFooterString, $footer);
    }
}

if (!function_exists('include_ckeditor')) {
    function include_ckeditor(&$header, &$footer) {
        $ckeditorString = '<script type="text/javascript" src="view/javascript/ckeditor/ckeditor.js"></script>';
        $header = str_replace('<scriptholder id="ckeditorplaceholder"></scriptholder>', $ckeditorString, $header);
    }
}

if (!function_exists('include_cssfile')) {
    function include_cssfile(&$header, &$footer, $filepath) {
        $libHeaderString = '<link rel="stylesheet"type="text/css" href="' . $filepath . '" />';
        $libHeaderString = $libHeaderString . '<scriptholder id="headerScriptsContent"></scriptholder>';
        $header = str_replace('<scriptholder id="headerScriptsContent"></scriptholder>', $libHeaderString, $header);
    }
}

if (!function_exists('include_htmlcode')) {
    function include_htmlcode(&$header, &$footer, $code, $sectionType) {
        if ($sectionType == 0) { //Header
            $libHeaderString = $code;
            $libHeaderString = $libHeaderString . '<scriptholder id="headerScriptsContent"></scriptholder>';
            $header = str_replace('<scriptholder id="headerScriptsContent"></scriptholder>', $libHeaderString, $header);
        }
        elseif ($sectionType == 1) { //Footer
            $libFooterString = $code;
            $libFooterString = $libFooterString . '<scriptholder id="footerScriptsContent"></scriptholder>';
            $footer = str_replace('<scriptholder id="footerScriptsContent"></scriptholder>', $libFooterString, $footer);
        }
    }
}
?>