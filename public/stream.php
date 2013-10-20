<?php

function failed($status, $message)
{
    header("HTTP/1.0 {$status}");
    echo $message;
    die();
}


if (!isset($_GET['q'])) {
    failed('400 Bad Request', 'The version requested could not be found');
}

$browscapVersion = strtolower($_GET['q']);

switch ($browscapVersion)
{
    case 'browscapini':
        $file = "browscap.ini";
        break;
    case 'full_browscapini':
        $file = "full_asp_browscap.ini";
        break;
    case 'php_browscapini':
        $file = "php_browscap.ini";
        break;
    case 'full_php_browscapini':
        $file = "full_php_browscap.ini";
        break;
    default:
        failed('404 Not Found', 'The version requested could not be found');
}

$buildDirectory = __DIR__ . '/../build/';

$fullpath = $buildDirectory . $file;

if (!file_exists($fullpath)) {
    failed('500 Internal Server Error', 'The original file for the version requested could not be found');
}

header("HTTP/1.0 200 OK");
header("Cache-Control: public");
header("Content-Type: application/zip");
header("Content-Transfer-Encoding: Binary");
header("Content-Length:" . filesize($fullpath));
header("Content-Disposition: attachment; filename=" . $file);
readfile($fullpath);
