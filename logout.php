<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

session_start();
function help_redirect($url)
{
    if (headers_sent() === false)
    {
        header('Location: ' . $url, true);
    }
}

$hdr = '<!DOCTYPE html>
        <html lang = "en">
          <head>
            <meta charset="UTF-8">
            <title>content 1 PHP</title>
            <link rel='."stylesheet".' href='."./style.css".' type='."text/css".' media='."all".' />
            <script src='."login.js".'></script>
          </head>
          <body>
          <div id="content">';

function killsession(){
    $_SESSION = array();
    session_destroy();
    $path = explode('/', $_SERVER['PHP_SELF'], -1);
    $path = implode('/', $path);
    $path = "http://$_SERVER[HTTP_HOST]$path/";
    help_redirect($path);
    die();
    exit();
}

killsession();
?>