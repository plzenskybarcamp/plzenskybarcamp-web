<?php

$file = __DIR__ . sprintf("/logo-colors/barcamp%02s.png",   mt_rand(1,15));

header('Expires: 0');
header('Cache-Control: no-cache');
header('Pragma: no-cache');

if (file_exists($file)) {
    header('Content-Description: Random logo color :)');
    header('Content-Type: image/png');
    header('Content-Length: ' . filesize($file));
    ob_clean();
    flush();
    readfile($file);
    exit;
}
else {
    header("HTTP/1.0 404 Not Found");
    echo "$file";
}