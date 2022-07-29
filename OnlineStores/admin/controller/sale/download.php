<?php
    class Controllersaledownload extends Controller
    {
        function index()
        {
            $fileName = $_GET["filename"];
            \Filesystem::setPath('downloads/' . $fileName);
            if (\Filesystem::isExists())
            {
                if($_GET["ob_clean"] == 'true') {
                    ob_clean();
                }
                header('Content-Type: application/octet-stream');
                header('Content-Description: File Transfer');
                header('Content-Disposition: attachment; filename="' . $fileName. '"');
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: no-cache');
                header('Content-Length: ' . \Filesystem::getSize());
                // readfile($filename);
                echo \Filesystem::read();

            }
            else
            {
                echo "File Not Found at \"$filename\"";
            }
        }
    }