<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

session_start();
$hdr = '<!DOCTYPE html>
<html lang = "en">
  <head>
    <meta charset="UTF-8">
    <title>Final Project</title>
    <link rel="stylesheet" href="./style.css" type="text/css" media="all" />
    <script src="index.js"></script>
  </head>
  <body>';

$ftr = '    </div>
  </body>
</html>';

if(isset($_SESSION['active']) && $_SESSION['active']){
    $content = '<div id="content">';
} else {
  $content = '<div id="login">';
}

echo $hdr;
echo $content;
echo $ftr;
?>

    
      
