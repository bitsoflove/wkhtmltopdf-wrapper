<?php

use BitsOfLove\PdfLib;
require_once('../src/pdflib.php');

//using a framework, you would probably create dynamic templates
//this here is just a static example. in any case, we need the raw html + js to send to wkhtmltopdf

$options = array('body' => file_get_contents('templates/body.html'),
                 //'cover' => file_get_contents('templates/cover.html'),
                 'footer' => file_get_contents('templates/footer.html'),
                 'header' => file_get_contents('templates/header.html'),
                 'immediateDownload' => true,
                 'name' => 'demo');

$pdfLib = new PdfLib();
$result = $pdfLib->generate($options);

//will output pdf location when immediateDownload = false
var_dump($result);
