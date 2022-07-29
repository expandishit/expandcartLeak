<?php
if (!function_exists('exec_command')) {
    // run background command
    function exec_command($command)
    {
        if (substr(php_uname(), 0, 7) == "Windows") {
            //windows
            pclose(popen("start /B cmd /C " . $command . " >NUL 2>&1", "r"));
        } else {
            //linux
            shell_exec($command . " > /dev/null 2>/dev/null &");
        }
    }
}
